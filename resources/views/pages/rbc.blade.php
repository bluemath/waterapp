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
	<div>Biodiversity</div>
@endsection


@section('pageIntro')
	<p>Red Butte Creek is a small stream whose headwaters are found in the northeast part of Salt Lake County, Utah. It flows west through the Red Butte Garden and Arboretum, by the University of Utah, Fort Douglas and flows southwesterly through Salt Lake City’s Liberty Park before forming a confluence with the Jordan River.</p>
	<p>Explore the data collected along the creek.</p>
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
		
		// Add sites to the map
		@foreach ($sites as $site)
			
			sites.push({
				name: '{{ str_replace(' Advanced Aquatic', '', str_replace(' Basic Aquatic', '', $site->sitename)) }}',
				code: '{{$site->sitecode}}',
				latitude: {{$site->latitude}},
				longitude: {{$site->longitude}},
				series: {
					@foreach ($site->series as $series) 
						code:{{$series->variablecode}},
						name:{{$series->variablename}},
						units: {
							name:{{$series->variableunitsname}},
							abbreviation:{{$series->variableunitsabbreviation}}
						}
					@endforeach
				}
			});

		@endforeach
		
		// Initialize Map
		var rbcmap = Map('rbcmap', sites);
		
		// Initalize Data Explorer
		var dataexplorer = DataExplorer(sites, variables);
	</script>
@endsection