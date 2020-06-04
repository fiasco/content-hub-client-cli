<?php

namespace AcquiaContentHubCli\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;
use AcquiaContentHubCli\Config\ClientConfig;
use Acquia\ContentHubClient\ResponseSigner;

class WebhookRequest extends Request
{

  protected $client;

  public static function createFromGlobals()
  {
      $request = parent::createFromGlobals();
      $client_name = substr($request->getPathInfo(), 1);

      if (empty($client_name)) {
        $client_name = 'default';
      }
      $client = ClientConfig::load($client_name);
      $request->setClient($client);
      return $request;
  }

  protected function setClient(ClientConfig $client)
  {
    $this->client = $client;
    return $this;
  }

  protected function getClient()
  {
    return $this->client;
  }

  public function validateSignature($status)
  {
      $headers = array_map('current', $this->headers->all());

      if (empty($headers['date'])) {
        throw new WebhookRequestException("The HTTP header 'Date' is missing.");
      }

      $request_timestamp = strtotime($headers['date']);
      $timestamp = time();

      // Webhooks out of sync by 60 seconds or more are considered invalid.
      if (abs($request_timestamp - $timestamp) > 60) {
        throw new WebhookRequestException("Request recieved was delayed by more than 60 seconds.");
      }

      // If the headers are not given, then the request is probably not coming from
      // the Content Hub. Replace them for empty string to fail validation.
      $message = array(
        $this->getMethod(),
        md5($this->getContent()),
        $headers['content-type'],
        $headers['date'],
        '',
        $this->getPathInfo(),
      );

      $message = implode("\n", $message);

      // This is for when status == 'pending';
      if ($status == 'pending') {
        $hmac = hash_hmac('sha256', $message, $this->getClient()->getSecretKey(), TRUE);
        $signature = base64_encode($hmac);
        $authorization = "Acquia " . $this->getClient()->getApiKey() . ":" . $signature;
        return ($headers['authorization'] == $authorization);
      }
      else {
        $hmac = hash_hmac('sha256', $message, $this->getClient()->getSharedSecret(), TRUE);
        $signature = base64_encode($hmac);
        $authorization = 'Acquia Webhook:' . $signature;
        return ($headers['authorization'] == $authorization);
      }
      return FALSE;
  }

  public function getPayload() {
    $json = $this->getContent();
    return json_decode($json, TRUE);
  }

  public function registerResponse() {
    $response = new ResponseSigner($this->getClient()->getApiKey(), $this->getClient()->getSecretKey());
    $response->setContent('{}');
    $response->setResource('');
    $response->setStatusCode(ResponseSigner::HTTP_OK);
    $response->signWithCustomHeaders(FALSE);
    $response->signResponse();
    $response->send();
  }
}

class WebhookRequestException extends \Exception {}
 ?>
