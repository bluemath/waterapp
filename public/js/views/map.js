function MapSpread(element, sites, zoom) {

	function transform(extent) {
		return ol.proj.transformExtent(extent, 'EPSG:4326', 'EPSG:3857');
	}
	
	var terrainLayer = new ol.layer.Tile({
		source: new ol.source.Stamen({
			layer: 'terrain-background'
		}),
		opacity: 1.0
	});
	
	var linesLayer = new ol.layer.Tile({
		source: new ol.source.Stamen({
			layer: 'terrain-lines'
		}),
		opacity: .25
	});
	
	
/*
	// Fill this with things to highlight (like the river)
	var geoJSON = { };
	// To highlight the river...
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
*/
	
	// Using the locations of the sites, calculate center and extent for map
	var lats = [];
	var lons = [];
	
	sites.each(function(site) {
		lats.push(site.get('latitude'));
		lons.push(site.get('longitude'));
	}, this);
	
	var minLat = Math.min.apply(null, lats);
	var maxLat = Math.max.apply(null, lats);
	var minLon = Math.min.apply(null, lons);
	var maxLon = Math.max.apply(null, lons);
	
	var maxExtent = ol.proj.transformExtent([minLon, minLat, maxLon, maxLat], 'EPSG:4326', 'EPSG:3857');
	var center = ol.proj.transform([maxLon - (maxLon - minLon) * .3, maxLat - (maxLat - minLat) * .95],'EPSG:4326','EPSG:3857');
	
	// If sites was empty, this will fail because center and extent will be invalid
	
	// Setup map
	var map = new ol.Map({
		target: element,
		layers: [terrainLayer, linesLayer], // // Removed for a cleaner image
		view: new ol.View({
			center: center,
			minZoom: zoom,
			maxZoom: 15,
			zoom: zoom,
			enableRotation: false,
			extent: maxExtent
		}),
		pixelRatio: 1 // Significantly improves iPad performance
	});
	
	map.updateSize();
	
	window.onorientationchange = function() {
		// Can't do this directly because Chrome on Android delays updating the size...
		setTimeout(function() {
			map.updateSize();
		}, 10);
		
	};
	
	map.recenter = function() {
		var pan = ol.animation.pan({
		    duration: 400,
		    source: (map.getView().getCenter())
		});
		map.beforeRender(pan);
		map.getView().setCenter(center);
		map.getView().setZoom(zoom);
	};
	
	return map;
}
