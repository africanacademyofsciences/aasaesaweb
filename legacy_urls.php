<?php
/*
	Legacy URLs
	
	Each site we launch will have existing URLs that are stored as bookmarks, in personal emails and search engines
	We need to redirect these URLs to the new Treeline URLs using 301 headers
	
	*****The two arrays old_urls and new_urls will be differenbt for EVERY site*****
*/



	// old system URLs
	$old_urls = array(
	'/index.asp?PageID=29&DuplicateID=151', /* Privacy policy*/
	'/index.asp?PageID=30&DuplicateID=7', /* Terms and conditions */
	'/index.asp?PageID=30&DuplicateID=8', /*Credits */
	'/index.asp?PageID=24&DuplicateID=2', /* Jobs */
	'/index.asp?PageID=26&DuplicateID=149', /* Glossary  */
	'/index.asp?PageID=25&DuplicateID=4', /* Help */
	'/index.asp?PageID=2', /* About us */
	'/index.asp?PageID=10', /* News */
	'/index.asp?PageID=27', /* Contact us */
	'/index.asp?PageID=33', /* Search */
	'/index.asp?PageID=28', /* Site map */
	'/index.asp?PageID=12', /* Projects */
	'/index.asp?PageID=41', /* Speaking out */
	'/index.asp?PageID=16', /* Events */
	'/index.asp?PageID=41', /* Donate */
	'/index.asp?PageID=73', /* Shop */
	'/index.asp?PageID=95', /* Flying Doctors */
	'/index.asp?PageID=111', /* African Medical kits */
	);
	
	//'', /* */
	
	// New Treeline URLs
	$new_urls = array(
	'/privacy-policy/',/* Privacy policy */
	'/terms-and-conditions/',/* Terms and conditions */
	'/credits/',/* Credits */
	'/get-involved/jobs/',/* Jobs */
	'/glossary/',/* Glossary */
	'/help/',/* Help */
	'/about-us/', /* About us */
	'/news/',/* News */
	'/contact-details/',/* Contact details */
	'/search/',/* Search */
	'/site-map/', /* Site map */
	'/what-we-do/',/* Projects */
	'/what-we-do/',/* Speaking out */
	'/what-we-do/', /* Events */
	'/donate/',/* Donate */
	'/', /* Shop */
	'/what-we-do/flying-doctors/', /* flying doctors*/
	'/what-we-do/', /* African Medical kits */
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