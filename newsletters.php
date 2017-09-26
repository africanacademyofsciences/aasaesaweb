<?php

//ini_set("display_errors", true);
//error_reporting(E_ALL);

// EMAIL NEWSLETTER SUBSCRIPTION
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/mailchimp.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/subscriber.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/member.class.php');

$newsletter = new Newsletter();
// $newsletter->setTesting("sub");

//$success = array();

include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

$action = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'action', 'subscribe');
$curaction=$action;

$message = array();
$feedback = "error";	
	
if ($mode != "edit") {
	if ($_SERVER['REQUEST_METHOD']=="POST") {
	
		// Unsubscribe
		if($_POST['action'] == 'unsubscribe'){
			if ($newsletter->unsubscribe($_POST['email'])) {
				$message[]=$page->drawLabel('unsubscribe-success', "You have been removed from all email news");
				$feedback="success";
			}
			else $message = $newsletter->errmsg;
		}
		else if (!count($_POST['preference']) && $_POST['all']!=1 && $needpref) {
			$message[]="You did not select any preferences";
		}
		// Subscribe
		else if($action=='subscribe' && !$_POST['homelink']){
			if($newsletter->subscribe()) {
				$message[] = $page->drawLabel('subscribe-success','Your preferences have been saved');
				$feedback="success";
			}
			else if (count($newsletter->errmsg)==1 && $newsletter->errmsg[0]=="This email address is already subscribed to our mailing lists, your email preferences have been updated but your personal data has not been modified") {
				$feedback="success";
				$message= $newsletter->errmsg;
			}
			else {
				$message = $newsletter->errmsg;
			}
		}
	}
}


// Check if we have an usubscribe request from a newsletter
if($_GET['oid']>0 && $_GET['mid']>0){
	$action="unsubscribe";
	if ($newsletter->unsubscribe('', $_GET['mid'])) {
		$message[] = $page->drawLabel('subscribe-success','You have been');
		$feedback = "success";
		$action = "subscribe";
	}
	else $message = $newsletter->errmsg;
}


	// Newsletter page side panel content
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);
	$content->setHeight('500px');

	// Side content
	$main_content = new HTMLPlaceholder();
	$main_content->load($pageGUID, 'main-content');
	$main_content->setMode($mode);
	
	// Tags
	$tags = new Tags($site->id, 1);
	$tags->setMode($mode);
	
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$referer.=(strpos($referer, "?")?"&":"?");
		$tl_action = read($_POST,'treeline','');
		if ($_POST['post_action']) $tl_action = $_POST['post_action'];
	
		if ($tl_action == 'Save changes') {
			$content->save();
			$main_content->save();
			if (is_object($header_img)) $header_img->save();
			$page->save(true);
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($pageGUID))."</strong>");
			
			$referer .= $feedback;
			$referer .= '&action=edit';
			
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$pageGUID;
			$publish_redirect .= '&'.$feedback;
			
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				redirect($publish_redirect); // show them the publish option
			} else{
				redirect($referer); // otherwise take the user back to the edit pages page
			}

		}
		// Posted in preview mode
		else if ($tl_action=="Preview") {
			$mode="preview";
			$page->setMode($mode);
			$content->setMode($mode);
			$main_content->setMode($mode);
			$showPreviewMsg=true;
		}
		else if ($tl_action == 'Discard changes') {			
			$page->releaseLock($_SESSION['treeline_user_id']);			
			$referer .= 'action='.$mode.'&'.createFeedbackURL('error','Your changes were not saved');
			redirect ($referer);
		}
	}

	// Page specific options
	
	$pageClass = 'enewsletters'; // used for CSS usually
	
	$css = array('page'); // all attached stylesheets
	if($page->style != NULL) $css[] = $page->style;

	$extraCSS = ' '; // extra page specific CSS
	
	$js = array(); // all atatched JS behaviours
	if($mode == 'edit'){
		//$js[] = 'jquery';
		//$js[] = 'styleSwitcher';
	}
	$extraJS = ''; // etxra page specific  JS behaviours
	
	if ($mode=="edit") {
		$disablePageStyle=true;
		$mceFiles=array("contact");

		$extraJSbottom .= '
			CKEDITOR.replace(\'treeline_main-content\', { toolbar : \'contentStandard\' });
			CKEDITOR.replace(\'treeline_content\', { toolbar : \'contentPanel\' });
		';
	}

	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");

	$pagetitle = "Join our mailing list";

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
	
?>
<div class="main-content">
    <div class="container">
		<div class="col-xs-12 col-sm-8" id="primarycontent">


			<? 
            echo drawFeedback($feedback, $message);
            
            // Successful subscription
            if($feedback == "success" && $action == "subscribe"){ 
                $newpassword=$db->get_var("select password from members where email='".$_POST['email']."'");
                $preferenceHTML = $newsletter->listPreferences($_POST['email']);
                if ($preferenceHTML && $preferenceHTML!="<p>You have elected to receive no information by email</p>") {
                    // Should extend this to send password also
                    $sendParams = array("EMAIL"=>$_POST['email'], 
                        "NAME"=>$_POST['name'], 
                        "PREFERENCES"=>$preferenceHTML
                        );
                    //print_r($sendParams);
                    $newsletter->sendText($_POST['email'], "SUBSCRIBE", $sendParams, true, true);
					//print "Sent subscribe to(".$_POST['email'].") p(".print_r($sendParams, 1).")<br>\n";
					
					// Notify admins that someone has registered
					if(addHistory($newsletter->member_id, 'Register', $page->getGUID(), "New member(".$newsletter->member_type_id.")", "members")) {
						/*
						//print "Added history<br>\n";
						$tasks=new Tasks($site->id);
						$sendParams = array(
							"NAME"=>$_POST['name'],
							"FELLOWSTAT"=>($newsletter->member_type_id==2?"This member is a fellow":"")
							);
						$tasks->notify("new-registration", $sendParams, 'Publisher+');
						*/
					}
					
					
                }
                ?><p><?=$labels['subscribe-email']['txt']?></p><?php
                echo $main_content->draw();
            } 
                    
            if ($mode!="edit") {			
                ?>
                <form id="sub-form" action="<?=$siteLink ?>enewsletters/" method="post">
                    <input type="hidden" name="action" value="<?php echo $action; ?>" />
                    
                    <?php 
                    if($action == 'subscribe' || $mode=="preview") { 
                        ?>
                        <fieldset id="sub-name" class="form-group form-group-sm">
                            <label class="sr-only" for="name"><?=$page->drawLabel('enews-name', "Full name")?>:</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?=$_POST['name']?>" placeholder="Full name" />
                        </fieldset>
            
                        <fieldset id="sub_email" class="form-group form-group-sm">
                            <label class="sr-only" for="email"><?=$page->drawLabel('enews-email', "Email address")?>:</label>
                            <input type="text" name="email" id="email" class="form-control" value="<?=($_GET['email'])?$_GET['email']:$_POST['email']?>" placeholder="Email address" />
                        </fieldset>

                        <p>If you would like to receive our newsletter or receive email updates when new material is added to the website please indicate your preferences below. You will be able to update your choices anytime.</p>
						<?=$newsletter->drawPreferences($site->id)?>

						<!--
						<p>If you are already an AAS fellow then please let us know here so we can also keep you up to date with other relevent material when it is added to the website.</p>
                        <fieldset class="form-group form-group-sm">
                            <input type="checkbox" class="checkbox" id="f_mtype" name="fellow" value="1" <?=($_POST['fellow']==1?'checked="checked"':"")?> />
                            <label class="" for="f_mtype">Are you a fellow</label>
                        </fieldset>
						-->
                        
                        <fieldset class="form-group form-group-sm">
                            <label class="sr-only" for="form_country">Country</label>
                            <select name="country" id="form_country" class="form-control">
                                <option value="0">Select country</option>
                                <?=$newsletter->drawCountrySelect($_POST['country'])?>
                            </select>
                        </fieldset>

                        <fieldset class="form-group form-group-sm">
                            <label class="sr-only" for="f_work">Where do you work</label>
                            <input type="text" name="work" id="f_work" class="form-control" value="<?=$_POST['work']?>" placeholder="Where do you work" />
                        </fieldset>
                        
                        <fieldset class="form-group form-group-sm">
                            <label class="sr-only" for="f_job">Job title</label>
                            <input type="text" name="job" id="f_job" class="form-control" value="<?=$_POST['job']?>" placeholder="Job title" />
                        </fieldset>

                        
                            
                        <!-- 
                        </fieldset>
                        <fieldset class="border" style="margin-top:20px;">
                
                        <legend><?=$labels['magmail']['txt']?></legend>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_houseno"><?=$labels['houseno']['txt']?>:</label>
                            <input type="text" name="houseno" id="form_houseno" class="text" value="<?=$_POST['houseno']?>" />
                        </fieldset>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_street"><?=$labels['street']['txt']?>:</label>
                            <input type="text" name="street" id="form_street" class="text" value="<?=$_POST['street']?>" />
                        </fieldset>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_address_2"><?=$labels['add2']['txt']?>:</label>
                            <input type="text" name="address_2" id="form_address_2" class="text" value="<?=$_POST['address_2']?>" />
                        </fieldset>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_locality"><?=$labels['locality']['txt']?>:</label>
                            <input type="text" name="locality" id="form_locality" class="text" value="<?=$_POST['locality']?>" />
                        </fieldset>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_towncity"><?=$labels['towncity']['txt']?>:</label>
                            <input type="text" name="towncity" id="form_towncity" class="text" value="<?=$_POST['towncity']?>" />
                        </fieldset>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_county"><?=$labels['county']['txt']?>:</label>
                            <input type="text" name="county" id="form_county" class="text" value="<?=$_POST['county']?>" />
                        </fieldset>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_postcode"><?=$labels['postcode']['txt']?>:</label>
                            <input type="text" name="postcode" id="form_postcode" class="text" value="<?=$_POST['postcode']?>" />
                        </fieldset>
        
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_telephone"><?=$labels['telephone']['txt']?>:</label>
                            <input type="text" name="telephone" id="form_telephone" class="text" value="<?=$_POST['telephone']?>" />
                        </fieldset>
                        
                        <?php //$newsletter->drawMailPreferences($siteID); ?>
        
                        <fieldset class="<?=$topclass?>" style="margin-top:20px;">
                            <label for="form_hearbout"><?=$labels['hearbout']['txt']?>:</label>
                            <input type="text" name="hearbout" id="form_hearbout" class="text" value="<?=$_POST['hearbout']?>" />
                        </fieldset>
        
                        <fieldset class="<?=$topclass?>">
                            <label for="form_contact" style="width:auto;"><?=$labels['contactvia1']['txt']?>:</label><br />
                            <label for="form_contact1"><?=$labels['contactvia2']['txt']?></label>
                            <div style="float:left;">
                            <input type="radio" name="contact" id="form_contact" class="text" value="1" <?=(($_POST['contact']==1)?"checked":"")?> /> <?=$labels['YES']['txt']?>
                            <input type="radio" name="contact" id="form_contact" class="text" value="0" <?=(($_POST['contact']==0)?"checked":"")?> /> <?=$labels['NO']['txt']?>
                            </div>
                        </fieldset>
                        
                        -->
                        
                        <?php 
                        if ($mode!="preview") { 
                            ?>
                            <fieldset class="form-group-sm">
                                <label for="f_submit" style="visibility: hidden;">Submit</label>
                                <button type="submit" class="btn btn-default btn-block"><?=ucfirst($curaction)?></button>
                            </fieldset>
                            <?php 
                        } 
					}
                    ?>
        
                </fieldset>
                </form>
                
                <!-- Link to alternate version of this page -->
                <p style="clear:left;">

                <?php 
				if($action == 'subscribe') { 
					$unsublink = $siteLink."member-login/";
					$unsublink = $site->link."enewsletters/?action=unsubscribe";
					?>
                    <a class="" id="unsubscribelink" href="<?=$unsublink?>"><?=$page->drawLabel('enews-unsub', 'Unsubscribe')?>?</a>
	                <?php 
				} 
				else { 
					?>
                	Newsletters are sent via our Mailchimp service. Please use the unsubscribe links at the bottom of any emails you no longer wish to receive.<br /><br />
                    <a class="" id="subscribelink" href="<?=$siteLink?>enewsletters/?action=subscribe"><?=$page->drawLabel('enews-subscribe', 'Subscribe')?>?</a>
	                <?php 
				}
				?>	
                </p>
                    
                
                <?php 
            } 
            else { 
                ?>
                <p>Subscribe form disabled in edit mode</p>
                <p>Please enter below some standard text to display upon successful subscription/modification of update preferences</p>            
                <?=$main_content->draw()?>
                <?php 
            } 
            ?>
                
            <?php if($mode == 'wysiwyg') { echo '{content}'; } ?>
    

		</div>

        <div id="secondarycontent" class="col-xs-12 col-sm-4">
        
			<?php 
            if ($content->draw() || $mode=="edit") { 
                ?>
                <div class="panel panel_orange">
                    <?=$content->draw()?>
                </div>
                <?php 
            } 
            ?>
		</div>
	</div>
</div>
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>
<!-- news -->
