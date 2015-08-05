@extends('app')

@section('content')

	<script src="{{ URL::asset('js/lib/jquery/jquery-1.11.3.js') }}"></script>
	<script src="{{ URL::asset('js/lib/highstock/highstock.src.js') }}"></script>
	<script src="{{ URL::asset('js/lib/backbone/underscore.js') }}"></script>

	<div><a href="{{ action('DataController@sites') }}">Back to Sites</a></div>

	<h1>{{ $series[0]->sitecode }}</h1>
	
	<div id="chart" style="height: 412px; width: 1260px;"></div>
	
	<script>
		var chart;
		$(function () {

			Highcharts.setOptions({
			        global: {
			            useUTC: false
			        }
			    });

	        // Create the chart
	        chart = new Highcharts.StockChart({
	            chart : {
		            renderTo: 'chart',
	                pinchType: '',
	                panning: false
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
		                r: 8,
		                style: {
		                    color: '#039',
		                    fontWeight: 'bold'
		                },
		                states: {
		                    hover: {
		                    },
		                    select: {
		                        fill: '#039',
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
		        	@foreach ($series as $s)
		        		
		        		{
			        		title: {
				        		text: null
			        		},
			        		labels: {
							  enabled: false
							}	
			        		
		        		},
		        		
		        	@endforeach 
	            ]
	        });
			
		});
		var axis = 0;
	</script>

	
	
	@foreach ($series as $s)
		<div><a href="{{ action('DataController@data', [$s->sitecode, $s->variablecode]) }}">{{ $s->variablecode }}</a> (<a href="{{ action('DataController@dataUpdate', [$s->sitecode, $s->variablecode]) }}">update</a>)</div>
		
		<script>
			$(function () {
				

			    var sitecode = '{{ $s->sitecode }}';
			    var variablecode = '{{ $s->variablecode }}';
			    var url = sitecode + '/' + variablecode + '?callback=?';
			    
			    console.log('{{ $s->variableunitsabbreviation }}');
			    
			    $.getJSON(url, function (data) {
				    
				    // Process timestamps from unix to js
					data = _.map(data, function(pair) {
						pair[0] = pair[0] * 1000;
						return pair;
					});
				    
				    var c = {
					    id: '{{ $s->variablecode }}',
					    name: '{{ $s->variablename }}',
					    yAxis: axis,
					    data: data,
					    tooltip: {
						    valueSuffix: ' {{ $s->variableunitsabbreviation }} '
					    }
				    }
				    axis++;
				    chart.addSeries(c);
			    });
			
			});
		</script>
		
	@endforeach

@stop