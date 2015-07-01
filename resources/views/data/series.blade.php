@extends('app')

@section('content')

	<div><a href="{{ action('DataController@sites') }}">Back to Sites</a></div>

	<h1>{{ $series[0]->sitecode }}</h1>
	
	@foreach ($series as $s)
		<div><a href="{{ action('DataController@data', [$s->sitecode, $s->variablecode]) }}">{{ $s->variablecode }}</a> {{ $s->methoddescription }}</div>
	@endforeach

@stop