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

class WebhookStopCommand extends WebhookServerCommand
{
  protected function configure()
    {
        $this
            ->setName('webhook:stop')
            ->setDescription('Stop logging from webhook server')
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

        if (!$this->isOtherServerProcessRunning($address)) {
          throw new \RuntimeException("Cannot find running server on $address.");
        }

        unlink($this->getLockFile($address));
        sleep(1);
        $io->success("Server listening on $address has stopped.");
    }
}
