<?php

namespace AcquiaContentHubCli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use AcquiaContentHubCli\Config\ClientConfig;

class WebhookDeleteCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('webhook:delete')
            ->setDescription('Delete a Webhook')
            ->addArgument(
               'uuid',
               InputArgument::REQUIRED,
               'UUID of the webhook to remove'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);

        $uuid = $input->getArgument('uuid');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to delete webhook ' . $uuid . '? (y/N) ', false);
        if (!$helper->ask($input, $output, $question)) {
          return;
        }

        $client = $config->loadClient();
        $client->deleteWebhook($input->getArgument('uuid'));

        $output->writeln("<info>Webhook $uuid has been deleted.</info>");
    }

}
