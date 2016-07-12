<?php include_once 'lib/init.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>DOST VI DRRMU - Devices</title>
<script type="text/javascript" src='js/jquery-1.11.1.min.js'></script>
<script type="text/javascript" src='js/jquery-ui.min.js'></script>
<script type="text/javascript" src='js/jquery.scrollTo.min.js'></script>
<script type="text/javascript" src='js/date-en-US.js'></script>
<link rel="stylesheet" href='css/jquery-ui.min.css'>
<link rel="stylesheet" href='css/jquery-ui.theme.min.css'>
<link rel="stylesheet" href='css/jquery-ui.structure.min.css'>
<link rel="stylesheet" type="text/css" href='css/style.css' />
<link rel="stylesheet" type="text/css" href='css/screen.css' />
<link rel="stylesheet" type="text/css" href='css/pages/devices.css' />
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBIHIWYF28n_7UpQiud5ZNQP6C4G3LmTtU&sensor=false"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

	const MARKERS = [
		{'name':'Rain1', 'src':'images/rain1'},
		{'name':'Rain2', 'src':'images/rain2'}, 
		{'name':'Waterlevel' , 'src':'images/waterlevel'}, 
		{'name':'Waterlevel & Rain 2', 'src':'images/waterlevel2'}, 
		{'name':'VAISALA', 'src':'images/vaisala'},
		{'name':'BSWM_Lufft', 'src':'images/vaisala'},
		{'name':'UAAWS', 'src':'images/vaisala'},
		{'name':'UPAWS', 'src':'images/vaisala'}
	];

	const MAP_MODES = {
		VIEW_DATA : '0',
		SWITCH_STATUS : '1'
	};

	var CURRENT_MODE = MAP_MODES.VIEW_DATA;
	var SDATE ='<?php echo $sdate;?>';

	var devices_map;
	var devices_map_markers = [];
	var lastValidCenter;
	var ALLOWEDIT = false;
	var TRYAUTH = '';
	$.xhrPool = [];
		$.xhrPool.abortAll = function() {
    		$(this).each(function(idx, jqXHR) {
       			jqXHR.abort();
    		});
    		$.xhrPool.length = 0
		};

	$.ajaxSetup({
	    beforeSend: function(jqXHR) {
	        $.xhrPool.push(jqXHR);
	    },
	    complete: function(jqXHR) {
	        var index = $.xhrPool.indexOf(jqXHR);
	        if (index > -1) {
	            $.xhrPool.splice(index, 1);
	        }
	    }
	});

	var DataLoader = (function(d, s, fnSuccess, fnFail, fnBeforeSend) {
		var daurl = DOCUMENT_ROOT + 'data.php';

		$.ajax({
			url : daurl,
			type: 'POST',
			beforeSend : fnBeforeSend,
			data : {
				start : 0,
				limit : "",
				sdate : s,
				edate : s,
				pattern: d
			},
			dataType: 'json'
		}).
		done(fnSuccess).
		fail(fnFail);

		return this;
	});


	var DeviceView = (function(e) {
		var c = $(document.getElementById(e));
		var data;
		var DEFAULT_HEIGHT = "420px";

		DeviceView.VIEWS = {
			TABLE : 0,
			RAIN : 1,
			NODATA : 99
		};

		var currentView;

		function DrawNoData() {
			c.css({'background-image': 'url(images/nodata.png)'});
		}

		function DrawDummy() {
			c.css({'background-image': 'url(images/bp-logo.png)'});
		}

		function DrawRetry() {
			c.css({'background-image': 'url(images/retry.png)'});
		}

		function DrawTable() {
			console.log("drawing table");

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

			for(var j=0;j<data.data.length;j++) {
				var datum = data.data[j];
				var dtrd = Date.parseExact(datum.dateTimeRead, 'yyyy-MM-dd HH:mm:ss');
				var dtrc = Date.parseExact(datum.dateTimeReceived, 'yyyy-MM-dd HH:mm:ss');

				var row = [];

				row.push({v:dtrd, f:dtrd.toString('yyyy-MM-dd HH:mm:ss')});
				row.push({v:dtrc, f:dtrc.toString('yyyy-MM-dd HH:mm:ss')});

				for(var key2 in datum) {

					if (key2 != 'dateTimeRead' && key2 != 'dateTimeReceived')
					row.push(datum[key2]);
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
			  title: 'Data Reading from ' + title_startdatetime + ' to ' + title_enddatetime,
			  showRowNumber: true,
			    page: 'enable',
			    pageSize: 24,
				sortAscending:false,
				sortColumn:0
			 };
			 
			 var chart = new google.visualization.Table(c[0]);
	         chart.draw(datatable, options);
		}

		return {
			Empty : function() {
				c.empty();
			},
			ResetData : function() {
				var u = (function () { return; })();
				this.SetData(u); // reset data to undefined
				//this.SetView(u); // reset view to undefined
			},
			ResetHeightDefault : function() {
				c.css('height', DEFAULT_HEIGHT);
			},
			SetOnLoadAnim : function() {
				c.css({'background-image': 'url(images/rain-loader.gif)'});
			},
			SetData : function(d) {
				data = d;
			},
			GetData : function() {
				return data;
			},
			SetView : function(v) {
				currentView = v;
			},
			DrawView : function() {
				console.log("drawing " + currentView);
				console.log(data);
				if (data.count == -1 || data.count == 0 || data.data.length == 0) {
					console.log(data.count);
					console.log(data.data.length);

					currentView = DeviceView.VIEWS.NODATA;
				}

				switch (currentView) {
					case (DeviceView.VIEWS.NODATA):
						DrawNoData();
						break;
					case (DeviceView.VIEWS.TABLE):
						DrawTable();
						break;
					default:
						DrawDummy();
						break;
				}
			},
			OnFail : function(fn) {
				DrawRetry();
				c.one("click", fn);
			}

		}
	});

	var ChartInfo = (function(e) {
		var chartInfo = $(document.getElementById(e));

		return {
			setTitle : function(t, s) {
				chartInfo.children("#title1").text(t);
				chartInfo.children("#title2").text(s);
			}
		};
	});

	var ChartLinks = (function(e) {
		var chartLinks = $(document.getElementById(e));
		var table = chartLinks.children('#table');
		var rain = chartLinks.children('#rain');
		var temp = chartLinks.children('#temperature');
		var wtrlevel = chartLinks.children('#waterlevel');
		var tblLinkHandler;
		var rnLinkHandler;

		return {
			setDeviceType : function(t) {
				chartLinks.children("input[name='chart-type']").prop("disabled", true);

				table.prop("disabled", false);
				
				if ($.inArray(t, ['VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft']) != -1) {
					rain.prop("disabled", false);
				}

				if ($.inArray(t, ['VAISALA', 'UAAWS', 'BSWM_Lufft']) != -1) {
					temp.prop("disabled", false);
				}

				if ($.inArray(t, ['Waterlevel', 'Waterlevel & Rain 2']) != -1) {
					wtrlevel.prop("disabled", false);
				}

				chartLinks.buttonset();

				table.on('click', tblLinkHandler);
				rain.on('click', rnLinkHandler);
			},
			onTableLinkClicked : function(fn) {
				tblLinkHandler = fn;
			},
			onRainLinkClicked : function(fn) {
				rnLinkHandler = fn;
			},
			triggerSelected : function() {
				var checkedEl = chartLinks.children(":checked");
				if (checkedEl != null) {
					var elID = checkedEl.attr('id');
					var elDisbaled = checkedEl.prop('disabled');
					console.log(elID + " " + elDisbaled);

					if (elDisbaled == false) {
						switch (elID) {
						case 'table':
							tblLinkHandler();
							break;
						case 'rain':
							rnLinkHandler();
							break;
						}
					} else {
						console.log("Not available");
					}

					
				}
			}
		};
	});

	var DateOption = (function(e) {
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
			getDate : function() {
				return date;
			},
			setDate : function(sd) {
				$(dtpicker).datepicker( "setDate", sd );
				date = sd;
			},
			onDateChanged : function(fn) {
				dateChangeHandler = fn;
			}
		}
	});

	var ViewStateDialog = (function() {
		var dialog;
		var device;
		var chartInfo;
		var chartLinks;
		var startDateOption;
		var dataLoader;
		var deviceView;

		return {

			Initialized : false,

			Init : function(d) {
				dialog = $(document.getElementById(d));
				var that = this;
				$(dialog).dialog({
					resizable: false,
					height:'auto',
					width:'auto',
					autoOpen: false,
					open: function(ev, ui) {
						that.CenterMe();

					},
					resize: function(ev, ui) {
						that.CenterMe();

					}
				});

				chartInfo = ChartInfo('chart-info');
				chartLinks = ChartLinks('chart-links');
				deviceView = DeviceView('chart-div');
				deviceView.Empty();
				deviceView.SetView(DeviceView.VIEWS.TABLE);
				chartLinks.onTableLinkClicked(function() {
					console.log("Table link clicked");
					deviceView.SetView(DeviceView.VIEWS.TABLE);
					if (deviceView.GetData() !== undefined) {
						deviceView.DrawView();
					}
				});
				chartLinks.onRainLinkClicked(function() {
					console.log("Rain link clicked");
					deviceView.SetView(DeviceView.VIEWS.RAIN);
					if (deviceView.GetData() !== undefined) {
						deviceView.DrawView();
					}
				});
				startDateOption = DateOption('sdate');
				startDateOption.setDate(SDATE);
				startDateOption.onDateChanged(function(d) {
					console.log("Date Changed to " + d + " " + startDateOption.getDate());
					that.LoadData();
				});

				this.Initialized = true;
			},

			SetDevID : function(dev_id) {
				device = search(devices, 'dev_id', dev_id);
				var title1;
				var title2;

				if (device != null) {
					title1 = device['location_name'];
					title2 = device['municipality_name'] + " - " + device['province_name'];
				} else {
					title1 = "Unknown";
					title2 = "Unknown";
				}

				chartInfo.setTitle(title1, title2);
				chartLinks.setDeviceType(device['type_name']);
			},

			Show : function() {
				$(dialog).dialog('open');
			},

			CenterMe : function() {
				dialog.dialog( "option", "position", { my: "center center", at: "center center", of: window } );
			},

			LoadData : function() {
				console.log(startDateOption.getDate());
				deviceView.ResetData();
				chartLinks.triggerSelected();
				var that = this;
				console.log(deviceView.GetData());
				dataLoader = DataLoader(device['dev_id'], startDateOption.getDate(), 
				function(data) {
					console.log('success');
					console.log(data);
					deviceView.SetData(data);
					deviceView.DrawView();
					that.CenterMe();
				}, function() {
					console.log('fail');
					deviceView.OnFail(function(){
						console.log("Retrying");
						that.LoadData();
					});
				}, function() {
					console.log('before send');
					deviceView.Empty();
					deviceView.SetOnLoadAnim();
					that.CenterMe();
				});
			}
		}
	})();

		

    google.charts.load('44', {packages: ['table', 'corechart']});
    google.charts.setOnLoadCallback(function() {
    	$(document).ready(function() {
	      	initMap("map-canvas");
	      	initMapLegends('legends');
	      	initMarkers();
	      	initControls('controls');
	      	initControls2('controls2');
   		});
    });


	function initMap(divcanvas) {
		var DOST_CENTER = new google.maps.LatLng(10.712317, 122.562362); //DOST CENTER
	
		var mapOptions = {
  			//zoom: 6, //Whole Philippines View
  			zoom: 8, //Region 6 Focus,
  			minZoom:8,
  			maxZoom:null,
  			center: DOST_CENTER,
  			disableDefaultUI: true,
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.LARGE,
				position: google.maps.ControlPosition.RIGHT_CENTER
			},
  			//draggableCursor:'crosshair'
		}
		
		devices_map = new google.maps.Map(document.getElementById(divcanvas), mapOptions);
		
		// Bounds for region xi
        var strictBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(9.1895  , 119.1193), 
            new google.maps.LatLng(12.2171, 125.9308)
         );

        lastValidCenter = devices_map.getCenter();

        // Listen for the dragend event
        google.maps.event.addListener(devices_map, 'idle', function() {
            var minLat = strictBounds.getSouthWest().lat();
            var minLon = strictBounds.getSouthWest().lng();
            var maxLat = strictBounds.getNorthEast().lat();
            var maxLon = strictBounds.getNorthEast().lng();
            var cBounds  =devices_map.getBounds();
            var cMinLat = cBounds.getSouthWest().lat();
            var cMinLon = cBounds.getSouthWest().lng();
            var cMaxLat = cBounds.getNorthEast().lat();
            var cMaxLon = cBounds.getNorthEast().lng();
            var centerLat = devices_map.getCenter().lat();
            var centerLon = devices_map.getCenter().lng();

            if((cMaxLat - cMinLat > maxLat - minLat) || (cMaxLon - cMinLon > maxLon - minLon)) {
               //We can't position the canvas to strict borders with a current zoom level
                //devices_map.setZoom(devices_map.getZoom()+1);
                return;
            }
            if(cMinLat < minLat)
                var newCenterLat = minLat + ((cMaxLat-cMinLat) / 2);
            else if(cMaxLat > maxLat)
                var newCenterLat = maxLat - ((cMaxLat-cMinLat) / 2);
            else
                var newCenterLat = centerLat;
            if(cMinLon < minLon)
                var newCenterLon = minLon + ((cMaxLon-cMinLon) / 2);
            else if(cMaxLon > maxLon)
                var newCenterLon = maxLon - ((cMaxLon-cMinLon) / 2);
            else
                var newCenterLon = centerLon;

            if(newCenterLat != centerLat || newCenterLon != centerLon)
                //devices_map.setCenter(new google.maps.LatLng(newCenterLat, newCenterLon));
        		devices_map.panTo(new google.maps.LatLng(newCenterLat, newCenterLon));
        });

        google.maps.event.addListener(devices_map, 'click', function (event) {
            var pnt = event.latLng;
            var lat = pnt.lat();
	        lat = lat.toFixed(6);
	        var lng = pnt.lng();
	        lng = lng.toFixed(6);
	        console.log("Latitude: " + lat + "  Longitude: " + lng);               
	    });
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
		
		$('<button id="togglelegends">Hide Legend</button>')
			.on('click', function() {
				$('.legend').toggle();
                if ($(this).text() == "Show Legend") {
                    $(this).text('Hide Legend');
                } else {
                    $(this).text('Show Legend');
                }
			})
			.appendTo(legendscontainer);
        $('<div class="legendtitle">Devices in Western Visayas</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+MARKERS[0].src+'.png" > Automatic Rain Gauge</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+MARKERS[1].src+'.png" > Automatic Rain Gauge w/ Air Pressure</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+MARKERS[2].src+'.png" > Waterlevel</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+MARKERS[3].src+'.png" > Waterlevel w/ Automatic Rain Gauge</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="'+MARKERS[4].src+'.png" > VAISALA, UAAWS, or BSWM_Lufft</div class="legend">').appendTo(legendscontainer);
		$('<div class="legend"><img src="images/overlay_notok.png" > Status Not Ok</div>').appendTo(legendscontainer);
	}

	function initControls(container) {
		controlscontainer = $(document.getElementById(container));
		devices_map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(document.getElementById(container));
		$('<button id="showall">Show All</button>')
			.on('click', function() {
				showMarkerWithStatusId('all');
			})
			.appendTo(controlscontainer);
		$('<button id="showall">Show only OK</button>')
			.on('click', function() {
				showMarkerWithStatusId('ok');
			})
			.appendTo(controlscontainer);
		$('<button id="showall">Show only NOT OK</button>')
			.on('click', function() {
				showMarkerWithStatusId('notok');
			})
			.appendTo(controlscontainer);
	}

	function initControls2(container) {
		controlscontainer = $(document.getElementById(container));
		devices_map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById(container));

		var MapMode = controlscontainer.children("#map-mode");

		MapMode.on("change", function(data) {
			var opt = $(this).val();

			if (opt === MAP_MODES.SWITCH_STATUS) {
				if (OpenUnlockSwitchStatusDialog(this)) {
					//VALID INPUT LET AJAX CALLBACK DECIDE
				} else {
					console.log("FALSE");
					$(this).val(MAP_MODES.VIEW_DATA);
					CURRENT_MODE = MAP_MODES.VIEW_DATA;
				}

			} else {
				CURRENT_MODE = opt;
			}
			
			console.log(CURRENT_MODE);

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
			}).done(function(data) {
				if (data['success']) {
					//$(select).val(MAP_MODES.SWITCH_STATUS);
					CURRENT_MODE = MAP_MODES.SWITCH_STATUS;
				} else {
					$(select).val(MAP_MODES.VIEW_DATA);
					CURRENT_MODE = MAP_MODES.VIEW_DATA;
					alert('Sorry. Wrong passphrase.');
				}
			}).fail(function(f, n){
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
		var type = device['type_name'];
		var status_id = device['status_id'];
		var title = device['municipality_name'] + ' - ' + device['location_name'];

		var image = createIcon(type, status_id);
		var pos = new google.maps.LatLng( posx, posy);
		
		var marker = new google.maps.Marker({
   			position: pos,
			icon: image,
   			title:title + " (" + device_id + ")",
   			dev_id: device_id,
   			type: type}
		);

		console.log(title);
		attachMarkerClickEvent(marker, device_id, status_id);

		return marker;
	}

	function createIcon(type, status_id) {
		var marker_url = 'http://maps.google.com/mapfiles/ms/icons/red-dot.png';

		var obj = search(MARKERS, 'name', type);
		if (obj != null) {
			if (status_id == null || status_id == '0') {
				marker_url =  obj['src'] +'.png';
			} else if (status_id == '1') {
				marker_url = obj['src'] +'-notok.png';
			} 
		} else {
			console.log('no icon for ' + type);
		}

		//console.log(marker_url);
		var image = {
   			url: marker_url,
   			size: new google.maps.Size(32, 37),
   			origin: new google.maps.Point(0,0),
   			anchor: new google.maps.Point(16, 37)
   		};
   		return image;
	}
	
	function addMarkerToMap(marker) {
		marker.setMap(devices_map);
		devices_map_markers.push(marker);
	}

	function attachMarkerClickEvent(marker, dev_id, status_id) {
		google.maps.event.addListener(marker, 'click', function() {
			if (CURRENT_MODE === MAP_MODES.SWITCH_STATUS) {
				console.log('Switching Status');
				var newstatus_id;
				if (status_id == null || status_id == '0') {
					newstatus_id = '1';
				} else if(status_id == '1') {
					newstatus_id = '0';
				} else {
					newstatus_id = null;
				}

				postUpdateDeviceStatus(dev_id, newstatus_id);
			} else if (CURRENT_MODE === MAP_MODES.VIEW_DATA){
				console.log('Viewing Data');
				if (!ViewStateDialog.Initialized)  {
					ViewStateDialog.Init('view-data-dialog');
				} 

				ViewStateDialog.SetDevID(dev_id);
				ViewStateDialog.Show();	
				ViewStateDialog.LoadData();
			}
		});
	}

	function showMarkerWithStatusId(option) {
		for (var i=0;i<devices_map_markers.length;i++) {
			if (option == 'all') {
				devices_map_markers[i].setMap(devices_map);
			} else {
				device_marker =  devices_map_markers[i];
				device_id = device_marker['dev_id'];
				device = search(devices, 'dev_id', device_id);
				device_status_id = null;

				if (device != null) {
					device_status_id = device['status_id'];
					switch (option) {
						case 'ok':
							if (device_status_id == null || device_status_id == 0) {
								device_marker.setMap(devices_map);
							} else {
								device_marker.setMap(null);
							}
							break;
						case 'notok':
							if (device_status_id == 1) {
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



	function postUpdateDeviceStatus(dev_id, status_id) {
		$.ajax({
			url: DOCUMENT_ROOT + 'update.php',
			type: "POST",
			data: {dev_id: dev_id,
		  		status_id: status_id,
				tryauth: TRYAUTH
		  	},
			dataType: 'json',
			})
			.done(onSuccessPostUpdate)
			.fail(onFailPostUpdate);
	}

	function onSuccessPostUpdate(data) {
		var device_id = data['dev_id'];
		var status_id = data['status_id'];

		var device_marker = search(devices_map_markers, 'dev_id', device_id);
		if (device_marker != null) {
			var type = device_marker['type'];

			var image = createIcon(type, status_id);
			device_marker.setIcon(image);
			google.maps.event.clearListeners(device_marker, 'click');
			attachMarkerClickEvent(device_marker, device_id, status_id);
		}

		var device = search(devices, 'dev_id', device_id);
		if (device != null) {
			device['status_id'] = status_id;
		}


	}

	function onFailPostUpdate(data) {
		console.log('POST fail');
	}

	function search(o, key, val) {
		for (var i=0; i<o.length;i++) {
			if (o[i][key] == val) {
				return o[i];
			}
		}
		return null;
	}	

	function drawRain(data, chartdiv) {
		console.log("drawing table");

		var chartDiv = $(document.getElementById("chartdiv"));
		chartDiv.empty();
		chartDiv.css({'background-image': 'url(images/bp-logo.png)'});
	}

	function drawWaterLevel(data, chartdiv) {
		console.log("drawing waterlevel");

		var chartDiv = $(document.getElementById("chartdiv"));
		chartDiv.empty();
		chartDiv.css({'background-image': 'url(images/bp-logo.png)'});
	}

	function drawTemperature(data, chartdiv) {
		console.log("drawing temperature");

		var chartDiv = $(document.getElementById("chartdiv"));
		chartDiv.empty();
		chartDiv.css({'background-image': 'url(images/bp-logo.png)'});
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
			<li ><a href="index.php">Home</a></li>
			<li><a href="rainfall.php">Rainfall Monitoring</a></li>
			<li><a href="waterlevel.php">Waterlevel Monitoring</a></li>
			<li><a href="waterlevel2.php">Waterlevel Map</a></li>
			<li><a href="#"  class='currentPage'>Devices Monitoring</a></li>
		</ul>
	</div>
	</div>
		
  	
</div>
<div id='content'>
	<div id='map-canvas'>
	</div>
	<div id='legends'>
	</div>
	<div id='controls'>
	</div>
	<div id='controls2'>
		<label for="map-mode">Map Mode</label>
	    <select name="map-mode" id="map-mode">
	      <option value="0" selected="selected">View Data</option>
	      <option value="1">Switch Status</option>
	    </select>
	</div>
	<div id="view-data-dialog" style="display:none">
		<input type="hidden" autofocus="autofocus" />
		<div id="chart-info">
			<h3 id="title2"></h3>
			<h5 id="title1"></h5>
		</div>
		<div id="chart-links">
			<input type="radio" id="table" name="chart-type" checked="checked"><label for="table">Table</label>
			<input type="radio" id="rain" name="chart-type"><label for="rain">Rain</label>
    		<input type="radio" id="waterlevel" name="chart-type"><label for="waterlevel">Water Level</label>
    		<input type="radio" id="temperature" name="chart-type"><label for="temperature">Temperature</label>
		</div>
		<div id="date-options">
		<label for="sdate">Start Date:</label>
		<input type="text" id="sdate"></p>
		<div id="chart-div"></div>
		</div>
	</div>
</div>
<div id='footer'>
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
var devices = <?php echo json_encode(Devices::getAllDevices());?>;
</script>
</html>