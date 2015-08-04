<?php include_once 'lib/init.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DOST VI DRRMU - Devices</title>
<script type="text/javascript" src='js/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='js/jquery.scrollTo.min.js'></script>
<link rel="stylesheet" type="text/css" href='css/style.css' />
<link rel="stylesheet" type="text/css" href='css/screen.css' />
<link rel="stylesheet" type="text/css" href='css/pages/devices.css' />

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBIHIWYF28n_7UpQiud5ZNQP6C4G3LmTtU&sensor=false"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

	var key = {'marker' : [
			   		{'name':'Rain1', 'src':'images/rain1'},
			   		{'name':'Rain2', 'src':'images/rain2'}, 
			   		{'name':'Waterlevel' , 'src':'images/waterlevel'}, 
			   		{'name':'Waterlevel & Rain 2', 'src':'images/waterlevel2'}, 
			   		{'name':'VAISALA', 'src':'images/vaisala'},
			   		{'name':'BSWM_Lufft', 'src':'images/vaisala'},
			   		{'name':'UAAWS', 'src':'images/vaisala'},
			   		{'name':'UPAWS', 'src':'images/vaisala'}
			   		]
			   	
			  };

	var devices_map;
	var devices_map_markers = [];
	var lastValidCenter;
	var ALLOWEDIT = false;
	var TRYAUTH = '';
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
      	initMarkers();
      	initControls('controls');
      	initControls2('controls2');

    });


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
  			//draggableCursor:'crosshair'
		}
		
		devices_map = new google.maps.Map(document.getElementById(divcanvas), mapOptions);
		
		// Bounds for region xi
        var strictBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(9.1895  , 119.1193), 
            new google.maps.LatLng(12.2171, 125.9308)
         );

        lastValidCenter = devices_map.getCenter();

        // Listen for the dragend event
        google.maps.event.addListener(devices_map, 'idle', function() {
            var minLat = strictBounds.getSouthWest().lat();
            var minLon = strictBounds.getSouthWest().lng();
            var maxLat = strictBounds.getNorthEast().lat();
            var maxLon = strictBounds.getNorthEast().lng();
            var cBounds  =devices_map.getBounds();
            var cMinLat = cBounds.getSouthWest().lat();
            var cMinLon = cBounds.getSouthWest().lng();
            var cMaxLat = cBounds.getNorthEast().lat();
            var cMaxLon = cBounds.getNorthEast().lng();
            var centerLat = devices_map.getCenter().lat();
            var centerLon = devices_map.getCenter().lng();

            if((cMaxLat - cMinLat > maxLat - minLat) || (cMaxLon - cMinLon > maxLon - minLon))
            {   //We can't position the canvas to strict borders with a current zoom level
                //devices_map.setZoom(devices_map.getZoom()+1);
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
                //devices_map.setCenter(new google.maps.LatLng(newCenterLat, newCenterLon));
        		devices_map.panTo(new google.maps.LatLng(newCenterLat, newCenterLon));
        });

        google.maps.event.addListener(devices_map, 'click', function (event) {
            var pnt = event.latLng;
            var lat = pnt.lat();
	        lat = lat.toFixed(6);
	        var lng = pnt.lng();
	        lng = lng.toFixed(6);
	        console.log("Latitude: " + lat + "  Longitude: " + lng);               
	    });
	}

	function initMarkers() {
		for (var i = 0; i < devices.length; i++) {
			device = devices[i];
			var marker = createMarker(device);
			addMarkerToMap(marker);
					
	 	}
	}	

	function initMapLegends(container) {
		legendscontainer = $(document.getElementById(container));
		devices_map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(document.getElementById(container));
		
		$('<button id="togglelegends">Hide Legend</button>')
			.on('click', function() {
				$('.legend').toggle();
                if ($(this).text() == "Show Legend") {
                    $(this).text('Hide Legend');
                } else {
                    $(this).text('Show Legend');
                }
			})
			.appendTo(legendscontainer);
        $('<div class="legendtitle">Devices in Western Visayas</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][0].src+'.png" > Automatic Rain Gauge</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][1].src+'.png" > Automatic Rain Gauge w/ Air Pressure</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][2].src+'.png" > Waterlevel</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][3].src+'.png" > Waterlevel w/ Automatic Rain Gauge</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+key['marker'][4].src+'.png" > VAISALA, UAAWS, or BSWM_Lufft</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="images/overlay_notok.png" > Status Not Ok</div>').appendTo(legendscontainer);
	}

	function initControls(container) {
		controlscontainer = $(document.getElementById(container));
		devices_map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(document.getElementById(container));
		$('<button id="showall">Show All</button>')
			.on('click', function() {
				showMarkerWithStatusId('all');
			})
			.appendTo(controlscontainer);
		$('<button id="showall">Show only OK</button>')
			.on('click', function() {
				showMarkerWithStatusId('ok');
			})
			.appendTo(controlscontainer);
		$('<button id="showall">Show only NOT OK</button>')
			.on('click', function() {
				showMarkerWithStatusId('notok');
			})
			.appendTo(controlscontainer);
	}

	function initControls2(container) {
		controlscontainer = $(document.getElementById(container));
		devices_map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById(container));
		var unlockediting = $('<button>Unlock Editing</button>');

		unlockediting.on('click', function() {
			if (!ALLOWEDIT) {
				var ans = prompt('[TODO] Enter passphrase: (*hint: macbookair pass)');
				if (ans != null) {
					$.ajax({
						url: DOCUMENT_ROOT + 'auth.php',
						type: "POST",
						data: {tryauth: ans
						},
						dataType: 'json',
					}).done(function(data) {
						if (data['success']) {
							unlockediting.text('Lock Editing');
							ALLOWEDIT = true;
							TRYAUTH = ans;
						} else {
							alert('Sorry. Wrong passphrase.');
						}
					});
				}

			} else {
				unlockediting.text('Unlock Editing');
				ALLOWEDIT = false;
				TRYAUTH = '';
			}
		})
		.appendTo(controlscontainer);
	}

	function createMarker(device) {
		var device_id = device['dev_id'];
		var posx = device['posx'];
		var posy = device['posy'];
		var type = device['type_name'];
		var status_id = device['status_id'];
		var title = device['municipality_name'] + ' - ' + device['location_name'];

		var image = createIcon(type, status_id);
		var pos = new google.maps.LatLng( posx, posy);
		
		var marker = new google.maps.Marker({
   			position: pos,
			icon: image,
   			title:title + " (" + device_id + ")",
   			dev_id: device_id,
   			type: type}
		);

		attachMarkerClickEvent(marker, device_id, status_id);

		return marker;
	}

	function createIcon(type, status_id) {
		var marker_url = 'http://maps.google.com/mapfiles/ms/icons/red-dot.png';

		var obj = search(key['marker'], 'name', type);
		if (obj != null) {
			if (status_id == null || status_id == 0) {
				marker_url =  obj['src'] +'.png';
			} else if (status_id == 1) {
				marker_url = obj['src'] +'-notok.png';
			} 
		} else {
			console.log('no icon for ' + type);
		}
		var image = {
   			url: marker_url,
   			size: new google.maps.Size(32, 37),
   			origin: new google.maps.Point(0,0),
   			anchor: new google.maps.Point(16, 37)};
   		return image;
	}
	
	function addMarkerToMap(marker) {
		marker.setMap(devices_map);
		devices_map_markers.push(marker);
	}

	function attachMarkerClickEvent(marker, dev_id, status_id) {
		google.maps.event.addListener(marker, 'click', function() {
			if (ALLOWEDIT == true) {
			
				var newstatus_id;
				if (status_id == null || status_id == 0) {
					newstatus_id = 1;
				} else if(status_id == 1) {
					newstatus_id = 0;
				} else {
					newstatus_id = null;
				}

				postUpdateDeviceStatus(dev_id, newstatus_id);
			} else {
				console.log('Action not allowed!');
			}
		});
	}

	function showMarkerWithStatusId(option) {
		for (var i=0;i<devices_map_markers.length;i++) {
			if (option == 'all') {
				devices_map_markers[i].setMap(devices_map);
			} else {
				device_marker =  devices_map_markers[i];
				device_id = device_marker['dev_id'];
				device = search(devices, 'dev_id', device_id);
				device_status_id = null;

				if (device != null) {
					device_status_id = device['status_id'];
					switch (option) {
						case 'ok':
							if (device_status_id == null || device_status_id == 0) {
								device_marker.setMap(devices_map);
							} else {
								device_marker.setMap(null);
							}
							break;
						case 'notok':
							if (device_status_id == 1) {
								device_marker.setMap(devices_map);
							} else {
								device_marker.setMap(null);
							}
							break;
					}
					
				}

			}

		}
	}



	function postUpdateDeviceStatus(dev_id, status_id) {
		$.ajax({
			url: DOCUMENT_ROOT + 'update.php',
			type: "POST",
			data: {dev_id: dev_id,
		  		status_id: status_id,
				tryauth: TRYAUTH
		  	},
			dataType: 'json',
			})
			.done(onSuccessPostUpdate)
			.fail(onFailPostUpdate);
	}

	function onSuccessPostUpdate(data) {
		var device_id = data['dev_id'];
		var status_id = data['status_id'];

		var device_marker = search(devices_map_markers, 'dev_id', device_id);
		if (device_marker != null) {
			var type = device_marker['type'];

			var image = createIcon(type, status_id);
			device_marker.setIcon(image);
			google.maps.event.clearListeners(device_marker, 'click');
			attachMarkerClickEvent(device_marker, device_id, status_id);


		}

		var device = search(devices, 'dev_id', device_id);
		if (device != null) {
			device['status_id'] = status_id;
		}


	}

	function onFailPostUpdate(data) {
		console.log('POST fail');
	}

	


	function search(o, key, val) {
		for (var i=0; i<o.length;i++) {
			if (o[i][key] == val) {
				return o[i];
			}
		}
		return null;
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
			<li><a href="#"  class='currentPage'>Devices Monitoring</a></li>
		</ul>
	</div>
	</div>
		
  	
</div>
<div id='content'>
	<div id='map-canvas'>
	</div>
	<div id='legends'>
	</div>
	<div id='controls'>
	</div>
	<div id='controls2'>
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
var devices = <?php echo json_encode(Devices::getAllDevices());?>;
</script>
</html>