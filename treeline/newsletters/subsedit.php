<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");

$curPage = "newsletters_sub_edit";
$action = read($_REQUEST,'action','');

$newsletter = new Newsletter();

$nextsteps = '';
$message = array();
$feedback = "notice";


$member_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'id', 0);

if($_SERVER['REQUEST_METHOD']=="POST") {
	// Process subscriber form	
	if ($_POST['email'] && $member_id>0 && $action=="edit") {
		$subscriber = new subscriber($member_id);
		Newsletter::updatePreferences($member_id);
		$message[]=$page->drawLabel("tl_nl_sedit_updated", "Subscriber updated");
		$feedback="success";
		$nextsteps.='<li><a href="/treeline/newsletters/subsbrowse">'.$page->drawLabel("tl_nl_next_bsubs", "Browse subcribers").'</a></li>';
		$nextsteps.='<li><a href="/treeline/members/?id='.$member_id.'&amp;action=edit">'.$page->drawLabel("tl_nl_next_edmem", "Edit member data").'</a></li>';
	}

} 
	
// Processing complete, reload subscriber object
$subscriber = new subscriber($member_id);


// PAGE specific HTML settings

$css = array('forms','tables'); // all CSS needed by this page
$extraCSS = ''; // extra on page CSS

$js = array(); // all external JavaScript needed by this page
$extraJS = ''; // extra on page JavaScript

// Page title	
$pageTitleH2 = $pageTitle = $page->drawPageTitle("newsletters", $action." subscribers");
$pageClass = 'newsletters';
	
include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	

?>
<div id="primarycontent">
<div id="primary_inner">

	<?php
	if ($nextsteps) echo treelineList($nextsteps, $page->drawGeneric("next steps", 1), "blue");
	echo drawFeedback($feedback,$message);
	
    if(!$subscriber->id) { 
		?>
    	<p class="error"><?=$page->drawLabel("tl_nl_sedit_nofind", "Cannot find the subscriber you would like to modify")?></p>
    	<?php
	}
	else {
		$page_html = '

		<p>Use the form below to set up your subscriber, then click &quot;Save&quot; to continue to the next page or click &quot;Cancel&quot;.</p>
		
		<form action="" method="post" id="frmSubscriber">
		<fieldset>
			<p class="instructions">Fields marked * are required.</p>
			<input type="hidden" name="id" value="'.$subscriber->id.'" />
			
			<div class="field">
				<label for="f_firstname" class="">Full name:</label>
				<input type="text" class="required" id="f_firstname" name="firstname" maxlength="255" value="'.$subscriber->firstname.' '.$subscriber->surname.'" disabled="disabled" />
			</div>
			<!--
			<div class="field">
				<label for="f_surname" class="required">Surname:</label>
				<input type="text" class="required" id="f_surname" name="surname" maxlength="255" value="'.$subscriber->surname.'" />
			</div>
			-->
			<div class="field">
				<label for="email" class="">Email:</label>
				<input type="text" class="required" maxlength="255" id="email" disabled="disabled" value="'.$subscriber->email.'" />
				<input type="hidden" name="email" value="'.$subscriber->email.'" />
			</div>
		';
		
		//echo Newsletter::listPreferences("", $subscriber->id);
		// prefil the _POST array with current preferences.
		$query = "SELECT nup.preference_id 
			FROM newsletter_user_preferences nup
			LEFT JOIN newsletter_preferences np ON nup.preference_id=np.preference_id
			WHERE np.site_id=".$site->id." AND member_id=".$subscriber->id;
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$_POST['preference'][]=$result->preference_id;
			}
		}
		$page_html.=$newsletter->drawPreferences($site->id);

		$page_html.='	
				<fieldset class="buttons">
					<input type="submit" class="submit" name="save" value="'.$page->drawGeneric("save", 1).'" />
				</fieldset>
			</fieldset>
			</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_nl_sedit_title", 'Edit subscriber').' : '.$subscriber->fullname, "blue");
    }

	?>
    
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>