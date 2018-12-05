<?php
if (!empty($_GET['q'])) {
    $q = $_GET['q'];
} else {
    header("Location: riverbasin.php?q=1");
    die();
}
$riverbasin = "Unknown";
$mapCoord = '10.712317, 122.562362';
$mapZoomLevel = 8;
switch ($q) {
    case 1:
        $riverbasin = "Aklan River Basin";
        $mapCoord = '11.558744, 122.293625';
        $mapZoomLevel = 11;
        break;
    case 2:
        $riverbasin = "Panay River Basin";
        $mapCoord = '11.330600, 122.531891';
        $mapZoomLevel = 10;
        break;
    case 3:
        $riverbasin = "Tigum-Aganan River Basin";
        $mapCoord = '11.008601, 122.454987';
        $mapZoomLevel = 10;
        break;
    case 4:
        $riverbasin = "Ilog-Hilabangan River Basin";
        $mapCoord = '9.906627, 122.712479';
        $mapZoomLevel = 11;
        break;
    default:
        header("Location: riverbasin.php?q=1");
        die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once 'lib/init3.php' ?>
    <meta charset="utf-8">
    <title>DOST VI DRRMU - River Basin Map</title>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.theme.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.structure.min.css'/>
    <link rel="stylesheet" type="text/css" href='css/style.css'/>
    <link rel="stylesheet" type="text/css" href='css/screen.css'/>
    <link rel="stylesheet" type="text/css" href='css/pages/riverbasin.css'/>
    <script type="text/javascript" src='vendor/jquery/jquery-1.12.4.min.js'></script>
    <script type="text/javascript" src='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js'></script>
    <script type="text/javascript" src='vendor/datejs/date.js'></script>
    <script type="text/javascript" src='js/jquery.scrollTo.min.js'></script>
    <script type="text/javascript" src='js/jquery.easy-ticker.min.js'></script>
    <script type="text/javascript" src='js/heat-index.js'></script>
    <script type="text/javascript"
            src="vendor/gasparesganga-jquery-loading-overlay-2.1.6/loadingoverlay.min.js"></script>
    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&sensor=false"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script type="text/javascript">
        setTimeout(function () {
            window.location.reload(true);
        }, 900000); // refresh 15 minutes
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

        var cumulative_rainfall_map;
        var cumulative_rainfall_map_markers = [];
        var lastValidCenter;
        var HISTORY = false;

        google.charts.load('current', {packages: ['corechart']});

        $.xhrPool = [];
        $.xhrPool.abortAll = function () {
            $(this).each(function (idx, jqXHR) {
                jqXHR.abort();
            });
            $.xhrPool.length = 0
        };

        $.ajaxSetup({
            beforeSend: function (jqXHR) {
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
                initMap("map-canvas");
                initMapLegends('legends');
                initRainfallTable("rainfall-canvas");
                //initTicker('ticker--1');
                //initTicker('ticker--2');
                initChartDivs('charts_div_container');
                initFetchData();
            });
        });


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

            /*setTimeout(function () {
                for (var i = 0; i < temperature_devices.length; i++) {
                    var cur = temperature_devices[i];
                    // if (history) {
                    postGetData(cur.dev_id, key['sdate'], key['sdate'], 96, onTemperatureDataResponseSuccess);
                    // } else {
                    // 	if (cur['status'] == null || cur['status'] == '0') {
                    // 		postGetData(cur.dev_id, key['sdate'], "", "", onTemperatureDataResponseSuccess);
                    // 	}
                    // }
                }

            }, 200);*/
        }

        function postGetData(dev_id, sdate, edate, limit, successcallback) {
            $.ajax({
                url: DOCUMENT_ROOT + 'data.php',
                //url:'http://localhost/dost6arc/api/archive',
                type: "POST",
                data: {
                    start: 0,
                    limit: limit,
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
            $.ajax({
                beforeSend: function () {
                    if (div != '') {
                        $("#" + div).LoadingOverlay("show", {
                            zIndex: 50
                        });
                    }
                },
                complete: function () {
                    if (div != '') {
                        $("#" + div).LoadingOverlay("hide");
                    }
                },
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
            })
                .done(function (d) {
                    if (cba !== 'undefined' && typeof  cba === 'function') {
                        cba();
                    }
                    d.forEach(function (e) {
                        successcallback(e);
                    })
                });
            /*.fail(function (f, n) {
                onRainfallDataResponseFail(dev_id)
            });*/
        }

        function onRainfallDataResponseSuccess(data) {
            var device_id = data[0].station_id;

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
                //var timeread = data.data[0].dateTimeRead.substring(10).substring(0, 6);
                var devicedtr = Date.parseExact(data.Data[last]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
                var serverdtr = Date.parseExact(key['serverdate'] + ' ' + key['servertime'] + ':00', 'yyyy-MM-dd HH:mm:ss');
                var hour12time = devicedtr.toString("h:mm tt");

                var rc = getRainCumulative(data.Data);
                var rv = parseFloat(data.Data[last]['Rainfall Amount']).toFixed(2);

                if (key['sdate'] == key['serverdate'] && devicedtr.add({minutes: 15}).compareTo(serverdtr) == -1) { //late
                    updateRainfallTable(device_id, hour12time, rv, rc, 'latedata');
                } else {
                    updateRainfallTable(device_id, hour12time, rv, rc, 'dataok');
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
            var DOST_CENTER = new google.maps.LatLng(<?php echo $mapCoord;?>);

            var mapOptions = {
                zoom: <?php echo $mapZoomLevel;?>,
                minZoom: <?php echo $mapZoomLevel;?>,
                maxZoom: null,
                center: DOST_CENTER,
                disableDefaultUI: true,
                mapTypeId: 'mapbox',
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE,
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
            };

            cumulative_rainfall_map = new google.maps.Map(document.getElementById(divcanvas), mapOptions);

            // Bounds for region xi
            var strictBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(9.1895, 119.1193),
                new google.maps.LatLng(12.2171, 125.9308)
            );

            lastValidCenter = cumulative_rainfall_map.getCenter();

            // Listen for the dragend event
            google.maps.event.addListener(cumulative_rainfall_map, 'idle', function () {
                var minLat = strictBounds.getSouthWest().lat();
                var minLon = strictBounds.getSouthWest().lng();
                var maxLat = strictBounds.getNorthEast().lat();
                var maxLon = strictBounds.getNorthEast().lng();
                var cBounds = cumulative_rainfall_map.getBounds();
                var cMinLat = cBounds.getSouthWest().lat();
                var cMinLon = cBounds.getSouthWest().lng();
                var cMaxLat = cBounds.getNorthEast().lat();
                var cMaxLon = cBounds.getNorthEast().lng();
                var centerLat = cumulative_rainfall_map.getCenter().lat();
                var centerLon = cumulative_rainfall_map.getCenter().lng();

                if ((cMaxLat - cMinLat > maxLat - minLat) || (cMaxLon - cMinLon > maxLon - minLon)) {   //We can't position the canvas to strict borders with a current zoom level
                    //cumulative_rainfall_map.setZoom(cumulative_rainfall_map.getZoom()+1);
                    return;
                }
                if (cMinLat < minLat)
                    var newCenterLat = minLat + ((cMaxLat - cMinLat) / 2);
                else if (cMaxLat > maxLat)
                    var newCenterLat = maxLat - ((cMaxLat - cMinLat) / 2);
                else
                    var newCenterLat = centerLat;
                if (cMinLon < minLon)
                    var newCenterLon = minLon + ((cMaxLon - cMinLon) / 2);
                else if (cMaxLon > maxLon)
                    var newCenterLon = maxLon - ((cMaxLon - cMinLon) / 2);
                else
                    var newCenterLon = centerLon;

                if (newCenterLat != centerLat || newCenterLon != centerLon)
                //cumulative_rainfall_map.setCenter(new google.maps.LatLng(newCenterLat, newCenterLon));
                    cumulative_rainfall_map.panTo(new google.maps.LatLng(newCenterLat, newCenterLon));
            });

            cumulative_rainfall_map.mapTypes.set("mapbox", new google.maps.ImageMapType({
                getTileUrl: function (coord, zoom) {
                    var tilesPerGlobe = 1 << zoom
                        , x = coord.x % tilesPerGlobe;
                    if (x < 0)
                        x = tilesPerGlobe + x;
                    //return "http://tile.openstreetmap.org/" + zoom + "/" + x + "/" + coord.y + ".png"
                    return "https://api.mapbox.com/styles/v1/dost6ryanb/cjcipbquu0khs2rqrlgcz44y7/tiles/256/" + zoom + "/" + x + "/" + coord.y + "?access_token=pk.eyJ1IjoiZG9zdDZyeWFuYiIsImEiOiI1OGMyZjdjNjZlYjlhNTMyNDc0NGQxOTY4ZDJlZjIxNyJ9.dkASVYIEPInwAEkwUkaGhQ";
                },
                tileSize: new google.maps.Size(256, 256),
                name: "MapBox",
                maxZoom: 18
            }));
        }

        function initMapLegends(container) {
            legendscontainer = $(document.getElementById(container));
            cumulative_rainfall_map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(document.getElementById(container));

            $('#togglelegends')
                .on('click', function () {
                    $('.legend').toggle();
                    $('.legendtitle').toggle();
                });
        }

        function initTicker(ticker) {
            $(document.getElementById(ticker)).css({'display': 'block'}).easyTicker({visible: 1, interval: 3500});
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


                if (cur['province'] != prevProvince) {
                    prevProvince = cur.province;
                    $('<tr/>').addClass('province_tr')
                        .append($('<th>' + prevProvince + '</th>'))
                        .append($('<th>Time</th>'))
                        .append($('<th>Rain (mm)</th>'))
                        .append($('<th>Cumulative (mm)</th>')).appendTo(table);
                }

                $('<tr/>', {'data-dev_id': cur.dev_id})
                    .append($('<td>' + cur.municipality + ' - ' + cur.location + '</td>'))
                    .append($('<td/>', {'data-col': 'dtr'}))
                    .append($('<td/>', {'data-col': 'rv'}))
                    .append($('<td/>', {'data-col': 'cr'})).appendTo(table);

                if (cur['status'] != null && cur['status'] != 0) {
                    updateRainfallTable(cur['dev_id'], '[DISABLED]', '', '', 'disabled');
                }

            }
        }


        function initChartDivs(chartdiv) {
            var charts_container = document.getElementById(chartdiv);
            var chart_wrapper = $('<div/>').attr({'class': 'innerWrap'}).appendTo(charts_container);
            for (var i = 0; i < waterlevel_devices.length; i++) {
                var device = waterlevel_devices[i];
                $('<div/>').attr({
                    'id': 'chart_div_' + device['dev_id'],
                    'class': 'chartWithOverlay list divrowwrapper'
                })
                    .append($('<p/>').addClass('overlay').text(device['municipality'] + ' - ' + device['location']))
                    .append($('<div/>', {'id': "line-chart-marker_" + device['dev_id']}).addClass('chart'))
                    .appendTo(chart_wrapper);

                var div = 'line-chart-marker_' + device['dev_id'];
                if (device['status'] != null && device['status'] != 0) {
                    $(document.getElementById(div)).css({'background': 'url(images/disabled.png)'});
                }
            }
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
                    map: cumulative_rainfall_map,
                    title: title + " (" + device_id + ")"
                }//,
                //url: server_name+base_url+'device/latest/'+ data.device[0].dev_id
            );

            cumulative_rainfall_map_markers.push(marker);

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
            var device_id = data[0]['station_id'];
            var div = 'line-chart-marker_' + device_id;

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
    </script>
</head>
<body>
<div id='header'>
    <div id="banner">
        <img id='logo' src='images/BANTAY_PANAHON.png'/>
        <img id='logo_right' src='images/header_1_right.png'/>
        <div id='menu'>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="rainfall.php">Rainfall Monitoring</a></li>
                <li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
                <li><a href="#" class='currentPage'>River Basin Map</a></li>
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
    <div style="display: none;">
        <div id='legends' class="custom-ctrl">
            <button id="togglelegends" class="ui-button ui-widget ui-corner-all ui-button-icon-only"
                    title="Show/Hide Legends">
                <span class="ui-icon  ui-icon-arrowthick-2-ne-sw"></span>
            </button>
            <h1>Daily Cumulative Rainfall</h1>
            <div style="display: none">
                <img src="images/rain-lighter_now.png">
                <img src="images/rain-light_now.png">
                <img src="images/rain-moderate_now.png">
                <img src="images/rain-heavy_now.png">
                <img src="images/rain-intense_now.png">
                <img src="images/rain-torrential_now.png">
            </div>
            <div class="legend"><img src="images/rain-lighter.png"><span>less than 5mm</span></div>
            <div class="legend"><img src="images/rain-light.png"><span>5mm to less than 25mm</span></div>
            <div class="legend"><img src="images/rain-moderate.png"><span>25mm to less than 50mm</span></div>
            <div class="legend"><img src="images/rain-heavy.png"><span>50mm to less than 75mm</span></div>
            <div class="legend"><img src="images/rain-intense.png"><span>75mm to less than 100mm</span></div>
            <div class="legend"><img src="images/rain-torrential.png"><span>100mm or more</span></div>
            <div class="legend"><img src="images/overlay_now.png"><span>currently raining</span></div>
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
    </div>
</div>
<div id='footer'>
    <p>Contact Bantay Panahon on <a href="https://www.facebook.com/bantaypanahonph/" target="_blank">Facebook</a> </p>
    <p>DRRM Unit - Department of Science and Technology Regional Office No. VI</p>
</div>
<script type="text/javascript">
    var rainfall_devices = <?php echo json_encode(Devices::GetRainFallDeviceFromBasin($riverbasin));?>;
    var waterlevel_devices = <?php echo json_encode(Devices::GetWaterDeviceFromBasin($riverbasin));?>;
    var temperature_devices = <?php echo json_encode(Devices::GetTempDeviceFromBasin($riverbasin));?>;
    var rainfall_device_ids_enabled = <?php echo json_encode(Devices::GetEnabledRainfallDeviceFromBasin($riverbasin));?>;
    var rainfall_device_ids_disabled = <?php echo json_encode(Devices::GetDisabledRainfallDeviceFromBasin($riverbasin));?>;
    var waterlevel_device_ids_enabled = <?php echo json_encode(Devices::GetEnabledWaterDeviceFromBasin($riverbasin));?>;
    var waterlevel_device_ids_disabled = <?php echo json_encode(Devices::GetDisabledWaterDeviceFromBasin($riverbasin));?>;
</script>
<?php include_once("analyticstracking.php") ?>
</body>
</html>