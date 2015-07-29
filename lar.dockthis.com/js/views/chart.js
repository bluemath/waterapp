var chart;

function Chart(element) {

    // Create the chart
    chart = new Highcharts.StockChart({
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
        yAxis: [     		
        		{
	        		labels: {
					  enabled: false
					}	
	        		
        		}		        	 
        ]
    });
	
}

function hcRemoveAll() {
	var seriesLength = chart.series.length;
	for(var i = seriesLength -1; i > -1; i--) {
		chart.series[i].remove();
	}
}

function hcAddSeries(url, sitecode, sitename, variablecode, variablename, axis, color) {
	$.getJSON(url, function (data) {
		 var s = {
		    id: sitecode+variablecode,
		    name: sitename+', '+variablename,
		    yAxis: axis,
		    data: data,
		    tooltip: {
			    valueSuffix: ''
		    },
		    color: color
	    }
	    chart.addSeries(s);
	});
}
