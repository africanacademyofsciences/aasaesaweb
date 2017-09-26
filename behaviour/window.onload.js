// JavaScript Document

$(window).load(function()
{
	// -------------------- Keep large images within the design --------------------
	
	$("#secondarycontent div.rounded").each(function()
	{	
		$(this).corner("8px");
	});
	$("#secondarycontent div.rounded h3.rounded").each(function()
	{	
		$(this).corner("8px");
	});
	
	// Dodgy link replacement stuff
	if (!document.getElementsByTagName) return;
	var anchors = document.getElementsByTagName("a");
	for (var i=0; i<anchors.length; i++) {
		var anchor = anchors[i];
		if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "external") {
			anchor.target = "_blank";
		}
	}	

});
