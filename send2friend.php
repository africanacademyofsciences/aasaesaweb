<?php
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/contact.class.php');

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');

	
	// Content
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);
	$content->setHeight('500px');

	// Tags
	$tags = new Tags();
	$tags->setMode($page->getMode());
	
	$feedback="error";
	
	// if we're not using folder.php, we need to get the page created date...
	$pageDate = (!$pageDate || $pageDate==0) ? $page->date_created : $pageDate;
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');

		if ($action=="sendlink") {

			if (!$_POST['email'] || !$_POST['name']) $message[]="You must enter your friends email address and name to send this page to them";
			else if (is_email($_POST['email'])) $message[]="You have entered an invalid email address";
			if (!$captcha->valid) foreach($captcha->errmsg as $tmp) $message[]=$tmp;

			if (!count($message)) {
				$link=((substr($siteLink, -1, 1)=="/")?substr($siteLink, 0, -1):$siteLink).$_POST['page'];
				//print "got site($siteLink) page(".$_POST['page'].") link($link)<br>";
				//$to=$_POST['email'];
				$to=testinject($_POST['email']);
				$message[]="Sent page ".$link." to ".$_POST['name']." at $to";
				$feedback="success";
				$contact_email = $site->contact['email'];
				$contact_name = $site->contact['name'];
				//print "send from($contact_email) name($contact_name)<br>";
				$headers="From: $contact_name <$contact_email>"."\r\n";
				$headers .= "Reply-To: $contact_email"."\r\n";
				$headers .= "Return-Path: $contact_email"."\r\n";
				//$headers .= 'Bcc: phil.redclift@ichameleon.com' . "\r\n";
				$headers .= 'X-Mailer: PHP/' . phpversion();
				// Grap these headers from config...
				$msg="Hi ".$_POST['name']."

A friend of yours was viewing the page at $link and thought you might find it interesting.

Please accept our apologies if this message has reached you in error.

Best regards
".$site->name."
";				
				///print "mail($to, 'A friend', $msg, $headers)<br>";
				if (!mail($to, "An interesting page for you to check out", $msg, $headers)) {
					//print "mail function failed??<br>";
				}
				$_POST['name']=$_POST['email']='';
			}
		}
	
		else if ($action == 'Save changes') {

			$content->save();
			$page->save(true);
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
			
			$referer .= $feedback;
			$referer .= '&action=edit';
			
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
			$publish_redirect .= '&'.$feedback;
			
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				redirect($publish_redirect); // show them the publish option
			} else{
				redirect($referer); // otherwise take the user back to the edit pages page
			}

		}
		// Posted in preview mode
		else if ($action=="Preview") {
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
	
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page','forms','contact'); // all attached stylesheets
	$extraCSS = '
	
';
	if ($siteData->ltr=="rtl") {
		$extraCSS.="form#sendfriend fieldset label { float:right; } ";
	}

	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	if ($mode=="edit") {
		$disablePageStyle=true;
		$mceFiles=array("contact");
	}
	
	$orderBy = ($location[0]=='news') ? 'date_created DESC' : 'sort_order ASC';

	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
?>	

    <h1 class="pagetitle"><?=$page->getTitle()?></h1>
    <div id="primarycontent">
	    <?php
        echo drawFeedback($feedback, $message);
        
		//echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
        if($page->getMode() == 'wysiwyg') { echo '{content}'; } 
        if($mode!="edit") { 

			$link = $_SERVER['REQUEST_METHOD']=="GET"?$_GET['page']:$_POST['page'];
			//print "got link($link)<br>\n";
			?>
        	<form method="POST" action="#" id="sendfriend" class="std-form" >
            <fieldset class="border">
                <input type="hidden" name="treeline" value="sendlink" />
                <input type="hidden" name="page" value="<?=str_replace("_AMP_", "&", $link)?>" />
            	<p><?=$page->drawLabel("frienddetail", "Enter your friends details")?></p>
                <fieldset class="padtop field">
                    <label for="send_email"><?=$page->drawLabel('friendemail', 'Friends email address')?></label>
                    <input type="text" name="email" id="send_email" value="<?=$_POST['email']?>" /><br />
                </fieldset>
                <fieldset class="field">
                    <label for="send_name"><?=$page->drawLabel('friendname', "Your friends name")?></label>
                    <input type="text" name="name" id="send_name" value="<?=$_POST['name']?>" />
                </fieldset>
				<?=($site->getConfig("setup_use_captcha")?$captcha->drawForm():'')?>
                <fieldset class="field buttons">
                    <label for="submit" style="visibility:hidden;">Submit</label>
                    <input type="submit" class="submit" value="<?=$page->drawLabel('sendlink', "Send")?>" />
                </fieldset>
            </fieldset>
            </form>
        	<?php 
		}
		else {
			?>
            <p>Input form disabled in edit mode</p>
            <?php
		} 
		?>
    </div>
    <div id="secondarycontent">
		<?php if ($content->draw() || $mode=="edit") { ?>
            <div class="panel <?=($mode=="edit"?"":"rounded")?>">
                <?=$content->draw()?>
            </div>
        <?php } ?>
    </div>
    
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>

