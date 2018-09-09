<?php include_once 'lib/init.php' ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DOST VI DRRMU - Devices</title>
    <script type="text/javascript" src='vendor/jquery/jquery-1.12.4.min.js'></script>
    <script type="text/javascript" src='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js'></script>
    <script type="text/javascript" src='vendor/datejs/date.js'></script>
    <script type="text/javascript" src='vendor/underscore-1.8.3/underscore-min.js'></script>
    <script type="text/javascript" src='vendor/mustache.js-2.2.1/mustache.min.js'></script>
    <script type="text/javascript" src='vendor/sprintf.js-1.0.3/dist/sprintf.min.js'></script>
    <script type="text/javascript" src='js/heat-index.js'></script>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.theme.min.css'/>
    <link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.structure.min.css'/>
    <link rel="stylesheet" type="text/css" href='css/style.css'/>
    <link rel="stylesheet" type="text/css" href='css/screen.css'/>
    <link rel="stylesheet" type="text/css" href='css/pages/devices.css'/>
    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4yau_nw40dWy2TwW4OdUq4OJKbFs1EOc&sensor=false"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        const MAP_MODES = {
            VIEW_DATA: '0',
            SWITCH_STATUS: '1'
        };

        const STATUS = {
            OK: '0',
            DISABLED: '1',
            ALL: '99'
        }

        var CURRENT_MODE = MAP_MODES.VIEW_DATA;
        var SDATE = '<?php echo $sdate;?>';

        var devices_map;
        var devices_map_markers = [];
        var lastValidCenter;
        var ALLOWEDIT = false;
        var TRYAUTH = '';
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

        var DataLoader = (function (d, s, fnSuccess, fnFail, fnBeforeSend) {
            var daurl = DOCUMENT_ROOT + 'data.php';

            $.ajax({
                url: daurl,
                type: 'POST',
                beforeSend: fnBeforeSend,
                data: {
                    start: 0,
                    limit: '',
                    sdate: s,
                    edate: s,
                    pattern: d
                },
                dataType: 'json'
            }).done(fnSuccess).fail(fnFail);

            return this;
        });


        var DeviceView = (function (e) {
            var c = $(document.getElementById(e));
            var data;
            var DEFAULT_HEIGHT = "420px";

            DeviceView.VIEWS = {
                TABLE: 0,
                RAIN: 1,
                WATERLEVEL: 2,
                TEMPERATURE: 3,
                NODATA: 99
            };

            var currentView;

            function __ResetView() {
                c.html('');
                c.css({
                    'background-image': ''
                });
            }

            function DrawNoData() {
                __ResetView();
                c.css({
                    'background-image': 'url(images/nodata.png)'
                });
            }

            function DrawDummy() {
                __ResetView();
                c.css({
                    'background-image': 'url(images/bp-logo.png)'
                });
            }

            function DrawRetry() {
                __ResetView();
                c.css({
                    'background-image': 'url(images/retry.png)'
                });
            }

            function DrawTable() {
                __ResetView();
                c.attr("class", "dialog--table");

                var datatable = new google.visualization.DataTable();
                datatable.addColumn('datetime', 'dateTimeRead');
                datatable.addColumn('datetime', 'dateTimeReceived');

                var columnLength = 2;

                for (var key in data.data[0]) {
                    if (key != 'dateTimeRead' && key != 'dateTimeReceived') {
                        datatable.addColumn('string', key);
                        columnLength++;
                    }
                }

                for (var j = 0; j < data.data.length; j++) {
                    var datum = data.data[j];
                    var dtrd = Date.parseExact(datum.dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                    //<#-- ASTI BSWM_Lufft not ISO STANDARD dateTimeRead FIX -_-
                    if (!dtrd) {
                        var datefixed = datum.dateTimeRead.substring(0, 19);
//              console.log(datefixed);
                        dtrd = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                    } //--#>
                    var dtrc = Date.parseExact(datum.dateTimeReceived, 'yyyy-MM-dd HH:mm:ss');

                    var row = [];

                    row.push({
                        v: dtrd,
                        f: dtrd.toString('yyyy-MM-dd HH:mm:ss')
                    });
                    row.push({
                        v: dtrc,
                        f: dtrc.toString('yyyy-MM-dd HH:mm:ss')
                    });

                    for (var key2 in datum) {

                        if (key2 != 'dateTimeRead' && key2 != 'dateTimeReceived')
                            row.push(datum[key2]);
                    }

                    datatable.addRow(row);

                }


                var maxdate;
                var mindate;

                var d = Date.parseExact(data.data[data.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                if (!d) {
                    var datefixed = data.data[data.data.length - 1].dateTimeRead.substring(0, 19);
//            console.log(datefixed);
                    d = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                } //--#>
                var d2 = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                if (!d2) {
                    var datefixed = data.data[0].dateTimeRead.substring(0, 19);
//            console.log(datefixed);
                    d2 = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                } //--#>

                //var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
                var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
                var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');


                var options = {
                    title: 'Data Reading from ' + title_startdatetime + ' to ' + title_enddatetime,
                    showRowNumber: true,
                    page: 'enable',
                    pageSize: 24,
                    sortAscending: false,
                    sortColumn: 0
                };

                var chart = new google.visualization.Table(c[0]);
                chart.draw(datatable, options);

                c.append('<br><a id="downloadCSV">Download as CSV</a>');
                $('#downloadCSV').off();
                $('#downloadCSV').on('click', function () {
                    //var csvFormattedDataTable = google.visualization.dataTableToCsv(datatable);
                    var csvFormattedDataTable = dataTableToCSV(datatable);
                    var encodedUri = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csvFormattedDataTable);
                    this.href = encodedUri;
                    this.download = 'bantaypanahon.csv';
                    this.target = '_blank';
                });

            }

            /**
             * Convert an instance of google.visualization.DataTable to CSV
             * @param {google.visualization.DataTable} dataTable_arg DataTable to convert
             * @return {String} Converted CSV String
             */
            function dataTableToCSV(dataTable_arg) {
                var dt_cols = dataTable_arg.getNumberOfColumns();
                var dt_rows = dataTable_arg.getNumberOfRows();

                var csv_cols = [];
                var csv_out;

                // Iterate columns
                for (var i = 0; i < dt_cols; i++) {
                    // Replace any commas in column labels
                    csv_cols.push(dataTable_arg.getColumnLabel(i).replace(/,/g, ""));
                }

                // Create column row of CSV
                csv_out = csv_cols.join(",") + "\r\n";

                // Iterate rows
                for (i = 0; i < dt_rows; i++) {
                    var raw_col = [];
                    for (var j = 0; j < dt_cols; j++) {
                        // Replace any commas in row values
                        //raw_col.push(dataTable_arg.getFormattedValue(i, j, 'label').replace(/,/g,""));
                        raw_col.push(dataTable_arg.getFormattedValue(i, j).replace(/,/g, ""))
                    }
                    // Add row to CSV text
                    csv_out += raw_col.join(",") + "\r\n";
                }

                return csv_out;
            }

            function DrawChartRain() {
                __ResetView();
                c.attr("class", "dialog--rain");

                var datatable = new google.visualization.DataTable();
                datatable.addColumn('datetime', 'DateTimeRead');
                datatable.addColumn('number', 'Cumulative Rain');
                datatable.addColumn('number', 'Rain Value');

                for (var j = 0; j < data.data.length; j++) {
                    var rainValue = parseFloat(data.data[j].rain_value);
                    var rainCumulative = parseFloat(data.data[j].rain_cumulative);

                    var row = Array(3);

                    row[0] = Date.parseExact(data.data[j].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                    if (!row[0]) {
                        var datefixed = data.data[j].dateTimeRead.substring(0, 19);
//              console.log("trimmed date " + datefixed);
                        row[0] = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                    } //--#>
                    row[1] = {
                        v: rainCumulative, //cumulative rain
                        f: rainCumulative + ' mm'
                    };
                    row[2] = {
                        v: rainValue, //rain value
                        f: rainValue + ' mm'
                    };

                    datatable.addRow(row);

                }
                var maxdate;
                var mindate;

                var d = Date.parseExact(data.data[data.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                if (!d) {
                    var datefixed = data.data[data.data.length - 1].dateTimeRead.substring(0, 19);
//            console.log(datefixed);
                    d = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                } //--#>
                var d2 = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                if (!d2) {
                    var datefixed = data.data[0].dateTimeRead.substring(0, 19);
//            console.log(datefixed);
                    d2 = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                } //--#>

                //var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
                var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
                var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');

                var options = {
                    title: 'Rainfall Reading from ' + title_startdatetime + ' to ' + title_enddatetime,
                    hAxis: {
                        title: 'Rainfall Cumulative: ' + data.data[0].rain_cumulative + " mm",
                        viewWindow: {
                            min: d,
                            max: d2
                        },
                        textStyle: {
                            fontSize: 10
                        },
                        gridlines: {
                            count: -1,
                            units: {
                                minutes: {
                                    format: ["h:mm a"]
                                }
                            }
                        }
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
                    crosshair: {
                        trigger: 'both'
                    }
                };
                var chart = new google.visualization.ComboChart(c[0]);
                chart.draw(datatable, options);
            }

            function DrawChartWaterlevel() {
                __ResetView();
                c.attr("class", "dialog--waterlevel");

                var datatable = new google.visualization.DataTable();
                datatable.addColumn('datetime', 'DateTimeRead');
                datatable.addColumn('number', 'Waterlevel');

                for (var j = 0; j < data.data.length; j++) {
                    var row = Array(2);

                    row[0] = Date.parseExact(data.data[j].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                    if (data.data[j].waterlevel != null) {
                        var waterlevel = parseFloat(data.data[j].waterlevel) / 100;
                        row[1] = {
                            v: waterlevel,
                            f: waterlevel + ' m'
                        };
                    }

                    datatable.addRow(row);
                }

                var maxdate;
                var mindate;

                var d = Date.parseExact(data.data[data.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                var d2 = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

                //var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
                var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
                var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');

                var options = {
                    title: 'Waterlevel Reading from ' + title_startdatetime + ' to ' + title_enddatetime,
                    hAxis: {
                        title: 'Waterlevel: ' + (data.data[0].waterlevel / 100) + ' m',
                        viewWindow: {
                            min: d,
                            max: d2
                        },
                        textStyle: {
                            fontSize: 10
                        },
                        textPosition: 'none',
                        gridlines: {
                            count: -1,
                            units: {
                                minutes: {
                                    format: ["h:mm a"]
                                }
                            }
                        },
                        minorGridlines: {
                            count: 4
                        },
                    },
                    vAxes: {
                        0: {
                            title: 'Waterlevel (m)',
                            format: '# m',
                            minValue: '0',
                            maxValue: '12'
                        }
                    },
                    seriesType: 'area',
                    curveType: 'function'
                };

                var chart = new google.visualization.ComboChart(c[0]);
                chart.draw(datatable, options);

            }

            function DrawChartTemperature() {
                __ResetView();
                c.attr("class", "dialog--temperature");

                var datatable = new google.visualization.DataTable();
                datatable.addColumn('datetime', 'DateTimeRead');
                datatable.addColumn('number', 'Temperature');
                datatable.addColumn('number', 'Heat Index');


                for (var j = 0; j < data.data.length; j++) {
                    var temp = parseFloat(data.data[j].air_temperature);
                    var humidity = parseFloat(data.data[j].air_humidity);
                    var heat_index = Math.round(HI.heatIndex({
                        temperature: temp,
                        humidity: humidity
                    }), 2);
                    var row = Array(3);

                    row[0] = Date.parseExact(data.data[j].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                    if (!row[0]) {
                        var datefixed = data.data[j].dateTimeRead.substring(0, 19);
//              console.log("trimmed date " + datefixed);
                        row[0] = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                    } //--#>
                    row[1] = {
                        v: temp,
                        f: temp + ' °C'
                    };
                    row[2] = {
                        v: heat_index,
                        f: heat_index + ' °C'
                    };

                    datatable.addRow(row);

                }
                var maxdate;
                var mindate;

                var d = Date.parseExact(data.data[data.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                if (!d) {
                    var datefixed = data.data[data.data.length - 1].dateTimeRead.substring(0, 19);
//            console.log(datefixed);
                    d = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                } //--#>
                var d2 = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
                if (!d2) {
                    var datefixed = data.data[0].dateTimeRead.substring(0, 19);
//            console.log(datefixed);
                    d2 = Date.parseExact(datefixed, 'yyyy-MM-dd HH:mm:ss');
                } //--#>

                //var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
                var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
                var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');

                var options = {
                    title: 'Air Temperature Reading from ' + title_startdatetime + ' to ' + title_enddatetime,
                    hAxis: {
                        title: 'Date Time Read',
                        format: 'LLL d h:mm:ss a',
                        //viewWindow : {min : haxis_mindate, max : haxis_maxdate},
                        gridlines: {
                            count: -1,
                            units: {
                                minutes: {
                                    format: ["h:mm a"]
                                }
                            }
                        },
                        minorGridlines: {
                            count: 4
                        },
                        textStyle: {
                            fontSize: 10
                        }
                    },
                    focusTarget: 'category',
                    vAxis: {
                        title: '',
                        format: '# ºC',
                        minValue: 0,
                        maxValue: 40
                    },
                    pointSize: 3,
                    seriesType: "line",
                    crosshair: {
                        trigger: 'both'
                    },
                    annotations: {
                        textStyle: {
                            fontSize: 12
                        }
                    }
                };
                var chart = new google.visualization.ComboChart(c[0]);
                chart.draw(datatable, options);
            }


            return {
                Empty: function () {
                    c.empty();
                },
                ResetData: function () {
                    var u = (function () {
                        return;
                    })();
                    this.SetData(u); // reset data to undefined
                    //this.SetView(u); // reset view to undefined
                },
                ResetHeightDefault: function () {
                    c.css('height', DEFAULT_HEIGHT);
                },
                SetOnLoadAnim: function () {
                    c.innerHTML = '';
                    c.css({
                        'background-image': 'url(images/rain-loader.gif)'
                    });
                },
                SetData: function (d) {
                    data = d;
                },
                GetData: function () {
                    return data;
                },
                SetView: function (v) {
                    currentView = v;
                },
                DrawView: function () {
                    if (data.count == -1 || data.count == 0 || data.data.length == 0) {
//              console.log(data.count);
//              console.log(data.data.length);

                        currentView = DeviceView.VIEWS.NODATA;
                    }

                    switch (currentView) {
                        case (DeviceView.VIEWS.NODATA):
                            DrawNoData();
                            break;
                        case (DeviceView.VIEWS.TABLE):
                            DrawTable();
                            break;
                        case (DeviceView.VIEWS.RAIN):
                            DrawChartRain();
                            break;
                        case (DeviceView.VIEWS.WATERLEVEL):
                            DrawChartWaterlevel();
                            break;
                        case (DeviceView.VIEWS.TEMPERATURE):
                            DrawChartTemperature();
                            break;
                        default:
                            DrawDummy();
                            break;
                    }
                },
                OnFail: function (fn) {
                    DrawRetry();
                    c.one("click", fn);
                }

            }
        });

        var ChartInfo = (function (e) {
            var chartInfo = $(document.getElementById(e));
            return {
                setTitle: function (t, s) {
                    chartInfo.children("#title1").text(t);
                    chartInfo.children("#title2").text(s);
                }
            };
        });

        var ChartLinks = (function (e) {
            var chartLinks = $(document.getElementById(e));
            var table = chartLinks.find('#table');
            var rain = chartLinks.find('#rain');
            var temp = chartLinks.find('#temperature');
            var wtrlevel = chartLinks.find('#waterlevel');
            var tblLinkHandler;
            var rnLinkHandler;
            var wtrLinkHandler;
            var tempLinkHandler;

            return {
                InitHandlers: function () {
                    table.on('click', tblLinkHandler);
                    rain.on('click', rnLinkHandler);
                    wtrlevel.on('click', wtrLinkHandler);
                    temp.on('click', tempLinkHandler);
                },
                setDeviceType: function (t) {
//            console.log("fn>setDeviceType " + t);

                    chartLinks.find("input[name='chart-type']").checkboxradio();
                    chartLinks.find("input[name='chart-type']").checkboxradio("disable");

                    table.checkboxradio("enable");

                    if ($.inArray(t, ['VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis']) != -1) {
                        rain.checkboxradio("enable");
                    }

                    if ($.inArray(t, ['VAISALA', 'UAAWS', 'BSWM_Lufft', 'Davis']) != -1) {
                        temp.checkboxradio("enable");
                    }

                    if ($.inArray(t, ['Waterlevel', 'Waterlevel & Rain 2']) != -1) {
                        wtrlevel.checkboxradio("enable");
                    }


                },
                onTableLinkClicked: function (fn) {
                    tblLinkHandler = fn;
                },
                onRainLinkClicked: function (fn) {
                    rnLinkHandler = fn;
                },
                onWaterlevelLinkClicked: function (fn) {
                    wtrLinkHandler = fn;
                },
                onTemperatureLinkClicked: function (fn) {
                    tempLinkHandler = fn;
                },
                triggerSelected: function () {
                    var checkedEl = chartLinks.find(":checked");
                    if (checkedEl != null) {
                        var elID = checkedEl.attr('id');
                        var elDisbaled = checkedEl.prop('disabled');
//              console.log(elID + " " + elDisbaled);

                        if (elDisbaled == false) {
                            switch (elID) {
                                case 'table':
                                    tblLinkHandler();
                                    break;
                                case 'rain':
                                    rnLinkHandler();
                                    break;
                                case 'waterlevel':
                                    wtrLinkHandler();
                                    break;
                                case 'temperature':
                                    tempLinkHandler();
                                    break;
                            }
                        } else {
//                console.log("Not available");
                            tblLinkHandler();
                        }


                    }
                }
            };
        });

        var DateOption = (function (e) {
            var dtpicker = $(document.getElementById(e));
            var startDateChangeHandler;
            var date;

            $(dtpicker).datepicker({
                onSelect: function (data) {
                    date = data;
                    dateChangeHandler(data);
                }
            });

            return {
                getDate: function () {
                    return date;
                },
                setDate: function (sd) {
                    $(dtpicker).datepicker("setDate", sd);
                    date = sd;
                },
                onDateChanged: function (fn) {
                    dateChangeHandler = fn;
                }
            }
        });

        var ViewStateDialog = (function () {
            var dialog;
            var device;
            var chartInfo;
            var chartLinks;
            var startDateOption;
            var dataLoader;
            var deviceView;

            return {

                Initialized: false,

                Init: function (d) {
                    dialog = $(document.getElementById(d));
                    var that = this;
                    $(dialog).dialog({
                        resizable: false,
                        height: 'auto',
                        width: 'auto',
                        autoOpen: false,
                        draggable: true,
                        open: function (ev, ui) {
                            that.CenterMe();

                        },
                        resize: function (ev, ui) {
                            that.CenterMe();
                        }
                    });

                    chartInfo = ChartInfo('chart-info');
                    chartLinks = ChartLinks('chart-links');
                    deviceView = DeviceView('chart-div');
                    deviceView.Empty();
                    deviceView.SetView(DeviceView.VIEWS.TABLE);
                    chartLinks.InitHandlers();
                    chartLinks.onTableLinkClicked(function () {
                        deviceView.SetView(DeviceView.VIEWS.TABLE);
                        if (deviceView.GetData() !== undefined) {
                            deviceView.DrawView();
                        }
                    });
                    chartLinks.onRainLinkClicked(function () {
                        deviceView.SetView(DeviceView.VIEWS.RAIN);
                        if (deviceView.GetData() !== undefined) {
                            deviceView.DrawView();
                        }
                    });
                    chartLinks.onWaterlevelLinkClicked(function () {
                        deviceView.SetView(DeviceView.VIEWS.WATERLEVEL);
                        if (deviceView.GetData() !== undefined) {
                            deviceView.DrawView();
                        }
                    });
                    chartLinks.onTemperatureLinkClicked(function () {
                        deviceView.SetView(DeviceView.VIEWS.TEMPERATURE);
                        if (deviceView.GetData() !== undefined) {
                            deviceView.DrawView();
                        }
                    });
                    startDateOption = DateOption('sdate');
                    startDateOption.setDate(SDATE);
                    startDateOption.onDateChanged(function (d) {
                        that.LoadData();
                    });

                    this.Initialized = true;
                },

                SetDevID: function (dev_id) {
                    device = _.findWhere(devices, {
                        dev_id: dev_id
                    });
                    var title1;
                    var title2;

                    if (device != null) {
                        title1 = device['location'];
                        title2 = device['municipality'] + " - " + device['province'];
                    } else {
                        title1 = "Unknown";
                        title2 = "Unknown";
                    }

                    chartInfo.setTitle(title1, title2);
                    chartLinks.setDeviceType(device['type']);
                    chartLinks.InitHandlers();
                },

                Show: function () {
                    $(dialog).dialog('open');
                },

                CenterMe: function () {
                    dialog.dialog("option", "position", {
                        my: "center center",
                        at: "center center",
                        of: window
                    });
                },

                LoadData: function () {
//            console.log(startDateOption.getDate());
                    deviceView.ResetData();
                    chartLinks.triggerSelected();
                    var that = this;
//            console.log(deviceView.GetData());
                    dataLoader = DataLoader(device['dev_id'], startDateOption.getDate(),
                        function (data) {
//                console.log('success');
//                console.log(data);
                            deviceView.SetData(data);
                            deviceView.DrawView();
                            that.CenterMe();
                        },
                        function () {
//                console.log('fail');
                            deviceView.OnFail(function () {
//                  console.log("Retrying");
                                that.LoadData();
                            });
                        },
                        function () {
//                console.log('before send');
                            deviceView.Empty();
                            deviceView.SetOnLoadAnim();
                            that.CenterMe();
                        });
                }
            }
        })();


        google.charts.load('44', {
            packages: ['table', 'corechart']
        });
        google.charts.setOnLoadCallback(function () {
            $(document).ready(function () {
                initMap("map-canvas");
                initMapLegends('legends');
                initMarkers();
                initControls('controls');
            });
        });


        function initMap(divcanvas) {
            var DOST_CENTER = new google.maps.LatLng(10.712317, 122.562362); //DOST CENTER

            var mapOptions = {
                //zoom: 6, //Whole Philippines View
                zoom: 8, //Region 6 Focus,
                minZoom: 8,
                maxZoom: null,
                center: DOST_CENTER,
                disableDefaultUI: true,
                mapTypeId: 'mapbox',
                mapTypeControl: true,
                mapTypeControlOptions: {
                    mapTypeIds: ['bantaypanahonstyle', 'osm', 'mapbox', 'mapboxdark']
                },
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE,
                    position: google.maps.ControlPosition.RIGHT_CENTER
                },
                //draggableCursor:'crosshair'
            };

            devices_map = new google.maps.Map(document.getElementById(divcanvas), mapOptions);

            // Bounds for region xi
            var strictBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(9.1895, 119.1193),
                new google.maps.LatLng(12.2171, 125.9308)
            );

            lastValidCenter = devices_map.getCenter();

            // Listen for the dragend event
            google.maps.event.addListener(devices_map, 'idle', function () {
                var minLat = strictBounds.getSouthWest().lat();
                var minLon = strictBounds.getSouthWest().lng();
                var maxLat = strictBounds.getNorthEast().lat();
                var maxLon = strictBounds.getNorthEast().lng();
                var cBounds = devices_map.getBounds();
                var cMinLat = cBounds.getSouthWest().lat();
                var cMinLon = cBounds.getSouthWest().lng();
                var cMaxLat = cBounds.getNorthEast().lat();
                var cMaxLon = cBounds.getNorthEast().lng();
                var centerLat = devices_map.getCenter().lat();
                var centerLon = devices_map.getCenter().lng();

                if ((cMaxLat - cMinLat > maxLat - minLat) || (cMaxLon - cMinLon > maxLon - minLon)) {
                    //We can't position the canvas to strict borders with a current zoom level
                    //devices_map.setZoom(devices_map.getZoom()+1);
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
                //devices_map.setCenter(new google.maps.LatLng(newCenterLat, newCenterLon));
                    devices_map.panTo(new google.maps.LatLng(newCenterLat, newCenterLon));
            });

            var BantayPanahonStyle = [{
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
            }];
            bantayPanahonMapType = new google.maps.StyledMapType(BantayPanahonStyle, {
                name: 'Google'
            });
            devices_map.mapTypes.set('bantaypanahonstyle', bantayPanahonMapType);
            devices_map.mapTypes.set("osm", new google.maps.ImageMapType({
                getTileUrl: function (coord, zoom) {
                    var tilesPerGlobe = 1 << zoom
                        , x = coord.x % tilesPerGlobe;
                    if (x < 0)
                        x = tilesPerGlobe + x;
                    return "http://tile.openstreetmap.org/" + zoom + "/" + x + "/" + coord.y + ".png"
                },
                tileSize: new google.maps.Size(256, 256),
                name: "OpenStreetMap",
                maxZoom: 18
            }));
            devices_map.mapTypes.set("mapbox", new google.maps.ImageMapType({
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
            devices_map.mapTypes.set("mapboxdark", new google.maps.ImageMapType({
                getTileUrl: function (coord, zoom) {
                    var tilesPerGlobe = 1 << zoom
                        , x = coord.x % tilesPerGlobe;
                    if (x < 0)
                        x = tilesPerGlobe + x;
                    //return "http://tile.openstreetmap.org/" + zoom + "/" + x + "/" + coord.y + ".png"
                    return "https://api.mapbox.com/styles/v1/dost6ryanb/cjdfnalwb15go2roccouzwbsr/tiles/256/" + zoom + "/" + x + "/" + coord.y + "?access_token=pk.eyJ1IjoiZG9zdDZyeWFuYiIsImEiOiI1OGMyZjdjNjZlYjlhNTMyNDc0NGQxOTY4ZDJlZjIxNyJ9.dkASVYIEPInwAEkwUkaGhQ";
                },
                tileSize: new google.maps.Size(256, 256),
                name: "MapBox Dark",
                maxZoom: 18
            }));
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
        }

        function initControls(container) {
            controlscontainer = $(document.getElementById(container));
            devices_map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById(container));

            var ShowStatusMode = controlscontainer.children("#status-filter");
            ShowStatusMode.on("change", function (data) {
                var opt = $(this).val();
                showMarkerWithStatus(opt);
            });

            var MapMode = controlscontainer.children("#map-mode");
            MapMode.on("change", function (data) {
                var opt = $(this).val();

                if (opt === MAP_MODES.SWITCH_STATUS) {
                    if (OpenUnlockSwitchStatusDialog(this)) {
                        //VALID INPUT LET AJAX CALLBACK DECIDE
                    } else {
                        $(this).val(MAP_MODES.VIEW_DATA);
                        CURRENT_MODE = MAP_MODES.VIEW_DATA;
                    }

                } else {
                    CURRENT_MODE = opt;
                }


            });
        }

        function OpenUnlockSwitchStatusDialog(select) {
            var ans = prompt('[TODO] Enter passphrase: (*hint: macbookair pass)');
            if (ans != null && ans !== "") {
                TRYAUTH = ans;
                $.ajax({
                    url: DOCUMENT_ROOT + 'auth.php',
                    type: "POST",
                    data: {
                        tryauth: ans
                    },
                    dataType: 'json',
                }).done(function (data) {
                    if (data['success']) {
                        //$(select).val(MAP_MODES.SWITCH_STATUS);
                        CURRENT_MODE = MAP_MODES.SWITCH_STATUS;
                    } else {
                        $(select).val(MAP_MODES.VIEW_DATA);
                        CURRENT_MODE = MAP_MODES.VIEW_DATA;
                        alert('Sorry. Wrong passphrase.');
                    }
                }).fail(function (f, n) {
                    $(select).val(MAP_MODES.VIEW_DATA);
                    CURRENT_MODE = MAP_MODES.VIEW_DATA;
                    alert('Sorry. Cannot reach authentication server. Please try again.');
                });
                return true;
            } else {
                TRYAUTH = "";
                return false;
            }

        }

        function createMarker(device) {
            var device_id = device['dev_id'];
            var posx = device['posx'];
            var posy = device['posy'];
            var type = device['type'];
            var status = device['status'];
            var title = device['municipality'] + ' - ' + device['location'];

            var image = createIcon(type, status);
            var pos = new google.maps.LatLng(posx, posy);

            var marker = new google.maps.Marker({
                position: pos,
                icon: image,
                title: title + " (" + device_id + ")",
                dev_id: device_id,
                type: type
            });

            attachMarkerClickEvent(marker, device_id, status);

            return marker;
        }

        function createIcon(type, status) {
            //values based on css spritesheet
            var iconorigin;
            if (type == "Rain1" && status == "0") {
                iconorigin = new google.maps.Point(0, 111);
            } else if (type == "Rain1" && status == "1") {
                iconorigin = new google.maps.Point(0, 74);
            } else if (type == "Rain2" && status == "0") {
                iconorigin = new google.maps.Point(0, 185);
            } else if (type == "Rain2" && status == "1") {
                iconorigin = new google.maps.Point(0, 148);
            } else if (type == "Waterlevel" && status == "0") {
                iconorigin = new google.maps.Point(0, 333);
            } else if (type == "Waterlevel" && status == "1") {
                iconorigin = new google.maps.Point(0, 296);
            } else if (type == "Waterlevel & Rain 2" && status == "0") {
                iconorigin = new google.maps.Point(0, 407);
            } else if (type == "Waterlevel & Rain 2" && status == "1") {
                iconorigin = new google.maps.Point(0, 370);
            } else if ((type == "VAISALA" || type == "BSWM_Lufft" || type == "UAAWS" || type == "UPAWS" || type == "Davis") && status == "0") {
                iconorigin = new google.maps.Point(0, 259);
            } else if ((type == "VAISALA" || type == "BSWM_Lufft" || type == "UAAWS" || type == "UPAWS" || type == "Davis") && status == "1") {
                iconorigin = new google.maps.Point(0, 222);
            } else {
                iconorigin = new google.maps.Point(0, 37);
            }

            var image = {
                url: 'images/Devices_LegendUI.png',
                size: new google.maps.Size(32, 37),
                origin: iconorigin,
                anchor: new google.maps.Point(16, 37)
            };
            return image;
        }

        function addMarkerToMap(marker) {
            marker.setMap(devices_map);
            devices_map_markers.push(marker);
        }

        function attachMarkerClickEvent(marker, dev_id, status) {
            google.maps.event.addListener(marker, 'click', function () {
                if (CURRENT_MODE === MAP_MODES.SWITCH_STATUS) {
//            console.log('Switching Status');
                    var newstatus;
                    if (status == null || status == '0') {
                        newstatus = '1';
                    } else if (status == '1') {
                        newstatus = '0';
                    } else {
                        newstatus = null;
                    }

                    postUpdateDeviceStatus(dev_id, newstatus);
                } else if (CURRENT_MODE === MAP_MODES.VIEW_DATA) {
//            console.log('Viewing Data');
                    if (!ViewStateDialog.Initialized) {
                        ViewStateDialog.Init('view-data-dialog');
                    }

                    ViewStateDialog.SetDevID(dev_id);
                    ViewStateDialog.Show();
                    ViewStateDialog.LoadData();
                }
            });
        }

        function showMarkerWithStatus(option) {
            for (var i = 0; i < devices_map_markers.length; i++) {
                if (option == STATUS.ALL) {
                    devices_map_markers[i].setMap(devices_map);
                } else {
                    var device_marker = devices_map_markers[i];
                    var device_id = device_marker['dev_id'];
                    var device = _.findWhere(devices, {
                        dev_id: device_id
                    });
                    var device_status = null;

                    if (device != null) {
                        device_status = device['status'];
                        switch (option) {
                            case STATUS.OK:
                                if (device_status == null || device_status == 0) {
                                    device_marker.setMap(devices_map);
                                } else {
                                    device_marker.setMap(null);
                                }
                                break;
                            case STATUS.DISABLED:
                                if (device_status == 1) {
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


        function postUpdateDeviceStatus(dev_id, status) {
            $.ajax({
                url: DOCUMENT_ROOT + 'update.php',
                type: "POST",
                data: {
                    dev_id: dev_id,
                    status: status,
                    tryauth: TRYAUTH
                },
                dataType: 'json',
            })
                .done(onSuccessPostUpdate)
                .fail(onFailPostUpdate);
        }

        function onSuccessPostUpdate(data) {
            var device_id = data['dev_id'];
            var status = data['status'];

            var device_marker = _.findWhere(devices_map_markers, {
                dev_id: device_id
            });
            if (device_marker != null) {
                var type = device_marker['type'];

                var image = createIcon(type, status);
                device_marker.setIcon(image);
                google.maps.event.clearListeners(device_marker, 'click');
                attachMarkerClickEvent(device_marker, device_id, status);
            }

            var device = _.findWhere(devices, {
                dev_id: device_id
            });
//        console.log(data['dev_id']);
//        console.log(device);
            if (device != null) {
                device['status'] = status;
            }


        }

        function onFailPostUpdate(data) {
//        console.log('POST fail');
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
                <li><a href="waterlevel2.php">Waterlevel Map</a></li>
                <li><a href="#" class='currentPage'>Devices Monitoring</a></li>
            </ul>
        </div>
    </div>
</div>
<div id='content'>
    <div id='map-canvas'>
    </div>
    <div id='legends'>
        <div class="legendtitle">Devices in Western Visayas</div>
        <div class="legend">
            <div class="legendicon sprite rain1"></div>
            <div class="legendtext">Automatic Rain Gauge</div>
        </div>
        <div class="legend">
            <div class="legendicon sprite rain2"></div>
            <div class="legendtext">Automatic Rain Gauge w/ Air Pressure</div>
        </div>
        <div class="legend">
            <div class="legendicon sprite waterlevel"></div>
            <div class="legendtext">Waterlevel</div>
        </div>
        <div class="legend">
            <div class="legendicon sprite waterlevel2"></div>
            <div class="legendtext">Waterlevel w/ Automatic Rain Gauge</div>
        </div>
        <div class="legend">
            <div class="legendicon sprite vaisala"></div>
            <div class="legendtext">VAISALA, UAAWS, Davis, or BSWM_Lufft</div>
        </div>
        <div class="legend">
            <div class="legendicon sprite notok"></div>
            <div class="legendtext">Status Not Ok</div>
        </div>
    </div>
    <div id='controls'>
        <label for="status-filter">Show</label>
        <select name="status-filter" id="status-filter">
            <option value="99" selected="selected">ALL</option>
            <option value="0">Only OK</option>
            <option value="1">Only DISABLED</option>
        </select>
        <label for="map-mode">Map Mode</label>
        <select name="map-mode" id="map-mode">
            <option value="0" selected="selected">View Data</option>
            <option value="1">Switch Status</option>
        </select>
    </div>
    <div id='controls2'>
    </div>
    <div id="view-data-dialog" style="display:none">
        <input type="hidden" autofocus="autofocus"/>
        <div id="chart-info">
            <h3 id="title2"></h3>
            <h5 id="title1"></h5>
        </div>
        <div id="chart-links">
            <fieldset>
                <legend>Select View Mode:</legend>
                <input type="radio" id="table" name="chart-type" checked="checked">
                <label for="table">Table</label>
                <input type="radio" id="rain" name="chart-type">
                <label for="rain">Rain</label>
                <input type="radio" id="waterlevel" name="chart-type">
                <label for="waterlevel">Water Level</label>
                <input type="radio" id="temperature" name="chart-type">
                <label for="temperature">Temperature</label>
            </fieldset>
        </div>
        <div id="date-options">
            <label for="sdate">Start Date:</label>
            <input type="text" id="sdate">
            </p>
        </div>
        <div id="chart-div"></div>
    </div>
</div>
<div id='footer'>
    <div id='contactus'>
        <div class='contact'>
            <p class='contactname'>Department of Science and Technology Regional Office No. VI</p>
            <p class='contactaddress'>Magsaysay Village La paz, Iloilo 5000</p>
            <p class='contactnumber'>(033) 508-6739 / 320-0908 (Telefax)</p>
        </div>
        <div class='contact'>
            <p class='contactname'>Aklan Provincial Science & Technology Center</p>
            <p class='contactaddress'>Capitol Compound, Kalibo, Aklan</p>
            <p class='contactnumber'>(036) 500-7550 (Telefax)</p>
        </div>
        <div class='contact'>
            <p class='contactname'>Antique Provincial Science & Technology Center</p>
            <p class='contactaddress'>San Jose de Buenevista, Antique</p>
            <p class='contactnumber'>(036) 540-8025</p>
        </div>
        <div class='contact'>
            <p class='contactname'>Capiz Provincial Science & Technology Center</p>
            <p class='contactaddress'>CapSU, Roxas City, Capiz</p>
            <p class='contactnumber'>(036) 522-1044</p>
        </div>
        <div class='contact'>
            <p class='contactname'>Guimaras Provincial Science & Technology Center</p>
            <p class='contactaddress'>PSHS Research Center, Jordan, Guimaras</p>
            <p class='contactnumber'>(033) 396-1765</p>
        </div>
        <div class='contact'>
            <p class='contactname'>Iloilo Provincial Science & Technology Center</p>
            <p class='contactaddress'>DOST VI Compound, Iloilo City, Iloilo</p>
            <p class='contactnumber'>(033) 508-7183</p>
        </div>
        <div class='contact'>
            <p class='contactname'>Negros Occidental Provincial Science & Technology Center</p>
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
    var devices = <?php echo json_encode(Devices::GetDevicesAll());?>;
</script>
<?php //include_once("analyticstracking.php") ?>

</html>