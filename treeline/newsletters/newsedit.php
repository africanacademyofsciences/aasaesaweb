<?php

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/subscriber.class.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/newsletter.class.php");

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/event.class.php");

$curPage = "newsletters_edit";
$nid = ($_POST?$_POST['id']:$_GET['id']);

// Choices for action: "create", "edit" and "reuse".
$action = read($_REQUEST,'action','');
$guid = read($_REQUEST,'guid','');
	
$message = array();
$feedback = 'notice';
$nextsteps = '';

//$nextsteps.='<li><a href="/treeline/newsletters/">Newsletter menu</a></li>';
$new_status = 'N';
	
if (!$action && $nid>0) $action = "edit";

// Newsletter processing
if($_POST["hidden_save"] == "save"){

	$newsletter = new newsletter($nid);
	
	// 1 Validate subject && text
	$newsletter->subject = $_POST['nl_subject'];
	$newsletter->html_text = $_POST['nl_text'];
	$newsletter->html_text2 = $_POST['nl_text2'];
	$newsletter->html_text3 = $_POST['nl_text3'];
	$newsletter->preferences = $_POST["preference"];
	$newsletter->event_id = $_POST['guid'];
	$newsletter->header = $_POST["header"];
	
	// Check the newsletter we are trying to update belongs to this site.
	//  	if not create a new newsletter on the current microsite
	if ($site->id>1 && $newsletter->status=='S') {
		$query = "SELECT msv FROM newsletter WHERE id = $nid";
		if ($db->get_var($query)!=$site->id) {
			$nid = 0;
			$new_status='S';
		}
	}
	
	// Check newsletter data is valid
	if($newsletter->validate()){

		// Update an existing Newsletter
		if ($nid>0) {
			$newsletter->update();
			$feedback = "success";
			$message[]=$page->drawLabel("tl_nl_ed_saved", "Newsletter saved");
			$nextsteps.='<li><a href="/treeline/newsletters/">'.$page->drawLabel("tl_nl_next_menu", "Newsletter menu").'</a></li>';
			$nextsteps.='<li><a href="/treeline/newsletters/newsbrowse/?status='.$newsletter->status.'">'.$page->drawLabel("tl_nl_ed_man".($newsletter->status=="S"?"fol":""), "Manage ".($newsletter->status=='S'?"follow up ":"")."newsletters").'</a></li>';
		}
		// Create a new newsletter
		else if ($newsletter->createNew($new_status)) {
			$feedback = "success";
			$message[]=$page->drawLabel("tl_nl_ed_saved", "Newsletter saved");
			$nextsteps.='<li><a href="/treeline/newsletters/">'.$page->drawLabel("tl_nl_next_menu", "Newsletter menu").'</a></li>';
			$nextsteps.='<li><a href="/treeline/newsletters/newsbrowse/">'.$page->drawLabel("tl_nl_ed_man", "Manage newsletters").'</a></li>';
			$action = "edit";
			$nid = $newsletter->id;
		}
		else $message[]=$page->drawLabel("tl_nl_crea_errsave", "There was a problem creating your newsletter");
	}
	else {
		$message = $newsletter->errmsg;
		//$message[]=$page->drawLabel("tl_nl_ed_errsave", "There was a problem saving your newsletter");
	}

	// Forget all about the newsletter we just updated
	unset($newsletter);
}
/*
else if ($_GET['action'] == "delete") {
	$query = "DELETE FROM newsletter WHERE id = ".$_GET['id'];
	if($db->query($query)){
		$nextsteps.='<li><a href="/treeline/newsletters/newsbrowse/">Manage newsletters</a></li>';
		$action = "create";
	}
	else{
		$message[] = 'Newsletter was not deleted due to a technical error';
	}
}
*/


// Reload the newsletter....
switch($action){
	case "edit":
		$pageTitle = $page->drawPageTitle("newsletters", "Edit newsletter");
		$newsletter = new newsletter($nid);
		break;
	case "reuse":
		$pageTitle = $page->drawPageTitle("newsletters", "Edit newsletter");
		$newsletter = new newsletter();
		$newsletter->reuse($nid);
		break;
	case "create":
	default:
		$pageTitle = $page->drawPageTitle("newsletters", "Create newsletter");
		$action = "create";
		$newsletter = new newsletter();
		break;
}

	
	// PAGE specific HTML settings
	
	$css = array('forms'); // all CSS needed by this page
	$extraCSS = '

form fieldset input.checkbox {
	margin-left: 0px;
}
div#cb-preferences {
	float: left;
}
table select{width: auto;} table.mceEditor{width: 600px !important;}

.mceEditorContainer select {
	width:100px !important;
	margin: 0px !important;
}
	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript

	$pageClass = 'newsletters';
		
	global $siteID;
		
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	


?>
<div id="primarycontent">
<div id="primary_inner">

<?php
	echo drawFeedback($feedback,$message);
	if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");

	if($action == "edit" && !$newsletter->id){ 
		$feedback = 'error';
		$message = 'Cannot find the newsletter you are trying to use.';
		echo drawFeedback($feedback,$message);
	} 
	else { 
	
		// Check if this newsletter is already associated with an event subscriber group?
		if ($nid>0) $event_id=$db->get_var("SELECT event_id FROM newsletter_send_preferences WHERE newsletter_id=".$nid);
		$event = new Event($event_id);

		$page_html='';
		if ($newsletter->status=="S") { 
			$page_title=$page->drawLabel("tl_nl_ed_foltitle", "Follow up email editor");
			$page_html='
			<p>'.$page->drawLabel("tl_nl_ed_foled1", "You are editing the").' <strong>'.$newsletter->html_text3.'</strong> '.$page->drawLabel("tl_nl_ed_foled2", "follow up email").'</p>
			<p>'.$page->drawLabel("tl_nl_ed_folmsg1", "Please take care when editing in here as all saved changes are final").'</p>
			'.$newsletter->html_text2.'
			';
		} 
		else { 
			if ($action=="edit") $page_title=$page->drawLabel("tl_nl_ed_stdtitle", "Modify your newsletter below");
			else $page_title=$page->drawLabel("tl_nl_ed_stdmsg1", "Use the editor below to create your newsletter");
		} 
		
		$page_html.= '

		<form method="post" id="frmNewsletter">
		<fieldset>
			<p class="instructions">'.$page->drawLabel("tl_nl_ed_mandatory", "All fields are mandatory").'</p>
			<input type="hidden" name="id" value="'.$newsletter->id.'" />
			
			<div class="field">
				<label for="subject" class="requried">'.$page->drawGeneric("subject", 1).':</label>
				<input type="text" id="subject" name="nl_subject" maxlength="255" size="40" value="'.($_POST?$_POST['nl_subject']:$newsletter->subject).'" class="requried" />
			</div>
			
			<div class="field">
				<label for="nl_text" class="requried">'.$page->drawLabel("tl_nl_edf_copy", "Newsletter copy").':</label>
				<div style="float:left;width:600px;padding-top:5px;">
					<textarea id="nl_text" class="mceEditor required" name="nl_text" rows="20" cols="20">'.($_POST?$_POST['nl_text']:$newsletter->html_text).'</textarea>
				</div>
			</div>
			';

		// System mail don't need extra copy blocks
        if ($newsletter->status=='S') { 
			$page_html.='
            <input type="hidden" name="nl_text3" value="'.$newsletter->html_text3.'" />
            <input type="hidden" name="nl_text2" value="'.$newsletter->html_text2.'" />
			';
		} 
		else {
			// Show edit for copy block 2
			if ($use_copy2) $page_html.='
				<div class="field">
					<label for="nl_text2" class="requried">Section 2 Copy:</label><br />
					<div style="float:left;width:600px;padding-top:5px;">
						<textarea id="nl_text2" class="mceEditor required" name="nl_text2" rows="20" cols="20">'.$newsletter->html_text2.'</textarea>
					</div>
				</div>
				';
			else $page_html.='<input type="hidden" name="nl_text2" value="'.$newsletter->html_text2.'" />';

			// Show edit area for copy block 3
			if ($use_copy3) $page_html.='
				<div class="field">
					<label for="nl_text3" class="requried">Section 3 Copy:</label><br />
					<div style="float:left;width:600px;padding-top:5px;padding-bottom:10px;">
						<textarea id="nl_text3" class="mceEditor required" name="nl_text3" rows="20" cols="20"><?php echo $newsletter->html_text3; ?></textarea>
					</div>
				</div>
				';
			else $page_html.='<input type="hidden" name="nl_text3" value="'.$newsletter->html_text3.'" />';

			$page_html.='
			<fieldset style="float:left;width:660px;margin-top:20px;">
				<legend>'.$page->drawLabel("tl_nl_edf_audience", "Audience").'</legend>
				';
			$event_select = $event->drawSelectList("guid", false, $page->drawLabel("tl_nl_edf_noevent", 'Not related to an event'));
			if ($event_select) {
				$page_html.='<p>'.$page->drawLabel("tl_nl_aud_msg1", "You can select an event to send your newsletter to all current attendees of that event. Or you can select one or more news groups to send this mail to").'</p>';
				$page_html.='<label for="s_event">'.$page->drawLabel("tl_nl_edf_subs", "Event subscribers").'</label>';
               	$page_html.=$event_select;
			}
			else $page_html.='<p>'.$page->drawLabel("tl_nl_aud_msg2", "Select one or more news groups to send this mail to").'</p>';
			
			$page_html.='
                <label for="c_preference">'.$page->drawLabel("tl_nl_edf_group", "News groups").'</label>
				<div id="cb-preferences">
				'.$newsletter->drawAdminPreferences($newsletter->id, $site->id).'
				</div>
			</fieldset>
			';
		}
         
		$page_html.='
			<input type="hidden" name="hidden_save" value="save" />
			
			<fieldset class="buttons">
				<input type="submit" name="save" class="submit" value="'.$page->drawGeneric("save", 1).'" />
			</fieldset>
	
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page_title, "blue");
	} 
?>


</div>
</div>

<?php 
echo $page->initCKE();
?>
<script type="text/javascript">
	CKEDITOR.replace('nl_text', {toolbar : 'contentStandard'});
</script>
<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>

