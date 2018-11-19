<?php
include_once 'lib/devices.class.php';
	date_default_timezone_set('Asia/Manila');

	$now = date("H:i");
	$hour = intval(substr($now, 0, 2));
	$sdate;
    $edate;

	if ($hour >= 0 && $hour < 8 ) {
			$sdate = date("Y-m-d", strtotime("yesterday"));
			$edate = date("Y-m-d");
	} elseif ($hour >= 8 && $hour <= 23) {
			$sdate = date("Y-m-d");
			$edate = date("Y-m-d", strtotime("+1 day"));
	}

	$server_name = $_SERVER['SERVER_NAME'];
?>
<script type="text/javascript" >
    var DOCUMENT_ROOT = "http://<?php echo $server_name;?>";
    var SERVER_DATE = '<?php echo date("F d,Y");?>';
    var SERVER_TIME = '<?php echo date("g:i A");?>';
    var SERVER_NAME = '<?php echo $_SERVER['SERVER_NAME'];?>';
    var SDATE = '<?php echo $sdate;?>';
    var EDATE = '<?php echo $edate;?>';
</script>
