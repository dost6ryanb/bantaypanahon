<?php
$dev_ids = $_POST['dev_ids'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];
$type = $_POST['type'];


if (!is_array($dev_ids) || count($dev_ids) == 0 ) return;
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = $sdate;
if ($type == FALSE) $type = 0;
$hash = md5(serialize($dev_ids));

$key = md5("$hash-$type-$sdate-$edate");
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');
header('Content-Type: application/json');

$cache = getCache($key);

if ($cache) { //cache available
    if (isCacheExpired($cache)) { // outdated cache
        $response = getBulkData($dev_ids, $sdate, $edate);
        if (is_array($response) || count($response) > 0 ) {
            $response_json = json_encode($response);
            echo $response;
            putCache($key, $response_json);
        } else {
            printCache($cache);
        }
    } else {
        printCache($cache);
    }
} else { //no-cache
    $response = getBulkData($dev_ids, $sdate, $edate);
    if (is_array($response) || count($response) > 0 ) {
        $response_json = json_encode($response);
        echo $response_json;
        putCache($key, $response_json);
    } else {
        echo '{"dev_ids":[{"dev_id":'.$dev_ids.'}],"count":0}';
    }
}

function getBulkData($dev_ids, $sdate, $edate) {
    $response = [];
    foreach($dev_ids as $dev_id) {
        $response[] = json_decode(getFromPhilSensorsService($dev_id, $sdate, $edate));
    }

    return $response;

}
function getFromPhilSensorsService($dev_id, $sdate, $edate) {
    $url = 'http://philsensors.asti.dost.gov.ph/php/dataduration.php?stationid=' . $dev_id . '&from=' . $sdate .'&to='. $edate;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return null;
    }
}

//@return
// on success - file is available, returns cache filename
// on failure - no cache, returns null
function getCache($key) {
    $fqfname = getCacheFileName($key);
    if (file_exists($fqfname)) {
        return $fqfname;
    } else {
        return null;
    }
}

//@params
// filename = fully qualified name
function printCache($filename) {
    readfile($filename);
}

//@params
// filename - fully qualified name
// cache_life - cache life in minutes
//@return
// true - meanings file age is older than cache life
// false - not true duh!
function isCacheExpired($filename, $life = 5) {
    $filetime = filemtime($filename);
    $cache_life = intval($life); // minutes
    $expirytime = (time() - 60 * $cache_life);
    if ($filetime > $expirytime) { // The cache file is fresh.
        return false;
    } else { // The cache file is outdated.
        return true;
    }
}

function putCache($key, $results) {
   // $json = $results;
    $fqfname = getCacheFileName($key);
    file_put_contents($fqfname, $results, LOCK_EX);
}

function getCacheFileName($key) {
    return "cache/$key.json";
}
