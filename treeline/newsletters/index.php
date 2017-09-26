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
	//$pageTitleH2 = ($action) ? 'Newsletters : '.ucwords($action) : 'Newsletters';
	//$pageTitle = ($action) ? 'Newsletters : '.ucwords($action) : 'Newsletters';
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("newsletters", $action);
	$action="newsletter";
	
	$curPage = "newsletters_home";
	
	$pageClass = 'newsletters';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php
	echo drawFeedback($feedback,$message);

	?>
	<h2 class="pagetitle rounded"><?=$page->drawGeneric("step", 1).' 1: '.$page->drawLabel("tl_nl_ind_msg1", "Select how you want to manage newsletters")?></p></h2>
	<?php
	// First set of newsletter options
	$opts1 = '<li><a href="newsedit/">'.$page->drawLabel("tl_nl_ind_create", "Create a new newsletter").'</a></li>';
	$opts1 .= '<li><a href="newsbrowse/">'.$page->drawLabel("tl_nl_ind_manage", "Manage newsletters").'</a></li>';
	$opts1 .= '<li><a href="newsbrowse/?status=S">'.$page->drawLabel("tl_nl_ind_follow", "Manage follow up emails").'</a></li>';
	if ($site->config['newsletter_banner']==1) $opts1 .= '<li><a href="newsbanner/">'.$page->drawLabel("tl_nl_ind_banner", "Upload banner").'</a></li>';
	if ($site->config['setup_campaigns']==1) $opts1 .= '<li><a href="/treeline/campaign/?">'.$page->drawLabel("tl_nl_ind_camp", "Campaigns").'</a></li>';
	
	echo treelineList($opts1, 	$page->drawLabel("tl_nl_ind_edittitle", 'Create or edit a newsletter'), 'blue');
		
	echo treelineList('<li><a href="newsbrowse/?action=test">'.$page->drawLabel("tl_nl_ind_test", "Test a newsletter").'</a></li>
		<li><a href="newsbrowse/?action=send">'.$page->drawLabel("tl_nl_ind_send", "Send a newsletter").'</a></li>', 
		$page->drawLabel("tl_nl_ind_sendtitle", "Send a newsletter"), "blue");

	echo treelineList('<li><a href="prefedit/">'.$page->drawLabel("tl_nl_ind_pref", "Manage preferences").'</a></li>
		<li><a href="subsbrowse/">'.$page->drawLabel("tl_nl_ind_subs", "Manage subscribers").'</a></li>
		<li><a href="/treeline/members/?action=create">'.$page->drawLabel("tl_nl_ind_addsub", "Add a new subscriber").'</a></li>
        <li><a href="subsdownload/?refresh=1">'.$page->drawLabel("tl_nl_ind_downsub", "Download subscriber database").'</a></li>', 
		$page->drawLabel("tl_nl_ind_subtitle", 'Manage subscriptions'));

?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>