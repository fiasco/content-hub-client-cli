<?php

namespace AcquiaContentHubCli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use AcquiaContentHubCli\Config\ClientConfig;

class ClientListCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('client:list')
            ->setDescription('List all clients connected to API key.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);

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
