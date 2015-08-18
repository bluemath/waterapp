	<div><a href="{{ action('DataController@series', [$sitecode] ) }}">Back to Series List</a></div>

	<h1>{{ $sitecode }} {{ $variablecode }}</h1>
	
	<div><a href="{{ action('DataController@dataUpdate', [$sitecode, $variablecode] ) }}">Query source for more</a></div><br>
	
	@foreach ($data as $d)
		<div>{{ $d->datetime }} {{ $d->value }}</div>
	@endforeach