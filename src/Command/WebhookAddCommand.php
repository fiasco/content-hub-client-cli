<?php

namespace AcquiaContentHubCli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AcquiaContentHubCli\Config\ClientConfig;

class WebhookAddCommand extends Command
{
  protected function configure()
    {
        $this
            ->setName('webhook:add')
            ->setDescription('Add a webhook')
            ->addArgument(
               'url',
               InputArgument::REQUIRED,
               'The webhook URL to notify on Content Hub notifications.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = ClientConfig::loadFromInput($input, $output);

        $client = $config->loadClient();
        $client->addWebhook($input->getArgument('url'));

        $output->writeln('<info>Webhook has been added</info>');
    }

}
