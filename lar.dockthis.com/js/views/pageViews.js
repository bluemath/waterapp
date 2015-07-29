
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
		
		// Load the view for the appropriate page type
		var type = this.currentPageModel.get('type');
		switch(type) {
			case "DataExplorer":
				new DataPageView({ model: this.currentPageModel });
				break;
			case "Photos":
				new PhotosPageView({ model: this.currentPageModel });
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
		
		this.loadMap();
		this.loadMapSites();
		this.loadChart();
		
		this.listenTo(App.State, 'change:currenttopic', this.changeTopic);
		this.listenTo(App.State, 'change:selectedsite', this.changeSite);

	},
	render: function() {
		
	},
	
	loadMap: function() {
		// Load Map
		var spread = $('.spread');
		var mapDiv = $("<div>").addClass('map');
		// Pass pointer events
		mapDiv.attr('touch-action', 'none');
		spread.append(mapDiv);
		this.map = new MapSpread(mapDiv[0], this.model.get('sites'));
	},
	
	loadMapSites: function() {
		// Load Sites
		var sites = this.model.get('sites');
		
		// Dark to light, from colorbrewer
		var colors = ['#021735','#08306b', '#08519c', '#2171b5', '#4292c6', '#6baed6', '#9ecae1', '#c6dbef', '#deebf7'];
		
		// Order sites by latitude (highest to lowest)
		sites.sort(function(a,b) {
			return a.latitude - b.latitude;
		});
		
		for (site in sites) {
			var site = sites[site];
			site.color = colors.shift();
			site.selected = false;
			
			var code = site.sitecode;
			var name = site.sitename;
			var latitude = site.latitude;
			var longitude = site.longitude;
			var color = site.color;
			
			var camera = (site.camera) ? '' : "<i class='fa fa-video-camera'></i>";
			
			this.map.addOverlay(new ol.Overlay({
			  position: ol.proj.transform(
			    [longitude, latitude],
			    'EPSG:4326',
			    'EPSG:3857'
			  ),
			  element: $("<div id='" + code + "'><div class='marker-left'></div><div class='marker'>" + name + camera + "</div></div>"),
			  positioning: 'center-left',
			}));
			
			$("#"+code).fadeTo(200,.8);
			//$("#"+code+" .marker").css('background-color', '');
			//$("#"+code+" .marker-left").css('border-right-color', '');
			
			(function(site) {
				$("#"+code).click(function() {
					App.State.set("selectedsite", site);
				});
			})(site);
			
		}
	},
	
	loadChart: function() {
		// Setup Chart
		var detail = $('.topic .detail');
		var chartDiv = $("<div>").addClass('chart');
		// Pass pointer events
		chartDiv.attr('touch-action', 'none');
		detail.append(chartDiv);
		this.chart = new Chart(chartDiv[0]);
	},
	
	changeSite: function() {
		debug('change site to ' + App.State.get("selectedsite").sitename);
	},
	
	changeTopic: function() {
		debug('change topic to ' + App.State.get("currenttopic").get("name"));
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