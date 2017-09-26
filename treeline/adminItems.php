<?php
	ini_set("display_errors", 1);
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/adminItem.class.php");
	
	$adminItemTitle = substr($adminItemType, -1, 1)=="s"?substr($adminItemType, 0, -1):$adminItemType;
	$adminItem = new adminItem($adminItemTitle);
	
	// Setup variables needed for this page
	$action = read($_REQUEST,'action',NULL);
	$section = read($_REQUEST,'section',NULL);
		
	$message = array();
	$feedback = read($_REQUEST,'feedback','notice');
	
	$orderBy = read($_REQUEST,'order','date');
	$currentPage = read($_REQUEST,'page',1);
	$responseId = read($_REQUEST,'response_id',NULL);

	$adminItemId = read($_REQUEST,$adminItemType.'_id',NULL);
	
	$rating = read($_GET,'rating',NULL);
	
	if($rating && !$adminItem->getRating($responseId)){ // Add rating .. but only if there isn't 1 already
		$message = $adminItem->rateResponse($responseId, $rating, $adminItemType);
	}
	
	// Any form processing?
	if ($_SERVER['REQUEST_METHOD']=="POST") {	

		if ($action == "create") {
			if ($adminItem->createItem($adminItemType, $client_id)) {
				$feedback="success";
				$message[]=$page->drawLabel("tl_adit_".substr($adminTitle, 0, 4)."_saved", "Your $adminItemTitle has been saved");
				$action="";
				
				// Notify somebody...
				$msg = "The ".$adminItemType." form on the site ".$_SERVER['HTTP_HOST']." was completed.
				
The message posted was :

".$_POST['description']."

Treeline ;o)

";
				mail("phil.redclift@ichameleon.com", "Treeline feedback submitted", $msg);
			}
			else $message = $adminItem->errmsg;
		}
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables','adminItems'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? ucwords($adminItemType).' : '.ucwords($action) : ucwords($adminItemType);
	$pageTitle = ($action) ? ucwords($adminItemType).' : '.ucwords($action) : ucwords($adminItemType);
	
	$pageClass = $adminItemType;
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
		
?>

<div id="primarycontent">
	
    <div id="primary_inner">
    
		<?php
        echo drawFeedback($feedback,$message);
    
		
		// Action: Create new admin item
		//print "A($action) item($adminItemId) resp($responseId)<br>\n";
        if ($action == 'create' && !$adminItemId && !$responseId) {  
			$admin_script = $_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ajax/send'.ucwords($adminItemType).'.php';
			//print "include($admin_script)<br>\n";
	        include($admin_script);
			echo treelineBox($page_html, $page->drawLabel("tl_feedb_tit_".$adminItemTitle, "Submit your $adminItemTitle"), "blue");
        }  
		else {
		
			// show a admin item item
			if($adminItemId) { 
				$page_html = $adminItem->drawItemById($client_id, $adminItemType, $adminItemId);
				echo treelineBox($page_html, "Reveiw item", "blue");
			}  
	
			// Show a response 
			else if($responseId) {  
				echo treelineBox($adminItem->drawResponseById($responseId, $adminItemType), "Response details", "blue");
			} 

			// No Action: Show previous items + responses || show options e.g.  Create item
			$page_html = '<p><a href="/treeline/'.$adminItemType.'/?action=create">Send us '.$adminItemType.'</a></p>';
			if ($client_id>0) $page_html.=$adminItem->drawItems($client_id,$adminItemType,$orderBy,$currentPage);
			echo treelineBox($page_html, "Got something to say?", "blue");
			
		}        
		?>        
	</div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>