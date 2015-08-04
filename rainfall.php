<?php include_once 'lib/init.php'?>
<html>
<head>
	<title>DOST VI - DRRMU - Rainfall Monitoring</title>
	<script type="text/javascript" src='js/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='js/jquery-ui.min.js'></script>
	<script type="text/javascript" src='js/date-en-US.js'></script>
	<link rel="stylesheet" href='css/jquery-ui.min.css'>
	<link rel="stylesheet" href='css/jquery-ui.theme.min.css'>
	<link rel="stylesheet" href='css/jquery-ui.structure.min.css'>
	<link rel="stylesheet" type="text/css" href='css/style.css' />
	<link rel="stylesheet" type="text/css" href='css/screen.css' />
	<link rel="stylesheet" type="text/css" href='css/superfish.css' />
	<link rel="stylesheet" type="text/css" href='css/pages/rainfall.css' />
</head>
<script type="text/javascript">
	var key = {'serverdate':'<?php echo $sdate;?>', 'servertime':'<?php echo date("H:i");?>',
				'provinces' : ['Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental'],
				'durations' : [{'label': '1 hr', 'minutes':'60'},
								{'label': '2 hr', 'minutes':'120'},
								{'label': '3 hr', 'minutes':'180'},
								{'label': '6 hr', 'minutes':'360'},
								{'label': '9 hr', 'minutes':'540'},
								{'label': '12 hr', 'minutes':'720'},
								{'label': '24 hr', 'minutes':'1440'}
							 ],
				'limits' : [
			   		{'min':0.01, 'max':5, 'name':'lighter', 'style':'rainlighter'},
			   		{'min':5, 'max':25, 'name':'light', 'style':'rainlight'}, 
			   		{'min':25, 'max':50, 'name':'moderate' , 'style':'rainmoderate'}, 
			   		{'min':50, 'max':75, 'name':'heavy', 'style':'rainheavy'}, 
			   		{'min':75, 'max':100, 'name':'intense', 'style':'rainintense'}, 
			   		{'min':100, 'max':999, 'name':'torrential', 'style':'raintorrential'}
			   		]
		  };

	var xtblcounter = 0;
	$(document).ready(function() {
		$("button").button();

		initProvincesSelect('provinces');
		initDurationSelect('durations');
		$('select').selectmenu({ width: 120 });
		initGo('goButton', 'provinces', 'durations', 'rainfalltable');
	});

	function initProvincesSelect(select) {
		var frmselect = $(document.getElementById(select));
		for (var i=0;i<key['provinces'].length;i++) {
			$('<option value="'+i+'">'+key['provinces'][i]+'</option>').appendTo(frmselect);
		}
	}

	function initDurationSelect(select) {
		var frmselect = $(document.getElementById(select));
		for (var i=0;i<key['durations'].length;i++) {
			//$('<option value="'+i+'">'+12+'</option>').appendTo(frmselect);
			$('<option value="'+i+'">'+key['durations'][i].label+'</option>').appendTo(frmselect);
		}
	}

	function initGo(button, provinceSelect, durationSelect, div) {
		var frmbutton= $(document.getElementById(button));
		var frmselectProvince = $(document.getElementById(provinceSelect));
		var frmselectDuration = $(document.getElementById(durationSelect));
		frmbutton.on('click', function() {
			var prov = key['provinces'][frmselectProvince.val()];
			var dur = key['durations'][frmselectDuration.val()].minutes;
			initRainfalltable(div, prov, dur);
		});
	}

	function initRainfalltable(div, province, duration) {
		var div = $(document.getElementById(div));

		var table = $('<table/>', {'class':'xtbl', 'id':'xtbl_'+ ++xtblcounter, 'data-duration':duration}).prependTo(div);
		//$('<tr><th>Server DateTime</th><td id="serverdtr">'+key['serverdate']+' '+ key['servertime']+'</td><tr>').appendTo(table);
		$('<tr/>').append($('<th colspan="2">Cumulative Rainfall Reading of '+ province +' for the last '+ parseInt(duration/60) +' hour/s.</th><'))
						.append($('<td/>@ ', {'class':'textalignright'}).text(key['serverdate']+' '+ key['servertime'])
							.append($('<button>close</button>')
								.on('click', function() {table.remove();})
								.button({
							      icons: {
							        primary: "ui-icon-cancel"
							      },
							      text: true})
					)).appendTo(table);

		$('<tr class="ui-widget-header"><th>Municipality</th><th>Location</th><th>Cumulative (mm)</th><tr>').appendTo(table);

		for(var i=0;i<rainfall_devices.length;i++) {
			var cur = rainfall_devices[i];
			if (cur['province_name'] == province) {
				$('<tr/>', {'data-dev_id':cur['dev_id']})
				.append($('<td>'+cur['municipality_name']+'</td>'))
				.append($('<td>'+cur['location_name']+'</td>'))
				.append($('<td/>', {'data-col':'cr'})).appendTo(table);
				if (cur['status_id'] == null || cur['status_id'] == '0') {
					postGetData('xtbl_'+xtblcounter, cur['dev_id'], "", "", duration);
				} else {
					updateRainfallTable('xtbl_'+xtblcounter, cur['dev_id'], "[DISABLED]", 'disabled') ;
				}
			}
		}
	}

	function postGetData( xtbl, dev_id, sdate, limit, duration) {
		$.ajax({
				url: DOCUMENT_ROOT + 'data.php',
				type: "POST",
				data: {start: 0,
		  		 limit: limit,
		  		 sdate: '',
		  		 edate: '',
		  		 pattern: dev_id
			},
			dataType: 'json',
			tryCount: 0,
			retry:20})
		.done(function(d){onRainfallDataResponseSuccess(d, xtbl, duration);})
		.fail(function(f, n){onRainfallDataResponseFail(xtbl, dev_id, duration);});
	}

	function onRainfallDataResponseSuccess(data, xtbl, duration) {
		var device_id = data.device[0].dev_id;

		$('#loadedraindevices').text(++key['loadedraindevices']);

		if (data.count == -1) {// cannot reach predict
			onRainfallDataResponseFail(xtbl, device_id, duration);
		} else if (data.count ==  0 ||// sensor no reading according to fmon.predict
			data.data.length == 0  || // predict reports that it has reading but actually doesnt have
			data.data[0].rain_cumulative == null || data.data[0].rain_cumulative=='' // errouneous readings
			) {
			updateRainfallTable(xtbl, device_id, '[NO DATA]', 'nodata') ;
		} else {			
			var rain_cumulative = solveforcumulative(data, duration)
			updateRainfallTable(xtbl, device_id, rain_cumulative) ;
		}
	}

	function onRainfallDataResponseFail(xtbl, dev_id, duration) {
		var retryhtml = "<a href=javascript:retryFetchRain('"+xtbl+"'," + dev_id + "," + duration + ")>Retry</a>";
		updateRainfallTable(xtbl, dev_id, retryhtml, null, null);
	}
    
    function retryFetchRain(xtbl, dev_id, duration) {
		postGetData(xtbl, dev_id, "", "", duration );
		updateRainfallTable(xtbl, dev_id, '', '', '');
	}

	function updateRainfallTable(xtbl, device_id, raincumulative, dataclass) {
		var table = $(document.getElementById(xtbl));
		var dtr = table.find('tr[data-dev_id=\''+device_id+'\']  td[data-col=\'dtr\']');
		var cr = table.find('tr[data-dev_id=\''+device_id+'\'] td[data-col=\'cr\']');

		//if (dateTimeRead != null) dtr.text(dateTimeRead); else dtr.text('');
		//if (rainvalue != null ) rv.text(rainvalue); else rv.text('');
		if (raincumulative != null ) {
			cr.html(raincumulative); 
			for (var i = 0;i<key['limits'].length;i++) {
				limit = key['limits'][i];
				if (raincumulative >= parseFloat(limit['min']) && raincumulative < parseFloat(limit['max'])) {
					cr.addClass(key['limits'][i].style);
					break;
				} 
			}
		} else {
			cr.text("");
		}

		if (dataclass != 'undefined') {
			//dtr.removeClass().addClass(dataclass);
			//rv.removeClass().addClass(dataclass);
			cr.addClass(dataclass);
		}
	}

	function solveforcumulative(data, duration) {
		var serverdtr = Date.parseExact(key['serverdate']+ ' '+ key['servertime']+':00', 'MM/dd/yyyy HH:mm:ss');
		var str = '';
		var cr = 0;
		var enddtr = serverdtr.clone().addMinutes(-parseInt(duration));

		for(var i=0;i<data.data.length;i++) {
			var devicedtr = Date.parseExact(data.data[i].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

			if (devicedtr.between(enddtr, serverdtr)) {
				cr += parseFloat(data.data[i].rain_value);
			} else {
				if (i==0) {
					cr += parseFloat(data.data[i].rain_value);
					serverdtr = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
					enddtr = serverdtr.clone().addMinutes(-parseInt(duration));
					str = ' <a class="ui-state-error" href="#" title="Out of Sync. Result from ' + serverdtr.toString('MMMM d yyyy h:mm:ss tt') + '">[!]</a>';
				} else {
					break;
				}
				
			}
		}

		return cr.toFixed(2) + str;

	}

</script>
<body>
	<div id="header">
		<div id="banner">
		<img id='logo' src='images/BANTAY_PANAHON.png'/>
        <img id='logo_right' src='images/header_1_right.png'/>
	</div>
		
	  	<div id='menu'>
			<ul>
				<li ><a href="index.php">Home</a></li>
				<li><a href="#" class='currentPage'>Rainfall Monitoring</a></li>
				<li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
				<li><a href="waterlevel2.php">Waterlevel Map</a></li>
				<li><a href="devices.php">Devices Monitoring</a></li>
			</ul>
		</div>
	</div>
	<div id="content">
		<div id="config">
			<label for='provinces'>Please select province:</label>
			<select id='provinces' name='province'>

			</select>
			<label for='durations'>Please select duration:</label>
			<select id='durations' name='duration'>

			</select>
			<button id='goButton' class='.ui-widget'>Go</button>
			<span>*Refresh page to update server date and time.</span>
		</div>
		<div id='rainfalltable'>
		
		</div>
	</div>
	<div id="footer">
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