// Basic functionality such as printing and bookmarking...

$(document).ready(function() {
	$("a#print_page").click(function() {
		window.print();
		return false;
	});
	
	$("a#bookmark_link").click(function() {
		var url = location.href;
		var title = document.title;

		if (window.sidebar) { // Mozilla Firefox Bookmark
			window.sidebar.addPanel(title, url,"");
		} else if( window.external ) { // IE Favorite
			window.external.AddFavorite( url, title); }
		else if(window.opera && window.print) { // Opera Hotlist
			return true; 
		}
	});

	
});