/* Textarea character limit */
var textareaMaxCharsMsg=' characters left.';

var startstr = "Maximum "; /* + (number) + */
var endstr = " characters.";

var keysLimtString = "There is a limit of "; /* + (number) + */
var keysLimitEnd = " characters for this field. \r\n\r\nSome text may have been lost, please check what you have entered."

var textareaLimitMsg = 'You have reached the limit of '; /* + (number) + */
var textareaLimitEnd = ' characters for this field';




function textAreaMaxLength(){
	if (!document.getElementById) return false; 
	
	// for each textarea
	var formTextareas = document.getElementsByTagName("textarea");	
 	for (var i=0; i < formTextareas.length; i++) { 
	
		
		// find the maxlength from the span 
		var siblings = formTextareas[i].nextSibling;
    	while (siblings) { 		
			for( var x = 0; siblings.childNodes[x]; x++ ){	
		 		 if (siblings.childNodes[x].className=="maxlength") {		
					
					
					formTextareas[i].onchange = function(){
						
						// get the maxlength value from this textareas note
						var siblings2 = this.nextSibling;
						while (siblings2) { 	
							for( var j = 0; siblings2.childNodes[j]; j++ ){	
						 		if (siblings2.childNodes[j].className=="maxlength") {	

								 	maxKeys = siblings2.childNodes[j].firstChild.nodeValue;
								}
							}
							siblings2 = siblings2.previousSibling;	
						}			
						
						var theInput = new String(this.value);
						
						if(theInput.length > maxKeys){
							alert(keysLimtString + maxKeys + keysLimitEnd);
							this.value = this.value.substring(0,maxKeys);
							startstr = startstr;
							endstr = endstr;
							swapPTag(this,maxKeys,startstr,endstr,0);
						}
					} 
					
					
					// assign a function to the keyup event handler for this textarea
					formTextareas[i].onkeyup = function(){
						
						
						// get the maxlength value from this textareas note
						var siblings2 = this.nextSibling;
						while (siblings2) { 	
							for( var j = 0; siblings2.childNodes[j]; j++ ){	
						 		if (siblings2.childNodes[j].className=="maxlength") {	

								 	maxKeys = siblings2.childNodes[j].firstChild.nodeValue;
								}
							}
							siblings2 = siblings2.previousSibling;
						}			
						
						// create the output string
						var str = new String(this.value);
						var remaining = maxKeys - str.length;
						if(remaining <= 0){
							//alert('none left');
							this.value = this.value.substring(0,maxKeys);
							
						}
						//var showstr = len + " characters of ";

						var endstr = remaining + textareaMaxCharsMsg;
						if (remaining < 0){ endstr = textareaLimitMsg + maxKeys + textareaLimitEnd;}
						
						swapPTag(this,maxKeys,'',endstr,1);
					}	
				}
			}	
			siblings = siblings.previousSibling;
	  	}
     }
}
function swapPTag(currentItem,maxKeys,startString,endString,hideThis){
	
	// find p.note and swap it for a new one containg the output string and maxlength (hidden)
	var temp = currentItem.parentNode.getElementsByTagName("em");	
	for( var k = 0; temp[k]; k++ ){	
 		if (temp[k].className.indexOf('maxLength') != -1) {
			//alert(temp[k].className);
			var theNewParagraph = document.createElement('em');
			theNewParagraph.className = 'note maxLength';
			
			var theOpeningText = document.createTextNode(startString);
			var textSpan = document.createElement('span');
			textSpan.className = 'maxlength';
			var spannedOpeningText = textSpan.appendChild(theOpeningText);
			theNewParagraph.appendChild(spannedOpeningText);	
			
			var theNewSpan = document.createElement('span');
			theNewSpan.className = "maxlength";
			if(hideThis){
				theNewSpan.style.display = 'none';
			}
			var theSpan = document.createTextNode(maxKeys);
			theNewSpan.appendChild(theSpan);
			
			theNewParagraph.appendChild(theNewSpan);
			
			var theClosingText = document.createTextNode(endString);
			theNewParagraph.appendChild(theClosingText);
			
			temp[k].parentNode.replaceChild(theNewParagraph,temp[k]);
		}	
	}
}

addEvent(window,"load",textAreaMaxLength);