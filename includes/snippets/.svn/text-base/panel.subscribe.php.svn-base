<?php
//print "mode($mode) pagemode({$page->getMode()})<br>";// exit;
	if ($testing) print "panel search siteLink($siteLink)<br>";
	
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'panelcontent');

	$referer = read($_REQUEST,'referer','/treeline/');

	$page->loadByGUID($pageGUID);
	$pageTitle = $page->drawTitle();
	$panelStyle = $page->getStyle();
	
	if ($page->getMode() == 'inline') {
		// If we've put the panel in 'list' mode -- ie, we've included the panel in a page we're editing -- we don't want to actively edit the content:
		$content->setMode('view');
	}
	else {
		$content->setMode($page->getMode());
	}
	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

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
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to panel '<strong>". $page->drawTitle() ."</strong>'");
			$referer .= $feedback;		
			$publish_redirect = '/treeline/panels/?action=publish&guid='.$page->getGUID();
			$publish_redirect .= '&'.$feedback;
			
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				redirect($publish_redirect); // show them the publish option
			} else{
				redirect($referer); // otherwise take the user back to the edit pages page
			}

		} else if (read($_POST,'treeline','') == 'Discard changes') {
			$referer .= createFeedbackURL('error','Your changes were not saved');
			redirect ($referer);
		}
	}
	
	// END DATABASE INTERACTION
	
	// START PREVIEW/EDIT/VIEW MODE
		
	if ($page->getMode() == 'preview'){ // PREVIEW MODE: SHOW PROPER HTML
		//$page->loadByGUID($pageGUID);
		$pageClass = 'panel'; // used for CSS usually
		//$panelStyle = $page->getStyle();
	
		$css = array('page','3col','editMode'); // all attached stylesheets
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	}
		
		if ($page->getMode() == 'edit') { // EDIT MODE: BASIC HTML
		
		?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Panel - <?=$page->drawTitle()?></title>
        <style type="text/css">
            @import url("/style/global.css");
            @import url("/treeline/style/editMode.css");
            body{background: #FFF;}
        </style>
        </head>
        
        <body>
        <?php
		
		// EDIT MODE: SHOW EDIT FORM
		
		// Should this be part of the treeline class?
		echo $page->drawPanelTinyMCE();

		// Now add the 'edit' form -- is there a neater way to do this, rathr than sandwiching the panel in form tags like this?
		// $dbqs = ($DEBUG)?'?debug':'';
		$dbqs = '';
		$action = $_SERVER['REQUEST_URI'].$dbqs;
		//$referer = read($_SERVER,'HTTP_REFERER','');
		// If we're debugging, apend ?debug to the form post
		echo '<form id="treeline_edit" action="'.$action.'" method="post">'."\n";
	
		// Add the panel toolbar:	
		echo $page->drawPanelToolbar();
		
		
		$currentStyle = ($_POST['style']) ? $_POST['style'] : $page->style_id;
		include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions/pages.php");
		echo drawStyleSwitcherMenu($currentStyle, 6);	// The six means draw the styles availble for panels (which have a template id of 6)
		
		
		echo '<input type="hidden" name="action" value="save" />'."\n";
		echo '<input type="hidden" name="mode" value="'.$page->getMode().'" />'."\n";
		echo '<input type="hidden" name="referer" value="'.$referer.'" />'."\n";
		echo '<input type="hidden" name="title" value="'.$page->drawTitle().'" />'."\n";
		
		echo '<div style="width: 300px; margin:0 auto;">'."\n"; /* This should inherit from a stylesheet somewhere, I think */

	}	
	
?>
<?php if ($page->getMode() == 'preview'){ // PREVIEW MODE: SHOW DUMMY CONTENT ?>
<h1>Dummy content</h1>
<div id="sidebar">
	<ul>
    	<li><a href="#">Sub-section</a></li>
        <li><a href="#">Sub-section</a></li>
        <li class="subon"><a href="#">Sub-section</a>
        <ul>
        	<li><a href="#">Sub-section</a></li>
            <li><a href="#">Sub-section</a></li>
            <li><a href="#">Sub-section</a></li>
        </ul>
        </li>
        <li><a href="#">Sub-section</a></li>
    </ul>
</div>
    <div id="primarycontent">
    <p>This page doesn't have any content, its purpose is to highlight what the panel (on your right) will look like in context.</p>
    <p>By seeing this so-called 'Dummy content' next to your new panel you should get a better feeling as to how the panel will look on your website.</p>
    </div>
    <div id="secondarycontent">
<?php } ?>
<div class="panel <?=$panelStyle?>">
    <h3><?= $pageTitle?></h3>
    <?=$content->draw()?>
    <?php 
		if ($mode!="edit") {
        	include $_SERVER['DOCUMENT_ROOT']."/includes/templates/subscribeForm.inc.php";
			?>
			<p style="padding-bottom:0px;"><a href="<?=$siteLink?>/privacy-policy/"><?=$labels['privacy']['txt']?></a> | <a href="<?=$siteLink?>/enewsletters/"><?=$labels['mysubs_link']['txt']?></a></p>
    	    <?php
		}
	?>
</div>
<?php

	if ($page->getMode() == 'edit') {
		// EDIT MODE: CLOSE FORM AND BASIC HTML
		// Close the form [and containing div] we opened above:
	?>
        </div>
       </form>
      </body>
     </html>
<?php
}
	if ($page->getMode() == 'preview') {
		// PREVIEW MODE: CLOSE PROPER HTML
		// Close containing div we opened above:
	?>
    </div>
        
<?php	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');	
	}
?>