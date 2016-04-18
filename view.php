<?php include_once 'lib/init.php'?>
<?php
	if (isset($_GET['q']) and !empty($_GET['q'])) {
		
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DOST VI DRRMU - View Device</title>
<link rel="stylesheet" type="text/css" href='css/style.css' />
<link rel="stylesheet" type="text/css" href='css/screen.css' />
<link rel="stylesheet" type="text/css" href='css/pages/view.css' />
<link rel="stylesheet" href='css/jquery-ui.min.css'>
<link rel="stylesheet" href='css/jquery-ui.theme.min.css'>
<link rel="stylesheet" href='css/jquery-ui.structure.min.css'>
<link rel="stylesheet" href='css/chosen.min.css'>
<script type="text/javascript" src='js/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='js/jquery-ui.min.js'></script>
<script type="text/javascript" src='js/chosen.jquery.min.js'></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
		var key = {'sdate':'<?php echo $sdate;?>'};
		google.load("visualization", "1", {packages:["corechart"]});
	  
	  	google.setOnLoadCallback(function() {
		 	$( document ).ready(function() {
		 		
		 		//key['sdate'] = Date.today().toString('MM/dd/yyyy');
	  	 		initDevIdSelectOption('dev_id_select_container');
	  	 		$("button").button();
	  	 		//initializeDateTimePicker('datetimepicker_container');
	  	 		initFetchData();
	  		});
		});



		function initFetchData(history) {
			setTimeout(function() {


		}, 200);
		}

		function initDevIdSelectOption(div) {
			var optionsValues = '<select id="dev_id_option" class="chzn-select" data-placeholder="Please select some options" multiple>';

			var prevProvince = '';
			//optionsValues += '<option value="">Please select some location</option>';
			for (var i = 0; i < rainfall_devices.length; i++) {
				var cur = rainfall_devices[i];

				if (cur['province_name'] != prevProvince) {
					if (prevProvince != '') {
						optionsValues += '</optgroup>';
					}
					optionsValues += '<optgroup label="'+cur.province_name+'">';
					prevProvince = cur.province_name;
				}

				if (cur.status_id == '0' || cur.status_id == null) {
					optionsValues += '<option value="' + cur.dev_id + '">' + cur.municipality_name + ' - ' + cur.location_name + '</option>';
					
				} else {
					optionsValues += '<option value="' + cur.dev_id + '" disabled>' + cur.municipality_name + ' - ' + cur.location_name + '</option>';
				}
				
			}
			optionsValues += '</optgroup>';
	    	optionsValues += '</select>';
	    	var options = $(document.getElementById(div));
	    	options.append(optionsValues);

	    	var go = $('<button type="button">Go</button>');
	    	options.append(go);

	    	$('.chzn-select').chosen({
	    		disable_search_threshold: 10,
	    		display_selected_options: false,
	    		placeholder_text_multiple : 'Please select some options',
	    		max_selected_options:5
	    	});
		}
</script>
</head>
<body>
<div id='header'>
	<div id="banner">
		<img id='logo' src='images/BANTAY_PANAHON.png'/>
        <img id='logo_right' src='images/header_1_right.png'/>
        <div id='menu'>
		<ul>
			<li ><a href="index.php">Home</a></li>
			<li><a href="rainfall.php">Rainfall Monitoring</a></li>
			<li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
			<li><a href="waterlevel2.php">Waterlevel Map</a></li>
			<li><a href="devices.php">Devices Monitoring</a></li>
		</ul>
	</div>
	</div>
		
  	
</div>
<div id='content'>
	<div id="config_container">
		<div id='dev_id_select_container'>
		</div>
	</div>
	<div id="chart_div_container">
	</div>

</div>
<div id='footer'>
    <div id='contactus'>
            <div class='contact'>
                <p class='contactname' >Department of Science and Technology Regional Office No. VI</p>
                <p class='contactaddress'>Magsaysay Village La paz, Iloilo 5000</p>
                <p class='contactnumber'>(033) 508-6739 / 320-0908 (Telefax)</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Aklan Provincial Science & Technology Center</p>
                <p class='contactaddress'>Capitol Compound, Kalibo, Aklan</p>
                <p class='contactnumber'>(036) 500-7550 (Telefax)</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Antique Provincial Science & Technology Center</p>
                <p class='contactaddress'>San Jose de Buenevista, Antique</p>
                <p class='contactnumber'>(036) 540-8025</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Capiz Provincial Science & Technology Center</p>
                <p class='contactaddress'>CapSU, Roxas City, Capiz</p>
                <p class='contactnumber'>(036) 522-1044</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Guimaras Provincial Science & Technology Center</p>
                <p class='contactaddress'>PSHS Research Center, Jordan, Guimaras</p>
                <p class='contactnumber'>(033) 396-1765</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Iloilo Provincial Science & Technology Center</p>
                <p class='contactaddress'>DOST VI Compound, Iloilo City, Iloilo</p>
                <p class='contactnumber'>(033) 508-7183</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Negros Occidental Provincial Science & Technology Center</p>
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
</body>
<script type="text/javascript">
var rainfall_devices = <?php echo json_encode(Devices::GetAllDevicesWithParameter('Rainfall'));?>;
</script>
</html>