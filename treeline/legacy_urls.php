<?php
/*
	Legacy URLs
	
	Each site we launch will have existing URLs that are stored as bookmarks, in personal emails and search engines
	We need to redirect these URLs to the new Treeline URLs using 301 headers
	
	*****The two arrays old_urls and new_urls will be differenbt for EVERY site*****
*/



	// old system URLs
	$old_urls = array(
	'/index.asp?PageID=2' /* About us */
	);
	
	//'', /* */
	
	// New Treeline URLs
	$new_urls = array(
	'/about-us/' /* About us */
	);
	
	// redirect the home page
	if($request == '/index.asp'){
		// Then send a 301 redirect
		header ('HTTP/1.1 301 Moved Permanently');
		// and send the visitor to the new homepage
		redirect('/');
	} 
	// check if the URL is in the old_urls array
	else if(in_array($request,$old_urls)){
		// if it is, replace it with its new URL
		$request = str_replace($old_urls,$new_urls,$request);
		// Then send a 301 redirect
		header ('HTTP/1.1 301 Moved Permanently');
		// and send the visitor to the new URL
		redirect($request);
	}
	
?>