@extends('pages.grid')


@section('pageName', 'Red Butte Creek')


@section('pageID', 'rbc')


@section('pageScripts')
	<script src="{{ URL::asset('js/rbc.js') }}"></script>
	<script src="{{ URL::asset('js/map.js') }}"></script>
	<script src="{{ URL::asset('js/chart.js') }}"></script>
@endsection


@section('pageMenu')
	<div>Great Salt Lake Watershed</div>
	<div>GAMUT Project</div>
	<div class='current'>Red Butte Creek</div>
	<div>Biodiversity</div>
@endsection


@section('pageIntro')
	<p>Red Butte Creek is a small stream whose headwaters are found in the northeast part of Salt Lake County, Utah. It flows west through the Red Butte Garden and Arboretum, by the University of Utah, Fort Douglas and flows southwesterly through Salt Lake City’s Liberty Park before forming a confluence with the Jordan River.</p>
@endsection


@section('pageSpread')
	<div id="rbcmap" class="map"></div>
@endsection


@section('topicDetail')
	<div class="detail block white">
		<div id="chart"></div>
	</div>
@endsection


@section('js')
	<script>
		// Initalize Data Structure
		var sites = [];
		
		// Sites with Variables
		@foreach ($sites as $site)
			
			sites.push({
				name: '{{ str_replace(' Advanced Aquatic', '', str_replace(' Basic Aquatic', '', $site->sitename)) }}',
				code: '{{$site->sitecode}}',
				latitude: {{$site->latitude}},
				longitude: {{$site->longitude}},
				series: [
					@foreach ($site->series as $series) 
						'{{$series->variablecode}}',
					@endforeach
				]
			});

		@endforeach
		
		var variables = [
		    {
			    name: 'Temperature',
			    codes: ['WaterTemp_EXO'],
		    },
		    {
			    name: 'Dissolved Oxygen',
			    codes: ['ODO']
		    },
		    {
			    name: 'pH',
			    codes: ['pH']
		    },
		    {
			    name: 'Specific Conductance',
			    codes: ['SpCond']
		    },
		    {
			    name: 'Turbitdity',
			    codes: ['TurbMed']
		    },
		    {
			    name: 'Water Level',
			    codes: ['Stage','Level']
		    }
	    ];

	    var topics = [];
	    
	    // All Varaibles, one site
	    topics.push({
				name: 'A Monitoring Station',
				variables: ['WaterTemp_EXO', 'ODO', 'pH', 'SpCond', 'TurbMed', 'Stage', 'Level'],
				mode: DE.mode.ONESITE
		    });
		    
		// Curated pairs
		topics.push({
			name: 'Dissolved Oxygen and Temperature',
			variables: ['ODO', 'WaterTemp_EXO'],
			mode: DE.mode.ONESITE
		});
		topics.push({
			name: 'Turbidity and Water Level',
			variables: ['TurbMed', 'Stage', 'Level'],
			mode: DE.mode.ONESITE
		});
		
		// One variable, many sites  
		for (index in variables) {
			variable = variables[index];
			topics.push({
				name: variable.name,
				variables: variable.codes,
				mode: DE.mode.MANYSITES
			});
		}

		// Initalize Data Explorer
		var dataexplorer = DE.create(sites, variables, topics);
		
	</script>
@endsection