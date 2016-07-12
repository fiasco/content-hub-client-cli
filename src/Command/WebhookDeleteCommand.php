<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Config\ClientConfig;

class WebhookDeleteCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('webhook:delete')
            ->setDescription('Delete a Webhook')
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
               'UUID of the webhook to remove'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input);

        $client = $config->loadClient();
        $client->deleteWebhook($input->getArgument('uuid'));

        $output->writeln('<info>Webhook has been removed</info>');
    }

}
