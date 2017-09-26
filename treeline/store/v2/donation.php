<?php
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/donation.class.php");

//ini_set ("display_errors", "yes");
//error_reporting(E_ALL);
	
$message = array();
$feedback = "error";


$did = read($_POST?$_POST:$_GET, 'did', 0);
$don = new Donation($site->id, $did);

// Pagination, manageament
$ssearch = read($_REQUEST, "q", "");
$thispage = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'p',1);
$don->setPage($thispage);
     
$action = read($_REQUEST,'action',NULL);
//print "Got action($action) donation($did)<br>\n";



if ($_SERVER['REQUEST_METHOD']=="POST") {

	//print "post(".print_r($_POST, true).")<br>\n";
	if ($action=="create") {
		if ($_POST['title']) {
			$query = "INSERT INTO donation(title, amount, description, added, msv) 
				VALUES
				(
					'".$db->escape($_POST['title'])."', 
					".($db->escape($_POST['amount'])+0).",
					'".$db->escape($_POST['description'])."',
					NOW(), 
					".($site->id+0)."
				)
				";
			//print "$query<br>\n";
			if (!$db->query($query)) $message[] = "Failed to create new donation($query)";
			else {
				$did = $db->insert_id;
				$action = "edit";
				$message[] = "New donation created";
				$feedback="success";
			}
		}
		else $message[] = "You must enter a title for your new donation";
	}
	// Update a donation
	else if ($action=="edit") {
		$query = "UPDATE donation SET 
			title = '".$db->escape($_POST['title'])."' ,
			amount = '".($db->escape($_POST['amount'])+0)."' ,
			description = '".$db->escape($_POST['description'])."' ,
			active = ".($_POST['active']+0)."
			WHERE id=".$did;
		//print "$query<br>\n";
		$db->query($query);
		if (!$db->last_error) {
			$message[] = "Donation updated";
			$feedback = "success";
		}
		else $message[] = "Failed to update donation($query)";
	}

	// Delete a donation
	else if ($action=="delete") {

		if ($did>0) {
			$query = "DELETE FROM donation WHERE id=".$did;
			//print "Delete donation($query)<br>\n";
			if ($db->query($query)) {
				$message[] = "This donation has been deleted";
				$feedback = "success";
				$action = "";
			}
		}
		else $message[] = "No donation to delete";		
	}
	else if ($action=="search") ;
	else $message[] = "POST action($action) Not processed";
}
else {
	
		
}


if ($did>0) $don->loadByID($did);


$css = array('forms','donation','tables'); // all CSS needed by this page
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
$pageTitleH2 = ($action) ? 'Donation: '.ucwords($action) : 'Donation';
$pageTitle = ($action) ? 'Donation: '.ucwords($action) : 'Donation';

$pageClass = 'donation';


include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');
?>

<div id="primarycontent">
<div id="primary_inner">

<?php 
echo drawFeedback($feedback, $message);

// Manage donations
if ($action=="create") {

	?>
    <h2 class="pagetitle rounded">Create a new donation</h2>
    <?php
	$page_html = '
	<p><a href="/treeline/store/'.$storeVersion.'/donation.php">Manage donations</a></p>
    <form id="donationForm" action="" method="post">
    <fieldset>
        <input type="hidden" name="action" value="create" />

		<div class="field">
	        <label for="f_title">Title:</label>
    	    <input type="text" name="title" id="f_title" value="'.($_POST['title']).'" />
		</div>

		<div class="field">
			<label for="f_amount">$ Amount:</label>
			<input type="text" name="amount" id="f_amount" value="'.($_POST['amount']+0).'" />
		</div>

		<div class="field">
			<label for="f_desc">Description:</label>
			<div class="ckeditor-box">
				<textarea type="text" name="description" id="f_desc">'.($_POST['description']).'</textarea>
			</div>
		</div>
		

        <fieldset class="buttons">
    	   <input type="submit" class="submit" name="submit" value="Submit" />
	    </fieldset>
    </fieldset>
    </form>
	';
	
	echo treelineBox($page_html, "Enter new donation details", "blue");	
}

else if ($action=="edit" && $did>0) {
	
	$page_title = "Manage donation: ";
	$page_title.=$don->title;
	?>
	<h2 class="pagetitle rounded" style="width:689px;">Edit this donation</h2>
		  
	<?php
    $page_html = '
		<p><a href="/treeline/store/'.$storeVersion.'/donation.php">Manage donations</a></p>
	';
	
	$page_html .= '<form id="donationForm" action="" method="post">
	<fieldset>
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="did" value="'.$did.'" />
		
		<div class="field">
			<label for="f_title">Title:</label>
			<input type="text" name="title" id="f_title" value="'.$don->title.'" />
		</div>

		<div class="field">
			<label for="f_amount">$ Amount:</label>
			<input type="text" name="amount" id="f_amount" value="'.($don->amount).'" />
		</div>

		<div class="field">
			<label for="f_desc">Description:</label>
			<div class="ckeditor-box">
				<textarea type="text" name="description" id="f_desc">'.($don->description).'</textarea>
			</div>
		</div>
		
		<div class="field">
			<label for="f_active">Active:</label>
			<input style="width:auto;" type="checkbox" name="active" value="1" id="f_active" '.($don->active==1?'checked="checked"':'').' />
		</div>

		<!--
		<div class="field">
			<label for="f_hidetitle">Hide page title:</label>
			<input style="width:auto;" type="checkbox" name="hidetitle" value="1" id="f_hidetitle" '.($don->hidetitle==1?'checked="checked"':'').' />
		</div>
		-->
		
		<fieldset class="buttons">
		   <input type="submit" class="submit" name="submit" value="Submit" />
		</fieldset>
	</fieldset>
	</form>
	';
	
	echo treelineBox($page_html, "Save donation details", "blue");
}
else if ($action=="delete" && $did>0) {

	$page_html = '
	<form id="treeline" enctype="" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
	<fieldset>
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="did" value="'.$did.'" />
		<input type="hidden" name="confirm" value="1" />
		<p>You are about to delete this donation, <strong>Are you sure?</strong></p>
		<fieldset class="buttons">
			<input type="submit" class="submit" value="Delete" />
		</fieldset>
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, "Confirm donation delete: ".$don->title, "blue");
	
}
else {

	?><h2 class="pagetitle rounded">Search for donation to manage</h2><?php 		
	
	$page_html = '
		<p><a href="/treeline/store/'.$storeVersion.'/donation.php?action=create">Add another donation</a></p>
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
	echo treelineBox($page_html, "Search by keyword or select a donation from the list below below", "blue");
	
	echo $don->drawList($thispage, $action, '', $ssearch);
	
}

?>

<script type="text/javascript" src="/treeline/includes/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
CKEDITOR.replace('f_desc', { toolbar : 'contentMinimal', height: '300px', width: '500px' });
</script>


</div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>
    