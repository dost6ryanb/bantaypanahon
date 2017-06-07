<?php
$dev_id = $_POST['pattern'];
$limit = $_POST['limit'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];
	
if ($dev_id == false) return;
if ($limit == FALSE) $limit = '';
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = '';

//$url = 'http://fmon.asti.dost.gov.ph/weather/home/index.php/device/getData/';
$url = 'http://fmon.asti.dost.gov.ph/api/index.php/device/getData/';
$data = array('start' => '0', 'limit' => $limit, 'sDate' => $sdate, 'eDate' => $edate, 'pattern' => $dev_id);

$options = array(
	'http' => array(
		'header'  => "Connection: close\r\nContent-type: application/x-www-form-urlencoded\r\n",
		'method'  => 'POST',
		'content' => http_build_query($data)/*,
		'proxy' => 'tcp://192.168.1.146:8888'*/
	),
);

//Cache-Control: public, max-age=60\r\n
$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);
header('Access-Control-Allow-Origin: *');

if ($result == FALSE) {
	echo '{"device":[{"dev_id":'.$dev_id.'}],"count":-1}';
} else {
	echo $result;
}


?>