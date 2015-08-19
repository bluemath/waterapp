
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
			case "Data":
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
			for (i in text) {
				this.$el.append($("<p>").html(this.model.get('text')));				
			}
		}
		return this;
	}
});

var CameraView = Backbone.View.extend({
	initialize: function() {
		debug("CameraView Init");
		this.cameras = [];
		this.listenTo(App.State, 'change:unixtimestamp', this.updateCameras);
	},
	render: function() {
		var cameraArea = $('.cameras');
		cameraArea.html("");
		for (i in this.cameras) {
			camera = this.cameras[i];
			div = $("<div>").attr("id", camera.code).addClass("camera");
			div.append($("<div>").addClass("name").html(camera.name));
			div.append($("<div>").addClass("image").append($("<img>").hide()));
			cameraArea.append(div);
		}
	},
	remove: function() {
		$('.cameras').empty();
		this.stopListening();
	},
	loadCameras: function() {
		debug("loadCameras");
		// Decide what cameras to show
		selectedSites = App.State.get("selectedsites");
		this.cameras = [];
		selectedSites.each(function(site) {
			if(site.get('camera')) {
				var camera = {
					code: site.get('sitecode'),
					name: site.get('sitename'),
					timestamps: [],
					timestamp: null,
					newtimestamp: null
				};

				$.getJSON('/cameras/' + camera.code, function(data) {
					camera.timestamps = data;
				})
				
				this.cameras.push(camera);
			}
		}, this);
		
		this.render();
		
		// Null the timestamp
		App.State.set('unixtimestamp', null);
	},
	updateCameras: function() {
		tz = (new Date()).getTimezoneOffset() * 60;
		// Adjust for timezone and add a minute
		timestamp = App.State.get('unixtimestamp') - tz + 60;
		for (i in this.cameras) {
			camera = this.cameras[i];
			index = _.sortedIndex(camera.timestamps, timestamp);
			if(index == 0) {
				// No camera image for this date
				camera.newtimestamp = null;	
			} else {
				index--;
				camera.newtimestamp = camera.timestamps[index];
				twohours = 2 * 60 * 60;
				if(Math.abs(camera.newtimestamp - timestamp) >= twohours) {
					// Too far away! Don't show anything...
					camera.newtimestamp = null;
				}
			}
			if(camera.newtimestamp == null) {
				camera.timestamp = camera.newtimestamp;
				$('#' + camera.code+ " img").hide();
			} else if(camera.timestamp != camera.newtimestamp) {
				camera.timestamp = camera.newtimestamp;
				date = new Date(camera.timestamp * 1000);
				year = date.getFullYear();
				month = ("0" + (date.getMonth() + 1)).slice(-2);
				$('#' + camera.code+ " img").attr("src", "/img/cameras/" + camera.code + "/" + year + "/" + month + "/" + camera.timestamp + ".jpg").show();
			}
		}
	}
});

var DataPageView = Backbone.View.extend({
	
	initialize: function() {
		
		debug("DataPageView Init");
		
		App.State.set('selectedsites', new Sites());
		
		this.listenTo(App.State, 'change:currenttopic', this.changeTopic);
		//this.listenTo(App.State, 'change:selectedsites', this.updateSites);
		
		this.cameras = new CameraView();
		
		this.render();
	},
	render: function() {
		debug("DataPageView Render");
		this.loadMap();
		this.loadMapSites();
		this.loadChart();
	},
	
	remove: function() {
		this.cameras.remove();
		this.stopListening();
	},
	
	loadMap: function() {
		// Load Map
		var spread = $('.spread');
		var mapDiv = $("<div>").addClass('map');
		spread.append(mapDiv);
		this.map = MapSpread(mapDiv[0], this.model.get('sites'), this.model.get('zoom'));
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
			
			if(site.get('camera')) {
				name = name + "<i class='fa fa-video-camera'></i>";
			}
			
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
		
		var selectedSites = App.State.get("selectedsites");
		
		if(App.State.get("currenttopic").get("mode") == "ONE") {
			// Toggle mode
			selectedSites.reset(site);
		} else {
			// Set of sites mode
			if(selectedSites.contains(site)) selectedSites.remove(site);
			else selectedSites.add(site);
			
			// Must have at least one site, so add it back if empty
			if(selectedSites.length == 0) selectedSites.add(site);
		}
		
		this.cameras.loadCameras();
		
		this.updateViews();
	},
	
	changeTopic: function() {
		
		var topic = App.State.get("currenttopic");
		var selectedSite = this.model.get("selectedsite");
		var selectedSites = App.State.get("selectedsites");
		var allSites = this.model.get('sites');
		
		// Prevent too many sites from being visible
		if(topic.get('mode') == "ONE") {
			if(selectedSites.length == 0) {
				// Default site
				this.model.set("selectedsite", allSites.first());
			} else {
				// Most recently selected site
				selectedSites.reset();
			}
		}
		
		// Center map
		this.map.recenter();
		
		this.updateSites();
	},
	
	updateViews: function() {
		
		var allSites = this.model.get('sites');
		var selectedSites = App.State.get("selectedsites");
		
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
		
		debug("DataPageView Init");
				
		// Listen to changes
		this.listenTo(App.State, 'change:currenttopic', this.changeTopic);
		this.listenTo(this.model, 'change:currentphoto', this.updatePhoto);
		
		var spread = $('.spread');
		this.background = $("<div>").addClass('image');
		spread.empty().append(this.background);
		
		var detail = $('.topic .detail');
		this.thumbnails = $("<div>").addClass('thumbnails');
		detail.empty().append(this.thumbnails);
		
		this.model.set('currentphoto', new Photo());
		
		// Inital render happens when model is changed
	},
	render: function() {
		var currentPhoto = this.model.get("currentphoto");
		
		// Set the splash photo
		if(currentPhoto != null) {
			this.background.css("background-image", "url('" + currentPhoto.get("img") + "')");	
		}
		
		// Update the highlighted thumbnail
		
		// Show caption
		
	},
	changeTopic: function() {

		var currentTopic = App.State.get("currenttopic");

		debug('change topic to ' + currentTopic.get("name"));
		
		// Show the default photo
		var model = this.model;
		model.set('currentphoto', currentTopic.get("photos").at(currentTopic.get("default")));
	
		// Load the thumbnails
		this.thumbnails.empty();
		currentTopic.get("photos").each(function(photo) {
			var image = $("<img>").attr("src", photo.get("img"));
			var thumb = $("<div>").addClass("thumbnail").append(image);
			thumb.css('transition', '.2s ease-out');
			this.thumbnails.append(thumb);
			thumb.click(function() {
				var a = Math.random() * 10 - 5;
				$(".thumbnail").css('transform', 'translateY(10px)');
				$(this).css('transform', '');
				model.set('currentphoto', photo);
			});
			
			if(model.get('currentphoto') != photo) {
				thumb.css('transform', 'translateY(10px)');
			}
			
		}, this);
		
	},
	updatePhoto: function() {
		this.render();
	}
});