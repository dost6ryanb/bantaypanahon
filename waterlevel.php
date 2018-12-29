<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once 'lib/init3.php' ?>
    <title>DOST VI - DRRMU - Waterlevel Monitoring</title>
    <link rel="stylesheet" type="text/css" href="vendor/jquery-ui-1.12.0.custom/jquery-ui.min.css"/>
    <link rel="stylesheet" type="text/css" href="vendor/jquery-ui-1.12.0.custom/jquery-ui.structure.min.css "/>
    <link rel="stylesheet" type="text/css" href="vendor/jquery-ui-1.12.0.custom/jquery-ui.theme.min.css"/>
    <link rel="stylesheet" type="text/css" href='css/style.css'/>
    <link rel="stylesheet" type="text/css" href='css/screen.css'/>
    <link rel="stylesheet" type="text/css" href='css/superfish.css'/>
    <link rel="stylesheet" type="text/css" href='css/pages/waterlevel.css'/>
</head>
<body>
<div id="header">
    <div id="banner">
        <img id='logo' src='images/BANTAY_PANAHON.png'/>
        <img id='logo_right' src='images/header_1_right.png'/>
        <div id='menu'>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="rainfall.php">Rainfall Monitoring</a></li>
                <li><a href="#" class='currentPage'>Waterlevel Monitoring</a></li>
                <li><a href="waterlevel2.php">Waterlevel Map</a></li>
                <li><a href="devices.php">Devices Monitoring</a></li>
            </ul>
        </div>
    </div>
</div>
<div id="content">
    <div class="container">
        <h1>Waterlevel Reading <span id="daterange"></span></h1>
        <div id="datetimepicker_container">
            <label for="date_picker1">From: </label>
            <input type="text" class='ui-corner-all ui-button ui-widget' id="date_picker1" name="date_picker1">
            <label for="date_picker2">To: </label>
            <input type="text" class='ui-corner-all ui-button ui-widget' id="date_picker2" name="date_picker2">
            <button id='go' class='ui-corner-all ui-button ui-widget'>Go</button>
            <button id="toggle" class='ui-corner-all ui-button ui-widget' >Show/Hide No Data</button>
        </div>
        <div id="beta-info" class="ui-state-highlight">
            <span>Beta Warning. Choosing dates longer than 7 days may require more memory and network usage that it may crash your browser. Use with caution.</span>
            <span class="ui-icon ui-icon-closethick"></span>
        </div>
        <div id="charts_div_container"></div>
    </div>
</div>
<div id='footer'>
    <p>Contact Bantay Panahon on <a href="https://www.facebook.com/bantaypanahonph/" target="_blank">Facebook</a></p>
    <p>DRRM Unit - Department of Science and Technology Regional Office No. VI</p>
</div>
<script type="text/javascript">
    var waterlevel_devices = <?php echo json_encode(Devices::GetDevicesByParam('Waterlevel'));?>;
    var waterlevel_device_ids_enabled = <?php echo json_encode(Devices::GetEnabledDeviceIdsByParam('Waterlevel'));?>;
    var waterlevel_device_ids_disabled = <?php echo json_encode(Devices::GetDisabledDeviceIdsByParam('Waterlevel'));?>;
</script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="vendor/jquery/jquery-1.12.4.min.js"></script>
<script src="vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js"></script>
<script src="vendor/datejs/date.min.js"></script>
<script src="vendor/moment.js-2.18.1/moment.js"></script>
<script src="vendor/jquery-ui-daterangepicker-0.6.0-beta.1/jquery.comiseo.daterangepicker.min.js"></script>
<script src="js/ajax.helper.js"></script>
<script type="text/javascript" src="vendor/gasparesganga-jquery-loading-overlay-2.1.6/loadingoverlay.min.js"></script>
<script src="js/waterlevel.js"></script>
<?php include_once("analyticstracking.php") ?>
</body>
</html>