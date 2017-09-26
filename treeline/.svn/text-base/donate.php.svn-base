<?php

	//ini_set("display_errors", "yes");
	//error_reporting(E_ALL);



	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions/pages.php");
	
	$action = read($_REQUEST,'action','');
	
	// user feedback
	$feedback = read($_REQUEST,'feedback','');
	$message = read($_REQUEST,'message','');
	
	$title = read($_POST,'title',''); // Page title

	$page = new Page;
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {// Form has been submitted
	
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = '
	
function toggleRange() {

	var f=document.getElementById("treeline");
	var disabled=f.donate_range.selectedIndex!=0;
	f.donate_start_day.disabled = disabled;
	f.donate_start_month.disabled = disabled;
	f.donate_start_year.disabled = disabled;
	f.donate_end_day.disabled = disabled;
	f.donate_end_month.disabled = disabled;
	f.donate_end_year.disabled = disabled;
}
toggleRange();
	
'; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Donations : '.ucfirst($action) : 'Donations';
	$pageTitle = ($action) ? 'Donations : '.ucfirst($action) : 'Donations';
	
	$pageClass = 'pages';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
	
?>

    <div id="primarycontent">
       <div id="primary_inner">
			<?=drawFeedback($feedback,$message)?>
            <?php 
				if (!$action) { // CREATE A NEW PAGE 
		            include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ajax/forms/donations.php');
				}
			?>
       </div>
    </div>

<?php 
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>

