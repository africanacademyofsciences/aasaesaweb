<?php
	//ini_set("display_errors", 1);


	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	if (!preg_match("/\/$/",$_SERVER['PHP_SELF'])) {
		// This makes sure we're looking at /treeline/, and not /cms
		//header("Location: {$_SERVER['PHP_SELF']}/");
	}

	$section = read($_GET,'section','');
	if (isset($_GET['message'])) {
		$message[] = read($_GET,'message','');
		$feedback = read($_GET,'feedback','generic');
	}
	
	// If we have just logged in then add some stuff to the message array
	//print "message(".print_r($message, true).")<br>\n";
	if ($message[0]=="login") {
		$feedback="welcome";
		$message[0]=$page->drawLabel("tl_index_logged", 'You are logged in as ').'<strong>'.$user->getFullName().'</strong> with an access level of <dfn title="'.$user->drawGroup().' is 1 of 3 different access levels"><strong>'.$user->drawGroup().'</strong></dfn>';
		//$message[0]='You have logged in '.$_SESSION['treeline_user_logins'].' times';

		$tmp='This system allows you to edit the content of the ';
		if ($site->getConfig("setup_languages")) $tmp.='<a href="/treeline/languages/?action=switch"><strong>'.ucfirst(strtolower($_SESSION['treeline_user_language_title'])).' language</strong></a> ';
		$tmp.='website at <a href="'.$site->link.'" target="_blank"><strong>'.$site->link.'</a></strong>.';
		$message[]=$tmp;

		if ($_SESSION['treeline_user_logins']<5) $message[]='<strong>Need help?</strong> Try the <a href="javascript:openhelp(\''.$help->helpLinkByID(90).'\');">Quick Start Guide</a> or look for this symbol <img src="/treeline/img/icons/help_logo.gif" alt="help symbol" />';
	}
		

	if (read($_GET,'lo',0) == 1) {
		if($treeline->endSession()){
			$message[] = $page->drawLabel("tl_index_logout", 'You have successfully logged out');
			//redirect('/treeline/login/?'.createFeedbackURL('success','You have successfully logged out'));
			redirect('/treeline/login/');
		}else{
			$message[] = $page->drawLabel("tl_index_err_logout", 'The system could not log you out!');
		}
	}

	// Check if we need to info this user of an current edit.
	if (!$section && $db->get_var("SELECT lock_guid FROM users WHERE id=".$_SESSION['treeline_user_id'])) {
	
		if (isset($_GET['cancel_edit'])) {
			$page->releaseLock($_SESSION['treeline_user_id']);
		}
		else {
			if (!$feedback) $feedback="info";
			$lock_data=$db->get_row("SELECT p.guid, p.title FROM pages p LEFT JOIN users u ON p.guid=u.lock_guid WHERE u.id=".$_SESSION['treeline_user_id']." LIMIT 1");
			$message[]=$page->drawLabel("tl_index_openedit", "You have an open edit on the page").' : <strong>'.$lock_data->title."</strong>";
			$message[]='<a href="'.$page->drawLinkByGUID($lock_data->guid).'?mode=edit&amp;referer=/treeline/">'.$page->drawLabel("tl_generic_presshere", "Press here").'</a> '.$page->drawLabel("tl_index_reedit", "if you would like to go back to editting this page");
			$message[]='<a href="/treeline/?cancel_edit">'.$page->drawLabel("tl_generic_presshere", "Press here").'</a> '. $page->drawLabel("tl_index_finished", "if you have finished working on this page");
		}
	}	
	
	// set the site to live or suspended...
	if ( isset($_GET['mstatus']) ) {
		$mstatus = read($_GET,'mstatus',false);
		if (in_array($mstatus,array('0','1'))) {
			$site->saveStatus($mstatus);
		}
	}
	
	
	// We refer directly to the homepage on this page, so get its GUID:
	// As the CMS sites directly underneath site root, its parent is the ID of the homepage
	//$homepage = new Page();
	//$homepage->loadByGUID($page->getParent());
	
	/// recent items
	$days = read($_POST,'days',14);
	$itemType = read($_POST,'type','pages');
	$itemAction = read($_POST,'action','edited');
	$sortBy = read($_GET,'order','newest');
	
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	if(!$section){
		$css[] = 'home';
	}
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = '
	

function loadpage(p) {
	if (p=="create-content") location = "/treeline/?section=create-content";
	else if (p=="manage-content") location = "/treeline/?section=edit-content";
	else if (p=="manage-content") location = "/treeline/?section=edit-content";
	else if (p=="manage-assets") location = "/treeline/?section=edit-assets";
	else if (p=="site-structure") location = "/treeline/?section=edit-structure";
	else if (p=="newsletters") location="/treeline/newsletters/";
	else if (p=="microsites") location="/treeline/?section=sites";
	else if (p=="languages") location="/treeline/?section=language";
	else if (p=="events") location="/treeline/events/";
	else if (p=="access") location="/treeline/?section=access";
	else if (p=="stats") location="/treeline/stats/";
	else alert(p);
}	

'; // extra on page JavaScript
	
	// Page title
	
	$pageTitleH2 = $pageTitle = ($section) ? $page->drawLabel("tl_title_".$section, ucwords(str_replace("-", " ", $section))) : $page->drawLabel("tl_index_welcome", 'Welcome to Treeline');
	$pageClass = ($section) ? $section : 'home';

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
		
?>
<div id="primarycontent">
	<div id="primary_inner">
		
		<?php 
		
        echo drawFeedback($feedback,$message);
		
		// SECTION: Home aka No Section/Default
		if (!$section) { 
	        ?>

         	<!--<div id="treelineNews">
                <h3>Treeline news</h3>
                <?php //echo drawRSSFeed('http://demo.treelinecms.com/feed/', true); ?>
            </div>-->
            
            
            <?
			// If this is not the CMS for the main site 
			if ($site->id > 1) { 
				if( $site->comment ){
					echo '<p id="site_comment"><strong>'.$page->drawTitle("tl_index_comment", "Comment").':</strong><br />'. $site->comment .'</p>';
				}
			}
            
			// Show the default button options
			?>
			<form id="f_options" method="post">
            <fieldset class="main-tasks">
            	<input onclick="javascript:loadpage('create-content');" type="button" name="action" class="big_button" id="bbtn_create" value="<?=$page->drawLabel("tl_generic_create", "Create")?> <?=$page->drawLabel("tl_index_newcontent", "new content")?>" />
            	<input onclick="javascript:loadpage('manage-content');" type="button" name="action" class="big_button" id="bbtn_edit" value="<?=$page->drawLabel("tl_index_manageex", "Manage existing")?> <?=$page->drawLabel("tl_generic_content", "content")?>" />
            	<input onclick="javascript:loadpage('manage-assets');" type="button" name="action" class="big_button" id="bbtn_assets" value="<?=$page->drawLabel("tl_but_resource_1", "Manage asset")?> <?=$page->drawLabel("tl_but_resource_2", "libraries")?>" />
                <?php if ($_SESSION['treeline_user_group']!="Author") { ?>
            	<input onclick="javascript:loadpage('site-structure');" type="button" name="action" class="big_button" id="bbtn_structure" value="<?=$page->drawLabel("tl_generic_manage", "Manage")?> <?=$page->drawLabel("tl_index_sitestruc", "site structure")?>" />
                <?php } ?>
            </fieldset>
            
            <h3><?=$page->drawLabel("tl_index_common_tasks", "Other common tasks")?></h3>
            <fieldset class="common-tasks">
            	<?php 
				// Manage newsletters
				if ($submenu_count<6 && $_SESSION['treeline_user_group']!="Author" && $site->getConfig("setup_newsletters")) {
					$submenu_count++;
					?><input onclick="javascript:loadpage('newsletters');" type="button" name="action" class="small_button" id="sbtn_newsletter" value="<?=$page->drawLabel("tl_index_s_newsletter", "Email newsletters")?>" /><?php 
				}
				// Manage microsites
            	if ($submenu_count<6 && $_SESSION['treeline_user_group']=="Superuser" && $site->getConfig("setup_microsites") && $site->id==1) {
					$submenu_count++;
					?><input onclick="javascript:loadpage('microsites');" type="button" name="action" class="small_button" id="sbtn_microsite" value="<?=$page->drawLabel("tl_index_s_microsite", "Manage microsites")?>" /><?php
				}
				// Languages setup and site lables
            	if ($submenu_count<6 && $_SESSION['treeline_user_group']!="Author" && $site->getConfig("setup_languages")) {
					$submenu_count++;
					?><input onclick="javascript:loadpage('languages');" type="button" name="action" class="small_button" id="sbtn_language" value="<?=$page->drawLabel("tl_index_s_languages", "Manage languages")?>" /><?php
            	}
				// Events manager
				if ($submenu_count<6 && $_SESSION['treeline_user_group']!="Author" && $site->getConfig("setup_events") && $site->id==1) {
					$submenu_count++;
					?><input onclick="javascript:loadpage('events');" type="button" name="action" class="small_button" id="sbtn_event" value="<?=$page->drawLabel("tl_index_s_events", "Manage events")?>" /><?php
            	}
				// Access options and site settings
				if ($submenu_count<6 && $_SESSION['treeline_user_group']=="Superuser") {
					$submenu_count++;
					?><input onclick="javascript:loadpage('access');" type="button" name="action" class="small_button" id="sbtn_access" value="<?=$page->drawLabel("tl_index_s_access", "Access rights")?>" /><?php
            	}
				// Statistics menu
				if ($submenu_count<6 && $_SESSION['treeline_user_group']!="Author") {
					$submenu_count++;
					?><input onclick="javascript:loadpage('stats');" type="button" name="action" class="small_button" id="sbtn_stats" value="<?=$page->drawLabel("tl_index_s_stats", "Statistics")?>" /><?php
				}
				?>
            </fieldset>
            </form>
            
            <?php
			echo $treeline->drawAllRecentItems($site->id, 28);
			
        }   
   		// SECTION: Create
		else if ($section == 'create-content') { 
			// Standard create options
			$createOpts = '
<li><a href="pages/?action=create">'.$page->drawLabel("tl_index_cre_page", "Create a new web page").'</a></li>
<li><a href="panels/?action=select&amp;mode=create">'.$page->drawLabel("tl_index_cre_panel", "Create a new panel").'</a></li>
';
			if ($site->getConfig("setup_galleries") && $_SESSION['treeline_user_group']!="Author") $createOpts.='<li><a href="galleries/?action=create">'.$page->drawLabel("tl_index_cre_gallery", "Create a new slideshow").'</a></li>'."\n";

			// Set up other create options
			$otherOpts='
<li><a href="/treeline/images/?action=create">'.$page->drawLabel("tl_index_cre_image", "Upload a new image to the image library").'</a></li>
<li><a href="/treeline/files/?action=create">'.$page->drawLabel("tl_index_cre_docum", "Upload a new document to the file library").'</a></li>
<li><a href="/treeline/media/?action=create">'.$page->drawLabel("tl_index_cre_media", "Add media code to the multimedia library").'</a></li>
<li><a href="/treeline/fancy/?action=create">Add a fancy code block to the library</a></li>
';
			if ($_SESSION['treeline_user_group']!="Author") $otherOpts.='<li><a href="tags/?action=create">'.$page->drawLabel("tl_index_cre_tags", "Add tags to the tag library").'</a></li>';
			if ($_SESSION['treeline_user_group']!="Author") $otherOpts.='<li><a href="sections/?action=create">'.$page->drawLabel("tl_index_cre_section", "Create a new section of the website").'</a></li>';
			
			?>
			<h2 class="pagetitle rounded"><?=$page->drawLabel("tl_index_cre_step1", "Step 1: Choose the type of content you want to create")?></h2>
            <?php
			echo treelineList($createOpts, $page->drawLabel("tl_index_cre_title", 'What type of content do you want to create'), 'blue', '', 0, 0, 110);
            echo treelineList($otherOpts, $page->drawLabel("tl_index_cre_similar", 'Similar tasks'), '', '', 0, 0, 111);
		}

		// ********************************************************
		// SECTION: Edit Content
		else if ($section == 'edit-content') { 

			$deleteOpts='';
			
			// Allow publishing/deleting pages if we have sufficient access rights.
			if ($_SESSION['treeline_user_group']!='Author') { 
				if (!$treeline->isContentPublishable($siteID)) $publabel = $page->drawLabel("tl_pedit_pub_nohome", 'The homepage is not currently publishable');
				else $publabel = '<a href="pages/?action=publish&amp;guid='.$site->id.'">'.$page->drawLabel("tl_pedit_pub_home", "Publish the homepage").'</a>';
				$publishOpts='<li>'.$publabel.'</li>';
  				if ($site->getConfig("setup_comments")) $publishOpts.='<li><a href="comments/">'.$page->drawLabel("tl_pedit_comments", "Review page comments").'</a></li>'."\n";
			}
			//print "<!-- opts for site(".$site->link.") -->\n";
			$editOpts='
<li><a href="'.$site->link.'?mode=edit&amp;referer='.urlencode('/treeline/?section=edit').'">'.$page->drawLabel("tl_pedit_edit_home", "Edit the homepage").'</a></li>
<li><a href="pages/?action=edit">'.$page->drawLabel("tl_pedit_manage_cont", "Manage a content page").'</a></li>
<li><a href="panels/?action=edit">'.$page->drawLabel("tl_pedit_manage_panel", "Manage panels").'</a></li>
<li><a href="pages/?action=edittabs">'.$page->drawLabel("tl_pedit_manage_edittabs", "Manage homepage slideshow").'</a></li>
';
			if ($site->getConfig("setup_galleries") && $_SESSION['treeline_user_group']!="Author") $editOpts.='<li><a href="galleries/">'.$page->drawLabel("tl_pedit_manage_gall", "Manage slideshows").'</a></li>'."\n";
			
			?>
			<h2 class="pagetitle rounded"><?=$page->drawLabel("tl_generic_step", "Step")?> 1: <?=$page->drawLabel("tl_pedit_sec_title", "Select the content type you would like to manage")?></h2>
            <?php
			echo treelineList($editOpts, $page->drawLabel("tl_pedit_title1", 'Select content type'), 'blue');
			if ($_SESSION['treeline_user_group']!="Author") {
				echo treelineList($publishOpts, $page->drawLabel("tl_pedit_title2", 'Select a type of content to publish'), 'blue');
				if ($deleteOpts) echo treelineList($deleteOpts, $page->drawLabel("tl_pedit_title3", 'Select a type of content to delete'));
			}
		}
		
		// ********************************************************
		// SECTION: Edit asset libraries
		else if ($section == 'edit-assets') { 
			//$assetOpts='<li><a href="tags/?action=all">'.$page->drawLabel("tl_index_ass_showtags", "Show all tags in the tag library").'</a></li>';
			if ($_SESSION['treeline_user_group']!="Author") $assetOpts='
				<li><a href="images/?action=edit">'.$page->drawLabel("tl_index_ass_manimages", "Manage the image library").'</a></li>
				<li><a href="files/?action=edit">'.$page->drawLabel("tl_index_ass_manfiles", "Manage the file library").'</a></li>
				<li><a href="media/?action=edit">'.$page->drawLabel("tl_index_ass_manmedia", "Manage the multimedia library").'</a></li>
				<li><a href="mosaic.php?action=edit">Manage the mosaic library</a></li>
				<li><a href="fancy/?action=edit">Manage the fancy code block library</a></li>
				<li><a href="tags/?action=all">'.$page->drawLabel("tl_index_ass_mantags", "Manage the tag library").'</a></li>
			';
			?>
			<h2 class="pagetitle rounded"><?=$page->drawLabel("tl_generic_step", "Step")?> 1: <?=$page->drawLabel("tl_index_ass_header", "Select the asset library you wish to manage")?></h2>
            <?php
			echo treelineList($assetOpts, $page->drawLabel("tl_index_ass_title", 'Edit a library asset'), 'blue');
			
			//if ($_SESSION['treeline_user_group']!="Author") echo treelineList('<li><a href="tags/?action=delete">'.$page->drawLabel("tl_index_ass_deltag", "Delete a tag from the tag library").'</a></li>', $page->drawLabel("tl_index_ass_title2", 'Other related tasks'));
		}
				
		// ********************************************************
		// SECTION: Edit site structure
		else if ($section == 'edit-structure') { 
			$editOpts='<li><a href="menus/">'.$page->drawLabel("tl_index_struc_manmenu", "Manage the menu").'</a></li>';
			if  ($_SESSION['treeline_user_group']!='Author') $editOpts.='<li><a href="sections/?action=edit">'.$page->drawLabel("tl_index_struc_edisect", "Edit sections").'</a></li>';
			?>
			<h2 class="pagetitle rounded"><?=$page->drawLabel("tl_generic_step", "Step")?> 1: <?=$page->drawLabel("tl_index_struc_header", "Select the structure element you wish to manage")?></h2>
            <?php
			echo treelineList($editOpts, $page->drawLabel("tl_index_struc_title", 'Edit site structure'), 'blue');
			if ($_SESSION['treeline_user_group']!="Author") echo treelineList('<li><a href="sections/?action=delete">'.$page->drawLabel("tl_index_struc_delsect", "Delete a section").'</a></li>', $page->drawLabel("tl_index_struc_title2", 'Delete a section'));
		}
		 
		
		// ********************************************************
		// SECTION: Events
		else if ($section =='events'  && $_SESSION['treeline_user_group']!='Author'){ 
			?>
			<p>This section allows super-users to manage events and related data.</p>
			<p>Select an option from below:</p>

			<div id="submenu">
				<ul>
					<li><a href="events/?action=create">To create or edit an event</a></li>
					<li><a href="events/?action=delete">Delete an event</a></li>
					<li><a href="blogs/">Review and moderate personal pages</a></li>
				</ul>		
			</div>	
	        <?php 
		}
		
		// SETCION : delete WITHOUT PERMISSIONS????
		else if ($section =='delete'  && $user->drawGroup()=='Author'){ 
			?>
            <div class="feedback error">
                <h3>Warning</h3>
                <p>You don't have the succificient priviledges to delete items from this website</p>
            </div>	
			<?php 
		}
			
		
		// -------------------------------------------------------------------------	
		// SECTION: Sites - for microsite control from the main site only 
		else if ($section =='sites' && $user->drawGroup()=='Superuser' && $site->id==1) {
			$page_html = '
            <p><a href="/treeline/sites/?action=create">Create a new site</a></p>
			<p class="instructions">If you wish to add content or make changes to a particular site you must log into Treeline as an administrator of that site.</p>
			';
			echo treelineBox($page_html, "Manage microsites", "blue");
			
			$page_html = '
	  		<form action="/treeline/sites/?action=delete" method="GET">
	        <input type="hidden" name="action" value="delete" />
			<fieldset>
					<p class="instructions">If you want to remove an existing site please select it below. This action will NOT be reversible please ensure you are sure you wish to continue.</p>	
					<label for="guid">Current sites:</label>
					'.$site->drawSelectMicrositeList('guid', array(1)).'<br />
					<fieldset class="buttons">
						<input type="submit" class="cancel" value="Delete" />
					</fieldset>
				</fieldset>
			</form>
			';
			echo treelineBox($page_html, "Delete a microsite");
			
        
		}
		// -------------------------------------------------------------------------	
		

		// -------------------------------------------------------------------------	
		// SECTION: Languages
		else if( $section=='language' && $user->drawGroup()!='Author'){
		
			//$page_html = '<p>'.$page->drawLabel("tl_lang_index_msg", "This section allows different language versions of this microsite to be managed and for translations to be made for labels used around sites in non-English languages.").'</p>';
			$page_html .= '
				<p>'.$page->drawLabel("tl_lang_index_msg2", "Select an option from below").'</p>
				<div id="submenu">
				';
			$versions=$page->getCurrentLanguageVersions();

			if (count($versions)>1) $page_html.='<ul><li><a href="languages/?action=switch">'.$page->drawLabel("tl_lang_opt_switch", "Switch to editing a different language version").'</a></li></ul>';

			$page_html.='<ul>';
			//$page_html.='<li><a href="languages/?action=create">'.$page->drawLabel("tl_lang_opt_add", "Add another language version to this site").'</a></li>';
			
			if (count($versions)>1) $page_html.='<li><a href="languages/?action=remove">'.$page->drawLabel("tl_lang_opt_remove", "Remove a language version from this site").'</a></li>';

			$page_html.='<li><a href="languages/?action=translations">'.$page->drawLabel("tl_generic_amend", "Amend").' '.strtolower($_SESSION['treeline_user_language_title']).' '.$page->drawLabel("tl_lang_opt_labels", "label translations").'</a></li>';
			$page_html.='</ul>';
	
			//if ($site->id == 1) $page_html.='<ul><li><a href="languages/?action=systranslations">'.$page->drawLabel("tl_lang_opt_master", "Amend master translations").'</a></li></ul>';
			
			$page_html.='</div>';
			echo treelineBox($page_html, $page->drawLabel("tl_index_languages", "Manage languages"), "blue");
			
			if ($site->config['tl_languages']) {
				$page_html = '<p>'.$page->drawLabel("tl_setting_lang_msg", "The following language options relate only to the language used to control Treeline and have no effect on the actual website itself.").'</p>';	
				$page_html .= '
				<div id="submenu">
					<ul>
					';
				$list = $page->getLanguageList();
				//print "Currently in (".$_SESSION['treeline_language'].")<br>\n";
				foreach ($list as $lang) {					
					if ($lang->abbr != $_SESSION['treeline_language']) {
						$page_html .= '<li><a href="/treeline/?section=language&action=lang&lang='.$lang->abbr.'">'.$page->drawLabel("tl_setting_view", "View Treeline in").' '.($_SESSION['treeline_language']=="en"?$lang->title:$lang->title_local).'</a></li>'."\n";
					}
				}
				$page_html .= '
					</ul>
					<ul>
					<li><a href="/treeline/languages/?action=translations&admin=true">'.$page->drawLabel('tl_generic_amend', 'Amend').' '.strtolower($_SESSION['treeline_language_title']).' '.$page->drawLabel('tl_lang_version', 'language version').'</a></li>
					</ul>
				</div>
				';
				//echo treelineBox($page_html, $page->drawLabel("tl_index_lang_treeline", "Treeline language"));
			}
		} 
		// -------------------------------------------------------------------------	


		// ********************************************************
		// SECTION: Store main menu
		else if ($section =='store' && $site->getConfig('setup_store')) {
			if ($site->id==1 || $site->getConfig("site_store")) { 
				if ($_SESSION['treeline_user_group']!='Author'){ 
	
					$orderOpts = '
<li><a href="/treeline/store/'.$storeVersion.'/orders.php">'.$page->drawLabel("tl_index_str_order", "View orders").'</a></li>
<li><a href="/treeline/store/'.$storeVersion.'/orders.php?action=download">'.$page->drawLabel("tl_index_str_dlorder", "Download orders as a CSV list").'</a></li>
					';
					$productOpts='
<li><a href="/treeline/store/'.$storeVersion.'/inventory.php?action=add&product=new">'.$page->drawLabel("tl_index_cre_prod", "Add a new product").'</a></li>
<li><a href="/treeline/store/'.$storeVersion.'/inventory.php?action=edit">'.$page->drawLabel("tl_index_ed_prod", "Edit products and stock levels").'</a></li>
					';
					if ($donationsAndCampaigns) {
					$productOpts.='
<li><a href="/treeline/store/'.$storeVersion.'/donation.php">'.$page->drawLabel("tl_index_str_dons", "Edit donations").'</a></li>
<li><a href="/treeline/store/'.$storeVersion.'/campaign.php">Manage campaigns</a></li>
					';
					}
					$categoryOpts = '
<li><a href="/treeline/store/'.$storeVersion.'/config.php?action=categories&mode=add">'.$page->drawLabel("tl_index_str_catc", "Add a new category").'</a></li>
<li><a href="/treeline/store/'.$storeVersion.'/config.php?action=categories&mode=edit">'.$page->drawLabel("tl_index_str_cate", "Manage categories").'</a></li>
<li><a href="/treeline/store/'.$storeVersion.'/config.php?action=variants&mode=add">'.$page->drawLabel("tl_index_str_varc", "Configure product variations").'</a></li>
					';
					
					$configOpts = '';
					if ($site->id==1) $configOpts = '
<li><a href="/treeline/store/'.$storeVersion.'/config.php?action=global">'.$page->drawLabel("tl_index_str_conlev", "Edit stock level alerts").'</a></li>
';
					$configOpts .= '
<li><a href="/treeline/store/'.$storeVersion.'/config.php?action=delivery&mode=post">'.$page->drawLabel("tl_index_str_conpost", "Edit postage costs").'</a></li>
<li><a href="/treeline/store/'.$storeVersion.'/config.php?action=delivery&mode=pack">'.$page->drawLabel("tl_index_str_conpack", "Edit packaging costs").'</a></li>
					';

					echo treelineList($orderOpts, $page->drawLabel("tl_index_str_ord_title", 'View orders'), 'blue', '', 0, 0, 1);
					echo treelineList($productOpts, $page->drawLabel("tl_index_str_prd_title", 'Manage products'), 'blue', '', 0, 0, 1);
					//echo treelineList($categoryOpts, $page->drawLabel("tl_index_str_cat_title", 'Manage categories'), 'blue', '', 0, 0, 1);
					echo treelineList($configOpts, $page->drawLabel("tl_index_str_con_title", 'Configuration'), 'blue', '', 0, 0, 1);
				}
			}
		}
		

		
		// -------------------------------------------------------------------------	
		// SECTION: Access
		// SETCION : Access WITHOUT PERMISSIONS
		else if ($section =='access'  && $user->drawGroup()!='Superuser'){ 
			?>
			<div class="feedback error">
				<h3>Warning</h3>
				<p>Only superusers can edit this website's users' access rights</p>
			</div>	
			<?php 
		}
		else if ($section =='access'){ 
			?>
			<h2 class="pagetitle rounded"><?=$page->drawLabel("tl_index_acc_title", "Set up access rights for administrators of Treeline")?></h2>
			<?php
			$tmp = '';
			$tmpTitle = $page->drawLabel("tl_index_acc_myacc", "Manage my account");

			// Only primary site superusers can add/edit users for this site.
			if ($user->drawGroup()=='Superuser' && $site->id == $_SESSION['treeline_user_default_site_id']) {
				$tmp = '<li><a href="access/?action=create">'.$page->drawLabel("tl_index_acc_create", "Create a new administrator").'</a></li>';
				$tmp.= '<li><a href="access/?action=edit">'.$page->drawLabel("tl_index_acc_edit", "Manage administrators").'</a></li>';
				$tmpTitle = $page->drawLabel("tl_index_acc_allacc", "Manage Treeline users");
			}
			$tmp.= '<li><a href="access/?action=notify">'.$page->drawLabel("tl_index_acc_notify", "Manage my notifications").'</a></li>';
			echo treelineList($tmp, $tmpTitle, "blue");
			if ($user->drawGroup()=='Superuser' && $site->id==1) {
				echo treelineList('<li><a href="access/?action=download">'.$page->drawLabel("tl_index_acc_download", "Download all administrator data").'</a></li>', $page->drawGeneric('similar tasks', 1));
			}
			// --------------------------------------
			// Unlock locked pages
			if ($_GET['guid']) {
				$query = "UPDATE users SET lock_guid = '' WHERE lock_guid = '".$_GET['guid']."'";
				//print "$query<br>\n";
				$db->query($query);
			}
			$query = "SELECT p.guid, p.title,
				u.full_name
				FROM users u INNER JOIN pages p
				ON p.guid = u.lock_guid
				WHERE p.msv = ".$_SESSION['treeline_user_site_id'];
			//print "Sess(".print_r($_SESSION, 1).")<br>\n";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
					$pageHTML .= '
						<tr>
							<td>'.$result->title.'</td>
							<td>'.$result->full_name.'</td>
							<td><a href="/treeline/?section=access&guid='.$result->guid.'">unlock</a></td>
						</tr>
					';
				}
			}
			if ($pageHTML) {
				$pageHTML = '
					<table class="tl_list">
						<tr>
							<th>Page title</th>
							<th>Author</th>
							<th>Action</th>
						</tr>
						'.$pageHTML.'
					</table>';
				echo treelineBox('<p>The following pages are currently locked for edit.</p>'.$pageHTML, "Unlock pages");
			}
			// --------------------------------------
			
		}
		// -------------------------------------------------------------------------	

		
		// ---------------------------------------------------------------------------
		// SECTION: Settings
		else if ($section =='settings'  && $user->drawGroup()=='Superuser'){ 
			
			// If we are admin of a microsite allow switch on/off option
			if ($site->id>1) {
				if ($site->properties['status'] == 0) $status_html.='This microsite is currently <strong>suspended</strong>. To make this microsite live, please click <a href="/treeline/?section=settings&amp;mstatus=1">here</a>.';
				else if ($site->properties['status'] == 1) $status_html ='This microsite is currently <strong>live</strong>. To suspend this microsite, please click <a href="/treeline/?section=settings&amp;mstatus=0">here</a>.';						
			}

			$page_html = ($status_html?'<p id="sitestatus">'.$status_html.'</p>':"").'
			<p>'.$page->drawLabel("tl_index_sett_title", "Select an option from below").'</p>

			<div id="submenu">
				<ul>
					<li><a href="settings/?action=edit">'.$page->drawLabel("tl_setting_conf_opt", "Edit configuration options").'</a></li>
                    ';
			if ($user->id==1 && $site->id==1) {
				$page_html .= '<li><a href="settings/?action=images">'.$page->drawLabel("tl_index_sett_images", "Edit library image sizes").'</a></li>'."\n";
			}
			$page_html .='
                    '.($site->id==1?'<li><a href="settings/?action=legacy">Edit legacy URLs</a></li>':"").'
				</ul>		
			</div>
			';
			
			/*
			$page_html.='<p>'.$page->drawLabel("tl_setting_lang_msg", "The following language options relate only to the language used to control Treeline and have no effect on the actual website itself.").'</p>';	
			if ($site->config['tl_languages']) {
				$page_html .= '
				<div id="submenu">
					<ul>
					';
				$list = $page->getLanguageList();
				//print "Currently in (".$_SESSION['treeline_language'].")<br>\n";
				foreach ($list as $lang) {					
					if ($lang->abbr != $_SESSION['treeline_language']) {
						$page_html .= '<li><a href="/treeline/?section=settings&action=lang&lang='.$lang->abbr.'">'.$page->drawLabel("tl_setting_view", "View Treeline in").' '.($_SESSION['treeline_language']=="en"?$lang->title:$lang->title_local).'</</li>';
					}
				}
				$page_html .= '
					</ul>
				</div>
				';
			}
			*/
			echo treelineBox($page_html, $page->drawLabel("tl_index_conf_title", "Manage configuration parameters"), "blue");
		}
		// SETCION : Settings WITHOUT PERMISSIONS
		else if ($section =='settings'  && $user->drawGroup()!='Superuser'){ 
			echo drawFeedback('error', $page->drawLabel('tl_err_no_auth', "Only superusers can edit this website\'s configuration settings"));
		} 
		// ---------------------------------------------------------------------------

		
		// SECTION: Restore ########### NOT YET ACTIVE #################
		else if ($section == 'restore' && $user->drawGroup()!='Author') { 
		?>
			<p>To restore content, please select an option from below.</p>
			<div id="submenu">
				<ul>
					<li><a href="restore/?view=content">Restore a single page</a></li>
					<li><a href="restore/?view=panels">Restore a single panel</a></li>
					<li><a href="#">Restore whole site</a></li>
				</ul>		
			</div>
			
		<?php 
		}
		
		?>
        
</div>
</div>
	  

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>