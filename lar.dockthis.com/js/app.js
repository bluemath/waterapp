/*
$( document ).ready(function() {
	
	// Load the model
	var pages = new Pages();
	pages.fetch().then(function() {
		console.log(pages);
	});
	
	// Page Menu
	var pageMenu = new MenuView({
		collection: pages,
		el:'#pagemenu',
		appkey: 'currentpage',
		mask: $("#mask")
	});
	
	// Page
	var pageContent = new PageView();
	
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
*/