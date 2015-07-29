var Topic = Backbone.Model.extend({
});

var Topics = Backbone.Collection.extend({
	model: Topic
});

var Page = Backbone.Model.extend({
	model: {
		topics: Topics
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
	url: '/app/pages',
	model: Page
})

