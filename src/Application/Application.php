<?php

namespace AcquiaContentHubCli\Application;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

class Application extends BaseApplication
{

  /**
   * Gets the name of the command based on input.
   *
   * @param InputInterface $input The input interface
   *
   * @return string The command name
   */
  // protected function getCommandName(InputInterface $input)
  // {
  //     $input->bind($this->getDefaultInputDefinition());
  //     $args = $input->getArguments();
  //     return reset($args);
  // }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultInputDefinition()
  {
      $inputDefinition = parent::getDefaultInputDefinition();
      $inputDefinition->addOptions([
        new InputOption(
          'client',
          'c',
          InputOption::VALUE_OPTIONAL,
          'Specify the api client credentials to interact with content hub as.',
          'default'
        ),
      ]);
     return $inputDefinition;
  }
}
 ?>
