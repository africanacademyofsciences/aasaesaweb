<?php
session_start();

//ini_set("display_errors", 1);
//error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");

$DEBUG=true;

$linktype = $_POST['linktype']; // Internal, External, File, Mail, Anchor, Shared file
if (!$linktype) $linktype="ext";

// Get the text for the new link
if ($_SERVER['REQUEST_METHOD']=="POST") {
	$linktext = $_POST['linktext'];
	if (!$linktext) $linktext=str_replace('\"', '"', $_POST['newlink']); 
}
else $linktext = $_GET['linktext'];

//print "got method(".$_SERVER['REQUEST_METHOD'].") linktext($linktext)<br>\n"; 

$prev_btn 		= read($_POST,'prev_btn','');			// Check if prev pressed
$submit 		= read($_POST,'submit','');				// Check if form submitted
$submitsection	= read($_POST,'submitsection','');		// Check if we chose a file category
$intlink 		= read($_POST,'intlink','');			// Get internal link data
$intsection 	= read($_POST,'intsection',$site->id);	// Get link section
$extprotocol 	= read($_POST,'extprotocol', '');		// Get external link url
$extlink 		= read($_POST,'extlink','');			// Get external link text
$extlink_target	= read($_POST,'target','');				// Get external link target
$email_to 		= read($_POST,'email_to','');			// Get email to name
$email_addr 	= read($_POST,'email_addr','');			// Get email to address
$email_subject	= read($_POST,'email_subject','');		// Get email subject
$anchor 		= read($_POST,'anchor','');				// Get name of anchor to link to
$category		= read($_POST,'category','');			// Get file category
$filesection 	= read($_POST,'filesection','');		// Get file section
$filesubsection = read($_POST,'filesubsection','');		// Get file subsection

if ($category!=$filesection) $filesubsection='';
//print "file cat($filesection) subcat($filesubsection)<br>";

// Dont encode if we are already encoded.
//print $_SERVER['REQUEST_METHOD']."<br>\nlink(".$linktext.")<br>\n";
if (!$_POST['linktext']) $linktext=urlencode(str_replace(" ","~", str_replace("'", "`", $linktext)));
//print $_SERVER['REQUEST_METHOD']."<br>\n link(".$linktext.")<br>\n";


// Crappy fix for apostrophes in email links
$email_to = str_replace("'", "`", $email_to);
$email_subject = str_replace("'", "`", $email_subject);


// If the previous button was pressed
// Go back to previous page
if( $prev_btn  ){
	unset($_POST);
	$prevlink = "location: linkpicker.php?linktext=".$linktext;
	header($prevlink);
}

// Do some checking and make sure we have all the required valid information
// to continue to the next stage of the process
// if not save the errors to show later.
$error_list = array();
if($linktype=='ext' && $submit=='true'){
	if(!$extlink){ 
		$error_list[] = "You need to enter a web address!"; 
	}
	else if (!preg_match('/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', santise_content($extlink), $m)) {
		$error_list[] = "Your web address does not appear to be valid!"; 
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='int' && $submitsection=='true' ){
	if(countPages($intsection)<=0){
		$error_list[] = "This section does not appear to have any pages to link to!";
	}
}
else if( $linktype=='int' && $submit=='true' ){
	if( empty($intlink) ){
		$error_list[] = "You need to specify a page to link to!";
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='anchor' && $submit=='true' ){
	if( empty($anchor) ){
		$error_list[] = "You need to specify an anchor to link to!";
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='mail' && $submit=='true' ){
	if(!$email_to){
		$error_list[] = "You have to enter the Recipient's Name!";
	}
	if(!$email_addr){
		$error_list[] = "You have to enter an email address!";
	}else if( !is_email($email_addr) ){
		$error_list[] = "Your email address does not appear to be valid!";
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='file' && $submitsection=='true' ){
	if(countFiles($filesection)<=0){
		$error_list[] = "This section does not appear to have any pages to link to!";
	}
}
else if( $linktype=='file_shared' && $submitsection=='true' ){
	if(countFiles($filesection, true)<=0){
		$error_list[] = "This section does not appear to have any pages to link to!";
	}
}
else if( $linktype=='file' && $submit=='true' ){
	if( empty($intlink) ){
		$error_list[] = "You need to specify a page to link to!";
	}		
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='file_shared' && $submit=='true' ){
	if( empty($intlink) ){
		$error_list[] = "You need to specify a page to link to!";
	}		
	else if (!$linktext) $error_list[]="You have not specified any link text";
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title>Treeline: Link picker</title>
<style type="text/css">
@import url('/treeline/style/global.css');
@import url('/treeline/style/linkPicker.css');
@import url('/treeline/style/linkPickerSite.css');
</style>
<script type="text/javascript">
var selected;
var selectedOldClassName;

function change(id,oldClass) {
	var oldClassName='';
	var identity=document.getElementById(id);
	var selectedIdentity = document.getElementById(selected);
	var intlink = document.getElementById('intlink');
	if(oldClass==1){
		oldClassName='row dark';
	}else{
		oldClassName='row pale';
	}

	if( identity.className=='row on' ){
		identity.className=oldClassName;
		selected = '';
		selectedOldClassName = '';
		intlink.value = '';
	}else{
		// this should...
		// set the current row to be selected
		// if there's another row already highlighted, then unset that...
		if(selected>''){
			selectedIdentity.className = selectedOldClassName;  /// set the currently selected to what it was
		}
		selectedOldClassName = oldClassName; 			/// now set the old classname to the newly selected row
		selected = id;										/// the selected row's ID
		identity.className='row on';
		intlink.value = selected;
	}
}

function resize(){
	//window.resizeTo(575,625);
	return true;
}

</script>
<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce_popup.js"></script>
<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/plugins/own_link/js/dialog.js"></script>
</head>

<body onload="resize()">
<div id="header">
  <h1>add a link</h1>
</div>
<div id="contentpane">

<?php
switch($linktype){

	// *******************************************
	/////////////// external link ////////////////
	// *******************************************
	case 'ext':
	
		if( empty($error_list) && ($submit=='true') ){
			//$link[] = 'http://'.$extlink;
			$link[] = $extprotocol.$extlink;
			$link[] = $extlink_target;
			$fulllink='<a href="'.$link[0].'" '.(($link[1]=="new")?'target="_blank"':"").'>'.str_replace("<", "&lt;", urldecode($linktext)).'</a>';
			?>
				<form>
				<input type="hidden" name="newlink" />
				<input type="hidden" name="linktext" value='<?=$fulllink?>' />
				</form>
				<script type="text/javascript">OwnLinkDialog.insert();</script>
			<?php

		}
		else {
			?>
			<div id="formwrap">
			<h2>Please enter link URL</h2>
			<form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
				<input type="hidden" name="newlink" />
				<input type="hidden" name="submit" value="true" />
				<?
					//////// show errors if there are any //////
					show_errors($error_list);
				?>
				
				<fieldset title="Please enter the address of the website to link to" id="exturl">
					<label for="extlink">What is the address (url) of the site?</label>
					<span id="extlinkwrap">
					<select name="extprotocol">
						<option value="http://">http://</option>
						<option value="ftp://">ftp://</option>
						<option value="https://">https://</option>
						<option value="feed://">feed://</option>
					</select>
					<input type="text" id="extlink" name="extlink" value="<?= santise_content($extlink); ?>" />
					</span>
				</fieldset>

				<fieldset id="linkwindow">
					<span>Open link in:</span>
					<label>a new window
					<input type="radio" name="target" value="new" checked="checked" />
					</label>
					<label>the current window
					<input type="radio" name="target" value="" />
					</label>
				</fieldset>

				<div id="footerlinks">
					<input type="submit" name="" id="" class="" value="Next" />
				</div>
			</form>
			</div>
			<?

		}
		break;
		// *******************************************

} // End of switch

?>
    
</div>
</body>
</html>

<?php

////////////// FROM TREELINE /////////////////////
// /treeline/functions.php
// /treeline/includes/functions.php
// /treeline/includes/adminfunctions.inc.php
// /treeline/includes/adminfunctions.php
// What on earth is all that about.

// Function moved here 081029 Phil Redclift.
// 
// Only place its actually used to keep it separate
// *************************************************************************
function drawSections($msv,$lang,$intsection){
	global $db;
	$query = "SELECT p.guid, p.name, p.title, p.locked FROM pages p
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE (p.parent=".$msv." OR p.guid=$msv) AND p.msv=$msv
				AND pt.template_php IN ('folder.php','index.php','landing.php', 'news.index.php')
				AND NOT (p.hidden=1 AND p.locked=1 AND 0)
				AND p.offline=0
				ORDER BY p.sort_order ASC, p.title ASC";
	//print "$query<bR>";
	$html = "<select id=\"intsection\" name=\"intsection\">\n";
	if ($pages = $db->get_results($query)) {
		foreach($pages as $page){
				$html .= "\t\t\t\t\t\t<option value=\"". $page->guid ."\"";
				if($intsection==$page->guid){
					$html .= ' selected="selected"';
				}
				$html .= ">". $page->title ."</option>\n";
		}
	}
	$html .= "</select>\n\n";
	return $html;
}

// *************************************************************************
function drawLPPages($parent, $indent=0) {

	// This function returns a list of all pages that the current user has the right to edit
	// Note that it should *exclude* pages that have already been edited by someone else
	// [unless the user is a superuser?]. THIS STILL NEEDS DOING.
	global $i,$db,$site;
	
	//print "drawLPPages($parent, $indent)<br>\n";

	$indentString = '&nbsp;&nbsp;&nbsp;&nbsp;';
	$html = '';
	$page = new Page();

	//$query = "SELECT guid, title, locked FROM pages WHERE parent = '$parent' AND lang='". $lang ."' 
	//			AND template NOT IN ('folder.php','index.php','landing.php') 
	//			AND NOT (hidden=1 AND locked=1) ORDER BY sort_order";

	$query = "SELECT p.guid, p.title, p.locked, p.hidden FROM pages p
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE p.parent = '$parent' AND p.msv=".$site->id."
				AND pt.template_php NOT LIKE '%panel%'
				AND NOT (p.hidden=1 AND p.locked=1)
				AND p.offline=0
				ORDER BY p.title, p.sort_order";
	if ($pages = $db->get_results($query)) {
		foreach ($pages as $p) {
			$num = ($i%2);
			$thisguid = $p->guid;	

			if($num == 0){
				$style='row pale';
				$j=0;
			}else{
				$style='row dark';
				$j=1;
			}		
			$hs=($p->hidden)?' [<font color="orange">hidden</font>]':"";
			$html .= '<span id="'.$p->guid.'" class="'.$style.'" onclick="change(this.id,'.$j.');"><span class="pagename" style="padding-left:'.$indent.'em">'.$p->title.$hs.'</span></span>'."\n";
			$i++;
			if($parent!=1){
				$html .= drawLPPages($thisguid, $indent+1.2);
			}
		}
	}
	// Add the home page to the top of the list if necessary
	if ($parent == $site->id) $html = '<span id="'.$site->id.'" class="row dark" onclick="change(this.id,1);"><span class="pagename" style="padding-left:0em">Home</span></span>'."\n".$html;
	return $html;
}


// *************************************************************************
function countPages($parent){
	global $db;
	$db->get_results("SELECT guid, title, locked FROM pages WHERE parent = '$parent' AND hidden = 0 ORDER BY sort_order");
	return $db->num_rows;
}
// *************************************************************************
function countFiles($cat, $shared=false, $subcat=''){
	global $db, $site;
	//print "count files($cat, $shared, $subcat)<br>";
	$query = "SELECT guid FROM files WHERE category = '$cat' ";
	if ($subcat) $query.="AND subcategory=$subcat ";
	if( !$shared ) $query.="AND site_id='".$site->id."'";
	//print "$query<br>";
	$db->get_results($query);
	return $db->num_rows;		
}


// *************************************************************************
function drawFileSections($lang,$filesection,$shared=false){
	global $db,$site,$lang;

	$html = "<select id=\"filesection\" name=\"filesection\">\n";
	$query = "SELECT DISTINCT f.category, fc.title, s.title AS sitename
		FROM files f 
		LEFT JOIN filecategories fc on f.category=fc.id 
		LEFT JOIN sites_versions sv ON f.site_id = sv.msv
		LEFT JOIN sites s on sv.microsite = s.microsite
		WHERE fc.title is not null ";
	if( !$shared ) $query.="AND f.site_id='". $site->id ."' ";
	//print "$query<br>";
	if ($files = @$db->get_results($query)) {
		foreach($files as $file){
			// if($file->locked<1){
				$html .= "\t\t\t\t\t\t<option value=\"". $file->category ."\"";
				if($filesection==$file->category){
					$html .= ' selected="selected"';
				}
				$html .= ">". $file->title.($shared?"(".$file->sitename.")":"")."</option>\n";
			// }
		}
	}
	$html .= "</select>\n\n";
	return $html;
}
// *************************************************************************
function drawFileSubSections($lang,$filesection,$filesubsection,$shared=false){
	global $db,$site,$lang;

	$html = "<select id=\"filesubsection\" name=\"filesubsection\" style=\"margin-top:5px;\">\n";
	$query = "SELECT DISTINCT f.subcategory, fc.title FROM files f 
		left join filecategories fc on f.subcategory=fc.id 
		WHERE fc.title is not null 
		AND f.category=$filesection ";
	if( !$shared ){
		$query.="AND f.site_id='". $site->id ."' ";
	}
	//print "$query<br>";
	if ($files = @$db->get_results($query)) {
		foreach($files as $file){
			$html .= "\t\t\t\t\t\t<option value=\"". $file->subcategory ."\"";
			if($filesubsection==$file->subcategory){
				$html .= ' selected="selected"';
			}
			$html .= ">". $file->title ."</option>\n";
		}
	}
	$html .= "</select>\n\n";
	return $html;
}
// *************************************************************************
function getFileList($cat,$shared=false,$subcat=''){
	global $i,$db,$site;
	$html = '';			
	$query = "SELECT * FROM files WHERE category='$cat' ";
	if ($subcat) $query.="AND subcategory=$subcat ";
	if( !$shared ) $query.= "AND site_id='".$site->id."' ";
	$query.="ORDER BY title";
	//print "$query<br>";
	if ($files = $db->get_results($query)) {
		foreach ($files as $file) {
			$num = ($i%2);
			
			if($num == 0){
				$style='row pale';
				$j=0;
			}else{
				$style='row dark';
				$j=1;
			}
	
			$html .= "<span id='". $file->guid ."' class='". $style ."' onclick='change(this.id,". $j .");'><span class='pagename'>".$file->title."</span>";
			$html .= "<span class='pagestatus'>". $file->extension ."</span><span class='pagevisibility'>". pretty_bytes($file->size) ."</span></span>\n\t\t\t\t";
			$i++;
		}
	}
	return $html;		
}


// *************************************************************************
// return filesize value in nice format
function pretty_bytes($size)
{
	$a = array("bytes", "kb", "MB", "GB", "TB", "PB");
	
	$pos = 0;
	while ($size >= 1024) {
				   $size /= 1024;
				   $pos++;
	}
	
	return round($size,2)."".$a[$pos];
}

// *************************************************************************
// Generate a link to a file in the library from its file guid
function getLinkByGUID($guid,$type='page'){
	global $db;

	switch($type){
		case 'page':
			$query = "SELECT name FROM pages WHERE guid = '$guid' limit 1";
			$results = $db->get_row($query);
			$link = $results->name .'/'; //.html removed and replaced by a  slash
			break;
		case 'file':
			$query = "SELECT CONCAT(name,'.',extension) filename FROM files WHERE guid='$guid'";
			$results = $db->get_row($query);
			$link = '/silo/files/'. $results->filename;
			break;
	}
	
	return $link;
}


// ************************************************************************
// 16/12/2008 Phil Redclift
// Show input errors 
function show_errors($error_list){
	if( !empty($error_list) ){
		echo '<div id="error_list"><strong>ERROR!</strong>';
		foreach($error_list as $err){
			echo '<br />'.$err;
		}
		echo '</div>'."\n";
	}
}

////////////////////////////////////////////////////////

?>