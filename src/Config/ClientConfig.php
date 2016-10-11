<?php

namespace Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Acquia\ContentHubClient\ContentHub;

class ClientConfig
{
  protected $apiKey;
  protected $secretKey;
  protected $sharedSecret;
  protected $hostname;
  protected $origin;
  protected $clientName;
  static $config_directory;

  public function __construct($config = array())
  {
    if (!empty($config['api_key'])) {
      $this->apiKey = $config['api_key'];
    }
    if (!empty($config['secret_key'])) {
      $this->secretKey = $config['secret_key'];
    }
    if (!empty($config['hostname'])) {
      $this->hostname = $config['hostname'];
    }
    if (!empty($config['origin'])) {
      $this->origin = $config['origin'];
    }
    if (!empty($config['client_name'])) {
      $this->clientName = $config['client_name'];
    }
    if (!empty($config['shared_secret'])) {
      $this->sharedSecret = $config['shared_secret'];
    }
  }

  static public function loadFromInput(InputInterface $input, OutputInterface $output)
  {
    $config_file = $input->getOption('client');
    $output->writeln("<info>Loading client $config_file.</info>");
    return self::load($config_file);
  }

  static public function load($config_file)
  {
    $config_location = self::getConfigDirectory() . '/' . $config_file;
    $value = [];
    if (file_exists($config_location)) {
      $value = Yaml::parse(file_get_contents($config_location));
    }
    else {
      throw new \RuntimeException("No client connection found. Cannot interact with Content Hub.");
    }

    return new static($value);
  }

  static public function getConfigDirectory()
  {
    if (empty(self::$config_directory)) {
      self::$config_directory = getenv('HOME') . '/.content-hub/clients';
    }
    if (!is_dir(dirname(self::$config_directory)) && !mkdir(dirname(self::$config_directory))) {
      throw new \RuntimeException("Cannot create config directory: " . dirname(self::$config_directory));
    }
    if (!is_dir(self::$config_directory) && !mkdir(self::$config_directory)) {
      throw new \RuntimeException("Cannot create config directory: " . self::$config_directory);
    }
    return self::$config_directory;
  }

  public function getApiKey()
  {
      return $this->apiKey;
  }

  public function getSecretKey()
  {
      return $this->secretKey;
  }

  public function getHostname()
  {
      return $this->hostname;
  }

  public function getOrigin()
  {
      return $this->origin;
  }

  public function setOrigin($origin)
  {
      $this->origin = $origin;
      return $this;
  }

  public function setHostname($hostname)
  {
      $this->hostname = $hostname;
      return $this;
  }

  public function setApiKey($api_key)
  {
      $this->apiKey = $api_key;
      return $this;
  }

  public function setSecretKey($secret_key)
  {
      $this->secretKey = $secret_key;
      return $this;
  }

  public function getClientName()
  {
      return $this->clientName;
  }

  public function setClientName($client_name)
  {
      $this->clientName = $client_name;
      return $this;
  }

  public function getSharedSecret() {
    if (!empty($this->sharedSecret)) {
      return $this->sharedSecret;
    }
    if (!$client = $this->loadClient()) {
      return '';
    }
    $this->sharedSecret = $client->getSettings()->getSharedSecret();
    return $this->sharedSecret;
  }

  public function loadClient()
  {
    $config['base_url'] = $this->hostname;
    $config['client-user-agent'] = 'Content Hub Client CLI';
    return new ContentHub($this->apiKey, $this->secretKey, $this->origin, $config);
  }

  public function save($config_file = FALSE)
  {
      $config = [
        'client_name' => $this->getClientName(),
        'origin' => $this->getOrigin(),
        'api_key' => $this->getApiKey(),
        'hostname' => $this->getHostname(),
        'secret_key' => $this->getSecretKey(),
        'shared_secret' => $this->getSharedSecret(),
      ];
      $yaml = Yaml::dump($config);

      if (!$config_file) {
        $config_file = $config['client_name'];
      }
      return file_put_contents(self::getConfigDirectory() . '/' . $config_file, $yaml);
  }
}


class ClientException extends \Exception {}

 ?>
