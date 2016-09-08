<?php
$dev_id = $_POST['dev_id'];
$status = $_POST['status'];
$tryauth = $_POST['tryauth'];

if (empty($tryauth) || $tryauth <> "orddrrmu6") return;

if (empty($dev_id) || (empty($status) && $status <> 0)) return;

include_once 'lib/devices.class.php';
$result = Devices::updateStatusId($dev_id, $status);

header('Content-Type: application/json');

if ($result == true) {
	echo '{"success":true, "dev_id":"'.$dev_id.'", "status":"'.$status.'"}';
} else {
	echo '{"success":false}';
}


?>