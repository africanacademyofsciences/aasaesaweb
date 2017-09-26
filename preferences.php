<?php


if ($_GET['redline']==1) $global_redline = 1;
//$global_redline = 1;	// While testing have this on permenantly.

// Current page location without the query string
if ($page->getGUID()) define('CURRENT_PAGE', $page->drawLinkByGUID($page->getGUID()));
else define('CURRENT_PAGE', rtrim($_SERVER['REQUEST_URI'], '?'.$_SERVER['QUERY_STRING']));

// Minor fix to allow us to force palate and font CSS
if ($_GET['palate']>0) $_SESSION['palate']=$_GET['palate'];
$palateNumber = $_SESSION['palate']?$_SESSION['palate']:$site->palate;
if ($_GET['fontno']>0) $_SESSION['fontno']=$_GET['fontno'];
$fontNumber = $_SESSION['fontno']?$_SESSION['fontno']:$site->font;


/* Run this to check if the user is trying to
toggle the graphics mode. If they are, the new
mode is saved in a cookie. */
function graphics_mode()
{
	if ($_SERVER['QUERY_STRING'] == 'toggle_graphics')	// We want to change the graphics mode
	{
		$graphics_mode = ($_COOKIE['graphics_mode'] == 'low') ? 'high' : 'low';	// Toggle graphics mode
		setcookie('graphics_mode', $graphics_mode, time()+31536000, '/');		// Store the selected mode in a cookie
		header('Location: '.CURRENT_PAGE);									// Reload the page so new cookie headers are read in
		exit;														// Make sure that no more code gets executed
	}
}
graphics_mode();







/* Output buffer is stripped of images elements,
and left with just the alt tags. */
function low_graphics($buffer)
{
	$buffer = preg_replace('/(<img[^>]+alt=")([^"]*)("[^>]*>)/i', '[Image: $2]', $buffer);
	return $buffer;
}
if ($_COOKIE['graphics_mode'] == 'low' && $mode!="edit")
{
	ob_start("low_graphics");
}



// Run this to save a font size in a cookie
function font_size($font_size)
{
	// Don't let any old thing be saved in the cookie
	$allowed_font_sizes = array
	(
		'xx-small',
		'x-small',
		'small',
		'medium',
		'large',
		'x-large',
		'xx-large'
	);
	if (in_array($font_size, $allowed_font_sizes))	// We want to change the font size
	{
		setcookie('font_size', $font_size, time()+31536000, '/');	// Store the selected size in a cookie
		header('Location: '.CURRENT_PAGE);						// Reload the page so new cookie headers are read in
		exit;											// Make sure that no more code gets executed
	}
}
font_size($_GET['font_size']);

?>