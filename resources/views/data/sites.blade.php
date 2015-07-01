@extends('app')

@section('content')
	<h1>Sites</h1>
	
	@foreach ($sites as $site)
		<h2><a href="{{ action('DataController@series', [$site->sitecode] )}}">{{ $site->sitecode }}</a> {{ $site->sitename }}</h2>
	@endforeach
@stop