<?php
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/store/includes/campaign.class.php");

//ini_set ("display_errors", "yes");
//error_reporting(E_ALL);
	
$message = array();
$feedback = "error";


$cid = read($_POST?$_POST:$_GET, 'cid', 0);
$camp = new Campaign($site->id, $cid);

// Pagination, manageament
$ssearch = read($_REQUEST, "q", "");
$thispage = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'p',1);
$camp->setPage($thispage);
     
$action = read($_REQUEST,'action',NULL);
//print "Got action($action) donation($did)<br>\n";



if ($_SERVER['REQUEST_METHOD']=="POST") {

	//print "post(".print_r($_POST, true).")<br>\n";
	if ($action=="create") {
		if ($_POST['title']) {
			$query = "INSERT INTO store_donation_campaign(title, added, msv) 
				VALUES
				(
					'".$db->escape($_POST['title'])."', 
					NOW(), 
					".($site->id+0)."
				)
				";
			//print "$query<br>\n";
			if (!$db->query($query)) $message[] = "Failed to create new campaign($query)";
			else {
				$cid = $db->insert_id;
				$action = "edit";
				$message[] = "New campaign created";
				$feedback="success";
			}
		}
		else $message[] = "You must enter a title for your new campaign";
	}
	// Update a campaign
	else if ($action=="edit") {
		$query = "UPDATE store_donation_campaign SET 
			title = '".$db->escape($_POST['title'])."' ,
			active = ".($_POST['active']+0)."
			WHERE id=".$cid;
		//print "$query<br>\n";
		$db->query($query);
		if (!$db->last_error) {
			$message[] = "Campaign updated";
			$feedback = "success";
			$cid="";
			$action = "";
		}
		else $message[] = "Failed to update campaign($query)";
	}

	// Delete a campaign
	else if ($action=="delete") {

		if ($cid>0) {
			$query = "DELETE FROM store_donation_campaign WHERE id=".$cid;
			print "Delete campaign($query)<br>\n";
			if ($db->query($query)) {
				$message[] = "This campaign has been deleted";
				$feedback = "success";
				$action = "";
			}
		}
		else $message[] = "No campaign to delete";		
	}
	else if ($action=="search") ;
	else $message[] = "POST action($action) Not processed";
}
else {
	
		
}


if ($cid>0) $camp->loadByID($cid);


$css = array('forms','campaign','tables'); // all CSS needed by this page
$extraCSS = '

table.tl_list {
}
	table.tl_list td.disabled {
		color: #ddd;
	}
'; // extra on page CSS
$extraJS = '

'; 

// Page title
$pageTitleH2 = ($action) ? 'Campaign: '.ucwords($action) : 'Campaign';
$pageTitle = ($action) ? 'Campaign: '.ucwords($action) : 'Campaign';

$pageClass = 'campaign';


include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');
?>

<div id="primarycontent">
<div id="primary_inner">

<?php 
echo drawFeedback($feedback, $message);

// Manage campaigns
if ($action=="create") {

	?>
    <h2 class="pagetitle rounded">Create a new campaign</h2>
    <?php
	$page_html = '
	<p><a href="/treeline/store/campaign.php">Manage campaigns</a></p>
    <form id="campaignForm" action="" method="post">
    <fieldset>
        <input type="hidden" name="action" value="create" />

		<div class="field">
	        <label for="f_title">Title:</label>
    	    <input type="text" name="title" id="f_title" value="'.($_POST['title']).'" />
		</div>

        <fieldset class="buttons">
    	   <input type="submit" class="submit" name="submit" value="Submit" />
	    </fieldset>
    </fieldset>
    </form>
	';
	
	echo treelineBox($page_html, "Enter new campaign details", "blue");	
}

else if ($action=="edit" && $cid>0) {
	
	$page_title = "Manage campaign: ";
	$page_title.=$camp->title;
	?>
	<h2 class="pagetitle rounded" style="width:689px;">Edit this campaign</h2>
		  
	<?php
    $page_html = '
		<p><a href="/treeline/store/campaign.php">Manage campaigns</a></p>
	';
	
	$page_html .= '<form id="campaignForm" action="" method="post">
	<fieldset>
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="cid" value="'.$cid.'" />
		
		<div class="field">
			<label for="f_title">Title:</label>
			<input type="text" name="title" id="f_title" value="'.$camp->title.'" />
		</div>

		<div class="field">
			<label for="f_active">Active:</label>
			<input style="width:auto;" type="checkbox" name="active" value="1" id="f_active" '.($camp->active==1?'checked="checked"':'').' />
		</div>

		<fieldset class="buttons">
		   <input type="submit" class="submit" name="submit" value="Submit" />
		</fieldset>
	</fieldset>
	</form>
	';
	
	echo treelineBox($page_html, "Save campaign details", "blue");
}
else if ($action=="delete" && $cid>0) {

	$page_html = '
	<form id="treeline" enctype="" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
	<fieldset>
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="cid" value="'.$cid.'" />
		<input type="hidden" name="confirm" value="1" />
		<p>You are about to delete this campaign, <strong>Are you sure?</strong></p>
		<fieldset class="buttons">
			<input type="submit" class="submit" value="Delete" />
		</fieldset>
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, "Confirm campaign delete: ".$camp->title, "blue");
	
}
else {

	?>
    <h2 class="pagetitle rounded">Search for campaign to manage</h2>
	<p>Campaigns appear in the "Give to a specific campaign" section of the donations/store checkout process</p>
	<?php 		
	
	
	$page_html = '
		<p><a href="/treeline/store/campaign.php?action=create">Add another campaign</a></p>
		<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="search" />

			<label for="ssearch">Kewords:</label>
			<input type="text" class="text" name="q" id="f_ssearch" value="'.$ssearch.'" />

			<fieldset class="buttons">
				<input type="submit" class="submit" value="Search">
			</fieldset>
		</fieldset>
		</form>
	';
	echo treelineBox($page_html, "Search by keyword or select a campaign from the list below below", "blue");
	
	echo $camp->drawList($thispage, $action, '', $ssearch);
	
}

?>

</div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>
    