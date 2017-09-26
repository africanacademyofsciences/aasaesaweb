// JavaScript  jQuery

/*

	clickableAreas.js
	
	Turn items (usually <div>s) with a class of 'clickable'
	and allows the user to click that div and go to the 
	location of the 1st link within it.

*/

/* click effect */

$(document).ready(
	function() {
		$('.clickable[a]').click(
			function(){
				/* locate value of first link within the clickable area */
				var divLink = $(this).find('a:first').attr("href");	
				/* redirect user */
				window.location = divLink;
			}
		);
	}
);

/* (un)hover effect */
$(document).ready(
	function() {
		$('.clickable[a]').hover(
			function(){
				$(this).addClass('hover');
			}
		,
		function(){
				$(this).removeClass('hover');
			}
		);
								 
	}
);