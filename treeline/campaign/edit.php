<?php
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/stats/classes/campaignstats.class.php");
	
	//instatiate campaignstats object 
		
	$action = read($_REQUEST,'action','');
		
	//if (!$action) header("Location: /treeline/"); // only for action pages
	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Campaign Statistics : '.ucwords($action) : 'Campaign Statistics';
	$pageTitle = ($action) ? 'Campaign Statistics : '.ucwords($action) : 'Campaign Statistics';
	
	$action="campaignstats";
	
	$curPage = "campaignstats_home";
	
	$pageClass = 'campaignstats';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php
	echo drawFeedback($feedback,$message);

	?>
	<h2 class="pagetitle rounded">Step 1: </p></h2>
	<?php
	/*
	echo treelineList('<li><a href="newsedit/">Create a new newsletter</a></li>
      	<li><a href="newsbrowse/">Manage newsletters</a></li>
		'.($site->id==1?'<li><a href="newsbrowse/?status=S">Browse/Edit follow up emails</a></li>':""), 'Create or edit a newsletter', 'blue');

	echo treelineList('<li><a href="newsbrowse/?action=test">Test a newsletter</a></li>
		<li><a href="newsbrowse/?action=send">Send a newsletter</a></li>
		<!-- <li><a href="digestedit/">Send a newsletter digest</a></li> -->', "Send a newsletter", "blue");

	echo treelineList('<li><a href="prefedit/">Browse/Edit preferences</a></li>
		<li><a href="subsbrowse/">Browse/Edit subscribers</a></li>
        <li><a href="subsdownload/?refresh=1">Download subscriber database</a></li>', 'Manage subscriptions');
	*/

?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>