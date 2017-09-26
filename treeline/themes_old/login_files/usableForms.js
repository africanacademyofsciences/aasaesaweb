/*

	USABLE FORMS
	JQUERY to enchnace form usability
	
	written by: Phil Thompson phil.thomposn@ichameleon.com
	when: 15/06/2007
	
	ToC
	
	- Highlight Current form label
	- show hide form help items

*/







/*
	Highlight the label of the current active form element 
*/

$(document).ready(
	function() {
	
		// when form element is in focus, add a highlight class to it's label ( its previous sibling)
		$("form *").focus(
			function(){
				$(this).prev("label").addClass("highlighted");
			}
		);
		// when form element is out of focus, remove the highlight class from it's label ( its previous sibling)
		$("form *").focus(
			function(){
				$(this).prev().prev("label").addClass("highlighted");
			}
		);

		// when form element is in focus, add a highlight class to it's label ( its previous sibling but one)
		$("form *").blur(
			function(){
				$(this).prev("label").removeClass("highlighted");
			}
		);
		// when form element is out of focus, remove the highlight class from it's label ( its previous sibling but one)
		$("form *").blur(
			function(){
				$(this).prev().prev("label").removeClass("highlighted");
			}
		);
	}
);

/*
	Show/Hide form help items
*/

$(document).ready(
	function() {
		$("div.hasHelp span.help").hide();
		$("fieldset.tags p.tagsHelp").hide();
	
		$("div.hasHelp *").focus(
			function(){
				//$(this).parent().removeClass("hidden");
				$(this).next("span.help").show();
			}
		);
		
	
		

		$("div.hasHelp *").blur(
			function(){
				//$(this).parent().addClass("hidden");
				$(this).next("span.help").fadeOut("slow");
			}
		);
		
		$("fieldset.tags input").focus(
			function(){
				$("fieldset.tags p.tagsHelp").show();
			}
		);
		
		$("fieldset.tags input.tag").blur(
			function(){
				$("fieldset.tags p.tagsHelp").hide();
			}
		);
	}
);
