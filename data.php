<?php
$dev_id = $_POST['pattern'];
$limit = $_POST['limit'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];
	
if ($dev_id == false) return;
if ($limit == FALSE) $limit = '';
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = '';

$key = md5("$dev_id-$limit-$sdate-$edate");
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');

$response = getCacheAndPrint($key);

if (!$response) {
    $response = getDataFromPredictService($dev_id, $limit, $sdate, $edate);
    if (!$response) {
        echo '{"device":[{"dev_id":'.$dev_id.'}],"count":-1}';
    } else {
        echo $response;
        putCache($key, $response);
    }
}

function getDataFromPredictService($dev_id, $limit, $sdate, $edate) {
    $url = 'http://fmon.asti.dost.gov.ph/api/index.php/device/getData/'; //ASTI API
    $data = array('start' => '0', 'limit' => $limit, 'sDate' => $sdate, 'eDate' => $edate, 'pattern' => $dev_id);
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_PROXY, '192.168.1.100:8888');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function getCacheAndPrint($key) {
    $cache_life = intval(5); // minutes
    if ($cache_life <= 0) return null;

    // fully-qualified filename
    $fqfname = getCacheFileName($key);

    if (file_exists($fqfname)) {
        $filetime = filemtime($fqfname);
        $expirytime = (time() - 60 * $cache_life);
        if ($filetime > $expirytime) {
            // The cache file is fresh.
            //$fresh = file_get_contents($fqfname);
            //$results = json_decode($fresh,true);
            readfile($fqfname);
            return true;// $fresh;
        }
        else {
            unlink($fqfname);
        }
    }

    return null;
}

function putCache($key, $results) {
    //$json = json_encode($results);
    $fqfname = getCacheFileName($key);
    file_put_contents($fqfname, $results, LOCK_EX);
}

function getCacheFileName($key) {
    return "cache/$key.json";
}
?>