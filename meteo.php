<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="vendor/materialize-0.100.2/css/materialize.min.css"
          media="screen,projection"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOST VI DRRMU - PAGASA-Meteopilipinas Proxy</title>
    <style>
        body {
            overflow: hidden;
        }

        .progress {
            margin: 0;
        }

        #map {
            margin: 0;
            padding: 0;
            height: calc(100vh - 84px);
            background-color: #2ea7c5;
        }

        .mapbox-maplogo {
            position: absolute;
            display: block;
            height: 20px;
            width: 65px;
            left: 80px;
            bottom: 22px;
            text-indent: -9999px;
            z-index: 99999;
            overflow: hidden;
            background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIIAAAAoCAMAAAAFWtJHAAAAwFBMVEUAAAAAAAAAAABtbW0AAAAAAAAAAAAAAAAAAAAAAAClpaUAAADp6ekAAAD5+fna2toAAAAMDAzv7+/Nzc0AAAA2Njb8/Pz9/f3T09MAAAAAAAD7+/sAAAArKyuxsbH39/fs7OwbGxuIiIjz8/N8fHyenp7u7u74+PgAAAC8vLxWVlbx8fF1dXXl5eVcXFyUlJTQ0NDFxcVCQkLAwMC4uLj19fXo6OjW1tarq6ve3t77+/vi4uL6+vrKysrNzc3///8w7gSSAAAAP3RSTlMAOQNdPSYBPywKexLLGPCxNEHXnzFL+v2nGwf1IEiE6dBFad9jd9PuLo1V2mDDV3Cjl06SiuXIq4C3973ym6BQMVUPAAAEXElEQVR4Ae2WCVP6OBiH05L0l1IqrVbkKHJ54I0oHn+PfP9vtUle0z/YdhbH2XVnd58ZnRJIeHiPJOx//mH4vQSAN+8FjAhFxgHIaPvJeZ99hxwEElon5iAQbj85Y98g8ODwjEOMAvGFyeE3FEKgodTBqj0BJGN9DhyNd5Ta3ean9QEopfaA+LsKhnEKRExqg4FSP6Og7oEkAjBWnxSCgBX4xF+kcLoPcOBQrSv0e5kH7s1j37jECQieCTPiFGxL5VHw2zQWCeeJiPt6kjRQw0XSkIdVChf67xGa4alSnZlT6HEQ8CK9ANbhvXUF9xlDkBfTuHDWScgC9+z5FQpPI12TlwC6+sV7ixR8CUMKiwjm2GQeOQWHMGuHGdbnObJAwCEqFJpNU5H6uaPUaEIKiQfg+PHk1+u4OwW9PlWW2ctbA4BHCtp+cNK+H8Jos4gDmC5ar4Nx9waaG/2B13NgDqS7+vm2RgEtEws82P+kwIHhs/pgkQKcFIhfd7CogtGNjYMHTLpurD0ERbYFw4JaD3GlQuNAL/JEsSAF4HqlCnaHACk4WhOn4OgCkMD5hSpYNYDJTD8Y46n+jsE1kPhVCuR6QBXhFK7MUOu9O6b1SWF3b+/9ZVWMGOlu93E8UDaAhgc7bfH+0DHqKXCkHzoNDFfU+zxiVQrUC9QXTuHYtKpN59OA3IxCG4b7jh6ZFuVockaNTW09mkJzOaPU49a6mE9cAchZpQJNpUWcwgV9r6FJswsFKrITp2B5pMBMdnS0z2HZNy2+BNKxSZxZfglkrFYBJxQnpzA5sN/HheR2aFQoZBLAi149dQoyAYYjW0hHlHguBAdMcR0DuDZ5omevX6+AI8qcU7ikKT3GBHCnXwydgmCC0tRwCnGQ2Wp6Be71yNIWfQSkOl9vAI1SBCNWrwC01RROgX7BuT2HI4r7tFAw086p/NwZEdOEa7R1uAFuNmQPuKAEAjYNQ0CyeoUEWHYBnpQVQgpvc0Ph+gsKlAnKg1+vEHsw5LKciLKCAJobiWBzYFGbCKpHqkZZrxBFHEASyFI59vJPCskcwNVGOWZAOqsrR+pKbaNeAMT1CixMEtlnsqopNxUMzVJT3tY35aXZm6a6Y9QhwMN6BUJWbE1lhbMO1WehkO7poO0sK7em9MJGxp1XSbC1gtugzzSLQmGsX7VntJGSwsPZ2d2z3bIPKzdoOp3Wzqt8G4XyMVUoFIxLx1S7+piaHtCvR3FeRVsq0GFdp9C5TbGpcNqsPqyHKxcfd14h21KhuLKUFU4f3osrC7F6uV3WXFnadL7wyAPeKDXw2RoJCO5GY4DouYvb/gepVXheLoewzPseQG9N/vzilrMIjoStE3++zvle4eSurw7XEe76ynI4aq+v7lEyt1x5awiFlFLQbHKIpabnM3eJLym4Szzzc/du7SU+zOXv9UNpECH7IoH/gecURPlN9vdQpeD47yhIFNX0U0QgvID9nENm+yxk/xb+AGAjNfRZuk9qAAAAAElFTkSuQmCC);
            background-repeat: no-repeat;
            background-position: 0 0;
            background-size: 65px 20px;
        }

        #disclamer {
            width: 100vw;
            height: 20px;
            background-color: #000058;

        }

        div[id="disclamer"] > span,
        div[id="disclamer"] > a {
            font-size: small;
            white-space: nowrap;
            color: #2b6bd5;
        }

        #doppler_time_selector {
            position: absolute;
            right: 20px;
            top: 80px;
            overflow: hidden;
        }

        @media only screen and (min-width: 768px) {
            #map {

            }

            .brand-logo {
                margin-left: 20px;
            }
        }
    </style>
</head>
<body>
<ul id="dropdown1" class="dropdown-content">
    <li><a id="nav_phdoppler" href="#!">PH Doppler</a></li>
    <li><a id="nav_himwari" href="#!">Himawari Satellite</a></li>
    <li><a id="nav_tytrack" href="#!">Typhoon Track</a></li>
</ul>
<nav>
    <div class="nav-wrapper">
        <a href="#!" class="brand-logo">BP2</a>
        <ul class="right">
            <!-- Dropdown Trigger -->
            <li><a class="dropdown-button" href="#!" data-activates="dropdown1">Start Here<i
                            class="material-icons right">arrow_drop_down</i></a></li>
        </ul>
    </div>
</nav>
<div class="progress">
    <div class="indeterminate"></div>
</div>
<div id="map">

</div>
<a id="doppler_time_selector" class="btn-floating btn-large waves-effect waves-light red btn modal-trigger"
   href="#modal1"><i class="material-icons">access_time</i></a>
<div id="controls"></div>
<a href="http://mapbox.com/about/maps" class='mapbox-maplogo' target="_blank">MapBox</a>
<div id="disclamer"><span>powered by DOST-PAGASA </span><a href="https://v2.meteopilipinas.gov.ph" target="_blank">METEOPILIPINAS</a>
</div>
<div id="modal1" class="modal modal-fixed-footer">
    <div class="modal-content">
        <div id="doppler_times" class="collection">
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat ">OK</a>
    </div>
</div>
<script type="text/javascript" src="//code.jquery.com/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="vendor/materialize-0.100.2/js/materialize.min.js"></script>
<script async defer
        src="//maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&callback=initMap">
</script>
<script src="vendor/gmaps/gmaps.js"></script>
<script>
    $(document).ready(function () {
        // the "href" attribute of the modal trigger must specify the modal ID that wants to be triggered
        $('.modal').modal();

        $.ajaxSetup({
            beforeSend: function (jqXHR) {
                $('.progress').show();
            },
            stop: function (jqXHR) {
                $('.progress').hide();
            }
        });

    });

    var METEO_MAP;
    var CURRENT_OVERLAY;
    var lines = [];
    var cyclone_marker_array = [];
    var hourly_cyclone_marker_array = [];
    var forecastCircles_array = [];
    var cyclonePath_array = [];
    var cyclonePath_LatLng = [];
    var forecastLine_array = [];
    var forecastHull_array = [];

    function initMap() {
        //var dost_center = new google.maps.LatLng(10.712317, 122.562362);
        //var dost_center = new google.maps.LatLng(, );
        var map = new GMaps({
            el: '#map',
            lat: 13,
            lng: 122,
            zoom: 6,
            mapTypeId: 'mapbox',
            disableDefaultUI: true,
        });
        /*var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 6,
            minZoom: 6,
            center: dost_center,
            mapTypeId: 'mapbox',
            disableDefaultUI: true,
        });*/

        /*map.mapTypes.set("mapbox", new google.maps.ImageMapType({
            getTileUrl: function (coord, zoom) {
                var tilesPerGlobe = 1 << zoom
                    , x = coord.x % tilesPerGlobe;
                if (x < 0)
                    x = tilesPerGlobe + x;
                return "//api.mapbox.com/styles/v1/dost6ryanb/cjcipbquu0khs2rqrlgcz44y7/tiles/256/" + zoom + "/" + x + "/" + coord.y + "?access_token=pk.eyJ1IjoiZG9zdDZyeWFuYiIsImEiOiI1OGMyZjdjNjZlYjlhNTMyNDc0NGQxOTY4ZDJlZjIxNyJ9.dkASVYIEPInwAEkwUkaGhQ";
            },
            tileSize: new google.maps.Size(256, 256),
            name: "MapBox",
            maxZoom: 18
        }));*/

        map.addMapType("mapbox", {
            getTileUrl: function (coord, zoom) {
                var tilesPerGlobe = 1 << zoom
                    , x = coord.x % tilesPerGlobe;
                if (x < 0)
                    x = tilesPerGlobe + x;
                return "//api.mapbox.com/styles/v1/dost6ryanb/cjcipbquu0khs2rqrlgcz44y7/tiles/256/" + zoom + "/" + x + "/" + coord.y + "?access_token=pk.eyJ1IjoiZG9zdDZyeWFuYiIsImEiOiI1OGMyZjdjNjZlYjlhNTMyNDc0NGQxOTY4ZDJlZjIxNyJ9.dkASVYIEPInwAEkwUkaGhQ";
            },
            tileSize: new google.maps.Size(256, 256),
            name: "MapBox",
            maxZoom: 18
        });

        //map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('controls'));
        var el = document.getElementById('controls').innerHTML;
        map.addControl({
            position: 'top_center',
            content: el,
            events: {
                click: function () {
                    console.log(this);
                }
            }
        });

        hideControls();

        METEO_MAP = map;
        attachDropdDownHandlers();
    }

    function attachDropdDownHandlers() {
        $("#nav_phdoppler").click(function () {
            console.log("Calling doppler()");
            doppler();
        });
        $("#nav_himwari").click(function () {
            console.log("Calling satellite()");
            satellite();
        });
        $("#nav_tytrack").click(function () {
            console.log("Calling cytrack()");
            cytrack();
        });
    }

    function doppler() {
        $.getJSON("meteo_proxy.php", {rq: 'ph-doppler'})
            .done(function (data) {
                hideControls();
                $("#doppler_time_selector").show();
                var result = data['result'];
                var dbounds = JSON.parse(result['bounds']);
                var bounds = new google.maps.LatLngBounds(new google.maps.LatLng(dbounds["s"], dbounds["w"]), new google.maps.LatLng(dbounds["n"], dbounds["e"]));

                var el = document.getElementById('doppler_times');
                $(el).empty();
                $.each(result['data'], function (k, v) {
                    var time = v['time_mosaic'];
                    var overlay_image = v['output_image_transparent_on_www'];
                    var doppler_overlay = new google.maps.GroundOverlay(overlay_image, bounds, {clickable: false});

                    if (time) {
                        $('<a/>', {id: k, name: k, text: time, class: "collection-item", href: "#!"}).appendTo(el)
                            .on('click', function () {
                                swapCurrentOverlay(doppler_overlay)
                                $('#modal1').modal('close');
                            });
                    } else {
                        $('<a/>', {
                            id: k,
                            name: k,
                            text: "Animated",
                            class: "collection-item",
                            href: "#!"
                        }).attr("active", '').prependTo(el)
                            .on('click', function () {
                                swapCurrentOverlay(doppler_overlay)
                                $('#modal1').modal('close');
                            });
                        swapCurrentOverlay(doppler_overlay)
                    }

                });
            });
    }

    function satellite() {
        $.getJSON("meteo_proxy.php", {rq: 'sat-himawari'})
            .done(function (data) {
                hideControls();
                var result = data['result'];
                var bounds = JSON.parse(result.bounds);
                console.log(result);
                var swBound = new google.maps.LatLng(bounds.s, bounds.w);
                var neBound = new google.maps.LatLng(bounds.n, bounds.e);
                var imageBounds = new google.maps.LatLngBounds(swBound, neBound);
                console.log(imageBounds);

                var satImg = result.animated_image;
                var sat_overlay = new google.maps.GroundOverlay(satImg, imageBounds);
                console.log(satImg);
                console.log(sat_overlay);
                swapCurrentOverlay(sat_overlay);
            });
    }

    function cytrack() {
        $.getJSON('meteo_proxy.php', {rq: 'cyclone-track'})
            .done(function (d) {
                var tracks = d['result'];
                var value = !"hourly";
                hideControls();
                var lastTrack = null
                    , forecastTrack = [];

                if (lines.length != 0) {
                    lines = [];
                    cyclone_marker_array = [];
                    hourly_cyclone_marker_array = [];
                    forecastCircles_array = [];
                    cyclonePath_array = [];
                    cyclonePath_LatLng = [];
                    forecastLine_array = [];
                    //if (value == "clear")
                    //    return
                }

                for (var key in tracks) {
                    var data = tracks[key], cycloneName = data.cyclone_name, cycloneInfos = data.info, lastPoint,
                        lastTrack = null, forecastTrack = [];
                    console.log(data);
                    for (var key in cycloneInfos) {
                        var cycloneInfo = cycloneInfos[key];
                        cyclonePath_array.push(new google.maps.LatLng(cycloneInfo.latitude, cycloneInfo.longitude));
                        cyclonePath_LatLng.push([cycloneInfo.latitude, cycloneInfo.longitude]);
                        var image = new google.maps.MarkerImage(cycloneInfo.icon, null, new google.maps.Point(0, 0), new google.maps.Point(13, 13))
                            , cyclone_marker = METEO_MAP.addMarker({
                            lat: cycloneInfo.latitude,
                            lng: cycloneInfo.longitude,
                            title: cycloneName,
                            icon: image,
                            infoWindow: {
                                content: "<p>" + cycloneName + "</p>as of: " + cycloneInfo.dateTime + "<br/>Coordinates: " + cycloneInfo.latitude + "° " + cycloneInfo.longitude + "°"
                            }
                        });
                        if (value == "hourly") {
                            hourly_cyclone_marker_array.push(cyclone_marker)
                        } else
                            cyclone_marker_array.push(cyclone_marker);
                        if (cycloneInfo.isForecast == "true") {
                            forecastTrack.push(cycloneInfo)
                        } else {
                            var lastPoint = new google.maps.LatLng(cycloneInfo.latitude, cycloneInfo.longitude);
                            lastTrack = cycloneInfo
                        }
                    }

                    var lineSymbol = {
                        path: 'M -2,0 0,-2 2,0 0,2 z',
                        strokeColor: '#FFF',
                        fillColor: '#F00',
                        fillOpacity: 1
                    };
                    if (forecastTrack.length > 0)
                        polygon = METEO_MAP.drawPolygon({
                            paths: nfc(forecastTrack, lastTrack),
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            strokeColor: '#00868B'
                        });
                    line = METEO_MAP.drawPolyline({
                        path: ra(cyclonePath_LatLng),
                        strokeColor: '#008000',
                        strokeOpacity: 1,
                        strokeWeight: 2,
                        icons: [{
                            icon: lineSymbol,
                            offset: '100%'
                        }]
                    });
                    for (var key in forecastTrack) {
                        var cycloneInfo = forecastTrack[key]
                            , cyclone_circles = METEO_MAP.drawCircle({
                            lat: cycloneInfo.latitude,
                            lng: cycloneInfo.longitude,
                            radius: cycloneInfo.radius * 1e3,
                            fillColor: cycloneInfo.color,
                            fillOpacity: 0.3,
                            strokeOpacity: 0.5,
                            strokeWeight: 1
                        });
                        forecastCircles_array.push(cyclone_circles);
                        //addToCoordinates(cyclone_circles);
                    }
                    ;lines.push(line);
                    if (typeof polygon != 'undefined')
                        forecastHull_array.push(polygon)
                }

            });
    }

    function hideControls() {
        $("#doppler_time_selector").hide();
        $('.progress').hide();
        clearCyTracks();
    }

    function swapCurrentOverlay(overlay) {
        if (CURRENT_OVERLAY) {
            CURRENT_OVERLAY.setMap(null);
            overlay.setMap(METEO_MAP.map);
            CURRENT_OVERLAY = overlay;
        } else {
            overlay.setMap(METEO_MAP.map);
            CURRENT_OVERLAY = overlay;
        }
    }

    function setMapNullArray(markers) {
        for (var key in markers)
            markers[key].setMap(null);
        markers.splice(0, markers.length);
        markers = []
    }

    /*function addToCoordinates(obj) {
        google.maps.event.addListener(obj, 'mousemove', function (event) {
            displayCoordinates(event.latLng)
        })
    }*/

    function ConvexHullGrahamScan(){this.anchorPoint=void(0),this.reverse=!1,this.points=[]};ConvexHullGrahamScan.prototype={constructor:ConvexHullGrahamScan,Point:function(a,b){this.x=a,this.y=b},_findPolarAngle:function(a,b){var c=57.295779513082,d=b.x-a.x,e=b.y-a.y;if(0==d&&0==e)return 0;var f=Math.atan2(e,d)*c;return this.reverse?0>=f&&(f+=360):f>=0&&(f+=360),f},addPoint:function(a,b){if(void(0)===this.anchorPoint){this.anchorPoint=new this.Point(a,b)}else if(this.anchorPoint.y>b||this.anchorPoint.y==b&&this.anchorPoint.x>a)return this.anchorPoint.y=b,this.anchorPoint.x=a,void(this.points.unshift(new this.Point(a,b)));this.points.push(new this.Point(a,b))},_sortPoints:function(){var a=this;return this.points.sort(function(b,c){var d=a._findPolarAngle(a.anchorPoint,b),e=a._findPolarAngle(a.anchorPoint,c);return e>d?-1:d>e?1:0})},_checkPoints:function(a,b,c){var d,e=this._findPolarAngle(a,b),f=this._findPolarAngle(a,c);return e>f?(d=e-f,!(d>180)):f>e?(d=f-e,d>180):!1},getHull:function(){var a,b,c=[];if(this.reverse=this.points.every(function(a){return a.x<0&&a.y<0}),a=this._sortPoints(),b=a.length,4>b)return a;for(c.push(a.shift(),a.shift());;){var d,e,f;if(c.push(a.shift()),d=c[c.length-3],e=c[c.length-2],f=c[c.length-1],this._checkPoints(d,e,f)&&c.splice(c.length-2,1),0==a.length){if(b==c.length)return c;a=c,b=a.length,c=[],c.push(a.shift(),a.shift())}}}},"function"==typeof define&&define.amd&&define(function(){return ConvexHullGrahamScan}),"undefined"!=typeof module&&(module.exports=ConvexHullGrahamScan)
    function equals(e,t){var i;for(i in e)if("undefined"==typeof t[i])return!1;for(i in e)if(e[i]){switch(typeof e[i]){case"object":if(!equals(e[i],t[i]))return!1;break;case"function":if("undefined"==typeof t[i]||"equals"!=i&&e[i].toString()!=t[i].toString())return!1;break;default:if(e[i]!=t[i])return!1}}else if(t[i])return!1;for(i in t)if("undefined"==typeof e[i])return!1;return!0};var gpcas=gpcas||{};gpcas.util={},gpcas.geometry={};var Point=function(e,t){this.x=e,this.y=t};gpcas.util.ArrayHelper=function(){};var static=gpcas.util.ArrayHelper;static.create2DArray=function(e){for(var t=[],i=0;e>i;i++)t[i]=[];return t},static.valueEqual=function(e,t){return e==t?!0:equals(e,t)?!0:!1},static.sortPointsClockwise=function(e){var t=!1;e instanceof ArrList&&(e=e.toArray(),t=!0);for(var i,o=null,n=null,l=null,r=null,p=e,a=0;a<e.length;a++){var s=e[a];(null==o||o.y>s.y||o.y==s.y&&s.x<o.x)&&(o=s),(null==n||n.y<s.y||n.y==s.y&&s.x>n.x)&&(n=s),(null==l||l.x>s.x||l.x==s.x&&s.y>l.y)&&(l=s,i=a),(null==r||r.x<s.x||r.x==s.x&&s.y<r.y)&&(r=s)};if(i>0){p=[];for(var u=0,a=i;a<e.length;a++)p[u++]=e[a];for(var a=0;i>a;a++)p[u++]=e[a];e=p};for(var y=!1,a=0;a<e.length;a++){var s=e[a];if(equals(s,n)){y=!0;break};if(equals(s,o))break};if(y){p=[],p[0]=e[0];for(var u=1,a=e.length-1;a>0;a--)p[u++]=e[a];e=p};return t?new ArrList(e):e};var ArrayHelper=gpcas.util.ArrayHelper;gpcas.util.ArrList=function(e){this._array=[],null!=e&&(this._array=e)};var p=gpcas.util.ArrList.prototype;p.add=function(e){this._array.push(e)},p.get=function(e){return this._array[e]},p.size=function(){return this._array.length},p.clear=function(){this._array=[]},p.equals=function(e){if(this._array.length!=e.size())return!1;for(var t=0;t<this._array.length;t++){var i=this._array[t],o=e.get(t);if(!ArrayHelper.valueEqual(i,o))return!1};return!0},p.hashCode=function(){return 0},p.isEmpty=function(){return 0==this._array.length},p.toArray=function(){return this._array},gpcas.geometry.Clip=function(){},gpcas.geometry.Clip.DEBUG=!1,gpcas.geometry.Clip.GPC_EPSILON=2.220446049250313e-16,gpcas.geometry.Clip.GPC_VERSION="2.31",gpcas.geometry.Clip.LEFT=0,gpcas.geometry.Clip.RIGHT=1,gpcas.geometry.Clip.ABOVE=0,gpcas.geometry.Clip.BELOW=1,gpcas.geometry.Clip.CLIP=0,gpcas.geometry.Clip.SUBJ=1;var p=gpcas.geometry.Clip.prototype,static=gpcas.geometry.Clip;static.intersection=function(e,t,i){return(null==i||void(0)==i)&&(i="PolyDefault"),Clip.clip(OperationType.GPC_INT,e,t,i)},static.union=function(e,t,i){return(null==i||void(0)==i)&&(i="PolyDefault"),Clip.clip(OperationType.GPC_UNION,e,t,i)},static.xor=function(e,t,i){return(null==i||void(0)==i)&&(i="PolyDefault"),Clip.clip(OperationType.GPC_XOR,e,t,i)},static.difference=function(e,t,i){return(null==i||void(0)==i)&&(i="PolyDefault"),Clip.clip(OperationType.GPC_DIFF,t,e,i)},static.intersection=function(e,t){return Clip.clip(OperationType.GPC_INT,e,t,"PolyDefault.class")},static.createNewPoly=function(e){return"PolySimple"==e?new PolySimple():"PolyDefault"==e?new PolyDefault():"PolyDefault.class"==e?new PolyDefault():null},static.clip=function(e,t,i,o){var n=Clip.createNewPoly(o);if(t.isEmpty()&&i.isEmpty()||t.isEmpty()&&(e==OperationType.GPC_INT||e==OperationType.GPC_DIFF)||i.isEmpty()&&e==OperationType.GPC_INT)return n;e!=OperationType.GPC_INT&&e!=OperationType.GPC_DIFF||t.isEmpty()||i.isEmpty()||Clip.minimax_test(t,i,e);var l=new LmtTable(),r=new ScanBeamTreeEntries(),p=null,a=null;if(t.isEmpty()||(p=Clip.build_lmt(l,r,t,Clip.SUBJ,e)),Clip.DEBUG&&l.print(),i.isEmpty()||(a=Clip.build_lmt(l,r,i,Clip.CLIP,e)),Clip.DEBUG&&l.print(),null==l.top_node)return n;var s=r.build_sbt(),u=[];u[0]=Clip.LEFT,u[1]=Clip.LEFT,e==OperationType.GPC_DIFF&&(u[Clip.CLIP]=Clip.RIGHT),Clip.DEBUG;for(var y=l.top_node,g=new TopPolygonNode(),C=new AetTree(),c=0;c<s.length;){var d=s[c++],f=0,m=0;if(c<s.length&&(f=s[c],m=f-d),null!=y&&y.y==d){for(var P=y.first_bound;null!=P;P=P.next_bound)Clip.add_edge_to_aet(C,P);y=y.next};Clip.DEBUG&&C.print();var x=-Number.MAX_VALUE,h=C.top_node,B=C.top_node;C.top_node.bundle[Clip.ABOVE][C.top_node.type]=C.top_node.top.y!=d?1:0,C.top_node.bundle[Clip.ABOVE][0==C.top_node.type?1:0]=0,C.top_node.bstate[Clip.ABOVE]=BundleState.UNBUNDLED;for(var _=C.top_node.next;null!=_;_=_.next){var E=_.type,v=0==_.type?1:0;_.bundle[Clip.ABOVE][E]=_.top.y!=d?1:0,_.bundle[Clip.ABOVE][v]=0,_.bstate[Clip.ABOVE]=BundleState.UNBUNDLED,1==_.bundle[Clip.ABOVE][E]&&(Clip.EQ(h.xb,_.xb)&&Clip.EQ(h.dx,_.dx)&&h.top.y!=d&&(_.bundle[Clip.ABOVE][E]^=h.bundle[Clip.ABOVE][E],_.bundle[Clip.ABOVE][v]=h.bundle[Clip.ABOVE][v],_.bstate[Clip.ABOVE]=BundleState.BUNDLE_HEAD,h.bundle[Clip.ABOVE][Clip.CLIP]=0,h.bundle[Clip.ABOVE][Clip.SUBJ]=0,h.bstate[Clip.ABOVE]=BundleState.BUNDLE_TAIL),h=_)};var b=[];b[Clip.CLIP]=HState.NH,b[Clip.SUBJ]=HState.NH;var L=[];L[Clip.CLIP]=0,L[Clip.SUBJ]=0;for(var I=null,P=C.top_node;null!=P;P=P.next){if(L[Clip.CLIP]=P.bundle[Clip.ABOVE][Clip.CLIP]+(P.bundle[Clip.BELOW][Clip.CLIP]<<1),L[Clip.SUBJ]=P.bundle[Clip.ABOVE][Clip.SUBJ]+(P.bundle[Clip.BELOW][Clip.SUBJ]<<1),0!=L[Clip.CLIP]||0!=L[Clip.SUBJ]){P.bside[Clip.CLIP]=u[Clip.CLIP],P.bside[Clip.SUBJ]=u[Clip.SUBJ];var O=!1,S=0,N=0,V=0,T=0;if(e==OperationType.GPC_DIFF||e==OperationType.GPC_INT?(O=0!=L[Clip.CLIP]&&(0!=u[Clip.SUBJ]||0!=b[Clip.SUBJ])||0!=L[Clip.SUBJ]&&(0!=u[Clip.CLIP]||0!=b[Clip.CLIP])||0!=L[Clip.CLIP]&&0!=L[Clip.SUBJ]&&u[Clip.CLIP]==u[Clip.SUBJ],S=0!=u[Clip.CLIP]&&0!=u[Clip.SUBJ]?1:0,N=0!=(u[Clip.CLIP]^P.bundle[Clip.ABOVE][Clip.CLIP])&&0!=(u[Clip.SUBJ]^P.bundle[Clip.ABOVE][Clip.SUBJ])?1:0,V=0!=(u[Clip.CLIP]^(b[Clip.CLIP]!=HState.NH?1:0))&&0!=(u[Clip.SUBJ]^(b[Clip.SUBJ]!=HState.NH?1:0))?1:0,T=0!=(u[Clip.CLIP]^(b[Clip.CLIP]!=HState.NH?1:0)^P.bundle[Clip.BELOW][Clip.CLIP])&&0!=(u[Clip.SUBJ]^(b[Clip.SUBJ]!=HState.NH?1:0)^P.bundle[Clip.BELOW][Clip.SUBJ])?1:0):e==OperationType.GPC_XOR?(O=0!=L[Clip.CLIP]||0!=L[Clip.SUBJ],S=u[Clip.CLIP]^u[Clip.SUBJ],N=u[Clip.CLIP]^P.bundle[Clip.ABOVE][Clip.CLIP]^(u[Clip.SUBJ]^P.bundle[Clip.ABOVE][Clip.SUBJ]),V=u[Clip.CLIP]^(b[Clip.CLIP]!=HState.NH?1:0)^(u[Clip.SUBJ]^(b[Clip.SUBJ]!=HState.NH?1:0)),T=u[Clip.CLIP]^(b[Clip.CLIP]!=HState.NH?1:0)^P.bundle[Clip.BELOW][Clip.CLIP]^(u[Clip.SUBJ]^(b[Clip.SUBJ]!=HState.NH?1:0)^P.bundle[Clip.BELOW][Clip.SUBJ])):e==OperationType.GPC_UNION&&(O=!((0==L[Clip.CLIP]||0!=u[Clip.SUBJ]&&0==b[Clip.SUBJ])&&(0==L[Clip.SUBJ]||0!=u[Clip.CLIP]&&0==b[Clip.CLIP])&&(0==L[Clip.CLIP]||0==L[Clip.SUBJ]||u[Clip.CLIP]!=u[Clip.SUBJ])),S=0!=u[Clip.CLIP]||0!=u[Clip.SUBJ]?1:0,N=0!=(u[Clip.CLIP]^P.bundle[Clip.ABOVE][Clip.CLIP])||0!=(u[Clip.SUBJ]^P.bundle[Clip.ABOVE][Clip.SUBJ])?1:0,V=0!=(u[Clip.CLIP]^(b[Clip.CLIP]!=HState.NH?1:0))||0!=(u[Clip.SUBJ]^(b[Clip.SUBJ]!=HState.NH?1:0))?1:0,T=0!=(u[Clip.CLIP]^(b[Clip.CLIP]!=HState.NH?1:0)^P.bundle[Clip.BELOW][Clip.CLIP])||0!=(u[Clip.SUBJ]^(b[Clip.SUBJ]!=HState.NH?1:0)^P.bundle[Clip.BELOW][Clip.SUBJ])?1:0),u[Clip.CLIP]^=P.bundle[Clip.ABOVE][Clip.CLIP],u[Clip.SUBJ]^=P.bundle[Clip.ABOVE][Clip.SUBJ],0!=L[Clip.CLIP]&&(b[Clip.CLIP]=HState.next_h_state[b[Clip.CLIP]][(L[Clip.CLIP]-1<<1)+u[Clip.CLIP]]),0!=L[Clip.SUBJ]&&(b[Clip.SUBJ]=HState.next_h_state[b[Clip.SUBJ]][(L[Clip.SUBJ]-1<<1)+u[Clip.SUBJ]]),O){var A=P.xb,U=VertexType.getType(V,T,S,N);switch(U){case VertexType.EMN:case VertexType.IMN:P.outp[Clip.ABOVE]=g.add_local_min(A,d),x=A,I=P.outp[Clip.ABOVE];break;case VertexType.ERI:A!=x&&(I.add_right(A,d),x=A),P.outp[Clip.ABOVE]=I,I=null;break;case VertexType.ELI:P.outp[Clip.BELOW].add_left(A,d),x=A,I=P.outp[Clip.BELOW];break;case VertexType.EMX:A!=x&&(I.add_left(A,d),x=A),g.merge_right(I,P.outp[Clip.BELOW]),I=null;break;case VertexType.ILI:A!=x&&(I.add_left(A,d),x=A),P.outp[Clip.ABOVE]=I,I=null;break;case VertexType.IRI:P.outp[Clip.BELOW].add_right(A,d),x=A,I=P.outp[Clip.BELOW],P.outp[Clip.BELOW]=null;break;case VertexType.IMX:A!=x&&(I.add_right(A,d),x=A),g.merge_left(I,P.outp[Clip.BELOW]),I=null,P.outp[Clip.BELOW]=null;break;case VertexType.IMM:A!=x&&(I.add_right(A,d),x=A),g.merge_left(I,P.outp[Clip.BELOW]),P.outp[Clip.BELOW]=null,P.outp[Clip.ABOVE]=g.add_local_min(A,d),I=P.outp[Clip.ABOVE];break;case VertexType.EMM:A!=x&&(I.add_left(A,d),x=A),g.merge_right(I,P.outp[Clip.BELOW]),P.outp[Clip.BELOW]=null,P.outp[Clip.ABOVE]=g.add_local_min(A,d),I=P.outp[Clip.ABOVE];break;case VertexType.LED:P.bot.y==d&&P.outp[Clip.BELOW].add_left(A,d),P.outp[Clip.ABOVE]=P.outp[Clip.BELOW],x=A;break;case VertexType.RED:P.bot.y==d&&P.outp[Clip.BELOW].add_right(A,d),P.outp[Clip.ABOVE]=P.outp[Clip.BELOW],x=A}}};Clip.DEBUG&&g.print(),g.print()};for(var P=C.top_node;null!=P;P=P.next)if(P.top.y==d){var D=P.prev,_=P.next;null!=D?D.next=_:C.top_node=_,null!=_&&(_.prev=D),P.bstate[Clip.BELOW]==BundleState.BUNDLE_HEAD&&null!=D&&D.bstate[Clip.BELOW]==BundleState.BUNDLE_TAIL&&(D.outp[Clip.BELOW]=P.outp[Clip.BELOW],D.bstate[Clip.BELOW]=BundleState.UNBUNDLED,null!=D.prev&&D.prev.bstate[Clip.BELOW]==BundleState.BUNDLE_TAIL&&(D.bstate[Clip.BELOW]=BundleState.BUNDLE_HEAD))}else P.xt=P.top.y==f?P.top.x:P.bot.x+P.dx*(f-P.bot.y);if(c<r.sbt_entries){var H=new ItNodeTable();H.build_intersection_table(C,m);for(var J=H.top_node;null!=J;J=J.next){if(h=J.ie[0],B=J.ie[1],!(0==h.bundle[Clip.ABOVE][Clip.CLIP]&&0==h.bundle[Clip.ABOVE][Clip.SUBJ]||0==B.bundle[Clip.ABOVE][Clip.CLIP]&&0==B.bundle[Clip.ABOVE][Clip.SUBJ])){var w=h.outp[Clip.ABOVE],M=B.outp[Clip.ABOVE],X=J.point.x,R=J.point.y+d,W=0!=h.bundle[Clip.ABOVE][Clip.CLIP]&&0==h.bside[Clip.CLIP]||0!=B.bundle[Clip.ABOVE][Clip.CLIP]&&0!=B.bside[Clip.CLIP]||0==h.bundle[Clip.ABOVE][Clip.CLIP]&&0==B.bundle[Clip.ABOVE][Clip.CLIP]&&0!=h.bside[Clip.CLIP]&&0!=B.bside[Clip.CLIP]?1:0,G=0!=h.bundle[Clip.ABOVE][Clip.SUBJ]&&0==h.bside[Clip.SUBJ]||0!=B.bundle[Clip.ABOVE][Clip.SUBJ]&&0!=B.bside[Clip.SUBJ]||0==h.bundle[Clip.ABOVE][Clip.SUBJ]&&0==B.bundle[Clip.ABOVE][Clip.SUBJ]&&0!=h.bside[Clip.SUBJ]&&0!=B.bside[Clip.SUBJ]?1:0,V=0,T=0,S=0,N=0;e==OperationType.GPC_DIFF||e==OperationType.GPC_INT?(V=0!=W&&0!=G?1:0,T=0!=(W^B.bundle[Clip.ABOVE][Clip.CLIP])&&0!=(G^B.bundle[Clip.ABOVE][Clip.SUBJ])?1:0,S=0!=(W^h.bundle[Clip.ABOVE][Clip.CLIP])&&0!=(G^h.bundle[Clip.ABOVE][Clip.SUBJ])?1:0,N=0!=(W^B.bundle[Clip.ABOVE][Clip.CLIP]^h.bundle[Clip.ABOVE][Clip.CLIP])&&0!=(G^B.bundle[Clip.ABOVE][Clip.SUBJ]^h.bundle[Clip.ABOVE][Clip.SUBJ])?1:0):e==OperationType.GPC_XOR?(V=W^G,T=W^B.bundle[Clip.ABOVE][Clip.CLIP]^(G^B.bundle[Clip.ABOVE][Clip.SUBJ]),S=W^h.bundle[Clip.ABOVE][Clip.CLIP]^(G^h.bundle[Clip.ABOVE][Clip.SUBJ]),N=W^B.bundle[Clip.ABOVE][Clip.CLIP]^h.bundle[Clip.ABOVE][Clip.CLIP]^(G^B.bundle[Clip.ABOVE][Clip.SUBJ]^h.bundle[Clip.ABOVE][Clip.SUBJ])):e==OperationType.GPC_UNION&&(V=0!=W||0!=G?1:0,T=0!=(W^B.bundle[Clip.ABOVE][Clip.CLIP])||0!=(G^B.bundle[Clip.ABOVE][Clip.SUBJ])?1:0,S=0!=(W^h.bundle[Clip.ABOVE][Clip.CLIP])||0!=(G^h.bundle[Clip.ABOVE][Clip.SUBJ])?1:0,N=0!=(W^B.bundle[Clip.ABOVE][Clip.CLIP]^h.bundle[Clip.ABOVE][Clip.CLIP])||0!=(G^B.bundle[Clip.ABOVE][Clip.SUBJ]^h.bundle[Clip.ABOVE][Clip.SUBJ])?1:0);var U=VertexType.getType(V,T,S,N);switch(U){case VertexType.EMN:h.outp[Clip.ABOVE]=g.add_local_min(X,R),B.outp[Clip.ABOVE]=h.outp[Clip.ABOVE];break;case VertexType.ERI:null!=w&&(w.add_right(X,R),B.outp[Clip.ABOVE]=w,h.outp[Clip.ABOVE]=null);break;case VertexType.ELI:null!=M&&(M.add_left(X,R),h.outp[Clip.ABOVE]=M,B.outp[Clip.ABOVE]=null);break;case VertexType.EMX:null!=w&&null!=M&&(w.add_left(X,R),g.merge_right(w,M),h.outp[Clip.ABOVE]=null,B.outp[Clip.ABOVE]=null);break;case VertexType.IMN:h.outp[Clip.ABOVE]=g.add_local_min(X,R),B.outp[Clip.ABOVE]=h.outp[Clip.ABOVE];break;case VertexType.ILI:null!=w&&(w.add_left(X,R),B.outp[Clip.ABOVE]=w,h.outp[Clip.ABOVE]=null);break;case VertexType.IRI:null!=M&&(M.add_right(X,R),h.outp[Clip.ABOVE]=M,B.outp[Clip.ABOVE]=null);break;case VertexType.IMX:null!=w&&null!=M&&(w.add_right(X,R),g.merge_left(w,M),h.outp[Clip.ABOVE]=null,B.outp[Clip.ABOVE]=null);break;case VertexType.IMM:null!=w&&null!=M&&(w.add_right(X,R),g.merge_left(w,M),h.outp[Clip.ABOVE]=g.add_local_min(X,R),B.outp[Clip.ABOVE]=h.outp[Clip.ABOVE]);break;case VertexType.EMM:null!=w&&null!=M&&(w.add_left(X,R),g.merge_right(w,M),h.outp[Clip.ABOVE]=g.add_local_min(X,R),B.outp[Clip.ABOVE]=h.outp[Clip.ABOVE])}};0!=h.bundle[Clip.ABOVE][Clip.CLIP]&&(B.bside[Clip.CLIP]=0==B.bside[Clip.CLIP]?1:0),0!=B.bundle[Clip.ABOVE][Clip.CLIP]&&(h.bside[Clip.CLIP]=0==h.bside[Clip.CLIP]?1:0),0!=h.bundle[Clip.ABOVE][Clip.SUBJ]&&(B.bside[Clip.SUBJ]=0==B.bside[Clip.SUBJ]?1:0),0!=B.bundle[Clip.ABOVE][Clip.SUBJ]&&(h.bside[Clip.SUBJ]=0==h.bside[Clip.SUBJ]?1:0);var D=h.prev,_=B.next;if(null!=_&&(_.prev=h),h.bstate[Clip.ABOVE]==BundleState.BUNDLE_HEAD)for(var F=!0;F;)D=D.prev,null!=D?D.bstate[Clip.ABOVE]!=BundleState.BUNDLE_TAIL&&(F=!1):F=!1;null==D?(C.top_node.prev=B,B.next=C.top_node,C.top_node=h.next):(D.next.prev=B,B.next=D.next,D.next=h.next),h.next.prev=D,B.next.prev=B,h.next=_,Clip.DEBUG&&g.print()};for(var P=C.top_node;null!=P;P=P.next){var _=P.next,k=P.succ;if(P.top.y==f&&null!=k){k.outp[Clip.BELOW]=P.outp[Clip.ABOVE],k.bstate[Clip.BELOW]=P.bstate[Clip.ABOVE],k.bundle[Clip.BELOW][Clip.CLIP]=P.bundle[Clip.ABOVE][Clip.CLIP],k.bundle[Clip.BELOW][Clip.SUBJ]=P.bundle[Clip.ABOVE][Clip.SUBJ];var D=P.prev;null!=D?D.next=k:C.top_node=k,null!=_&&(_.prev=k),k.prev=D,k.next=_}else P.outp[Clip.BELOW]=P.outp[Clip.ABOVE],P.bstate[Clip.BELOW]=P.bstate[Clip.ABOVE],P.bundle[Clip.BELOW][Clip.CLIP]=P.bundle[Clip.ABOVE][Clip.CLIP],P.bundle[Clip.BELOW][Clip.SUBJ]=P.bundle[Clip.ABOVE][Clip.SUBJ],P.xb=P.xt;P.outp[Clip.ABOVE]=null}}};return n=g.getResult(o)},static.EQ=function(e,t){return Math.abs(e-t)<=Clip.GPC_EPSILON},static.PREV_INDEX=function(e,t){return(e-1+t)%t},static.NEXT_INDEX=function(e,t){return(e+1)%t},static.OPTIMAL=function(e,t){return e.getY(Clip.PREV_INDEX(t,e.getNumPoints()))!=e.getY(t)||e.getY(Clip.NEXT_INDEX(t,e.getNumPoints()))!=e.getY(t)},static.create_contour_bboxes=function(e){for(var t=[],i=0;i<e.getNumInnerPoly();i++){var o=e.getInnerPoly(i);t[i]=o.getBounds()};return t},static.minimax_test=function(e,t,i){for(var o=Clip.create_contour_bboxes(e),n=Clip.create_contour_bboxes(t),l=e.getNumInnerPoly(),r=t.getNumInnerPoly(),p=ArrayHelper.create2DArray(l,r),a=0;l>a;a++)for(var s=0;r>s;s++)p[a][s]=!(o[a].getMaxX()<n[s].getMinX()||o[a].getMinX()>n[s].getMaxX()||o[a].getMaxY()<n[s].getMinY()||o[a].getMinY()>n[s].getMaxY());for(var s=0;r>s;s++){for(var u=!1,a=0;!u&&l>a;a++)u=p[a][s];u||t.setContributing(s,!1)};if(i==OperationType.GPC_INT)for(var a=0;l>a;a++){for(var u=!1,s=0;!u&&r>s;s++)u=p[a][s];u||e.setContributing(a,!1)}},static.bound_list=function(e,t){if(null==e.top_node)return e.top_node=new LmtNode(t),e.top_node;for(var i=null,o=e.top_node,n=!1;!n;)if(t<o.y){var l=o;o=new LmtNode(t),o.next=l,null==i?e.top_node=o:i.next=o,n=!0}else t>o.y?null==o.next?(o.next=new LmtNode(t),o=o.next,n=!0):(i=o,o=o.next):n=!0;return o},static.insert_bound=function(e,t){if(null==e.first_bound){e.first_bound=t}else for(var i=!1,o=null,n=e.first_bound;!i;)t.bot.x<n.bot.x?(null==o?e.first_bound=t:o.next_bound=t,t.next_bound=n,i=!0):t.bot.x==n.bot.x&&t.dx<n.dx?(null==o?e.first_bound=t:o.next_bound=t,t.next_bound=n,i=!0):null==n.next_bound?(n.next_bound=t,i=!0):(o=n,n=n.next_bound)},static.add_edge_to_aet=function(e,t){if(null==e.top_node){e.top_node=t,t.prev=null,t.next=null}else for(var i=e.top_node,o=null,n=!1;!n;)t.xb<i.xb?(t.prev=o,t.next=i,i.prev=t,null==o?e.top_node=t:o.next=t,n=!0):t.xb==i.xb&&t.dx<i.dx?(t.prev=o,t.next=i,i.prev=t,null==o?e.top_node=t:o.next=t,n=!0):(o=i,null==i.next?(i.next=t,t.prev=i,t.next=null,n=!0):i=i.next)},static.add_to_sbtree=function(e,t){if(null==e.sb_tree)return e.sb_tree=new ScanBeamTree(t),void(e.sbt_entries++);for(var i=e.sb_tree,o=!1;!o;)i.y>t?null==i.less?(i.less=new ScanBeamTree(t),e.sbt_entries++,o=!0):i=i.less:i.y<t?null==i.more?(i.more=new ScanBeamTree(t),e.sbt_entries++,o=!0):i=i.more:o=!0},static.build_lmt=function(e,t,i,o,n){for(var l=new EdgeTable(),r=0;r<i.getNumInnerPoly();r++){var p=i.getInnerPoly(r);if(p.isContributing(0)){var a=0,s=0;l=new EdgeTable();for(var u=0;u<p.getNumPoints();u++)if(Clip.OPTIMAL(p,u)){var y=p.getX(u),g=p.getY(u);l.addNode(y,g),Clip.add_to_sbtree(t,p.getY(u)),a++};for(var C=0;a>C;C++)if(l.FWD_MIN(C)){for(var c=1,d=Clip.NEXT_INDEX(C,a);l.NOT_FMAX(d);)c++,d=Clip.NEXT_INDEX(d,a);var f=C,m=l.getNode(s);m.bstate[Clip.BELOW]=BundleState.UNBUNDLED,m.bundle[Clip.BELOW][Clip.CLIP]=0,m.bundle[Clip.BELOW][Clip.SUBJ]=0;for(var u=0;c>u;u++){var P=l.getNode(s+u),x=l.getNode(f);P.xb=x.vertex.x,P.bot.x=x.vertex.x,P.bot.y=x.vertex.y,f=Clip.NEXT_INDEX(f,a),x=l.getNode(f),P.top.x=x.vertex.x,P.top.y=x.vertex.y,P.dx=(x.vertex.x-P.bot.x)/(P.top.y-P.bot.y),P.type=o,P.outp[Clip.ABOVE]=null,P.outp[Clip.BELOW]=null,P.next=null,P.prev=null,P.succ=c>1&&c-1>u?l.getNode(s+u+1):null,P.pred=c>1&&u>0?l.getNode(s+u-1):null,P.next_bound=null,P.bside[Clip.CLIP]=n==OperationType.GPC_DIFF?Clip.RIGHT:Clip.LEFT,P.bside[Clip.SUBJ]=Clip.LEFT};Clip.insert_bound(Clip.bound_list(e,l.getNode(C).vertex.y),m),Clip.DEBUG&&e.print(),s+=c};for(var C=0;a>C;C++)if(l.REV_MIN(C)){for(var c=1,d=Clip.PREV_INDEX(C,a);l.NOT_RMAX(d);)c++,d=Clip.PREV_INDEX(d,a);var f=C,m=l.getNode(s);m.bstate[Clip.BELOW]=BundleState.UNBUNDLED,m.bundle[Clip.BELOW][Clip.CLIP]=0,m.bundle[Clip.BELOW][Clip.SUBJ]=0;for(var u=0;c>u;u++){var P=l.getNode(s+u),x=l.getNode(f);P.xb=x.vertex.x,P.bot.x=x.vertex.x,P.bot.y=x.vertex.y,f=Clip.PREV_INDEX(f,a),x=l.getNode(f),P.top.x=x.vertex.x,P.top.y=x.vertex.y,P.dx=(x.vertex.x-P.bot.x)/(P.top.y-P.bot.y),P.type=o,P.outp[Clip.ABOVE]=null,P.outp[Clip.BELOW]=null,P.next=null,P.prev=null,P.succ=c>1&&c-1>u?l.getNode(s+u+1):null,P.pred=c>1&&u>0?l.getNode(s+u-1):null,P.next_bound=null,P.bside[Clip.CLIP]=n==OperationType.GPC_DIFF?Clip.RIGHT:Clip.LEFT,P.bside[Clip.SUBJ]=Clip.LEFT};Clip.insert_bound(Clip.bound_list(e,l.getNode(C).vertex.y),m),Clip.DEBUG&&e.print(),s+=c}}else p.setContributing(0,!0)};return l},static.add_st_edge=function(e,t,i,o){if(null==e){e=new StNode(i,null)}else{var n=e.xt-e.xb-(i.xt-i.xb);if(i.xt>=e.xt||i.dx==e.dx||Math.abs(n)<=Clip.GPC_EPSILON){var l=e;e=new StNode(i,l)}else{var r=(i.xb-e.xb)/n,p=e.xb+r*(e.xt-e.xb),a=r*o;t.top_node=Clip.add_intersection(t.top_node,e.edge,i,p,a),e.prev=Clip.add_st_edge(e.prev,t,i,o)}};return e},static.add_intersection=function(e,t,i,o,n){if(null==e){e=new ItNode(t,i,o,n,null)}else if(e.point.y>n){var l=e;e=new ItNode(t,i,o,n,l)}else e.next=Clip.add_intersection(e.next,t,i,o,n);return e},gpcas.geometry.AetTree=function(){this.top_node=null},gpcas.geometry.AetTree.prototype.print=function(){for(var e=this.top_node;null!=e;e=e.next);},gpcas.geometry.BundleState=function(e){this.m_State=e},gpcas.geometry.BundleState.UNBUNDLED=new gpcas.geometry.BundleState("UNBUNDLED"),gpcas.geometry.BundleState.BUNDLE_HEAD=new gpcas.geometry.BundleState("BUNDLE_HEAD"),gpcas.geometry.BundleState.BUNDLE_TAIL=new gpcas.geometry.BundleState("BUNDLE_TAIL"),gpcas.geometry.BundleState.prototype.toString=function(){return this.m_State},gpcas.geometry.EdgeNode=function(){this.vertex=new Point(),this.bot=new Point(),this.top=new Point(),this.xb,this.xt,this.dx,this.type,this.bundle=ArrayHelper.create2DArray(2,2),this.bside=[],this.bstate=[],this.outp=[],this.prev,this.next,this.pred,this.succ,this.next_bound},gpcas.geometry.EdgeTable=function(){this.m_List=new ArrList()},gpcas.geometry.EdgeTable.prototype.addNode=function(e,t){var i=new EdgeNode();i.vertex.x=e,i.vertex.y=t,this.m_List.add(i)},gpcas.geometry.EdgeTable.prototype.getNode=function(e){return this.m_List.get(e)},gpcas.geometry.EdgeTable.prototype.FWD_MIN=function(e){var t=this.m_List,i=t.get(Clip.PREV_INDEX(e,t.size())),o=t.get(Clip.NEXT_INDEX(e,t.size())),n=t.get(e);return i.vertex.y>=n.vertex.y&&o.vertex.y>n.vertex.y},gpcas.geometry.EdgeTable.prototype.NOT_FMAX=function(e){var t=this.m_List,i=t.get(Clip.NEXT_INDEX(e,t.size())),o=t.get(e);return i.vertex.y>o.vertex.y},gpcas.geometry.EdgeTable.prototype.REV_MIN=function(e){var t=this.m_List,i=t.get(Clip.PREV_INDEX(e,t.size())),o=t.get(Clip.NEXT_INDEX(e,t.size())),n=t.get(e);return i.vertex.y>n.vertex.y&&o.vertex.y>=n.vertex.y},gpcas.geometry.EdgeTable.prototype.NOT_RMAX=function(e){var t=this.m_List,i=t.get(Clip.PREV_INDEX(e,t.size())),o=t.get(e);return i.vertex.y>o.vertex.y},gpcas.geometry.HState=function(){},gpcas.geometry.HState.NH=0,gpcas.geometry.HState.BH=1,gpcas.geometry.HState.TH=2;var NH=gpcas.geometry.HState.NH,BH=gpcas.geometry.HState.BH,TH=gpcas.geometry.HState.TH;gpcas.geometry.HState.next_h_state=[[BH,TH,TH,BH,NH,NH],[NH,NH,NH,NH,TH,TH],[NH,NH,NH,NH,BH,BH]],gpcas.geometry.IntersectionPoint=function(e,t,i){this.polygonPoint1=e,this.polygonPoint2=t,this.intersectionPoint=i},gpcas.geometry.IntersectionPoint.prototype.toString=function(){return"P1 :"+polygonPoint1.toString()+" P2:"+polygonPoint2.toString()+" IP:"+intersectionPoint.toString()},gpcas.geometry.ItNode=function(e,t,i,o,n){this.ie=[],this.point=new Point(i,o),this.next=n,this.ie[0]=e,this.ie[1]=t},gpcas.geometry.ItNodeTable=function(){this.top_node},gpcas.geometry.ItNodeTable.prototype.build_intersection_table=function(e,t){for(var i=null,o=e.top_node;null!=o;o=o.next)(o.bstate[Clip.ABOVE]==BundleState.BUNDLE_HEAD||0!=o.bundle[Clip.ABOVE][Clip.CLIP]||0!=o.bundle[Clip.ABOVE][Clip.SUBJ])&&(i=Clip.add_st_edge(i,this,o,t))},gpcas.geometry.Line=function(){this.start,this.end},gpcas.geometry.LineHelper=function(){},gpcas.geometry.LineHelper.equalPoint=function(e,t){return e[0]==t[0]&&e[1]==t[1]},gpcas.geometry.LineHelper.equalVertex=function(e,t,i,o){return gpcas.geometry.LineHelper.equalPoint(e,i)&&gpcas.geometry.LineHelper.equalPoint(t,o)||gpcas.geometry.LineHelper.equalPoint(e,o)&&gpcas.geometry.LineHelper.equalPoint(t,i)},gpcas.geometry.LineHelper.distancePoints=function(e,t){return Math.sqrt((t[0]-e[0])*(t[0]-e[0])+(t[1]-e[1])*(t[1]-e[1]))},gpcas.geometry.LineHelper.clonePoint=function(e){return[e[0],e[1]]},gpcas.geometry.LineHelper.cloneLine=function(e){for(var t=[],i=0;i<e.length;i++)t[i]=[e[i][0],e[i][1]];return t},gpcas.geometry.LineHelper.addLineToLine=function(e,t){for(var i=0;i<t.length;i++)e.push(clonePoint(t[i]))},gpcas.geometry.LineHelper.roundPoint=function(e){e[0]=Math.round(e[0]),e[1]=Math.round(e[1])},gpcas.geometry.LineHelper.lineIntersectLine=function(e,t,i,o,n){null==n&&(n=!0);var l,r,p,a,s,u,y;r=t.y-e.y,a=e.x-t.x,u=t.x*e.y-e.x*t.y,p=o.y-i.y,s=i.x-o.x,y=o.x*i.y-i.x*o.y;var g=r*s-p*a;if(0==g)return null;if(l=new Point(),l.x=(a*y-s*u)/g,l.y=(p*u-r*y)/g,n){if(Math.pow(l.x-t.x+(l.y-t.y),2)>Math.pow(e.x-t.x+(e.y-t.y),2))return null;if(Math.pow(l.x-e.x+(l.y-e.y),2)>Math.pow(e.x-t.x+(e.y-t.y),2))return null;if(Math.pow(l.x-o.x+(l.y-o.y),2)>Math.pow(i.x-o.x+(i.y-o.y),2))return null;if(Math.pow(l.x-i.x+(l.y-i.y),2)>Math.pow(i.x-o.x+(i.y-o.y),2))return null};return new Point(Math.round(l.x),Math.round(l.y))},gpcas.geometry.LineIntersection=function(){},gpcas.geometry.LineIntersection.iteratePoints=function(e,t,i,o,n){var l=!0,r=e.length,p=e.indexOf(t),a=e.indexOf(i),s=p;a>p&&(l=!1);var u,y=[];if(l){for(var g=0;r>g&&(u=r>g+s?e[g+s]:e[g+s-r],y.push(u),!equals(u,o)&&!equals(u,n));g++);}else for(var g=r;g>=0&&(u=r>g+s?e[g+s]:e[g+s-r],y.push(u),!equals(u,o)&&!equals(u,n));g--);return y},gpcas.geometry.LineIntersection.intersectPoly=function(e,t){for(var i,o,n,l,r,p=e.getNumPoints(),a=null,s=null,u=-1,y=-1,g=!1,C=1;C<t.length;C++){o=t[C-1],n=t[C];for(var c=0,d=Number.MAX_VALUE,f=-1,m=0;p>m;m++)l=e.getPoint(0==m?p-1:m-1),r=e.getPoint(m),null!=(i=LineHelper.lineIntersectLine(o,n,l,r))&&(f=Point.distance(i,n),f>c&&!g&&(c=f,a=new IntersectionPoint(l,r,i),u=C),d>f&&(d=f,s=new IntersectionPoint(l,r,i),y=C-1));g=null!=a};if(null!=a&&null!=s){var P=[];P[0]=a.intersectionPoint;for(var m=1,C=u;y>=C;C++)P[m++]=t[C];if(P[P.length-1]=s.intersectionPoint,equals(a.polygonPoint1,s.polygonPoint1)&&equals(a.polygonPoint2,s.polygonPoint2)||equals(a.polygonPoint1,s.polygonPoint2)&&equals(a.polygonPoint2,s.polygonPoint1)){var x=new PolySimple();x.add(P);var h=e.intersection(x),B=e.xor(x);if(checkPoly(h)&&checkPoly(B))return[h,B]}else{var _=iteratePoints(e.getPoints(),a.polygonPoint1,a.polygonPoint2,s.polygonPoint1,s.polygonPoint2);_=_.concat(P.reverse());var E=iteratePoints(e.getPoints(),a.polygonPoint2,a.polygonPoint1,s.polygonPoint1,s.polygonPoint2);E=E.concat(P);var x=new PolySimple();x.add(_);var v=new PolySimple();v.add(E);var h=e.intersection(x),B=e.intersection(v);if(checkPoly(h)&&checkPoly(B))return[h,B]}};return null},gpcas.geometry.LineIntersection.checkPoly=function(e){for(var t=0,i=0;i<e.getNumInnerPoly();i++){var o=e.getInnerPoly(i);if(o.isHole())return!1;if(t++,t>1)return!1};return!0},gpcas.geometry.LmtNode=function(e){this.y=e,this.first_bound,this.next},gpcas.geometry.LmtTable=function(){this.top_node},gpcas.geometry.LmtTable.prototype.print=function(){for(var e=0,t=this.top_node;null!=t;){for(var i=t.first_bound;null!=i;i=i.next_bound);e++,t=t.next}},gpcas.geometry.OperationType=function(e){this.m_Type=e},gpcas.geometry.OperationType.GPC_DIFF=new gpcas.geometry.OperationType("Difference"),gpcas.geometry.OperationType.GPC_INT=new gpcas.geometry.OperationType("Intersection"),gpcas.geometry.OperationType.GPC_XOR=new gpcas.geometry.OperationType("Exclusive or"),gpcas.geometry.OperationType.GPC_UNION=new gpcas.geometry.OperationType("Union"),gpcas.geometry.PolyDefault=function(e){null==e&&(e=!1),this.m_IsHole=e,this.m_List=new ArrList()},gpcas.geometry.PolyDefault.prototype.equals=function(e){if(!(e instanceof PolyDefault))return!1;var t=e;return this.m_IsHole!=t.m_IsHole?!1:equals(this.m_List,t.m_List)?!0:!1},gpcas.geometry.PolyDefault.prototype.hashCode=function(){var e=this.m_List,t=17;return t=37*t+e.hashCode()},gpcas.geometry.PolyDefault.prototype.clear=function(){this.m_List.clear()},gpcas.geometry.PolyDefault.prototype.add=function(e,t){var i=[];if(i[0]=e,t&&(i[1]=t),2==i.length){this.addPointXY(i[0],i[1])}else if(1==i.length)if(i[0]instanceof Point){this.addPoint(i[0])}else if(i[0]instanceof gpcas.geometry.PolySimple){this.addPoly(i[0])}else if(i[0]instanceof Array){var o=i[0];if(2==o.length&&o[0]instanceof Number&&o[1]instanceof Number){this.add(o[0],o[1])}else for(var n=0;n<i[0].length;n++)this.add(i[0][n])}},gpcas.geometry.PolyDefault.prototype.addPointXY=function(e,t){this.addPoint(new Point(e,t))},gpcas.geometry.PolyDefault.prototype.addPoint=function(e){var t=this.m_List;0==t.size()&&t.add(new PolySimple()),t.get(0).addPoint(e)},gpcas.geometry.PolyDefault.prototype.addPoly=function(e){var t=this.m_IsHole,i=this.m_List;i.size()>0&&t&&alert("ERROR : Cannot add polys to something designated as a hole."),i.add(e)},gpcas.geometry.PolyDefault.prototype.isEmpty=function(){return this.m_List.isEmpty()},gpcas.geometry.PolyDefault.prototype.getBounds=function(){var e=this.m_List;if(0==e.size())return new Rectangle();if(1==e.size()){var t=this.getInnerPoly(0);return t.getBounds()};console.log("getBounds not supported on complex poly.")},gpcas.geometry.PolyDefault.prototype.getInnerPoly=function(e){return this.m_List.get(e)},gpcas.geometry.PolyDefault.prototype.getNumInnerPoly=function(){var e=this.m_List;return e.size()},gpcas.geometry.PolyDefault.prototype.getNumPoints=function(){return this.m_List.get(0).getNumPoints()},gpcas.geometry.PolyDefault.prototype.getX=function(e){return this.m_List.get(0).getX(e)},gpcas.geometry.PolyDefault.prototype.getPoint=function(e){return this.m_List.get(0).getPoint(e)},gpcas.geometry.PolyDefault.prototype.getPoints=function(){return this.m_List.get(0).getPoints()},gpcas.geometry.PolyDefault.prototype.isPointInside=function(e){var t=this.m_List;if(!t.get(0).isPointInside(e))return!1;for(var i=0;i<t.size();i++){var o=t.get(i);if(o.isHole()&&o.isPointInside(e))return!1};return!0},gpcas.geometry.PolyDefault.prototype.getY=function(e){var t=this.m_List;return t.get(0).getY(e)},gpcas.geometry.PolyDefault.prototype.isHole=function(){var e=this.m_List,t=this.m_IsHole;return e.size()>1&&alert("Cannot call on a poly made up of more than one poly."),t},gpcas.geometry.PolyDefault.prototype.setIsHole=function(e){var t=this.m_List;t.size()>1&&alert("Cannot call on a poly made up of more than one poly."),this.m_IsHole=e},gpcas.geometry.PolyDefault.prototype.isContributing=function(e){var t=this.m_List;return t.get(e).isContributing(0)},gpcas.geometry.PolyDefault.prototype.setContributing=function(e,t){var i=this.m_List;1!=i.size()&&alert("Only applies to polys of size 1"),i.get(e).setContributing(0,t)},gpcas.geometry.PolyDefault.prototype.intersection=function(e){return Clip.intersection(e,this,"PolyDefault")},gpcas.geometry.PolyDefault.prototype.union=function(e){return Clip.union(e,this,"PolyDefault")},gpcas.geometry.PolyDefault.prototype.xor=function(e){return Clip.xor(e,this,"PolyDefault")},gpcas.geometry.PolyDefault.prototype.difference=function(e){return Clip.difference(e,this,"PolyDefault")},gpcas.geometry.PolyDefault.prototype.getArea=function(){for(var e=0,t=0;t<getNumInnerPoly();t++){var i=getInnerPoly(t),o=i.getArea()*(i.isHole()?-1:1);e+=o};return e},gpcas.geometry.PolyDefault.prototype.toString=function(){for(var e="",t=this.m_List,i=0;i<t.size();i++){var o=this.getInnerPoly(i);e+="InnerPoly("+i+").hole="+o.isHole();for(var n=[],l=0;l<o.getNumPoints();l++)n.push(new Point(o.getX(l),o.getY(l)));n=ArrayHelper.sortPointsClockwise(n);for(var r=0;r<n.length;r++)e+=n[r].toString()};return e},gpcas.geometry.Polygon=function(){this.maxTop,this.maxBottom,this.maxLeft,this.maxRight,this.vertices},gpcas.geometry.Polygon.prototype.fromArray=function(e){this.vertices=[];for(var t=0;t<e.length;t++){var i=e[t];this.vertices.push(new Point(i[0],i[1]))}},gpcas.geometry.Polygon.prototype.normalize=function(){for(var e,t=this.vertices,i=this.vertices,o=0;o<t.length;o++){var n=t[o];(null==maxTop||maxTop.y>n.y||maxTop.y==n.y&&n.x<maxTop.x)&&(maxTop=n),(null==maxBottom||maxBottom.y<n.y||maxBottom.y==n.y&&n.x>maxBottom.x)&&(maxBottom=n),(null==maxLeft||maxLeft.x>n.x||maxLeft.x==n.x&&n.y>maxLeft.y)&&(maxLeft=n,e=o),(null==maxRight||maxRight.x<n.x||maxRight.x==n.x&&n.y<maxRight.y)&&(maxRight=n)};if(e>0){i=[];for(var l=0,o=e;o<t.length;o++)i[l++]=this.vertices[o];for(var o=0;e>o;o++)i[l++]=this.vertices[o];t=i};for(var r=!1,p=0;p<this.vertices.length;p++){var n=this.vertices[p];if(equals(n,maxBottom)){r=!0;break};if(equals(n,maxTop))break};if(r){i=[],i[0]=t[0];for(var l=1,o=t.length-1;o>0;o--)i[l++]=this.vertices[o];t=i}},gpcas.geometry.Polygon.prototype.getVertexIndex=function(e){for(var t=0;t<this.vertices.length;t++)if(equals(vertices[t],e))return t;return-1},gpcas.geometry.Polygon.prototype.insertVertex=function(e,t,i){var o=getVertexIndex(e),n=getVertexIndex(t);if(-1==o||-1==n)return!1;if(o>n){var l=o;o=n,n=l};if(n==o+1){for(var r=[],l=0;o>=l;l++)r[l]=this.vertices[l];r[n]=i;for(var l=n;l<this.vertices.length;l++)r[l+1]=this.vertices[l];this.vertices=r}else n==vertices.length-1&&0==o&&this.vertices.push(i);return!0},gpcas.geometry.Polygon.prototype.clone=function(){var e=new Polygon();return e.vertices=vertices.slice(this.vertices.length-1),e},gpcas.geometry.Polygon.prototype.toString=function(){for(var e=this.vertices,t="[",i=0;i<e.length;i++){var o=e[i];t+=(i>0?",":"")+"["+o.x+","+o.y+"]"};return t+="]"},gpcas.geometry.PolygonNode=function(e,t,i){this.active,this.hole,this.v=[],this.next,this.proxy;var o=new VertexNode(t,i);this.v[Clip.LEFT]=o,this.v[Clip.RIGHT]=o,this.next=e,this.proxy=this,this.active=1},gpcas.geometry.PolygonNode.prototype.add_right=function(e,t){var i=new VertexNode(e,t);this.proxy.v[Clip.RIGHT].next=i,this.proxy.v[Clip.RIGHT]=i},gpcas.geometry.PolygonNode.prototype.add_left=function(e,t){var i=this.proxy,o=new VertexNode(e,t);o.next=i.v[Clip.LEFT],i.v[Clip.LEFT]=o},gpcas.geometry.PolySimple=function(){this.m_List=new ArrList(),this.m_Contributes=!0},gpcas.geometry.PolySimple.prototype.equals=function(e){if(!(e instanceof PolySimple))return!1;var t=e,i=this.m_List.size(),o=t.m_List.size();if(i!=o)return!1;if(i>0){for(var n=this.getX(0),l=this.getY(0),r=-1,p=0;-1==r&&o>p;p++){var a=t.getX(p),s=t.getY(p);n==a&&l==s&&(r=p)};if(-1==r)return!1;for(var p=r,u=0;i>u;u++){n=this.getX(u),l=this.getY(u);var a=t.getX(p),s=t.getY(p);if(n!=a||l!=s)return!1;p++,p>=o&&(p=0)}};return!0},gpcas.geometry.PolySimple.prototype.hashCode=function(){var e=17;return e=37*e+this.m_List.hashCode()},gpcas.geometry.PolySimple.prototype.toString=function(){return"PolySimple: num_points="+getNumPoints()},gpcas.geometry.PolySimple.prototype.clear=function(){this.m_List.clear()},gpcas.geometry.PolySimple.prototype.add=function(e,t){var i=[];if(i[0]=e,t&&(i[1]=t),2==i.length){this.addPointXY(i[0],i[1])}else if(1==i.length)if(i[0]instanceof Point){this.addPoint(i[0])}else if(i[0]instanceof Poly){this.addPoly(i[0])}else if(i[0]instanceof Array)for(var o=0;o<i[0].length;o++){var n=i[0][o];this.add(n)}},gpcas.geometry.PolySimple.prototype.addPointXY=function(e,t){this.addPoint(new Point(e,t))},gpcas.geometry.PolySimple.prototype.addPoint=function(e){this.m_List.add(e)},gpcas.geometry.PolySimple.prototype.addPoly=function(){alert("Cannot add poly to a simple poly.")},gpcas.geometry.PolySimple.prototype.isEmpty=function(){return this.m_List.isEmpty()},gpcas.geometry.PolySimple.prototype.getBounds=function(){for(var e=Number.MAX_VALUE,t=Number.MAX_VALUE,i=-Number.MAX_VALUE,o=-Number.MAX_VALUE,n=0;n<this.m_List.size();n++){var l=this.getX(n),r=this.getY(n);e>l&&(e=l),l>i&&(i=l),t>r&&(t=r),r>o&&(o=r)};return new Rectangle(e,t,i-e,o-t)},gpcas.geometry.PolySimple.prototype.getInnerPoly=function(e){return 0!=e&&alert("PolySimple only has one poly"),this},gpcas.geometry.PolySimple.prototype.getNumInnerPoly=function(){return 1},gpcas.geometry.PolySimple.prototype.getNumPoints=function(){return this.m_List.size()},gpcas.geometry.PolySimple.prototype.getX=function(e){return this.m_List.get(e).x},gpcas.geometry.PolySimple.prototype.getY=function(e){return this.m_List.get(e).y},gpcas.geometry.PolySimple.prototype.getPoint=function(e){return this.m_List.get(e)},gpcas.geometry.PolySimple.prototype.getPoints=function(){return this.m_List.toArray()},gpcas.geometry.PolySimple.prototype.isPointInside=function(e){for(var t=this.getPoints(),i=t.length-1,o=!1,n=0;n<t.length;n++)(t[n].y<e.y&&t[i].y>=e.y||t[i].y<e.y&&t[n].y>=e.y)&&t[n].x+(e.y-t[n].y)/(t[i].y-t[n].y)*(t[i].x-t[n].x)<e.x&&(o=!o),i=n;return o},gpcas.geometry.PolySimple.prototype.isHole=function(){return!1},gpcas.geometry.PolySimple.prototype.setIsHole=function(){alert("PolySimple cannot be a hole")},gpcas.geometry.PolySimple.prototype.isContributing=function(e){return 0!=e&&alert("PolySimple only has one poly"),this.m_Contributes},gpcas.geometry.PolySimple.prototype.setContributing=function(e,t){0!=e&&alert("PolySimple only has one poly"),this.m_Contributes=t},gpcas.geometry.PolySimple.prototype.intersection=function(e){return Clip.intersection(this,e,"PolySimple")},gpcas.geometry.PolySimple.prototype.union=function(e){return Clip.union(this,e,"PolySimple")},gpcas.geometry.PolySimple.prototype.xor=function(e){return Clip.xor(e,this,"PolySimple")},gpcas.geometry.PolySimple.prototype.difference=function(e){return Clip.difference(e,this,"PolySimple")},gpcas.geometry.PolySimple.prototype.getArea=function(){if(this.getNumPoints()<3)return 0;for(var e=this.getX(0),t=this.getY(0),i=0,o=1;o<this.getNumPoints()-1;o++){var n=this.getX(o),l=this.getY(o),r=this.getX(o+1),p=this.getY(o+1),a=(r-n)*(t-l)-(e-n)*(p-l);i+=a};return i=.5*Math.abs(i)},gpcas.geometry.Rectangle=function(e,t,i,o){this.x=e,this.y=t,this.w=i,this.h=o},gpcas.geometry.Rectangle.prototype.getMaxY=function(){return this.y+this.h},gpcas.geometry.Rectangle.prototype.getMinY=function(){return this.y},gpcas.geometry.Rectangle.prototype.getMaxX=function(){return this.x+this.w},gpcas.geometry.Rectangle.prototype.getMinX=function(){return this.x},gpcas.geometry.Rectangle.prototype.toString=function(){return"["+x.toString()+" "+y.toString()+" "+w.toString()+" "+h.toString()+"]"},gpcas.geometry.ScanBeamTree=function(e){this.y=e,this.less,this.more},gpcas.geometry.ScanBeamTreeEntries=function(){this.sbt_entries=0,this.sb_tree},gpcas.geometry.ScanBeamTreeEntries.prototype.build_sbt=function(){var e=[],t=0;return t=this.inner_build_sbt(t,e,this.sb_tree),t!=this.sbt_entries,e},gpcas.geometry.ScanBeamTreeEntries.prototype.inner_build_sbt=function(e,t,i){return null!=i.less&&(e=this.inner_build_sbt(e,t,i.less)),t[e]=i.y,e++,null!=i.more&&(e=this.inner_build_sbt(e,t,i.more)),e},gpcas.geometry.StNode=function(e,t){this.edge,this.xb,this.xt,this.dx,this.prev,this.edge=e,this.xb=e.xb,this.xt=e.xt,this.dx=e.dx,this.prev=t},gpcas.geometry.TopPolygonNode=function(){this.top_node},gpcas.geometry.TopPolygonNode.prototype.add_local_min=function(e,t){var i=this.top_node;return this.top_node=new PolygonNode(i,e,t),this.top_node},gpcas.geometry.TopPolygonNode.prototype.merge_left=function(e,t){t.proxy.hole=!0;var i=this.top_node;if(e.proxy!=t.proxy){e.proxy.v[Clip.RIGHT].next=t.proxy.v[Clip.LEFT],t.proxy.v[Clip.LEFT]=e.proxy.v[Clip.LEFT];for(var o=e.proxy,n=i;null!=n;n=n.next)n.proxy==o&&(n.active=0,n.proxy=t.proxy)}},gpcas.geometry.TopPolygonNode.prototype.merge_right=function(e,t){var i=this.top_node;if(t.proxy.hole=!1,e.proxy!=t.proxy){t.proxy.v[Clip.RIGHT].next=e.proxy.v[Clip.LEFT],t.proxy.v[Clip.RIGHT]=e.proxy.v[Clip.RIGHT];for(var o=e.proxy,n=i;null!=n;n=n.next)n.proxy==o&&(n.active=0,n.proxy=t.proxy)}},gpcas.geometry.TopPolygonNode.prototype.count_contours=function(){for(var e=0,t=this.top_node;null!=t;t=t.next)if(0!=t.active){for(var i=0,o=t.proxy.v[Clip.LEFT];null!=o;o=o.next)i++;i>2?(t.active=i,e++):t.active=0};return e},gpcas.geometry.TopPolygonNode.prototype.getResult=function(e){var t=this.top_node,i=Clip.createNewPoly(e),o=this.count_contours();if(o>0){for(var n=0,l=null,r=t;null!=r;r=l)if(l=r.next,0!=r.active){var p=i;o>1&&(p=Clip.createNewPoly(e)),r.proxy.hole&&p.setIsHole(r.proxy.hole);for(var a=r.proxy.v[Clip.LEFT];null!=a;a=a.next)p.add(a.x,a.y);o>1&&i.addPoly(p),n++};var s=i;i=Clip.createNewPoly(e);for(var u=0;u<s.getNumInnerPoly();u++){var y=s.getInnerPoly(u);y.isHole()||i.addPoly(y)};for(var u=0;u<s.getNumInnerPoly();u++){var y=s.getInnerPoly(u);y.isHole()&&i.addPoly(y)}};return i},gpcas.geometry.TopPolygonNode.prototype.print=function(){for(var e=this.top_node,t=0,i=null,o=e;null!=o;o=i)if(i=o.next,0!=o.active){for(var n=o.proxy.v[Clip.LEFT];null!=n;n=n.next);t++}},gpcas.geometry.VertexNode=function(e,t){this.x,this.y,this.next,this.x=e,this.y=t,this.next=null},gpcas.geometry.VertexType=function(){},gpcas.geometry.VertexType.NUL=0,gpcas.geometry.VertexType.EMX=1,gpcas.geometry.VertexType.ELI=2,gpcas.geometry.VertexType.TED=3,gpcas.geometry.VertexType.ERI=4,gpcas.geometry.VertexType.RED=5,gpcas.geometry.VertexType.IMM=6,gpcas.geometry.VertexType.IMN=7,gpcas.geometry.VertexType.EMN=8,gpcas.geometry.VertexType.EMM=9,gpcas.geometry.VertexType.LED=10,gpcas.geometry.VertexType.ILI=11,gpcas.geometry.VertexType.BED=12,gpcas.geometry.VertexType.IRI=13,gpcas.geometry.VertexType.IMX=14,gpcas.geometry.VertexType.FUL=15,gpcas.geometry.VertexType.getType=function(e,t,i,o){return e+(t<<1)+(i<<2)+(o<<3)},gpcas.geometry.WeilerAtherton=function(){},gpcas.geometry.WeilerAtherton.prototype.merge=function(e,t){e=e.clone(),t=t.clone()};;

    function cb(e){for(var t=e[0],o=1;o<e.length;o++)t=t.union(e[o]);return t=gpv(t)};var PolyDefault=gpcas.geometry.PolyDefault,ArrList=gpcas.util.ArrList,PolySimple=gpcas.geometry.PolySimple,Clip=gpcas.geometry.Clip,OperationType=gpcas.geometry.OperationType,LmtTable=gpcas.geometry.LmtTable,ScanBeamTreeEntries=gpcas.geometry.ScanBeamTreeEntries,EdgeTable=gpcas.geometry.EdgeTable,EdgeNode=gpcas.geometry.EdgeNode,ScanBeamTree=gpcas.geometry.ScanBeamTree,Rectangle=gpcas.geometry.Rectangle,BundleState=gpcas.geometry.BundleState,LmtNode=gpcas.geometry.LmtNode,TopPolygonNode=gpcas.geometry.TopPolygonNode,AetTree=gpcas.geometry.AetTree,HState=gpcas.geometry.HState,VertexType=gpcas.geometry.VertexType,VertexNode=gpcas.geometry.VertexNode,PolygonNode=gpcas.geometry.PolygonNode,ItNodeTable=gpcas.geometry.ItNodeTable,StNode=gpcas.geometry.StNode,ItNode=gpcas.geometry.ItNode,cp=function(e){for(var t=new PolyDefault(),o=0;o<e.length;o++)t.addPoint(new Point(e[o].x,e[o].y));return t},gpv=function(e){var t,o=[],g=e.getNumPoints();for(t=0;g>t;t++)o.push(new google.maps.LatLng(e.getY(t),e.getX(t)));return o};;


    function ra(arr) {
        var cyclonePath = [];
        for (var key in arr) {
            cyclonePath.push(arr[key][0]);
            cyclonePath.push(arr[key][1])
        }
        ;var cp = getCurvePoints(cyclonePath, .10, 20, false), NPath = [];
        for (var i = 0; i < cp.length; i += 2) NPath.push([cp[i], cp[i + 1]]);
        return NPath
    }


    function getCurvePoints(points, tension, numOfSeg, close) {
        'use strict';
        tension = (typeof tension === 'number') ? tension : 0.5;
        numOfSeg = numOfSeg ? numOfSeg : 25;
        var pts, i = 1, l = points.length, rPos = 0, rLen = (l - 2) * numOfSeg + 2 + (close ? 2 * numOfSeg : 0),
            res = new Float32Array(rLen), cache = new Float32Array((numOfSeg + 2) * 4), cachePtr = 4;
        pts = points.slice(0);
        if (close) {
            pts.unshift(points[l - 1]);
            pts.unshift(points[l - 2]);
            pts.push(points[0], points[1])
        } else {
            pts.unshift(points[1]);
            pts.unshift(points[0]);
            pts.push(points[l - 2], points[l - 1])
        }
        ;cache[0] = 1;
        for (; i < numOfSeg; i++) {
            var st = i / numOfSeg, st2 = st * st, st3 = st2 * st, st23 = st3 * 2, st32 = st2 * 3;
            cache[cachePtr++] = st23 - st32 + 1;
            cache[cachePtr++] = st32 - st23;
            cache[cachePtr++] = st3 - 2 * st2 + st;
            cache[cachePtr++] = st3 - st2
        }
        ;cache[++cachePtr] = 1;
        parse(pts, cache, l);
        if (close) {
            pts = [];
            pts.push(points[l - 4], points[l - 3], points[l - 2], points[l - 1]);
            pts.push(points[0], points[1], points[2], points[3]);
            parse(pts, cache, 4)
        }

        function parse(pts, cache, l) {
            for (var i = 2, t; i < l; i += 2) {
                var pt1 = pts[i], pt2 = pts[i + 1], pt3 = pts[i + 2], pt4 = pts[i + 3],
                    t1x = (pt3 - pts[i - 2]) * tension,
                    t1y = (pt4 - pts[i - 1]) * tension, t2x = (pts[i + 4] - pt1) * tension,
                    t2y = (pts[i + 5] - pt2) * tension;
                for (t = 0; t < numOfSeg; t++) {
                    var c = t << 2, c1 = cache[c], c2 = cache[c + 1], c3 = cache[c + 2], c4 = cache[c + 3];
                    res[rPos++] = c1 * pt1 + c2 * pt3 + c3 * t1x + c4 * t2x;
                    res[rPos++] = c1 * pt2 + c2 * pt4 + c3 * t1y + c4 * t2y
                }
            }
        };l = close ? 0 : points.length - 2;
        res[rPos++] = points[l];
        res[rPos] = points[l + 1];
        return res
    }

    function nfc(fts, lt) {
        var h = [];
        for (var key in fts) {
            var convexHull = new ConvexHullGrahamScan(), ft = fts[key], ft0 = fts[key - 1];
            for (i = 0; i <= 360; i += 5) {
                var p = fnp(ft.latitude, ft.longitude, ft.radius, i % 360);
                if (key == 0) {
                    convexHull.addPoint(parseFloat(lt.longitude), parseFloat(lt.latitude))
                } else {
                    var p0 = fnp(ft0.latitude, ft0.longitude, ft0.radius, i % 360);
                    convexHull.addPoint(p0[1], p0[0])
                }
                ;convexHull.addPoint(p[1], p[0])
            }
            ;h.push(cp(convexHull.getHull()))
        }
        ;
        return cb(h)
    }

    function clearCyTracks() {
        setMapNullArray(lines);
        setMapNullArray(cyclone_marker_array);
        setMapNullArray(hourly_cyclone_marker_array);
        setMapNullArray(forecastHull_array);
        setMapNullArray(forecastCircles_array);
    }

    function ConvexHullGrahamScan() {
        this.anchorPoint = void(0), this.reverse = !1, this.points = []
    };ConvexHullGrahamScan.prototype = {
        constructor: ConvexHullGrahamScan, Point: function (a, b) {
            this.x = a, this.y = b
        }, _findPolarAngle: function (a, b) {
            var c = 57.295779513082, d = b.x - a.x, e = b.y - a.y;
            if (0 == d && 0 == e) return 0;
            var f = Math.atan2(e, d) * c;
            return this.reverse ? 0 >= f && (f += 360) : f >= 0 && (f += 360), f
        }, addPoint: function (a, b) {
            if (void(0) === this.anchorPoint) {
                this.anchorPoint = new this.Point(a, b)
            } else if (this.anchorPoint.y > b || this.anchorPoint.y == b && this.anchorPoint.x > a) return this.anchorPoint.y = b, this.anchorPoint.x = a, void(this.points.unshift(new this.Point(a, b)));
            this.points.push(new this.Point(a, b))
        }, _sortPoints: function () {
            var a = this;
            return this.points.sort(function (b, c) {
                var d = a._findPolarAngle(a.anchorPoint, b), e = a._findPolarAngle(a.anchorPoint, c);
                return e > d ? -1 : d > e ? 1 : 0
            })
        }, _checkPoints: function (a, b, c) {
            var d, e = this._findPolarAngle(a, b), f = this._findPolarAngle(a, c);
            return e > f ? (d = e - f, !(d > 180)) : f > e ? (d = f - e, d > 180) : !1
        }, getHull: function () {
            var a, b, c = [];
            if (this.reverse = this.points.every(function (a) {
                return a.x < 0 && a.y < 0
            }), a = this._sortPoints(), b = a.length, 4 > b) return a;
            for (c.push(a.shift(), a.shift()); ;) {
                var d, e, f;
                if (c.push(a.shift()), d = c[c.length - 3], e = c[c.length - 2], f = c[c.length - 1], this._checkPoints(d, e, f) && c.splice(c.length - 2, 1), 0 == a.length) {
                    if (b == c.length) return c;
                    a = c, b = a.length, c = [], c.push(a.shift(), a.shift())
                }
            }
        }
    }, "function" == typeof define && define.amd && define(function () {
        return ConvexHullGrahamScan
    }), "undefined" != typeof module && (module.exports = ConvexHullGrahamScan)

    function fnp(x, y, d, a) {
        var r = 6378.14;
        lat = x * (Math.PI / 180);
        lng = y * (Math.PI / 180);
        b = a * (Math.PI / 180);
        lat2 = Math.asin(Math.sin(lat) * Math.cos(d / r) + Math.cos(lat) * Math.sin(d / r) * Math.cos(b));
        lng2 = lng + Math.atan2(Math.sin(b) * Math.sin(d / r) * Math.cos(lat), Math.cos(d / r) - Math.sin(lat) * Math.sin(lat2));
        return [lat2 * (180 / Math.PI), lng2 * (180 / Math.PI)]
    }

</script>
</body>
</html>