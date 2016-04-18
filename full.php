<?php include_once 'lib/init.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DOST VI DRRMU </title>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&ext=.js&key=AIzaSyBIHIWYF28n_7UpQiud5ZNQP6C4G3LmTtU"></script>


<script type="text/javascript">
	

	google.maps.event.addDomListener(window, 'load', function() {
		initMap("map-canvas");
	});
	
	function initMap(divcanvas) {
		//var DOST_CENTER = new google.maps.LatLng(10.712317, 122.562362); //DOST CENTER
		var DOST_CENTER = new google.maps.LatLng(11.321510, 122.584763); 
	
		var mapOptions = {
  			//zoom: 6, //Whole Philippines View
  			zoom: 13, //Region 6 Focus,
  			minZoom:8,
  			maxZoom:null,
  			center: DOST_CENTER,
  			disableDefaultUI: true,
			  zoomControl: false,
  			draggableCursor:'crosshair',
        mapTypeId: google.maps.MapTypeId.SATELLITE,
        disableDoubleClickZoom: true,
  			//mapTypeId: google.maps.MapTypeId.ROADMAP,
  			scale:2
        //style : [ { "featureType": "road.local", "elementType": "labels.text", "stylers": [ { "visibility": "off" } ] },{ "featureType": "road.arterial", "elementType": "labels.text", "stylers": [ { "visibility": "off" } ] },{ "featureType": "administrative.neighborhood", "stylers": [ { "visibility": "off" } ] },{ "elementType": "labels.text", "stylers": [ { "visibility": "off" } ] },{ "elementType": "labels.icon", "stylers": [ { "visibility": "off" } ] },{ } ]
		};
		
		cumulative_rainfall_map = new google.maps.Map(document.getElementById(divcanvas), mapOptions);

    cropdata = new google.maps.Data();
  cropdata.loadGeoJson(DOCUMENT_ROOT + 'database/regionvi6_2.geojson');
  //cropdata.setMap(cumulative_rainfall_map);

  cropdata.setStyle(function(feature) {

    return /** @type {google.maps.Data.StyleOptions} */({
      fillColor: 'red',
      strokeWeight: 1,
      fillOpacity: 0
    });
  });
		
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
	        console.log(lat + ', '+lng);
	    });
	}	

	

</script>
</head>
<body style="padding:0px;margin:0px;">
<div id='header'>
  	
</div>
<div id='content'>
		<div id='map-canvas' style="padding:0px;margin:0px;width:12800px;height:11000px">
		</div>
		<div id='rainfall-canvas'>
		</div>
	<div id='legends'>
	</div>

</div>
<div id='footer'>


</div>
</body>

</html>