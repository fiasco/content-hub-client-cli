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

        // Flatten the filters array before rendering the table.
        array_walk($list, function(&$value, &$key) {
          if (is_array($value['filters'])) {
            $value['filters'] = implode(",", $value['filters']);
          }
        });

        $table = new Table($output);
        $table
            ->setHeaders(array('UUID', 'URL', 'Version', 'disable retries', 'Filters'))
            ->setRows($list)
        ;
        $table->render();
    }

}
