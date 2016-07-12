<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Config\ClientConfig;

class EntitiesPurgeCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('entities:purge')
            ->setDescription('Delete all entities in the content hub instance')
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

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to remove all entities? (Y/n) ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $config = ClientConfig::loadFromInput($input);
        $client = $config->loadClient();

        $entities = $client->listEntities();
        $total = $entities['total'];
        $done = 0;
        $complete = '0%';
        foreach ($entities['data'] as $entity) {
          $output->writeln('<info>[' . $complete . ']</info> Deleting entity ' . $entity['uuid'] . ' of type ' . $entity['type']);

          try {
            $config->setOrigin($entity['origin'])
                 ->loadClient()
                 ->deleteEntity($entity['uuid']);
          }
          catch (\GuzzleHttp\Exception\ClientException $e) {
               $output->writeln('<error>Deleting entity ' . $entity['uuid'] . ' failed. Try another existing API key.</error>');
          }

          $done++;
          $complete = round($done / $total * 100, 2) . '%';
        }
    }

}
