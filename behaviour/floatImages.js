// JavaScript Document

$(document).ready(
	function() {
		
		$('#midholder img[@align*=left]').addClass("left").removeAttr("align");
		$('#midholder img[@align*=right]').addClass("right").removeAttr("align");
	}
);