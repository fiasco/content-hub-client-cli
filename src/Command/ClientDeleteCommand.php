<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Config\ClientConfig;

class ClientDeleteCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('client:delete')
            ->setDescription('Delete a Client by UUID')
            ->addArgument(
               'uuid',
               InputArgument::REQUIRED,
               'UUID of the client to remove'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);

        $uuid = $input->getArgument('uuid');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to remove client ' . $uuid . '? (y/N) ', false);
        if (!$helper->ask($input, $output, $question)) {
          return;
        }

        $client = $config->loadClient();
        $client->delete('settings/client/uuid/' . $uuid);
        $output->writeln("<info>Client $uuid has been deleted.</info>");
    }

}
