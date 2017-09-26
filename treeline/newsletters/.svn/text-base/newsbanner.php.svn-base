<?php

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/newsletter.class.php");

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/event.class.php");

// Choices for action: "create", "edit" and "reuse".
$action = read($_REQUEST,'action','');
$guid = read($_REQUEST,'guid','');
	
$feedback = "error";
	
// Need to get the banner email
$banner = new Image();
$banner->loadImageByGUID($site->properties['email_logo']);

// Process and actions
if ($_SERVER['REQUEST_METHOD']=="POST") {

	switch(strtolower($action)){
		case "save":

			// Strip the first image from content and find its guid
			if (preg_match("/img src=\"(.*?)\"(.*)/", $_POST['nl_banner'], $reg)) {
				$tmp=explode("/", $reg[1]);
				$filename=array_pop($tmp);
				$query = "SELECT iz1.guid FROM images_sizes iz1
					LEFT JOIN images_sizes iz2 ON iz1.guid=iz2.guid 
					WHERE iz1.filename='".array_pop(explode("/",$reg[1]))."'
					AND iz2.width=550";
				//print $query."<br>\n";
				$email_logo_guid=$db->get_var($query);
				if ($email_logo_guid>'') {
					$query = "UPDATE sites_versions SET email_logo='$email_logo_guid' WHERE msv=".$site->id;
					//print "$query<br>\n";	
					if ($db->query($query)) {
						$message[]="Your new banner has been uploaded";
					}
					$banner->loadImageByGUID($email_logo_guid);
					$feedback="success";
				}
				else $message[]="A 550 pixel wide version of this image was not found. This image cannot be used as an email banner";
			}
			// Do we want to remove our email banner??
			else if (!$_POST['nl_banner']) {
				$query = "UPDATE sites_versions SET email_logo=NULL WHERE msv=".$site->id;
				//print "$query<br>\n";
				if ($db->query($query)) {
					$message[]="Your email banner has been removed. No logo will appear on your emails until you add a new banner here";
					$feedback="success";
				}
				unset($banner);
			}
			else $message[]="No image was found in the saved content";
			break;
	}
}

	
// Get some html for the banner image
if (is_array($banner->subimages)) {
	for($i=0; $i<count($banner->subimages); $i++) {
		if ($banner->subimages[$i]['width']+0==550) {
			$banner_content='<img src="/silo/images/'.$banner->subimages[$i]['filename'].'" />';
		}
	}
}	
	
	// PAGE specific HTML settings
	
	$css = array('forms'); // all CSS needed by this page
	$extraCSS = '

table select{width: auto;} table.mceEditor{width: 600px !important;}
.mceEditorContainer select {
	width:100px !important;
	margin: 0px !important;
}
	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript

	$pageClass = 'newsletters';
		
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	

?>
<div id="primarycontent">
<div id="primary_inner">

<?php
	echo drawFeedback($feedback,$message);

	$page_title = "Select a new banner for emails";
	$page_html= '

		<p>Please select the banner image you would like to appear at the top of your newsletter emails and website emails. Please note the system will only include the 550 pixel wide email in your emails.</p>
		<p>Banner images should ideally be short and wide to avoid taking up too much space in a email.</p>
		<form action="/treeline/newsletters/newsbanner/" method="post" id="frmNewsletter">
		<fieldset>
			<input type="hidden" name="action" value="Save" />
			<label for="nl_text" class="requried">Select banner image:</label><br />
			<div style="float:left;width:600px;padding-top:5px;">
				<textarea id="nl_banner" class="mceEditor required" name="nl_banner" rows="20" cols="20">'.$banner_content.'</textarea>
			</div>

			<fieldset class="buttons">
				<input type="submit" class="submit" value="Save" />
			</fieldset>
	
		</fieldset>
		</form>
		';
	echo treelineBox($page_html, $page_title, "blue");
?>


</div>
</div>

<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>

<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>

<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_newsletters.js"></script>
