
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
			case "Hover":
				App.State.pageView = new HoverPageView({ model: this.currentPageModel });
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
			bar = $("<div>").addClass("bar").css('background-color', camera.color)
			bar.append($("<div>").addClass("name").html(camera.name + " <i class='fa fa-video-camera'></i>"));
			bar.append($("<div>").addClass("note").html("closest daytime photo").hide());
			div.append(bar);
			image = div.append($("<div>").addClass("image").append($("<img>").hide()));
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
		var timestamp = unix - tz + 60;
		
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
				var image = $('#' + camera.code + " img").attr("src", src).show();
				
				
				
			}
			
			var note = $('#' + camera.code + ' .note');
			
			// Show disclaimer if the shown image is temporally distant from currently selected time
			twohours = 2 * 60 * 60;
			if(Math.abs(camera.newtimestamp - timestamp) >= twohours) {
				note.show();
			} else {
				note.hide();
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
					console.log("selectedsite = " + site);
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
			$div = $(div);
			this.addToMap(latitude, longitude, $div, 'center-left');
			
			// Closure for this as that
			var that = this;
			
			$div.click(function() {
				
				// Load image
				$(".detail .panoramic").empty().append($("<img>").attr("src", p.get('image')));
				
				// Swap Chart for Image
				$(".chart").hide();
				$(".detail .panoramic").show();
				
				App.State.get("selectedsites").reset();
				that.model.set("selectedsite", null);
				that.updateSites();
			});
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
		variables = this.model.get("variables");
		this.chart = new Chart(chartDiv[0], sites, variables);
		
		// Setup Image
		var imageDiv = $("<div>").addClass('panoramic').hide();
		detail.append(imageDiv);
	},
	
	changeTopic: function() {
		
		var topic = App.State.get("currenttopic");
		var selectedSite = this.model.get("selectedsite");
		var selectedSites = App.State.get("selectedsites");
		var allSites = this.model.get('sites');
		
		// Default site
		if(selectedSites.length == 0) {
			if(this.model.has("defaultsite")) {
				this.model.set("selectedsite", allSites.at(this.model.get("defaultsite")));	
			} else {
				this.model.set("selectedsite", allSites.first());	
			}
		}
		
		// Prevent too many sites from being visible
		if(topic.get('mode') == "ONE") {
			// Most recently selected site will populate
			selectedSites.reset();
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
		
		// Don't update the chart if selected sites is empty
		if(selectedSites.length > 0) {
			// Send the correct series to the chart
			var that = this;
			setTimeout(function() {
				var topic = App.State.get("currenttopic");
				$(".detail .panoramic").hide();
				$(".chart").show();
				that.chart.update(topic, selectedSites);	
			}, 0);
		}

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
		this.background.css("background-image", "url('" + currentTopic.get("background") +"')");
		
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
			this.thumbnails.append(thumb);
			
			// Respond to clicks
			thumb.click(function() {
				var a = Math.random() * 10 - 5;

				$(".thumbnails").children().css('transform', 'translateY(5px)');
				thumb.css('transform', '');

				model.set('currentphoto', photo);
			});
			
			// Accentuate the current
			if(model.get('currentphoto') != photo) {
				thumb.css('transform', 'translateY(5px)');
			}

			
		}, this);
		
	},
	updatePhoto: function() {
		this.render();
	}
});

var HoverPageView = Backbone.View.extend({
	initialize: function() {
		
		debug("HoverPageView Init");
		
		this.listenTo(App.State, 'change:currenttopic', this.changeTopic);
				
		// Listen to finger
		this.listenTo(this.model, 'change:currenthotspot', this.changeHotspot);
		
		// Setup the background image holder
		this.spread = $('.spread');
		this.background = $("<img>").attr("usemap", "#hotspots");
		this.spread.empty().append(this.background);
		this.info = $("<div>").addClass("info");
		this.info.hide();
		this.spread.append(this.info);
		this.template = _.template("<div class='details'><%= text %></div><div class='photo'><img class='coverphoto' src='<%= img %>'></div><div class='tag'><div class='arrow'><span class='name'><%= name %></span><span class='subname'><%= subname %></span></div><div class='end'></div></div>");
		
		this.spread.append($("<div class='touch'>").html("<img src='/img/app/touch.png'> Touch an animal to learn more"));
		
		this.spread.on("pointermove", {that: this}, this.pointerMove);
		this.spread.on("pointerdown", {that: this}, this.pointerMove);
		this.spread.on("pointerup", {that: this}, this.pointerUp);
		
		// Disable topic menu
		$("#topicmenu").hide();
		
		// Create hotspot view
		
	},
	render: function() {
	},
	remove: function() {
		$("#topicmenu").show();	
	},
	changeHotspot: function() {
		// Change View
		if(this.model.has("currenthotspot")) {
			var hotspot = this.model.get("currenthotspot");
			this.info.html(this.template(hotspot));
			if(hotspot.img != '') {
				$(".info .coverphoto").cover();
				$(".info .photo").fadeIn();
			}
		}
	},
	pointerMove: function(event) {
		// Move View
		that = event.data.that;
		if(that.model.has("currenthotspot")) {
			that.info.show();
		} else {
			that.info.hide();
		}
		/* update the location of the hover box */
		that.info.css("top", Math.min(event.clientY, $(document).height()));
		that.info.css("left", Math.min(event.clientX + 20, $(document).width() - 300));
	},
	pointerDown: function(event) {
		// Show view
		that = event.data.that;
		if(that.model.has("currenthotspot")) {
			that.info.show();
		}
	},
	pointerUp: function(event) {
		// Hide View
		that = event.data.that;
		//that.info.hide();
	},
	changeTopic: function() {

		var currentTopic = App.State.get("currenttopic");
		
		this.map = $("<map>").attr("name", "hotspots");
		var hotspots = currentTopic.get("hotspots");
		
		_.each(hotspots, function(hotspot) {
			var area = $("<area>").attr("shape", "poly").attr("coords", hotspot.coords);
			var model = this.model;
			area.on("pointerover", function() {
				model.set("currenthotspot", hotspot);
			});
			area.on("pointerout", function() {
				model.set("currenthotspot", null);
			});

			
			this.map.append(area);
		}, this);
		
		this.spread.append(this.map);
		
		// Set the background
		this.background.attr("src", currentTopic.get("background"));
		this.background.cover({
			backgroundPosition: "top right",
			callbacks: { "ratioSwitch" : function() {
				$(window).trigger('resize'); // Fixes the map on iOS devices
			}}});
		
		this.background.rwdImageMaps();
	},

});