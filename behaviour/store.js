$(document).ready(function(){
	// get the tallest of all products...
	if( $('ol#product_list') ){
		var maxHeight = 0;
		$('ol#product_list').find('li').each( function(i){
			// if i=0 then that's the 'featured item'
			var paddingOffset = 30; // offsetHeight doesn't consider padding or margins
			var tmpHeight = (this.offsetHeight-paddingOffset);
			if( i>0 && tmpHeight > maxHeight){
				maxHeight = tmpHeight;
			}
		});
		$('ol#product_list').find('li').each( function(i){
			// if i=0 then that's the 'featured item'
			if( i>0 ){										   	
				this.style.height = maxHeight+'px';
			}
		});
	}
	
	// product panels
	if( $('div#productPanels') ){
		var maxHeight = 0;
		$('div#productPanels').find('div.panel').each( function(i){
			// if i=0 then that's the 'featured item'
			var paddingOffset = 15; // offsetHeight doesn't consider padding or margins
			var tmpHeight = (this.offsetHeight-paddingOffset);
			maxHeight = tmpHeight;
		});
		$('div#productPanels').find('div.panel').each( function(i){								   	
			this.style.height = maxHeight+'px';
		});
	}
	
	// product 'about' panel...
	if( $('ul#aboutTabs') ){
		
		$('div#abouttab2').hide(); // hide the second div until we need it
		
		// click the product info tab
		$('ul#aboutTabs li#tab1 a').click(function(){
			// switch div visibility
			$('div#abouttab1').show();
			$('div#abouttab2').hide();
			// switch which tab has an href
			$('ul#aboutTabs li#tab2').attr('href','');
			$('ul#aboutTabs li#tab1').removeAttr('href');
			// remove classes from the ul
			$('ul#aboutTabs').removeClass();
			return false;
		});
		// click the second tab
		$('ul#aboutTabs li#tab2 a').click(function(){
			$('div#abouttab1').hide();
			$('div#abouttab2').show();
			$('ul#aboutTabs li#tab1').attr('href','');
			$('ul#aboutTabs li#tab2').removeAttr('href');
			$('ul#aboutTabs').addClass('leftoff');
			return false;
		});

	}
	
	// Product image swap
	if( $('div.smallImageHolder') ){
		var mainImgSrc = $('div#mainImage').css('background-image');
		
		$('div.smallImageHolder').hover(
			function(){
				var thisBackground = $(this).find('div.productImage').css('background-image');
				// we need to replace it with the next size up!
				var newBackground = thisBackground.replace(/_sm./gi, '_m.');
				$('div#mainImage').css('background-image',newBackground);
			},
			function(){
				//$('div#mainImage').css('background-image',mainImgSrc);
			}
		);
	}
	

});