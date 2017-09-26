<? 
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
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
	$pageTitleH2 = ($action) ? 'Campaigns : '.ucwords($action) : 'Campaigns';
	$pageTitle = ($action) ? 'Campaigns : '.ucwords($action) : 'Campaigns';
	
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
	<h2 class="pagetitle rounded">Step 1: Select how you want to manage campaigns</h2>
	<?php
	echo treelineList('<li><a href="/treeline/campaign/manage/">Create a new campaign</a></li>
      	<li><a href="/treeline/campaign/read/">Manage campaigns</a></li>','Create or  manage campaigns', 'blue');
	?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>