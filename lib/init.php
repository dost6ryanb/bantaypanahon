<?php
include_once 'lib/devices.class.php';
	date_default_timezone_set('Asia/Manila');

	$now = date("H:i");
	$hour = intval(substr($now, 0, 2));
	$sdate;

	if ($hour >= 0 && $hour < 8 ) {
			$sdate = date("m/d/Y", strtotime("yesterday"));
	} elseif ($hour >= 8 && $hour <= 23) {
			$sdate = date("m/d/Y");
	}
	$server_name = $_SERVER['SERVER_NAME'];
?>
<script type="text/javascript" >
    var DOCUMENT_ROOT = "http://<?php echo $server_name;?>/";
    var SERVER_DATE = '<?php echo date("F d,Y");?>';
    var SERVER_TIME = '<?php echo date("g:i A");?>';
    var SERVER_NAME = '<?php echo $_SERVER['SERVER_NAME'];?>';
    var SDATE = '<?php echo $sdate;?>';
</script>
