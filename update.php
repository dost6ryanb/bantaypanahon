<?php
$dev_id = $_POST['dev_id'];
$status_id = $_POST['status_id'];
$tryauth = $_POST['tryauth'];

if (empty($tryauth) || $tryauth <> "orddrrmu6") return;

if (empty($dev_id) || (empty($status_id) && $status_id <> 0)) return;

include_once 'lib/devices.class.php';
$result = Devices::updateStatusId($dev_id, $status_id);

header('Content-Type: application/json');

if ($result == true) {
	echo '{"success":true, "dev_id":'.$dev_id.', "status_id":'.$status_id.'}';
} else {
	echo '{"success":false}';
}


?>