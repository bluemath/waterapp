<h1>Splash Page</h1>

@foreach ($pages as $page)

	<h2><a href="{{ action($page['controller']) }}">{{ $page['name'] }}</a></h2>

@endforeach
