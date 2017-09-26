/* JavaScript Document */

function prepareForm(){
	var selectCSS = document.getElementById("style");
	
	var allCSS;
	/* get all stylesheets */
	
   	for(var i=0; (allCSS = document.getElementsByTagName("link")[i]); i++) {
		if(allCSS.getAttribute("rel") == 'stylesheet') {
		  allCSS.disabled = false; /* disable all stylesheets that can be altered */
		}
	}
	
	selectCSS.onchange = function(){
		/* updateCSS(this.value); */
		var CSS = document.getElementById('CSS'+this.value);
		setActiveStyleSheet(CSS.getAttribute("title"));
	}
}

function updateCSS(value){
	/* alert(value);	 */
	var CSS = document.getElementById("CSS"+value);
	var title = CSS.getAttribute("title");
	var a;
	/* get all stylesheets */
   	for(var i=0; (a = document.getElementsByTagName("link")[i]); i++) {
		if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
		  	a.disabled = true; /* disable all stylesheets that can be altered */
			if(a.getAttribute("title") == title) a.disabled = false;
		}
  }

	if(CSS.getAttribute("rel") == 'alternate stylesheet'){
			CSS.disabled = false; /* enable the newly selected stylesheet */
	}
	
	
	/* find alternate stylesheet whose title is the same as this supplied value */
	
	/* change rel="stylesheet" to rel="alernate stylesheet" */
	
	/* change rel to rel="stylesheet" */
	
	
}

function changeCSS(v) {
	setActiveStyleSheet(v);
}

/* Functions below this point are taken from http://www.alistapart.com/articles/alternate/ by Paul Sowden. Thanks. */

function setActiveStyleSheet(title) {
  var i, a, main;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
      a.disabled = true;
      if(a.getAttribute("title") == title) a.disabled = false;
    }
  }
}

function getActiveStyleSheet() {
  var i, a;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && !a.disabled) return a.getAttribute("title");
  }
  return 'default';
}

function getPreferredStyleSheet() {
  var i, a;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1
       && a.getAttribute("rel").indexOf("alt") == -1
       && a.getAttribute("title")
       ) return a.getAttribute("title");
  }
  return null;
}
function unload(e){
	var title = getActiveStyleSheet();
}



$(document).ready(function() { 
	prepareForm ();
});

$(document).unload(function() { 
	unload();
});