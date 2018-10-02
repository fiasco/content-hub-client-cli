<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\Table;
use Config\ClientConfig;

class ClientRegisterCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('client:register')
            ->setDescription('Register one or more clients against a Content Hub Backend.')
            ->addArgument(
              'client_name',
              InputArgument::IS_ARRAY | InputArgument::REQUIRED,
              'The client name to register as. Must be Unique.'
            )
            ->addOption(
              'api_key',
              'k',
              InputOption::VALUE_OPTIONAL,
              'Supply the API key to register with'
            )
            ->addOption(
              'secret_key',
              's',
              InputOption::VALUE_OPTIONAL,
              'Secret ket to register with'
            )
            ->addOption(
              'hostname',
              'H',
              InputOption::VALUE_OPTIONAL,
              'Supply the hostname to register against'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $new_config = false;
      $config = ClientConfig::loadFromInput($input, $output);

      // Allow to specify API credentials via the CLI if config doesn't exist.
      if (empty($config)) {
        $new_config = true;
        $config = new ClientConfig();

        $helper = $this->getHelper('question');

        if ($value = $input->getOption('api_key')) {
          $config->setApiKey($value);
        }
        else {
          $question = new Question('API Key: ');
          $config->setApiKey($helper->ask($input, $output, $question));
        }

        if ($value = $input->getOption('secret_key')) {
          $config->setSecretKey($value);
        }
        else {
          $question = new Question('Secret Key: ');
          $config->setSecretKey($helper->ask($input, $output, $question));
        }

        if ($value = $input->getOption('hostname')) {
          $config->setHostname($value);
        }
        else {
          $question = new Question('Hostname: ');
          $config->setHostname($helper->ask($input, $output, $question));
        }
      }

      $client = $config->loadClient();

      // Fetch list of existing client names to avoid conflicts (only existing
      // config with a registered client can fetch the settings).
      $existing_clients = [];
      if (!$new_config) {
        $settings = $client->getSettings();
        $existing_clients = $settings->getClients();
        array_walk($existing_clients, function(&$value, &$key) {
          $value = $value['name'];
        });
      }

      $client_list = $input->getArgument('client_name');
      $table = new Table($output);
      $table
        ->setHeaders(array('Client Name'));
      foreach ($client_list as $client_name) {
        $table->addRow([$client_name]);
      };
      $table->render();
      $helper = $this->getHelper('question');
      $question = new ConfirmationQuestion('You are about to register the above as new clients. Are you sure you want to continue? (y/N) ', false);
      if (!$helper->ask($input, $output, $question)) {
        return;
      }

      foreach ($client_list as $client_name) {
        if (in_array($client_name, $existing_clients)) {
          $output->writeln('<info>Client ' . $client_name . ' is already registered.</info>');
        }
        else {
          $response = $client->register($client_name);
          $output->writeln('<info>New client registered as ' . $response['uuid'] . ' ' . $response['name'] . '</info>');

        }
      };

      // Save new client on disk.
      if ($new_config && count($input->getArgument('client_name')) == 1 && !empty($response)) {
        $config->setOrigin($response['uuid'])
          ->setClientName($response['name']);
        $config->save($client_name);
        $output->writeln('<info>Saved new client ' . $response['name'] . ' on disk</info>');
      }
    }

}
