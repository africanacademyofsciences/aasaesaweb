<?

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
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
	$pageTitleH2 = ($action) ? 'Newsletters : '.ucwords($action) : 'Newsletters';
	$pageTitle = ($action) ? 'Newsletters : '.ucwords($action) : 'Newsletters';
	
	$action="newsletter";
	
	$curPage = "newsletters_home";
	
	$pageClass = 'newsletters';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php

	echo drawFeedback($feedback,$message);
	if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");
	
	// No options so just show the newsletter menu items.
 
	?>
	<h2 class="pagetitle">Step 1: Select how you want ot manage newsletters</p>
	<?php
	echo treelineList('<li><a href="newsedit/">Create and test a newsletter</a></li>
		<li><a href="newsbrowse/">Manage newsletters</a></li>', 'Create or edit a newsletter', 'blue');
	echo treelneList('<li><a href="newsbrowse/?action=test">Test a newsletters</a></li>
		<li><a href="newsbrowse/?action=send">Send a newsletters</a></li>
		<li><a href="digestedit/">Send a newsletter digest</a></li>', 'Send a newsletter', 'blue');

	?>	  
		<ul class="submenu">
				<li><a href="subsbrowse/">Browse/Edit subscribers</a></li>
				<li><a href="prefbrowse/">Browse/Edit preferences</a></li>
				<li><a href="subsdownload/">Download entire subscriber database</a></li>
			</ul>
			
	<?php 

?>
</div>
</div>
<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>