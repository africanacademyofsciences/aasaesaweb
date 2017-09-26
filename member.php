<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

	if (!$site->config['setup_members_area'] && $page->getMode()!="edit") redirect($site->link."/enewsletters");
	
	// MEMBER AREA
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/member.class.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/mailchimp.class.php');
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

	$member = new Member();
	
	// Tags
	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());

	// Header image
	$header_img = new HTMLPlaceholder();
	$header_img->load($siteID, 'header_img');
	if (!$header_img->draw()) {
		$header_img->load($siteData->primary_msv, 'header_img');
		if (!$header_img->draw()) {
			$header_img->load(1, 'header_img');
		}
	}
	$header_img->setMode("view");
	
	// footer text
	$footer = new HTMLPlaceholder();
	$footer->load($site->id, 'footer');
	$footer->setMode("view");	// You can only edit the footer on the homepage.
	
	$member_id = $_SESSION['member_id'];
	if ($member_id>0) $member->getById($member_id);		// Load member data to details object
	
	$action = read($_REQUEST,'action',NULL);
	$status = read($_REQUEST,'status','approved'); // Member status e.g. approved == ON FRONTEND 
	$search = read($_REQUEST,'q',NULL); // Search Query
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = read($_REQUEST,'show',15);

	// Page specific options
	
	$action = read($_REQUEST,'action',NULL);
	
	if ($_SERVER['REQUEST_METHOD']=="POST") {
		//print "posted action($action)<br>\n";
		$feedback="error";
		
		if ($action=="login") {
			$message = $member->logIn();
			$member_id = $_SESSION['member_id'];
			if ($member_id>0) { 
				$action="welcome";
				$member->getById($member_id);
			}
		}
		else if ($action == "password") {
			// check for data
			$current = $_POST['current'];
			$password1 = $_POST['password1'];
			$password2 = $_POST['password2'];
		
			if($current && $password1 && $password2){
				//print "entered($current) should be(".$result->password.")<Br>\n";
				if($current  == $member->details->password){ // correct password
					if($password1 == $password2){ // matching  new passwords
						if($done = $member->updatePassword($member_id, $password1)){ // successful dataabse call
							$feedback = 'success';
							$message = 'You have updated your password.';
						}
						else{ // tehnical error (they could have put back in the old password causing no changes)
							$feedback = 'error';
							$message = 'A technical error has occurred. Please try aagin in a few moments.';
						}
						
					}
					else{ // Password 1 & Password don't match
						$feedback = 'error';
						$message = 'Your new password and the confirmation are not identical.';
					}
				}
				else{ // user has entered wrong password
					$feedback = 'error';
					$message = 'You have entered an incorrect current password';
				}
			}
			else{ // user has missed out a field
				$feedback = 'error';
				$message[] = 'You have missed something out:';
				if(!$current){
					$message[] = 'your current password';
				}
				if(!$password1){
					$message[] = 'your new password';
				}
				if(!$password2){
					$message[] = 'your new password confirmation';
				}
			}
		}
		// Update subscription preferences
		else if (strtolower($action)=="update" && $member_id>0) {
			
			//print_r($_POST['preference']);
			$newsletter = new Newsletter();
			//$newsletter->setTesting("sub");
			if ($newsletter->updatePreferences($member_id)>=0) {
				$action = "subscriptions";
				$feedback="success";
				$message[]="You subscriptions have been updated";
			}
			else $message[]="Failed to update subscription preferences";
		}
		
				
	}
	else {
		if ($action == "logout") {
			$message = $member->logOut();
		}
	}
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page', 'members'); // all attached stylesheets
	//if($page->style) $css[] = $page->style;
	
	$extraCSS = ''; // extra page specific CSS
	
	$js = array(); // all atatched JS behaviours
	if($action){
		$js[] = 'usableForms';
		$js[] = 'showHideAddress';
	}
	$extraJS = ''; // etxra page specific JS behaviours
	
	// Page title
	
	$pageTitle = $pagetitle = $member->details->type_name;
	//print "<!-- pagetitle($pagetitle) d(".print_r($member, 1).") -->\n";
	if (!$pagetitle) $pageTitle = $pagetitle = 'Members';
	else if ($pagetitle=="Fellow") $pagetitle = "Fellows' tools";
	//print "pagetitle($pagetitle)<br>\n";
	
	//$pageTitle = 'Manage subscriptions';
	
	if ($action) $pageTitle.= ' - Welcome';
	else if ($member_id) $pageTitle .= ' &gt; '. ucfirst($member->details->firstname).' '.ucfirst($member->details->surname);
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');

?>

<div class="main-content">
    <div class="container">
		<div class="col-xs-12 col-sm-8" id="primarycontent">
        
			<?php 
            echo drawFeedback($feedback,$message);
    
			// Member is not logged in
			if(!$member->checkLogin()) { 
				
				// FORGOT PASSWORD
				if(!$member_id && $action == 'forgotten-password'){ 
					include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/emailPassword.php');
				}
				// Not logged in and not requesting allowable action and no error message
				else if (!$message) {
					//echo drawFeedback("error","Only logged in members can view this information");
					?>
					<!-- <p><?=$site->title?> members can log in to view member content, edit preferences, password and email subscriptions. Please enter your email address and password in the form on the right hand side of this page to access this area.</p> -->
					<p>Site members can log in to view member content (Blogs, Forums), edit preferences, password and email subscriptions. Please enter your email address and password in the form on the right hand side of this page to access this area.</p>
					<p>If you would like to be a member of this site please complete the Contact us form using the link at the top of the page and choose Become a Member in the Contact about selection.</p>
		
					<?php 
				}
				
			} 
			// Member is logged in.
			else if($member_id) { 
		
				// get details of member if we are logging in to THIS site
				$memberDetails = $member->getById($member_id); 	
				//print_r($memberDetails);
				
				// edit member (not sure we would want to do this ?
				if($action == 'edit'){ 
					include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/joinEditMembers.php');
				}
				// edit password
				else if($action == 'password'){ 
					include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/updatePassword.php');
				}
				// ***************************************************
				// Display and maybe let them edit all subscriptions??
				else if($action == 'subscriptions') { 
					// prefil the _POST array with current preferences.
					$query = "SELECT nup.preference_id 
						FROM newsletter_user_preferences nup
						LEFT JOIN newsletter_preferences np ON nup.preference_id=np.preference_id
						WHERE np.site_id=".$site->id." AND member_id=".$member_id;
					//print "$query<br>\n";
					if ($results = $db->get_results($query)) {
						foreach ($results as $result) {
							$_POST['preference'][]=$result->preference_id;
						}
					}
					include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/updateSubscriptions.php');
				}
				// ***************************************************
				// edit profile
				else if($action == 'profile'){ 
					// if this member does not have a profile ID already then create them one.
					if (!$memberDetails->profile_id) $memberDetails->profile_id = $member->getProfileID($memberDetails->access_id);
					// Show the profile edit/update form.
					include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/editProfile.php');
				}
				// ***************************************************
				// search members (no sites do this so it probably does not work
				else if ($action=="join" || $action=="renew") {
					include($_SERVER['DOCUMENT_ROOT'].'/store/pesapal/renew.inc.php');
				}
				// ***************************************************
				// search members (no sites do this so it probably does not work
				else if ($action ==  "search") { 
					include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/searchResults.php');
				}
				// ***************************************************
				// Nothing to do so just say hi for now.
				else if ($action == "welcome" || !$action) {
					?>
					<p>Welcome to the <?=$site->title?> member area</p>
					<?php
				}
					
			} 
			?>
		</div>

        <div id="secondarycontent" class="col-xs-12 col-sm-4 col-md-3 col-md-offset-1 sidebar">
        
            <?php
            // NOT LOGGED IN: Show log in form
            // *******************************
           if(!$member->checkLogin()) { 
                ?>
                <div class="panel panel-primary">
                    <div class="panel-heading">Login</div>
                    <div class="panel-body">
                    <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/memberLogin.php'); ?>
                    </div>
                </div>
                <?php
            }
            else { 
            
                // show log out link and logged in options	
                // **************************
                if($member_id>0){ 
					print "<!-- mem data(".print_r($member, 1).") -->\n";
                    ?>
                    <div class="panel panel-primary">
                        <div class="panel-heading">Member options</div>
                        <div class="panel-body">
                        <?php
						print "<!-- member type(".$member->details->type_name.") -->\n";
						if ($member->details->type_name=="Fellow") {
							
							// How long (in days) past the renewal date can someone go before they need 
							// to pay again?
							$renew_cutoff = 60;	// Days until you can no longer renew but have to rejoin
							$renew_advance = 30; // Days before membership expires when you can renew
							
							print "<!-- member overdue(".$member->details->renewal_overdue_days.") -->\n";
							//if ($member->details->renewal_overdue_days >= $renew_cutoff) {
							if ($member->details->paid_year==2000) {
								?>
		                        <p><a href="<?=$site->link?>member-login/?action=join" rel="nofollow">Pay for fellowship</a></p>
                                <?php
							}
							else if ($member->details->renewal_overdue_days >= -$renew_advance) {
								?>
		                        <p><a href="<?=$site->link?>member-login/?action=renew" rel="nofollow">Renew my fellowship</a> - due: <?=$member->details->renewal_due?></p>
                                <?php
							}
							else if ($member->details->renewal_overdue_days < -$renew_advance) {
								?>
		                        <p>Renewal due: <?=$member->details->renewal_due?></p>
                                <?php
							}
							else if ($member->details->paid_year<2000) {
								mail("phil@treelinesoftware.com", "Paid date problem", "Member ".print_r($member, 1)." has a problem with their paid_year");
								?>
                                <p>Paid date error[<?=($member->details->paid_year+0)?>]</p>
                                <?php
							}
							else {
								mail("phil@treelinesoftware.com", "Paid date problem", "Member ".print_r($member, 1)." does not appear to be due a join or renew");
								?>
                                <p>Unknown renewal status.</p>
                                <?php
							}
							?>
	                       	<p><a href="<?=$site->link?>fellows-pages/" rel="nofollow">Fellows pages</a></p>
                            <?php
						}
						else if ($member->details->type_name=="ISSAB Board") {
							?>
	                       	<p><a href="<?=$site->link?>issab-board/" rel="nofollow">Board member pages</a></p>
                            <?php
						}
						?>
                        <p><a href="<?=$site->link?>member-login/?action=password" rel="nofollow">Change password</a></p>
                        <p><a href="<?=$site->link?>member-login/?action=subscriptions" rel="nofollow">Manage email subscriptions</a></p>
                        <?php
                        //var_dump($member->details);
                        if ($site->getConfig("setup_blogs") && $member->details->bloggable) {
                            ?>
                            <p><a href="<?=$site->link?>blogs/" rel="nofollow">Blogging</a></p>
                            <?php
                        }
                        ?>
                        <p><a href="<?=$site->link?>member-login/?action=profile" rel="nofollow">Edit my profile</a></p>
                        <p><a href="<?=$site->link?>member-login/?action=logout" rel="nofollow">Log out of members area</a></p>
                        </div>
                    </div>
                    <?php				
                }
                // **************************
                ?>
        
            <?php } ?>
        
        </div>
	</div>
</div>    
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>