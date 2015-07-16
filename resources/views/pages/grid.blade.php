<html>
	
	<head>
		
		<meta name="viewport" content="width=1920, height=1080, user-scalable=no">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">

		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>		
		<script src="//openlayers.org/en/v3.7.0/build/ol.js" type="text/javascript"></script>
		<script src="//code.highcharts.com/stock/highstock.js"></script>
		<script src="//code.highcharts.com/stock/modules/exporting.js"></script>
		<script src="{{ URL::asset('js/fastclick.js') }}"></script>
		
		<script src="{{ URL::asset('js/ui.js') }}"></script>
		@yield('pageScripts')
		
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="{{ URL::asset('css/normalize.css') }}" type="text/css" media="screen" charset="utf-8">
		<link rel="stylesheet" href="{{ URL::asset('css/boilerplate.css') }}" type="text/css" media="screen" charset="utf-8">
		<link rel="stylesheet" href="{{ URL::asset('css/page.css') }}" type="text/css" media="screen" charset="utf-8">
		
	</head>
	
	<body>

		<div class="page" id="@yield('pageID')"> 
	
			<div class="spread">
				@yield('pageSpread')
			</div>
			
			<div class="topic">
				<div class="row">
					<div class='chooser button'>
						<span class="label"><!-- filled by js -->Loading...</span>
						<i class="fa fa-chevron-down pull-right"></i>
					</div>
					<div class="control">
						<!-- cloned by jQuery -->
						<div class="inline button" style="display:none;">
							<span class="label"></span>
							<i class="fa fa-times pull-right"></i>
						</div> 
					</div>
				</div>
				<div id="detailBlock" class="row big">
					<div class="text block white">
						<!-- filled by js -->
						<p><i class="fa fa-circle-o-notch fa-spin"></i></p>
					</div>
					@yield('topicDetail')
				</div>
			</div>
			
			<div class="intro">
				@yield('pageIntro')
			</div>

			@yield('js')

		</div>
		
		<div id="mask"></div>
		
		<div id="credits">
		
			<img src="{{ URL::asset('img/nhmu.svg') }}" alt="NHMU Logo" width="36" height="36">
			<img src="{{ URL::asset('img/iutahepscor.svg') }}" alt="iUtah EPSCoR Logo" width="81" height="36">
			<img src="{{ URL::asset('img/nsf.svg') }}" alt="NSF Logo" width="36" height="36">
			<div class="text">
				This application was developed by the Natural History Museum of Utah with<br>
				support from iUtah and National Science Foundation award ABC-123456789
			</div>
			
		</div>
		
		<div id="menu">
			<div class='big button'>
				<span class="label">@yield('pageName')</span>
				<i class="fa fa-chevron-down pull-right"></i>
			</div>
			<div class="dropdown">
				@yield('pageMenu')
			</div>
		</div>
		
	</body>
	
</html>