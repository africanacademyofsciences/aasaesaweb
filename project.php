<?php

//print "SERVER(".print_r($_SERVER, true).")<br>\n";
//print "ENV(".print_r($_ENV, true).")<br>\n";
//print "SESSION(".print_r($_SESSION, true).")<br>\n";
//print "REQ(".print_r($_REQUEST, true).")<br>\n";

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

	// Panels
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');
	$panels->setMode($mode);

	$feedback="error";
	$message = array();
	
	$show = read($_POST,'shownews',false);
	$show = ($show>'') ? 1 : false ;
	
	// if we're not using folder.php, we need to get the page created date...
	$pageDate = (!$pageDate || $pageDate==0) ? $page->date_created : $pageDate;
	

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
			$panels->save();
			
			// Intelligent link panels
			//$tags->updateIntelligentLinkPanelDetails($page->getGUID(), $_POST['accuracy'], $_POST['maxlinks'], $_POST['show_related_content']);
			
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
		
		// Delete a panel from the panel list
		else if ($action=="Delete") {
			if (is_object($panels)) $page->deletePanel($panels, $_POST['treeline_panels'], $_POST['delete_panel']);
		}
		
		// Login to members area
		else if ($action=="login") {
			include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/login.class.php');
			$login = new MemberLogin();
			$message = $login->logIn();
		}

		else if ($action == "process-form") {
			//$message[]="Form processing ...";
			$form = new Form($_POST['fid']);
			$data_id = $form->processData($_POST, $_POST['data_id'], $_POST['member_id']);
			if (count($form->errormsg)) {
				foreach ($form->errormsg as $tmp) {
					$message[]=$tmp;
				}
			}
			else {
				$hide_submit_button_just_this_once = true;
				$message[]=($form->successmsg?$form->successmsg:"Your information has been saved");
				//$message[]="Hide submit(".$hide_submit_button_just_this_once.")";
				$feedback="success";
				$form->sendData($data_id);
				// We are removing the post data instead
				$hide_submit_button_just_this_once = false;
			}
			unset($form);
		}
				
	}
	

	// If we have nipped into preview mode we need to set up lots of stuff
	// Just do it in one place as too many templted to keep copying it to.
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page'); // all attached stylesheets
	if($page->style != NULL && $mode=="edit") $css[] = $page->style;
	//print "Style(".$page->style.") <br>\n";
	$primarycols = 8;
	if ($page->style=="1col") $primarycols = 12;
	


	// Are comments allowed on this page?
	$commentHTML = '';
	$comment = new Comment($page->getGUID());
	if($page->getComment() && $site->getConfig("setup_comments")) {
		$css[]="comment";
		$commentHTML = $comment->draw($_GET['commentid']); 
	}

	$extraCSS = '';
	
	$js = array("swipe"); // all atatched JS behaviours
	if($mode == 'edit'){
		//$js[] = 'showHideDetails';
		$toolmode="";
		$jsBottom[] = 'styleSwitcher';

		//$extraJSbottom .= '	CKEDITOR.replace(\'treeline_news1\', { toolbar : \'contentPanel\', height: \'60px\' });	';

		$extraJSbottom .= '
			CKEDITOR.replace(\'treeline_content\', { toolbar : \'contentStandard\' });
		';

	}
	$extraJS = ' '; // etxra page specific  JS behaviours

	$pagetitle = $page->getTitle();

	//$mceFiles = array("content", "headerimage");
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');

?>
<div class="main-content">
    <div class="container">
		<div class="col-xs-12" id="primarycontent">
			<?php 
            echo drawFeedback($feedback, $message);
            
			if ($mode=="edit") {
				?>
				<p>This page is intended only for use on the GCA eMarketplace site. However other sites can use it as a simple single column content page.</p>
				<?php
			}
			echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
            ?>
        </div>
	</div>    
</div>

<?php
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');
?>