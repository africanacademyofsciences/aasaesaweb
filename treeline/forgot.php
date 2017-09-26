<?php
	//ini_set("display_errors", 1);

	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/image.class.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/user.class.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/website.class.php");

	$skipSupport = true;

	// Set the debugging on
	$DEBUG = (read($_GET,'debug',false) !== false) ? true : false;

	session_start();
	$_SESSION['treeline_language'] = $_COOKIE['tl_lang'];
	
	$message = array();
	$feedback = read($_GET,'feedback','notice');

	$page = new Page(); 	// Needed for translations.
	$labels=$page->getTranslations(1, $_SESSION['treeline_language'], 2);
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/browserinfo.php");

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$website = new Website();
		$website->getSiteConfig();
		$email = read($_POST,'email','');
		$user = new User();		

		if($email){

			// check person has an account
			if ($results = $user->getDetailsFromEmail($email)) {
				foreach ($results as $details) { 
					//print_r($details);
					//$message = $user->sendPasswordReminder($email);
					$sendParams = array("FULLNAME"=>$details->full_name,
						"USERNAME"=>$details->name,
						"PASSWORD"=>$details->password,
						"SITENAME"=>$details->name
						);
					include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/site.class.php");
					include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");
					include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
					include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
					unset($site);
					//print "load site (".$details->msv).")<br>\n";
					$site = new Site($details->msv);
					$newsletter = new Newsletter();
					if ($newsletter->sendText($email, "PWD_REMIND", $sendParams)) {
						$feedback = "success";
						$message[]=$page->drawLabel("tl_forgot_send_msg", "You login details have been sent to your email address");
					}
				}
			} 
			else $message[] = $page->drawLabel("tl_forgot_not_registered", 'Your email address is not registered with Treeline');
		} 
		else $message[] = $page->drawLabel("tl_forgot_err_noemail", 'You must enter an email address');
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','login'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = $pageTitle = $page->drawPageTitle('forgot your password');
	
	$pageClass = 'login';
	
	$noUserInfo = true;
	$noMenu = true;

	$loginboxheight=220;
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
	<div id="primary_inner">
    <?php 
	echo drawFeedback($feedback,$message);
    echo treelineBox('
<form id="forgotPasswordForm" action="/treeline/forgot/" method="post">
	<fieldset>
		<div class="field">
			<label for="username">'.$page->drawGeneric("email_address", 1).'</label>
			<input type="text" value="" id="email" name="email" />
		</div>
		<div class="field">
			<label for="f_submit" style="visibility:hidden;">Submit</label>
			<input id="f_submit" type="submit" class="submit" value="'.$page->drawLabel("tl_forgot_send_pass", "Send password").'" />
			<span class="reminder"><a href="/treeline/login/">'.$page->drawGeneric("login", 1).'</a></span>
		</div>
	</fieldset>
</form>
', $page->drawLabel("tl_forgot_title", 'Get your password back'), 'blue', 450, $loginboxheight, 'login', "newbox", "tl-box-left");
	//echo treelineBox($formhtml, "Enter your username and password", "", 450, $loginboxheight, 'login', "newbox", "dood hello");
    echo treelineBox($browserhtml, $browser['supported']?$page->drawLabel("tl_log_plat_title", "Your platform details"):$page->drawLabel("tl_log_browse_title", "Supported browsers"), "", 450, $loginboxheight, 'login', "newbox", "");
	?>
	</div>
</div>

<?php 
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>