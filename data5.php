<?php
$dev_ids = $_POST['dev_ids'];
$sdate = $_POST['sdate'];
$edate = $_POST['edate'];
$type = $_POST['type'];

if (!is_array($dev_ids) || count($dev_ids) == 0) return;
if ($sdate == FALSE) $sdate = '';
if ($edate == FALSE) $edate = $sdate;
if ($type == FALSE) $type = 0;

set_time_limit(60);

header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=300, private');
header('Content-Type: application/json');


$responseCount = 0;
$response = '[';

//$len = count($dev_ids);
$done = false;
$responseInitid = false;
$taskQueue = $dev_ids;
$nextTaskQueue = [];

do {
    $nextTaskQueue = [];
    //error_log("size of taskQueue: " . sizeof($taskQueue));
    foreach ($taskQueue as $dev_id) {
        $key = md5("$dev_id")."-$sdate-$edate-device";
        $cache = getCacheFqfname($key);
        $lockDir = getLockname($key);
        $lockExists = isLockExist($lockDir);
        $read ='';

        $cb = function () use ($dev_id, $sdate, $edate) {
            return getFromApiMulti($dev_id, $sdate, $edate);
        };

        if ($cache && !$lockExists) {
            if (!isCacheExpired($cache)) {
                $read = readCache($key, $lockDir);
            } else {
                renewCache($key, $cb, $lockDir);
                $nextTaskQueue[] = $dev_id;
            }
        } elseif ($cache && $lockExists) {
            if (isLockExpired($lockDir)) {
                releaseLock($lockDir);
            }
            $nextTaskQueue[] = $dev_id;
        } elseif (!$cache && $lockExists) {
            if (isLockExpired($lockDir)) {
                releaseLock($lockDir);
            }
            $nextTaskQueue[] = $dev_id;
        } else {
            renewCache($key, $cb, $lockDir);
            $nextTaskQueue[] = $dev_id;
        }

        if (!empty($read)) {
            if ($responseInitid) $response .= ' ,';
            $response .= $read;
            if (!$responseInitid) $responseInitid = true;
        }
    }

    //error_log("size of nextTaskQueue: " . sizeof($nextTaskQueue));

    if (!empty($nextTaskQueue)) {
        $taskQueue = $nextTaskQueue;
    } else {
        $done = true;
    }

} while (!$done);

/*foreach ($dev_ids as $i => $dev_id) {
    $key = md5("$dev_id")."-$sdate-$edate-device";
    $cache = getCacheFqfname($key);
    $lockDir = getLockname($key);

    $read ='';

    $cb = function () use ($dev_id, $sdate, $edate) {
        return getFromPhilSensorsService($dev_id, $sdate, $edate);
    };

    if ($cache) {
        if (!isCacheExpired($cache)) {
            $read = readCache($key, $lockDir);
        } else {
            renewCache($key, $cb, $lockDir);
            $read = readCache($key, $lockDir);
        }
    } else {
        renewCache($key, $cb, $lockDir);
        $read = readCache($key, $lockDir);
    }

    if (!empty($read)) {
        if ($i != 0) $response .= ' ,';
        $response .= $read;
        $responseCount++;
    }
}*/
$response .= ']';
echo $response;
//trigger_error("Oops!", E_USER_ERROR);

function shutdown($lockDir)
{
    releaseLock($lockDir);
}

function getFromPhilSensorsService($dev_id, $sdate, $edate)
{
    $url = 'http://philsensors.asti.dost.gov.ph/php/dataduration.php?stationid=' . $dev_id . '&from=' . $sdate . '&to=' . $edate;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return $response;
    } else {
        return null;
    }
}

function getFromApiMulti($dev_id, $sdate, $edate) {
    $url = 'http://philsensors.asti.dost.gov.ph/php/dataduration.php?stationid=' . $dev_id . '&from=' . $sdate . '&to=' . $edate;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
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

    $maxTry = 3;
    $try = 1;
    if ($fp && flock($fp, LOCK_EX)) {
        do {
            $response = $cb();
            if ($response != null) {
                ftruncate($fp, 0); // <-- this will erase the contents such as 'w+'
                rewind($fp);
                fwrite($fp, $response);
                $success = true;
            } else {
                if ($try++ > $maxTry) {
                    $success = true;
                }
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


function readCache($key, $lockDir)
{
    $response ='';
    $filename = getCacheFileName($key);
    $fp = false;

    $success = false;
    do {
        if (isLockExpired($lockDir)) {
                releaseLock($lockDir);
                $success = true;
        } else {
                usleep(rand(300, 1000));
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
        $response.= fread($fp, 8192);
    }

    fclose($fp);

    return $response;
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
