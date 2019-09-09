<?php
$dev_id = $_POST['pattern'];
$limit = $_POST['limit'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];

if ($dev_id == false) return;
if ($limit == FALSE) $limit = '';
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = $sdate;

$key = md5("$dev_id-$limit-$sdate-$edate") . '-device';
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');
header('Content-Type: application/json');

$cache = getCacheFqfname($key);
$cb = function () use ($dev_id, $sdate, $edate) {
    return getDataFromWeatherAstiService($dev_id, $sdate, $edate);
};

function shutdown($lockDir)
{
    releaseLock($lockDir);
}

$lockDir = getLockname($key);

if ($cache) { //cache available
    //if (true) { //cache still fresh
    if (!isCacheExpired($cache)) { //cache still fresh
        printCache($key, $lockDir);
    } else { //cache outdated
        renewCache($key, $cb, $lockDir);
        //print cache if everthing was good
        printCache($key, $lockDir);
    }
} else { //no-cache
    renewCache($key, $cb, $lockDir);
    printCache($key, $lockDir);
}


//@return
// on success - returns response (http 200)
// on failure - returns null (http code is not 200)
function getDataFromPredictService($dev_id, $limit, $sdate, $edate)
{
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

function getDataFromWeatherAstiService($dev_id, $sdate, $edate)
{
    $username = 'dostregion06';
    $password = 'dost.reg06[1117]';
    $url = 'http://weather.asti.dost.gov.ph/web-api/index.php/api/data/' . $dev_id . '/from/' . $sdate . '/to/' . $edate; //ASTI API
    //$data = array('start' => '0', 'limit' => $limit, 'sDate' => $sdate, 'eDate' => $edate, 'pattern' => $dev_id);
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_PROXY, 'http://192.168.1.59:8888');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_PROXY, "http://192.168.1.242:8888");
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return null;
    }
}

function getFromPhilSensorsService($dev_id, $sdate, $edate)
{
    $params = array(
        "r" => "site/get-by-duration",
        "stationid" => $dev_id,
        "from" => $sdate,
        "to" => $edate
    );
    $url = 'http://philsensors.asti.dost.gov.ph/index.php?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_PROXY, "http://192.168.1.62:8888");
    curl_setopt($ch, CURLOPT_USERAGENT, 'BPDOSTVI992019');

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $chart = new \stdClass();

        $response_chart = json_decode($response)->{'chrt_'};
        $columns = ['Datetime Read'];

        foreach ($response_chart as $key => $value) {
            $columns[] = $key;
            foreach ($value as $key2 => $value2) {
                $chart->{$value2[0]}[$key] = strval($value2[1]);
            }

        }

        $data = [];
        foreach ($chart as $okey => $oval) {
            $timestamp = intval($okey) / 1000;
            //$datetimeread = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            $dt = new DateTime('@' . $timestamp);
            $datetimeread = $dt->format('Y-m-d H:i:s');
            $a = array("Datetime Read" => $datetimeread);
            $data[] = array_merge($a, $oval);
        }

        return json_encode(array("0" => array("station_id" => $dev_id), "Columns" => $columns, "Data" => $data));

    } else {
        return null;
    }
}

//@return
// on success - file is available, returns cache filename
// on failure - no cache, returns null
function getCacheFqfName($key)
{
    $fqfname = getCacheFileName($key);
    if (file_exists($fqfname)) {
        return $fqfname;
    } else {
        return null;
    }
}

//@params
// filename = fully qualified name
function printCache($key, $lockDir)
{
    $filename = getCacheFileName($key);
    $fp = false;

    $success = false;
    do {
        if (isLockExist($lockDir)) {
            if (isLockExpired($lockDir)) {
                releaseLock($lockDir);
            }
            usleep(rand(300, 1000));
        } else {
            $success = true;
        }
    } while (!$success);

    $success = false;
    do {
        $fp = fopen($filename, "r");
        if ($fp) {
            $success = true;
        } else { //someone is updating the cache
            usleep(rand(300, 1000));
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
function isCacheExpired($filename, $life = 5)
{
    $filetime = filemtime($filename);
    $cache_life = intval($life); // minutes
    $expirytime = (time() - 60 * $cache_life);
    if ($filetime > $expirytime) { // The cache file is fresh.
        return false;
    } else { // The cache file is outdated.
        return true;
    }
}

function putCache($key, $cb, $lockDir)
{
    $fqfname = getCacheFileName($key);
    $fp = false;

    if (isLockExpired($lockDir)) {
        releaseLock($lockDir);
    }

    $success = false;
    do {
        $fp = fopen($fqfname, "c");
        if ($fp) {
            $success = true;
        } else { //someone is updating the cache
            usleep(rand(300, 1000));
        }
    } while (!$success);

    if ($fp && flock($fp, LOCK_EX)) {
        do {
            $response = $cb();
            if ($response != null) {
                ftruncate($fp, 0); // <-- this will erase the contents such as 'w+'
                rewind($fp);
                fwrite($fp, $response);
                $success = true;
            } else {
                usleep(rand(300, 1000));
            }
        } while (!$success);

        flock($fp, LOCK_UN);
    }

    fclose($fp);

    return $success;
}

function renewCache($key, $cb, $lockDir)
{
    if (createLock($lockDir)) { // create lock to update cache
        putCache($key, $cb, $lockDir); //cache renewed
        releaseLock($lockDir);
    } else {
        return false;
    }

    return true;
}

function getCacheFileName($key)
{
    return __DIR__ . DIRECTORY_SEPARATOR . "cache/$key.json";
}

function getLockname($key)
{
    return __DIR__ . DIRECTORY_SEPARATOR . "cache/tmp-$key.lock";
}

function createLock($lockDir)
{
    if (@mkdir($lockDir, 0700)) {
        register_shutdown_function('shutdown', $lockDir);
        return true;
    }
    return false;
}

function releaseLock($lockDir)
{
    clearstatcache();
    if (is_dir($lockDir) && rmdir($lockDir)) {
        return true;
    }
    return false;
}

function isLockExist($lockDir)
{
    clearstatcache();
    return is_dir($lockDir);
}

function isLockExpired($lockDir, $life = 1)
{
    if (isLockExist($lockDir)) {
        $filetime = filemtime($lockDir);
        $cache_life = intval($life); // minutes
        $expirytime = (time() - 60 * $cache_life);
        if ($filetime > $expirytime) { // The lock file is fresh.
            return false;
        }
    }

    return true;
}
