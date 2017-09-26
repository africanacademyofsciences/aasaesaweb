jQuery.fn.vjustify=function() {
    var maxHeight=0;
    this.each(function(){
        if (this.offsetHeight>maxHeight) {maxHeight=this.offsetHeight;}
    });
    this.each(function(){
        $(this).height(maxHeight + "px");
        if (this.offsetHeight>maxHeight) {
            $(this).height((maxHeight-(this.offsetHeight-maxHeight))+"px");
        }
    });
};

$(document).ready(
	function() {
		$("#midholder h3.column_h3").vjustify();
		$("#primarycontent .column").vjustify(); 
		$("#story .column").vjustify();
		$("#secondarycontent .column").vjustify();
		$("#tertiarycontent .column").vjustify();
		
	}
);