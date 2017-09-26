<?php 

// FILE addEditTags
// Created September 2008 
// Author: Phil Redclift

// Purpose
// This file attempts as much as possible to abstract the adding of tags from tag libraries
// and the javascript auto complete functionality

// Global data.
// It is important that the variable $pagetagslist is used in the main files to contain the current
// list of tag IDs for the page in comma separated format.




//print "got act($action) sub($subaction) list($pagetagslist)<br>";


// Collect any tag list we have been passed.
$pagetagslist=read($_REQUEST, "pagetagslist", ',');
//print "got read list($pagetagslist) act($action)<br>";

// In edit mode we can populate and empty tags list.
if(!isset($_REQUEST['pagetagslist'])
 && ($action=="edit" || $action=="editrss")){
	$pagetagslist = $tags->drawTags($guid, "csvbyid");
}
//print "got list($pagetagslist)<br>";

// This should only be called in POST mode if a new tag has been added to the page
// Before we can show the new tag in the list we must ensure it exists in the library.
if ($_SERVER['REQUEST_METHOD']=="POST") { 
		
	//print "post(".print_r($_POST, true).")<br>\n";		
	$message=array();
	$feedback='';
	//if (strtolower($_POST['tagaction'])=="ok, add another") {
	if ($_POST['newtag']) {
		$newtag=$_POST['newtag'];
		if ($tag_id=$db->get_var("SELECT id FROM tags WHERE tag='".strtolower($db->escape($newtag))."'")) {
			$pagetagslist.=$tag_id.",";
		}
		else {
			echo drawFeedback("error", $newtag." ".$page->drawLabel("tl_tags_err_valid", "is not a valid tag"));
		}
		// Set tags updated to allow the page to autoscroll to the tags list.
		$tagsupdated=true;
	}
	else if (substr($_POST['tagaction'],0,7)=="remove-") {
		$tagaction="remove tag";
		$remove_id=substr($_POST['tagaction'],7);
		//print "got list $pagetagslist<br>remove $remove_id<br>";
		$pagetagslist=str_replace(",".$remove_id.",", ",", $pagetagslist);
		$tagsupdated=true;
		//print "got list $pagetagslist<br>";
	}		

}

// If we have added or removed a tag from the list we want to auto scroll the page
// back down to the tags list to avoid the user having to keep relocating their
// place on the page.
if ($tagsupdated) $extraBottomJS.='window.location=\'#tagslist\';';

$tags_html='

<style type="text/css">
div.auto_complete {
	border:1px solid #000;
	width:300px;
}
div.auto_complete ul {
	border:0px solid #888888;
	list-style-type:none;
	margin:0pt;
	padding:0pt;
	width:100%;
}
div.auto_complete ul li {
	margin:0;
	padding:0;
	padding-left:4px;
	background-color:#FFF;
	opacity:1;
}
div.auto_complete ul li.selected {
	background-color:#FFFFBB;
}
</style>
<script type="text/javascript" src="/behaviour/ajax/script.aculo.us/prototype.js" ></script>
<script type="text/javascript" src="/behaviour/ajax/script.aculo.us/scriptaculous.js" ></script>


    <input type="hidden" name="pagetagslist" value ="'.$pagetagslist.'" />
    <div id="tagsHolder">
    <div id="tagsElement" style="float:left;clear:left;">
        <label for="ssearchTag" style="width:110px;">'.$page->drawLabel("tl_pedit_field_tags", "Add tags").$help->drawSmallPopupByID(113).'</label>
        <div id="eggTimerHolder">
            <div id="eggTimer" style="display: none;width: 23px; height: 23px; padding: 3px;"><img src="/behaviour/ajax/hourglass.png" alt="" width="16" height="16" /></div>
        </div>
        <input type="text" name="newtag" class="autoText" autocomplete="off" id="ssearchTag" />
        <input type="submit" name="tagaction" id="tagbutton" class="cancel" value="'.$page->drawLabel("tl_pedit_but_tags", "Ok, add another").'" />
    </div>
    <div class="auto_complete" id="ssearch_auto_complete" style="display:none;"></div>
';
if ($tags->drawAdminTags($pagetagslist, "remove")) { 
	$tags_html.='
    <div id="tagsListElement" style="float:left;clear:left;width:600px;">
        <label for="tagslist" style="visibility:hidden;">'.$page->drawLabel("tl_pedit_tags_page", "Tags on this page").'</label>
        <p style="float:left;clear:none;width:450px;">'.$tags->drawAdminTags($pagetagslist, "remove").'</p>
    </div>
';
}

$tags_html.='</div>


<script type="text/javascript">new Ajax.Autocompleter("ssearchTag", "ssearch_auto_complete", "/behaviour/ajax/list_tags.php?msv='.$site->id.'", {indicator: "eggTimer"})</script>	

';

