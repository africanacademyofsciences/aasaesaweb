/* ==================================================
This script makes the defined element (in this case ".valign") vertically align within it's parent.
Add .valign to any element to make this work. For example <h3 class="valign">...</h3>
================================================== */
(function ($) {
  // VERTICALLY ALIGN FUNCTION
  $.fn.vAlign = function () {
	  return this.each(function (i) {
		  var ah = $(this).height();
		  var ph = $(this).parent().height();
		  var mh = Math.ceil((ph - ah) / 2);
		  $(this).css('top', mh);
	  });
  };
})(jQuery);
$('.valign').vAlign();