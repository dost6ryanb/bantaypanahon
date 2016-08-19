<?php

$date = new DateTime("now");
$timestamp = $date->getTimestamp() . '000';

$url = 'http://meteopilipinas.gov.ph/api/?key=250d41d4d52cbd2f8cd5b320314ada99&req=doppler&a=iloilo&_=' . $timestamp;

$options = array(
	'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		'method'  => 'GET'/*,
		'content' => http_build_query($data),
		'proxy' => 'tcp://127.0.0.1:8888'*/
	),
);

//Cache-Control: public, max-age=60\r\n
$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);
header('Access-Control-Allow-Origin: *');

if ($result == FALSE) {
	echo '{"success":"false"}';
} else {
	echo $result;
}


?>