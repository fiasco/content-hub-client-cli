<?php

use HttpFoundation\WebhookRequest;

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('UTC');

// if (ini_get('auto_prepend_file') && !in_array(realpath(ini_get('auto_prepend_file')), get_included_files(), true)) {
//     require ini_get('auto_prepend_file');
// }
//
// if (is_file($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$_SERVER['SCRIPT_NAME'])) {
//     return false;
// }
//
// $_SERVER = array_merge($_SERVER, $_ENV);
// $_SERVER['SCRIPT_FILENAME'] = __FILE__;
//
// // Since we are rewriting to app.php, adjust SCRIPT_NAME and PHP_SELF accordingly
// $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = DIRECTORY_SEPARATOR . 'webhook_log_router.php';

$request = WebhookRequest::createFromGlobals();
$headers = array_map('current', $request->headers->all());

$logger = function ($message) use ($request, $headers) {
  // "%h %l %u %t \"%r\" %>s %b"
  $message = implode('|', [
    'date' => date('Y-m-d H:i:s'),
    'method' => $request->getMethod(),
    'path' => $request->getPathInfo(),
    'protocol' => strtoupper($request->getScheme()),
    'status' => http_response_code(),
    'user-agent' => $headers['user-agent'],
    'message' => $message
  ]);
  error_log($message . "\n", 3, dirname(__DIR__) . '/webhook.log');
};

try {

  // $logger('Headers: ' . print_r(array_map('current', $request->headers->all()), 1));
  // $logger('Server: ' . print_r($_SERVER, 1));

  $webhook = $request->getPayload();

  if (!$request->validateSignature($webhook['status'])) {
    throw new \Exception("Signature invalid.");
  }

  if ($webhook['status'] == "pending") {
    $request->registerResponse();
    $logger("Webhook registration request recieved and processed.");
  }
  else {
    $logger(strtr('uuid|crud|status|message', $webhook));
  }
}
catch (\Exception $e)
{
  $logger($e->getMessage());
}
 ?>
