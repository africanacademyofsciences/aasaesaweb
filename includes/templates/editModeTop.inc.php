<?php

if ($mode == 'edit') {

	require_once ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/help.class.php");
	
	// Is there a neater way to do this, rather than sandwiching the page in form tags like this?
	$dbqs = ($DEBUG)?'?debug':'';
	$fmAction = str_replace("&", "&amp;", $_SERVER['REQUEST_URI']).$dbqs;
	// If we're debugging, apend ?debug to the form post
	echo '<form action="'.$fmAction.'" method="post" id="treeline_edit">
<fieldset>
<input type="hidden" name="mode" value="'.$mode.'" />
<input type="hidden" name="referer" value="'.$referer.'" />
<input type="hidden" name="post_action" value="" />
';

	$currentStyle = ($_POST['style']) ? $_POST['style'] : $page->style_id;
	//print "got style($currentStyle)<br>\n";
	//print "got toolmode($toolmode)<br>\n";
	echo $page->drawToolbar($currentStyle, $disablePageStyle, $toolmode?$toolmode:'page');
	
}
	
?>