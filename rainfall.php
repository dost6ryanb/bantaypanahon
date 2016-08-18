<?php include_once 'lib/init.php'?>
<html>
<head>
	<title>DOST VI - DRRMU - Rainfall Monitoring</title>
	<script type="text/javascript" src='vendor/jquery/jquery-1.12.4.min.js'></script>
	<script type="text/javascript" src='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js'></script>
	<script type="text/javascript" src='vendor/datejs/date.js'></script>
	<script type="text/javascript" src='vendor/underscore-1.8.3/underscore-min.js'></script>
	<script type="text/javascript" src='vendor/mustache.js-2.2.1/mustache.min.js'></script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.css'>
	<link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.theme.min.css'>
	<link rel="stylesheet" href='vendor/jquery-ui-1.12.0.custom/jquery-ui.structure.min.css'>
	<link rel="stylesheet" type="text/css" href='css/style.css' />
	<link rel="stylesheet" type="text/css" href='css/screen.css' />
	<link rel="stylesheet" type="text/css" href='css/superfish.css' />
	<link rel="stylesheet" type="text/css" href='css/pages/rainfall.css' />
</head>
<script type="text/javascript">
	var key = {'serverdate':'<?php echo $sdate;?>', 'servertime':'<?php echo date("H:i");?>',
				'provinces' : ['Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental'],
				'durations' : [{'label': '1 hr', 'minutes':'60'},
								{'label': '2 hr', 'minutes':'120'},
								{'label': '3 hr', 'minutes':'180'},
								{'label': '6 hr', 'minutes':'360'},
								{'label': '9 hr', 'minutes':'540'},
								{'label': '12 hr', 'minutes':'720'},
								{'label': '24 hr', 'minutes':'1440'}
							 ],
				'limits' : [
			   		{'min':0.01, 'max':5, 'name':'lighter', 'style':'rainlighter'},
			   		{'min':5, 'max':25, 'name':'light', 'style':'rainlight'}, 
			   		{'min':25, 'max':50, 'name':'moderate' , 'style':'rainmoderate'}, 
			   		{'min':50, 'max':75, 'name':'heavy', 'style':'rainheavy'}, 
			   		{'min':75, 'max':100, 'name':'intense', 'style':'rainintense'}, 
			   		{'min':100, 'max':999, 'name':'torrential', 'style':'raintorrential'}
			   		]
		  };

	MyApp = {};

	MyApp.SERVER_DATE = '<?php echo $sdate;?>';

	MyApp.config = {
		provinces : ['Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental'],
		durations : [
			{'label': '1 hr', 'minutes':'60'},
			{'label': '3 hr', 'minutes':'180'},
			{'label': '6 hr', 'minutes':'360'},
			{'label': '9 hr', 'minutes':'540'},
			{'label': '12 hr', 'minutes':'720'},
			{'label': '24 hr', 'minutes':'1440'}
		]
	};

	MyApp.RainfallTableGenerator = (function() {
		var TEMPLATE_ID = 'rainfall-table_template';
		var _templateSource = null;
		//var _templateRendered = null;

		var build_template = function() {
			_templateSource = $(document.getElementById(TEMPLATE_ID)).html();
			Mustache.parse(_templateSource);
		}

		return {
			getRenderedTemplate(context) {
				if (!_templateSource) {
					build_template();
				}

				return Mustache.render(_templateSource, context);
			}
		}
	})();

	MyApp.RainfallTableCount = 0;

	MyApp.RainfallTable = (function() {
		var _p; //jq object of the container
		var _e; //jq object of the table
		var class_pref = "xtbl";
		var _onClickCallBack;
		var _id; //global counter mirror

		return {
			add : function(container, location_filter, duration, basedate) {
				_p =  _p || $(document.getElementById(container));
				_id = class_pref + "--" + MyApp.RainfallTableCount++;
				var filteredDevices = _.where(rainfall_devices, {province_name : location_filter});
				var dateText = basedate.toString("MM/dd/yyyy");
				var timeText = (duration > 60) ? parseInt(duration/60) + ' hours' : '1 hour';
				var options = {
					pref : class_pref,
					id : _id,
					location_group : location_filter,
					time : timeText,
					date : dateText,
					devices : filteredDevices,
					cssByStatus : function() {
						return function(text, render) {
							if (render(text) == "1") {
								return 'class="disabled"';
							} else {
								return '';
							}
						}
					}
				};

				console.log(options.devices);

				//var rendered = render_template(options);
				var rendered = MyApp.RainfallTableGenerator.getRenderedTemplate(options);

				_p.append(rendered);

				_e = $(document.getElementById(_id));
				var btnEl = _e.find("button." + class_pref + "__close-button");
				btnEl.on('click', function(){
					console.log("Click internal");
					_onClickCallBack(_id);
				});
				//_e.effect('shake');
			},

			remove : function() {
				_e.fadeOut('slow', function() {
					$(this).remove();
				});
			},

			onCloseButtonClick : function(fn) {
				_onClickCallBack = fn;
			},

		};
	});

	var xtblcounter = 0;
	google.charts.load('current', {packages: ['corechart']});
	$(document).ready(function() {
		initConfigUI('config-form', MyApp.config);
	});

	//el - element(div) ID
	function initConfigUI(el, context) {
		var source = $(document.getElementById(el));

		var template = source.html();
		var rendered = Mustache.render(template, context);

		source.html(rendered);

		//JQuery Ui selectmenu
		$('select').selectmenu({ width: 200 });

		//JQuery Ui DatePicker
		var default_date = Date.parseExact(MyApp.SERVER_DATE, 'MM/dd/yyyy');
		initDatePicker('basedate', default_date);

		//JQuery button
		$("#go-button").button();
		$("#go-button").on('click', function() {
			var province = $(document.getElementById('provinces')).val();
			var duration = $(document.getElementById('durations')).val();
			var basedate = $(document.getElementById('basedate')).datepicker( "getDate" );
			var rainTable = new MyApp.RainfallTable();
			rainTable.add('tables-container', province, duration, basedate);
			rainTable.onCloseButtonClick(function(d) {
				console.log("Clicked " + d);
				rainTable.remove();
			});
		});


		//Some info
		$('#info-refresh').one('click', function() {
			console.log($(this));
			$(this).fadeOut();
		});
	}

	//el - element(input[text]) ID
	//date - Date Object
	function initDatePicker(el, date) {
		var source = document.getElementById(el);
		var date = date || Date.now();

		$(source).datepicker({
			defaultDate: date,
			dateFormat: 'M dd, yy',
			//onSelect: function(dateText) {		
				//var predict_date = Date.parseExact(dateText, "MMM dd, yyyy").toString("MM/dd/yyyy");
				//key['serverdate'] = predict_date;
				//MyApp.config.querydate = predict_date;
			//}
		})
		.datepicker( "setDate", date);
	}


	function initRainfalltable(div, province, duration, basedate) {
		var div = $(document.getElementById(div));
		var thisdate = Date.parseExact(basedate, 'MM/dd/yyyy');
		var yesterday = thisdate.clone().add({days: -1});
		var table = $('<table/>', {'class':'xtbl', 'id':'xtbl_'+ ++xtblcounter, 'data-duration':duration}).prependTo(div);
		//$('<tr><th>Server DateTime</th><td id="serverdtr">'+key['serverdate']+' '+ key['servertime']+'</td><tr>').appendTo(table);
		$('<tr/>')
		.append($('<th colspan="2" class="ui-widget-header">Cumulative Rainfall Reading of '+ province +' for the last '+ parseInt(duration/60) +' hour/s from ' + thisdate.toString("MMM dd, yyyy") +  '.</th>').append('<button id="cx-xtbl_'+ xtblcounter +'" class="close-button">close</button>'))
						// .append($('<td/>@ ', {'class':'textalignright'}).text(key['serverdate']+' '+ key['servertime'])
							// .append($('<button>close</button>')	
					.appendTo(table);
		
		$("#cx-xtbl_"+ xtblcounter).on('click', function() {table.remove();})
								.button({
							      icons: {
							        primary: "ui-icon-cancel"
							      },
							      text: true});
		// $('<tr class="ui-widget-header"><th>Municipality</th><th>Location</th><th>Cumulative (mm)</th><tr>').appendTo(table);
		//$('<tr class="ui-widget-header"><th>Location</th><th>Cumulative (mm)</th><tr>').appendTo(table);
		//$('<tr class="ui-widget-header"><th colspan="2">Cumulative (mm)</th><tr>').appendTo(table);
						
		for(var i=0;i<rainfall_devices.length;i++) {
			var cur = rainfall_devices[i];
			if (cur['province_name'] == province) {
				$('<tr/>', {'data-dev_id':cur['dev_id']})
				.append($('<td>'+cur['municipality_name']+ " - " + cur['location_name'] + '</td>'))
				// .append($('<td>'+cur['location_name']+'</td>'))
				.append($('<td/>', {'colspan':'2','data-col':'cr'})).appendTo(table);
				if (cur['status_id'] == null || cur['status_id'] == '0') {
					postGetData('xtbl_'+xtblcounter, cur['dev_id'], yesterday.toString("MM/dd/yy"), thisdate.toString("MM/dd/yy"), "", duration);
				} else {
					updateRainfallTable('xtbl_'+xtblcounter, cur['dev_id'], "[DISABLED]", 'disabled') ;
				}
			}
		}
	}

	function postGetData( xtbl, dev_id, sdate, edate, limit, duration) {
		$.ajax({
				url: DOCUMENT_ROOT + 'data.php',
				type: "POST",
				data: {start: 0,
		  		 limit: limit,
		  		 sdate: sdate,
		  		 edate: edate,
		  		 pattern: dev_id
			},
			dataType: 'json',
			tryCount: 0,
			retry:20})
		.done(function(d){onRainfallDataResponseSuccess(d, xtbl, duration);})
		.fail(function(f, n){onRainfallDataResponseFail(xtbl, dev_id, duration);});
	}

	function onRainfallDataResponseSuccess(data, xtbl, duration) {
		var device_id = data.device[0].dev_id;

		$('#loadedraindevices').text(++key['loadedraindevices']);

		if (data.count == -1) {// cannot reach predict
			onRainfallDataResponseFail(xtbl, device_id, duration);
		} else if (data.count ==  0 ||// sensor no reading according to fmon.predict
			data.data.length == 0  || // predict reports that it has reading but actually doesnt have
			data.data[0].rain_cumulative == null || data.data[0].rain_cumulative=='' // errouneous readings
			) {
			updateRainfallTable(xtbl, device_id, '[NO DATA]', 'nodata') ;
		} else {			
			var rain_cumulative = solveforcumulative(data, duration, xtbl, device_id);
			//updateRainfallTable(xtbl, device_id, rain_cumulative) ;

		}
	}

	function onRainfallDataResponseFail(xtbl, dev_id, duration) {
		var retryhtml = "<a href=javascript:retryFetchRain('"+xtbl+"'," + dev_id + "," + duration + ")>Retry</a>";
		updateRainfallTable(xtbl, dev_id, retryhtml, null, null);
	}
    
    function retryFetchRain(xtbl, dev_id, duration) {
		postGetData(xtbl, dev_id, "", "", duration );
		updateRainfallTable(xtbl, dev_id, '', '', '');
	}

	function updateRainfallTable(xtbl, device_id, raincumulative, dataclass) {
		var table = $(document.getElementById(xtbl));
		var dtr = table.find('tr[data-dev_id=\''+device_id+'\']  td[data-col=\'dtr\']');
		var cr = table.find('tr[data-dev_id=\''+device_id+'\'] td[data-col=\'cr\']');

		//if (dateTimeRead != null) dtr.text(dateTimeRead); else dtr.text('');
		//if (rainvalue != null ) rv.text(rainvalue); else rv.text('');
		if (raincumulative != null ) {
			cr.html(raincumulative); 
			for (var i = 0;i<key['limits'].length;i++) {
				limit = key['limits'][i];
				if (raincumulative >= parseFloat(limit['min']) && raincumulative < parseFloat(limit['max'])) {
					cr.addClass(key['limits'][i].style);
					break;
				} 
			}

			

		} else {
			cr.text("");
		}

		if (dataclass != 'undefined') {
			//dtr.removeClass().addClass(dataclass);
			//rv.removeClass().addClass(dataclass);
			cr.addClass(dataclass);
		}
	}

	function drawChartRain(xtbl, dev_id, json) {
		var table = $(document.getElementById(xtbl));
		var cr = table.find("tr[data-dev_id='" + dev_id + "'] td[data-col='cr']");
		var div_id = xtbl + '-' + dev_id;
		var c = $('<div id="'+ div_id + '"" class="rain-chart"></div>');
		cr.append(c);
		//console.log(c);
		var chartdiv = xtbl + '-' + dev_id;
	  	var datatable = new google.visualization.DataTable();
		datatable.addColumn('datetime', 'DateTimeRead');
		datatable.addColumn('number', 'Cumulative Rain');
		datatable.addColumn('number', 'Rain Value');
		
		//j - index of data
		// i - index of column
		for(var j=0;j<json.data.length;j++) {
			var row = Array(3);
			//console.log(json.data[j].dateTimeRead);
			row[0] = Date.parseExact(json.data[j].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
			row[1] = {
					v:parseFloat(json.data[j].rain_cumulative), //cumulative rain
					f:json.data[j].rain_cumulative + ' mm'
				}
			row[2] = {
					v:parseFloat(json.data[j].rain_value), //rain value
					f:json.data[j].rain_value + ' mm'
				}
			
			datatable.addRow(row);
			
		}
		var maxdate;
		var mindate;

		var d =  Date.parseExact(json.data[json.data.length - 1].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
		var d2 =  Date.parseExact(json.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

		//var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); //from last data
		var title_startdatetime = d.toString('MMMM d yyyy h:mm:ss tt'); // from 8:00 AM
		var title_enddatetime = d2.toString('MMMM d yyyy h:mm:ss tt');

		var options = {
		  title: 'Rainfall Reading from ' + title_startdatetime + ' to ' + title_enddatetime,
		  hAxis: {
		    title: 'Rainfall Cumulative: ' + json.data[0].rain_cumulative + " mm", 
			format : 'LLL d h:mm:ss a',
			viewWindow : {min : d, max : d2},
			textStyle : {fontSize: 10}
		  },
		  vAxes : {
		  	0 : {
		  		title: 'Rain Value (mm)',
				format: '# mm',
				minValue: '0',
				maxValue: '50'
		  	},
		  	1 : {
		  		title: 'Cumulative (mm)',
		  		direction: -1,
		  		format: '# mm',
		  		minValue: '0',
				maxValue: '200',
		  	}
		  },
		  pointsize: 3,
		  seriesType: "line",
          series: {
          	0 : {
          		type: "line",
          		targetAxisIndex : 1
          	},
          	1: {
          		type: "bars",
          		targetAxisIndex : 0
          		}
          },
		  crosshair : {trigger: 'both'}
        };
		var chart =  new google.visualization.ComboChart(document.getElementById(chartdiv));
        chart.draw(datatable, options);
	  }

	function solveforcumulative(data, duration, xtbl, device_id) {
		var serverdtr = Date.parseExact(key['serverdate']+ ' '+ key['servertime']+':00', 'MM/dd/yyyy HH:mm:ss');
		var str = '';
		var cr = null;
		var enddtr = serverdtr.clone().addMinutes(-parseInt(duration));

		var i=0;
		for(i=0;i<data.data.length;i++) {
			var devicedtr = Date.parseExact(data.data[i].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');

			if (devicedtr.between(enddtr, serverdtr)) {
				if (cr == null) cr = 0;
				cr += parseFloat(data.data[i].rain_value);
				data.data[i].rain_cumulative = cr;
			} else {
				if (i==0) {
					cr += parseFloat(data.data[i].rain_value);
					serverdtr = Date.parseExact(data.data[0].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
					enddtr = serverdtr.clone().addMinutes(-parseInt(duration));
					str = ' <a class="ui-state-error" href="#" title="Out of Sync. Result from ' + serverdtr.toString('MMMM d yyyy h:mm:ss tt') + '">[!]</a>';
				} else {
					
					break;
				}
				
			}
		}

		var t = data;
		t.data.splice(i);
		t.count = i;

		var rel_cr = 0.00;


		for (i=t.data.length-1;i>=0;i--) {
			//console.log(t.data[i].dateTimeRead + " - " + t.data[i].rain_value + " - " + rel_cr);
			var devicedtr = Date.parseExact(t.data[i].dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
			rel_cr += parseFloat(t.data[i].rain_value);
			t.data[i].rain_cumulative = rel_cr.toFixed(2);
		}

		
		drawChartRain(xtbl, device_id, t);
		
		return cr.toFixed(2) + str;

	}

</script>
<body>
	<div id="header">
		<div id="banner">
		<img id='logo' src='images/BANTAY_PANAHON.png'/>
        <img id='logo_right' src='images/header_1_right.png'/>
	</div>
		
	  	<div id='menu'>
			<ul>
				<li ><a href="index.php">Home</a></li>
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
			<table class="{{pref}}" id="{{id}}">
				<tr>
					<th colspan="2" class="ui-widget-header">Cumumative Rainfall Reading of {{location_group}} for the last {{time}} from {{date}}. <button class="{{pref}}__close-button"></button></th>
				</tr>
				{{#devices}}
				<tr data-dev_id="{{dev_id}}">
					<td>{{municipality_name}} - {{location_name}}</td>
					<td data-col="result"{{#cssByStatus}}{{status_id}}{{/cssByStatus}}></td>
				</tr>
				{{/devices}}
			</table>
		</script>
		</div>
	</div>
	<div id="footer">
        <div id='contactus'>
            <div class='contact'>
                <p class='contactname' >Department of Science and Technology Regional Office No. VI</p>
                <p class='contactaddress'>Magsaysay Village La paz, Iloilo 5000</p>
                <p class='contactnumber'>(033) 508-6739 / 320-0908 (Telefax)</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Aklan Provincial Science & Technology Center</p>
                <p class='contactaddress'>Capitol Compound, Kalibo, Aklan</p>
                <p class='contactnumber'>(036) 500-7550 (Telefax)</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Antique Provincial Science & Technology Center</p>
                <p class='contactaddress'>San Jose de Buenevista, Antique</p>
                <p class='contactnumber'>(036) 540-8025</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Capiz Provincial Science & Technology Center</p>
                <p class='contactaddress'>CapSU, Roxas City, Capiz</p>
                <p class='contactnumber'>(036) 522-1044</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Guimaras Provincial Science & Technology Center</p>
                <p class='contactaddress'>PSHS Research Center, Jordan, Guimaras</p>
                <p class='contactnumber'>(033) 396-1765</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Iloilo Provincial Science & Technology Center</p>
                <p class='contactaddress'>DOST VI Compound, Iloilo City, Iloilo</p>
                <p class='contactnumber'>(033) 508-7183</p>
            </div>
            <div class='contact'>
                <p class='contactname' >Negros Occidental Provincial Science & Technology Center</p>
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
var rainfall_devices = <?php echo json_encode(Devices::GetAllDevicesWithParameter('Rainfall'));?>;
</script>
<?php //include_once("analyticstracking.php") ?>
</html>