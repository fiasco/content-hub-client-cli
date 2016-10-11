<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Config\ClientConfig;

class ListConnectionsCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('list:connections')
            ->setDescription('List all available registered client connections.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = [];
        foreach (ClientConfig::listConnections() as $origin => $client_name) {
          $list[] = [$origin, $client_name];
        }

        $table = new Table($output);
        $table
            ->setHeaders(array('Origin', 'Client name'))
            ->setRows($list)
        ;
        $table->render();
    }

}
