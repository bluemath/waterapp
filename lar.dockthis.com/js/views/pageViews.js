
var PageView = Backbone.View.extend({
	el: "#page",
	initialize: function() {
		// Render when the app changes
		this.listenTo(App.State, 'change:currentpage', this.renderPage);
		this.listenTo(App.State, 'change:currenttopic', this.renderTopic);
	},
	renderPage: function() {
		
		// Get the current page model
		this.currentPageModel = App.State.get('currentpage');
		
		// Every page has intro text
		new TextView({ model: this.currentPageModel, el: '#page .intro' });
		
		// Clear the page
		$('.spread').empty();
		$('.topic .control').empty();
		$('.topic .detail').empty();
		
		// Remove exisiting Page View
		if(App.State.pageView != undefined) {
			App.State.pageView.remove();
		}
		
		// Load the view for the appropriate page type
		var type = this.currentPageModel.get('type');
		switch(type) {
			case "DataExplorer":
				App.State.pageView = new DataPageView({ model: this.currentPageModel });
				break;
			case "Photos":
				App.State.pageView = new PhotosPageView({ model: this.currentPageModel });
				break;
			default:
				App.State.set('currentpage', undefined);
				return this;
		}
		
		return this;
	},
	
	renderTopic: function() {
		this.currentTopicModel = App.State.get('currenttopic');
		
		// Every topic has text
		new TextView({ model: this.currentTopicModel, el: '.topic .text' });
	}
});

var TextView = Backbone.View.extend({
	initialize: function() {
		this.render();
	},
	render: function() {
		this.$el.empty();
		text = this.model.get('text');
		if(text != undefined) {
			this.$el.html($("<p>").html(this.model.get('text')));
		}
		return this;
	}
});

var DataPageView = Backbone.View.extend({
	
	initialize: function() {
		
		debug("DataPageView Init");
		
		this.model.set('selectedsites', new Sites());
		
		this.listenTo(App.State, 'change:currenttopic', this.changeTopic);
		this.listenTo(this.model, 'change:selectedsites', this.updateSites);
		
		this.render();

	},
	render: function() {
		this.loadMap();
		this.loadMapSites();
		this.loadChart();
	},
	
	loadMap: function() {
		// Load Map
		var spread = $('.spread');
		var mapDiv = $("<div>").addClass('map');
		spread.append(mapDiv);
		this.map = MapSpread(mapDiv[0], this.model.get('sites'));
	},
	
	loadMapSites: function() {
		// Get Sites
		var sites = this.model.get('sites');
		
		// Dark to light, from colorbrewer
		var colors = ['#021735','#08306b', '#08519c', '#2171b5', '#4292c6', '#6baed6', '#9ecae1', '#c6dbef', '#deebf7'];

		sites.each(function(site) {
			site.set('color', colors.shift());
			
			var code = site.get('sitecode');
			var name = site.get('sitename');
			var latitude = site.get('latitude');
			var longitude = site.get('longitude');
			
			this.map.addOverlay(new ol.Overlay({
			  position: ol.proj.transform(
			    [longitude, latitude],
			    'EPSG:4326',
			    'EPSG:3857'
			  ),
			  element: $("<div id='" + code + "' class='markercontainer'><div class='marker-left'></div><div class='marker'>" + name + "</div></div>"),
			  positioning: 'center-left',
			}));
			
			$("#"+code).fadeTo(200,.8);
			
			var that = this;
			
			(function(site) {
				$("#"+code).click(function() {
					that.model.set("selectedsite", site);
					that.updateSites();
				});
			})(site);
			
		}, this);
		
	},
	
	loadChart: function() {
		// Setup Chart
		var detail = $('.topic .detail');
		var chartDiv = $("<div>").addClass('chart');
		detail.append(chartDiv);
		
		sites = this.model.get("sites");
		console.log(sites);
		variables = this.model.get("variables");
		console.log(variables);
		this.chart = new Chart(chartDiv[0], sites, variables);
	},
	
	updateSites: function() {

		var site = this.model.get("selectedsite");
		
		debug('clicked site ' + site.get("sitename"));
		
		var selectedSites = this.model.get("selectedsites");
		
		if(App.State.get("currenttopic").get("sites") == "ONE") {
			// Toggle mode
			selectedSites.reset(site);
		} else {
			// Set of sites mode
			if(selectedSites.contains(site)) selectedSites.remove(site);
			else selectedSites.add(site);
			
			// Must have at least one site, so add it back if empty
			if(selectedSites.length == 0) selectedSites.add(site);
		}
		
		this.updateMapAndChart();
	},
	
	changeTopic: function() {
		debug('change topic to ' + App.State.get("currenttopic").get("name"));
		
		var topic = App.State.get("currenttopic");
		var selectedSite = this.model.get("selectedsite");
		var selectedSites = this.model.get("selectedsites");
		var allSites = this.model.get('sites');
		
		// Prevent too many sites from being visible
		if(topic.get('sites') == "ONE") {
			if(selectedSites.length == 0) {
				// Default site
				selectedSites.reset(allSites.first());
			} else {
				// Most recently selected site
				selectedSites.reset(selectedSite);
			}
		}
		
		// Center map
		this.map.recenter();
		
		this.updateMapAndChart();
	},
	
	updateMapAndChart: function() {
		
		var allSites = this.model.get('sites');
		var selectedSites = this.model.get("selectedsites");
		
		// Properly shade the sites
		allSites.each(function(site) {
			var code = site.get("sitecode");
			var color = site.get("color");
			if(selectedSites.contains(site)) {
				// Activate
				$("#"+code+" .marker").css('background-color', color);
				$("#"+code+" .marker-left").css('border-right-color', color);
				$("#"+code).fadeTo(0, 1);
			} else {
				// Deactivate
				$("#"+code).fadeTo(0,.8);
				$("#"+code+" .marker").css('background-color', '');
				$("#"+code+" .marker-left").css('border-right-color', '');
			}
		});
		
		// Send the correct series to the chart
		var topic = App.State.get("currenttopic");
		this.chart.update(topic, selectedSites);
		
	}
	
});

var PhotosPageView = Backbone.View.extend({
	initialize: function() {
		// Clear Spread
		var spread = $('.spread').empty();
	},
	render: function() {
		
	}
});