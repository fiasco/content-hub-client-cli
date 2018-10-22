<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\Table;
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
               'UUID of the client to remove, or "all" to delete all clients'
            )
            ->addOption(
              'force',
              'f',
              InputOption::VALUE_OPTIONAL,
              'Use to confirm that all clients should be deleted',
              false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);

        if ($input->getArgument('uuid') == 'all' ) {

          if ($input->getOption('force') === false) {
            $output->writeln("<info>This command is so dangerous that we're asking you to confirm that you know what you are doing by using the '--force' option.</info>");
            return;
          }

          $client = $config->loadClient();
          $settings = $client->getSettings();
          $client_list = $settings->getClients();

          $table = new Table($output);
          $table
            ->setHeaders(array('Client Name', 'UUID'))
            ->setRows($client_list)
          ;
          $table->render();
          $output->writeln("<error>You are about to delete all the clients from this account.</error>");

          $helper = $this->getHelper('question');
          $question = new ConfirmationQuestion('Are you sure you want to continue? You will need to register a new client to continue using this CLI tool. (y/N) ', false);
          if (!$helper->ask($input, $output, $question)) {
            return;
          }

          $client = $config->loadClient();
          foreach ($client_list as $client_definition) {
            $uuid = $client_definition['uuid'];
            $client->delete('settings/client/uuid/' . $uuid);
            $output->writeln("<info>Client $uuid has been deleted.</info>");
          }

        }
        else {
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

}
