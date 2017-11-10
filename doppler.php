<?php include_once 'lib/init.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DOST VI DRRMU - Doppler</title>
<script type="text/javascript" src='js/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&sensor=false"></script>
<script type="text/javascript">

var DOPPLER_MAP;
var CURRENT_OVERLAY;

$(document).ready(function() {
	initMap("map");
	initDoppler();
});


function initMap(divcanvas) {
	var DOST_CENTER = new google.maps.LatLng(10.712317, 122.562362);
	
	var mapOptions = {
  			zoom: 7,
  			//minZoom:8,
  			maxZoom:null,
  			center: DOST_CENTER,
  			disableDefaultUI: true,
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.LARGE,
				position: google.maps.ControlPosition.RIGHT_CENTER
			},
  			draggableCursor:'crosshair',
  			styles: [{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#000000"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#000000"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":21}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":17}]}]
	};
		
	DOPPLER_MAP = new google.maps.Map(document.getElementById(divcanvas), mapOptions);
    DOPPLER_MAP.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(document.getElementById('controls'));

    /*google.maps.event.addListener(DOPPLER_MAP, 'click', function (event) {
        var pnt = event.latLng;
        var lat = pnt.lat();
        lat = lat.toFixed(6);
        var lng = pnt.lng();
        lng = lng.toFixed(6);
    });*/
}

function initDoppler() {
	$.ajax({
		dataType: "json",
		cache: false,
		url: "doppler_proxy.php",
		success: function(data){
			var result = data['result'];
			var dbounds = JSON.parse(result['bounds']);
			var bounds = new google.maps.LatLngBounds(new google.maps.LatLng(dbounds["s"], dbounds["w"]), new google.maps.LatLng(dbounds["n"], dbounds["e"]));

			console.log(result['data']);

			var el = document.getElementById('controls');
			$.each(result['data'], function(k, v) {
			    var time = v['time_mosaic'];
			    var overlay_image = v['output_image_transparent_on_www'];
                var doppler_overlay = new google.maps.GroundOverlay(overlay_image, bounds, {clickable: false});

			    if (time) {
                    $('<button/>', {id: k, name: k, text: time}).appendTo(el)
                        .on('click', function() {
                            CURRENT_OVERLAY.setMap(null);
                            doppler_overlay.setMap(DOPPLER_MAP)
                            CURRENT_OVERLAY = doppler_overlay;
                        });
                } else {
                    $('<button/>', {id: k, name: k, text: "Animated"}).prependTo(el)
                        .on('click', function() {
                            CURRENT_OVERLAY.setMap(null);
                            doppler_overlay.setMap(DOPPLER_MAP)
                            CURRENT_OVERLAY = doppler_overlay;
                        });
                    doppler_overlay.setMap(DOPPLER_MAP)
                    CURRENT_OVERLAY = doppler_overlay;
                }

            });
		}
	});
}
</script>
<style>
	body {background-color: black;}
	p    {color: white;}
	#map {
		width:800px;
		height:768px;
		margin:0 auto;
		background-color:#0C0C0C;
	}
    #map:hover {
        -webkit-animation-play-state: paused; /* Chrome, Safari, Opera */
        animation-play-state: paused;
    }
    #controls {
        display: inline-table;
        white-space: nowrap;
    }

    #controls > button {
        font-size: smaller;
        height:30px;
        margin-bottom: 24px;
        vertical-align:middle;
        padding: 2px;
    }
</style>
</head>
<body>
	<div id="map">
	</div>
	<div id="controls">
	</div>
</body>
</html>