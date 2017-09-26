<?php

	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/event.class.php");	

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	//$mode = read($_REQUEST,'mode','');

	//$_SESSION['user_registered']=false;
	$register_now=isset($_REQUEST['register']);
	
//ini_set("display_errors", true);
//error_reporting(E_ALL);
	$event = new Event($page->getGUID());
	
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

	// Content
	$jumbo = new HTMLPlaceholder();
	$jumbo->load($page->getGUID(), 'jumbo');
	$jumbo->setMode($mode);
	//print "jumbo mode($mode) content(".$jumbo->getMode().")<br>\n";
	
	$show = read($_POST,'shownews',false);
	$show = ($show>'') ? 1 : false ;
	
	$feedback="error";
	$message = array();
	
	// Only used for event registration form processing
	$action=$_SERVER['REQUEST_METHOD']=="POST"?$_POST['action']:$_GET['action'];

	
	// if we're not using folder.php, we need to get the page created date...
	$pageDate = (!$pageDate || $pageDate==0) ? $page->date_created : $pageDate;

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
		if ($_POST['post_action']) $action = $_POST['post_action'];
		//print "got action ($action)<br>\n";
	
		if ($action == 'Save changes') {
		
			$content->save();
			$jumbo->save();
			$page->save(true);
			$panels->save();
			
			// intelligent link panels
			$tags->updateIntelligentLinkPanelDetails($page->getGUID(), $_POST['accuracy'], $_POST['maxlinks'], $_POST['show_related_content']);
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
			
			$referer .= $feedback;
			$referer .= '&action=edit';
			
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
			$publish_redirect .= '&'.$feedback;
			
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				//print "would go to $publish_redirect<br>";
				redirect($publish_redirect); // show them the publish option
			} else{
				redirect($referer); // otherwise take the user back to the edit pages page
			}

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
				//print "<!-- sD($data_id) -->\n";
				$form->sendData($data_id);
			}
			unset($form);
		}

		/*
		// Posted in preview mode
		else if ($action=="Preview") {

			// Maybe should save the page content to the user table.
			// Not sure how to deal with panel changes though?
			// Maybe I dont actually need to save anything 
			// hmmm...
			//$content->save();
			//$page->save();
			//$panels->save();
			if ($_POST['style']) $page->setStyle($_POST['style']."col");
			
			$page->setMode("preview");
			$mode="preview";
			$content->setMode($mode);
			$panels->setMode($mode);
			$tags->setMode($mode);
			
			$showPreviewMsg=true;
		}
		*/
		else if ($action == 'Discard changes') {			
			$page->releaseLock($_SESSION['treeline_user_id']);			
			$referer .= 'action='.$page->getMode().'&'.createFeedbackURL('error','Your changes were not saved');
			redirect ($referer);
		}
		
		// Delete a panel from the panel list
		else if ($action=="Delete") {
			if (is_object($panels)) $page->deletePanel($panels, $_POST['treeline_panels'], $_POST['delete_panel']);
		}

        if ($action=='process_entry_form' || $action=="Submit entry") {
		
			$ef_entry_id=$_POST['entry_id'];
			
			//include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/event_entry_process.php");
			$message[] = "You cannot register for this event";
			if (!count($message)) {
				$message[]="Register success";
				$message[]="We will let you know once your application to attend this event has been processed.  In the meantime if you have any questions please contact our events team</a>";
				$feedback="success";
				
				$register_now=false;
				
				// Need to add a notification tasky thing and email all the superusers.....
				$tasks=new Tasks($site->id);
				if ($tasks->add(0, 'Event application', $event->id, "ENTRY ID:".$ef_entry_id, 7, 0)) {
					$pageLink='(<a href="'.$page->drawLinkByGUID($page->getGUID()).'">'.$page->getTitle().'</a>)';
					$sendParams = array(
						"PAGELINK"=>$pageLink
						);
					$tasks->notify("EVENT-APPLY", $sendParams, 'Publisher+');
				}				
			}
			else $feedback="error";
		}		

	}
	
	
	// If we have nipped into preview mode we need to set up lots of stuff
	// Just do it in one place as too many templted to keep copying it to.
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page', 'events'); // all attached stylesheets
	if($page->style != NULL && $mode=="edit") $css[] = $page->style;
	//print "Style(".$page->style.") <br>\n";
	$primarycols = 8;
	if ($page->style=="1col") $primarycols = 12;
	

	// Are comments allowed on this page?
	if($page->getComment() && $site->getConfig("setup_comments")) {
		$comment = new Comment($page->getGUID());
		$css[]="comment";
	}
	
	if (isset($_REQUEST['register']) && $_REQUEST['id']>0) {
		$extraCSS = ' ';
	}
	
	$js = array(); // all atatched JS behaviours
	if($mode == 'edit'){
		$js[] = 'styleSwitcher';

		$extraJSbottom .= '
			CKEDITOR.replace(\'treeline_content\', { toolbar : \'contentStandard\' });
            CKEDITOR.replace(\'treeline_jumbo\', { toolbar : \'contentStandard\' });
		';

	}
	$extraJS = ''; // etxra page specific  JS behaviours

	$orderBy = ($location[0]=='news') ? 'date_created DESC' : 'sort_order ASC';
	
	$pagetitle = $page->getTitle();

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/html/events.php'); 
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
	
	/*
	?>	

    <div id="sidebar">
        <!--SUBNAV -->
        <ul class="submemu">
        <?=$menu->drawSecondaryByParent($page->getPrimary($pageGUID), $pageGUID, $orderBy)?>
        </ul>
        <?php //include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/donatebutton.php');	?>
        <?php // echo $left_panels->draw(array(13), array()); ?>
    </div>

    <div id="contentholder">

        <h1 class="pagetitle"><?=$page->getTitle()?></h1>
        
        <div id="primarycontent">
            <?php 
            echo drawFeedback($feedback, $message); 
            
            if ($page->private && !$_SESSION['member_id'] && $mode!="edit" && $mode!="preview") {
                ?>
                <p>This page is only available to logged in members. To access this information please log in below.</p>
                <?php
                include $_SERVER['DOCUMENT_ROOT']."/includes/ajax/memberLogin.php";
            }
            else if ($register_now) { 
                include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/event_entry_form.php");
            }
            else if (isset($_GET['showcomments']) && $site->getConfig('setup_comments')==1 && ($comment->count>0 || ($mode=="preview" && $_GET['commentid']>0)))	{ 
                echo $comment->draw($_GET['commentid']); 
            }
            else {
                echo $event->drawEventInfo();
                echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
    			//echo $event->drawBookingButton();
				
                if ($page->getMode()!="edit" && $page->getComment() && $site->getConfig("setup_comments")==1) {
                    include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/formAddComment.php"; 
                }
            }
            ?>
        </div>
        <div id="secondarycontent">
            <!--PANELS-->
            <?php
                echo $panels->draw(array(), array(13))
            ?>
        </div>
    </div>
    
	<?php 
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');

	*/

?>