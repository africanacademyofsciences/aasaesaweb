/*

	Hide Feedback
	JQUERY to allow users to hide feedback messages when they see them
	
	written by: Phil Thompson phil.thompson@ichameleon.com
	when: 15/06/2007
	
	ToC
	
	- Highlight Current form label
	- show hide form help items

*/







/*
	Create a hide link, then slide up the suer feedback when clicked
*/

$(document).ready(
	function() {
		
		// add link to feedback message
		$("div.feedback").prepend('<p class="hideFeedback"><a href="#">Hide this message</a></p>');
		$("div.feedback.error").prepend('<p class="reportBug"><a href="/treeline/bugs/?action=create&bug=bug">Report this as a bug</a></p>');
	
		// when clicked hide the feedback message box
		$("div.feedback p.hideFeedback a").click(
			function(){
				$('div.feedback').slideUp("slow");
				return("false");
			}
		);
		
	}
);
