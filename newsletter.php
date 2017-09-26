<?php


//ini_set("display_errors", 1);
//include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");

$nid=$_GET['id'];
if ($nid>0) {

	//print "running newsletter pages for letter($nid)";
	$newsletter = new newsletter($nid);
	
	if ($newsletter->msv) $site = new Site($newsletter->msv);
	
	if ($newsletter->validate() && $site->id>0 && $site->id == $newsletter->msv) {
	
		
		//$showPreviewMsg=true;
		//include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewModeTop.inc.php");

		// Generate HTML in screen mode
		$newsletter->setMode("VGA");
		
		$page=new Page();
		$newsletter->labels=$page->getTranslations($newsletter->msv, $newsletter->sitelang);
		$strHTMLEmail = $newsletter->getHTMLEmail();
		$strHTMLPlain = $newsletter->getPlainEmail();
		if ($_GET['s']=="plain") echo nl2br($strHTMLPlain);
		else echo $strHTMLEmail;
	}
	else {
	
		$tags = new Tags();

		// Header image
		$header_img = new HTMLPlaceholder();
		// Does this site have a header image?
		$header_img->load($siteID, 'header_img');
		if (!$header_img->draw()) {
			// If not see if the primary site has a header image
			$header_img->load($siteData->primary_msv, 'header_img');
			if (!$header_img->draw()) {
				// If not use the banner fro the main website.
				$header_img->load(1, 'header_img');
			}
		}
		$header_img->setMode("view");

		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
		$message[]="That newsletter is invalid or was not found";
		?>
	    <h1 class="pagetitle"><?=$page->getTitle()?></h1>
	    <?=drawFeedback($feedback, $message)?>
	    <div id="primarycontent">
        </div>
        <?php
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');
	}
}
else {
	$_GET['error'] = 404; // set error number
	header ('HTTP/1.1 404 Page Not Found');
	include($_SERVER['DOCUMENT_ROOT'].'/error.php');
}

?>