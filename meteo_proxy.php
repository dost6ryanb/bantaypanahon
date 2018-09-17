<?php
header('Content-Type: application/json');
$rq = $_GET['rq'];


switch ($rq) {
    case "ph-doppler":
        echo getdoppler();
        break;
    case "iloilo-doppler":
        echo getdoppleriloilo();
        break;
    case 'sat-himawari':
        echo getsat();
        break;
    case 'cyclone-track':
        echo getcytrck();
        break;
    default:
        echo json_encode(array("request" => "unknown request"));

}

function getdoppler()
{
    $host = "https://v2-cloud.meteopilipinas.gov.ph";
    $url = "$host/api/radar-timeline?theme=null";
    $data = array('request' => 'rd.mosaic-cmax-reflectivity');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Origin: $host",
        'X-Requested-With: XMLHttpRequest',
        "Referer: $host/",
    ));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return json_encode(array("success" => "false", "http_code" => $http_code, "curl_error" => $error));
    }
}

function getdoppleriloilo()
{
    $host = "https://v2-cloud.meteopilipinas.gov.ph";
    $url = "$host/api/radar-timeline?theme=lightmap";
    $data = array('request' => 'rd.iloilo-cappi-reflectivity');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //'Host: v2.meteopilipinas.gov.ph',
        //'Connection: keep-alive',
        //'Content-Length: 39',
        //'Accept: */*',
        "Origin: $host",
        'X-Requested-With: XMLHttpRequest',
        //'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
        //'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        "Referer: $host/",
        //'Accept-Encoding: gzip, deflate, br',
        //'Accept-Language: en-US,en;q=0.9',
        //'Cookie: _ga=GA1.3.197305203.1516941407; _gid=GA1.3.1604690445.1535098321; laravel_session=eyJpdiI6IlBVSVVqeVhnWEFqaElZUWlENXMrTmc9PSIsInZhbHVlIjoiVWg1MTF1RjhBTVBvMUZRdWxnd3pNSk5JeEdJN1wvV0kxUXZHRkg3RVJmdFhEbGtvU3VoOCtUS0RFTFwvNFhYaHB1S0dScVBMN1k0b2I3K1A3Y3dIMnFzZz09IiwibWFjIjoiZjE1ZjQyNDY1ZGNiZWRiNjU5YjY0OTMyNWI1NGQzNWM5NGQ0NmQxNzdjY2VhOGI2NzU1Mzg2OWZjNTFmMzhhMSJ9'
    ));
    //curl_setopt($ch, CURLOPT_REFERER, $referer);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return json_encode(array("success" => "false", "http_code" => $http_code, "curl_error" => $error));
    }
}

function getsat()
{
    $host = "https://v2-cloud.meteopilipinas.gov.ph";
    $url = "$host/api/satellite?theme=null";
    $data = array('request' => 'sat.himawari-ir1');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Origin: $host",
        'X-Requested-With: XMLHttpRequest',
        "Referer: $host/",
    ));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return null;
    }
}

function getcytrck()
{
    $host = "https://v2-cloud.meteopilipinas.gov.ph";
    $url = "$host/api/cyclone-track?theme=null";
    $data = array('request' => '36hourly');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //$referer = 'https://v2.meteopilipinas.gov.ph/';
    //curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Origin: $host",
        'X-Requested-With: XMLHttpRequest',
        "Referer: $host/",
    ));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return json_encode(array("success" => "false", "http_code" => $http_code, "curl_error" => $error));
    }
}