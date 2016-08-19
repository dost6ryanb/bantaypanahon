<?php include_once 'lib/init.php'?>
<html>
<head>
	<title>DOST VI - DRRMU - Rainfall Monitoring</title>
	<script type="text/javascript" src='vendor/jquery/jquery-1.12.4.min.js'></script>
	<script type="text/javascript" src='vendor/jquery-ui-1.12.0.custom/jquery-ui.min.js'></script>
	<script type="text/javascript" src='vendor/datejs/date.js'></script>
	<script type="text/javascript" src='vendor/underscore-1.8.3/underscore-min.js'></script>
	<script type="text/javascript" src='vendor/sprintf.js-1.0.3/dist/sprintf.min.js'></script>
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
		var $parent; //jq object of the container
		var $el; //jq object of the table
		var htmlID; //html ID of $el
		var cssClass = "xtbl";
		var fnOnClickCallBack;
		var baseDate;
		var duration;
		var that = this;
		var xhrPool = [];
		var xhrPoolAbortAll = function() {
			_.each(xhrPool, function(me) {
	   			console.log("cancel ");
	   			console.log(me);
			});
			xhrPool.length = 0
		};

		var fetchData = function(dev_id, sdate, edate, limit) {
			$.ajax({
				url: DOCUMENT_ROOT + 'data.php',
				type: "POST",
				data: {
					start: 0,
		  		 	limit: limit,
		  		 	sdate: sdate,
		  		 	edate: edate,
		  		 	pattern: dev_id
				},
				dataType: 'json',
				beforeSend: function(jqXHR) {
			        xhrPool.push(jqXHR);
			    },
			    complete: function(jqXHR) {
			        var index = xhrPool.indexOf(jqXHR);
			        if (index > -1) {
			            xhrPool.splice(index, 1);
			        }
			    }
			})
			.fail(function(f, n){
				putRetryOnTD(dev_id);
			})
			.done(function(d){
				putRetryOnTD(dev_id);
			});
		};

		var onDataArrive = function(data) {

		};

		var putRetryOnTD = function(dev_id) {
			updateDeviceDataTD(dev_id, "RETRY ka " + dev_id);
		};

		var updateDeviceDataTD = function(dev_id, html) {
			var selector = sprintf("tr[data-dev_id='%s'] td[data-col='result']", dev_id);
			$el.find(selector).html(html);
		};


		return {
			add : function(container, location_filter, duration, basedate) {
				$parent = $parent || $(document.getElementById(container));
				htmlID = cssClass + "--" + MyApp.RainfallTableCount++;
				
				var selectedDevices = _.where(rainfall_devices, {province_name : location_filter});
				var baseDate = basedate.clone();
				var baseDateText = baseDate.toString("MM/dd/yyyy");
				var yesterdayDate = baseDate.clone().add({days:-1});
				var yesterdayDateText = yesterdayDate.toString("MM/dd/yyyy");
				var timeText = (duration > 60) ? parseInt(duration/60) + ' hours' : '1 hour';
				var options = {
					cssClass : cssClass,
					id : htmlID,
					location_group : location_filter,
					time : timeText,
					date : baseDateText,
					devices : selectedDevices,
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

				var rendered = MyApp.RainfallTableGenerator.getRenderedTemplate(options);

				$parent.prepend(rendered);

				$el = $(document.getElementById(htmlID));
				var btnSelector = sprintf("button.%s__close-button", cssClass);
				console.log(btnSelector);
				var btnEl = $el.find(btnSelector);
				btnEl.button({
					icons: { primary: "ui-icon-closethick"},
					text: false
				});
				btnEl.on('click', function(){
					fnOnClickCallBack(htmlID);
				});

				_.each(selectedDevices, function(c) {
					if (c['status_id'] == "0") { //fetch only enabled device
						fetchData(c['dev_id'], yesterdayDateText, baseDateText, "", duration);
					}
				});
			},

			remove : function() {
				$el.remove();
				xhrPoolAbortAll();
			},

			onCloseButtonClick : function(fn) {
				fnOnClickCallBack = fn;
			},

		};
	});

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
			$(this).fadeOut("fast");
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
			<table class="{{cssClass}}" id="{{id}}">
				<tr>
					<th colspan="2" class="ui-widget-header">Cumumative Rainfall Reading of {{location_group}} for the last {{time}} from {{date}}. <button class="{{cssClass}}__close-button"></button></th>
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