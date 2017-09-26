
// TOUCH-EVENTS SINGLE-FINGER SWIPE-SENSING JAVASCRIPT
// Courtesy of PADILICIOUS.COM and MACOSXAUTOMATION.COM

var photoCount = 8; // the number of images in the slideshow
var fingerCount = 0;
var startX = 0;
var startY = 0;
var curX = 0;
var curY = 0;
var deltaX = 0;
var deltaY = 0;
var horzDiff = 0;
var vertDiff = 0;
var minLength = 72; // the shortest distance the user may swipe
var swipeLength = 0;
var swipeAngle = null;
var swipeDirection = null;
var triggerElementID = null; // this variable is used throughout the script


var imgIndex = 0;
var imgArray = new Array();
var imgText = new Array();

// The 4 Touch Event Handlers

// the touchStart handler should also receive the ID of the triggering element
// make sure it's ID is passed in the event call placed in the element declaration, like:
// <div id="picture-frame" ontouchstart="touchStart(event,'picture-frame');"  ontouchend="touchEnd(event);" ontouchmove="touchMove(event);" ontouchcancel="touchCancel(event);"></div>
function touchStart(event,passedID) {
	// disable the standard ability to select the touched object
	//event.preventDefault();
	// get the total number of fingers touching the screen
	fingerCount = event.touches.length;
	// since we're looking for a swipe (single finger) and not a gesture (multiple fingers),
	// check that only one finger was used
	if ( fingerCount == 1 ) {
		// get the coordinates of the touch
		startX = event.touches[0].pageX;
		startY = event.touches[0].pageY;
		// store the triggering element ID
		triggerElementID = passedID;
	} else {
		// more than one finger touched so cancel
		touchCancel(event);
	}
}

function touchMove(event) {
	//event.preventDefault();
	if ( event.touches.length == 1 ) {
		curX = event.touches[0].pageX;
		curY = event.touches[0].pageY;
	} else {
		touchCancel(event);
	}
}

function touchEnd(event) {
	//event.preventDefault();
	// check to see if more than one finger was used and that there is a and ending coordinate
	if ( fingerCount == 1 && curX != 0 ) {
		// use the Distance Formula to determine the length of the swipe
		swipeLength = Math.round(Math.sqrt(Math.pow(curX - startX,2) + Math.pow(curY - startY,2)));
		// if the user swiped more than the minimum length, perform the appropriate action
		if ( swipeLength >= minLength ) {
			caluculateAngle();
			determineSwipeDirection();
			processingRoutine();
			touchCancel(event);
		} else {
			touchCancel(event);
		}	
	} else {
		touchCancel(event);
	}
}

function touchCancel(event) {
	// reset the variables back to default values
	fingerCount = 0;
	startX = 0;
	startY = 0;
	curX = 0;
	curY = 0;
	deltaX = 0;
	deltaY = 0;
	horzDiff = 0;
	vertDiff = 0;
	swipeLength = 0;
	swipeAngle = null;
	swipeDirection = null;
	triggerElementID = null;
}


		function caluculateAngle() {
			var X = startX-curX;
			var Y = curY-startY;
			var Z = Math.round(Math.sqrt(Math.pow(X,2)+Math.pow(Y,2))); //the distance - rounded - in pixels
			var r = Math.atan2(Y,X); //angle in radians (Cartesian system)
			swipeAngle = Math.round(r*180/Math.PI); //angle in degrees
			if ( swipeAngle < 0 ) { swipeAngle =  360 - Math.abs(swipeAngle); }
		}
		
		function determineSwipeDirection() {
			if ( (swipeAngle <= 45) && (swipeAngle >= 0) ) {
				swipeDirection = 'left';
			} else if ( (swipeAngle <= 360) && (swipeAngle >= 315) ) {
				swipeDirection = 'left';
			} else if ( (swipeAngle >= 135) && (swipeAngle <= 225) ) {
				swipeDirection = 'right';
			} else if ( (swipeAngle > 45) && (swipeAngle < 135) ) {
				swipeDirection = 'down';
			} else {
				swipeDirection = 'up';
			}
			//alert ("angle: "+swipeAngle+" direction: "+swipeDirection);
		}
		
		// the function for performing actions, possibly on the triggering element
		function processingRoutine() {
			//event.preventDefault();
			// for this example, only respond to horizontal swipes
			if ( swipeDirection == 'left' ) {
				event.preventDefault();
				nextImage();
			} 
			else if ( swipeDirection == 'right' ) {
				event.preventDefault();
				prevImage();
			}
			else if ( swipeDirection == 'up' ) {
				return;
			} 
			else if ( swipeDirection == 'down' ) {
				return;
			}
		}
		
		<!-- Picture Load Routines -->
		// images are assumed to be named in this format "X.jpg" where X is a number within the count of images
		// images are assumed to be in a folder named "photos" placed at the same level as this file
		function nextImage()
		{
			//alert ("Next - "+triggerElementID);
			imgIndex++;
			if (imgIndex>(imgArray.length-1)) imgIndex = 0;
			var nextName = imgArray[imgIndex];
			var nextText = imgText[imgIndex];
			//alert ("Next "+nextText);
			document.getElementById(triggerElementID).style.backgroundImage = "url('" + nextName + "')";
			document.getElementById("pictureText").innerHTML = nextText;
		}

		function prevImage()
		{
			//alert ("Next - "+triggerElementID);
			imgIndex--;
			if (imgIndex<0) imgIndex = imgArray.length-1;
			var nextName = imgArray[imgIndex];
			var nextText = imgText[imgIndex];
			//alert ("Next "+nextName);
			document.getElementById(triggerElementID).style.backgroundImage = "url('" + nextName + "')";
			document.getElementById("pictureText").innerHTML = nextText;
		}
		
		function swipe(passedID, next) {
			triggerElementID = passedID;
			if (next) nextImage();
			else prevImage();
		}
		
		function findBaseName(url) {
			var fileName = url.substring(url.lastIndexOf('/') + 1);
			var dot = fileName.lastIndexOf('.');
			return dot == -1 ? fileName : fileName.substring(0, dot);
		}


