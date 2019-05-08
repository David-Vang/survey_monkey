<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/request-header.php');
$data_string = array(
  'name'              => 'Webhook',
  'event_type'        => 'response_completed',
  'object_type'       => 'survey',
  'object_ids'        => array('168028862', '169760298', '169766613', '169766613', '169771232', '169775243'),
  'subscription_url'  => 'http://domain.addy/listener.php'
);

$url = 'https://api.surveymonkey.com/v3/webhooks';
$data_string = json_encode($data_string);

$ch  = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
$result = curl_exec($ch);
curl_close($ch);

$fp = file_put_contents('webhook.log', print_r($result, true));
die;

?>
