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

$(document).ready(function() {
	initMap("map");
	initDoppler();
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
  			draggableCursor:'crosshair',
  			styles: [{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#000000"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#000000"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":21}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":17}]}]
	};
		
	DOPPLER_MAP = new google.maps.Map(document.getElementById(divcanvas), mapOptions);
		
    google.maps.event.addListener(DOPPLER_MAP, 'click', function (event) {
        var pnt = event.latLng;
        var lat = pnt.lat();
        lat = lat.toFixed(6);
        var lng = pnt.lng();
        lng = lng.toFixed(6);
    });
}

function initDoppler() {
	$.ajax({
		dataType: "json",
		cache: false,
		url: "doppler_proxy.php",
		success: function(data){
			console.log(data);

			var bounds = new google.maps.LatLngBounds(new google.maps.LatLng(data["s"], data["w"]), new google.maps.LatLng(data["n"], data["e"]));
            var link = data["imageUrl"] + "?random=" + (new Date).getTime();     
            var options = {
            	clickable: false
            }

            var doppler_overlay = new google.maps.GroundOverlay(link, bounds, options);
            doppler_overlay.setMap(DOPPLER_MAP);

            var panes = DOPPLER_MAP.getPanes();

             console.log(panes);
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
</style>
</head>
<body>
	<div id="map">
	</div>
	<div id="controls" style="display:none;">
	</div>
</body>
</html>