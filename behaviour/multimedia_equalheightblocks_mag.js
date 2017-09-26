// Make all blocks (divs) on one page the same height as the tallest one (when faux columns can't be used)

function getHeights(){
	//check for standards compliance
	if(!document.getElementById) return;
	if(!document.getElementsByTagName) return;
	
	var maxH
	if (maxH=getMaxHeight("reports")) setHeights("reports", maxH)
	if (maxH=getMaxHeight("galleries")) setHeights("galleries", maxH);
	if (maxH=getMaxHeight("videos")) setHeights("videos", maxH);
	if (maxH=getMaxHeight("reports_l1")) setHeights("reports_l1", maxH)
	if (maxH=getMaxHeight("galleries_l1")) setHeights("galleries_l1", maxH);
}
	
	
function getMaxHeight(type) {
	var maxHeight=0;
	var elementId="panel_holder_"+type
	//alert("check id("+elementId+")");
	var holder = document.getElementById(elementId);

	// Does this page contain this holder???
	if (holder) {
	
		var blocks = holder.getElementsByTagName("div");
		for(var i = 0; i < blocks.length; i++){
			//alert("found block("+i+") class("+blocks[i].className+")");
			if(blocks[i].className == 'panel' || blocks[i].className == 'panel first'){ // we only want <div class="block">
				height = blocks[i].offsetHeight;
				//alert ("got "+elementId+"  height("+height+")");
				if (height>maxHeight) maxHeight=height;
			}
		}
	
		// Check height of right hand div if its there.
		holder=document.getElementById("panel_right_"+type);
		if (holder) {
			var blocks = holder.getElementsByTagName("div");
			for(var i = 0; i < blocks.length; i++){
				if(blocks[i].className == 'panel'){ // we only want <div class="block">
					height = blocks[i].offsetHeight;
					//alert ("got "+elementId+"  height("+height+")");
					if (height>maxHeight) maxHeight=height;
				}
			}
		}

	}
	
	//alert ("got max("+maxHeight+") for "+type);	
	return maxHeight;
}

// make all divs the same height in pixels. must be run on window resize, text increase/decrease. (a ballache basically.)
function setHeights(type, height){
	var elementId="panel_holder_"+type
	var holder = document.getElementById(elementId);
	
	if (!holder) alert ("Failed to get holder for "+type);
	else {
		var blocks = holder.getElementsByTagName("div");
		for(var i = 0; i < blocks.length; i++){
			if(blocks[i].className == 'panel' || blocks[i].className == 'panel first'){ // we only want <div class="block">
				blocks[i].style.height=height+"px";
			}
		}
		holder=document.getElementById("panel_right_"+type);
		if (holder) {
			var blocks = holder.getElementsByTagName("div");
			for(var i = 0; i < blocks.length; i++){
				if(blocks[i].className == 'panel'){ // we only want <div class="block">
					blocks[i].style.height=height+"px";
				}
			}
		}
	}

}

//addEvent(window,"load",getHeights);