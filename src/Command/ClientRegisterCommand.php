<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Config\ClientConfig;

class ClientRegisterCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('client:register')
            ->setDescription('Register a client against a Content Hub Backend.')
            ->addArgument(
              'client_name',
              InputArgument::REQUIRED,
              'The client name to register as. Must be Unique.'
            )
            ->addOption(
               'config',
               'c',
               InputOption::VALUE_OPTIONAL,
               'Specify the config file to load api client credentials out of.',
               getenv('HOME') . '/.content-hub-client-cli-config'
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
        $config = ClientConfig::loadFromInput($input);

        $helper = $this->getHelper('question');

        if (!$config->getApiKey()) {
          if ($value = $input->getOption('api_key')) {
            $config->setApiKey($value);
          }
          else {
            $question = new Question('API Key: ');
            $config->setApiKey($helper->ask($input, $output, $question));
          }
        }

        if (!$config->getSecretKey()) {
          if ($value = $input->getOption('secret_key')) {
            $config->setSecretKey($value);
          }
          else {
            $question = new Question('Secret Key: ');
            $config->setSecretKey($helper->ask($input, $output, $question));
          }
        }

        if (!$config->getHostname()) {
          if ($value = $input->getOption('hostname')) {
            $config->setHostname($value);
          }
          else {
            $question = new Question('Hostname: ');
            $config->setHostname($helper->ask($input, $output, $question));
          }
        }

        if ($config->getOrigin()) {
          $output->writeln('<error>Client already registered as ' . $config->getOrigin() . ' ' . $config->getClientName() . '</error>');
          return;
        }

        $client = $config->loadClient();
        $response = $client->register($input->getArgument('client_name'));
        $config->setOrigin($response['uuid'])
               ->setClientName($response['name']);
        $config->save($input->getOption('config'));

        $output->writeln('<info>Client Registered as ' . $config->getOrigin() . ' ' . $config->getClientName() . '</info>');
    }

}
