<!DOCTYPE html>
<html lang="en">
<head>
    <link href="//fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="vendor/materialize-0.100.2/css/materialize.min.css"  media="screen,projection"/>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOST VI DRRMU - PAGASA-Meteopilipinas Proxy</title>
<style>
    body {
        overflow: hidden;
    }
    .progress{
        margin: 0;
    }
    #map {
        margin: 0;
        padding: 0;
        height: calc(100vh - 84px);
        background-color: #2ea7c5;
    }
    .mapbox-maplogo {
        position:absolute;
        display:block;
        height: 20px;
        width: 65px;
        left:80px;
        bottom:22px;
        text-indent: -9999px;
        z-index:99999;
        overflow:hidden;
        background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIIAAAAoCAMAAAAFWtJHAAAAwFBMVEUAAAAAAAAAAABtbW0AAAAAAAAAAAAAAAAAAAAAAAClpaUAAADp6ekAAAD5+fna2toAAAAMDAzv7+/Nzc0AAAA2Njb8/Pz9/f3T09MAAAAAAAD7+/sAAAArKyuxsbH39/fs7OwbGxuIiIjz8/N8fHyenp7u7u74+PgAAAC8vLxWVlbx8fF1dXXl5eVcXFyUlJTQ0NDFxcVCQkLAwMC4uLj19fXo6OjW1tarq6ve3t77+/vi4uL6+vrKysrNzc3///8w7gSSAAAAP3RSTlMAOQNdPSYBPywKexLLGPCxNEHXnzFL+v2nGwf1IEiE6dBFad9jd9PuLo1V2mDDV3Cjl06SiuXIq4C3973ym6BQMVUPAAAEXElEQVR4Ae2WCVP6OBiH05L0l1IqrVbkKHJ54I0oHn+PfP9vtUle0z/YdhbH2XVnd58ZnRJIeHiPJOx//mH4vQSAN+8FjAhFxgHIaPvJeZ99hxwEElon5iAQbj85Y98g8ODwjEOMAvGFyeE3FEKgodTBqj0BJGN9DhyNd5Ta3ean9QEopfaA+LsKhnEKRExqg4FSP6Og7oEkAjBWnxSCgBX4xF+kcLoPcOBQrSv0e5kH7s1j37jECQieCTPiFGxL5VHw2zQWCeeJiPt6kjRQw0XSkIdVChf67xGa4alSnZlT6HEQ8CK9ANbhvXUF9xlDkBfTuHDWScgC9+z5FQpPI12TlwC6+sV7ixR8CUMKiwjm2GQeOQWHMGuHGdbnObJAwCEqFJpNU5H6uaPUaEIKiQfg+PHk1+u4OwW9PlWW2ctbA4BHCtp+cNK+H8Jos4gDmC5ar4Nx9waaG/2B13NgDqS7+vm2RgEtEws82P+kwIHhs/pgkQKcFIhfd7CogtGNjYMHTLpurD0ERbYFw4JaD3GlQuNAL/JEsSAF4HqlCnaHACk4WhOn4OgCkMD5hSpYNYDJTD8Y46n+jsE1kPhVCuR6QBXhFK7MUOu9O6b1SWF3b+/9ZVWMGOlu93E8UDaAhgc7bfH+0DHqKXCkHzoNDFfU+zxiVQrUC9QXTuHYtKpN59OA3IxCG4b7jh6ZFuVockaNTW09mkJzOaPU49a6mE9cAchZpQJNpUWcwgV9r6FJswsFKrITp2B5pMBMdnS0z2HZNy2+BNKxSZxZfglkrFYBJxQnpzA5sN/HheR2aFQoZBLAi149dQoyAYYjW0hHlHguBAdMcR0DuDZ5omevX6+AI8qcU7ikKT3GBHCnXwydgmCC0tRwCnGQ2Wp6Be71yNIWfQSkOl9vAI1SBCNWrwC01RROgX7BuT2HI4r7tFAw086p/NwZEdOEa7R1uAFuNmQPuKAEAjYNQ0CyeoUEWHYBnpQVQgpvc0Ph+gsKlAnKg1+vEHsw5LKciLKCAJobiWBzYFGbCKpHqkZZrxBFHEASyFI59vJPCskcwNVGOWZAOqsrR+pKbaNeAMT1CixMEtlnsqopNxUMzVJT3tY35aXZm6a6Y9QhwMN6BUJWbE1lhbMO1WehkO7poO0sK7em9MJGxp1XSbC1gtugzzSLQmGsX7VntJGSwsPZ2d2z3bIPKzdoOp3Wzqt8G4XyMVUoFIxLx1S7+piaHtCvR3FeRVsq0GFdp9C5TbGpcNqsPqyHKxcfd14h21KhuLKUFU4f3osrC7F6uV3WXFnadL7wyAPeKDXw2RoJCO5GY4DouYvb/gepVXheLoewzPseQG9N/vzilrMIjoStE3++zvle4eSurw7XEe76ynI4aq+v7lEyt1x5awiFlFLQbHKIpabnM3eJLym4Szzzc/du7SU+zOXv9UNpECH7IoH/gecURPlN9vdQpeD47yhIFNX0U0QgvID9nENm+yxk/xb+AGAjNfRZuk9qAAAAAElFTkSuQmCC);
        background-repeat:no-repeat;
        background-position: 0 0 ;
        background-size: 65px 20px;
    }

    #disclamer {
        width: 100vw;
        height: 20px;
        background-color: #000058;

    }

    div[id="disclamer"] > span,
    div[id="disclamer"] > a
    {
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
            <li><a class="dropdown-button" href="#!" data-activates="dropdown1">Start Here<i class="material-icons right">arrow_drop_down</i></a></li>
        </ul>
    </div>
</nav>
<div class="progress">
    <div class="indeterminate"></div>
</div>
<div id="map">

</div>
<a id="doppler_time_selector" class="btn-floating btn-large waves-effect waves-light red btn modal-trigger" href="#modal1"><i class="material-icons">access_time</i></a>
<div id="controls"></div>
<a href="http://mapbox.com/about/maps" class='mapbox-maplogo' target="_blank">MapBox</a>
<div id="disclamer"><span>powered by DOST-PAGASA </span><a href="https://v2.meteopilipinas.gov.ph" target="_blank">METEOPILIPINAS</a></div>
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
<script>
    $(document).ready(function(){
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

    function initMap() {
        //var dost_center = new google.maps.LatLng(10.712317, 122.562362);
        var dost_center = new google.maps.LatLng(13, 122);
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 6,
            minZoom: 6,
            center: dost_center,
            mapTypeId: 'mapbox',
            disableDefaultUI: true,
        });

        map.mapTypes.set("mapbox", new google.maps.ImageMapType({
            getTileUrl: function(coord, zoom) {
                var tilesPerGlobe = 1 << zoom
                    , x = coord.x % tilesPerGlobe;
                if (x < 0)
                    x = tilesPerGlobe + x;
                return "//api.mapbox.com/styles/v1/dost6ryanb/cjcipbquu0khs2rqrlgcz44y7/tiles/256/" + zoom + "/" + x + "/" + coord.y + "?access_token=pk.eyJ1IjoiZG9zdDZyeWFuYiIsImEiOiI1OGMyZjdjNjZlYjlhNTMyNDc0NGQxOTY4ZDJlZjIxNyJ9.dkASVYIEPInwAEkwUkaGhQ";
            },
            tileSize: new google.maps.Size(256,256),
            name: "MapBox",
            maxZoom: 18
        }));

        map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('controls'));
        hideControls();

        attachDropdDownHandlers()
        METEO_MAP = map;
    }

    function attachDropdDownHandlers() {
        $("#nav_phdoppler").click(function(){
            console.log("Calling doppler()");
            doppler();
        });
        $("#nav_himwari").click(function(){
            console.log("Calling satellite()");
            satellite();
        });
        $("#nav_tytrack").click(function(){
            console.log("Calling tytrack()");
            tytrack();
        });
    }

    function doppler() {
        $.getJSON("meteo_proxy.php", {rq: 'ph-doppler'})
            .done(function(data){
                hideControls();
                $("#doppler_time_selector").show();
                var result = data['result'];
                var dbounds = JSON.parse(result['bounds']);
                var bounds = new google.maps.LatLngBounds(new google.maps.LatLng(dbounds["s"], dbounds["w"]), new google.maps.LatLng(dbounds["n"], dbounds["e"]));

                var el = document.getElementById('doppler_times');
                $(el).empty();
                $.each(result['data'], function(k, v) {
                    var time = v['time_mosaic'];
                    var overlay_image = v['output_image_transparent_on_www'];
                    var doppler_overlay = new google.maps.GroundOverlay(overlay_image, bounds, {clickable: false});

                    if (time) {
                        $('<a/>', {id: k, name: k, text: time, class:"collection-item", href:"#!"}).appendTo(el)
                            .on('click', function() {
                                swapCurrentOverlay(doppler_overlay)
                                $('#modal1').modal('close');
                            });
                    } else {
                        $('<a/>', {id: k, name: k, text: "Animated", class:"collection-item", href:"#!"}).attr("active", '').prependTo(el)
                            .on('click', function() {
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
            .done(function(data){
                hideControls();
                var result = data['result'];
                var bounds = JSON.parse(result.bounds);
                console.log(result);
                var swBound = new google.maps.LatLng(bounds.s,bounds.w);
                var neBound = new google.maps.LatLng(bounds.n,bounds.e);
                var imageBounds = new google.maps.LatLngBounds(swBound,neBound);
                console.log(imageBounds);

                var satImg = result.animated_image;
                var sat_overlay = new google.maps.GroundOverlay(satImg,imageBounds);
                console.log(satImg);
                console.log(sat_overlay);
                swapCurrentOverlay(sat_overlay);
            });
    }

    function tytrack() {
        alert("Not yet implemented");
    }

    function hideControls() {
        $("#doppler_time_selector").hide();
        $('.progress').hide();
    }

    function swapCurrentOverlay(overlay) {
        if (CURRENT_OVERLAY) {
            CURRENT_OVERLAY.setMap(null);
            overlay.setMap(METEO_MAP);
            CURRENT_OVERLAY = overlay;
        } else {
            overlay.setMap(METEO_MAP);
            CURRENT_OVERLAY = overlay;
        }
    }

</script>
<script async defer
        src="//maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&callback=initMap">
</script>
</body>
</html>