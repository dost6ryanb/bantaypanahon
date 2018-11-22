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

$key = "$hash-$type-$sdate-$edate";
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');
header('Content-Type: application/json');

$cache = getCacheFqfname($key);

if ($cache) { //cache available
    if (isCacheExpired($cache)) { // outdated cache
        $response = getBulkData($dev_ids, $sdate, $edate);
        if (!empty($response)) {
            putCache($key, $response);
            unset($response);
            printCache($key);
        } else {
            printCache($key);
        }
    } else {
        printCache($key);
    }
} else { //no-cache
    $response = getBulkData($dev_ids, $sdate, $edate);
    if (!empty($response)) {
        putCache($key, $response);
        printCache($key);
    } else {
        echo '{"dev_ids":[{"dev_id":'.$dev_ids.'}],"count":0}';
    }
}

function getBulkData($dev_ids, $sdate, $edate) {
    $len = count($dev_ids);
    $response = '[';
    foreach($dev_ids as $i => $dev_id) {
        $tmp = getFromPhilSensorsService($dev_id, $sdate, $edate);
        if ($tmp) {
            $response .= $tmp;
            if ($i != $len - 1)  $response .= ', ';
        }
    }

    return $response . ']' ;

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
function getCacheFqfname($key) {
    $fqfname = getCacheFileName($key);
    if (file_exists($fqfname)) {
        return $fqfname;
    } else {
        return null;
    }
}

//@params
// filename = fully qualified name
function printCache($key) {
    $filename = getCacheFileName($key);
    $fp = fopen($filename, "r");
    if (flock($fp, LOCK_SH)) {
        //$content = '';
        clearstatcache($filename);
        //$ = fread($fp, filesize($filename));
        while (!feof($fp)) {
            echo fread($fp, 8192);
        }
        flock($fp, LOCK_UN);
        //if (!empty($content)) echo $content;
    }
    fclose($fp);
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
    } else { // The cache file is outdated. or does not exists
        return true;
    }
}

function putCache($key, $results) {
    $fqfname = getCacheFileName($key);
    $fp = fopen($fqfname, "c");
    if (flock($fp, LOCK_EX | LOCK_NB)) {
        //sleep(10);
        ftruncate($fp, 0) ; // <-- this will erase the contents such as 'w+'
        fwrite($fp, $results);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

function getCacheFileName($key) {
    return "cache/$key.json";
}
