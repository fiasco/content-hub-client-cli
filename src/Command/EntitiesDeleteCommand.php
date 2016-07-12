<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Config\ClientConfig;

class EntitiesDeleteCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('entity:delete')
            ->setDescription('Delete a specific entity by UUID')
            ->addOption(
               'config',
               'c',
               InputOption::VALUE_OPTIONAL,
               'Specify the config file to load api client credentials out of.',
               getenv('HOME') . '/.content-hub-client-cli-config'
            )
            ->addArgument(
               'uuid',
               InputArgument::REQUIRED,
               'UUID of the entity to remove'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input);
        $client = $config->loadClient();

        $entity = $client->readEntity($input->getArgument('uuid'));

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to remove ' . $entity->getType() . ' ' . $input->getArgument('uuid') . '? (Y/n) ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $config->setOrigin($entity->getOrigin());

        // Masqurade as the origin client.
        $client = $config->loadClient();
        $response = $client->deleteEntity($input->getArgument('uuid'))->json();

        if (!empty($response['success'])) {
          $output->writeln('<info> Entity has been successfully deleted.</info>');
        }
        else {
          $output->writeln('<error>Failed to delete entity.</error>');
        }
    }

}
