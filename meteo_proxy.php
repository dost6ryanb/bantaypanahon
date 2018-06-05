<?php
$rq = $_GET['rq'];


switch ($rq) {
    case "ph-doppler":
        echo getdoppler();
        break;
    case 'sat-himawari':
        echo getsat();
        break;
    case 'cyclone-track':
        echo getcytrck();
        break;
    default:
        echo json_encode(array("request"=>"unknown request"));

}

function getdoppler() {
    $url = 'https://v2.meteopilipinas.gov.ph/api/radar-timeline?theme=null';
    $referer = 'https://v2.meteopilipinas.gov.ph/';
    $data = array('request' => 'rd.mosaic-cmax-reflectivity');
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

function getsat() {
    $url = 'https://v2.meteopilipinas.gov.ph/api/satellite?theme=null';
    $referer = 'https://v2.meteopilipinas.gov.ph/';
    $data = array('request' => 'sat.himawari-ir1');
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

function getcytrck() {
    $url = 'https://v2.meteopilipinas.gov.ph/api/cyclone-track?theme=null';
    $referer = 'https://v2.meteopilipinas.gov.ph/';
    $data = array('request' => '36hourly');
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