// Make all blocks (divs) on one page the same height as the tallest one (when faux columns can't be used)

function getHeights(){
	//check for standards compliance
	if(!document.getElementById) return;
	if(!document.getElementsByTagName) return;
	
	// If this is a product page try to set the right column
	var holder = document.getElementById("product");
	if (holder) {
		var imgdiv = document.getElementById("mainImage");	
		var detaildiv = document.getElementById("productDetail");	
		var exHeight = imgdiv.offsetHeight - detaildiv.offsetHeight;
		//alert ("there is a "+exHeight+" difference img("+imgdiv.offsetHeight+") detai("+detaildiv.offsetHeight+")");
		if (exHeight > 0) {
			var baskettitle = document.getElementById("basketTitle");
			baskettitle.style.marginTop = (exHeight+20)+"px";
		}
	}
	
	
	// If its a shop listing set heights for all rows.
	var maxH
	if (maxH=getMaxHeight("1")) setHeights("1", maxH)
	else return;
	if (maxH=getMaxHeight("2")) setHeights("2", maxH);
	else return;
	if (maxH=getMaxHeight("3")) setHeights("3", maxH);
	else return 
	if (maxH=getMaxHeight("4")) setHeights("4", maxH)
	else return 
	if (maxH=getMaxHeight("5")) setHeights("5", maxH)
	else return 
	if (maxH=getMaxHeight("6")) setHeights("6", maxH)
	else return 
	if (maxH=getMaxHeight("7")) setHeights("7", maxH)
	else return 
	if (maxH=getMaxHeight("8")) setHeights("8", maxH)
	else return 
}
	
	
function getMaxHeight(rownum) {
	var maxHeight=0;
	var elementId="store_list_"+rownum
	//alert("check id("+elementId+")");
	var holder = document.getElementById(elementId);

	// Does this page contain this holder???
	if (!holder) return;
	
	var lis = holder.getElementsByTagName("li");
	for(var j = 0; j < lis.length; j++){
		//alert("found li("+j+") class("+lis[j].className+")");
		var thisheight = 0;
		var blocks = lis[j].getElementsByTagName("div");
		for(var i = 0; i < blocks.length; i++){
			//alert("found li("+j+")block("+i+") class("+blocks[i].className+")");
			if(blocks[i].className == 'productDetail') {
				height = blocks[i].offsetHeight;
				thisheight += height;
			}
			if(blocks[i].className == 'productPurchase') {
				height = blocks[i].offsetHeight;
				thisheight += height;
			}
			/*
			// USE THIS BLOCK IF LISTINGS CAN HAVE DIFFERENT SIZE IMAGES
			if (blocks[i].className == 'productImage productImageSmall'){ // we only want <div class="block">
				height = blocks[i].offsetHeight;
				//alert ("li ("+j+") image height is ("+height+") style("+blocks[i].style.height+")");
				thisheight += height;
			}
			*/
			if (thisheight>maxHeight) maxHeight=thisheight;
			//alert ("li("+j+") height("+thisheight+") max("+maxHeight+")");
		}
	}
	
	//alert ("GOT MAX("+maxHeight+") for "+rownum);	
	return maxHeight;
}

// make all divs the same height in pixels. must be run on window resize, text increase/decrease. (a ballache basically.)
function setHeights(type, height){
	var elementId="store_list_"+type
	var holder = document.getElementById(elementId);
	
	// This should really be impossible as the element had
	// to exist for us to have called this function in the first place.
	if (!holder) {
		//alert ("Failed to get holder for "+type);
		return ;
	}

	//var blocks = holder.getElementsByTagName("div");

	var lis = holder.getElementsByTagName("li");
	for(var j = 0; j < lis.length; j++){

		var thisheight = 0;
		var blocks = lis[j].getElementsByTagName("div");
		
		for(var i = 0; i < blocks.length; i++){

			//alert("set height li("+j+") block("+i+") class("+blocks[i].className+")");
			// Add productImageSmall if we need image height too
			if(blocks[i].className == 'productDetail' || 
					blocks[i].className=="productPurchase")
				{ // we only want <div class="block">
				thisheight += blocks[i].offsetHeight;
				//alert("currently("+thisheight+") height("+height+")");
				// Only adjust height when looking at productinfo
				if (blocks[i].className=='productPurchase' && thisheight < height) {
					var exHeight = height - thisheight;
					blocks[i].style.marginTop = exHeight+"px";
					/*
					var paras = blocks[i].getElementsByTagName("p");
					for(var k=0; k<paras.length; k++) {
						//alert("got li("+j+") block("+i+") para("+k+") class("+paras[k].className+") add ("+exHeight+")px ");
						if (paras[k].className=="variants adjustable") {
							paras[k].style.marginTop=exHeight+"px";
						}
					}
					*/
				}
			}
		}
	}

}

//addEvent(window,"load",getHeights);