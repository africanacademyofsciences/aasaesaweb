	
// Speed of the automatic slideshow
var slideshowSpeed = 7000;



$(document).ready(function() {
		
	// Backwards navigation
	$("#header-back").click(function() {
		stopAnimation();
		navigate("back");
	});

	$("#header-first").click(function() {
		stopAnimation();
		navigate("first");
	});
	$("#header-second").click(function() {
		stopAnimation();
		navigate("second");
	});
	$("#header-third").click(function() {
		stopAnimation();
		navigate("third");
	});
	$("#header-fourth").click(function() {
		stopAnimation();
		navigate("fourth");
	});
	$("#header-fifth").click(function() {
		stopAnimation();
		navigate("fifth");
	});




	// Forward navigation
	$("#header-next").click(function() {
		stopAnimation();
		navigate("next");
	});
	
	var interval;
	$("#control").toggle(function(){
		stopAnimation();
	}, function() {
		
		// Change the background image to "pause"
		//$(this).css({ "background-image" : "url(images/btn_pause.png)" });
		
		// Show the next image
		navigate("next");
		
		// Start playing the animation
		interval = setInterval(function() {
			navigate("next");
		}, slideshowSpeed);
	});
	
	
	var activeContainer = 1;	
	var currentImg = 0;
	var animating = false;

	var navigate = function(direction) {
		// Check if no animation is running. If it is, prevent the action
		if (typeof(window['photos'])=="undefined") return;
		
		if(animating) {
			//alert ("animating don't go");
			return;
		}
		
		// Check which current image we need to show
		//alert ("nav in direction("+direction+")");
		if (direction == "first") {
			currentImg = 1;
		}
		else if (direction == "second") {
			currentImg = 2;
		}
		else if (direction == "third") {
			currentImg = 3;
		}
		else if (direction == "fourth") {
			currentImg = 4;
		}
		else if (direction == "fifth") {
			currentImg = 5;
		}
		else if(direction == "next") {
			currentImg++;
			//alert ("set first cur("+currentImg+") nphotos("+photos.length+")");
			if(currentImg == photos.length + 1) {
				currentImg = 1;
			}
		} 
		else {
			currentImg--;
			if(currentImg == 0) {
				currentImg = photos.length;
			}
		}
		
		// Check which container we need to use
		var currentContainer = activeContainer;
		if(activeContainer == 1) {
			activeContainer = 2;
		} else {
			activeContainer = 1;
		}
		
		
		//alert ("nav("+direction+") img["+currentImg+"]");
		showImage(photos[currentImg - 1], currentContainer, activeContainer);
		
	};
	
	var currentZindex = -1;
	var currentZindex = 1000;
	var showImage = function(photoObject, currentContainer, activeContainer) {
		animating = true;
		
		// Make sure the new container is always on the background
		/*
		if(activeContainer == 1)
			currentZindex = 1;
		else 
			currentZindex = 0;
		*/
		currentZindex --;
		
		//alert("showImage("+photoObject+", "+currentContainer+", "+activeContainer+") curz("+currentZindex+")");
		// Set the background image of the new active container
		$("#headerimg" + activeContainer).css({
			"background-image" : "url("+photoObject.image+")",
			"display" : "block",
			"z-index" : currentZindex
		});
		//alert ("set z");
		

		$("#header-first").css('backgroundImage', 'url(\'/store/images/layout/dot.png\')');
		$("#header-second").css('backgroundImage', 'url(\'/store/images/layout/dot.png\')');
		$("#header-third").css('backgroundImage', 'url(\'/store/images/layout/dot.png\')');
		$("#header-fourth").css('backgroundImage', 'url(\'/store/images/layout/dot.png\')');
		$("#header-fifth").css('backgroundImage', 'url(\'/store/images/layout/dot.png\')');
		
		if (currentImg==1) $("#header-first").css('backgroundImage', 'url(\'/store/images/layout/dot-selected.png\')');
		else if (currentImg==2) $("#header-second").css('backgroundImage', 'url(\'/store/images/layout/dot-selected.png\')');
		else if (currentImg==3) $("#header-third").css('backgroundImage', 'url(\'/store/images/layout/dot-selected.png\')');
		else if (currentImg==4) $("#header-fourth").css('backgroundImage', 'url(\'/store/images/layout/dot-selected.png\')');
		else if (currentImg==5) $("#header-fifth").css('backgroundImage', 'url(\'/store/images/layout/dot-selected.png\')');

		// Hide the header text
		$("#headertxt").css({"display" : "none"});
		
		// Set the new header text
		$("#firstline").html(photoObject.title);
		$("#secondline").html(photoObject.secondline);
		$("#fader-price").html(photoObject.price);
		if (photoObject.physical==1) $("#fader-priceinfo").html('+P&amp;P');
		else $("#fader-priceinfo").html('');

		$("#fader-more").html(texts['findmore']);
		
		if (photoObject.detail) {
			$("#firstline").attr("href", photoObject.detail);
			$("#readmore")
				.attr("href", photoObject.detail)
				.html(texts['readmore']);
			$("#fader-more")
				.attr("href", photoObject.detail);
		}
		else $("#secondline").html("");

		// Only one item we can go to the basket
		if (photoObject.basketurl) {
			$("#fader-basket")
				.attr("href", photoObject.basketurl)
				.html(texts['additems']);
		}
		// More than 1 item need to select
		else {
			if (photoObject.detail) {
				$("#fader-basket")
					.attr("href", photoObject.detail)
					.html(texts['selitem']);
			}
			// Hmm odd just show nuffin
			else {
				$("#fader-basket").html("");
			}
		}

		
		// Fade out the current container
		// and display the header text when animation is complete
		//alert ("Fade(headerimg"+currentContainer+")");
		$("#headerimg" + currentContainer).fadeOut(function() {
			setTimeout(function() {
				$("#headertxt").css({"display" : "block"});
				animating = false;
			}, 300);
		});
	};
	
	var stopAnimation = function() {
		// Change the background image to "play"
		$("#control").css({ "background-image" : "url(images/btn_play.png)" });
		
		// Clear the interval
		clearInterval(interval);
	};
	
	// We should statically set the first image
	navigate("next");
	
	// Start playing the animation
	interval = setInterval(function() { navigate("next"); }, slideshowSpeed);
	
});