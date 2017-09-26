<?

	// TAGS
	$tags = new Tags($site->id, 1);

	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($page->getMode());	
	
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		if (strpos($referer,'?') > 0) {
	// If we've already got a querystring in the referring URL, we need to append the message onto it
			$referer .= '&';
		}
		else {
			// Otherwise, create a new querystring
			$referer .= '?';
		}
	
		if (read($_POST,'treeline','') == 'Save changes') {
			$content->save();
			$page->save();			
			$referer .= 'message='.urlencode("Changes saved to page '<strong>".$page->getTitle

()."</strong>' in section <strong>". $page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID())) 

."</strong>");
			$referer .= '&action=edit';
			redirect ($referer);

		}
		else if (read($_POST,'treeline','') == 'Discard changes') {
			$referer .= 'message='.urlencode("Changes discarded");
			$referer .= '&action=edit';
			redirect ($referer);
		}
	}
	
	// Page specific options
	
	$pageClass = 'tags'; // used for CSS usually
	
	$css = array('tags','forms','page','1col'); // all attached stylesheets
	$extraCSS = ''; // extra page specific CSS
	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	if ($_GET['tag']) {
		$pageTitle = ucwords($_GET['tag']);
		$global_meta_desc = "Read articles related to ".ucwords($_GET['tag'])." and find out what ".$site->properties['site_title']." is doing to help. Please give your support today!";
		$global_meta_keyw = ucwords($_GET['tag']).", ".$site->properties['site_title'];
	}
	else $pageTitle = "Tags";
	$pagetitle = $pageTitle;

include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');

?>
<div class="main-content">
	<div class="container">
		<div class="col-lg-12" id="primarycontent">
			<?
			if($_GET['tag']) $tagslist = $tags->drawContentByTag($_GET['tag']);
			else $tagslist = $tags->drawTagCloud();
			echo $tagslist;
			?>
		</div>
	</div>
</div>            

<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>