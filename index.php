<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once 'lib/init3.php' ?>
    <meta charset="utf-8">
    <title>DOST VI DRRMU - Home</title>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.theme.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.structure.min.css'/>
    <link rel="stylesheet" type="text/css" href='css/style.css'/>
    <link rel="stylesheet" type="text/css" href='css/screen.css'/>
    <link rel="stylesheet" type="text/css" href='css/pages/index.css'/>
    <script type="text/javascript" src='vendor/jquery/jquery-1.12.4.min.js'></script>
    <script type="text/javascript" src='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js'></script>
    <script type="text/javascript" src='vendor/datejs/date.js'></script>
    <script type="text/javascript" src='js/jquery.scrollTo.min.js'></script>
    <script type="text/javascript" src='js/jquery.easy-ticker.min.js'></script>
    <script type="text/javascript" src='js/heat-index.js'></script>
    <script type="text/javascript" src="js/tytrack_pagasa.js"></script>
    <script type="text/javascript"
            src="vendor/gasparesganga-jquery-loading-overlay-2.1.6/loadingoverlay.min.js"></script>
    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&sensor=false"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script type="text/javascript">
        setTimeout(function () {
            window.location.reload(true);
        }, 1800000); // refresh 30 minutes
        var key = {
            'sdate': SDATE, 'edate': EDATE, 'numraindevices': 0, 'loadedraindevices': 0,
            'serverdate': '<?php echo date("Y-m-d");?>', 'servertime': '<?php echo date("H:i");?>',
            'startDateTime': '',
            'endDateTime': '',
            'marker': [
                {'min': 0.01, 'max': 5, 'name': 'lighter', 'src': 'images/rain-lighter'},
                {'min': 5, 'max': 25, 'name': 'light', 'src': 'images/rain-light'},
                {'min': 25, 'max': 50, 'name': 'moderate', 'src': 'images/rain-moderate'},
                {'min': 50, 'max': 75, 'name': 'heavy', 'src': 'images/rain-heavy'},
                {'min': 75, 'max': 100, 'name': 'intense', 'src': 'images/rain-intense'},
                {'min': 100, 'max': 999, 'name': 'torrential', 'src': 'images/rain-torrential'}
            ]

        };

        key['startDateTime'] = Date.parseExact(key['sdate'] + ' 08:00:00', 'yyyy-MM-dd HH:mm:ss');
        key['endDateTime'] = Date.parseExact(key['edate'] + ' 07:59:59', 'yyyy-MM-dd HH:mm:ss');

        var DOST_CENTER;
        var WV_MAP;
        var WV_MAP_MARKERS = [];
        var WV_BOUNDARIES;
        var ACTIVE_UI = "rainfall";
        var CURRENT_OVERLAY;
        var HISTORY = false;

        var LAST_RAIN_DEVID = 0, LAST_WTR_DEVID = 0, LAST_AWS_DEVID = 0;

        var lines = [],
            cyclone_marker_array = [],
            hourly_cyclone_marker_array = [],
            forecastCircles_array = [],
            cyclonePath_array = [],
            cyclonePath_LatLng = [],
            forecastLine_array = [],
            forecastHull_array = [];

        google.charts.load('current', {packages: ['corechart']});

        $.xhrPool = [];
        $.xhrPool.abortAll = function () {
            $(this).each(function (idx, jqXHR) {
                console.log('abort ajax');
                jqXHR.abort();
            });
            $.xhrPool.length = 0
        };

        $.ajaxSetup({
            beforeSend: function (jqXHR) {
                console.log('push ajax');
                $.xhrPool.push(jqXHR);
            },
            complete: function (jqXHR) {
                var index = $.xhrPool.indexOf(jqXHR);
                if (index > -1) {
                    $.xhrPool.splice(index, 1);
                }
            }
        });

        google.charts.setOnLoadCallback(function () {
            $(document).ready(function () {
                $.LoadingOverlaySetup({zIndex: 50,fade: false});
                initMap("map-canvas");
                initControls();
                initRainfallTable("rainfall-canvas");
                initFetchData();
                initFeedee();
            });
        });

        function initControls() {
            initMapLegends('legends');
            initMapChooser('chooser');
            initDopplerControls('dopplertime');
        }

        function initFetchData(history) {
            if (history) {
                HISTORY = true;

                postGetDataBulk(rainfall_device_ids_enabled, key['sdate'], key['edate'], 'rainfall', onRainfallDataResponseSuccess, 'map-canvas', function () {
                    postGetDataBulk(rainfall_device_ids_disabled, key['sdate'], key['edate'], 'rainfall', onRainfallDataResponseSuccess, '');
                });
                postGetDataBulk(waterlevel_device_ids_enabled, key['sdate'], key['edate'], 'waterlevel', onWaterlevelDataResponseSuccess, 'charts_div_container', function () {
                    postGetDataBulk(waterlevel_device_ids_disabled, key['sdate'], key['edate'], 'waterlevel', onWaterlevelDataResponseSuccess, '');
                });
            } else {
                postGetDataBulk(rainfall_device_ids_enabled, key['sdate'], key['edate'], 'rainfall', onRainfallDataResponseSuccess, 'map-canvas');
                postGetDataBulk(waterlevel_device_ids_enabled, key['sdate'], key['edate'], 'waterlevel', onWaterlevelDataResponseSuccess, 'charts_div_container');
            }

            /*
            setTimeout(function () {

                var t = getIndexOfDevID(rainfall_devices, LAST_RAIN_DEVID);
                console.log(t);
                for (var i = t; i < rainfall_devices.length; i++) {
                    var cur = rainfall_devices[i];
                    if (history) {
                        postGetData(cur['dev_id'], key['sdate'], key['edate'], 1, onRainfallDataResponseSuccess);
                    } else {
                        if (cur['status'] == null || cur['status'] == '0') {
                            postGetData(cur['dev_id'], key['sdate'], key['edate'], 1, onRainfallDataResponseSuccess);
                        } // else SKIP
                    }

                }

            }, 200);

            setTimeout(function () {
                var t = getIndexOfDevID(waterlevel_devices, LAST_WTR_DEVID);
                for (var i = t; i < waterlevel_devices.length; i++) {
                    var cur = waterlevel_devices[i];
                    if (history) {
                        postGetData(cur['dev_id'], key['sdate'], key['edate'], "144", onWaterlevelDataResponseSuccess);
                    } else {
                        if (cur['status'] == null || cur['status'] == '0') {
                            postGetData(cur['dev_id'], key['sdate'],  key['edate'], "", onWaterlevelDataResponseSuccess);
                        }
                    }
                }
            }, 200);

            setTimeout(function () {
                var t = getIndexOfDevID(temperature_devices, LAST_AWS_DEVID);
                for (var i = t; i < temperature_devices.length; i++) {
                    var cur = temperature_devices[i];
                    // if (history) {
                    postGetData(cur.dev_id, key['sdate'], key['sdate'], 96, onTemperatureDataResponseSuccess);
                    // } else {
                    // 	if (cur['status'] == null || cur['status'] == '0') {
                    // 		postGetData(cur.dev_id, key['sdate'], "", "", onTemperatureDataResponseSuccess);
                    // 	}
                    // }
                }

            }, 200);

            */
        }

        function postGetData(dev_id, sdate, edate, limit, successcallback) {
            $.ajax({
                url: DOCUMENT_ROOT + 'data3.php',
                type: "POST",
                data: {
                    start: 0,
                    sdate: sdate,
                    edate: edate,
                    pattern: dev_id,
                },
                dataType: 'json',
                tryCount: 0,
                retry: 20
            })
                .done(successcallback)
                .fail(function (f, n) {
                    onRainfallDataResponseFail(dev_id)
                });
        }

        function postGetDataBulk(dev_ids, sdate, edate, type, successcallback, div, cba) {
            if (div != '') {
                $("#" + div).LoadingOverlay("show");
            }
            $.ajax({
                url: DOCUMENT_ROOT + 'data5.php',
                type: "POST",
                data: {
                    dev_ids: dev_ids,
                    sdate: sdate,
                    edate: edate,
                    type: type,
                },
                dataType: 'json',
                tryCount: 0,
                retry: 20
            }).done(function (d) {
                if (div != '') {
                    $("#" + div).LoadingOverlay("hide");
                }
                if (cba !== 'undefined' && typeof  cba === 'function') {
                    cba();
                }
                d.forEach(function (e) {
                    successcallback(e);
                })
            }).fail(function (f, n) {
                if (div != '') {
                    $("#" + div).LoadingOverlay("hide");
                }
            });
        }

        function onRainfallDataResponseSuccess(data) {
            var device_id = data[0].station_id;
            LAST_RAIN_DEVID = device_id;
            $('#loadedraindevices').text(++key['loadedraindevices']);

            var newdata = $.grep(data.Data, function (n, i) {
                thisdate = Date.parseExact(n['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
                result = thisdate.between(key['startDateTime'], key['endDateTime']);
                //if (result) console.log(thisdate.toString() + " - " + result);
                return result;
            });
            var len = newdata.length;
            data.Data = newdata;
            data.Data.length = len;

            if (data.Data.length == 0) {
                updateRainfallTable(device_id, '[NO DATA]', '', '', 'nodata');
            } else {
                var last = data.Data.length - 1;
                var device = search(rainfall_devices, 'dev_id', device_id);
                var devicedtr = Date.parseExact(data.Data[last]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
                //<#-- ASTI BSWM_Lufft not ISO STANDARD dateTimeRead FIX -_-
                if (!devicedtr) {
                    var datefixed = data.data[0].dateTimeRead.substring(0, 19);
//				console.log(datefixed);
                    devicedtr = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                }//--#>
                var serverdtr = Date.parseExact(key['serverdate'] + ' ' + key['servertime'] + ':00', 'yyyy-MM-dd HH:mm:ss');
                var hour12time = devicedtr.toString("h:mm tt");

                var rc = getRainCumulative(data.Data);
                var rv = parseFloat(data.Data[last]['Rainfall Amount']).toFixed(2);
                if (key['sdate'] == key['serverdate'] && devicedtr.add({minutes: 15}).compareTo(serverdtr) == -1) { //late
                    updateRainfallTable(device_id, hour12time, rv, rc, 'latedata');
                } else {
                    updateRainfallTable(device_id, hour12time, rv, rc, 'dataok');
                }

                if (!HISTORY && !(device['status'] == null || device['status'] == '0')) {
                    return;
                }

                var marker_url;
                for (var i = 0; i < key['marker'].length; i++) {
                    limit = key['marker'][i];
                    if (rc >= parseFloat(limit['min']) && rc < parseFloat(limit['max'])) {
                        if (rv > 0) {
                            marker_url = limit['src'] + '_now.png';
                        } else {
                            marker_url = limit['src'] + '.png';
                        }
                        addMarker(device['dev_id'], device['posx'], device['posy'], device['municipality'] + ' - ' + device['location'], device['type'], marker_url);
                        var text = "[Cumulative Rainfall] " + device['municipality'] + ' - ' + device['location'] + ' : ' + rc + ' mm';
                        addTicker(text, 'ticker--1__list');

                        break;
                    }
                }
            }
        }

        function getRainCumulative(data) {
            var total = 0;
            $.each(data, function () {
                var rn = parseFloat(this['Rainfall Amount']);
                total += rn;
            });
            return total.toFixed(2);
        }

        function onRainfallDataResponseFail(dev_id) {
            var retryhtml = '<a href=javascript:retryFetchRain(' + dev_id + ')>Retry</a>';
            updateRainfallTable(dev_id, retryhtml, null, null);
        }

        function retryFetchRain(dev_id) {
            postGetData(dev_id, key['sdate'], key['edate'], 1, onRainfallDataResponseSuccess);
            updateRainfallTable(dev_id, '', '', '');
        }

        function onWaterlevelDataResponseSuccess(data) {
            updateWaterlevelChart(data)
        }

        function onTemperatureDataResponseSuccess(data) {
            updateTemperatureTicker(data);
        }

        function initMap(divcanvas) {
            DOST_CENTER = new google.maps.LatLng(10.712317, 122.562362); //DOST CENTER

            var mapOptions = {
                //zoom: 6, //Whole Philippines View
                zoom: 8, //Region 6 Focus,
                center: DOST_CENTER,
                disableDefaultUI: true,
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE,
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
                mapStyleControl: true,
                draggableCursor: 'crosshair',
                mapTypeId: 'mapbox',
                styles: [{
                    "featureType": "administrative.land_parcel",
                    "stylers": [{"visibility": "off"}]
                }, {"featureType": "poi", "stylers": [{"visibility": "off"}]}, {
                    "featureType": "road",
                    "stylers": [{"visibility": "off"}]
                }, {"featureType": "road.highway", "stylers": [{"visibility": "on"}]}, {
                    "featureType": "road.arterial",
                    "stylers": [{"visibility": "on"}]
                }, {"featureType": "landscape", "stylers": [{"lightness": 47}]}, {
                    "featureType": "water",
                    "stylers": [{"lightness": 39}]
                }]
            };

            WV_MAP = new google.maps.Map(document.getElementById(divcanvas), mapOptions);
            WV_MAP.mapTypes.set("mapbox", new google.maps.ImageMapType({
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
            }));

            var lineSymbol = {
                path: 'M 0,-1 0,1',
                strokeOpacity: 1,
                scale: 4,
                strokeColor: 'white',
                strokeWeight: 1
            };

            var line = new google.maps.Polyline({
                path: [{lat: 25, lng: 120}, {lat: 25, lng: 135}, {lat: 5, lng: 135}, {lat: 5, lng: 115}, {
                    lat: 15,
                    lng: 115
                }, {lat: 21, lng: 120}, {lat: 25, lng: 120}],
                strokeOpacity: 0,
                icons: [{
                    icon: lineSymbol,
                    offset: '0',
                    repeat: '20px'
                }],
                map: WV_MAP
            });
        }

        function initMapLegends(container) {
            legendscontainer = $(document.getElementById(container));
            WV_MAP.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(document.getElementById(container));

            $('#togglelegends')
                .on('click', function () {
                    $('.legend').toggle();
                    $('.legendtitle').toggle();
                });
        }

        function initMapChooser(container) {
            choosercontainer = $(document.getElementById(container));
            //WV_MAP.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById(container));

            $("#toggleLayers").on('click', function () {
                $(this).hide();
                $("#layersform").show();
            });

            $("#layersform input").on('click', function () {
                $("#layersform").hide();
                $("#toggleLayers").show();
            });

            $("#layersform").on('mouseleave', function () {
                $("#layersform").hide();
                $("#toggleLayers").show();
            });

            $("#toggleRainfallMap").on('click', function () {
                if (ACTIVE_UI == 'rainfall') return; else hideCurrentAndShowNewUI(ACTIVE_UI, 'rainfall');

                console.log("toggleRainfallMap");
                makeActiveClassOnly('#toggleRainfallMap');
            });

            $("#toggleDoppler").on('click', function () {
                if (typeof WV_BOUNDARIES == 'undefined') initBoundaries();
                if (ACTIVE_UI == 'doppler') return; else hideCurrentAndShowNewUI(ACTIVE_UI, 'doppler');

                console.log("toggleDoppler");
                initDoppler();

                makeActiveClassOnly('#toggleDoppler');

            });

            $("#toggleTyphoonTrack").on('click', function () {
                if (ACTIVE_UI == 'tytrack') return; else hideCurrentAndShowNewUI(ACTIVE_UI, 'tytrack');


                console.log("toggleTyphoonTrack");
                initTyphoonTrack();

                makeActiveClassOnly('#toggleTyphoonTrack');
            });

            $("#toggleSatellite").on('click', function () {
                if (ACTIVE_UI == 'satellite') return; else hideCurrentAndShowNewUI(ACTIVE_UI, 'satellite');


                console.log("toggleSatellite");
                initSatellite();

                makeActiveClassOnly('#toggleSatellite');
            });

            $("#toggleWeatherForecast").on('click', function () {
                $("#regionalweather").dialog("open");
            });


            function showRainfallUI() {
                $('#legends').show();
            }

            function hideRainfallUI() {
                $('#legends').hide();
            }

            function showDopplerUI() {
                $('#dopplertime').show();
            }

            function hideDopplerUI() {
                $('#dopplertime').hide().empty();
            }

            function showSatUI() {

            }

            function hideSatUI() {

            }

            function showTyTrackUI() {

            }

            function hideTyTrackUI() {

            }

            function fullScreenMapMode() {
                $('#rainfall-canvas').css({width: '0%'}).hide();
                $('#map-canvas').css({width: '100%', height: '740px'});
                $('#charts_div_container').hide();
                console.log('fullscreen');
                WV_MAP.setZoom(6);
            }

            function classicMode() {
                $('#rainfall-canvas').css({width: '30%'}).show();
                $('#map-canvas').css({width: '70%', height: '520px'});
                $('#charts_div_container').show();
                console.log('classic');
                WV_MAP.panTo(DOST_CENTER);
                WV_MAP.setZoom(8);
            }

            function hideCurrentAndShowNewUI(state, newState) {
                $.xhrPool.abortAll();
                switch (state) {
                    case 'rainfall':
                        hideRainfallUI();
                        setMarkersVisibility(false);
                        break;
                    case 'doppler':
                        if (WV_BOUNDARIES) WV_BOUNDARIES.setMap(null);
                        if (CURRENT_OVERLAY) CURRENT_OVERLAY.setMap(null);
                        hideDopplerUI();
                        break;
                    case 'satellite':
                        if (CURRENT_OVERLAY) CURRENT_OVERLAY.setMap(null);
                        hideSatUI();
                        break;
                    case 'tytrack':
                        if (CURRENT_OVERLAY) CURRENT_OVERLAY.setMap(null);
                        hideTyTrackUI();
                        clearCyTracks();
                        break;

                }

                switch (newState) {
                    case 'rainfall':
                        initFetchData();
                        showRainfallUI();
                        setMarkersVisibility(true);
                        classicMode();
                        break;
                    case 'doppler':
                        WV_BOUNDARIES.setMap(WV_MAP);
                        showDopplerUI();
                        fullScreenMapMode();
                        break;
                    case 'satellite':
                        showSatUI();
                        fullScreenMapMode();
                        break;
                    case 'tytrack':
                        showTyTrackUI();
                        fullScreenMapMode();
                        break;

                }

                ACTIVE_UI = newState;
            }

            function makeActiveClassOnly(active) {
                $('#chooser').children('button').removeClass('active');
                $(active).addClass('active');
            }
        }

        function initDoppler() {
            $.ajax({
                dataType: 'json',
                cache: false,
                url: "meteo_proxy.php",
                //data: {rq: 'iloilo-doppler'}
                data: {rq: 'ph-doppler'}
            }).done(function (data) {
                var result = data['result'];
                var dbounds = JSON.parse(result['bounds']);
                var bounds = new google.maps.LatLngBounds(new google.maps.LatLng(dbounds["s"], dbounds["w"]), new google.maps.LatLng(dbounds["n"], dbounds["e"]));

                var $dopplertime = $('#dopplertime');
                $.each(result['data'], function (k, v) {
                    var time = v['time_mosaic'],
                        overlay_image = v['output_image_transparent_on_www'],
                        doppler_overlay = new google.maps.GroundOverlay(overlay_image, bounds, {clickable: false});
                    // if (time) {
                    $('<button/>', {id: k, name: k, text: time}).appendTo($dopplertime)
                        .on('click', function () {
                            swapCurrentOverlay(doppler_overlay);
                            $dopplertime.children('button').removeClass('active');
                            $(this).addClass('active');
                        });
                    /*} else {
                       $('<button/>', {id: k, name: k, text: "Animated", class: 'active'}).prependTo($dopplertime)
                           .on('click', function() {
                               swapCurrentOverlay(doppler_overlay);
                               $dopplertime.children('button').removeClass('active');
                               $(this).addClass('active');
                           });

                       swapCurrentOverlay(doppler_overlay);
                    }*/
                });
                var overlay_image = result["gif"],
                    doppler_overlay = new google.maps.GroundOverlay(overlay_image, bounds, {clickable: false});

                $('<button/>', {
                    id: "AnimatedDoppler",
                    name: "AnimatedDoppler",
                    text: "Animated",
                    class: 'active'
                }).prependTo($dopplertime)
                    .on('click', function () {
                        swapCurrentOverlay(doppler_overlay);
                        $dopplertime.children('button').removeClass('active');
                        $(this).addClass('active');
                    });

                swapCurrentOverlay(doppler_overlay);
            });
        }

        function initBoundaries() {
            WV_BOUNDARIES = new google.maps.Data();
            WV_BOUNDARIES.loadGeoJson('region6.geojson');


            WV_BOUNDARIES.setStyle({
                fillColor: 'white',
                strokeColor: '#ff51d7',
                fillOpacity: 0,
                strokeWeight: 1
            });

            google.maps.event.addListener(WV_MAP, 'zoom_changed', function () {
                zoomLevel = WV_MAP.getZoom();
                console.log(zoomLevel);
                if (zoomLevel >= 8) {
                    WV_BOUNDARIES.setStyle({
                        fillColor: 'white',
                        strokeColor: '#ff51d7',
                        fillOpacity: 0,
                        strokeWeight: 2
                    });

                } else {
                    WV_BOUNDARIES.setStyle({
                        fillColor: 'white',
                        strokeColor: '#ff51d7',
                        fillOpacity: 0,
                        strokeWeight: 1
                    });
                }
            });

        }

        function initSatellite() {
            var swBound = new google.maps.LatLng(-1.0593208520000024, 103.99541937000095);
            var neBound = new google.maps.LatLng(30.014531363000003, 147.02927158600028);
            var imageBounds = new google.maps.LatLngBounds(swBound, neBound);

            var satImg = "http://121.58.193.148/repo/mtsat-colored/24hour/latest-him-colored-hourly.gif";
            var sat_overlay = new google.maps.GroundOverlay(satImg, imageBounds);
            swapCurrentOverlay(sat_overlay);
        }

        function initTyphoonTrack() {
            $.getJSON('meteo_proxy.php', {rq: 'cyclone-track'})
                .done(function (d) {
                    var tracks = d['result'];
                    var value = "hourly";

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
                    }

                    for (var key in tracks) {
                        var data = tracks[key], cycloneName = data.cyclone_name, cycloneInfos = data.info, lastPoint,
                            lastTrack = null, forecastTrack = [], cyclonePath_LatLng = [];

                        for (var key in cycloneInfos) {
                            var cycloneInfo = cycloneInfos[key];

                            cyclonePath_array.push(new google.maps.LatLng(cycloneInfo.latitude, cycloneInfo.longitude));
                            cyclonePath_LatLng.push([cycloneInfo.latitude, cycloneInfo.longitude]);

                            var image = new google.maps.MarkerImage(cycloneInfo.icon, null, new google.maps.Point(0, 0), new google.maps.Point(13, 13));
                            var cyclone_marker = addTyTrackMarker({
                                lat: cycloneInfo.latitude,
                                lng: cycloneInfo.longitude,
                                title: cycloneName,
                                icon: image,
                                infoWindow: {
                                    content: "<p>" + cycloneName + "</p>as of: " + cycloneInfo.dateTime + "<br/>Coordinates: " + cycloneInfo.latitude + "° " + cycloneInfo.longitude + "°"
                                }
                            }, WV_MAP);

                            if (value == "hourly") {
                                hourly_cyclone_marker_array.push(cyclone_marker)
                            } else {
                                cyclone_marker_array.push(cyclone_marker);
                            }
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

                        if (forecastTrack.length > 0) {
                            polygon = drawPolygon({
                                paths: nfc(forecastTrack, lastTrack),
                                strokeOpacity: 0.8,
                                strokeWeight: 2,
                                strokeColor: '#00868B'
                            }, WV_MAP);
                        }

                        line = drawPolyline({
                            path: ra(cyclonePath_LatLng),
                            strokeColor: '#008000',
                            strokeOpacity: 1,
                            strokeWeight: 2,
                            icons: [{
                                icon: lineSymbol,
                                offset: '100%'
                            }]
                        }, WV_MAP);

                        for (var key in forecastTrack) {
                            var cycloneInfo = forecastTrack[key]
                                , cyclone_circles = drawCircle({
                                lat: cycloneInfo.latitude,
                                lng: cycloneInfo.longitude,
                                radius: cycloneInfo.radius * 1e3,
                                fillColor: cycloneInfo.color,
                                fillOpacity: 0.3,
                                strokeOpacity: 0.5,
                                strokeWeight: 1
                            }, WV_MAP);
                            forecastCircles_array.push(cyclone_circles);
                            //addToCoordinates(cyclone_circles);
                        }

                        lines.push(line);
                        if (typeof polygon != 'undefined')
                            forecastHull_array.push(polygon)
                    }
                });
        }

        function clearCyTracks() {
            setMapNullArray(lines);
            setMapNullArray(cyclone_marker_array);
            setMapNullArray(hourly_cyclone_marker_array);
            setMapNullArray(forecastHull_array);
            setMapNullArray(forecastCircles_array);
        }

        function setMapNullArray(markers) {
            for (var key in markers)
                markers[key].setMap(null);
            markers.splice(0, markers.length);
            markers = []
        }

        function swapCurrentOverlay(overlay) {
            if (CURRENT_OVERLAY) {
                CURRENT_OVERLAY.setMap(null);
                overlay.setMap(WV_MAP);
                CURRENT_OVERLAY = overlay;
            } else {
                overlay.setMap(WV_MAP);
                CURRENT_OVERLAY = overlay;
            }
        }

        function initDopplerControls(container) {
            dopplertimecontainer = $(document.getElementById(container));
            WV_MAP.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(document.getElementById(container));
        }

        function initTicker(ticker) {
            $(document.getElementById(ticker)).css({'display': 'block'}).easyTicker({visible: 1, interval: 3500});
        }

        function initRainfallTable(div) {
            $sdate = $("#sdate");
            $sdate.text(key['sdate']);

            $('#dtpicker2').datepicker({
                onSelect: function (data) {
                    $sdate.text(data);
                    newsdate = Date.parseExact(data, 'MM/dd/yyyy');
                    newedate = Date.parseExact(data, 'MM/dd/yyyy');
                    newedate = newedate.addDays(1);
                    key['sdate'] = newsdate.toString('yyyy-MM-dd');
                    key['edate'] = newedate.toString('yyyy-MM-dd');
                    startDateTime = Date.parseExact(key['sdate'] + ' 08:00:00', 'yyyy-MM-dd HH:mm:ss');
                    endDateTime = Date.parseExact(key['edate'] + ' 07:59:59', 'yyyy-MM-dd HH:mm:ss');
                    key['startDateTime'] = startDateTime;
                    key['endDateTime'] = endDateTime;
                    key['numraindevices'] = 0;
                    key['loadedraindevices'] = 0;
                    $.xhrPool.abortAll();
                    clearMarkers();
                    clearRainfallTable();
                    clearAllTicker('ticker1list');
                    clearAllTicker('ticker2list');
                    LAST_RAIN_DEVID = 0;
                    LAST_AWS_DEVID = 0;
                    LAST_WTR_DEVID = 0;
                    initFetchData(true);

                }
            });
            $sdate.click(function () {
                $('#dtpicker2').datepicker('show');
            });

            $("#serverdate").text(SERVER_DATE);
            $("#servertime").text(SERVER_TIME);
            $("#numraindevices").text(rainfall_device_ids_enabled.length  + rainfall_device_ids_disabled.length);

        }

        function initFeedee(div) {
            $.ajax({
                url: 'regional-weather-forecast.php',
                tryCount: 0,
                retryLimit: 3,
            }).done(function (res) {
                console.log("success");

                $("#regionalweather_issuedat").text(res.issuedat);
                $("#regionalweather_validity").text(res.validity);
                $("#regionalweather_synopsis").text(res.synopsis);
                $("#regionalweather_forecast").text(res.forecast);

            }).fail(function (jqXHR, textStatus) {
                console.log("fail retrying");
                this.tryCount++;
                if (this.tryCount <= this.retryLimit) {
                    $.ajax(this);
                    return;
                }
                console.log("failed");
            });

            $("#regionalweather").dialog({
                autoOpen: false,
                width: 600
            });

        }

        function drawChartWaterlevel(chartdiv, json) {
            var last = json.Data.length - 1;
            var datatable = new google.visualization.DataTable();
            datatable.addColumn('datetime', 'DateTimeRead');
            datatable.addColumn('number', 'Waterlevel'); //add column from index i

            for (var j = 0; j < json.Data.length; j++) {
                var row = Array(2);
                row[0] = Date.parseExact(json.Data[j]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
                waterlevel = json.Data[j]['Waterlevel'];
                if (waterlevel != null) {
                    row[1] = {
                        v: parseFloat(waterlevel),
                        f: waterlevel + ' m'
                    };
                }
                datatable.addRow(row);
            }

            var d = Date.parseExact(json.Data[0]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
            var d2 = Date.parseExact(json.Data[last]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');

            //var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
            var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
            var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');

            var options = {
                title: title_enddatetime,

                hAxis: {
                    title: 'Waterlevel: ' + json.Data[0]['Waterlevel'] + ' m',
                    format: 'LLL d h:mm:ss a',
                    viewWindow: {min: d, max: d2},
                    gridlines: {color: 'none'},
                    textStyle: {fontSize: 10},
                    textPosition: 'none'
                },
                vAxis: {
                    title: '',
                    format: '# m',
                    minValue: '0',
                    maxValue: '12',
                    gridlines: {count: 13},
                    viewWindow: {min: 0},
                },
                legend: {
                    position: 'none'
                },
                pointsize: 3,
                seriesType: 'area',
                crosshair: {trigger: 'both'},
                allowHtml: true
            };

            var chart = new google.visualization.ComboChart(document.getElementById(chartdiv));
            chart.draw(datatable, options);
            //$('<div/>').text('Waterlevel: '+json.data[0].waterlevel+ ' cm').css({'height':'20px'}).appendTo('#'+chartdiv);
        }

        function updateRainfallTable(device_id, dateTimeRead, rainvalue, raincumulative, dataclass) {
            var tr = $('tr[data-dev_id=\'' + device_id + '\']');
            var dtr = $('tr[data-dev_id=\'' + device_id + '\'] td[data-col=\'dtr\']');
            var rv = $('tr[data-dev_id=\'' + device_id + '\'] td[data-col=\'rv\']');
            var cr = $('tr[data-dev_id=\'' + device_id + '\'] td[data-col=\'cr\']');

            if (!HISTORY) {
                if (dtr.hasClass("disabled")) return;
            }

            if (dateTimeRead != null) dtr.html(dateTimeRead); else dtr.text('');
            if (rainvalue != null) rv.text(rainvalue); else rv.text('');
            if (raincumulative != null) cr.text(raincumulative); else cr.text("");

            if (dataclass != 'undefined') {
                dtr.removeClass().addClass(dataclass);
                rv.removeClass().addClass(dataclass);
                cr.removeClass().addClass(dataclass);
            }

        }

        function clearRainfallTable() {
            for (var i = 0; i < rainfall_devices.length; i++) {
                updateRainfallTable(rainfall_devices[i]['dev_id'], null, null, null)
            }

        }

        function addMarker(device_id, posx, posy, title, type, marker_url) {
            var visible = false;
            if (ACTIVE_UI == 'rainfall') {
                visible = true;
            }

            var pos = new google.maps.LatLng(posx, posy);
            var image = {
                url: marker_url,
                size: new google.maps.Size(32, 37),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(16, 37)
            };

            var marker = new google.maps.Marker({
                    position: pos,
                    icon: image,
                    map: WV_MAP,
                    visible: visible,
                    title: title + " (" + device_id + ")"
                }//,
                //url: server_name+base_url+'device/latest/'+ data.device[0].dev_id
            );

            WV_MAP_MARKERS.push(marker);

            google.maps.event.addListener(marker, 'click', function () {
                var tr = $('tr[data-dev_id=\'' + device_id + '\']');
                var div = $('#chart_div_' + device_id + ' p');

                $('#rainfall-canvas').scrollTo(tr, {duration: 1000});
                if (type == 'Waterlevel & Rain 2') {
                    $('#charts_div_container').scrollTo(div, {duration: 1000});
                }
                tr.addClass('selected_device_tr');
                div.addClass('selected_device_tr');
                setTimeout(function () {
                    tr.removeClass('selected_device_tr');
                    div.removeClass('selected_device_tr');
                }, 3000);

            });
        }

        function addTyTrackMarker(options, map) {
            if (options.lat == undefined && options.lng == undefined && options.position == undefined) {
                throw 'No latitude or longitude defined.';
            }
            var base_options = {
                position: new google.maps.LatLng(options.lat, options.lng),
                map: null
            }, marker_options = extend_object(base_options, options);

            delete marker_options.lat;
            delete marker_options.lng;

            var marker = new google.maps.Marker(marker_options);

            marker.setMap(map);

            if (options.infoWindow) {
                marker.infoWindow = new google.maps.InfoWindow(options.infoWindow);

                marker.addListener('click', function () {
                    marker.infoWindow.open(map, marker);
                });
            }

            return marker;
        }

        function drawPolyline(options, map) {
            var path = [],
                points = options.path;

            if (points.length) {
                if (points[0][0] === undefined) {
                    path = points;
                }
                else {
                    for (var i = 0, latlng; latlng = points[i]; i++) {
                        path.push(new google.maps.LatLng(latlng[0], latlng[1]));
                    }
                }
            }

            var polyline_options = {
                map: map,
                path: path,
                strokeColor: options.strokeColor,
                strokeOpacity: options.strokeOpacity,
                strokeWeight: options.strokeWeight,
                geodesic: options.geodesic,
                clickable: true,
                editable: false,
                visible: true
            };
            var polyline = new google.maps.Polyline(polyline_options);

            return polyline;

        }


        function drawCircle(options, map) {
            options = extend_object({
                map: map,
                center: new google.maps.LatLng(options.lat, options.lng)
            }, options);

            delete options.lat;
            delete options.lng;

            var polygon = new google.maps.Circle(options);

            return polygon;
        }

        function drawPolygon(options, map) {
            options = extend_object({
                map: map
            }, options);

            options.paths = [options.paths.slice(0)];

            if (options.paths.length > 0) {
                if (options.paths[0].length > 0) {
                    options.paths = array_flat(array_map(options.paths, arrayToLatLng));
                }
            }

            var polygon = new google.maps.Polygon(options);

            return polygon;
        }

        var arrayToLatLng = function (coords) {
            var i;

            for (i = 0; i < coords.length; i++) {
                if (!(coords[i] instanceof google.maps.LatLng)) {
                    if (coords[i].length > 0 && typeof(coords[i][0]) === "object") {
                        coords[i] = arrayToLatLng(coords[i]);
                    }
                    else {
                        coords[i] = coordsToLatLngs(coords[i]);
                    }
                }
            }

            return coords;
        };

        var coordsToLatLngs = function (coords) {
            var first_coord = coords[0],
                second_coord = coords[1];

            return new google.maps.LatLng(first_coord, second_coord);
        };

        var array_map = function (array, callback) {
            var original_callback_params = Array.prototype.slice.call(arguments, 2),
                array_return = [],
                array_length = array.length,
                i;

            if (Array.prototype.map && array.map === Array.prototype.map) {
                array_return = Array.prototype.map.call(array, function (item) {
                    var callback_params = original_callback_params.slice(0);
                    callback_params.splice(0, 0, item);

                    return callback.apply(this, callback_params);
                });
            }
            else {
                for (i = 0; i < array_length; i++) {
                    callback_params = original_callback_params;
                    callback_params.splice(0, 0, array[i]);
                    array_return.push(callback.apply(this, callback_params));
                }
            }

            return array_return;
        };

        var array_flat = function (array) {
            var new_array = [],
                i;

            for (i = 0; i < array.length; i++) {
                new_array = new_array.concat(array[i]);
            }

            return new_array;
        };

        function extend_object(obj, new_obj) {
            var name;

            if (obj === new_obj) {
                return obj;
            }

            for (name in new_obj) {
                if (new_obj[name] !== undefined) {
                    obj[name] = new_obj[name];
                }
            }

            return obj;
        }

        function setAllMap(map) {
            for (var i = 0; i < WV_MAP_MARKERS.length; i++) {
                WV_MAP_MARKERS[i].setMap(map);
            }
        }

        function clearMarkers() {
            setAllMap(null);
        }

        function showMarkers() {
            setAllMap(WV_MAP);
        }

        function deleteMarkers() {
            clearMarkers();
            WV_MAP_MARKERS = [];
        }

        function setMarkersVisibility(state) {
            for (var i = 0; i < WV_MAP_MARKERS.length; i++) {
                WV_MAP_MARKERS[i].setVisible(state);
            }
        }


        function updateWaterlevelChart(data) {
            var device_id = data[0]['station_id'];
            LAST_WTR_DEVID = device_id;
            var div = 'line-chart-marker_' + device_id;
            if (!HISTORY) {
                if ($(document.getElementById(div)).hasClass("disabled")) return;
            }
            if (HISTORY) {
                var newdata = $.grep(data.Data, function (n, i) {
                    thisdate = Date.parseExact(n['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
                    result = thisdate.between(key['startDateTime'], key['endDateTime']);
                    //if (result) console.log(thisdate.toString() + " - " + result);
                    return result;
                });
                data.Data = newdata;
                data.Data.length = newdata.length;
            }

            if (data.Data.length == 0) {
                $(document.getElementById(div)).css({'background': 'url(images/nodata.png)'});
            } else {
                drawChartWaterlevel(div, data);
            }
        }

        function updateTemperatureTicker(data) {
            if (data.count == 0 || data.data == null || data.device[0].minmax['max'] == null) return;
            var text = "";
            var device_id = data.device[0].dev_id;
            LAST_AWS_DEVID = device_id;
            var device = search(temperature_devices, 'dev_id', device_id);

            var municipality = device['municipality'];
            var location = device['location'];
            var max = data.device[0].minmax['max'];

            var time = search(data.data, 'air_temperature', max, true);

            //console.log(time);

            if (time != null) {
                var d = Date.parseExact(time['dateTimeRead'], 'yyyy-MM-dd HH:mm:ss');
                //<#-- ASTI BSWM_Lufft not ISO STANDARD dateTimeRead FIX -_-
                if (!d) {
                    var datefixed = time.dateTimeRead.substring(0, 19);
//				console.log(datefixed);
                    d = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                }//--#>
                time = d.toString('h:mm tt');
            } else {
                time = 'unknown';
            }

            var current_time_date = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
            //<#-- ASTI BSWM_Lufft not ISO STANDARD dateTimeRead FIX -_-
            if (!current_time_date) {
                var datefixed = data.data[0].dateTimeRead.substring(0, 19);
//			console.log(datefixed);
                current_time_date = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
            }//--#>
            var current_time = current_time_date.toString('h:mm tt');
            var current_temperature = parseFloat(data.data[0].air_temperature);
            var humidity = parseFloat(data.data[0].air_humidity);

            //console.log(current_temperature);
            //console.log(humidity);

            var heat_index = (HI.heatIndex({temperature: current_temperature, humidity: humidity})).toFixed(2);

            //console.log(heat_index);

            text += "[" + current_time + ']: ' + municipality + ' - Temp/Heat Index: ' + current_temperature + '/' + heat_index + ' \u2103. Max Temp: ' + max + ' last ' + time;
            addTicker(text, 'ticker--2__list');
        }

        function addTicker(text, tickerlist) {
            $('<li/>').text(text).appendTo($('#' + tickerlist));
        }

        function clearAllTicker(tickerlist) {
            $('#' + tickerlist).empty();
        }

        function search(o, key, val, greedy) {
            var ret = null;
            for (var i = 0; i < o.length; i++) {
                if (o[i][key] == val) {
                    ret = o[i];
                    if (!greedy) {
                        break;
                    }
                }
            }
            return ret;
        }

        function getIndexOfDevID(o, dev_id) {
            if (dev_id == 0) return 0;
            for (var i = 0; i < o.length; i++) {
                if (o[i]['dev_id'] == dev_id) {
                    return i - 1;
                }
            }
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
                <li><a href="#" class='currentPage'>Home</a></li>
                <li><a href="rainfall.php">Rainfall Monitoring</a></li>
                <li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
                <li><a href="waterlevel2.php">Waterlevel Map</a></li>
                <li><a href="devices.php">Devices Monitoring</a></li>
            </ul>
        </div>
    </div>


</div>
<div id='content'>
    <div id='chooser' class="custom-ctrl btn-group">
        <button id="toggleLayers"><img src="images/layers.png"/></button>
        <form id="layersform" style="display: none">
            <input id="toggleRainfallMap" type="radio" name="chooser_c" value="toggleRainfallMap" checked><label
                    for="toggleRainfallMap">Rainfall</label> <br>
            <input id="toggleDoppler" type="radio" name="chooser_c" value="toggleDoppler"><label for="toggleDoppler">Doppler</label>
            <br>
            <input id="toggleTyphoonTrack" type="radio" name="chooser_c" value="toggleTyphoonTrack"><label
                    for="toggleTyphoonTrack">Typhoon Track</label> <br>
            <input id="toggleSatellite" type="radio" name="chooser_c" value="toggleSatellite"><label
                    for="toggleSatellite">Satellite</label> <br>
            <ul>
                <li id="toggleWeatherForecast">Weather Forecast</li>
            </ul>
        </form>
    </div>
    <div id='map-canvas'>

    </div>
    <div id='rainfall-canvas'>
        <table>
            <tr>
                <th>Rainfall</th>
                <td colspan="3">
                    <a title="Click to change" href="#" id="sdate"></a>
                    <input type="text" style="height: 0; width:0; border: 0;" id="dtpicker2">
                </td>
            </tr>
            <tr>
                <th>Server DateTime</th>
                <td id="serverdtr" colspan="3">
                    <table>
                        <tr>
                            <td colspan="3" id="serverdate"></td>
                        </tr>
                        <tr>
                            <td colspan="3" id="servertime"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>Total Devices</th>
                <td id="numraindevices" colspan="3">119</td>
            </tr>
            <tr><th>Loaded</th><td id="loadedraindevices" colspan="3"></td></tr>
            <?php
                $rainfall_devices = Devices::GetDevicesByParam('Rainfall');
                $prevProvince = "";

                foreach ($rainfall_devices as $dev) {
                    $province = $dev["province"];
                    $dev_id = $dev['dev_id'];
                    $location = $dev['municipality'] . ' - ' . $dev['location'];
                    $status = $dev['status'];
                    if ($prevProvince != $province) {
                        echo '<tr class="province_tr">' . "<th>$province</th><th>Time</th><th>Rain (mm)</th><th>Cumulative (mm)</th></tr>";
                        $prevProvince = $province;
                    }
                    echo "<tr data-dev_id=" . $dev_id . "><td>" . $location . '</td>';
                    if ($status != null && $status != '0') {
                        echo '<td data-col="dtr" class="disabled">[DISABLED]</td><td data-col="rv" class="disabled"></td><td data-col="cr" class="disabled"></td>';
                    } else {
                        echo '<td data-col="dtr"></td><td data-col="rv"></td><td data-col="cr"></td>';
                    }
                    echo "</tr>";
                }
            ?>
        </table>
    </div>
    <div style="display: none;">
        <div id='legends' class="custom-ctrl">
            <button id="togglelegends" class="ui-button ui-widget ui-corner-all ui-button-icon-only"
                    title="Show/Hide Legends">
                <span class="ui-icon  ui-icon-arrowthick-2-ne-sw"></span>
            </button>
            <h1>Daily Cumulative Rainfall</h1>
            <div style="display: none">
                <img src="images/rain-lighter_now.png"/>
                <img src="images/rain-light_now.png"/>
                <img src="images/rain-moderate_now.png"/>
                <img src="images/rain-heavy_now.png"/>
                <img src="images/rain-intense_now.png"/>
                <img src="images/rain-torrential_now.png"/>
            </div>
            <div class="legend"><img src="images/rain-lighter.png"/><span>less than 5mm</span></div>
            <div class="legend"><img src="images/rain-light.png"/><span>5mm to less than 25mm</span></div>
            <div class="legend"><img src="images/rain-moderate.png"/><span>25mm to less than 50mm</span></div>
            <div class="legend"><img src="images/rain-heavy.png"/><span>50mm to less than 75mm</span></div>
            <div class="legend"><img src="images/rain-intense.png"/><span>75mm to less than 100mm</span></div>
            <div class="legend"><img src="images/rain-torrential.png"/><span>100mm or more</span></div>
            <div class="legend"><img src="images/overlay_now.png"/><span>currently raining</span></div>
        </div>

        <div id="dopplertime" class="custom-ctrl btn-group" style="display: none">

        </div>
    </div>
    <div id="ticker-container">
        <div class="ticker" id="ticker--1">
            <ul id='ticker--1__list'>
            </ul>
        </div>
        <div class="ticker" id="ticker--2">
            <ul id='ticker--2__list'>
            </ul>
        </div>
    </div>
    <div id="charts_div_container">
        <div class="innerWrap">
            <?php
                $waterlevel_devices = Devices::GetDevicesByParam('Waterlevel');

                foreach ($waterlevel_devices as $dev) {
                    $dev_id = $dev['dev_id'];
                    $location = $dev['municipality'] . ' - ' . $dev['location'];
                    $status = $dev['status'];

                    echo '<div id="chart_div'.$dev_id.'" class="chartWithOverlay list divrowwrapper">'.
                        '<p class="overlay">'. $location . '</p>';
                    if ($status != null && $status != '0') {
                        echo '<div id="line-chart-marker_' . $dev_id .'" class="chart disabled" style="background: url(&quot;images/disabled.png&quot;);"></div>';
                    } else {
                        echo '<div id="line-chart-marker_' . $dev_id .'" class="chart"></div>';
                    }

                    echo '</div>';
                }
            ?>
        </div>
    </div>
    <div id="feeds">
        <div id="regionalweather" style="display:none" class="feedcontainer">
            <h1>REGIONAL WEATHER FORECAST</h1>
            <span>Visayas Weather forecast</span>
            <h2>Issued at</h2>
            <span id="regionalweather_issuedat">[date time]</span>
            <h2>Valid Beginning</h2>
            <span id="regionalweather_validity">[date time]</span>
            <h2>Synopsis</h2>
            <span id="regionalweather_synopsis">[synopsis]</span>
            <h2>Forecast</h2>
            <span id="regionalweather_forecast">[forecast]</span>
            <h2>More info:</h2>
            <a href="https://www1.pagasa.dost.gov.ph/index.php/vis-weather/local-weather-forecast" target="_blank">Source:
                PAGASA</a>
        </div>
    </div>
</div>
<div id='footer'>
    <p>Contact Bantay Panahon on <a href="https://www.facebook.com/bantaypanahonph/" target="_blank">Facebook</a></p>
    <p>DRRM Unit - Department of Science and Technology Regional Office No. VI</p>
</div>
<script type="text/javascript">
    var rainfall_devices = <?php echo json_encode($rainfall_devices);?>;
    //var waterlevel_devices = <?php echo json_encode($waterlevel_devices);?>;
    var temperature_devices = <?php echo json_encode(Devices::GetDevicesByParam('Temperature'));?>;
    var rainfall_device_ids_enabled = <?php echo json_encode(Devices::GetEnabledDeviceIdsByParam('Rainfall'));?>;
    var rainfall_device_ids_disabled = <?php echo json_encode(Devices::GetDisabledDeviceIdsByParam('Rainfall'));?>;
    var waterlevel_device_ids_enabled = <?php echo json_encode(Devices::GetEnabledDeviceIdsByParam('Waterlevel'));?>;
    var waterlevel_device_ids_disabled = <?php echo json_encode(Devices::GetDisabledDeviceIdsByParam('Waterlevel'));?>;

</script>
<?php include_once("analyticstracking.php") ?>
</body>
</html>