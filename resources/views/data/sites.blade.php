@extends('app')

@section('content')
	<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="http://openlayers.org/en/v3.7.0/build/ol.js" type="text/javascript"></script>
	<style>
		@font-face {
			font-family: "reykjavikone";
			src: url("/fonts/reykjavikoneagauge-webfont.woff") format('woff');
			font-weight: normal;
		}
		@font-face {
			font-family: "reykjavikone";
			src: url("/fonts/reykjavikonebgauge-webfont.woff") format('woff');
			font-weight: bold;
		}
		@font-face {
			font-family: "reykjavikone";
			src: url("/fonts/reykjavikonecgauge-webfont.woff") format('woff');
			font-weight: 700;
		}
		@font-face {
			font-family: "reykjavikone";
			src: url("/fonts/reykjavikonedgauge-webfont.woff") format('woff');
			font-weight: 900;
		}
		
		body {
			font-family: 'reykjavikone';
			background-color: #ffffff;
			color: #333;
			margin: 0;
		}
		
		h1 {
			margin: 5px 10px;
		}
		
		.map {
			height: 100%;
			width: 100%;
		}
		
		.marker {
			background-color: #2f5266;
			color: #ffffff;
			padding: 0px 6px;
			overflow: hidden;
			white-space: nowrap;
			font-size: 20px;
			padding-right:10px;
			line-height: 30px;
			box-shadow: 1px 1px 6px 1px rgba(0, 0, 0, 0.5);
			border-top-right-radius: 7px;
			border-bottom-right-radius: 7px;
			border-top-left-radius: 3px;
			border-bottom-left-radius: 3px;
			
			margin-left: 14px;
		}
		
		.marker:before {
			content: '';
			display:block;
			width: 0; 
			height: 0; 
			border-top: 15px solid transparent;
			border-bottom: 15px solid transparent; 
			border-right: 15px solid #2f5266; 
			position: absolute;
			top: 0px;
			left: 0px;
		}
		
		a {
			text-decoration: none;
		}


	</style>
	
	<div id="map" class="map"></div>
	
	<script type="text/javascript">

		var geoJSON = { };


		function transform(extent) {
			return ol.proj.transformExtent(extent, 'EPSG:4326', 'EPSG:3857');
		}

		
		var satLayer = new ol.layer.Tile({
			source: new ol.source.TileArcGISRest({
				url: 'http://mapserv.utah.gov/arcgis/rest/services/AerialPhotography_Color/HRO2012Color6Inch_4Band/ImageServer'
			}),
			opacity: .5
		});
		
		var hillshadeLayer = new ol.layer.Tile({
			source: new ol.source.TileArcGISRest({
				url: 'http://mapserv.utah.gov/arcgis/rest/services/BaseMaps/Hillshade/MapServer'
			})
		});
		
		var liteLayer = new ol.layer.Tile({
			source: new ol.source.TileArcGISRest({
				url: 'http://mapserv.utah.gov/arcgis/rest/services/BaseMaps/Lite/MapServer'
			})
		});
		
		var terrainLayer = new ol.layer.Tile({
			source: new ol.source.TileArcGISRest({
				url: 'http://mapserv.utah.gov/arcgis/rest/services/BaseMaps/Terrain/MapServer'
			})
		});
		
		var linesLayer = new ol.layer.Tile({
			source: new ol.source.Stamen({
				layer: 'terrain-lines'
			})
		});
		
		var terrainLayer = new ol.layer.Tile({
			source: new ol.source.Stamen({
				layer: 'terrain-background'
			}),
		});
		
		var riverLayer = new ol.layer.Vector({
			source: new ol.source.Vector({
				features: (new ol.format.GeoJSON()).readFeatures(geoJSON, {
					dataProjection: 'EPSG:4326',
					featureProjection: 'EPSG:3857'
				}),
			}),
			projection: 'EPSG:4326',
			style: new ol.style.Style({
			    stroke: new ol.style.Stroke({
			      color: '#92d0dc',
			      width: 4
				}),
				fill: new ol.style.Fill({
			      color: '#92d0dc',
				})
			})
		});
		
		
		var lats = [];
		var lons = [];
		
		@foreach ($sites as $site)
		
			lats.push({{$site->latitude}});
			lons.push({{$site->longitude}});
		
		@endforeach
		
		var minLat = Math.min.apply(null, lats);
		var maxLat = Math.max.apply(null, lats);
		var minLon = Math.min.apply(null, lons);
		var maxLon = Math.max.apply(null, lons);
		
		var maxExtent = ol.proj.transformExtent([minLon, minLat, maxLon, maxLat], 'EPSG:4326', 'EPSG:3857');
		
		var center = ol.proj.transform([(minLon + maxLon)/2, (minLat + maxLat)/2],'EPSG:4326','EPSG:3857');
		
		// Setup map
		var map = new ol.Map({
			target: 'map',
			layers: [terrainLayer, linesLayer],
			view: new ol.View({
				center: center,
				minZoom: 9,
				maxZoom: 15,
				zoom: 9,
				enableRotation: false,
				extent: maxExtent
			})
		});
		
		// Add sites
		@foreach ($sites as $site)
		
			name = '{{$site->sitename}}';
			
			// Remove common prefix / suffix to clean up map
			
			// All sites
			name = name.replace('Basic Aquatic', '');
			name = name.replace('Advanced Aquatic', '');
			
			// Provo River
			name = name.replace('Provo River at ', '');
			name = name.replace('Provo River near ', '');
			name = name.replace('Provo River Below ', '');
			
			// Logan River
			name = name.replace('Logan River at ', '');
			name = name.replace('Logan River near ', '');
		
			map.addOverlay(new ol.Overlay({
			  position: ol.proj.transform(
			    [{{$site->longitude}}, {{$site->latitude}}],
			    'EPSG:4326',
			    'EPSG:3857'
			  ),
			  element: $('<a href="{{ action('DataController@series', [$site->sitecode] )}}"><div class="marker">'+name+'</div></a>'),
			  positioning: 'center-left'
			}));
			
		@endforeach
		
		
    </script>
@stop