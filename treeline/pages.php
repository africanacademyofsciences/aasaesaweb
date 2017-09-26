<?php
	ini_set("display_errors", "yes");
	//error_reporting(E_ALL);

	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/event.class.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/petition.class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/resources.class.php");
	//include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions/pages.php");

	$tags = new Tags($site->id, 1);// TAGS
	
	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/"); // hide this page if no action is present
	
	$guid = read($_REQUEST,'guid','');
	$view = read($_REQUEST,'view',false);
	$mode = read($_REQUEST,'mode',false);

	$event = new Event($guid);
	$petition = new Petition($guid);

	// Create resource obejct
	$resource = new Resource($guid);

	// user feedback
	$feedback = read($_REQUEST,'feedback',"error");
	$message = array();
	if ($_REQUEST['message']) $message[]=$_REQUEST['message'];
	
	$title = read($_POST,'title',''); // Page title
	$parent = read($_POST,'parent','xx'); // Page parent
	$meta_desc = read($_POST,'description',false); // Meta Description
	$template = read($_POST,'template',2); // Page template e.g. page.php
	$style = read($_POST,'style',2); // Page style e.g. 2 (2 col layout for a page)
	$shorturl = read($_POST,'shorturl',false); // page shortcut e.g. example.com/link

	$hidden = read($_POST,'hidden',false); // Hidden (from menu) status
	$hiddenflag = ($hidden=='on')?'1':'0';
	$robots = read($_POST,'robots',false); // Hidden (from menu) status
	$robotsflag = ($robots=='on')?'1':'0';
	$offline = read($_POST,'offline',false); // Offline (from menu) status
	$offlineflag = ($offline=='on')?'1':'0';
	$private = read($_POST,'private',0); // Get member types allowed to view this page
	$comment = read($_POST,'comment',false); // Allow comments (from menu) status
	$commentflag = ($comment=='on')?'1':'0';

	$page_type = read($_POST,'page_type',false); // Describe the kind of page it is...
	$page_type = ($page_type<='') ? 0 : $page_type;
	$news_display = ($_POST['news_display']) ? 1 : 0;
	
	//$page = new Page;
	
	$keywords = read($_REQUEST,'keywords',false);
	$category = read($_REQUEST,'category','xx');
	$findcat = read($_REQUEST,'findcat',false);
	$thispage = read($_REQUEST,'page',1);
	$page->setPage($thispage);
	
	//print "<!-- got page type($page_type) --> \n";
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {// Form has been submitted
	
		// Not very keen on this but keep the site name for emptying cache files later.
		// Need to check this works really.
		$tmp = ($site->properties['site_name'] >'' ? '_'.$site->properties['site_name'] : '');
	
		// Need to check for tag adding and ensure no other actions are processed
		// We actually add tags in the /treeline/includes/ajax/forms/addEditTags.php file
		if ($_POST['tagaction']) {
			;
		}
		else if ($action == 'create') { // Create new page

			if ($parent == "xx") $parent = $site->id;

			//print "got title($title) section($parent)<br>\n";
			if (!$title) {
				$message[] = $page->drawLabel("tl_pedit_err_title", 'Please enter a title for this page');
			}
			else if (!$template) {
				$message[] = $page->drawLabel("tl_pedit_err_type", 'Please select a page type for this page');
			}
			else {
			
				if ($template==67) $style=17; // If creating a landing page default to 2 col layout
				
				// Create a new page:
				$newPage = new Page;
				$newPage->setParent($parent);
				$newPage->setTitle($title);
				$newPage->setTemplate($template);
				$newPage->setLocked(0);
				$newPage->setStyle($style);
				$newPage->setPageType($page_type);
				
				// If we have meta data, set it in the page class
				if($meta_desc){
					$newPage->setMetaDescription($meta_desc);
				}

				
				// Generate a unique name for the page:
				$name_ok = $newPage->generateName();
				if (!$name_ok) {
					$message[] = $page->drawLabel("tl_pedit_err_exists", 'A page with that name already exists in that section');
				}
				// The main site cannot contain any pages that clash with existing microsites.
				else if ($site->id==1 && $site->checkSiteName($newPage->getName())) {
					$message[] = $page->drawLabel("tl_pedit_err_micro1", "A microsite exists with that name");
					$message[] = $page->drawLabel("tl_pedit_err_micro2", "You cannot create pages on this site that have the same name as any microsites");
				}
				else if($shorturl && !$newPage->validShortURL($shorturl)){
					$message[] = "This short URL is the same as the page name";
				}
				else if($shorturl && $newPage->checkShortURL($shorturl) && ($shorturl!=$newPage->getShortURL()) ) {
					//$message[] = $page->drawLabel("tl_pedit_err_short", "An existing page already uses this short URL");
					$surl = $newPage->drawLinkByGUID($newPage->checkShortURL($shorturl, true));
					$message[] = 'An existing page already uses this short URL at <a href="'.$surl.'">'.$surl.'</a>';
				} 
				else {			
					$newPage->setHidden($hiddenflag);
					$newPage->setRobots($robotsflag);
					$newPage->setOffline($offlineflag);
					$newPage->setPrivate($private);
					$newPage->setComment($commentflag);
					$newPage->setSortOrder();					
					$newPage->setShortURL($shorturl);
					if( $newPage->create() ){

						// add tags
						$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
						$tags->addTagsToContent($newPage->getGUID(), str_replace(", ",",",$tagslist));						

						// page specific processing here
						switch ($template) {
							// Events page create
							case 19: $event->update($newPage->getGUID(), $_POST); break;
							// Resources page create
							case 16: $resource->update($newPage->getGUID(), $_POST['resource-list']); break;
							// Petition page create
							case 22: $petition->update($newPage->getGUID(), $_POST); break;
						}
						
						// If we created a hidden page we should go straight to edit the page contents
						if($hiddenflag=='1' && $newPage->getPageType()!=4){
							$redirectURL="{$newPage->drawLinkByGUID($newPage->getGUID())}?mode=edit&referer=/treeline/&guid={$newPage->getGUID()}";
							//print "goto($redirectURL)<br>";
							redirect($redirectURL);
						}
						// Otherwise we need to position the new page in the menu
						else {
							//print "for now we are not redirecting<br>\n";
							redirect("/treeline/menus/?mode=edit&referer=/treeline/&parent=$parent&guid={$newPage->getGUID()}&pagename={$newPage->getName()}");
						}
					}
					else {
						$message[] = 'Your page could not be added due to a technical error.  Please content your site\'s administrator.';
					}
				}
			}
		}
		else if ($action == 'edit') { // Edit an existing page
		
			if(!$findcat){
				$newPage = new Page();
				$newPage->loadByGUID($guid);
				$newPage->setHidden($hiddenflag);
				$newPage->setRobots($robotsflag);
				$newPage->setOffline($offlineflag);
				$newPage->setPrivate($private);
				$newPage->setComment($commentflag);

				if (!$title) {
					$message[] = $page->drawLabel("tl_pedit_err_title", 'Please enter a title for this page');
				}
				else if ($parent == 'xx' && 0) {
					$message[] = $page->drawLabel("tl_pedit_err_section", 'Please select a section for this page');
				}
				else if($shorturl && !$newPage->validShortURL($shorturl)){
					$message[] = "This short URL is the same as the page name";
				}
				else if($shorturl && $newPage->checkShortURL($shorturl) && ($shorturl!=$newPage->getShortURL()) ){
					$surl = $newPage->drawLinkByGUID($newPage->checkShortURL($shorturl, true));
					$message[] = 'An existing page already uses this short URL at <a href="'.$surl.'">'.$surl.'</a>';
				}
				else {
				
					if ($parent=="xx") $parent=$site->id;
					
					// Do we need to remove a shorturl?
					if (!$shorturl && $newPage->getShortURL()) {
						$newPage->deleteShortURL();
					}
					
					/*
					// Allow changing page names for the ar site as there 
					// may be a problem with generateName???
					if ($newPage->getTitle() != $title && $_SESSION['treeline_user_language']=="ar") {
						//print_r($_SESSION);
						$newPage->setTitle($title);
						$newName=$newPage->generateName();
						//print "got newname($newName)<br>";
						//$newPage->setName($newName);
					}
					*/
					
					// We should only set the parent if we dont have one or if the $parent(selected) is not its current master?
					if (!$newPage->getParent() || $newPage->getPrimary()!=$parent) {
						if( $newPage->getPageType()==4 || $newPage->getTemplate()==19){
							$newPage->setParent($site->id);
						}else{
							$newPage->setParent($parent);				
						}
					}

					$newPage->setTitle($title);
					if ($template>0) $newPage->setTemplate($template);
					//$newPage->setStyle($style);
					$newPage->setPageType($page_type);
					// If we have meta data, set it in the page class
					$newPage->setMetaDescription($meta_desc);

					$newPage->setShortURL($shorturl);
					
					// add tags
					$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
					$tags->addTagsToContent($guid, str_replace(", ",",",$tagslist));						

					$newPage->save();

					$link = $page->drawLinkByGUID($guid);

					// Page specific processing
					switch ($template) {
						// Events page save
						case 19: $event->update($guid, $_POST); break;
						// Resources page save
						case 16: $resource->update($newPage->getGUID(), $_POST['resource-list'], $_POST['description']); break;
						// Petition page update
						case 22: $petition->update($guid, $_POST); break;
					}
					
					// We have modified the page attributes
					// we do this here as the page is saved in other places but we only want 
					// to log an attribute update here.
					addHistory($user->id, "", $newPage->getGUID(), "Attributes modified", "pages");
					
					// If its a hiddne page go to edit content.
					if($_REQUEST['button-action']=="Save attributes" || $_POST['post_action']=="Save attributes") {
						$nextsteps.='<li><a href="/treeline/pages/?action=create">'.$page->drawLabel("tl_pedit_next_create", "Create a new web page").'</a></li>';
						$action='edit';
						$guid='';
					}
					else {
						if($hiddenflag=='1') $redirectURL="{$link}?mode=edit&referer=/treeline/&guid={$guid}";
						// Go to the add gallery images page
						else if($newPage->getTemplate()==18) $redirectURL="/treeline/galleries/?action=edit&guid={$guid}";
						// Go to the inline page editor
						else $redirectURL="{$link}?mode=edit&referer=/treeline/&guid={$guid}";
						redirect($redirectURL);
					}
				}
			}
		}
		else if ($action == "edittabs") {
		
			// Go through post data and save all tabs
			$message = array();
			foreach ($_POST as $k=>$v) {
				//$message[]="Got k($k)=v($v)";
				if (substr($k,0,6)=="title-") {
					$sql = '';
					$id = substr($k, 6);
					$title = $db->escape($v);
					$order = $_POST['order-'.$id]+0;
					$description = $db->escape($_POST['desc-'.$id]);
					$image = $db->escape($_POST['image-'.$id]);
					$delete = $_POST['delete-'.$id];
					//print "got ID($id) title($title) color($color) order($order)<br>\n";
					if ($id=="new") {
						if ($title) {
							$placeholder = uniqid();
							$query = "SELECT count(*) FROM slideshows WHERE guid='$placeholder' AND msv=".$site->id;
							//print "$query<br>\n";
							if ($db->get_var($query)) $message[]="You cannot add a new block with this title as one already exists";
							else {
								// now check we can put it in the content table
								$query = "SELECT count(*) FROM slideshows where guid='$placeholder' AND parent='".$site->id."'";
								//print "$query<br>\n";
								if ($db->get_var($query)) $message[]="You cannot add a new block with this title as content exists on this homepage already with this placeholder";
								else $sql = "INSERT INTO slideshows 
									(firstline, guid, secondline, `image`, sortorder, msv) 
									VALUES 
									('$title', '$placeholder', '$description', '$image', $order, ".$site->id.")
									";
							}
						}
						//else $message[] = "You must enter a title to add a new block to the slideshow";
					}
					else if ($id>0) {
						// Don't update the placeholder it will lead to dead content entries.
						if ($delete==1) {
							$sql = "DELETE FROM slideshows WHERE guid='".$id."'";
						}
						else if ($title) $sql = "UPDATE slideshows 
							SET firstline='$title', secondline='$description', 
							image='$image', sortorder=$order , parent='".$guid."'
							WHERE guid='$id'
							";
						else $message[]="No title entered for block ID[$id]";
					}
					else $message[]="Attempt to update invalid block ID[$id]";
					if ($sql) {
						//print "q($sql)<br>\n";
						$db->query($sql);
						if ($db->last_error) $message[]="Failed to update block ID[$id]";
					}
				}
				
			}
			// If all went ok then back to manage pages page.
			//print "done, message count(".count($message).")<br>\n";
			if (!count($message)) {
				$action = '';
				$nextsteps.='<li><a href="'.$site->link.'?mode=edit&amp;referer='.urlencode('/treeline/?section=edit').'">Edit the homepage</a></li>';
				$nextsteps.='<li><a href="/treeline/pages/?action=edittabs">Manage more tabs</a></li>';
				$feedback = "success";
				$message[]="Slideshow tabs have been updated";
			}
			
		}
		else if ($action == 'publish') { // Publish a page
			if($treeline->isContentPublishable($guid)) { 
				$newPage = new Page();
				$newPage->loadByGUID($guid);
				
				if ($newPage->publish()) {

					// Panels
					$panels = new PanelsPlaceholder();
					$panels->load($guid, 'panels');
					$panels->setMode("edit");
					$panels->publish();
					
					clearCache(array('footer'. $tmp .'.inc', 'menu'. $tmp .'.inc', 'sitemap'. $tmp .'.inc'));
		
					// What do we want to do next ?			
					//$nextsteps='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_pedit_next_manage", "Manage another web page").'</a></li>';
					$nextsteps.='<li><a href="/treeline/pages/?action=create">'.$page->drawLabel("tl_pedit_next_create", "Create a new web page").'</a></li>';
					if ($tasks->total>0) $nextsteps.='<li><a href="/treeline/tasks/">'.$page->drawLabel("tl_pedit_next_tasks", "View my tasks list").'</a></li>';
					$action="edit";
					$guid = '';
				}
				else $message[]=$page->drawLabel("tl_pedit_err_pubfail", "Failed to publish page");
			}
			else $message[]=$page->drawLabel("tl_pedit_err_publish", "Content is not publishable");
		}

		// Reject page edits
		// If we do not want the current edit we just remove the entry completely from content
		else if ($action == 'reject') { 

			$newPage = new Page();
			$newPage->loadByGUID($guid);
			if (!$newPage->rejectedits()) {
				$message[]=$page->drawLabel("tl_pedit_err_reject", "Failed to delete the current version of this page");
				$feedback="error";
			}

			// What do we want to do next ?			
			//$nextsteps='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_pedit_next_manage", "Manage another web page").'</a></li>';
			$nextsteps.='<li><a href="/treeline/pages/?action=create">'.$page->drawLabel("tl_pedit_next_create", "Create a new web page").'</a></li>';
			$action="edit";
			$guid = '';
		}


		else if ($action == 'delete') {
			if(!$findcat){
				$newPage = new Page();
				$newPage->loadByGUID($guid);
				$newPage->delete();
				clearCache(array('footer'. $tmp .'.inc', 'menu'. $tmp .'.inc', 'sitemap'. $tmp .'.inc'));

				// What do we want to do next ?			
				$nextsteps='<li><a href="/treeline/pages/?action=create">'.$page->drawLabel("tl_pedit_next_create", "Create a new web page").'</a></li>';
				/*
				<li><a href="/treeline/pages/?action=edit">Manage more pages</a></li>
				<li><a href="/treeline/pages/?action=create">Create a new web page</a></li>
				*/
				$action="edit"; $guid="";
			}
		}
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables','events'); // all CSS needed by this page
	$extraCSS = '
	em.optional {
		font-size: smaller;
		color: #999;
		float: left;
		margin: .75em 10px;
	}
	em.url {
		color: #666;
		float: left;
		font: smaller;
		margin: .75em 2px .75em 0;
	}
	input#shorturl{
		width: 160px;
	}
	
'."\n"; // extra on page CSS
	

	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = '
	
	$(document).ready(
		function() { 
				/*If "newsheadlines" is the selected option then show the checkbox otherwise don\'t*/
				$("select#parent option.newsheadlines").is(":selected") ? $("div#news_checkbox").show() : $("div#news_checkbox").hide();
				
				/* hide the checkbox * when other options are selected */
				$("select#parent option").click(
					function(){
						$("div#news_checkbox").hide();
					}
				);
				
				/* show the checkbox * when "newsheadlines" is selected */
				$("select#parent option.newsheadlines").click(
					function(){
						$("div#news_checkbox").show();
					}
				);
		}
	);
	
	
	function toggleEvent(index) {
		var event_div=document.getElementById("event_treelineBox");
		var res_div = document.getElementById("resource_treelineBox");
		var pet_div = document.getElementById("petition_treelineBox");
		
		event_div.style.display = "none";
		res_div.style.display = "none";
		pet_div.style.display = "none";		
		
		if (index==16) res_div.style.display = "block";	
		if (index==19) event_div.style.display = "block";	
		if (index==22) pet_div.style.display = "block";	
		
	}
	
	'; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("pages", $action);
	//$pageTitleH2 =  ucfirst($page->drawLabel("tl_generic_pages", 'Pages'));
	//$pageTitleH2 .= ($action)?' : '.ucfirst($page->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action)))):'';
	//$pageTitle = $pageTitleH2;
	
	//$pageClass = 'pages';
	$pageClass = $action."-content";
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
	
?>
<div id="primarycontent">
	<div id="primary_inner">
	<?php
		
	echo drawFeedback($feedback,$message);
		
	if ($nextsteps) echo treelineList($nextsteps, $page->drawLabel("tl_generic_next_steps", "Next steps"), "blue");

	// CREATE A NEW PAGE
    if ($action == 'create') { 
		include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ajax/forms/addEditPages.php');
    }  
				
	// EDIT MODE
	else if ($guid && $action == 'edit') { 

		// We do have a guid so edit the page
		$newPage = new $page;
		$newPage->loadByGUID($guid);
		include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ajax/forms/addEditPages.php');
	} 

	// Show page saved menu 
	else if ($guid && $action == "saved") {
	
		$nextsteps='<li><a href="/treeline/pages/?action=publish&guid='.$guid.'">'.$page->drawLabel("tl_next_publish", "Publish this page now").'</a></li>';
		$nextsteps.='<li><a href="/treeline/pages/?action=create">'.$page->drawLabel("tl_next_create_page", "Create a new web page").'</a></li>';
		$nextsteps.='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_next_manage_page", "Manage another web page").'</a></li>';
		echo treelineList($nextsteps, $page->drawLabel("tl_generic_next_steps", "Next steps"), "blue");
	}
			
	// Delete pages
	else if ($guid && $action == 'delete') { 
		$newPage = new $page;
		$newPage->loadByGUID($guid);

		// Check if this page has children and if so do not allow the page to be delete
		$query = "SELECT guid, title FROM pages WHERE parent = '$guid'";
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			if (count($results)) {
				$page_html = '<p>You cannot delete this page as it has the following children:</p>';
				foreach ($results as $result) {
					$page_html .= '<p><a href="/treeline/pages/?action=list&guid='.$result->guid.'">'.$result->title.'</a></p>';
					$child_count++;
				}
			}
		}
		if (!$child_count) {

			$page_html = '
				<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':'').'" method="post">
					<fieldset>
						<input type="hidden" name="action" value="'.$action.'" />
						<input type="hidden" name="guid" value="'.$guid.'" />
						</legend>
						<p class="instructions">'.$page->drawLabel("tl_pedit_del_msg1", "You are about to delete this page are you sure?").'</strong></p>
						<p class="instructions">'.$page->drawLabel("tl_pedit_reject_message2", "To preview this page first ").', <a href="'.$newPage->drawLink().'?mode=preview" target="_blank">'.$page->drawLabel("tl_generic_click_here", "click here").'</a>.</p>
						<fieldset class="buttons">
							<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_delete", "Delete")).'" />
						</fieldset>
					</fieldset>
				</form>
				';
		}
		$page_title = $page->drawLabel("tl_pedit_del_title", 'Delete page').' : '.$newPage->getTitle();
		echo treelineBox($page_html, $page_title, "blue");
	} 
				
	else if ($action == "edittabs") {
		include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ajax/forms/addHomepageTabs.php');
	}

	// Publish pages
	else if ($action == 'publish' && $guid) { 
					
		// Show individual publish form
		$newPage = new $page;
		$newPage->loadByGUID($guid);
						
		// Check this page is publishable 
		if($treeline->isContentPublishable($guid)) { 
			
			$page_html = '
			<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="guid" value="'.$guid.'" />
				<p class="instructions">'.$page->drawLabel("tl_pedit_pub_message", "You are about to publish this page. Are you sure?").'</p>
				<p class="instructions">'.$page->drawLabel("tl_pedit_reject_message2", "To preview this page first").', <a href="'.$newPage->drawLink().'?mode=preview" target="_blank">'.$page->drawLabel("tl_generic_click_here", "click here").'</a></p>
				<fieldset class="buttons">
					<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_continue", "Yes, publish it").'" />
				</fieldset>
			</fieldset>
			</form>
			';
			$page_title = $page->drawLabel("tl_pedit_pub_title", 'Publish page').' : '.$newPage->getTitle();
		} 
		// page isn't publishable 
		else { 
			$page_html = '
			<p>'.$page->drawLabel("tl_pedit_err_publish", "This page has not been edited recently and so is not eligible to be published. If it were to be published it could produce errors on your website").'</p>
			<p><a href="/treeline/pages/?action=publish">'.$page->drawLabel("tl_pedit_view_publish", "View all publishable pages").'</a></p>
			';
			$page_title=$newPage->getTitle().' '.$page->drawLabel("tl_pedit_not_publishable", "is not publishable");
		}
		echo treelineBox($page_html, $page_title, "blue");
	} 
				
	// Reject a publishable page
	else if ($action == 'reject' && $guid) { 
		
		// Show individual reject form
		$newPage = new $page;
		$newPage->loadByGUID($guid);
			
		// Check this page is publishable 
		// if we cant publish it then we cant reject edits to it.
		if($treeline->isContentPublishable($guid)) { 
			$page_html='
			<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="guid" value="'.$guid.'" />
				<p class="instructions">'.$page->drawLabel("tl_pedit_reject_message1", "You are about to reject changes to this page. Are you sure? All changes to the page since it was last published will be permenantly deleted").'</p>
				<p class="instructions">'.$page->drawLabel("tl_pedit_reject_message2", "To preview this page first").', <a href="'.$newPage->drawLink().'?mode=preview" target="_blank">'.$page->drawLabel("tl_generic_click_here", "click here").'</a>.</p>
				<fieldset class="buttons">
					<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_ereject", "Yes, reject it").'" />
				</fieldset>
			</fieldset>
			</form>
			';
			$page_title=$page->drawLabel("tl_pedit_reject_title", 'Reject edits to page').': '.$newPage->getTitle();
		} 
		// page isn't publishable  
		else { 
			$page_html='
			<p>'.$page->drawLabel("tl_pedit_err_reject", "This page has not been edited recently and so there is not content to reject").'</p>
			<p><a href="/treeline/pages/?action=publish">'.$page->drawLabel("tl_pedit_view_publish", "View all publishable pages").'</a></p>
			';
			$page_title = $newPage->getTitle().' '.$page->drawLabel("tl_pedit_not_publishable", "is not publishable");
		} 
		echo treelineBox($page_html, $page_title, "blue");
	}

	
	// Search for a page to manage
	else if (!$guid || $action=="list") { //or action == 'edit' ??? :/

		// no individual page selected 
		?><h2 class="pagetitle rounded"><?=$page->drawLabel("tl_pedit_list", "Please select the page you would like to edit from the list below")?>.</h2><?php 
				
		// Should we show the site map
		if($view == 'map') {  
			$page_html='<p>'.$page->drawLabel("tl_pedit_sel_page1", "To edit a page, please select it from the list below below or go back to").' <a href="'.$_SERVER['PHP_SELF'].'?action=edit">'.$page->drawLabel("tl_pedit_form_view", "form view").'</a>.</p>';
			$page_html.=$treeline->drawEditablePagesByParent($site->id);
			echo treelineBox($page_html, $page->drawLabel("tl_pedit_find_content", "Find existing content to manage"), "blue");
		}
		// Or allow page searching and show the default list / search list 
		else {
			if (!$category) $catgory="title";
			$page_html='
			<form id="treeline" action="/treeline/pages/'.($DEBUG?'?debug':"").'" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="guid" value="'.$guid.'" />
				<p class="instructions">'.$page->drawLabel("tl_pedit_cant_fint", "Can't find the page you need? Try ").' <a href="/treeline/pages/?action=edit&amp;view=map"><em>'.lcfirst($page->drawLabel("tl_pedit_site_map", "Site map view")).'</em></a>.</p>
				<label for="keywords">'.$page->drawGeneric("search_for", 1).': </label>
				<input type="text" name="keywords" id="keywords" value="'.$keywords.'" /><br />
				<label for="category">'.$page->drawGeneric("search_by", 1).':</label>
				<select name="category" id="category">
					<option value="title" '.($category=='title'?'selected="selected"':"").'>'.ucfirst($page->drawLabel("tl_generic_title", "Title")).'</option>
					<option value="content" '.($category=='content'?'selected="selected"':"").'>'.ucfirst($page->drawLabel("tl_generic_content", "Content")).'</option>
					<option value="tags" '.($category=='tags'?'selected="selected"':"").'>'.ucfirst($page->drawLabel("tl_generic_tags", "Tags")).'</option>
				</select><br />
				<input type="hidden" name="findcat" value="1" />
				<fieldset class="buttons">
					<input name="filter" type="submit" class="submit" value="'.$page->drawLabel("tl_generic_search", "Search").'" />
				</fieldset>
			</fieldset>
			</form>
			';
			echo treelineBox($page_html, $page->drawLabel("tl_pedit_search_title", "Find existing content to manage"), "blue");
			
			$category = ($category == 'xx') ? '' : $category;
			if(!$category && $_POST['filter']=="Search") {
				$feedback = 'error';
				$message = $page-drawLabel("tl_pedit_err_field", 'You need to specify a field to search by');
				echo drawFeedback($feedback,$message);
			}
			else if(!$keywords && $_POST['filter']=="Search") {
				$feedback = 'error';
				$message = $page->drawLabel("tl_pedit_err_keyword", 'You need to specify keywords to search with');
				echo drawFeedback($feedback,$message);
			}
			
			if( (!$category && !$keywords) || ($category && $keywords)) {
				echo $page->drawPageList($thispage,$action,$category,$keywords,0,0, $guid);
			}
		}
	} 
	
	else if ($action) {
		// we have been passed a guid but nothing has been done with it.
		mail("phil.redclift@ichameleon.com", $site->name." edit page", "Send guid($guid) and action($action) but didnt process it????");
	}

	else {
		// This is fine, 
		// This happens if we publish/reject or delete a page.
	}
	?>				

	</div>
</div>

<?php 
if ($global_cke_init) {
	echo $page->initCKE();
	echo $global_cke_init;
}

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 

?>

