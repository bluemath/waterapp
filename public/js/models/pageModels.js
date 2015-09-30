var Photo = Backbone.Model.extend({
	
});
var Photos = Backbone.Collection.extend({
	model: Photo
});

var Topic = Backbone.Model.extend({
	model: {
		photos: Photos
	},
	parse: function(response){
		// http://stackoverflow.com/questions/6535948/nested-models-in-backbone-js-how-to-approach
		// Nested model parsing
        for(var key in this.model)
        {
            var embeddedClass = this.model[key];
            var embeddedData = response[key];
            response[key] = new embeddedClass(embeddedData, {parse:true});
        }
        return response;
    }
});
var Topics = Backbone.Collection.extend({
	model: Topic
});

var Site = Backbone.Model.extend({
});
var Sites = Backbone.Collection.extend({
	model: Site,
	comparator: function(site) {
		return -site.get("latitude");
	}
});

var POI = Backbone.Model.extend({
});
var POIs = Backbone.Collection.extend({
	model: POI,
	comparator: function(poi) {
		return -poi.get("latitude");
	}
});

var Variable = Backbone.Model.extend({
});
var Variables = Backbone.Collection.extend({
	model: Variable
});

var Page = Backbone.Model.extend({
	model: {
		topics: Topics,
		sites: Sites,
		poi: POIs,
		variables: Variables
	},
	initialize: function() {
		//this.fetch();
	},
	parse: function(response){
		// http://stackoverflow.com/questions/6535948/nested-models-in-backbone-js-how-to-approach
		// Nested model parsing
        for(var key in this.model)
        {
            var embeddedClass = this.model[key];
            var embeddedData = response[key];
            response[key] = new embeddedClass(embeddedData, {parse:true});
        }
        return response;
    }
});

var Pages = Backbone.Collection.extend({
	url: '/pages',
	model: Page
});