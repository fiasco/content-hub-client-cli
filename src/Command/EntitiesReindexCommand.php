<?php

namespace AcquiaContentHubCli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use AcquiaContentHubCli\Config\ClientConfig;

class EntitiesReindexCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('entities:reindex')
            ->setDescription('Sets the backend to drop schema and reindex content.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);
        $client = $config->loadClient();
        $response = $client->post('reindex')->json();

        if (!empty($response['success'])) {
          $output->writeln('<info>Reindex has been initiated.</info>');
        }
        else {
          $output->writeln('<error>Reindex failed.</error>');
        }
    }

}
