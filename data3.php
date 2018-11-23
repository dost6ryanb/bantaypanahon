<?php
$dev_id = $_POST['pattern'];
$limit = $_POST['limit'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];

if ($dev_id == false) return;
if ($limit == FALSE) $limit = '';
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = $sdate;

$key = md5("$dev_id-$limit-$sdate-$edate").'-device';
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');
header('Content-Type: application/json');

$cache = getCacheFqfname($key);
$lockCreated = false;
$lockFile = '';
$cb = function() use ($dev_id, $sdate, $edate) {
    return getFromPhilSensorsService($dev_id, $sdate, $edate);
};

$shutdown = function() use(&$lockCreated, &$lockFile) {
    echo $lockCreated;
    if ($lockCreated) {
        releaseLock($lockFile, $lockCreated);
        //echo "removed $lockFile";
    }
};
register_shutdown_function($shutdown);

if ($cache) { //cache available
    //if (true) { //cache still fresh
    if (!isCacheExpired($cache)) { //cache still fresh
        printCache($key);
    } else { //cache outdated
        if (createLock($key, $lockCreated, $lockFile)) { // create lock to update cache
            renewCache($key, $cb); //cache renewed
            releaseLock($lockFile, $lockCreated);
        }
        //print cache if everthing was good
        printCache($key);
    }
} else { //no-cache
    $response = $cb();
    if (!empty($response)) {
        putCache($key, $response);
        printCache($key);
    } else {
        echo "{'error':'Cannot reach api'}";
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
function printCache($key) {
    $filename = getCacheFileName($key);
    $fp = false;
    $success = false;
    do {
        $fp = fopen($filename, "r");
        if ($fp) {
            $success = true;
        } else { //someone is updating the cache
            usleep ( rand ( 300, 1000));
        }
    } while (!$success);

    while (!feof($fp)) {
        echo fread($fp, 8192);
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
    } else { // The cache file is outdated.
        return true;
    }
}

function renewCache($key, $cb) {
    $success = false;
    $fqfname = getCacheFileName($key);
    $fp = fopen($fqfname, 'c');

    if ($fp && flock($fp, LOCK_EX | LOCK_NB, $wb)) {
        $response = $cb();
        if (!empty($response)) {
            ftruncate($fp, 0) ; // <-- this will erase the contents such as 'w+'
            rewind($fp);
            fwrite($fp, $response);
        }

        flock($fp, LOCK_UN);

    }/* else {
        if ($wb) {
            echo "File locked...";
        } else {
            echo 'Cannot Open file';
        }
    }*/
    fclose($fp);

    return $success;
}

function putCache($key, $results) {
    $fqfname = getCacheFileName($key);
    $fp = fopen($fqfname, "c");
    if (flock($fp, LOCK_EX | LOCK_NB)) {
        ftruncate($fp, 0) ; // <-- this will erase the contents such as 'w+'
        rewind($fp);
        fwrite($fp, $results);
        flock($fp, LOCK_UN);
    }

    fclose($fp);
}

function getCacheFileName($key) {
    return  __DIR__ . DIRECTORY_SEPARATOR . "cache/$key.json";
}

function getLockname($key) {
    return  __DIR__ . DIRECTORY_SEPARATOR . "cache/tmp-$key.lock";
}
function createLock($key, &$status, &$dir) {
    $dir = getLockname($key);
    if (@mkdir($dir, 0700)) {
        $status = true;
        return true;
    }
    return false;
}
function releaseLock($dir, &$status) {
    if (rmdir($dir)) {
        $status = false;
        return true;
    }
    return false;
}
function isExistLock($key) {
    $lockdir = getLockname($key);
    return is_dir($lockdir);
}


