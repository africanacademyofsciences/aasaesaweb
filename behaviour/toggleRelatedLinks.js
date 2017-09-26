/*

	Hide related items form extra when the checkbox is checked.

	taken mostly from:
	http://www.iamzed.com/2006/12/14/using-jquery-to-show-hide-form-elements-based-on-a-checkbox-selection/

*/



$(document).ready(function(){
	
	if ($("#show_related_content").is(":checked"))
	{
		//show the hidden div
		$("#related_options").show("fast");
	}
	else
	{      
		//otherwise, hide it
		$("#related_options").hide("fast");
	}

		// Add onclick handler to checkbox w/id checkme
   $("#show_related_content").click(function(){
	// If checked

	if ($("#show_related_content").is(":checked"))
	{
		//show the hidden div
		$("#related_options").show("fast");
	}
	else
	{      
		//otherwise, hide it
		$("#related_options").hide("fast");
	}
  });
});
