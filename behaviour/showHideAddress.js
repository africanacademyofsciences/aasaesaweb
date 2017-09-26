$(document).ready(
	function() {
		/* hide details */
		$('#extraContactDetails').hide();
		
		$('#contactDetailsHelp').append(' <a href="#extraContactDetails" id="showExtraDeatils">Add your own contact details</a> ');
		
		$('#showExtraDeatils').click(
			function(){
				$('#extraContactDetails').show();
			}
		);
		
	}
);
