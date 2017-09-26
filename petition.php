<?php

	include_once ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/petition.class.php");
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	
	//ini_set("display_errors", true);
	$petition = new Petition($page->getGUID());
	$feedback = "error";
	
	// Content
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);

	$thanks = new HTMLPlaceholder();
	$thanks->load($page->getGUID(), 'success');
	$thanks->setMode($mode);

	//print "mode($mode) content(".$content->draw().")<br>\n";
	
	// Tags
	$tags = new Tags($site->id, 1);

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
		if ($_POST['post_action']) $action = $_POST['post_action'];

		//print "got action ($action)<br>\n"; //exit();
		if ($action == 'Save changes') {
		
			$content->save();
			$thanks->save();
			$page->save(true);
			
			// Intelligent link panels
			//$tags->updateIntelligentLinkPanelDetails($page->getGUID(), $_POST['accuracy'], $_POST['maxlinks'], $_POST['show_related_content']);
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
			
			$author_redirect = '/treeline/pages/?action=edit&'.$feedback;
			$author_redirect = $referer."action=edit&".$feedback;
			
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
			
			//print "would redirect($redirectURL)<br>\n";
			redirect($redirectURL);
				
		}


		// Discard changes was pressed
		else if ($action == 'Discard changes') {
			
			// We have to manually release the page here as we are not saving the page.
			$page->releaseLock($_SESSION['treeline_user_id']);			
			$redirectURL = $referer."action=edit&";
			$redirectURL.=createFeedbackURL('notice','Your changes were not saved');
			//print "would go to $redirectURL<br>\n";
			redirect ($redirectURL);
		}
		
		else if ($action=="sign") {
			if ($petition->sign($_POST)) $showthanks = true;
			else {
				$message=$petition->errmsg;
			}
		}
				
	}
	

	//print "mode($mode) post(".print_r($_POST, true).") <br>\n";	
	// If we have nipped into preview mode we need to set up lots of stuff
	// Just do it in one place as too many templted to keep copying it to.
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page','petition','2colR'); // all attached stylesheets
	//if($page->style != NULL) $css[] = $page->style;

	// Are comments allowed on this page?
	if($page->getComment() && $site->getConfig("setup_comments")) {
		$comment = new Comment($page->getGUID());
		$css[]="comment";
	}
	$extraCSS = '';
	
	$js = array(); // all atatched JS behaviours
	if($mode == 'edit'){
		//$js[] = 'showHideDetails';
		$toolmode="petition";
		//$js[] = 'styleSwitcher';
	}
	$disablePageStyle = true;

	$extraJS = '
	
'; // etxra page specific  JS behaviours

	$mceFiles = array("content", "headerimage", "petition");

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	
	$pagetitle = $page->getTitle();
	if ($showthanks) $pagetitle = "Thank you for your support";
	
?>	

    <div id="contentholder">

        <h1 class="pagetitle"><?=$pagetitle?></h1>
        <div id="primarycontent">

        <?php 
		echo drawFeedback($feedback, $message);
		
        // Are we going to run this page in show comments mode? if so just display page comments.
        //print "show() conf() count() mode(".$mode.") id()<br>\n";
        if ($page->private!=($_SESSION['member_type']+0) && $mode!="edit" && $mode!="preview") {
            ?>
            <p>This page is only available to logged in members. To access this information please log in.</p>
            <?php
            echo drawFeedback("notice", $message);
        }
        else if (!$showthanks) {
		
            echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
			if ($mode=="edit") {
				?>
                <h4 style="padding-top:30px;">Thank you message</h4>
                <p>Please enter some text to be shown in response to this petition being signed.</p>
                <?php
				echo $thanks->draw();
			}
			else echo $petition->drawSignatureForm($_POST, $mode);
        }
		else if ($showthanks) echo $thanks->draw();
		
        ?>
        
    	</div>

        <div id="secondarycontent">
    
            <!--PANELS-->
            <?php 
            if ($info = $petition->drawStats() ) {
                ?>
                <h3 class="moreinfo">More infomration</h3>
                <ul id="petition-info">
                    <li class="left">Petition started on:</li>
                    <li class="right"><?=$info->start_date?></li>
                    <?php if ($info->end_date) { ?>
                    <li class="left">Petition closes on:</li>
                    <li class="right"><?=$info->end_date?></li>
                    <?php } ?>
                    <?php if ($info->total > $petition->threshold) { ?>
                    <li class="left">Signatures so far:</li>
                    <li class="right"><?=$info->total?></li>
                    <?php } ?>
                </ul>
                <?php
            }
            
            $otherpets=$petition->drawOther();
            if ($otherpets) {
                ?>
                <h3 class="">Other actions</h3>
                <?=$otherpets?>
                <?php
            }
            ?>
            
        </div>
	
    </div>    
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>