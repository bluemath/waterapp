var DE = {};

// Modes: site, variable, pair
// Site shows 		all variables 	for one site 		selected from the map
// Variable shows 	one variable	for multiple sites 	selected from the map
// Pairs shows 		two variables 	for one site 		selected from the map
DE.mode = {	ONESITE: 1,
			MANYSITES: 2 }

DE.create = function(sites, variables, topics) {

	var result = {};
		
	// Dark to light, from colorbrewer
	var colors = ['#021735','#08306b', '#08519c', '#2171b5', '#4292c6', '#6baed6', '#9ecae1', '#c6dbef', '#deebf7'];
	
	// Order sites by latitude (highest to lowest)
	sites.sort(function(a,b) {
		return a.latitude - b.latitude;
	});
	
	// Assign colors to sites
	for (site in sites) {
		site = sites[site];
		site.color = colors.shift();
	}
		
	$( document ).ready(function() {
	
		// Make map
		result.map = Map('rbcmap', sites);
	
		// Add markers to the map
		for (site in sites) {
			site = sites[site];
			
			var camera = (site.camera) ? '' : "<i class='fa fa-video-camera'></i>";
			
			result.map.addOverlay(new ol.Overlay({
			  position: ol.proj.transform(
			    [site.longitude, site.latitude],
			    'EPSG:4326',
			    'EPSG:3857'
			  ),
			  element: $("<div id='" + site.code + "'><div class='marker-left'></div><div class='marker'>" + site.name + camera + "</div></div>"),
			  positioning: 'center-left',
			}));
			$("#"+site.code).fadeTo(200,.6);
			$("#"+site.code+" .marker").css('background-color', site.color);
			$("#"+site.code+" .marker-left").css('border-right-color', site.color);
			
			$("#"+site.code).click(function() {
				$(this).fadeTo(200,1);
				$(this).children('.marker').addClass('on');
			});

		}
	
		// Make chart
		result.chart = Chart('chart');
		
		// Add topics to menu
		for (topic in topics) {
			topic = topics[topic];
			console.log(topic.name);
			$(".topic .dropdown").append($('<div>' + topic.name + '</div>'));
		}
		
		var topicChooserTimeout;
		$(".topic .chooser").click(function() {
			$(".topic .dropdown").slideToggle(100);
			
			// Set Timeout to hide topic chooser
			clearTimeout(topicChooserTimeout);
			topicChooserTimeout = setTimeout(function() {
				$(".topic .dropdown").slideUp(100);
			}, longtimeout);
		})
		
	
	});
	
	return result;
};