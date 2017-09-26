<?php

$pathinfo = pathinfo($_SERVER['PHP_SELF']);
$extension = (isset($_GET['extension'])) ? $_GET['extension'] : $pathinfo['extension'];
//$headers = array();

if ($pathinfo['basename']=="generate.php" ||
	$pathinfo['basename']=="download.php" ||
	$pathinfo['basename']=="securimage_show.php" ||
	$pathinfo['basename']=="emailtrack.php")
{
	; // FLIR sends its own headers
}
else {


	// These filetypes are run through the .htaccess code.  Here we reset their mime type to what it should be...
	switch( $extension ){
		case 'css':
			header("Content-type: text/css");
			break;
		case 'js':
			header("Content-type: text/javascript");
			break;
		case 'gif':
			//header("Content-type: image/gif");
			break;
		case 'jpg':
			header("Content-type: image/jpeg");
			break;
		case 'png':
			header("Content-type: image/png");
			break;
		default:
			header("Content-type: text/html");
			break;
	}



	//header('Content-Length: '. filesize($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']));
	header('Pragma: public',true);
	header('Vary: User-Agent',true);
	header('X-Powered-By: Treeline',true);
	
	// If the server supports it, use GZIP compression
	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip")>0){
		header("Content-Encoding: gzip, deflate",true);
	}
	// set the expiry date of the page to be a year from now - this ensures that the cache will stay until the page has been updated.
	header('Expires: '. date('D, j M Y H:i:s',mktime(date('H'),date('i'),01,date('n'),date('j'),date('Y')+1)) .' GMT');
	// This tells the client to use their private local cache - you'll often see this set to cache-control: no-cache
	header('Cache-control: public');
	header("Cache-Control: max-age=315360000");
	
	unset($extension);

}
?>