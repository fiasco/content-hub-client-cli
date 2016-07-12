<?php

namespace Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;
use Acquia\ContentHubClient\ContentHub;

class ClientConfig
{
  protected $apiKey;
  protected $secretKey;
  protected $hostname;
  protected $origin;
  protected $clientName;

  public function __construct($config)
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
  }

  static public function loadFromInput(InputInterface $input)
  {
    $config_file = $input->getOption('config');
    $value = [];
    if (file_exists($config_file)) {
      $value = Yaml::parse(file_get_contents($config_file));
    }

    return new static($value);
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
      ];
      $yaml = Yaml::dump($config);

      if (!$config_file) {
        $config_file = getenv('HOME') . '/.content-hub-client-cli-config';
      }
      return file_put_contents($config_file, $yaml);
  }
}


class ClientException extends \Exception {}

 ?>
