<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Config\ClientConfig;

class WebhookAddCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('webhook:add')
            ->setDescription('Add a webhook')
            ->addOption(
               'config',
               'c',
               InputOption::VALUE_OPTIONAL,
               'Specify the config file to load api client credentials out of.',
               getenv('HOME') . '/.content-hub-client-cli-config'
            )
            ->addArgument(
               'url',
               InputArgument::REQUIRED,
               'The webhook URL to notify on Content Hub notifications.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input);

        $client = $config->loadClient();
        $client->addWebhook($input->getArgument('url'));

        $output->writeln('<info>Webhook has been added</info>');
    }

}
