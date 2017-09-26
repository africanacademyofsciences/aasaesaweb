<?php

include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/gallery.class.php");
$gallery=new Gallery($page->getGUID());

$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
$mode = read($_REQUEST,'mode','');

if ($page->private && !$_SESSION['member_id']) redirect($site->link."member-login/");

// Tags
$tags = new Tags();
$tags->setMode($page->getMode());

$start=read($_GET, 'start', read($_GET, 'p', 1));
$type=$_GET['type'];
//print "got type($type) start($start)<br>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
	$referer.=(strpos($referer, "?")?"&":"?");
	$action = read($_POST,'treeline','');

	if ($action == 'Save changes') {
		//$content->save();
		$page->save(true);
		
		// intelligent link panels
		$tags->updateIntelligentLinkPanelDetails($page->getGUID(), $_POST['accuracy'], $_POST['maxlinks'], $_POST['show_related_content']);
		
		// Content is saved so redirect the user
		$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
		
		$referer .= $feedback;
		$referer .= '&action=edit';
		
		$publish_redirect = '/treeline/pages/?action=publish&guid='.$page->getGUID();
		$publish_redirect .= '&'.$feedback;
		
		include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
		if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
			redirect($publish_redirect); // show them the publish option
		} else{
			redirect($referer); // otherwise take the user back to the edit pages page
		}
	}
	else if ($action == 'Discard changes') {			
		// We have to manually release the page here as we are not saving the page.
		$page->releaseLock($_SESSION['treeline_user_id']);			
		$referer .= 'action='.$page->getMode().'&'.createFeedbackURL('error','Your changes were not saved');
		redirect ($referer);
	}
}

if ($mode=="preview") $showPreviewMsg=true;

// Page specific options

$pageClass = 'page'; // used for CSS usually

$css = array('lytebox','gallery'); // all attached stylesheets
// Ignore page style
//if($page->style != NULL) $css[] = $page->style;

$js = array('multimedia_equalheightblocks','lytebox'); // all atatched JS behaviours
$extraJS = ' '; // etxra page specific  JS behaviours

$extraJSbottom='

// getHeights();

';

$disablePageStyle=true;	// Dont show the styleswitcher on this page.

include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	
// Editable content block gallery pages - not fully supported in this version?
if (!$start) { 
	?>
	<div id="maincontent">
		<!-- <div id="landing-section"><h1><?=ucfirst($page->getTitle())?></h1></div> -->
		<div id="landing-content">
		<?php
			//echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
		?>
		</div>
	</div>
	<?php 
} 
?>

<div id="primarycontent">
	<!-- Photographic reports -->
	<?php // echo $media->drawGallery("report", $type, $start); ?>
	<?=$gallery->drawGallery("image", $type, $start, $page->private)?>
	<?php // echo $media->drawVideo($type, $start); ?>
</div>
    
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>