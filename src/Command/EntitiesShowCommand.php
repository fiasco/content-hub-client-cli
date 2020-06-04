<?php

namespace AcquiaContentHubCli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use AcquiaContentHubCli\Config\ClientConfig;
use Symfony\Component\Yaml\Dumper;

class EntitiesShowCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('entity:show')
            ->setDescription('Show a specific entity by UUID')
            ->addArgument(
               'uuid',
               InputArgument::REQUIRED,
               'UUID of the entity to remove'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);
        $client = $config->loadClient();
        $uuid = $input->getArgument('uuid');
        $entity = $client->getResponseJson($client->get("entities/$uuid"));

        // This just helps when generating diffs for example.
        if (!empty($entity['attributes']) && is_array($entity['attributes'])) {
          ksort($entity['attributes']);
        }

        $dumper = new Dumper();
        $output->writeln($dumper->dump($entity['data']['data'], 10, 2));
    }

}
