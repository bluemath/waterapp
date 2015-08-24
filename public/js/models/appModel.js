$(function() {

	// Prevent secondary click
	$(document).on({
	    "contextmenu": function(e) {
	        e.preventDefault();
		}
	});

	// Credits
	$("#credits .text").hide();
	var creditsTimeout;
	$("#credits").click(function() {
		
		// Show the credits
		$("#credits .text").toggle(200).css("display", "inline-block");
		
		// Set Timeout to hide them again
		clearTimeout(creditsTimeout);
		creditsTimeout = setTimeout(function() {
			$("#credits .text").hide(200);
		}, App.State.get('timeout'));
	});


	// Build the App object (namespaced)
	(function() {
		
		window.App = {
			State: undefined,
			Models: {},
			Collections: {},
			Views: {},
			Router: {},
			Splash: undefined
		};
				
	})();
	
	// Create defaults and state
	App.State = new (Backbone.Model.extend({
		defaults: {
			timeout: 7 * 1000,
			longtimeout: 20 * 1000,
			inactivity: 30 * 1000
		}
	}))();
	
	// Page Model
	App.Collections.Pages = new Pages();
	
	// Page Menu
	App.Views.Menus = new MenuView({
		collection: App.Collections.Pages
	});
	
	// Page View
	App.Views.PageView = new PageView();
	
	// Load data
	App.Collections.Pages.fetch().then(function() {
		App.CreateRouter();
	});
	
	App.CreateRouter = function() {
		App.Router = new (Backbone.Router.extend({

			initialize: function() {
				App.Splash = new Splash($('#splash'));
				
				App.Collections.Pages.each(function (item) {
					// Add nav bubble
					App.Splash.addImageBubble(item.get('bubblescale'), item.get('img'), item.get('name'), function() {
						App.Router.navigate(item.get('id')+"/0", true);
						App.Splash.hide();
					});
				}, this);
				
				// Start the router
				console.log("Starting Router");
				Backbone.history.start();
			},
	
			routes: {
				'': 'splash',
				':id': 'page',
				':id/:topicid': 'topic'
			},
	
			splash: function(){
				console.log("ROUTE: splash");
				
				// As the site was accessed through the splash, return if idle
				$.idleTimer(App.State.get('inactivity'));
				$( document ).on( "idle.idleTimer", function(event, elem, obj){
					App.Splash.show();
					App.Router.navigate("", true);
				});
				
				// Show the splash
				App.Splash.show();
				
				// Set page title
				$(document).attr('title', 'iUtah');
				
			},
			
			page: function(id) {
				
				console.log("ROUTE: " + id);
				
				if(App.Splash != undefined) {
					App.Splash.hide();	
				}
				
				var model = App.Collections.Pages.where({id: id})[0];
				
				// Redirect to splash if invalid page
				if(model === undefined) {
					this.navigate("", true);
					return;
				}
				
				// No topic specified, so go for the first one
				this.navigate(id+"/0");
				this.topic(id, 0);
			},
			
			topic: function(id, topicid) {
				
				console.log("ROUTE: " + id + " " + topicid);
				
				if(App.Splash != undefined) {
					App.Splash.hide();
				}
				
				var pagemodel = App.Collections.Pages.where({id: id})[0];
				
				// Redirect to splash if invalid page
				if(pagemodel === undefined) {
					console.log("pagemodel for " + id + " couldn't be found");
					this.navigate("", true);
					return;
				}
				
				var topicmodel = pagemodel.get('topics').at(topicid);
				// Redirect to page if invalid topic
				if(topicmodel === undefined) {
					console.log("topicmodel for " + topicid + " couldn't be found");
					this.navigate(id, true);
					return;
				}
				
				// change to the correct models
				App.State.set("currentpage", pagemodel);
				App.State.set("currenttopic", topicmodel);
				
				// Set page title
				$(document).attr('title', pagemodel.get('name'));
			}
		
		}))();
	}
	
});