/*

	Hide Feedback
	JQUERY to allow users to hide feedback messages when they see them
	
	written by: Phil Thompson phil.thompson@ichameleon.com
	when: 15/06/2007
	
	ToC
	
	- Highlight Current form label
	- show hide form help items


	Create a hide link, then slide up the user feedback when clicked
*/
$(document).ready(
	function() {
		
		// when clicked hide the feedback message box
		$("div.feedback p.hideFeedback a").click(
			function(){
				$('div.feedback').slideUp("slow");
				return("false");
			}
		);
		
	}
);
