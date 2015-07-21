var timeout = 7 * 1000;
var longtimeout = 20 * 1000;
var inactivity = 30 * 1000;

$( document ).ready(function() {

	// Improve click responsiveness on iOS and Android
	// FastClick.attach(document.body);

	// Menu
	var menuTimeout;
	$("#menu .button").click( function() {
		
		// Show Menu
		$("#menu .dropdown").slideToggle(100);
		$("#mask").delay(0).fadeToggle(400);
		
		// Set Timeout to hide menu
		clearTimeout(menuTimeout);
		menuTimeout = setTimeout(function() {
			$("#menu .dropdown").slideUp(100);
			$("#mask").fadeOut(200);
		}, timeout);
		
	});
	$("#mask").click(function() {
		
		// If others are clicking the screen, this could make the menu unusable...
		
		// Hide the menu
		$("#menu .dropdown").toggle();
		$("#mask").toggle();
		
		// Cancel Timeout
		clearTimeout(menuTimeout);
		
	});
	
	// Credits
	$("#credits .text").hide();
	var creditsTimeout;
	$("#credits").click(function() {
		
		// Show the credits
		$("#credits .text").toggle(200).css("display", "inline-block");
		
		// Set Timeout to hide them again
		clearTimeout(creditsTimeout);
		creditsTimeout = setTimeout(function() {
			$("#credits .text").hide(200);
		}, timeout);
	});

});