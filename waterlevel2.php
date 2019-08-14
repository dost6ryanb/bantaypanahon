<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once 'lib/init3.php' ?>
    <meta charset="utf-8">
    <title>DOST VI DRRMU - Waterlevel Map</title>
    <script type="text/javascript" src='vendor/jquery/jquery-1.12.4.min.js'></script>
    <script type="text/javascript" src='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js'></script>
    <script type="text/javascript" src='vendor/datejs/date.js'></script>
    <script type="text/javascript" src='js/jquery.scrollTo.min.js'></script>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.theme.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.structure.min.css'/>
    <link rel="stylesheet" type="text/css" href='css/style.css'/>
    <link rel="stylesheet" type="text/css" href='css/screen.css'/>
    <link rel="stylesheet" type="text/css" href='css/pages/waterlevel2.css'/>

    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&sensor=false"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript"
            src="vendor/gasparesganga-jquery-loading-overlay-2.1.6/loadingoverlay.min.js"></script>
    <script type="text/javascript">
        setTimeout(function () {
            window.location.href = window.location.href;
        }, 900000); // refresh 10 minutes
    </script>
    <script type="text/javascript">
        var key = {
            'serverdate': '<?php echo date("m/d/Y");?>',
            'servertime': '<?php echo date("H:i");?>',
            'sdate': SDATE,
            'edate': EDATE,
            'numwaterleveldevices': 0,
            'loadedwaterleveldevices': 0
        };

        key['startDateTime'] = Date.parseExact(key['sdate'] + ' 08:00:00', 'yyyy-MM-dd HH:mm:ss');
        key['endDateTime'] = Date.parseExact(key['edate'] + ' 07:59:59', 'yyyy-MM-dd HH:mm:ss');

        var waterlevel_map;
        var waterlevel_map_markers = [];
        var lastValidCenter;
        var HISTORY = false;

        google.charts.load('current', {
            packages: ['corechart']
        });

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
                $.LoadingOverlaySetup({zIndex: 50,fade: false});
                initMap("map-canvas");
                initWaterlevelTable("waterlevel-table");
                initChartDivs('charts_div_container');
                initFetchData();
            });
        });

        function initFetchData(history) {
            if (history) {
                HISTORY = true;
                postGetDataBulk(waterlevel_device_ids_enabled, key['sdate'], key['edate'], 'waterlevel', onWaterlevelDataResponseSuccess, 'map-canvas', function () {
                    postGetDataBulk(waterlevel_device_ids_disabled, key['sdate'], key['edate'], 'waterlevel', onWaterlevelDataResponseSuccess, '');
                });
            } else {
                postGetDataBulk(waterlevel_device_ids_enabled, key['sdate'], key['edate'], 'waterlevel', onWaterlevelDataResponseSuccess, 'map-canvas');
            }
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

        function onWaterlevelDataResponseSuccess(data) {
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

            setTimeout(function () {
                updateWaterlevelChart(data);
            }, 200);

            var device_id = data.dev_id;

            $('#loadedwaterleveldevices').text(++key['loadedwaterleveldevices']);

            var last = data.data.length - 1;

            if (data.data.length < 2 || (typeof  data.data[0]['waterlevel'] == 'undefined') || (typeof data.data[1]['waterlevel'] == 'undefined')) {
                updateWaterlevelTable(device_id, '[NO DATA]', '', '', 'nodata');
            } else {
                var device = search(waterlevel_devices, 'dev_id', device_id);
                var datefixed = data.data[0]['dateTimeRead'].substring(0, 19);
                var devicedtr = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                var serverdtr = Date.parseExact(key['serverdate'] + ' ' + key['servertime'] + ':00', 'yyyy-MM-dd HH:mm:ss');

                var hour12time = devicedtr.toString("h:mm tt");
                var wtrlvl = parseFloat(data.data[0]['waterlevel'] / 100).toFixed(2);
                var wl0 = wtrlvl;
                var wl1 = parseFloat(data.data[1]['waterlevel'] / 100).toFixed(2);
                var deviation = parseFloat(wl0 - wl1).toFixed(2);

                if (key['sdate'] == key['serverdate'] && devicedtr.add({
                    minutes: 15
                }).compareTo(serverdtr) == -1) { //late
                    updateWaterlevelTable(device_id, hour12time, wtrlvl, deviation, 'latedata');
                } else {
                    updateWaterlevelTable(device_id, hour12time, wtrlvl, deviation, 'dataok');
                }


                var marker_url;
                if (wl0 > wl1) {
                    marker_url = "images/waterlevel_up.png";
                } else if (wl0 < wl1) {
                    marker_url = "images/waterlevel_down.png";
                } else {
                    marker_url = "images/waterlevel.png";
                }

                addMarker(device['dev_id'], device['posx'], device['posy'], device['municipality'] + ' - ' + device['location'], device['type'], marker_url);


            }
        }

        function onWaterlevelDataResponseFail(dev_id) {
            var retryhtml = '<a href=javascript:retryFetchWaterlevel(' + dev_id + ')>Retry</a>';
            updateWaterlevelTable(dev_id, retryhtml, null, null, null);
        }

        function retryFetchWaterlevel(dev_id) {
            postGetData(dev_id, key['sdate'], key['sdate'], 1, onWaterlevelDataResponseSuccess);
            updateWaterlevelTable(dev_id, '', '', '', '');
        }

        function initMap(divcanvas) {
            var DOST_CENTER = new google.maps.LatLng(10.712317, 122.562362); //DOST CENTER

            var mapOptions = {
                //zoom: 6, //Whole Philippines View
                zoom: 8, //Region 6 Focus,
                minZoom: 8,
                maxZoom: null,
                center: DOST_CENTER,
                disableDefaultUI: true,
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE,
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
                mapTypeId: 'mapbox',
                draggableCursor: 'crosshair'
            };

            waterlevel_map = new google.maps.Map(document.getElementById(divcanvas), mapOptions);

            // Bounds for region xi
            var strictBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(9.1895, 119.1193),
                new google.maps.LatLng(12.2171, 125.9308)
            );

            lastValidCenter = waterlevel_map.getCenter();

            // Listen for the dragend event
            google.maps.event.addListener(waterlevel_map, 'idle', function () {
                var minLat = strictBounds.getSouthWest().lat();
                var minLon = strictBounds.getSouthWest().lng();
                var maxLat = strictBounds.getNorthEast().lat();
                var maxLon = strictBounds.getNorthEast().lng();
                var cBounds = waterlevel_map.getBounds();
                var cMinLat = cBounds.getSouthWest().lat();
                var cMinLon = cBounds.getSouthWest().lng();
                var cMaxLat = cBounds.getNorthEast().lat();
                var cMaxLon = cBounds.getNorthEast().lng();
                var centerLat = waterlevel_map.getCenter().lat();
                var centerLon = waterlevel_map.getCenter().lng();

                if ((cMaxLat - cMinLat > maxLat - minLat) || (cMaxLon - cMinLon > maxLon - minLon)) { //We can't position the canvas to strict borders with a current zoom level
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
                    waterlevel_map.panTo(new google.maps.LatLng(newCenterLat, newCenterLon));
            });

            waterlevel_map.mapTypes.set("mapbox", new google.maps.ImageMapType({
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

            $('<button id="togglelegends">Hide Legend</button>')
                .on('click', function () {
                    $('.legend').toggle();
                    if ($(this).text() == "Show Legend") {
                        $(this).text('Hide Legend');
                    } else {
                        $(this).text('Show Legend');
                    }
                })
                .appendTo(legendscontainer);
            $('<div class="legendtitle">Daily Cumulative Rainfall</div class="legend">').appendTo(legendscontainer);
            $('<div class="legend"><img src="' + key['marker'][0].src + '.png" > less than 5mm</div class="legend">').appendTo(legendscontainer);
            $('<div class="legend"><img src="' + key['marker'][1].src + '.png" > 5mm to less than 25mm</div class="legend">').appendTo(legendscontainer);
            $('<div class="legend"><img src="' + key['marker'][2].src + '.png" > 25mm to less than 50mm</div class="legend">').appendTo(legendscontainer);
            $('<div class="legend"><img src="' + key['marker'][3].src + '.png" > 50mm to less than 75mm</div class="legend">').appendTo(legendscontainer);
            $('<div class="legend"><img src="' + key['marker'][4].src + '.png" > 75mm to less than 100mm</div class="legend">').appendTo(legendscontainer);
            $('<div class="legend"><img src="' + key['marker'][5].src + '.png" > 100mm or more</div class="legend">').appendTo(legendscontainer);
            $('<div class="legend"><img src="images/overlay_now.png" > currently raining</div>').appendTo(legendscontainer);
        }

        function initTicker(ticker) {
            $(document.getElementById(ticker)).css({
                'display': 'block'
            }).easyTicker({
                visible: 1
            });
        }

        function initWaterlevelTable(div) {

            var prevProvince = '';
            var maindiv = document.getElementById(div);
            var table = $('<table/>').appendTo(maindiv);
            var sdate = $('<td colspan="3"><a title="Click to change" href="#" id="sdate">' + key['sdate'] + '</a></td>');
            var datepicker = $('<input type="text" style="height: 0px; width:0px; border: 0px;" id="dtpicker2"/>');
            datepicker.appendTo(sdate);

            $('<tr/>').append($('<th>Waterlevel</th>'))
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
                    key['numwaterleveldevices'] = 0;
                    key['loadedwaterleveldevices'] = 0;
                    $.xhrPool.abortAll();
                    clearMarkers();
                    clearWaterlevelTable();
                    //clearAllTicker();
                    initFetchData(true);
                }
                /*,
                        altField: '#datepicker_start',
                        altFormat : 'mm/dd/yy',
                        dateFormat : 'yymmdd'*/
            });
            $('#sdate').click(function () {
                $('#dtpicker2').datepicker('show');
            });


            $('<tr><th>Server DateTime</th><td id="serverdtr" colspan="3">' + key['serverdate'] + ' ' + key['servertime'] + '</td><tr>').appendTo(table);
            $('<tr><th>Total Devices</th><td id="numwaterleveldevices" colspan="3">' + waterlevel_devices.length + '</td><tr>').appendTo(table);
            $('<tr><th>Loaded</th><td id="loadedwaterleveldevices" colspan="3">0</td><tr>').appendTo(table);
            for (var i = 0; i < waterlevel_devices.length; i++) {
                var cur = waterlevel_devices[i];


                if (cur['province'] != prevProvince) {
                    prevProvince = cur.province;
                    $('<tr/>').addClass('province_tr')
                        .append($('<th>' + prevProvince + '</th>'))
                        .append($('<th>Time (HH:MM)</th>'))
                        .append($('<th>Waterlevel (m)</th>'))
                        .append($('<th>Last Reading Deviation (m)</th>')).appendTo(table);
                }

                $('<tr/>', {
                    'data-dev_id': cur.dev_id
                })
                    .append($('<td>' + cur.municipality + ' - ' + cur.location + '</td>'))
                    .append($('<td/>', {
                        'data-col': 'dtr'
                    }))
                    .append($('<td/>', {
                        'data-col': 'wl'
                    }))
                    .append($('<td/>', {
                        'data-col': 'wld'
                    })).appendTo(table);

                if (cur['status'] != null && cur['status'] != 0) {
                    updateWaterlevelTable(cur['dev_id'], '[DISABLED]', "", "", 'disabled');
                }

            }
            //$('<h4><b>x</b> <span style="font-weight:normal;">mark means waterlevel monitoring station is</span> <b>down</b>.</h4>').appendTo(maindiv);
            //$('<h4><b>-</b> <span style="font-weight:normal;">mark means waterlevel monitoring station is sending </span> <b>empty data</b>.</h4>').appendTo(maindiv);
        }


        function initChartDivs(chartdiv) {
            var charts_container = document.getElementById(chartdiv);
            var chart_wrapper = $('<div/>').attr({
                'class': 'innerWrap'
            }).appendTo(charts_container);
            for (var i = 0; i < waterlevel_devices.length; i++) {
                var cur = waterlevel_devices[i];
                $('<div/>').attr({
                    'id': 'chart_div_' + cur['dev_id'],
                    'class': 'chartWithOverlay list divrowwrapper'
                })
                    .append($('<p/>').addClass('overlay').text(cur['municipality'] + ' - ' + cur['location']))
                    .append($('<div/>', {
                        'id': "line-chart-marker_" + cur['dev_id']
                    }).addClass('chart'))
                    .appendTo(chart_wrapper);

                var div = 'line-chart-marker_' + cur['dev_id'];
                if (cur['status'] != null && cur['status'] != 0) {
                    $(document.getElementById(div)).css({
                        'background': 'url(images/disabled.png)'
                    });
                }
            }
        }

        function drawChartWaterlevel(chartdiv, json) {
            var last = json.data.length - 1;
            var datatable = new google.visualization.DataTable();
            datatable.addColumn('datetime', 'DateTimeRead');
            datatable.addColumn('number', 'Waterlevel'); //add column from index i

            for (var j = 0; j < json.data.length; j++) {
                var row = Array(2);

                var datefixed = json.data[j].dateTimeRead.substring(0, 19);
                var date = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');

                row[0] = date;
                if (json.data[j].waterlevel != null) {
                    var waterlevel = parseFloat(json.data[j].waterlevel) / 100;

                    row[1] = {
                        v: waterlevel,
                        f: waterlevel + ' m'
                    };
                }

                datatable.addRow(row);
            }

            var datefixed = json.data[0].dateTimeRead.substring(0, 19);
            var d = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');

            var title_enddatetime = d.toString('MMMM d yyyy h:mm:ss tt');

            var options = {
                title: title_enddatetime,

                hAxis: {
                    title: 'Waterlevel: ' + (json.data[0].waterlevel / 100).toFixed(2) + ' m',
                    format: 'LLL d h:mm:ss a',
                    //viewWindow: {min: d, max: d2},
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
        }

        function updateWaterlevelTable(device_id, dateTimeRead, waterlevel, waterleveldeviation, dataclass) {
            var tr = $('tr[data-dev_id=\'' + device_id + '\']');
            var dtr = $('tr[data-dev_id=\'' + device_id + '\'] td[data-col=\'dtr\']');
            var wl = $('tr[data-dev_id=\'' + device_id + '\'] td[data-col=\'wl\']');
            var wld = $('tr[data-dev_id=\'' + device_id + '\'] td[data-col=\'wld\']');

            if (dateTimeRead != null) dtr.html(dateTimeRead);
            else dtr.text('');
            if (waterlevel != null) wl.text(waterlevel);
            else wl.text('');
            if (waterleveldeviation != null) wld.text(waterleveldeviation);
            else wld.text('');

            if (dataclass != 'undefined') {
                dtr.removeClass().addClass(dataclass);
                wl.removeClass().addClass(dataclass);
                wld.removeClass().addClass(dataclass);
            }

        }

        function clearWaterlevelTable() {
            for (var i = 0; i < waterlevel_devices.length; i++) {
                updateWaterlevelTable(waterlevel_devices[i]['dev_id'], null, null, null, null)
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
                    map: waterlevel_map,
                    title: title + " (" + device_id + ")"
                } //,
                //url: server_name+base_url+'device/latest/'+ data.device[0].dev_id
            );

            waterlevel_map_markers.push(marker);

            google.maps.event.addListener(marker, 'click', function () {
                var tr = $('tr[data-dev_id=\'' + device_id + '\']');
                var div = $('#chart_div_' + device_id + ' p');

                $('#waterlevel-table').scrollTo(tr, {
                    duration: 1000
                });
                $('#charts_div_container').scrollTo(div, {
                    duration: 1000
                });
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
            for (var i = 0; i < waterlevel_map_markers.length; i++) {
                waterlevel_map_markers[i].setMap(map);
            }
        }

        // Removes the markers from the map, but keeps them in the array.
        function clearMarkers() {
            setAllMap(null);
        }

        // Shows any markers currently in the array.
        function showMarkers() {
            setAllMap(waterlevel_map);
        }

        // Deletes all markers in the array by removing references to them.
        function deleteMarkers() {
            clearMarkers();
            waterlevel_map_markers = [];
        }

        function updateWaterlevelChart(data) {
            var device_id = data.dev_id;
            var div = 'line-chart-marker_' + device_id;

            if (data.data.length == 0) {
                $(document.getElementById(div)).css({
                    'background': 'url(images/nodata.png)'
                });
            } else {
                drawChartWaterlevel(div, data);
            }

        }


        function addTicker1(text) {
            $('<li/>').text(text).appendTo($('#ticker1list'));
        }

        function clearAllTicker() {
            $('#ticker1list').empty();
        }

        function search(o, key, val) {
            for (var i = 0; i < o.length; i++) {
                if (o[i][key] == val) {
                    return o[i];
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
                <li><a href="index.php">Home</a></li>
                <li><a href="rainfall.php">Rainfall Monitoring</a></li>
                <li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
                <li><a href="#" class='currentPage'>Waterlevel Map</a></li>
                <li><a href="devices.php">Devices Monitoring</a></li>
            </ul>
        </div>
    </div>
</div>
<div id='content'>
    <div id="riverbasins" class="custom-ctrl">
        <b>River Basin Map</b>
        <ul>
            <li><a href="riverbasin.php?q=1">Aklan River Basin</a></li>
            <li><a href="riverbasin.php?q=2">Panay River Basin</a></li>
            <li><a href="riverbasin.php?q=3">Tigum-Aganan River Basin</a></li>
            <li><a href="riverbasin.php?q=4">Ilog-Hilabangan River Basin</a></li>
        </ul>
    </div>
    <div id='map-canvas'>
    </div>
    <div id='waterlevel-table'>
    </div>
    <div id="charts_div_container">
    </div>
    <div style="display: none">
        <div id='legends'>
            <div style="display: none">
                <img src="images/waterlevel_down.png">
                <img src="images/waterlevel_up.png">
            </div>
        </div>
    </div>

</div>
<div id='footer'>
    <p>Contact Bantay Panahon on <a href="https://www.facebook.com/bantaypanahonph/" target="_blank">Facebook</a></p>
    <p>DRRM Unit - Department of Science and Technology Regional Office No. VI</p>
</div>
<script type="text/javascript">
    var waterlevel_devices = <?php echo json_encode(Devices::GetDevicesByParam('Waterlevel'));?>;
    var waterlevel_device_ids_enabled = <?php echo json_encode(Devices::GetEnabledDeviceIdsByParam('Waterlevel'));?>;
    var waterlevel_device_ids_disabled = <?php echo json_encode(Devices::GetDisabledDeviceIdsByParam('Waterlevel'));?>;
</script>
<?php include_once("analyticstracking.php") ?>
</body>
</html>