var MenuView = Backbone.View.extend({
	el: 'body',
	initialize: function(options) {
		
		console.log("MenuViewInit");
		
		this.options = options;
		
		// After Fetch 
		this.listenTo(this.collection, 'change', this.render);
		
		// State Change
		this.listenTo(App.State, 'change:currentpage', this.render);
		this.listenTo(App.State, 'change:currenttopic', this.render);
		
		// Get the elements used
		this.mask = $("#mask");
		this.pageMenu = $("#pagemenu");
		this.pageDropdown = this.pageMenu.find(".dropdown");
		this.topicMenu = $("#topicmenu");
		this.topicDropdown = this.topicMenu.find(".dropdown");
		
		this.render();
	},
	
	render: function() {
		
		// Bail if there are no menu elements to show
		if(this.collection === undefined) return this;
		
		// set the current page
		var currentPage = this.pageMenu.find(".chooser span");
		currentPage.html('&nbsp;');
		
		// Indicate the current page
		var currentPageModel = App.State.get('currentpage');
		if(currentPageModel === undefined) {
		} else {
			currentPage.html(currentPageModel.get("name"));
		}
		
		// set the current topic
		var currentTopic = this.topicMenu.find(".chooser span");
		currentTopic.html('&nbsp;');
		
		// Indicate the current topic
		var currentTopicModel = App.State.get('currenttopic');
		if(currentTopicModel === undefined) {
		} else {
			currentTopic.html(currentTopicModel.get("name"));
		}
		
		// Close the exisitng dropdowns and mask
		this.closeMenus();
		
		// Build the page dropdown
		this.pageDropdown.empty();
		this.collection.each(function (item) {
			var menuItem = new PageMenuItem({ model: item, parent: this });
			this.pageDropdown.append(menuItem.render().$el);
		}, this);
		
		// Build the topic dropdown
		this.topicDropdown.empty();
		if(currentPageModel != undefined) {
			currentPageModel.get('topics').each(function (item) {
				var menuItem = new TopicMenuItem({ model: item, currentPageModel: currentPageModel, parent: this });
				this.topicDropdown.append(menuItem.render().$el);
			}, this);
		}
		
		return this;
	},
	
	events: {
		"click #pagemenu .chooser" : function() { this.menuClick(this.pageMenu.find(".dropdown"), this.mask); },
		"click #topicmenu .chooser" : function() { this.menuClick(this.topicMenu.find(".dropdown")); },
	},
	
	menuClick: function(dropdown, mask) {
		// Show Menu
		dropdown.stop().slideToggle(100);
		if(mask != undefined) mask.stop().fadeToggle(400);
		
		// Set Timeout to hide menu
		clearTimeout(this.timeout);
		this.timeout = setTimeout(function() {
			dropdown.stop().slideUp(100);
			if(mask != undefined) mask.stop().fadeOut(400);
		}, 7000);
	},
	
	closeMenus: function() {
		this.pageMenu.find(".dropdown").slideUp(100);
		this.topicMenu.find(".dropdown").slideUp(100);
		this.mask.fadeOut(400);
	}
	
});

// The choices in the dropdown
var PageMenuItem = Backbone.View.extend({
	tagName: 'span',
	initialize: function(options) {
		this.options = options;
	},
	render: function() {
		this.$el.html(this.model.get('name'));
		return this;
	},
	events: {
      "click" : "itemClick"
    },
    itemClick: function(event) {
	    // Goto the page, topic zero
	    App.Router.navigate(this.model.get('id'), true);
	    this.options.parent.closeMenus();
    }
});

// The choices in the dropdown
var TopicMenuItem = Backbone.View.extend({
	tagName: 'span',
	initialize: function(options) {
		this.options = options;
	},
	render: function() {
		this.$el.html(this.model.get('name'));
		return this;
	},
	events: {
      "click" : "itemClick"
    },
    itemClick: function(event) {
	    // Go to the specific topic for the page
	    var url = this.options.currentPageModel.get('id') + "/" + this.options.currentPageModel.get("topics").indexOf(this.model);
	    App.Router.navigate(url, true);
	    this.options.parent.closeMenus();
    }
});

