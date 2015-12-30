
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
				this.$el.append($("<p>").html(text[i]));				
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
			div.append($("<div>").addClass("name").html(camera.name + " <i class='fa fa-video-camera'></i>").css('background-color', camera.color));
			div.append($("<div>").addClass("image").append($("<img>").hide()));
			cameraArea.prepend(div);
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
					color: site.get('color'),
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
		tzh = (new Date()).getTimezoneOffset() / 60;
		tz =  tzh * 60 * 60;
		
		unix = App.State.get('unixtimestamp');
		// Adjust for timezone and add a minute (so we hit after not on)
		timestamp = unix - tz + 60;
		
		for (i in this.cameras) {
			camera = this.cameras[i];
			index = _.sortedIndex(camera.timestamps, timestamp);
			
			// Find the image
			if(index == 0) {
				
				// No camera image for this date
				camera.newtimestamp = null;	
				
			} else {
				
				// Get the closest previous photo within half a day
				index--;
				camera.newtimestamp = camera.timestamps[index];
				halfday = 13 * 60 * 60;
				
				// Too far away, show no photo
				if(Math.abs(camera.newtimestamp - timestamp) >= halfday) {
					camera.newtimestamp = null;
				}
			}
			
			// Update the view
			if(camera.newtimestamp == null) {
				
				// No image
				camera.timestamp = camera.newtimestamp;
				$('#' + camera.code+ " img").hide();
				
			} else if(camera.timestamp != camera.newtimestamp) {
				
				// Photo URL
				camera.timestamp = camera.newtimestamp;
				date = new Date(camera.timestamp * 1000);
				year = date.getFullYear();
				month = ("0" + (date.getMonth() + 1)).slice(-2);
				src = "/img/cameras/" + camera.code + "/" + year + "/" + month + "/" + camera.timestamp + ".jpg";
				
				// Create a dummy <img> to force the browser to load the image (and cache it) in the event
				// that the image is changed (by scrubbing) before it has a chance to download.
				// e.g. without this, Chrome aborts downloading if scrubbed away before success.
				// Could this lead to memory leak / performance issues?
				$("<img>").attr("src", src);

				// Set actual visible image
				image = $('#' + camera.code+ " img").attr("src", src).show();
				
				// Show disclaimer if the shown image is temporally distant from currently selected time
				twohours = 2 * 60 * 60;
				if(Math.abs(camera.newtimestamp - timestamp) >= twohours) {
					// Show message
				} else {
					// Hide message
				}
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
		this.loadPOI();
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

		var pLatitude = 0;

		sites.each(function(site) {
			
			// Set color
			// The color is used to highlight sites in updateViews()
			site.set('color', colors.shift());
			
			// Marker data
			var code = site.get('sitecode');
			var name = site.get('sitename');
			var latitude = site.get('latitude');
			var longitude = site.get('longitude');
			var div = "";
			var positioning = "";
			
			debug(Math.abs(pLatitude - latitude) + " from the previous marker");
			
			// Compute div of marker
			if(pLatitude != null && Math.abs(pLatitude - latitude) < .002) {
				// Arrow Left
				positioning = 'center-left';
				if(site.get('camera')) name = "<i class='fa fa-video-camera fa-flip-horizontal'></i>" + name;
				div = "<div id='" + code + "' class='markercontainerL'><div class='dot'></div><div class='markerArrow'></div><div class='marker'>" + name + "</div></div>"
			} else {
								// Arrow Right
				positioning = 'center-right';
				if(site.get('camera')) name = name + "<i class='fa fa-video-camera'></i>";
				div = "<div id='" + code + "' class='markercontainerR'><div class='marker'>" + name + "</div><div class='markerArrow'></div><div class='dot'></div></div>"

			}
			
			this.addToMap(latitude, longitude, $(div), positioning);
/*
			// Add marker
			this.map.addOverlay(new ol.Overlay({
			  position: ol.proj.transform(
			    [longitude, latitude],
			    'EPSG:4326',
			    'EPSG:3857'
			  ),
			  element: $(div),
			  positioning: positioning,
			}));
*/
			
			// Dim 
			$("#"+code).fadeTo(200,.8);
			
			var that = this;
			(function(site) {
				$("#"+code).click(function() {
					that.model.set("selectedsite", site);
					that.updateSites();
				});
			})(site);
			
			pLatitude = latitude;
			
		}, this);
		
	},
	
	loadPOI: function() {
		var poi = this.model.get('poi');
		poi.each(function(p) {
			var name = p.get('name');
			var latitude = p.get('latitude');
			var longitude = p.get('longitude');
			var icon = p.get('icon');
			var div = "<div class='markericon'><div class='dot'></div><div class='iconArrow'></div><img src=\"" + icon + "\"></div>";
			this.addToMap(latitude, longitude, $(div), 'center-left');
		}, this);
	},
	
	addToMap: function(lat, lon, JQE, position) {
		this.map.addOverlay(new ol.Overlay({
		  position: ol.proj.transform(
		    [lon, lat],
		    'EPSG:4326',
		    'EPSG:3857'
		  ),
		  element: $(JQE),
		  positioning: position,
		}));
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
		
		this.updateViews();
		this.cameras.loadCameras();
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
				$("#"+code+" .markerArrow").css('border-right-color', color);
				$("#"+code+" .markerArrow").css('border-left-color', color);
				$("#"+code).fadeTo(0, 1);
			} else {
				// Deactivate
				$("#"+code).fadeTo(0,.8);
				$("#"+code+" .marker").css('background-color', '');
				$("#"+code+" .markerArrow").css('border-right-color', '');
				$("#"+code+" .markerArrow").css('border-left-color', '');
			}
		});
		
		// Send the correct series to the chart
		var that = this;
		setTimeout(function() {
			var topic = App.State.get("currenttopic");
			that.chart.update(topic, selectedSites);			
		}, 0);
	}
	
});

var PhotosPageView = Backbone.View.extend({
	initialize: function() {
		
		debug("DataPageView Init");
				
		// Listen to changes
		this.listenTo(App.State, 'change:currenttopic', this.changeTopic);
		this.listenTo(this.model, 'change:currentphoto', this.updatePhoto);
		
		this.spread = $('.spread');
		this.background = $("<div>").addClass('image');
		this.spread.empty().append(this.background);
		
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
		
		// Set the topic background
		this.spread.css("background-image", "url('" + currentTopic.get("background") +"')");
		
		// Show the default photo
		var model = this.model;
		model.set('currentphoto', currentTopic.get("photos").at(currentTopic.get("default")));
	
		// Load the thumbnails
		this.thumbnails.empty();
		currentTopic.get("photos").each(function(photo) {
			
			// Setup the thumbnail
			//var image = $("<div>").attr("src", photo.get("img"));
			var image = $("<div>").addClass("image").css("background-image", "url('" + photo.get("img") + "')");
			
			if(photo.get("type") == "imagefile") {
				// For debugging images, show the filename
				name = photo.get("img").split("/")[3].split(".")[0];
				var text = $("<div>").addClass("label").html(name);	
			} else {
				var text = $("<div>").addClass("label").html("<span>" + photo.get("label") + "</span>");	
			}
			
			var thumb = $("<div>").addClass(photo.get("type"));
			thumb.append(image).append(text);
			thumb.css('transition', '.2s ease-out');
			//deg = Math.random() * 14 - 7;
			//thumb.css('transform', 'rotate(' + deg + 'deg)');
			this.thumbnails.append(thumb);
			
			// Respond to clicks
			thumb.click(function() {
				var a = Math.random() * 10 - 5;
/*
				$(".thumbnail").css('transform', 'translateY(10px)');
				$(this).css('transform', '');
*/
				deg = (Math.random() < .5 ? -1 : 1) * ((Math.random() * 3) + 3);
				//$(this).css('transform', 'rotate(' + deg + 'deg)');

				model.set('currentphoto', photo);
			});
			
			// Accentuate the current
/*
			if(model.get('currentphoto') != photo) {
				thumb.css('transform', 'translateY(10px)');
			}
*/
			
		}, this);
		
	},
	updatePhoto: function() {
		this.render();
	}
});