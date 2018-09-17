<?php
function getdoppler() {
    $url = 'https://v2-cloud.meteopilipinas.gov.ph/api/radar-timeline?theme=null';
    $referer = 'https://v2-cloud.meteopilipinas.gov.ph/';
    $data = array('request' => 'rd.iloilo-cmax-reflectivity');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return null;
    }
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$result = getdoppler();

if ($result == FALSE) {
	echo '{"success":"false"}';
} else {
	echo $result;
}
?>