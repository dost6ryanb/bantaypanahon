<?php
$dev_id = $_POST['pattern'];
$limit = $_POST['limit'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];

if ($dev_id == false) return;
if ($limit == FALSE) $limit = '';
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = $sdate;

$key = md5("$dev_id-$limit-$sdate-$edate");
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');
header('Content-Type: application/json');

$cache = getCacheFqfname($key);

if ($cache) { //cache available
    if (isCacheExpired($cache)) { // outdated cache
        $response = getFromPhilSensorsService($dev_id, $sdate, $edate);
        if ($response) {
            echo $response;
            putCache($key, $response);
        } else {
            printCache($cache);
        }
    } else {
        printCache($cache);
    }
} else { //no-cache
    $response = getFromPhilSensorsService($dev_id, $sdate, $edate);
    if ($response) {
        echo $response;
        putCache($key, $response);
    } else {
        echo '{"device":[{"dev_id":'.$dev_id.'}],"count":-1}';
    }
}


//@return
// on success - returns response (http 200)
// on failure - returns null (http code is not 200)
function getDataFromPredictService($dev_id, $limit, $sdate, $edate) {
    $url = 'http://fmon.asti.dost.gov.ph/api/index.php/device/getData/'; //ASTI API
    $data = array('start' => '0', 'limit' => $limit, 'sDate' => $sdate, 'eDate' => $edate, 'pattern' => $dev_id);
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_PROXY, '192.168.1.200:8888');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return null;
    }

}

function getDataFromWeatherAstiService($dev_id, $limit, $sdate, $edate) {
    $username = 'dostregion06';
    $password = 'dost.reg06[1117]';
    $url = 'http://weather.asti.dost.gov.ph/web-api/index.php/api/data/' . $dev_id . '/from/' . $sdate . '/to/' . $edate; //ASTI API
    //$data = array('start' => '0', 'limit' => $limit, 'sDate' => $sdate, 'eDate' => $edate, 'pattern' => $dev_id);
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_PROXY, 'http://192.168.1.239:8888');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);
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
function getCacheFqfName($key) {
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
    //$json = json_encode($results);
    $fqfname = getCacheFileName($key);
    file_put_contents($fqfname, $results, LOCK_EX);
}

function getCacheFileName($key) {
    return "cache/$key.json";
}
