function Chart(element, sites, variables) {

	this.baseURL = "/data/sites";

	this.sites = sites;
	this.variables = variables;

	this.chart = new Highcharts.StockChart({
        chart : {
            renderTo: element,
            pinchType: '',
            panning: false,
            margin: [10, 15, 10, 15]
        },
        plotOptions : {
        	series: {
	        	states: {
		        	hover: {
			        	lineWidthPlus: 0
		        	}	
	        	}, 
	        	tooltip: {
		        	valueDecimals: 2
	        	},
	        	point: {
		        	events: {
			        	mouseOver: function() {
				        	console.log(this.x);
			        	}
		        	}
	        	}
        	}  
        },
        navigator : {
            baseSeries : 'WaterTemp_EXO'
        },
        legend : {
            enabled: true,
            align: 'right',
            backgroundColor: 'white',
            borderColor: 'black',
            borderWidth: 1,
            layout: 'vertical',
            verticalAlign: 'top',
            shadow: false,
            itemStyle: { "color": "#333333", "cursor": "pointer", "fontSize": "12px", "fontWeight": "bold" },
			floating: true
        },
        tooltip : {
        	borderColor: "#333333",
        	 
        },
        rangeSelector : {
            selected : 1,
            inputEnabled: false,
            buttons: [
            {
				type: 'day',
				count: 1,
				text: '1d'
			}, {
				type: 'week',
				count: 1,
				text: '1w'
			}, {
				type: 'month',
				count: 1,
				text: '1m'
			}, {
				type: 'month',
				count: 3,
				text: '3m'
			}, {
				type: 'ytd',
				text: 'YTD'
			}, {
				type: 'year',
				count: 1,
				text: '1y'
			}, {
				type: 'all',
				text: 'all'
			}],
			buttonTheme: { // styles for the buttons
                fill: 'none',
                stroke: 'none',
                'stroke-width': 0,
                r: 0,
                style: {
                    color: '#296f99',
                    fontWeight: 'bold'
                },
                states: {
                    hover: {
                    },
                    select: {
                        fill: '#296f99',
                        style: {
                            color: 'white'
                        }
                    }
                }
            }
        },
        exporting : {
            enabled: false  
        },
        scrollbar: {
            enabled: false
        },
        credits : {
            enabled: false    
        },
        title : {
            text: null
        },
        series : [],
        yAxis: []
    });
    
    this.variables.each(function(variable) {
		var variablecode = variable.get('variablecode');
		var axis = {
						labels: {
							enabled: false
						}, 
						id: variablecode
					};	

		this.chart.addAxis(axis);
	}, this);
	
	this.update = function(topic, selectedSites) {
		
		// Clear exisiting data
		this.removeAll();
		
		series = topic.get('variables');
		mode = topic.get('sites');
		
		selectedSites.each(function(site) {
			for(i in series) {
				s = series[i];
				
				// axis
				axis = i;
				
				// color is undefined (auto picked) if only one site is shown
				// otherwise, it's the same as the site
				var color = (mode == 'ONE') ? undefined : site.get('color');
				
				// add the series if the site has it
				if(_.contains(site.get('series'), s)) {
					variable = variables.findWhere({variablecode: s});
					this.addSeries(site, variable, color, axis);
				}
			}
		}, this);
	}
	
	this.removeAll = function() {
		var seriesLength = this.chart.series.length;
		for(var i = seriesLength -1; i > -1; i--) {
			this.chart.series[i].remove();
		}
	}
	
	this.addSeries = function(site, variable, color) {
		
		sitecode = site.get('sitecode');
		variablecode = variable.get('variablecode');
		url = this.baseURL + "/" + sitecode + "/" + variablecode;
		
		chart = this.chart;
		
		$.getJSON(url, (function(site, variable) {
				
				return function(data) {
					var sitecode = site.get('sitecode');
					var variablecode = variable.get('variablecode');
					var sitename = site.get('sitename');
					var variablename = variable.get('variablename');
					var units = variable.get('variableunitsabbreviation');
					
					var s = {
					    id: sitecode+variablecode,
					    name: sitename+', '+variablename,
					    yAxis: variablecode,
					    data: data,
					    tooltip: {
						    valueSuffix: units
					    },
				    }
				    if(color != undefined) s.color = color;
				    
				    //console.log(this);
				    
				    chart.addSeries(s);
				}

		})(site, variable));
	}
}
