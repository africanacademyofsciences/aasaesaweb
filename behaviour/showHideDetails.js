/*

	SHOW/HIDE PAGE DETAILS
	JQUERY to enchnace form usability
	
	written by: Phil Thompson phil.thomposn@ichameleon.com
	when: 06/07/2007
	
	ToC
	


*/







/*
	
*/

$(document).ready(
	function() {
		/* hide details */
		$('#pageDetails').hide();
		/* create link in page Details */
		$('#pageDetails').append('<p id="hideDetails"><a href="#">Hide this page info</a></p>');
		
		/* create link in toolbar */
		$('#toolbar').append('<p id="showDetails"><a href="#">Show page info</a></p>');
		
		
		$('#showDetails a').click(
			function(){
				$('#pageDetails').slideDown("slow");
				$('#showDetails').hide();
			}
		);
		$('#hideDetails a').click(
			function(){
				$('#pageDetails').slideUp("slow");
				$('#showDetails').show();
				/* create link in toolbar */
			}
		);
	}
);
