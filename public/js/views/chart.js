function Chart(element, sites, variables) {

	this.baseURL = "/sites";

	this.sites = sites;
	this.variables = variables;

    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });

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
				        	unixtimestamp = this.x / 1000;
							App.State.set('unixtimestamp', unixtimestamp);
			        	}
		        	}
	        	},
	        	lineWidth: 2
        	}  
        },
        navigator : {
            baseSeries : 'WaterTemp_EXO',
            margin: 40
        },
        legend : {
            enabled: false,
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
        	dateTimeLabelFormats : {
			    millisecond:"%A, %b %e, %l:%M:%S.%L %p",
			    second:"%A, %b %e, %l:%M:%S %p",
			    minute:"%A, %b %e, %l:%M %p",
			    hour:"%A, %b %e, %l:%M %p",
			    day:"%A, %b %e, %Y",
			    week:"Week from %A, %b %e, %Y",
			    month:"%B %Y",
			    year:"%Y"
			}
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
        yAxis: [],
        xAxis: {
	        type: 'datetime',
			ordinal: false,
		    dateTimeLabelFormats : {
		        minute: '%l:%M %p',
		        hour: '%l %p',
		        day: '<b>%b %e</b>',
				week: '%b %e',
				month: '%b %Y',
				year: '%Y'
		    }
        }
    });
	
	this.update = function(topic, selectedSites) {
		
		// Remove current series
		this.removeAll();
		
		series = topic.get('variables');
		mode = topic.get('mode');
		
		selectedSites.each(function(site) {
			for(i in series) {
				s = series[i];
				
				// add the series if the site has it
				if(_.contains(site.get('series'), s)) {
					variable = variables.findWhere({variablecode: s});
					// color is variable color if only one site is shown
					// otherwise, it's the same as the site
					var color = (mode == 'ONE') ? undefined : site.get('color');
					this.addSeries(site, variable, color);
				}
			}
		}, this);
	}
	
	this.removeAll = function() {
		var seriesLength = this.chart.series.length;
		for(var i = seriesLength -1; i > -1; i--) {
			this.chart.series[i].remove();
		}
		this.chart.showLoading();
	}
	
	this.addSeries = function(site, variable, color) {

		chart = this.chart;
		sitecode = site.get('sitecode');
		variablecode = variable.get('variablecode');
		sitename = site.get('sitename');
		variablename = variable.get('variablename');
		var units = variable.get('variableunitsabbreviation');
		
		// convert degC to ºF
		if (units == 'degC') {
			units = "ºF";
		}
		
		// convert m to cm
		if (units == 'm') {
			units = "cm";
		}
		
		// Present most units on the same axis
		yaxis = variablecode;
		
		// present cm and NTU series on individual axes
		// this is because the measurements are relative
		if (units == 'cm' || units == 'NTU') {
			yaxis = variablecode+sitecode;
		}
		
		// Add axis if it doens't exist
		if(chart.get(yaxis) == null) {
			var axis = {
				labels: { enabled: false }, 
				title: { enabled: false },
				id: yaxis
			};
			chart.addAxis(axis);
		}

		var s = {
		    id: sitecode+variablecode,
		    name: sitename+', '+variablename,
		    yAxis: yaxis,
		    tooltip: {
			    valueSuffix: units
		    },
		    visible: true,
		    color: color
	    }
	    
		chart.addSeries(s);
		
		url = this.baseURL + "/" + sitecode + "/" + variablecode;
		$.getJSON(url, (function(site, variable) {
				
			return function(data) {
				var sitecode = site.get('sitecode');
				var variablecode = variable.get('variablecode');
				var sitename = site.get('sitename');
				var variablename = variable.get('variablename');
				var units = variable.get('variableunitsabbreviation');
				
				// convert degC to ºF
				if (units == 'degC') {
					data = _.map(data, function(pair) {
						pair[1] = pair[1] * 1.8 + 32;
						return pair;
					});
				}
				
				// convert m to cm
				if (units == 'm') {
					data = _.map(data, function(pair) {
						pair[1] = pair[1] * 100;
						return pair;
					});
				}
								
				// Process timestamps from unix to js
				data = _.map(data, function(pair) {
					pair[0] = pair[0] * 1000;
					return pair;
				});
			    
				if(chart.get(sitecode+variablecode) != null) {
					chart.get(sitecode+variablecode).setData(data);
					chart.hideLoading();
				}
			}

		})(site, variable));
	}
	
}
