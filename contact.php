<?php

// CONTACT FORM
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/contact.class.php');
	
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');
	$type = read($_REQUEST,'type','');
	
	$contact = new Contact();

	$feedback="error";
	
	if ($_POST['action']=="send") {
		if (!$captcha->valid) $message=$captcha->errmsg;
		//print_r($message);
		if (!$contact->sendEmail()) {
			$message=$contact->errmsg;
		}
		else {
			$feedback = "success";
			$message[]=$page->drawLabel("contact-sent", "Your enquiry has been sent");
		}
	}
		

	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);
	
	//tags
	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());

	
	// if we're not using folder.php, we need to get the page created date...
	$pageDate = (!$pageDate || $pageDate==0) ? $page->date_created : $pageDate;
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
		if ($_POST['post_action']) $action = $_POST['post_action'];

		if ($action == 'Save changes') {

			$content->save();
			$page->save(true);
			
			// intelligent link panels
			$tags->updateIntelligentLinkPanelDetails($page->getGUID(), $_POST['accuracy'], $_POST['maxlinks'], $_POST['show_related_content']);
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
			
			$referer .= $feedback;
			$referer .= '&action=edit';
			
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
			//$publish_redirect .= '&'.$feedback;
			
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				redirect($publish_redirect); // show them the publish option
			} else{
				redirect($referer); // otherwise take the user back to the edit pages page
			}

		}
		// Posted in preview mode
		else if ($action=="Preview") {
			if ($_POST['style']) $page->setStyle($_POST['style']."col");
			$mode="preview";
			$page->setMode($mode);
			$content->setMode($mode);
			$showPreviewMsg=true;
		}
		
		else if ($action == 'Discard changes') {			
			$page->releaseLock($_SESSION['treeline_user_id']);			
			$referer .= 'action='.$page->getMode().'&'.createFeedbackURL('error','Your changes were not saved');
			redirect ($referer);
		}
	}
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	
	$pageClass = 'contact'; // used for CSS usually
	
	$css = array('page','contact'); // all attached stylesheets
	if($page->style) $css[] = $page->style;
	
	$extraCSS = ' '; // extra page specific CSS

	if ($siteData->ltr=="rtl") {
		$extraCSS.="form.contact fieldset label { float:right; } ";
	}
	
	$extraJS = ''; // etxra page specific  JS behaviours

	if ($mode=="edit") {
		$disablePageStyle=true;
		$mceFiles=array("contact");
	}
	
	$pagetitle = $page->getTitle();
	$pagetitle = "Contact us";

	$extraJSbottom .= '
		CKEDITOR.replace(\'treeline_content\', { toolbar : \'contentStandard\' });
		';

	//$pageClass = "smurf";
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');	

	
?>	

<div class="main-content">
    <div class="container">

     	<div class="col-xs-12 col-sm-6" id="primarycontent">
           	<div class="contact-details">
                <h3>How to contact us</h3>
                <address>
					<?php 
                    if ($content->draw() || $mode=="edit") { 
                        echo $content->draw();
                    } 
                    ?>
                </address>
                
                <h3>How to contact us</h3>
                <address>
                      
                      <ul>
                          <li><i class="ion-ios-navigate-outline"></i>8 Miotoni Lane, Karen</li>
                          <li>P.O. Box 24916-00502</li>
                          <li>Nairobi, Kenya</li>
                      </ul>
                      <ul>
                          <li><i class="ion-ios-telephone-outline"></i>+254 20 240 5150</li>
                          <li>+254 20 806 0674</li>
                      </ul>
                      <ul>
                          <li><i class="ion-iphone"></i>+254 736 888 001</li>
                          <li>+254 725 290 145</li>
                      </ul>
                      <ul>
                          <li><i class="ion-ios-printer-outline"></i>+254 20 8060674</li>
                      </ul>
                      <ul>
                          <li><a href="#"><i class="ion-ios-email-outline"></i>Email us</a></li>
                      </ul>
                      
                  </address>
            </div>
        </div>
        
     	<div class="col-xs-12 col-sm-6" id="secondarycontent">

			<?=drawFeedback($feedback, $message)?>
            <?php
                if ($sent) { 
					// if they have sent the form show a thankyou message
					?>
                    <p><strong>Thank you for contacting us.</strong></p>
                    <p>If your message requires a reply, we will get in touch with you as soon as possible.</p>
                    <?php
                } 
                else if ($mode=="edit") {
                    echo "<p>Contact form disabled in edit mode</p>";
                }
                else  {
                    echo $contact->drawContactForm($type);
                }  
            ?>
            
        </div>

	</div>
</div>
    
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>


