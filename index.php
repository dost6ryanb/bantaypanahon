<?php include_once 'lib/init.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DOST VI DRRMU - Home</title>
<script type="text/javascript" src='js/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='js/jquery-ui.min.js'></script>
<script type="text/javascript" src='js/date-en-US.js'></script>
<script type="text/javascript" src='js/jquery.scrollTo.min.js'></script>
<script type="text/javascript" src='js/jquery.easy-ticker.min.js'></script>
<script type="text/javascript" src='js/heat-index.js'></script>
<link rel="stylesheet" href='css/jquery-ui.min.css'>
<link rel="stylesheet" href='css/jquery-ui.theme.min.css'>
<link rel="stylesheet" href='css/jquery-ui.structure.min.css'>
<link rel="stylesheet" type="text/css" href='css/style.css' />
<link rel="stylesheet" type="text/css" href='css/screen.css' />
<link rel="stylesheet" type="text/css" href='css/pages/index.css' />
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&sensor=false"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
	setTimeout(function(){
		window.location.reload(true);
	},900000); // refresh 10 minutes
	var key = {'sdate':'<?php echo $sdate;?>', 'numraindevices':0, 'loadedraindevices':0,
			   'serverdate':'<?php echo date("m/d/Y");?>', 'servertime':'<?php echo date("H:i");?>',
			   'marker' : [
			   		{'min':0.01, 'max':5, 'name':'lighter', 'src':'images/rain-lighter'},
			   		{'min':5, 'max':25, 'name':'light', 'src':'images/rain-light'}, 
			   		{'min':25, 'max':50, 'name':'moderate' , 'src':'images/rain-moderate'}, 
			   		{'min':50, 'max':75, 'name':'heavy', 'src':'images/rain-heavy'}, 
			   		{'min':75, 'max':100, 'name':'intense', 'src':'images/rain-intense'}, 
			   		{'min':100, 'max':999, 'name':'torrential', 'src':'images/rain-torrential'}
			   		]
			   	
			  };

	var cumulative_rainfall_map;
	var cumulative_rainfall_map_markers = [];
	var lastValidCenter;

	//google.load("visualization", "1", {packages:["corechart"]});
	google.charts.load('current', {packages: ['corechart']});
	//google.load('visualization', '1', {packages:['table']});

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

	$(document).ready(function() {
      	initMap("map-canvas");
		initMapLegends('legends');
		initRainfallTable("rainfall-canvas");
		initTicker('ticker1');
		initTicker('ticker2');
		initChartDivs('charts_div_container');
		initFetchData();

    });


	function initFetchData(history) {
		setTimeout(function() {
			for(var i=0;i<rainfall_devices.length;i++) {
				var cur = rainfall_devices[i]; 
				if (history) {
					postGetData(cur['dev_id'], key['sdate'], key['sdate'], 1, onRainfallDataResponseSuccess);
				} else {
					if (cur['status_id'] == null || cur['status_id'] == '0') {
						postGetData(cur['dev_id'], key['sdate'], "", 1, onRainfallDataResponseSuccess);
					} // else SKIP
				}
		
			}

		}, 200);
		
		setTimeout(function() {

			for(var i=0;i<waterlevel_devices.length;i++) {
				var cur = waterlevel_devices[i];
					if (history) {
						postGetData(cur['dev_id'], key['sdate'], key['sdate'], "144", onWaterlevelDataResponseSuccess);
					} else {
						if (cur['status_id'] == null || cur['status_id'] == '0') {
							postGetData(cur['dev_id'], key['sdate'], "", "", onWaterlevelDataResponseSuccess);
						}
					}	
			}
		}, 200);

		setTimeout(function() {
			for(var i=0;i<temperature_devices.length;i++) {
				var cur = temperature_devices[i]; 
				// if (history) {
					postGetData(cur.dev_id, key['sdate'], key['sdate'], 96, onTemperatureDataResponseSuccess);
				// } else {
				// 	if (cur['status_id'] == null || cur['status_id'] == '0') {
				// 		postGetData(cur.dev_id, key['sdate'], "", "", onTemperatureDataResponseSuccess);
				// 	}
				// }
			}

		}, 200);
	}

	function postGetData(dev_id, sdate, edate, limit, successcallback) {
		$.ajax({
				url: DOCUMENT_ROOT + 'data.php',
				//url:'http://localhost/dost6arc/api/archive',
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

	function onRainfallDataResponseSuccess(data) {
		var device_id = data.device[0].dev_id;
		console.log(device_id);
		console.log(data);

		$('#loadedraindevices').text(++key['loadedraindevices']);

		if (data.count == -1) {// cannot reach predict
			//TODO either add retry or initiate retry;
		} else if (data.count ==  0 ||// sensor no reading according to fmon.predict
			data.data.length == 0  || // predict reports that it has reading but actually doesnt have
			data.data[0].rain_cumulative == null || data.data[0].rain_cumulative=='' // errouneous readings
			) {
			updateRainfallTable(device_id, '[NO DATA]', '', '', 'nodata');
		} else {
			var device = search(rainfall_devices, 'dev_id', device_id);
			//var timeread = data.data[0].dateTimeRead.substring(10).substring(0, 6);
			var devicedtr = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
			var serverdtr = Date.parseExact(key['serverdate']+ ' '+ key['servertime']+':00', 'MM/dd/yyyy HH:mm:ss');

			var hour12time = devicedtr.toString("h:mm tt");

			if (key['sdate'] == key['serverdate'] && devicedtr.add({minutes:15}).compareTo(serverdtr) == -1) { //late
				updateRainfallTable(device_id, hour12time, data.data[0].rain_value, data.data[0].rain_cumulative, 'latedata');
			} else {
				updateRainfallTable(device_id, hour12time, data.data[0].rain_value, data.data[0].rain_cumulative, 'dataok');
			}
			
			var rc = parseFloat(data.data[0].rain_cumulative);
			var rv = parseFloat(data.data[0].rain_value);
			var marker_url;
			for (var i = 0;i<key['marker'].length;i++) {
				limit = key['marker'][i];
				if (rc >= parseFloat(limit['min']) && rc < parseFloat(limit['max'])) {
					if (rv > 0) {
						marker_url = limit['src']+'_now.png';
					} else {
						marker_url = limit['src']+'.png';
					}
					addMarker(device['dev_id'], device['posx'], device['posy'], device['municipality_name'] + ' - ' + device['location_name'], device['type_name'], marker_url);
					var text =  "[Cumulative Rainfall] "+device['municipality_name'] + ' - ' + device['location_name'] + ' : ' + data.data[0].rain_cumulative + ' mm';
					addTicker(text, 'ticker1list');

					break;
				} 
			}
			
			
		}
	}

	function onRainfallDataResponseFail(dev_id) {
		var retryhtml = '<a href=javascript:retryFetchRain('+dev_id+')>Retry</a>';
		updateRainfallTable(dev_id, retryhtml, null, null);
	}

	function retryFetchRain(dev_id) {
		postGetData(dev_id, key['sdate'], key['sdate'], 1, onRainfallDataResponseSuccess);
		updateRainfallTable(dev_id, '', '', '');
	}

	function onWaterlevelDataResponseSuccess(data) {
		updateWaterlevelChart(data)
	}

	function onTemperatureDataResponseSuccess(data) {
		updateTemperatureTicker(data);
	}

	function initMap(divcanvas) {
		var DOST_CENTER = new google.maps.LatLng(10.712317, 122.562362); //DOST CENTER
	
		var mapOptions = {
  			//zoom: 6, //Whole Philippines View
  			zoom: 8, //Region 6 Focus,
  			minZoom:8,
  			maxZoom:null,
  			center: DOST_CENTER,
  			disableDefaultUI: true,
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.LARGE,
				position: google.maps.ControlPosition.RIGHT_CENTER
			},
  			draggableCursor:'crosshair',
  			styles: [{"featureType":"administrative.land_parcel","stylers":[{"visibility":"off"}]},{"featureType":"poi","stylers":[{"visibility":"off"}]},{"featureType":"road","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","stylers":[{"visibility":"on"}]},{"featureType":"landscape","stylers":[{"lightness":47}]},{"featureType":"water","stylers":[{"lightness":39}]}]
		};
		
		cumulative_rainfall_map = new google.maps.Map(document.getElementById(divcanvas), mapOptions);
		
		// Bounds for region xi
        var strictBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(9.1895  , 119.1193), 
            new google.maps.LatLng(12.2171, 125.9308)
         );

        lastValidCenter = cumulative_rainfall_map.getCenter();

        // Listen for the dragend event
        google.maps.event.addListener(cumulative_rainfall_map, 'idle', function() {
            var minLat = strictBounds.getSouthWest().lat();
            var minLon = strictBounds.getSouthWest().lng();
            var maxLat = strictBounds.getNorthEast().lat();
            var maxLon = strictBounds.getNorthEast().lng();
            var cBounds  =cumulative_rainfall_map.getBounds();
            var cMinLat = cBounds.getSouthWest().lat();
            var cMinLon = cBounds.getSouthWest().lng();
            var cMaxLat = cBounds.getNorthEast().lat();
            var cMaxLon = cBounds.getNorthEast().lng();
            var centerLat = cumulative_rainfall_map.getCenter().lat();
            var centerLon = cumulative_rainfall_map.getCenter().lng();

            if((cMaxLat - cMinLat > maxLat - minLat) || (cMaxLon - cMinLon > maxLon - minLon))
            {   //We can't position the canvas to strict borders with a current zoom level
                //cumulative_rainfall_map.setZoom(cumulative_rainfall_map.getZoom()+1);
                return;
            }
            if(cMinLat < minLat)
                var newCenterLat = minLat + ((cMaxLat-cMinLat) / 2);
            else if(cMaxLat > maxLat)
                var newCenterLat = maxLat - ((cMaxLat-cMinLat) / 2);
            else
                var newCenterLat = centerLat;
            if(cMinLon < minLon)
                var newCenterLon = minLon + ((cMaxLon-cMinLon) / 2);
            else if(cMaxLon > maxLon)
                var newCenterLon = maxLon - ((cMaxLon-cMinLon) / 2);
            else
                var newCenterLon = centerLon;

            if(newCenterLat != centerLat || newCenterLon != centerLon)
                //cumulative_rainfall_map.setCenter(new google.maps.LatLng(newCenterLat, newCenterLon));
        		cumulative_rainfall_map.panTo(new google.maps.LatLng(newCenterLat, newCenterLon));
        });

        google.maps.event.addListener(cumulative_rainfall_map, 'click', function (event) {
            var pnt = event.latLng;
            var lat = pnt.lat();
	        lat = lat.toFixed(6);
	        var lng = pnt.lng();
	        lng = lng.toFixed(6);
	    });
	}	

	function initMapLegends(container) {
		legendscontainer = $(document.getElementById(container));
		cumulative_rainfall_map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(document.getElementById(container));
		
		$('<button id="togglelegends">Hide Legend</button>')
			.on('click', function() {
				$('.legend').toggle();
				$('.legendtitle').toggle();
                if ($(this).text() == "Show Legend") {
                    $(this).text('Hide Legend');
                } else {
                    $(this).text('Show Legend');
                }
			})
			.appendTo(legendscontainer);
        $('<div class="legendtitle">Daily Cumulative Rainfall</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][0].src+'.png" > less than 5mm</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][1].src+'.png" > 5mm to less than 25mm</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][2].src+'.png" > 25mm to less than 50mm</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][3].src+'.png" > 50mm to less than 75mm</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][4].src+'.png" > 75mm to less than 100mm</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][5].src+'.png" > 100mm or more</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="images/overlay_now.png" > currently raining</div>').appendTo(legendscontainer);
	}

	function initTicker(ticker) {
		$(document.getElementById(ticker)).css({'display':'block'}).easyTicker({visible:1, interval: 3500});
	}

	function initRainfallTable(div) {

		var prevProvince = '';
		var maindiv = document.getElementById(div);
		var table = $('<table/>').appendTo(maindiv);
		var sdate = $('<td colspan="3"><a title="Click to change" href="#" id="sdate">' + key['sdate'] + '</a></td>');
		var datepicker = $('<input type="text" style="height: 0; width:0; border: 0;" id="dtpicker2"/>');
		datepicker.appendTo(sdate);

		$('<tr/>').append($('<th>Rainfall</th>'))
			.append(sdate)
			.appendTo(table);

		$('#dtpicker2').datepicker({
			onSelect: function (data) {
				sdate.find('a').text(data);
				key['sdate'] = data;
				key['numraindevices'] = 0;
				key['loadedraindevices'] = 0;
				$.xhrPool.abortAll();
				clearMarkers();
				clearRainfallTable();
				clearAllTicker('ticker1list');
				clearAllTicker('ticker2list');
				initFetchData(true);
			}/*,
			 altField: '#datepicker_start',
			 altFormat : 'mm/dd/yy',
			 dateFormat : 'yymmdd'*/
		});
		$('#sdate').click(function () {
			$('#dtpicker2').datepicker('show');
		});


		$('<tr><th>Server DateTime</th><td id="serverdtr" colspan="3"><table><tr><td colspan="3">' + SERVER_DATE + '</td></tr> <tr><td colspan="3">' + SERVER_TIME + '</tr></td></table></td><tr>').appendTo(table);
		$('<tr><th>Total Devices</th><td id="numraindevices" colspan="3">' + rainfall_devices.length + '</td><tr>').appendTo(table);
		$('<tr><th>Loaded</th><td id="loadedraindevices" colspan="3">0</td><tr>').appendTo(table);
		for (var i = 0; i < rainfall_devices.length; i++) {
			var cur = rainfall_devices[i];


			if (cur['province_name'] != prevProvince) {
				prevProvince = cur.province_name;
				$('<tr/>').addClass('province_tr')
					.append($('<th>' + prevProvince + '</th>'))
					.append($('<th>Time</th>'))
					.append($('<th>Rain Value (mm)</th>'))
					.append($('<th>Cumulative (mm)</th>')).appendTo(table);
			}

			$('<tr/>', {'data-dev_id': cur.dev_id})
				.append($('<td nowrap="">' + cur.municipality_name + ' - ' + cur.location_name + '</td>'))
				.append($('<td/>', {'data-col': 'dtr'}))
				.append($('<td/>', {'data-col': 'rv'}))
				.append($('<td/>', {'data-col': 'cr'})).appendTo(table);

			if (cur['status_id'] != null && cur['status_id'] != 0) {
				updateRainfallTable(cur['dev_id'], '[DISABLED]', '', '', 'disabled');
			}

		}
	}


	function initChartDivs(chartdiv) {
		var charts_container = document.getElementById(chartdiv);	
		var chart_wrapper = $('<div/>').attr({'class':'innerWrap'}).appendTo(charts_container);
		for(var i=0;i<waterlevel_devices.length;i++) {
			var device = waterlevel_devices[i];
			$('<div/>').attr({'id':'chart_div_'+device['dev_id'], 'class':'chartWithOverlay list divrowwrapper'})
				.append($('<p/>').addClass('overlay').text(device['municipality_name'] + ' - '+ device['location_name']))
				.append($('<div/>', {'id':"line-chart-marker_"+device['dev_id']}).addClass('chart'))
				.appendTo(chart_wrapper);

			var div = 'line-chart-marker_'+ device['dev_id'];
			if (device['status_id'] != null && device['status_id'] != 0) {
				$(document.getElementById(div)).css({'background':'url(images/disabled.png)'});
			}
		}	
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
				};
			
			datatable.addRow(row);
			
		}
		
		var d = Date.parseExact(json.data[json.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
		var d2 = Date.parseExact(json.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

		//var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
		var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
		var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');
		
		var options = {
          title: title_enddatetime ,

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
	  

	function updateRainfallTable(device_id, dateTimeRead, rainvalue, raincumulative, dataclass) {
		var tr = $('tr[data-dev_id=\''+device_id+'\']');
		var dtr = $('tr[data-dev_id=\''+device_id+'\'] td[data-col=\'dtr\']' );
		var rv = $('tr[data-dev_id=\''+device_id+'\'] td[data-col=\'rv\']' );
		var cr = $('tr[data-dev_id=\''+device_id+'\'] td[data-col=\'cr\']' );

		if (dateTimeRead != null) dtr.html(dateTimeRead); else dtr.text('');
		if (rainvalue != null ) rv.text(rainvalue); else rv.text('');
		if (raincumulative != null ) cr.text(raincumulative); else cr.text("");

		if (dataclass != 'undefined') {
			dtr.removeClass().addClass(dataclass);
			rv.removeClass().addClass(dataclass);
			cr.removeClass().addClass(dataclass);
		}

	}

	function clearRainfallTable() {
		for(var i=0;i<rainfall_devices.length;i++) {
			updateRainfallTable(rainfall_devices[i]['dev_id'], null, null, null)
		}
		
	}

	function addMarker(device_id, posx, posy, title, type, marker_url) {
		var pos = new google.maps.LatLng( posx, posy);
		var image = {
   			url: marker_url,
   			size: new google.maps.Size(32, 37),
   			origin: new google.maps.Point(0,0),
   			anchor: new google.maps.Point(16, 37)};

		var marker = new google.maps.Marker({
   			position: pos,
			icon: image,
    		map: cumulative_rainfall_map,
   			title:title + " (" + device_id + ")"}//,
			//url: server_name+base_url+'device/latest/'+ data.device[0].dev_id
		);

		cumulative_rainfall_map_markers.push(marker);

		google.maps.event.addListener(marker, 'click', function() {
			var tr = $('tr[data-dev_id=\''+device_id+'\']');
			var div =$('#chart_div_'+device_id + ' p');

    		$('#rainfall-canvas').scrollTo(tr, {duration:1000});
    		if (type == 'Waterlevel & Rain 2') {
				$('#charts_div_container').scrollTo(div, {duration:1000});
			}
    		tr.addClass('selected_device_tr');
    		div.addClass('selected_device_tr');
    		setTimeout(function() {
      			tr.removeClass('selected_device_tr');
      			div.removeClass('selected_device_tr');
			}, 3000);

		});
	}

	// Sets the map on all markers in the array.
	function setAllMap(map) {
	  for (var i = 0; i < cumulative_rainfall_map_markers.length; i++) {
	    cumulative_rainfall_map_markers[i].setMap(map);
	  }
	}

	// Removes the markers from the map, but keeps them in the array.
	function clearMarkers() {
	  setAllMap(null);
	}

	// Shows any markers currently in the array.
	function showMarkers() {
	  setAllMap(cumulative_rainfall_map);
	}

	// Deletes all markers in the array by removing references to them.
	function deleteMarkers() {
	  clearMarkers();
	  cumulative_rainfall_map_markers = [];
	}

	function updateWaterlevelChart(data) {
		var device_id = data.device[0].dev_id;
		var div = 'line-chart-marker_'+ device_id;
		if (data.count == -1 || // fmon.predict 404
			data.count == 0 || // sensor no reading according to fmon.predict
			data.data.length == 0 || // predict reports that it has reading but actually doesnt have
			data.data[0].waterlevel == null || data.data[0].waterlevel == '' // errouneous readings
		) {
			$(document.getElementById(div)).css({'background': 'url(images/nodata.png)'});
		} else {
			drawChartWaterlevel(div, data);
		}

	}

	function updateTemperatureTicker(data) {
		if (data.count == 0 || data.data == null || data.device[0].minmax['max'] == null) return;
		var text = "";
		var device_id = data.device[0].dev_id;
		var device = search(temperature_devices, 'dev_id', device_id);
		
		var municipality = device['municipality_name'];
		var location = device['location_name'];
		var max = data.device[0].minmax['max'];

		var time = search(data.data, 'air_temperature', max, true);

		//console.log(time);

		if (time != null) {
			var d = Date.parseExact(time['dateTimeRead'], 'yyyy-MM-dd HH:mm:ss');
			time = d.toString('h:mm tt'); 
		} else {
			time = 'unknown';
		}

		var current_time = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss').toString('h:mm tt');
		var current_temperature = parseFloat(data.data[0].air_temperature);
		var humidity = parseFloat(data.data[0].air_humidity);

		//console.log(current_temperature);
		//console.log(humidity);

		var heat_index = (HI.heatIndex({temperature: current_temperature, humidity: humidity})).toFixed(2);

		console.log(heat_index);

		text += "[" + current_time + ']: ' + municipality + ' - Temp/Heat Index: ' + current_temperature  + '/' + heat_index + ' \u2103. Max Temp: ' + max + ' last ' + time ;
		addTicker(text, 'ticker2list');
	}

	function addTicker(text, tickerlist) {
		$('<li/>').text(text).appendTo($('#' +tickerlist));
	}

	function clearAllTicker(tickerlist) {
		$('#' + tickerlist).empty();
	}

	function search(o, key, val, greedy) {
		var ret = null;
		for (var i=0; i<o.length;i++) {
			if (o[i][key] == val) {
				ret = o[i];
				if (!greedy) {
					break;
				}
			}
		}
		return ret;
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
			<li ><a href="#" class='currentPage'>Home</a></li>
			<li><a href="rainfall.php">Rainfall Monitoring</a></li>
			<li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
			<li><a href="waterlevel2.php">Waterlevel Map</a></li>
			<li><a href="devices.php">Devices Monitoring</a></li>
		</ul>
	</div>
	</div>
		
  	
</div>
<div id='content'>
		<div id='map-canvas'>
		</div>
		<div id='rainfall-canvas'>
		</div>
	<div id='legends'>
	</div>
	
</div>
<div id='footer'>
	<div>
		<div id="ticker1">
				<ul id='ticker1list'>
				</ul>	   
		</div>
		<div id="ticker2">
				<ul id='ticker2list'>
				</ul>	   
		</div>
	</div>
	<div id="charts_div_container">
	</div>
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
var waterlevel_devices = <?php echo json_encode(Devices::GetAllDevicesWithParameter('Waterlevel'));?>;
var temperature_devices = <?php echo json_encode(Devices::GetAllDevicesWithParameter('Temperature'));?>;
</script>
</html>