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
    <link rel="stylesheet" type="text/css" href='css/superfish.css'/>
    <link rel="stylesheet" type="text/css" href='css/pages/rainfall.css'/>
</head>
<body>
<div id="header">
    <div id="banner">
        <img id='logo' src='images/BANTAY_PANAHON.png'/>
        <img id='logo_right' src='images/header_1_right.png'/>
    </div>

    <div id='menu'>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#" class='currentPage'>Rainfall Monitoring</a></li>
            <li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
            <li><a href="waterlevel2.php">Waterlevel Map</a></li>
            <li><a href="devices.php">Devices Monitoring</a></li>
        </ul>
    </div>
</div>
<div id="content">
    <div id="config-form">
        <div class="form-group">
            <label for='provinces'>Province: </label>
            <select id='provinces' name='province'>
                {{#provinces}}
                <option value="{{.}}">{{.}}</option>
                {{/provinces}}
            </select>
        </div>
        <div class="form-group">
            <label for='durations'>Duration: </label>
            <select id='durations' name='duration'>
                {{#durations}}
                <option value="{{minutes}}">{{label}}</option>
                {{/durations}}
            </select>
        </div>
        <div class="form-group">
            <label for="basedate">Base Date: </label>
            <input type="text" id="basedate" class='ui-corner-all ui-button ui-widget'>
        </div>
        <div class="form-group">
            <button id='go-button' class='ui-widget'>Go</button>
        </div>
        <div id="info-refresh" class="ui-state-highlight">
            <span>Refresh page to update server date and time.</span>
            <span class="ui-icon ui-icon-closethick"></span>
        </div>
    </div>
    <div id='tables-container'>
        <script id="rainfall-table_template" type="text/html">
            <table class="{{cssClass}}" id="{{id}}">
                <tr>
                    <th colspan="2" class="ui-widget-header">Cumumative Rainfall Reading of {{location_group}} for the
                        last {{time}} from {{date}}.
                        <button class="{{cssClass}}__close-button"></button>
                    </th>
                </tr>
                {{#devices}}
                <tr data-dev_id="{{dev_id}}">
                    <td>{{municipality}} - {{location}}</td>
                    <td data-col="result" {{#cssByStatus}}{{status}}{{
                    /cssByStatus}}></td>
                </tr>
                {{/devices}}
            </table>
        </script>
    </div>
</div>
<div id='footer'>
    <p>Contact Bantay Panahon on <a href="https://www.facebook.com/bantaypanahonph/" target="_blank">Facebook</a></p>
    <p>DRRM Unit - Department of Science and Technology Regional Office No. VI</p>
</div>
<script type="text/javascript" src='vendor/jquery/jquery-1.12.4.min.js'></script>
<script type="text/javascript" src='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js'></script>
<script type="text/javascript" src='vendor/datejs/date.js'></script>
<script type="text/javascript" src='vendor/underscore-1.8.3/underscore-min.js'></script>
<script type="text/javascript" src='vendor/mustache.js-2.2.1/mustache.min.js'></script>
<script type="text/javascript" src='vendor/sprintf.js-1.0.3/dist/sprintf.min.js'></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="vendor/gasparesganga-jquery-loading-overlay-2.1.6/loadingoverlay.min.js"></script>
<script type="text/javascript">
    var rainfall_devices = <?php echo json_encode(Devices::GetDevicesByParam('Rainfall'));?>;

</script>
<script type="text/javascript">
    MyApp = {
        SERVER_DATE: SDATE,
        SERVER_TIME: SERVER_TIME,
        sdate: SDATE,
        edate: EDATE
    };

    MyApp.startDateTime = Date.parseExact(MyApp.sdate + ' 08:00:00', 'yyyy-MM-dd HH:mm:ss');
    MyApp.endDateTime = Date.parseExact(MyApp.edate + ' 07:59:59', 'yyyy-MM-dd HH:mm:ss');

    MyApp.config = {
        provinces: ['Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental'],
        durations: [
            {'label': '1 hr', 'minutes': '60'},
            {'label': '3 hr', 'minutes': '180'},
            {'label': '6 hr', 'minutes': '360'},
            {'label': '9 hr', 'minutes': '540'},
            {'label': '12 hr', 'minutes': '720'},
            {'label': '24 hr', 'minutes': '1440'},
            {'label': 'Last 2 Days', 'minutes': '2880'},
            {'label': 'Last 3 Days', 'minutes': '4320'},
        ]
    };

    MyApp.RainfallTableGenerator = (function () {
        var TEMPLATE_ID = 'rainfall-table_template';
        var _templateSource = null;

        var build_template = function () {
            _templateSource = $(document.getElementById(TEMPLATE_ID)).html();
            Mustache.parse(_templateSource);
        };

        return {
            getRenderedTemplate: function (context) {
                if (!_templateSource) {
                    build_template();
                }

                return Mustache.render(_templateSource, context);
            }
        }
    })();

    MyApp.RainfallTableCount = 0;

    MyApp.RainfallTable = (function () {
        var $parent; //jq object of the container
        var $el; //jq object of the table
        var htmlID; //html ID of $el
        var cssClass = "xtbl";
        var fnOnClickCallBack;
        var that = this;
        var xhrPool = [];
        var xhrPoolAbortAll = function () {
            _.each(xhrPool, function (me) {
                me.abort();
            });
            xhrPool.length = 0
        };

        var requestParam = {
            baseDate: "",
            duration: ""
        };

        var fetchData = function (dev_id, sdate, edate, limit) {
            $.ajax({
                url: DOCUMENT_ROOT + 'data3.php',
                type: "POST",
                data: {
                    start: 0,
                    limit: limit,
                    sdate: sdate,
                    edate: edate,
                    pattern: dev_id
                },
                dataType: 'json',
                beforeSend: function (jqXHR) {
                    xhrPool.push(jqXHR);
                },
                complete: function (jqXHR) {
                    var index = xhrPool.indexOf(jqXHR);
                    if (index > -1) {
                        xhrPool.splice(index, 1);
                    }
                }
            })
                .fail(function (f, n) {
                    putRetryOnTD(dev_id);
                })
                .done(function (d) {
                    onDataArrive(d, dev_id);
                });
        };

        var fetchDataBulk = function (dev_ids, sdate, edate, type, divloading, cba) {
            if (divloading != '') {
                $("#" + divloading).LoadingOverlay("show");
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
            }).done(function (data) {
                if (divloading != '') {
                    $("#" + divloading).LoadingOverlay("hide");
                }
                if (cba !== 'undefined' && typeof  cba === 'function') {
                    cba();
                }
                data.forEach(function (datum) {
                    onDataArrive(datum);
                });

            }).fail(function (f, n) {
                if (divloading != '') {
                    $("#" + divloading).LoadingOverlay("hide");
                }
                dev_ids.forEach(function (dev_id) {
                    putRetryOnTD(dev_id);
                });

            });
        };


        var onDataArrive = function (data) {
            var dev_id = data[0].station_id;

            var newdata = $.grep(data.Data, function (n, i) {
                thisdate = Date.parseExact(n['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
                result = thisdate.between(MyApp.startDateTime, MyApp.endDateTime);
                //if (result) console.log(thisdate.toString() + " - " + result);
                return result;
            });

            var len = newdata.length;
            data.Data = newdata;
            data.Data.length = len;

            if (data.Data.length == 0) {
                updateDeviceDataTD(dev_id, "Sorry. No Data available for this date.");
            } else {
                drawChartDualRain(data, dev_id);
            }
        };

        var drawChartDualRain = function (data, dev_id) {
            var chartDiv = htmlID + "__rain-chart--" + dev_id;
            updateDeviceDataTD(dev_id, '<div id="' + chartDiv + '" class="rain-chart"></div>')
            var trimmedData = trimAndRecalculateRain(data);
            drawChartRain(chartDiv, dev_id, trimmedData);
        };

        var putRetryOnTD = function (dev_id) {
            updateDeviceDataTD(dev_id, "Site cannot be reached. Try again later.");
        };

        var updateDeviceDataTD = function (dev_id, html) {
            var selector = sprintf("tr[data-dev_id='%s'] td[data-col='result']", dev_id);
            $el.find(selector).html(html);
        };

        var trimAndRecalculateRain = function (data) {
            var last = data.Data.length - 1;
            var startDtr = Date.parseExact(data.Data[last]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
            var endDtr = startDtr.clone().addMinutes(-parseInt(requestParam.duration));
            var cumulativeRain = null;

            var i = 0;
            for (i = 0; i < data.Data.length; i++) {
                var deviceDtr = Date.parseExact(data.Data[i]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');

                if (deviceDtr.between(endDtr, startDtr)) {
                    break;
                }
            }

            filterData = data.Data.splice(i);
            data.Data = filterData;

            return data;
        };

        return {
            add: function (container, location_filter, duration, basedate) {
                $parent = $parent || $(document.getElementById(container));
                htmlID = cssClass + "--" + MyApp.RainfallTableCount++;
                requestParam.baseDate = basedate;
                requestParam.duration = duration;

                var selectedDevices = _.where(rainfall_devices, {province: location_filter});
                var baseDate = basedate.clone();
                var yesterdayDate = basedate.clone();
                var futureDate = baseDate.clone();
                var baseDateText = baseDate.toString("yyyy-MM-dd");
                yesterdayDate.add({minutes: -(duration)});
                var yesterdayDateText = yesterdayDate.toString("yyyy-MM-dd");

                MyApp.sdate = yesterdayDateText;
                MyApp.edate = baseDateText;
                //if (MyApp.SERVER_DATE != baseDateText) { //realtime
                futureDate.add({days: 1});
                var futureDateText = futureDate.toString("yyyy-MM-dd");
                MyApp.edate = futureDateText;
                //}

                MyApp.startDateTime = Date.parseExact(MyApp.sdate + ' 08:00:00', 'yyyy-MM-dd HH:mm:ss');
                MyApp.endDateTime = Date.parseExact(MyApp.edate + ' 07:59:59', 'yyyy-MM-dd HH:mm:ss');

                var timeText = (duration < 60) ? '1 hour' : (duration < 2880) ? parseInt(duration / 60) + ' hours' : parseInt(duration / (60 * 24)) + ' days';
                var options = {
                    cssClass: cssClass,
                    id: htmlID,
                    location_group: location_filter,
                    time: timeText,
                    date: baseDateText,
                    devices: selectedDevices,
                    cssByStatus: function () {
                        return function (text, render) {
                            if (render(text) == "1") {
                                return 'class="disabled"';
                            } else {
                                return '';
                            }
                        }
                    }
                };

                var rendered = MyApp.RainfallTableGenerator.getRenderedTemplate(options);

                $parent.prepend(rendered);

                $el = $(document.getElementById(htmlID));
                var btnSelector = sprintf("button.%s__close-button", cssClass);
                var btnEl = $el.find(btnSelector);
                btnEl.button({
                    icon: "ui-icon-closethick",
                    text: false
                });
                btnEl.one('click', function () {
                    fnOnClickCallBack(htmlID);
                });

                var rainfall_device_ids_enabled = _.map(_.where(selectedDevices, {status: '0'}), function (d) {
                    return d['dev_id'];
                });

                var rainfall_device_ids_disabled = _.map(_.filter(selectedDevices, _.negate(_.matches({status: '0'}))), function (d) {
                    return d['dev_id'];
                });

                if (MyApp.SERVER_DATE == baseDateText) {
                    fetchDataBulk(rainfall_device_ids_enabled, MyApp.sdate, MyApp.edate, 'rainfall', htmlID);
                } else {
                    fetchDataBulk(rainfall_device_ids_enabled, MyApp.sdate, MyApp.edate, 'rainfall', htmlID, function () {
                        fetchDataBulk(rainfall_device_ids_disabled, MyApp.sdate, MyApp.edate, 'rainfall', '');
                    });
                }

            },

            remove: function () {
                $el.remove();
                xhrPoolAbortAll();
            },

            onCloseButtonClick: function (fn) {
                fnOnClickCallBack = fn;
            },

        };
    });

    google.charts.load('current', {packages: ['corechart']});
    $(document).ready(function () {
        initConfigUI('config-form', MyApp.config);
    });

    //el - element(div) ID
    function initConfigUI(el, context) {
        var source = $(document.getElementById(el));

        var template = source.html();
        var rendered = Mustache.render(template, context);

        source.html(rendered);

        //JQuery Ui selectmenu
        $('select').selectmenu({width: 200});

        //JQuery Ui DatePicker
        var default_date = Date.parseExact(MyApp.SERVER_DATE, 'yyyy-MM-dd');
        initDatePicker('basedate', default_date);

        //JQuery button
        $("#go-button").button();
        $("#go-button").on('click', function () {
            var province = $(document.getElementById('provinces')).val();
            var duration = $(document.getElementById('durations')).val();
            var basedate = $(document.getElementById('basedate')).datepicker("getDate");
            var rainTable = new MyApp.RainfallTable();
            rainTable.add('tables-container', province, duration, basedate);
            rainTable.onCloseButtonClick(function (d) {
                rainTable.remove();
            });
        });


        //Some info
        $('#info-refresh').one('click', function () {
            $(this).hide();
        });
    }

    //el - element(input[text]) ID
    //date - Date Object
    function initDatePicker(el, date) {
        var source = document.getElementById(el);
        var date = date || Date.now();

        $(source).datepicker({
            defaultDate: date,
            dateFormat: 'M dd, yy'
        })
            .datepicker("setDate", date);
    }

    function drawChartRain(container, dev_id, data) {
        var datatable = new google.visualization.DataTable();
        datatable.addColumn('datetime', 'DateTimeRead');
        datatable.addColumn('number', 'Cumulative Rain');
        datatable.addColumn('number', 'Rain Value');

        var rain_cumulative_tmp = 0.00;
        for (var j = 0; j < data.Data.length; j++) {
            var rainValue = parseFloat(data.Data[j]['Rainfall Amount']);
            var rainCumulative = rainValue + rain_cumulative_tmp;
            rain_cumulative_tmp = rainCumulative;

            var row = Array(3);

            row[0] = Date.parseExact(data.Data[j]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
            row[1] = {
                v: rainCumulative, //cumulative rain
                f: rainCumulative.toFixed(2) + ' mm'
            };
            row[2] = {
                v: rainValue, //rain value
                f: rainValue + ' mm'
            };

            datatable.addRow(row);
        }
        var maxdate;
        var mindate;

        var last = data.Data.length - 1;

        var d2 = Date.parseExact(data.Data[last]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');
        var d = Date.parseExact(data.Data[0]['Datetime Read'], 'yyyy-MM-dd HH:mm:ss');

        var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
        var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');


        var options = {
            title: 'Rainfall Reading from ' + title_startdatetime + ' to ' + title_enddatetime,
            hAxis: {
                title: 'Rainfall Cumulative: ' + rainCumulative.toFixed(2) + " mm",
                format: 'LLL d h:mm:ss a',
                viewWindow: {min: d, max: d2},
                textStyle: {fontSize: 10}
            },
            vAxes: {
                0: {
                    title: 'Rain Value (mm)',
                    format: '# mm',
                    viewWindow: {min: 0, max: 40}
                },
                1: {
                    title: 'Cumulative (mm)',
                    direction: -1,
                    format: '# mm',
                    viewWindow: {min: 0, max: 300}
                }
            },
            seriesType: "line",
            series: {
                0: {
                    type: "line",
                    targetAxisIndex: 1,
                    pointSize: 3,
                },
                1: {
                    type: "bars",
                    targetAxisIndex: 0
                }
            },
            crosshair: {trigger: 'both'}
        };
        var chart = new google.visualization.ComboChart(document.getElementById(container));
        chart.draw(datatable, options);
    }

</script>
<?php //include_once("analyticstracking.php") ?>
</body>
</html>