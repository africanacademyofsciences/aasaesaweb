	
		$(function () {
		// initialize skrollr if the window width is large enough
		if ($(window).width() > 767) {
			var s = skrollr.init();

		//The options (second parameter) are all optional. The values shown are the default values.
		skrollr.menu.init(s, {
			//skrollr will smoothly animate to the new position using `animateTo`.
			animate: true,
		
			//The easing function to use.
			easing: 'sqrt',
		
			//Multiply your data-[offset] values so they match those set in skrollr.init
			scale: 2,
		
			//How long the animation should take in ms.
			duration: function(currentTop, targetTop) {
				//By default, the duration is hardcoded at 500ms.
				return 1000;
		
				//But you could calculate a value based on the current scroll position (`currentTop`) and the target scroll position (`targetTop`).
				return Math.abs(currentTop - targetTop) * 10;
			}
		});
		}
		else
		{
			//$('#our-impact-laboke').css('height', 'auto');
			//$('#our-impact-laboke').removeClass('fullheight');
		}

		// disable skrollr if the window is resized below 768px wide
		$(window).on('resize', function () {
		if ($(window).width() <= 767) {
		skrollr.init().destroy(); // skrollr.init() returns the singleton created above
		}
		});
		});
 		
	
    	


/*! viewportSize | Author: Tyson Matanich, 2013 | License: MIT */
(function(n){n.viewportSize={},n.viewportSize.getHeight=function(){return t("Height")},n.viewportSize.getWidth=function(){return t("Width")};var t=function(t){var f,o=t.toLowerCase(),e=n.document,i=e.documentElement,r,u;return n["inner"+t]===undefined?f=i["client"+t]:n["inner"+t]!=i["client"+t]?(r=e.createElement("body"),r.id="vpw-test-b",r.style.cssText="overflow:scroll",u=e.createElement("div"),u.id="vpw-test-d",u.style.cssText="position:absolute;top:-1000px",u.innerHTML="<style>@media("+o+":"+i["client"+t]+"px){body#vpw-test-b div#vpw-test-d{"+o+":7px!important}}<\/style>",r.appendChild(u),i.insertBefore(r,e.head),f=u["offset"+t]==7?i["client"+t]:n["inner"+t],i.removeChild(r)):f=n["inner"+t],f}})(this);

/**
 * How to create a parallax scrolling website
 * Author: Petr Tichy
 * URL: www.ihatetomatoes.net
 * Article URL: http://ihatetomatoes.net/how-to-create-a-parallax-scrolling-website/
 */
if ($(window).width() > 767) {
( function( $ ) {
	
	// Setup variables
	$window = $(window);
	$slide = $('section.fullheight');
	$slideTall = $('section.halfheight');
	$body = $('body');
	
    //FadeIn all sections   
	$body.imagesLoaded(function() {
		setTimeout(function() {
		      // Resize sections
		      adjustWindow();

		}, 100);
	});
	
	$window.resize(function() {
		adjustWindow();
	});
	
	function adjustWindow(){
		
		// Init Skrollr
		var s = skrollr.init({
		    render: function(data) {
		    
		        //Debugging - Log the current scroll position.
		        //console.log(data.curTop);
		    }
		});
		
		// Get window size
	    winH = $window.height();
	    
	    // Keep minimum height 550
	    if(winH <= 550) {
			winH = 550;
		} 
	    
	    // Resize our slides
	    $slide.height(winH);
	    $slideTall.height(winH/2);
	    
	    // Refresh Skrollr after resizing our sections
	    s.refresh($('.fullheight'));
	    
	}
		
} )( jQuery );
}