/* ==================================================
This script applies the .smaller class to the header element when you scroll to the number stated (100 in this example). 
Use CSS to define what .smaller and header do.
Can apply this process to anything - text, spacing, colours, etc - using basic CSS. 
================================================== */
function init() {
	window.addEventListener('scroll', function(e){
		var distanceY = window.pageYOffset || document.documentElement.scrollTop,
			shrinkOn = 100,
			header = document.querySelector(".page-title");
		if (distanceY > shrinkOn) {
			classie.add(header,"shrink");
		} else {
			if (classie.has(header,"shrink")) {
				classie.remove(header,"shrink");
			}
		}
	});
}
window.onload = init();