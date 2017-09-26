<?php

	//print "hit page.php(".time().")<br>\n";
	//$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	ini_set("display_errors", true);

	// Content
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);

	// Tags
	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		//$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
		$redirect = true;
		
		if ($_POST['post_action']) $action = $_POST['post_action'];
		//print "<!-- got post action ($action) -->\n";
		if ($action == 'Save changes' || $action=="Save") {

			//print "post(".print_r($_POST, true).")<br>\n";
			$content->save();
			$page->save(true);
			
			// Content is saved so redirect the user
			$feedback = 'feedback=success&message='.urlencode($page->getLabel("tl_pedit_msg_saved", true));
			
			//$author_redirect = '/treeline/pages/?action=edit&'.$feedback;
			$author_redirect = "/treeline/pages/?action=edit";
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
			//$publish_redirect .= '&'.$feedback;

			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");

			// For users with authorisation go to the publish option				
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				//print "would go to $publish_redirect<br>";
				$redirectURL = $publish_redirect; // show them the publish option
			}
			// Just go back to the page edit listing. 
			else $redirectURL = $author_redirect; 
			
			if ($redirect && $action=='Save changes') redirect($redirectURL);
				
		}

		// Discard changes was pressed
		else if ($action == 'Discard changes') {
			// We have to manually release the page here as we are not saving the page.
			$page->releaseLock($_SESSION['treeline_user_id']);			
			if ($redirect) redirect ('/treeline/pages/?action=edit&feedback=notice&message='.urlencode($page->getLabel("tl_pedit_err_nosave", true)));
		}
		
	}
	
	// If we have nipped into preview mode we need to set up lots of stuff
	// Just do it in one place as too many templted to keep copying it to.
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page', 'report', 'report-'.$page->getGUID()); // all attached stylesheets

	$extraCSS = '';
	
	$js = array("swipe"); // all atatched JS behaviours
	if($mode == 'edit'){
		//$js[] = 'showHideDetails';
		$toolmode="";
		$jsBottom[] = 'styleSwitcher';

		//$extraJSbottom .= '	CKEDITOR.replace(\'treeline_news1\', { toolbar : \'contentPanel\', height: \'60px\' });	';

		$extraJSbottom .= '
		';

	}
	$extraJS = ' '; // etxra page specific  JS behaviours

	$pagetitle = $page->getTitle();

	//$mceFiles = array("content", "headerimage");
	
	ob_start();	
	//$reportfile = $_SERVER['DOCUMENT_ROOT'].'/includes/snippets/reports/'.$page->name.'.inc.php';
	$reportfile = $_SERVER['DOCUMENT_ROOT'].'/includes/snippets/reports/annual-report-2017.inc.php';
	if (file_exists($reportfile)) include ($reportfile);
	else print "<p>Report file $reportfile does not exist</p>\n";
	$reportHTML = ob_get_contents();
	ob_end_clean();
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	//include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
	
	if ($mode=="edit") {
		print "draw content<br>\n";
		?>
        <div class="hide">
			<?=$content->draw()?>
        </div>
        <?php
	}

	echo $reportHTML;
		
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');
?>