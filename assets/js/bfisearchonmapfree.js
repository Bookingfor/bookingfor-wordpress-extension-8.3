		var bfiMapMapDrawer;
		var bfiMyLatlngMapDrawer;
		var bfiDrawingManager;
		var bfiSelectedShape;
		var bfiCurrDialog;
		var bfiCurrXGooglePos;
		var bfiCurrYGooglePos;
		var $googlemapsapykey;
		var bfiCurrStartzoom = 12;
		var bfiCurrInizializated = 0;
		var bfiCurrLatLngbounds;
		var bfiCurrTitlePopupMap = "";
		
		var bfiCurrForm;
		var bfiCurrPoint;
		var bfiCurrMapDrawer;
		var bfiCurrMapCanvas;
		var bfiCurrMapCanvas;
		var bfiCurrBtndelete;
		var bfiCurrBtnconfirm;
		var bfiCurrbtnCompleta;
		var bfiCurrAddresssearch;
		var bfiCurrSpanArea;
		var bfiCurrDrawpoligon;
		var bfiCurrDrawcircle;

		// drawing setting
		var polyOptions = {
			strokeWeight: 0,
			fillOpacity: 0.45,
		};
	
		var Leaflet;
		Leaflet = L.noConflict();

		// make map
		function handleApiReadyMapDrawer() {

			bfiMapMapDrawer = Leaflet.map(bfiCurrMapCanvas.attr('id')).setView([bfi_variables.bfi_mapx,bfi_variables.bfi_mapy],  bfi_variables.bfi_mapstartzoom);
			var OpenStreetMap_Mapnik = Leaflet.tileLayer(bfi_variables.bfi_freemaptileurl, {
				maxZoom: 19,
				attribution: bfi_variables.bfi_freemaptileattribution
			});	
			OpenStreetMap_Mapnik.addTo(bfiMapMapDrawer);
//								const freeDraw = window.freeDraw = new FreeDraw({ 
//									mode: FreeDraw.ALL
//									});
			var drawnItems = new L.FeatureGroup();
			 bfiMapMapDrawer.addLayer(drawnItems);
			 var drawControl = new L.Control.Draw({
				 draw: {
					 circlemarker: false,
					 rectangle: false,
					 polyline: false,
					 marker: false
				 },
//				 edit: {
//					 featureGroup: drawnItems
//				 }
			});
			bfiMapMapDrawer.addControl(drawControl);

//			var info = L.control();
//			info.onAdd = function (bfiMapMapDrawer) {
//				this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
//				this.update();
//				return this._div;
//			};
//			// method that we will use to update the control based on feature properties passed
//			info.update = function () {
//				this._div.innerHTML = jQuery(".bfi-map-tooltip").html() ;
//			};
//			info.addTo(bfiMapMapDrawer);

			bfiMapMapDrawer.on('draw:editstop', function(evt) {
				bfi_consolelog("draw:editstop ");
			});
			bfiMapMapDrawer.on('draw:edited', function(evt) {
				bfi_consolelog("draw:edited ");
			});
			bfiMapMapDrawer.on('draw:editresize', function(evt) {
				bfi_consolelog("draw:editresize");
				calculateArea();
				bfiCurrbtnCompleta.show();
			});
			bfiMapMapDrawer.on('draw:drawstop', function(evt) {
				bfi_consolelog("draw:drawstop");
			});
			bfiMapMapDrawer.on('draw:editmove', function(evt) {
				bfi_consolelog("draw:editmove");
				calculateArea();
				bfiCurrbtnCompleta.show();
			});
			bfiMapMapDrawer.on('draw:editvertex', function(evt) {
				bfi_consolelog("draw:editvertex");
				calculateArea();
				bfiCurrbtnCompleta.show();
			});
			bfiMapMapDrawer.on('draw:editstart', function(evt) {
				bfi_consolelog("draw:editstart");
			});
			bfiMapMapDrawer.on('draw:drawstart', function(evt) {
				if (bfiSelectedShape) {
					bfiMapMapDrawer.removeLayer(bfiSelectedShape.layer)
					bfiSelectedShape = null;
				}
			});
			 
			bfiMapMapDrawer.on('draw:created', function(evt) {
				var type = evt.layerType,
					layer = evt.layer;
				layer.editing.enable();
bfi_consolelog("draw:created");
bfi_consolelog(evt.layerType);
bfi_consolelog(evt.layer);
				drawnItems.addLayer(layer);
				setSelection(evt);
				calculateArea();
				bfiCurrbtnCompleta.show();
			});

			
//																// Add the FreeDraw layer.
//																bfiMapMapDrawer.addLayer(freeDraw);
//																freeDraw.on('markers', event => {
//																	if (event.eventType === 'create')
//																	{
//																		setSelection(event);
//																		bfi_consolelog(event.latLngs);
//																		bfiCurrbtnCompleta.show();
//																		calculateArea()
//																	}
//																});	
			jQuery(document).on('click tap', ".bfi-btndelete" , deleteSelectedShape);
			jQuery(document).on('click tap', ".bfi-btnconfirm" ,  function() {
				bfiCurrDialog.dialog('close');
			});			
			drawShape();

//---------------------------------------------------------
//	drawShape();
	//------------------------------------------------------------
//		if (typeof bfiCurrLatLngbounds !== 'undefined' && typeof bfiCurrLatLngbounds === 'object' ){
//			bfiMapMapDrawer.fitBounds(bfiCurrLatLngbounds);
//		}

		//bfiMapMapDrawer.fitBounds(bfiCurrLatLngbounds);
	}
	function drawShape(){
		if (bfiCurrInizializated==0)
		{

			if (bfiSelectedShape) {
				bfiMapMapDrawer.removeLayer(bfiSelectedShape.layer)
				bfiSelectedShape = null;
			}
			if(bfiCurrPoint[0].value.length > 0){
				var existingPoints =bfiCurrPoint.val();
				var typeShape = existingPoints.split("|");
				switch(typeShape[0]){
					case "0": // draw circle
						var coords= typeShape[1].split(" ");
						var layer = L.circle([coords[0], coords[1]], {radius: parseFloat(coords[2])});
						layer.editing.enable();
						layer.addTo(bfiMapMapDrawer);
						var newShape = { layer: layer, layerType: 'circle'};
						setSelection(newShape);
						//bfiMapMapDrawer.fitBounds(newShape.getBounds());
						bfiMapMapDrawer.fitBounds(layer.getBounds());
						bfiCurrbtnCompleta.show();
						calculateArea();
						break;
					case "1": //draw poligon
						var coords= typeShape[1].split(",");
						var pts=new Array();
						jQuery.each(coords,function(i,point){
							var singlecoord= point.split(" ");
							var singleLatLng= new L.LatLng(singlecoord[0], singlecoord[1]);
							pts.push(singleLatLng);
						});
						var layer = L.polygon(pts, {radius: parseFloat(coords[2])});
						layer.editing.enable();
						layer.addTo(bfiMapMapDrawer);
						var newShape = { layer: layer, layerType: 'polygon'};
						bfiMapMapDrawer.fitBounds(layer.getBounds());
						setSelection(newShape);
						bfiCurrbtnCompleta.show();
						calculateArea();

						break;
					default:
						drawPoligon();
						break;
				}
			}else{
				drawPoligon();
			}

		}else{
			drawPoligon();
		}

	}

	function calculateArea(){
		if (bfiSelectedShape) {
			if (bfiSelectedShape.layerType === 'circle') {
				var radius = bfiSelectedShape.layer.getRadius();
				var area = ( Math.round(radius*radius*Math.PI / 10000) / 100 );
				bfiCurrSpanArea.html("Km&sup2;: " + area);
			}
			if (bfiSelectedShape.layerType === 'polygon') {
				var path = bfiSelectedShape.layer.getLatLngs();
				var area = ( Math.round(L.GeometryUtil.geodesicArea(path[0]) / 10000) / 100 );//area will be in squareMeters by default
				bfi_consolelog("path");
				bfi_consolelog(path[0]);
				bfi_consolelog("area");
				bfi_consolelog(area);
				bfiCurrSpanArea.html("Km&sup2;: " + area);

			}
		}
	}

	function getShapePath() {
		if (bfiSelectedShape) {
			bfiCurrLatLngbounds = new Leaflet.latLngBounds();
			if (bfiSelectedShape.layerType === 'circle') {
				var circleCenter = bfiSelectedShape.layer.getLatLng();
				var radius = bfiSelectedShape.layer.getRadius();
				var circlepoints = "0|" + circleCenter.lat + " " + circleCenter.lng + " " + radius;
				bfiCurrPoint.val(circlepoints);
				bfiMapMapDrawer.fitBounds(bfiSelectedShape.layer.getBounds());
			}
			if (bfiSelectedShape.layerType === 'polygon') {
				var path =  bfiSelectedShape.layer.getLatLngs()[0];
				var pts=new Array();
				jQuery.each(path,function(i,point){
					pts.push(point.lat + " "+point.lng);
					bfiCurrLatLngbounds.extend(point);
				});
				pts.push(path[0].lat+" "+path[0].lng);
				var stringJoin=pts.join(",");
				bfiCurrPoint.val("1|" + stringJoin);
			}
			//jQuery("#zoneId").val("0");
			//jQuery("#locationZonesList").find("option").prop("selected", "");
			//jQuery("#locationzones").val("");
			//jQuery("#locationZonesList").find("option[value='-1']").prop("selected", "selected");
//			jQuery("#mapSearch").prop("checked", "checked");

			bfiCurrForm.find("[name=stateIds],[name=regionIds],[name=cityIds],[name=locationzone],[name=searchterm],[name=searchTermValue]").val("");
			bfiCurrForm.find("[name=searchType]").val("1");
			bfiCurrForm.find(".bfi-mapsearchbtn").addClass("bfi-alternative");
			bfiCurrForm.find(".bfi-mapsearchbtn").removeClass("bfi-alternative4");

		}else{
//			jQuery("#mapSearch").prop("checked", "");
			bfiCurrForm.find("[name=searchType]").val("0");
			bfiCurrForm.find(".bfi-mapsearchbtn").removeClass("bfi-alternative");
			bfiCurrForm.find(".bfi-mapsearchbtn").addClass("bfi-alternative4");
		}
	}

	function drawPoligon() {
		deleteSelectedShape();
//		jQuery(".bfi-select-figure").addClass("unactive");
//		bfiCurrDrawpoligon.removeClass("unactive");
		jQuery(".bfi-select-figure").addClass("bfi-alternative3");
		bfiCurrDrawpoligon.removeClass("bfi-alternative3");
//		bfiDrawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
//		bfiDrawingManager.setMap(bfiMapMapDrawer);
//		bfiDrawingManager.setOptions({
//			drawingControl: false
//		});
	}
	function drawCircle() {
		deleteSelectedShape();
//		jQuery(".bfi-select-figure").addClass("unactive");
//		bfiCurrDrawcircle.removeClass("unactive");
		jQuery(".bfi-select-figure").addClass("bfi-alternative3");
//		bfiCurrDrawcircle.removeClass("bfi-alternative3");
//		bfiDrawingManager.setDrawingMode(google.maps.drawing.OverlayType.CIRCLE);
//		bfiDrawingManager.setMap(bfiMapMapDrawer);
//		bfiDrawingManager.setOptions({
//			drawingControl: false
//		});
	}

	function clearSelection() {
		if (bfiSelectedShape) {
			bfiSelectedShape.layer.editing.disable();
			bfiSelectedShape = null;
			bfiCurrBtndelete.attr("disabled", "disabled");
			bfiCurrBtndelete.addClass("bfi-not-active");                              
		}
	}
	function setSelection(shape) {
//		var polygon = L.polygon(shape.latLngs, {color: 'red'}).addTo(bfiMapMapDrawer);
		clearSelection();
		bfiSelectedShape = shape;
//		shape.setEditable(true);
		bfiCurrBtndelete.removeAttr("disabled");                              
		bfiCurrBtndelete.removeClass("bfi-not-active");                              
	}
	
	function deleteSelectedShape() {
		if (bfiSelectedShape) {
			bfiMapMapDrawer.removeLayer(bfiSelectedShape.layer)
			bfiSelectedShape = null;
		}

		bfiCurrbtnCompleta.hide();

		bfiCurrBtndelete.attr("disabled", "disabled");
		bfiCurrBtndelete.addClass("bfi-not-active");                              

		bfiCurrSpanArea.html("");
		bfiCurrPoint.val("");
	}

		function bfiOpenGoogleMapDrawer(bfiCurrFormId, bfiCurrModID) {
			
			bfiCurrForm = jQuery("#"+bfiCurrFormId);
			bfiCurrPoint = bfiCurrForm.find("input[name='points']").first() ;
			bfiCurrMapDrawer = jQuery("#bfi_MapDrawer"+bfiCurrModID);
			bfiCurrMapCanvas = bfiCurrMapDrawer.find(".bfi-map-canvas").first() ;
			bfiCurrBtndelete = bfiCurrMapDrawer.find(".bfi-btndelete").first() ;
			bfiCurrBtnconfirm = bfiCurrMapDrawer.find(".bfi-btnconfirm").first() ;
			bfiCurrbtnCompleta = bfiCurrMapDrawer.find(".bfi-btnCompleta").first() ;
			bfiCurrAddresssearch = bfiCurrMapDrawer.find(".bfi-map-addresssearch").first() ;
			bfiCurrSpanArea = bfiCurrMapDrawer.find(".bfi-spanarea").first() ;
			bfiCurrDrawpoligon = bfiCurrMapDrawer.find(".bfi-drawpoligon").first() ;
			bfiCurrMapeditor = bfiCurrMapDrawer.find(".bfi-mapeditor").first() ;
			
			bfiCurrDrawcircle = bfiCurrMapDrawer.find(".bfi-drawcircle").first() ;
			bfiCurrAddresssearch.hide();
			bfiCurrMapeditor.hide();

			var width = jQuery(window).width()*0.9;
			var height = jQuery(window).height()*0.9;

			if (typeof bfiMapMapDrawer !== 'object'){
				var script = document.createElement("script");
				script.type = "text/javascript";
				script.src = bfi_variables.urlPlugin + "assets/js/leaflet/Leaflet.draw.js";
				document.body.appendChild(script);
				var cssstyle = document.createElement("style");
				cssstyle.type = "text/css";
				cssstyle.src = bfi_variables.urlPlugin + "assets/js/leaflet/leaflet.draw.css";
				document.body.appendChild(cssstyle);
			}
			if(bfiCurrMapCanvas.hasClass("ui-dialog-content") && bfiCurrMapCanvas.dialog("isOpen" )){
			
			}else{
				bfiCurrMapCanvas.css("height", height-130);
				bfiCurrDialog = bfiCurrMapDrawer.dialog({
						closeText: "",
						open: function( event, ui ) {
							if (typeof bfiMapMapDrawer !== 'object'){
				//					bfiCurrInizializated = 1;
									handleApiReadyMapDrawer();
							}else{
				//				drawShape();
							}
						},
						modal: true,
						resize: function( event, ui ) {
	//						bfiMapMapDrawer.setCenter(bfiMyLatlngMapDrawer);
						},
						height:height,
						width: width,
						fluid: true, //new option
						title: bfiCurrTitlePopupMap,
						dialogClass: 'bfi-dialog bfi-dialog-map',
						close: getShapePath
					});
			}


		}
