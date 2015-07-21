<!DOCTYPE html>
<head>
	
	<meta name="viewport" content="width=1920, height=1080, user-scalable=no">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">

<style type="text/css">

/* Fonts */
@font-face {
	font-family: "reykjavikone";
	src: url("../../fonts/reykjavikoneagauge-webfont.woff") format('woff');
	font-weight: normal;
}
@font-face {
	font-family: "reykjavikone";
	src: url("../../fonts/reykjavikonebgauge-webfont.woff") format('woff');
	font-weight: bold;
}
@font-face {
	font-family: "reykjavikone";
	src: url("../../fonts/reykjavikonecgauge-webfont.woff") format('woff');
	font-weight: 800;
}
@font-face {
	font-family: "reykjavikone";
	src: url("../../fonts/reykjavikonedgauge-webfont.woff") format('woff');
	font-weight: 900;
}

html,
body {
	cursor: none;
	
	font-family: 'reykjavikone';
    margin: 0;
    padding: 0;
    overflow: hidden;
    height: 100%;
    position: fixed;
	width: 100%;
	background-color: #296f99;
	color: #ffffff;
	
	/* Prevent highlight on click */
	-webkit-tap-highlight-color:  rgba(255, 255, 255, 0);  
	-webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

canvas {

}

canvas[resize] {
    width: 100%;
    height: 100%;
}

.banner {
	position: absolute;
	top: 43%;
	left: 25%;
	width: 50%;
	text-align: center;
	display: inline-block;
	background-color: rgba(67, 165, 197, .95);
	text-align: center;
	padding: 30px 0;
}

.banner p {
	padding: 0 0;
	margin: 0 30px;
	font-size: 30pt;
}

.banner p.bigger {
	font-size: 45pt;
}

</style>

<script src="/js/jquery/jquery-1.11.3.js"></script>

<script type="text/javascript" src="/js/jquery/pep.js"></script>

<script type="text/javascript" src="/js/paper/paper.js"></script>
<script type="text/javascript" src="/js/chipmunk/cp.js"></script>

<script type="text/javascript" src="/js/bubbles.js"></script>
<script type="text/javascript" src="/js/idle-timer/idle-timer.js"></script>

</head>
<body> 
	
	<canvas id="canvas" hidpi resize touch-action="none" keepalive="true"></canvas>
	
	<script type="text/javascript">
		// Load dots
		$(document).ready(function() {
			var UI = new DotUI(document.getElementById('canvas'));
			
			// Reload every 5 minutes (if idle)
			$.idleTimer(15*60*1000);
			
			$( document ).on( "idle.idleTimer", function(event, elem, obj){
				window.location.reload(true);
			});
			
		});
		
		// Prevent right click
		$(document).on({
		    "contextmenu": function(e) {
		        e.preventDefault();
			}
		});
		
	</script>
	
	<div class='banner'>
		<p>Red Butte Creek to the Great Salt Lake</p>
		<p class="bigger">New Exhibit Coming Soon!</p>
	</div>

</body>
</html>