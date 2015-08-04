<?php include_once 'lib/init.php'?>
<html>
<head>
	<title>DOST VI - DRRMU - Waterlevel Monitoring</title>
	<script type="text/javascript" src='js/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='js/jquery-ui.min.js'></script>
	<script type="text/javascript" src='js/date.js'></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<link rel="stylesheet" href='css/jquery-ui.min.css'>
	<link rel="stylesheet" href='css/jquery-ui.theme.min.css'>
	<link rel="stylesheet" href='css/jquery-ui.structure.min.css'>
	<link rel="stylesheet" type="text/css" href='css/style.css' />
	<link rel="stylesheet" type="text/css" href='css/screen.css' />
	<link rel="stylesheet" type="text/css" href='css/superfish.css' />
	<link rel="stylesheet" type="text/css" href='css/pages/waterlevel.css' />
	<script type="text/javascript">
		var key = {'sdate':'<?php echo $sdate;?>'};
		google.load("visualization", "1", {packages:["corechart"]});
	    google.load('visualization', '1', {packages:['table']});
	  
	  	google.setOnLoadCallback(function() {
		 	$( document ).ready(function() {
		 		//key['sdate'] = Date.today().toString('MM/dd/yyyy');
	  	 		initializeChartDivs('charts_div_container');
	  	 		initializeDateTimePicker('datetimepicker_container');
	  	 		initFetchData();
	  		});
		});

		$.xhrPool = [];
		$.xhrPool.abortAll = function() {
    		$(this).each(function(idx, jqXHR) {
       			jqXHR.abort();
    		});
    		$.xhrPool.length = 0
		};

		$.ajaxSetup({
		    beforeSend: function(jqXHR) {
		        $.xhrPool.push(jqXHR);
		    },
		    complete: function(jqXHR) {
		        var index = $.xhrPool.indexOf(jqXHR);
		        if (index > -1) {
		            $.xhrPool.splice(index, 1);
		        }
		    }
		});

	  	function initializeDateTimePicker(div) {
	  		var container = $(document.getElementById(div));	
	  		$('<h2>Waterlevel Reading for &nbsp;</h2>').appendTo(container);
	  		var datepicker = $('<input type="text" style="height: 0px; width:0px; border: 0px;z-index: 10000; position: relative" id="dtpicker"/>');
			var sdate = $('<a title="Click to change" href="#" id="sdate">'+SERVER_DATE+'</a>');
			datepicker.appendTo(container);
			sdate.appendTo(container);
			

			$('#dtpicker').datepicker({
			onSelect : function(data) {
							sdate.text(data);
							console.log(data);
							key['sdate'] = data;
							$.xhrPool.abortAll();
							initializeChartDivs('charts_div_container');	
							initFetchData(true);
						}/*,
					altField: '#datepicker_start',
					altFormat : 'mm/dd/yy',
					dateFormat : 'yymmdd'*/
			});
			$('#sdate').click(function(){
		   		$('#dtpicker').datepicker('show');
	    	});
	  	}

		function initializeChartDivs(div) {
			var charts_container = $(document.getElementById(div));
			charts_container.empty();
			//charts_container.append($('<h4>Latest Waterlevel Reading @ ' + key['serverdate']+' '+ key['servertime'] +'</h4>'));
			

			var prevProvince = '';

			for(var i=0;i<waterlevel_devices.length;i++) {
				var cur = waterlevel_devices[i];
				if (cur['province_name'] != prevProvince) {
					prevProvince = cur['province_name'];
					$('<br/><h3 class="provincelabel">'+prevProvince+'</h3>').appendTo(charts_container);
				}
				var chart = $('<div/>').attr({'id':'chart_div_'+waterlevel_devices[i].dev_id, 'class':'chart'}).text(waterlevel_devices.location_name).appendTo(charts_container);
				if (cur['status_id'] != null && cur['status_id'] != 0) {
					chart.css({'background':'url(images/disabled.png)', 'background-size':'100%', 'background-repeat':'no-repeat'});
				}
			}

		}

		function initFetchData(history) {
			setTimeout(function() {

			for(var i=0;i<waterlevel_devices.length;i++) {
				var cur = waterlevel_devices[i];
				if (cur['status_id'] != null && cur['status_id'] != 0) {
					console.log('skipping ' + cur['dev_id']);
				} else {
					if(typeof history === 'undefined') {
						postGetData(cur['dev_id'], key['sdate'], "", "", onWaterlevelDataResponseSuccess);
					} else {
						postGetData(cur['dev_id'], key['sdate'], key['sdate'], "144", onWaterlevelDataResponseSuccess);
					}
				}

				
			}
		}, 200);
		}



		function postGetData(dev_id, sdate, edate, limit, successcallback) {
			$.ajax({
					url: DOCUMENT_ROOT + 'data.php',
					type: "POST",
					data: {start: 0,
			  		 limit: limit,
			  		 sdate: sdate,
			  		 edate: edate,
			  		 pattern: dev_id,
				},
				dataType: 'json',
				tryCount: 0,
				retry:20})
			.done(successcallback)
			.fail(function(f, n){onRainfallDataResponseFail(dev_id)});
		}

		function onWaterlevelDataResponseSuccess(data) {
			var device_id = data.device[0].dev_id;
			var div = 'chart_div_'+ device_id;

			if (data.count == -1) {  // fmon.predict 404


			} else if (data.count ==  0 || // sensor no reading according to fmon.predict
				data.data.length == 0  || // predict reports that it has reading but actually doesnt have
				data.data[0].waterlevel == null || data.data[0].waterlevel=='' // errouneous readings
				) {
				//$(document.getElementById(div)).hide();
				$(document.getElementById(div)).css({'background':'url(images/nodata.png)', 'background-size':'100%', 'background-repeat':'no-repeat'});
			} else {
				drawChartWaterlevel(div, data);
			}
		}

		function onRainfallDataResponseFail(dev_id) {
			postGetData(dev_id, '', '');
		}


		function drawChartWaterlevel(chartdiv, json) {
			var datatable = new google.visualization.DataTable();
			datatable.addColumn('datetime', 'DateTimeRead');
			datatable.addColumn('number', 'Waterlevel'); //add column from index i
			
			//j - index of data
			// i - index of column
			for(var j=0;j<json.data.length;j++) {
				var row = Array(2);
				row[0] = Date.parseExact(json.data[j][json.column[0]], 'yyyy-MM-dd HH:mm:ss');
				row[1] = {
						v:parseFloat(json.data[j].waterlevel / 100), 
						f:(json.data[j].waterlevel / 100) + ' m'
					}
				
				datatable.addRow(row);
				
			}

			var d = Date.parseExact(json.data[json.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
			var d2 = Date.parseExact(json.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

			/*console.log(json.data[json.data.length - 1].dateTimeRead + " -- " + json.data[0].dateTimeRead);
			console.log(d + " -- " + d2);
			console.log('>>');
			*/
			//var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
			var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
			var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');
			
			var options = {
	          title: json.device[0].province + ' ' + json.device[0].municipality + ' - ' + json.device[0].location +' @ ' + title_enddatetime ,

			  hAxis: {
			    title: 'Waterlevel: '+(json.data[0].waterlevel / 100 )+ ' m',
				format : 'LLL d h:mm:ss a',
				viewWindow : {	min:d,max : d2},
				gridlines : {color : 'none'},
				textStyle : {fontSize: 10},
				textPosition : 'none'
			  },
			  vAxis: {
			  	title: '',
				format: '# m',
				 minValue: '0',
				 maxValue: '12',
				 gridlines : {count : 13}
			  },
			  legend : {
			  	position : 'none'
			  },
			  pointsize: 3,
			  seriesType: 'area',
			  crosshair : {trigger: 'both'},
			  allowHtml: true
	        };
			var chart =  new google.visualization.ComboChart(document.getElementById(chartdiv));
	        chart.draw(datatable, options);
			//$('<div/>').text('Waterlevel: '+json.data[0].waterlevel+ ' cm').css({'height':'20px'}).appendTo('#'+chartdiv);
	  }


	</script>
</head>
<body>
	<div id="header">
		<div id="banner">
		      <img id='logo' src='images/BANTAY_PANAHON.png'/>
              <img id='logo_right' src='images/header_1_right.png'/>
            <div id='menu'>
		      <ul>
                <li ><a href="index.php">Home</a></li>
                <li><a href="rainfall.php">Rainfall Monitoring</a></li>
                <li><a href="#" class='currentPage'>Waterlevel Monitoring</a></li>
				<li><a href="waterlevel2.php">Waterlevel Map</a></li>
				<li><a href="devices.php">Devices Monitoring</a></li>
            </ul>
		</div>
	    </div>
		
  		
	</div>
	<div id="content">
		<div id="container">
			<div id="datetimepicker_container">
			</div>
			<div id="charts_div_container"></div>
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
var waterlevel_devices = <?php echo json_encode(Devices::GetAllDevicesWithParameter('Waterlevel'));?>;
</script>
</html>