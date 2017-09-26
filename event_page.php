<?php

//ini_set("display_errors", 1);
//error_reporting(E_ALL);
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/event.class.php");

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
	include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include ($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');
	$revid = read ($_GET, 'revid', '');
	$action=$_SERVER['REQUEST_METHOD']=="POST"?$_POST['action']:$_GET['action'];
	
//session_destroy();
//	print "load page in ($mode) mode action($action)<br>";
	$event = new Event($page->getParent(), $page->getGUID());
	if (!$event->is_published()) {
		header("Location: /");
		exit();
	}	
	
	if ($action) {
	
		switch($action) {
		
			case 'add' :
				if (!$_SESSION['user_register_required']) $revid=1;
				else $message[]="You must complete the registration process before you can start blogging";
				break;
			case 'login' : 
				// Update this to check details and add real user id
				$_SESSION['user_register_required']=false;
				$cur_user=$event->login($_POST['username'], $_POST['password'], $page->getGUID());
				if ($cur_user==$event->pp['member_id']){ 
					$_SESSION['user_logged_in']=$cur_user;
					if (!$event->pp['registration_complete']) {
						$_SESSION['user_register_required']=true;
					}
				} else if ($cur_user>0) {
					// This is a log in by a valid dude (member of the site.....
					// We could set a flag to open up other options in here such as send message etc...
					$action="showlogin";
					$_SESSION['member_logged_in']=$cur_user;
					$message[]="You are a valid member, but only the owner of this page can log in currently.";
				} else {
					$action="showlogin";
					$message[]='Incorrect username/password entered. <a href="'.$page->drawLinkByGUID($page->getGUID()).'?action=forgot">Forgotten password</a>';
				}
				break; 
			case 'delete' :
				// Need to delete the specified revision id then shift up all lower revids by 1
				if (!$_SESSION['user_register_required']) {
					$query="delete from content where parent='".$page->getGUID()."' and revision_id=$revid and placeholder='content'";
					if ($db->query($query)) {
						$query="update content set revision_id=revision_id+1 where parent='".$page->getGUID()."' and placeholder='content' and revision_id<$revid";
						$db->query($query);
						$message[]="You blog entry has been removed";
					}
					else $message[]="Failed to remove this entry";
				}
				else $message[]="You must complete your registration before you can use the blogging system";
				break;
			case 'logout' : 
				$_SESSION['user_logged_in']=0;
				$_SESSION['user_register_required']=false;
				$mode="view";
				break;
			case 'retrieve' :
				$message[]="Your password has been emailed to your registered email address";
				$event->sendPassword($_POST['username']);
				$action="showlogin";
				break;
			case 'process_entry_form' :
				include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/event_entry_process.php");
				if (!$message) {
					$query="update event_entry set registered=1 where id=".$_POST['entry_id'];
					if ($db->query($query)) {
						$message[]="Register success";
						$message[]="You have successfully completed the registration process, instructions etc, to be supplied by ".$site->name." and will be entered in here.";
						$_SESSION['user_register_required']=false;
					}
					else {
						$message[]="Event form entry failed. Please try again later or inform ".$site->name." support team";
						mail("phil.redclift@ichameleon.com", $site->name." event register query failure", "Failed($query)");
					}
				}
				break;
				
		}
	}

	if ($_SESSION['user_logged_in'] && ($revid+0)==1 && $event->getPersonalPageGUID($page->getParent(), $_SESSION['user_logged_in'])==$page->getGUID()) {
		 $mode="edit";
		 $page->setMode($mode);
	}
	//print "user(".$_SESSION['user_logged_in'].") rev($revid) guid(".$page->getGUID().")<br>";
	//print "running ($action) in mode($mode)<br>";
	
	// Content
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content', 0, $revid);
	$content->setMode($mode);
	if ($action=="add" && $mode=="edit") { 
		$content->content='';
		$content->title='';
		$content->revision_id=1;
	}
	if ($content->age>0 && $content->revision_id==1) $message[]="Your new blog is currently awaiting approval by a ".$site->name." administrator";

	$event_image = new HTMLPlaceholder();
	$event_image->load($page->getParent(), 'pp_image');
	if (preg_match("/src=\"(.*?)\"/", $event_image->draw(), $reg)) {
		$event_image_src=$reg[1];
		//print "got image src=($event_image_src)<br>";
	}
	//print "loaded content(".$content->name.") in mode($mode) action($action)<br>";
	
	$event_message = new HTMLPlaceholder();
	$event_message->load($page->getParent(), 'pp_message');
	
	// Tags
	$tags = new Tags();
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
	
	// Panels
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');

	// if we're not using folder.php, we need to get the page created date...
	$pageDate = (!$pageDate || $pageDate==0) ? $page->date_created : $pageDate;
	
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		if (read($_POST,'treeline','') == 'Save blog') {
			
			$content->title = $_POST['title'];
			//print "set title to(".$content->title.")<br>";
			$content->save();
			//$page->save();
			$message[] = "New blog entry has been saved. This blog will not appear on your personal page until it has been approved by a ".$site->name." administrator.";
			// Send notification to admin
			$notifyEmail=$site->config['contact_recipient_email'];
			//$notifyEmail="phil.redclift@ichameleon.com";
			$newsletter = new Newsletter();
			$newsletter->sendText($notifyEmail, BLOG_UPDATED, array());
		}
	}
	
	

	// Page specific options
	
	$pageClass = 'event_page'; // used for CSS usually
	
	$css = array('page','forms','events'); // all attached stylesheets
	$extraCSS='';
	if ($event_image_src) $extraCSS .= '

	div.titlebar {
		background: #AD2A27 url(\''.$event_image_src.'\') no-repeat right;
	}
	
	
';
	
	$js = array(); // all atatched JS behaviours
	$extraJS = '

function hideMag(link, rev) {
	document.cookie="hide_mag_message=1";
	document.location=link+\'?revid=\'+rev;
}	

function showLogin() {
	document.getElementById("login_block").style.display="block";
}


function addBookmark(url,title) {
	if ((navigator.appName == "Microsoft Internet Explorer") && (parseInt(navigator.appVersion) >= 4)) {
		window.external.AddFavorite(url,title);
	} else if (navigator.appName == "Netscape") {
		window.sidebar.addPanel(title,url,"");
	} else {
		alert("Press CTRL-D (Netscape) or CTRL-T (Opera) to bookmark");
	}
}
function _addBookmark(url, name) { 
	if (window.external) window.external.AddFavorite(url, name) 
	else alert("Sorry! Your browser doesn\'t support this function.");  
} 

'; 
// etxra page specific  JS behaviours
	if ($mode=="edit" || 
		$_SESSION['user_register_required'] || 
		$action=="showlogin" || $action=="forgot" || $action=="delete" ||
		($_SESSION['user_logged_in'] && count($message)) 
		) $extraJSbottom.='showLogin();';
	
	$disablePageStyle=true;
	
	$orderBy = ($location[0]=='news') ? 'date_created DESC' : 'sort_order ASC';

	$oldmode=$mode; $mode="";	// Fudgy but we need to avoid showing the treeline editmode header.
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	$mode=$oldmode;

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
	
	$login_link='<a href="javascript:showLogin()">Log in to edit your own blog</a>';
	$logout_link='<a href="'.$page->drawLinkByGUID($page->getGUID()).'?action=logout">Log out</a>';
	
?>	
<form action="" method="post" id="treeline_edit">
<input type="hidden" name="mode" value="<?=$mode?>" />
<input type="hidden" name="referer" value="/treeline/?" />

    <div class="titlebar"><h1 class="pagetitle"><?=($event->own_event?$event->title:$page->getTitle())?> <?=$event->pp['title']?></h1></div>
    <div id="primarycontent">
    	<h1 class="blogbar">
			<?php 
				if ($_SESSION['user_register_required']) echo "Event registration";
				else {
					if (!$event->pp['grp_title']) {
						if (!$event->own_event) echo $event->pp['firstname']."'s blog : ";
					}
					echo $content->title;
				}
			?>
        </h1>
        <p class="blogbar"><span class="left"><?=$content->blog_date?></span><span class="right"><?=($_SESSION['user_logged_in']?$logout_link:$login_link)?></span></p>
        <div id="login_block">
            <?php if ($message) foreach($message as $msg) print '<p>'.$msg.'</p>'; ?>
       		<fieldset>
			<?php if ($action=="forgot") { ?>
                <input type="hidden" name="action" value="retrieve" />
                    <label for="form_username">Email</label>
                    <input type="text" id="form_username" name="username" />
                    <input type="submit" class="button" name="treeline" value="Get password" />
	        <?php } else if (!$_SESSION['user_logged_in']) { ?>
                <input type="hidden" name="action" value="login" />
                    <label for="form_username">Email</label>
                    <input type="text" id="form_username" name="username" />
                    <label for="form_password">Password</label>
                    <input type="password" id="form_password" name="password" />
                    <input type="submit" class="button" name="treeline" value="Log in" />
			<?php } else if ($_SESSION['user_register_required']) { ?>
            		<!-- <p>You have signed up for this event but you have not yet completed the registration process. Please complete the form below to allow us to process your entry.</p> -->
            <?php } else if ($mode=="edit") { ?>
                    <label for="form_title">Blog title</label>
                    <input type="text" id="form_title" name="title" style="width:400px;" value="<?=$content->title?>" />
					<input type="submit" class="button" name="treeline" value="Save blog" />
			<?php } ?>
            </fieldset>
        </div>
		<?php if ($event_message->draw() && $mode!="edit" && !$_COOKIE['hide_mag_message'] && !$_SESSION['user_logged_in']) { ?>
        	<div id="mag_message" style="clear:left;">
				<?=$event_message->draw()?>
                <p><a href="javascript:hideMag('<?=$page->drawLinkByGUID($page->getGUID())?>', <?=($revid+0)?>);">Hide this message in future</a></p>
            </div>
        <? } else { ?>
        	<?php //echo "Not showing message($mode) cookie(".$_COOKIE['hide_mag_message'].") logg s(".$_SESSION['user_logged_in'].")<br>"; ?>
		<? } ?>

		<div id="blog_text"><?php 
		if ($_SESSION['user_register_required'] && $_SESSION['user_logged_in']) {         
			include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/event_entry_form.php");
		} 
		else { 
			if ($content->draw()) {
				echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
				if (($prev=$event->getPreviousBlogRevID($page->getGUID(), $content->revision_id))<0) { 
					?><p><a class="arrow" href="<?=$page->drawLinkByGUID($page->getGUID()).'?revid='.$prev?>">Read <?=$event->pp['firstname']?> <?=$event->pp['surname']?>'s previous Blog</a></p><?php 
				}
			}
			else echo $event->drawDefaultPPContent();
			if($page->getMode() == 'wysiwyg') { echo '{content}'; }
		} 
        ?></div></div><div id="secondarycontent">
        <!--PANELS-->
        <?php if ($_SESSION['user_logged_in']) include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panel_prev_blogs.php"; ?>

        <?php include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panel_sponsor.php"; ?>

        <?php include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panel_event_more.php"; ?>

        <?php include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panel_countdown.php"; ?>

        <?php if (!$_SESSION['user_logged_in']) include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panel_prev_blogs.php"; ?>

        <?php include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panel_tools.php"; ?>

    </div>
</form>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>


 <?php /* TINY MCE */ 
 if($mode == 'edit'){	?>
	 <script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
	 <script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_personalpage.js"></script>
 <?php 
 }
 ?>