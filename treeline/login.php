<?php

	//ini_set("display_errors", 1);

	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");
	
	$skipSupport = true;

	if ($_SERVER['HTTP_HOST']=="aas.treelinesoftware.com") {
		header("location: http://aasciences.ac.ke/treeline\n\n"); exit();
	}
		
	// Set the debugging on
	$DEBUG = (read($_GET,'debug',false) !== false)?true:false;
	
	session_start();
	$_SESSION['treeline_language'] = $_COOKIE['tl_lang'];
	
	$page = new Page(); 	// Needed for translations.
	$labels=$page->getTranslations(1, $_SESSION['treeline_language'], 2);
	
	//include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/browserinfo.php");
	
	$message = array();	
	$feedback = read($_REQUEST,'feedback','notice');	
	$error = '';

	if (read($_GET,'logout',false) !== false) {
		$_SESSION = array();
		session_destroy();
	}
	else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$username = read($_POST,'username','');
		$password = read($_POST,'password','');		
		$username = $db->escape($username);
		$password = $db->escape($password);
		$query="SELECT u.id, u.logins, if(u.lock_time>now()-INTERVAL 1 HOUR,u.lock_guid,'') as lock_guid, 
				p.`level`, g.name as `group`,
				s.microsite, s.title as site_title, s.name as site_name, s.primary_msv as default_msv,
				sv.msv, sv.language as language,
				l.title as language_title, l.encoding as encoding
				FROM users u
				LEFT JOIN permissions p ON u.group=p.group
				LEFT JOIN groups g ON g.id=u.group 
				LEFT JOIN sites_versions sv ON g.domain=sv.msv
				LEFT JOIN sites s ON sv.microsite = s.microsite
				LEFT JOIN languages l ON sv.language = l.abbr
				WHERE u.name = '$username' AND u.password = '$password' AND u.blocked=0";
		//print "$query<br>";
		$data = $db->get_row($query);
		if ($db->num_rows > 0) {
			//print "got a row :o)<br>\n";
			$_SESSION['userid'] = $data->id; // depracated - needs a more explicit name as below - DD (23/08/07)
			$_SESSION['treeline_user_id'] = $data->id;
			$_SESSION['treeline_user_level'] = $data->level;
			$_SESSION['treeline_user_group'] = $data->group;
			$_SESSION['treeline_user_logins'] = $data->logins+1;
			$_SESSION['treeline_user_lock_guid'] = $data->lock_guid;
			$_SESSION['treeline_user_site_id'] = $data->msv;
			$_SESSION['treeline_user_microsite_id'] = $data->microsite;
			$_SESSION['treeline_user_site_title'] = $data->site_title;
			$_SESSION['treeline_user_site_name']= $data->site_name;
			$_SESSION['treeline_user_language']=$data->language;
			$_SESSION['treeline_user_encoding']=$data->encoding;
			$_SESSION['treeline_user_language_name']=$data->language_name;
			$_SESSION['treeline_user_language_title']=$data->language_title;
			$_SESSION['treeline_user_default_site_id']=$data->default_msv;
			$_SESSION['treeline_preview'] = $data->msv; // this enables us to access pages within the site for editing if it's not yet live...
			$_SESSION['show_tl_message']=true;

			// Update user logins
			$db->query("update users set logins=".$_SESSION['treeline_user_logins']." where id=".$data->id);
			addHistory($_SESSION['treeline_user_id'], "login");

			//print_r($_SESSION);
			redirect('/treeline/');
		}
		else $message[] = $page->drawLabel("tl_log_err_invalid", 'Your login details appear to be invalid');
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','login'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = $page->drawLabel("tl_log_title_sign", 'Sign in');
	$pageTitle = $page->drawGeneric("login", 1);
	
	$pageClass = 'login';
	
	$noUserInfo = true;
	/* don't show the menu (as we're not logged in) */
	$noMenu = true;
	
	$formhtml = '
          <form id="login" action="/treeline/login/" method="post">
            <fieldset>
			<div class="field">
            <label for="username">'.$page->drawLabel("tl_login_username", "User name").'</label>
            <input type="text" value="'.$_POST['username'].'" id="username" name="username" />
			</div>
			<div class="field">
            <label for="password">'.$page->drawGeneric("password", 1).'</label>
            <input type="password" value="" id="password" name="password" />
			</div>
			<div class="field">
			<label for="f_submit" style="visibility:hidden;">Submit</label>
            <input type="submit" class="submit" value="'.($_SERVER['SERVER_ADDR']=="78.129.246.186"?"Log in":"Login").'" />
			<span class="reminder"><a href="/treeline/forgot/">'.$page->drawLabel("tl_login_pass_remind", "Get a password reminder").'</a></span>
			</div>
            </fieldset>
          </form>
	';

	$loginboxheight=$broswer['supported']?160:220;
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
      <div id="primarycontent">
        <div id="primary_inner">
		<?php
       	echo drawFeedback($feedback,$message);
        //echo $formhtml;
		?>
        <!-- Log in box -->
        <?=treelineBox($formhtml, $page->drawLabel("tl_log_log_title", "Enter your username and password"), "blue", 450, $loginboxheight, 0, "newbox", "tl-box-left")?>
        <!-- Browser box -->
        <?=treelineBox($browserhtml, $browser['supported']?$page->drawLabel("tl_log_plat_title", "Your platform details"):$page->drawLabel("tl_log_browse_title", "Supported browsers"), "", 450, $loginboxheight, 0, "newbox", "dood hello")?>
        </div>
      </div>

<?php 
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>