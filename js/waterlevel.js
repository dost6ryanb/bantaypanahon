//waterlevel.js
var key = {
    'sdate': '<?php echo $sdate;?>'
};

var app = {
    sdate: SDATE,
    xhrHelper: xhrPoolHelper($)
}

google.charts.load('current', {packages: ['corechart']});

google.charts.setOnLoadCallback(function () {
    $(document).ready(function () {
        var string_date = moment(SDATE, 'MM/DD/YYYY').format('MMMM DD, YYYY');
        updateTitle(string_date);
        initializeChartDivs('charts_div_container');
        initializeDateTimePicker('datetimepicker_container');
        initFetchData();
    });
});

function updateTitle(text) {
    var el_daterange = $('#daterange');
    el_daterange.text(text);
}

function initializeDateTimePicker(div) {
    var container = $(document.getElementById(div));
    $('<h2>Waterlevel Reading for &nbsp;</h2>').appendTo(container);
    var datepicker = $('<input type="text" style="height: 0px; width:0px; border: 0px;z-index: 10000; position: relative" id="dtpicker"/>');
    var sdate = $('<a title="Click to change" href="#" id="sdate">' + SERVER_DATE + '</a>');
    datepicker.appendTo(container);
    sdate.appendTo(container);


    $('#dtpicker').datepicker({
        onSelect: function (data) {
            sdate.text(data);
            console.log(data);
            key['sdate'] = data;
            //$.xhrPool.abortAll();
            app.xhrHelper.abortAll();
            initializeChartDivs('charts_div_container');
            initFetchData(true);
        }
        //,
       //  altField: '#datepicker_start',
       //  altFormat : 'mm/dd/yy',
        // dateFormat : 'yymmdd'
    });
    $('#sdate').click(function () {
        $('#dtpicker').datepicker('show');
    });
}

function initializeChartDivs(div) {
    var charts_container = $(document.getElementById(div));
    charts_container.empty();
    //charts_container.append($('<h4>Latest Waterlevel Reading @ ' + key['serverdate']+' '+ key['servertime'] +'</h4>'));


    var prevProvince = '';
    var prevRiverIndex = '';
    var addLeadingArrow = false;

    for (var i = 0; i < waterlevel_devices.length; i++) {
        var cur = waterlevel_devices[i];
        if (cur['province'] != prevProvince) {
            prevProvince = cur['province'];
            $('<br/><h3 class="provincelabel">' + prevProvince + '</h3>').appendTo(charts_container);
        }

        if (cur['riverindex'] && prevRiverIndex && cur['riverindex'].charAt(0) == prevRiverIndex.charAt(0)) {
            addLeadingArrow = true;
        } else {
            addLeadingArrow = false;
        }

        prevRiverIndex = cur['riverindex'];

        var chart_title = cur['municipality'] + ' - ' + cur['location'];
        var chart_div = $('<div></div>')
            .attr({
                'id': 'chart_div_' + cur['dev_id'],
                'class': 'chart_div'
            }).appendTo(charts_container);

        var chart_overlay_class = 'chart_overlay';
        if (addLeadingArrow) {
            chart_overlay_class = 'chart_overlay connected';
        }

        chart_div.html('<div class="'+chart_overlay_class+'"><p>' + chart_title + '</p></div><div id="chart_' + cur['dev_id'] + '" class="chart"></div>');

        if (cur['status'] != null && cur['status'] != 0) {
            $("#chart_" + cur['dev_id']).css({
                'background': 'url(images/disabled.png)',
                'background-size': '100%',
                'background-repeat': 'no-repeat'
            });
        }

    }

}

function initFetchData(history) {
    setTimeout(function () {

        for (var i = 0; i < waterlevel_devices.length; i++) {
            var cur = waterlevel_devices[i];

            if (history) {
                postGetData(cur['dev_id'], key['sdate'], key['sdate'], "144", onWaterlevelDataResponseSuccess);
            } else {
                if (cur['status'] == null || cur['status'] == '0') {
                    postGetData(cur['dev_id'], key['sdate'], "", "", onWaterlevelDataResponseSuccess);
                }
            }
        }
    }, 200);
}


function postGetData(dev_id, sdate, edate, limit, successcallback) {
    $.ajax({
        url: DOCUMENT_ROOT + 'data.php',
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

function onWaterlevelDataResponseSuccess(data) {
    var device_id = data.device[0].dev_id;
    var div = 'chart_' + device_id;

    if (data.count == -1) { // fmon.predict 404


    } else if (data.count == 0 || // sensor no reading according to fmon.predict
        data.data.length == 0
    /*|| // predict reports that it has reading but actually doesnt have
     data.data[0].waterlevel == null || data.data[0].waterlevel=='' // errouneous readings*/
    ) {
        //$(document.getElementById(div)).hide();
        $(document.getElementById(div)).css({
            'background': 'url(images/nodata.png)',
            'background-size': '100%',
            'background-repeat': 'no-repeat'
        });
    } else {
        drawChartWaterlevel(div, data);
    }
}

function onRainfallDataResponseFail(dev_id) {
    postGetData(dev_id, '', '');
}


function drawChartWaterlevel(chartdiv, json) {
    var datatable = new google.visualization.DataTable();
    datatable.addColumn('datetime', 'DateTimeRead');
    datatable.addColumn('number', 'Waterlevel'); //add column from index i


    datatable.addColumn('number', 'Waterlevel Above 12 Meter'); //add column from index i
    datatable.addColumn('number', 'Waterlevel Above Sensor'); //add column from index i
    datatable.addColumn('number', 'Waterlevel Possible Overflow'); //add column from index i

    device = search(waterlevel_devices, "dev_id", json.device[0]['dev_id']);
    //console.log(device);

    for (var j = 0; j < json.data.length; j++) {
        var row = Array(5);

        row[0] = Date.parseExact(json.data[j].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

        //if (value > 1) {
        /*row[2] = {
         v:parseFloat(value),
         f:formattedvalue
         };*/
        //} else {
        if (json.data[j].waterlevel != null) {
            var value = json.data[j].waterlevel / 100;
            var formattedvalue = value + ' m';
            row[1] = {
                v: parseFloat(value),
                f: formattedvalue
            };
        }
        //}
        if (j == 0 || j == json.data.length - 1) {

            if (device['device_height'] != null) {
                row[3] = parseFloat(device['device_height']);
                row[2] = 12.0;
            }
            if (device['water_overflow'] != null) {
                row[4] = parseFloat(device['water_overflow']);
                row[2] = 12.0;
            }

        }

        datatable.addRow(row);

    }

    var d = Date.parseExact(json.data[json.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
    var d2 = Date.parseExact(json.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

    /*console.log(json.data[json.data.length - 1].dateTimeRead + " -- " + json.data[0].dateTimeRead);
     console.log(d + " -- " + d2);
     console.log('>>');
     */
    //var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
    var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
    var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');

    var options = {
        title: title_enddatetime,
        hAxis: {
            title: 'Waterlevel: ' + (json.data[0].waterlevel / 100) + ' m',
            format: 'LLL d h:mm:ss a',
            viewWindow: {
                min: d,
                max: d2
            },
            gridlines: {
                color: 'none'
            },
            textStyle: {
                fontSize: 10
            },
            textPosition: 'none'
        },
        vAxis: {
            title: '',
            format: '# m',
            minValue: '0',
            maxValue: '12',
            gridlines: {
                count: 13
            },
            viewWindow: {
                min: 0
            }

        },
        legend: {
            position: "none",
            maxLines: 4
        },
        // chartArea : {
        // 	backgroundColor: "#ff6666"
        // }	,
        pointsize: 3,
        seriesType: 'area',
        crosshair: {
            trigger: 'both'
        },
        allowHtml: true,
        //interpolateNulls: true,
        lineWidth: 0,
        areaOpacity: 0.5,
        series: {
            0: {
                areaOpacity: 0.0,
                lineWidth: 2.0
            },
            1: {
                color: "red"
            },
            2: {
                color: "orange"
            },
            3: {
                color: "yellow"
            }
        }
    };

    if (device['water_normal'] != null) {
        options['vAxis'].baseline = device['water_normal'];
    }
    var chart = new google.visualization.ComboChart(document.getElementById(chartdiv));
    chart.draw(datatable, options);
    //$('<div/>').text('Waterlevel: '+json.data[0].waterlevel+ ' cm').css({'height':'20px'}).appendTo('#'+chartdiv);
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