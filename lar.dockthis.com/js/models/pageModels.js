var Topic = Backbone.Model.extend({
});

var Topics = Backbone.Collection.extend({
	model: Topic
});

var Site = Backbone.Model.extend({
});

var Sites = Backbone.Collection.extend({
	model: Site,
	comparator: 'latitude'
});

var Variable = Backbone.Model.extend({
});

var Variables = Backbone.Collection.extend({
	model: Variable,
});

var Page = Backbone.Model.extend({
	model: {
		topics: Topics,
		sites: Sites,
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
})

