<?php
$dev_ids = $_POST['dev_ids'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];
$type = $_POST['type'];

if (!is_array($dev_ids) || count($dev_ids) == 0) return;
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = $sdate;
if ($type == FALSE) $type = 0;
$hash = md5(serialize($dev_ids));

$key = "$hash-$type-$sdate-$edate";
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');
header('Content-Type: application/json');

$cache = getCacheFqfname($key);
$cb = function () use ($dev_ids, $sdate, $edate) {
    return getBulkData($dev_ids, $sdate, $edate);
};
//trigger_error("Oops!", E_USER_ERROR);

function shutdown($lockDir)
{
    releaseLock($lockDir);
}

$lockDir = getLockname($key);

set_time_limit(60);

if ($cache) { //cache available
//if (false) { //debug
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

function getBulkData($dev_ids, $sdate, $edate)
{
    $len = count($dev_ids);
    $count = 0;
    $response = '[';
    foreach ($dev_ids as $i => $dev_id) {
        $tmp = getFromPhilSensorsService($dev_id, $sdate, $edate);
        if ($tmp) {
            $count++;
            $response .= $tmp;
            if ($i != $len - 1) $response .= ', ';
        }
    }

    if ($count > 0) {
        return $response . ']';
    } else {
        return null;
    }

}

function getFromPhilSensorsService($dev_id, $sdate, $edate)
{
    $url = 'http://philsensors.asti.dost.gov.ph/php/dataduration.php?stationid=' . $dev_id . '&from=' . $sdate . '&to=' . $edate;
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
function getCacheFqfname($key)
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
        }else {
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
function isCacheExpired($filepath, $life = 5)
{
    $filetime = filemtime($filepath);
    $cache_life = intval($life); // minutes
    $expirytime = (time() - 60 * $cache_life);
    if ($filetime > $expirytime) { // The cache file is fresh.
        return false;
    } else { // The cache file is outdated. or does not exists
        return true;
    }
}

function putCache($key, $cb, $lockDir)
{
    $fqfname = getCacheFileName($key);
    $fp = false;
    //sleep(10);
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

function isLockExpired($lockDir, $life = 2) {
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
