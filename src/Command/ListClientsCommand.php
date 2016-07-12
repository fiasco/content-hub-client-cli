<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Config\ClientConfig;

class ListClientsCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('list:clients')
            ->setDescription('List all clients connected to API key.')
            ->addOption(
               'config',
               'c',
               InputOption::VALUE_OPTIONAL,
               'Specify the config file to load api client credentials out of.',
               getenv('HOME') . '/.content-hub-client-cli-config'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input);

        $client = $config->loadClient();
        $settings = $client->getSettings();
        $list = $settings->getClients();

        $table = new Table($output);
        $table
            ->setHeaders(array('Client Name', 'UUID'))
            ->setRows($list)
        ;
        $table->render();
    }

}