// Make all blocks (divs) on one page the same height as the tallest one (when faux columns can't be used)

function getHeights(){
	//check for standards compliance
	if(!document.getElementById) return;
	if(!document.getElementsByTagName) return;
	
	var maxH
	if (maxH=getMaxHeight("landing-1")) setHeights("landing-1", maxH)
	if (maxH=getMaxHeight("landing-2")) setHeights("landing-2", maxH);
	if (maxH=getMaxHeight("landing-3")) setHeights("landing-3", maxH)
	if (maxH=getMaxHeight("landing-4")) setHeights("landing-4", maxH);
	if (maxH=getMaxHeight("landing-5")) setHeights("landing-5", maxH)
	if (maxH=getMaxHeight("landing-6")) setHeights("landing-6", maxH);
}
	
	
function getMaxHeight(type) {
	var maxHeight=0;
	var elementId=type
	//alert("check id("+elementId+")");
	var holder = document.getElementById(elementId);

	// Does this page contain this holder???
	if (holder) {
	
		var blocks = holder.getElementsByTagName("div");
		for(var i = 0; i < blocks.length; i++){
			//alert("found block("+i+") class("+blocks[i].className+")");
			if(blocks[i].className.substr(0,7) == 'landing') { // we only want <div class="landing-panel">
				height = blocks[i].offsetHeight;
				//alert ("got "+elementId+"  height("+height+")");
				if (height>maxHeight) maxHeight=height;
			}
		}
	
	}
	
	//alert ("got max("+maxHeight+") for "+type);	
	return maxHeight;
}

// make all divs the same height in pixels. must be run on window resize, text increase/decrease. (a ballache basically.)
function setHeights(type, height){
	var elementId=type;
	var holder = document.getElementById(elementId);
	
	if (!holder) alert ("Failed to get holder for "+type);
	else {
		var blocks = holder.getElementsByTagName("div");
		for(var i = 0; i < blocks.length; i++){
			if(blocks[i].className.substr(0,7) == 'landing') { // we only want <div class="block">
				blocks[i].style.height=height+"px";
			}
		}
	}
}

//addEvent(window,"load",getHeights);