<?php

namespace AcquiaContentHubCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use AcquiaContentHubCli\Config\ClientConfig;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\RuntimeException;

class WebhookListenCommand extends WebhookServerCommand
{
  protected function configure()
    {
        $this
            ->setName('webhook:listen')
            ->setDescription('Show messages emitted from content hub in realtime.')
            ->addOption(
               'webhook_proxy_url',
               'p',
               InputOption::VALUE_OPTIONAL,
               'Use a proxy url (e.g. Ngrok) to register a webhook from behind a firewall.'
            )
            ->addOption(
               'address',
               'H',
               InputOption::VALUE_OPTIONAL,
               'The address to bind to e.g. 127.0.0.1:8080. Defaults to localhost:8000.',
               'localhost:8000'
            )
            ->addOption(
               'port',
               'P',
               InputOption::VALUE_OPTIONAL,
               'The port to bind to e.g. 8080. Defaults to 8000.',
               '8000'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if (!extension_loaded('pcntl')) {
          throw new \RuntimeException("PHP extension pcntl is required to use this command.");
        }
        $config = ClientConfig::loadFromInput($input, $output);
        $client = $config->loadClient();

        $address = $input->getOption('address');
        if (FALSE === strpos($address, ':')) {
            $address = $address . ':' . $input->getOption('port');
        }

        if ($this->isOtherServerProcessRunning($address)) {
          throw new \RuntimeException("Cannot bind to $address: address already in use.");
        }

        $documentRoot = dirname(__DIR__);
        $router = $documentRoot . '/' . 'webhook_log_router.php';
        $logfile = dirname($documentRoot);

        $pid = pcntl_fork();
        if ($pid < 0) {
            $io->error('Unable to start the server process.');
            return 1;
        }

        if ($pid > 0) {
            $io->success(sprintf('Web server listening on http://%s', $address));
            return;
        }

        if (posix_setsid() < 0) {
           $io->error('Unable to set the child process as session leader');
           return 1;
        }

        if (null === $process = $this->createServerProcess($io, $address, $documentRoot, $router)) {
            return 1;
        }

        $process->disableOutput();
        $process->start();
        $lockFile = $this->getLockFile($address);
        touch($lockFile);
        if (!$process->isRunning()) {
            $io->error('Unable to start the server process');
            unlink($lockFile);
            return 1;
        }
        $io->comment("Running. Webhook requests are logging to $logfile.");
        sleep(1);

        try {
          // Register the webhook with Content Hub.
          if (!$url = $input->getOption('webhook_proxy_url')) {
            $url = 'http://' . $address;
          }

          $url .= '/' . $config->getClientName();

          $output = $client->addWebhook($url);

          $webhook_uuid = $output['uuid'];
          $io->comment("Webhook registered as {$output['uuid']}");

          // stop the web server when the lock file is removed
          while ($process->isRunning()) {
              if (!file_exists($lockFile)) {
                  $process->stop();
              }
              sleep(1);
          }

          $client->deleteWebhook($webhook_uuid);
        }
        catch (\Exception $e) {
          $io->error($e->getMessage());
          $process->isRunning() && $process->stop();
          !empty($webhook_uuid) && $client->deleteWebhook($webhook_uuid);

          return 1;
        }
    }

    /**
     * Creates a process to start PHP's built-in web server.
     *
     * @param SymfonyStyle $io           A SymfonyStyle instance
     * @param string       $address      IP address and port to listen to
     * @param string       $documentRoot The application's document root
     * @param string       $router       The router filename
     *
     * @return Process The process
     */
    private function createServerProcess(SymfonyStyle $io, $address, $documentRoot, $router)
    {
        $finder = new PhpExecutableFinder();
        if (false === $binary = $finder->find()) {
            $io->error('Unable to find PHP binary to start server.');
            return;
        }
        $script = implode(' ', array_map(array('Symfony\Component\Process\ProcessUtils', 'escapeArgument'), array(
            $binary,
            '-S',
            $address,
            $router,
        )));
        return new Process('exec '.$script, $documentRoot, null, null, null);
    }
}
