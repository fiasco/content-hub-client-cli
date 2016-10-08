<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Config\ClientConfig;

class EntitiesListCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('entities:list')
            ->setDescription('List entities')
            ->addOption(
               'limit',
               'l',
               InputOption::VALUE_OPTIONAL,
               'Limit the number of results',
               1000
            )
            ->addOption(
               'start',
               'o',
               InputOption::VALUE_OPTIONAL,
               'Offset to start from.',
               0
            )
            ->addOption(
               'type',
               't',
               InputOption::VALUE_OPTIONAL,
               'The type of entity to retrieve',
               ''
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);

        $options = [
          'limit' => $input->getOption('limit'),
          'start' => $input->getOption('start'),
          'fields' => 'title',
          'type' => $input->getOption('type'),
        ];

        $client = $config->loadClient();
        $entities = $client->listEntities(array_filter($options));

        $rows = [];
        foreach ($entities['data'] as $entity) {
          $row = $entity;
          unset($row['attributes']);
          if (!empty($entity['attributes']['title'])) {
            $row['title'] = array_shift($entity['attributes']['title']);
          }
          $rows[] = $row;
        }

        $table = new Table($output);
        $table
            ->setHeaders(array('UUID', 'Origin', 'Modified', 'Type', 'Title'))
            ->setRows($rows)
        ;
        $table->render();

        $output->writeln('<info> Total records: ' . $entities['total'] . '</info>');
    }

}
