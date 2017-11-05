<?php
$url = 'https://v2.meteopilipinas.gov.ph/api/radar-timeline?theme=null';
//$data = array('request' => 'rd.iloilo-cappi-reflectivity');
$data = array('request' => 'rd.iloilo-cmax-reflectivity');
$options = array(
	'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		'method'  => 'POST',
		'content' => http_build_query($data)/*,
		'proxy' => 'tcp://192.168.1.174:8888'*/
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