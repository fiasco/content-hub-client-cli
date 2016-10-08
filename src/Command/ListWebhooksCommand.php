<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Config\ClientConfig;

class ListWebhooksCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('list:webhooks')
            ->setDescription('List all clients connected to API key.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);

        $client = $config->loadClient();
        $settings = $client->getSettings();
        $list = $settings->getWebhooks();

        $table = new Table($output);
        $table
            ->setHeaders(array('UUID', 'URL', 'Version'))
            ->setRows($list)
        ;
        $table->render();
    }

}
