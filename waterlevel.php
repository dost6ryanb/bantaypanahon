<?php include_once 'lib/init3.php'?>
<!DOCTYPE html>
<html>
  <head>
    <title>DOST VI - DRRMU - Waterlevel Monitoring</title>
    <link rel="stylesheet" type="text/css" href="vendor/jquery-ui-1.12.0.custom/jquery-ui.min.css" />
    <link rel="stylesheet" type="text/css" href="vendor/jquery-ui-1.12.0.custom/jquery-ui.structure.min.css " />
    <link rel="stylesheet" type="text/css" href="vendor/jquery-ui-1.12.0.custom/jquery-ui.theme.min.css" />
    <link rel="stylesheet" type="text/css" href='css/style.css' />
    <link rel="stylesheet" type="text/css" href='css/screen.css' />
    <link rel="stylesheet" type="text/css" href='css/superfish.css' />
    <link rel="stylesheet" type="text/css" href='css/pages/waterlevel.css' />
   </head>
  <body>
    <div id="header">
      <div id="banner">
        <img id='logo' src='images/BANTAY_PANAHON.png' />
        <img id='logo_right' src='images/header_1_right.png' />
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
      <div class="container" >
        <h1>Waterlevel Reading <span id="daterange"></span></h1>
        <div id="datetimepicker_container">
            <label for="date_picker1">From: </label>
            <input type="text" class='ui-corner-all ui-button ui-widget' id="date_picker1" name="date_picker1">
            <label for="date_picker2">To: </label>
            <input type="text" class='ui-corner-all ui-button ui-widget' id="date_picker2" name="date_picker2">
            <button id='go' class='ui-corner-all ui-button ui-widget'>Go</button>
        </div>
          <div id="beta-info" class="ui-state-highlight">
              <span>Beta Warning. Choosing dates longer than 7 days may require more memory and network usage that it may crash your browser. Use with caution.</span>
              <span class="ui-icon ui-icon-closethick"></span>
          </div>
        <div id="charts_div_container"></div>
      </div>
    </div>
    <div id="footer">
      <div id='contactus'>
        <div class='contact'>
          <p class='contactname'>Department of Science and Technology Regional Office No. VI</p>
          <p class='contactaddress'>Magsaysay Village La paz, Iloilo 5000</p>
          <p class='contactnumber'>(033) 508-6739 / 320-0908 (Telefax)</p>
        </div>
        <div class='contact'>
          <p class='contactname'>Aklan Provincial Science & Technology Center</p>
          <p class='contactaddress'>Capitol Compound, Kalibo, Aklan</p>
          <p class='contactnumber'>(036) 500-7550 (Telefax)</p>
        </div>
        <div class='contact'>
          <p class='contactname'>Antique Provincial Science & Technology Center</p>
          <p class='contactaddress'>San Jose de Buenevista, Antique</p>
          <p class='contactnumber'>(036) 540-8025</p>
        </div>
        <div class='contact'>
          <p class='contactname'>Capiz Provincial Science & Technology Center</p>
          <p class='contactaddress'>CapSU, Roxas City, Capiz</p>
          <p class='contactnumber'>(036) 522-1044</p>
        </div>
        <div class='contact'>
          <p class='contactname'>Guimaras Provincial Science & Technology Center</p>
          <p class='contactaddress'>PSHS Research Center, Jordan, Guimaras</p>
          <p class='contactnumber'>(033) 396-1765</p>
        </div>
        <div class='contact'>
          <p class='contactname'>Iloilo Provincial Science & Technology Center</p>
          <p class='contactaddress'>DOST VI Compound, Iloilo City, Iloilo</p>
          <p class='contactnumber'>(033) 508-7183</p>
        </div>
        <div class='contact'>
          <p class='contactname'>Negros Occidental Provincial Science & Technology Center</p>
          <p class='contactaddress'>Cottage Road, Bacolod City</p>
          <p class='contactnumber'>(034) 707-0170</p>
        </div>
      </div>
      <div id='footerbanner' class='centeralign'>
        Disaster Risk Reduction and Management Unit</br>
        Department of Science and Technology Regional Office No. VI</br>
        Copyright 2014
      </div>
    </div>
    <script type="text/javascript">
        var waterlevel_devices = <?php echo json_encode(Devices::GetDevicesByParam('Waterlevel'));?>;
        var waterlevel_device_ids = <?php echo json_encode(Devices::GetDeviceIdsByParam('Waterlevel'));?>;
    </script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="vendor/jquery/jquery-1.12.4.min.js"></script>
    <script src="vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js"></script>
    <script src="vendor/datejs/date.min.js"></script>
    <script src="vendor/moment.js-2.18.1/moment.js"></script>
    <script src="vendor/jquery-ui-daterangepicker-0.6.0-beta.1/jquery.comiseo.daterangepicker.min.js"></script>
    <script src="js/ajax.helper.js"></script>
    <script src="js/waterlevel.js"></script>
  </body>
  <?php //include_once("analyticstracking.php") ?>
  </html>