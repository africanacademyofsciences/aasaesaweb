<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/preference.class.php");

$digest_id = 0;			// Set this to the ID of the digest preference to avoid it being deleted.

$action = strtolower(read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'action', ''));

$currentPage = read($_GET, 'page', 1);
$perPage = 10;

$pid = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'preference_id', 0);

$feedback="notice";
$message = array();

// Create a new Preference
if ($_SERVER['REQUEST_METHOD']=="POST") {
	
	//print "posted action($action) id($pid)<br>\n";
	$preference = new preference($pid);
	
	// Create a new preference
	if($action == "create"){
		if ($_POST['preference_title'] && $_POST['preference_description']) {
			$preference->set('preference_title', $_POST["preference_title"]);
			$preference->set('preference_description', $_POST["preference_description"]);
			$preference->set('siteID', $site->id);
			if (!$preference->createNew()) $message[]=$page->drawLabel("tl_nl_pedit_errcrea", "Failed to create your new preference");
			else {
				$message[]=$page->drawLabel("tl_nl_pedit_added", "Your preference has been added");
				$feedback="success";
				$action="";
			}
		}
		else $message[]=$page->drawLabel("tl_nl_pedit_info", "You must enter a title and description");
	}

	// Update an existing preference
	else if ($action=="edit") {

		if($pid > 0){
			//print "update preference<br>\n";
			if ($_POST['preference_title'] && $_POST['preference_description']) {
				$preference->set('preference_title', $_POST["preference_title"]);
				$preference->set('preference_description', $_POST["preference_description"]);
				$preference->update();
				$action = '';
			}
			else $message[]=$page->drawLabel("tl_nl_pedit_info", "You must enter a title and description");
		}
		else $message[]="No preference ID found for update";
	} 
	
}
else {
	if ($action == "delete") {
		// Delete id
		$preference = new preference(addslashes($_GET["preference_id"]));
		$preference->delete();
		$action="";
	}
	else if ($action == "re_enable") {
		// Re-enable preference
		$preference = new preference(addslashes($_GET["preference_id"]));
		$preference->re_enable();
		$action="";
	}
}
		

// Load the preference for use on the page.
unset($preference);
if ($action=="edit") $preference = new preference($pid, $site->id);
else $preference = new preference(null);

	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	//$pageTitleH2 = ($action) ? 'Newsletters Preferences: '.ucwords($action) : 'Newsletters Preferences';
	//$pageTitle = ($action) ? 'Newsletters Preferences : '.ucwords($action) : 'Newsletters Preferences';
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("newsetters", "Preferences ".$action);
	$pageClass = 'newsletters';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
<div id="primary_inner">
<?php

	echo drawFeedback($feedback, $message);
	
    if(($action == "edit" || $action=="delete") && !$preference->preference_id){ 
		?>
	    <p class="error">Cannot find the preference you are trying to <?=$action?>.</p>
    	<?php
	}
	else if ($action=="create" || $action=="edit") {
		$page_html = '
		<p>'.$page->drawLabel("tl_nl_pedit_msg1", "Use the form below to set up a preference").'</p>
		';
		if ($action=="edit") {
			$page_html .= '<p class="instructions">PLEASE NOTE: If you change a preference title you must also change it on your Mailchimp account otherwise it will not be possible for people to register their subscriptions on Mailchimp.</p>';
		}
		$page_html .= '
		<form action="" method="post" id="frmPreference">
		  <fieldset>
			  <input type="hidden" name="preference_id" value="'.$preference->preference_id.'" />
			  <div class="field">
			  <label for="preference_title" class="required">'.$page->drawGeneric("title", 1).':</label>
			  <input type="text" class="required" id="preference_title" name="preference_title" maxlength="255" value="'.($_POST?$_POST['preference_title']:$preference->preference_title).'" />
			  </div>
			  <div class="field">
			  <label for="preference_description" class="required">'.$page->drawGeneric("description", 1).':</label>
			  <textarea class="preference_description" name="preference_description" id="preference_description">'.($_POST?$_POST['preference_description']:$preference->preference_description).'</textarea>
			  </div>
			  <fieldset class="buttons">
			  	<input type="hidden" name="action" value="'.$action.'" />
				<input type="submit" class="submit" value="'.$page->drawGeneric($action, 1).'" />
			  </fieldset>
		  </fieldset>
		</form>
		';
		$page_title=($action == 'add' || $action == 'create') ? $page->drawLabel("tl_nl_pedit_addtitle", 'Add new preference'):$page->drawLabel("tl_nl_pedit_edititle", 'Edit preference').' : '.$preference->preference_title;
		echo treelineBox($page_html, $page_title, "blue");
    } 

	?>
	<p><a href="/treeline/newsletters/prefedit/?action=create"><?=$page->drawLabel("tl_nl_pedit_addpref", "Add a new preference")?></a></p>
	<?php

	// Run stripped down SQL for pagination data
	$strSQL = "SELECT count(*) FROM newsletter_preferences WHERE site_id = ".$site->id;
	$hits = $db->get_var($strSQL);

	// Fetch old newsletters
	$query = "
		SELECT preference_id, preference_title, preference_description, deleted 
		FROM newsletter_preferences 
		WHERE site_id = ".$site->id."
		ORDER BY preference_title
		LIMIT ".getQueryLimits($perPage, $currentPage);
	//echo $query;
	if($db->query($query)) {
		$results = $db->get_results(null);
		?>
		<table class="tl_list">
			<caption><?=$page->drawGeneric("preferences", 1)?> - <?=$hits?> <?=$page->drawGeneric("result".($hits==1?"":"s"))?> <?=$page->drawGeneric("found")?></caption>
			<thead>
				<tr>
					<th scope="col"><?=$page->drawGeneric("title", 1)?></th>			
					<th scope="col"><?=$page->drawLabel("tl_nl_pedit_manage", "Manage preference")?></th>
				</tr>
			</thead>
			<tbody>
		<?php
		//echo "<pre>".print_r($results, true)."</pre>";
		foreach($results as $result){

			$nolink = '<span class="no-action"></span>';
			$dellink = $reenablelink = $nolink;
			$editlink = '<a class="edit" '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_prefedit", "Edit this preference")).' href="/treeline/newsletters/prefedit/?action=edit&amp;preference_id='.$result->preference_id.'">Edit</a>';
			if ($result->deleted == 1){
				$reenablelink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_enable", "Re-enable this preference")).' class="publish" href="/treeline/newsletters/prefedit/?preference_id='.$result->preference_id.'&amp;action=re_enable">Re-enable</a>';
			}
			// Scrappy but dont allow the digest preference to be deleted. 
			else if ($result->preference_id != $digest_id) {
				$dellink = '<a class="delete" '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_delpref", "Disable this preference")).' href="/treeline/newsletters/prefedit/?preference_id='.$result->preference_id.'&amp;action=delete">Delete</a>';
			}
			$date = ($result->date_changed) ? $result->date_changed : $result->date_added;
			?>
			<tr>
				<td><?php echo smartTruncate(stripslashes($result->preference_title), 40); ?></td>
				<td class="action"><?=$editlink.$dellink.$reenablelink?></td>
			</tr>
			<?php 
		} 
		?>
		
			</tbody>
		</table>
		<ul class="pagination"><?=drawPagination($hits, $perPage, $currentPage)?></ul>
		
		<?php
	}
	else { 
		?><p><?=$page->drawLabel("tl_nl_pedit_nopref", "No preferences found")?></p><?php
	}
	?>

</div>
</div>
<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>