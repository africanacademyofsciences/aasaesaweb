<?php

//ini_set("display_errors", 1);
//error_reporting(E_ALL ^ E_NOTICE);

	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/poll.class.php");

	$poll = new Poll($guid);

	// We are not going to tag panels any more
	//$tags = new Tags($site->id, 4);// TAGS
	
	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/");
	

	$message = array();
	if ($_REQUEST['message']) $message[]=$_REQUEST['message'];
	$feedback = read($_REQUEST,'feedback','error');

	$guid = read($_REQUEST,'guid',false);
	$mode = read($_REQUEST,'mode','');
	$view = read($_REQUEST,'view',false);
	$type = read($_POST,'type',false);
	$title = read($_POST,'title','');
	$style = read($_POST,'style',8);
	$treeline_panelcontent = read($_POST,'treeline_panelcontent',false);

	$keywords = read($_REQUEST,'keywords',false);
	$category = read($_REQUEST,'category','');
	if ($keywords && !$category) $category="title";	// Default category if we are searching
	
	$findcat = read($_REQUEST,'findcat',false);
	$thispage = read($_REQUEST,'page',1);
	$page->setPage($thispage);
	
	$template_id = 0;
	
	/*
	if( substr_count($action,'poll')>0 ){
		foreach( $_POST as $key => $value ){
			if( substr_count($key,'poll')>0 ){
				${$key} = $value;
			}
		}
	}
	*/
	
	$thisURL = substr($_SERVER['REQUEST_URI'],0,strrpos($_SERVER['REQUEST_URI'],'_'));

	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
		//print "Action($action) submitted(".$_POST['submitted'].")<br>\n";
		if($action=='select'){
			if(!$type) $message[] = $page->drawLabel("tl_paedit_err_type", 'You need to select a panel type');
			else {
				$action = "create";
				$template_id = $_POST['type'];
			}
		}
		//print "Action($action) submitted(".$_POST['submitted'].")<br>\n";
		
		
		// --------------------------------------------------------------------------		
		// Need to check for tag adding and ensure no other actions are processed
		// We actually add tags in the /treeline/includes/ajax/forms/addEditTags.php file
		if ($_POST['tagaction']) {
			;
		}
		
		
		// --------------------------------------------------------------------------		
		else if ($action == 'create' && $_POST['submitted']==1) {
			
			$panel_template_id=6;	// Standard panel
			if ($_POST['type']>0) $panel_template_id = $_POST['type'];
			
			switch ($panel_template_id) {
				
				// --------------------------------------------------------------------------		
				// CREATE A STANDARD PANEL
				case 6 : 
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else {
						// Create a new panel:
						$panel = new Page;
						$panel->setParent($site->id); 	// Set it to be a child of this page -- that's how we know it's a panel belonging to this site
						$panel->setTitle($title);
						$panel->setStyle($style);
						// Generate a unique name for the panel. Is this necessary?
						$name = $panel->generateName();
						if (!$name) $message[] = $page->drawLabel("tl_paedit_err_exists", 'A panel with that name already exists');
						else {		
										
							$panel->setHidden('0');
							$panel->setSortOrder();					
							$panel->setTemplate($panel_template_id);
							$panel->setMetaDescription('A new panel');
							
							$panel->create(2);
							$guid=$panel->getGUID();

							// Head off to the website and add panel content
							$redirectURL = $panel->drawLink()."?mode=edit&guid=$guid&referer=/treeline/";
							redirect($redirectURL);
							//print "would go to($redirectURL)<br>\n";
						}
					}
					break;
					
				// --------------------------------------------------------------------------		
				// CREATE AN RSS FEED PANEL
				case 7:
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else if(!$treeline_panelcontent) $message[] = $page->drawLabel("tl_paedit_err_RSSURL", 'You need to specify the full URL for the RSS feed');
					else{
						// Create a new panel:
						$panel = new Page;
						$panel->setParent($site->id); 	// Set it to be a child of this page -- that's how we know it's a panel belonging to this site
						$panel->setTitle($title);
						$name = $panel->generateName();
						if (!$name) $message[] = $page->drawLabel("tl_paedit_err_exists", 'A panel with that name already exists');
						else {
							$panel->setHidden('0');
							$panel->setSortOrder();					
							$panel->setTemplate(7);
							$panel->setMetaDescription('A new RSS panel');
		
							$panel->create(2); // 2 = panel (content type)
							
							$content = new HTMLPlaceholder();
							$content->parent = $panel->getGUID();
							$content->name = 'panelcontent';
							$content->guid = uniqid();
							if($content->save()) {
								$message[] = ' -> content saved too...'; 
							}
							$panel->publish();	// Its an RSS panel, we dont have any content to mess with.
							$feedback = "success";
						}
					}
					break;		

				// --------------------------------------------------------------------------		
				// Create an intelligent panel
				case 15: 
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else{
						// Create a new panel:
						$panel = new Page;
						$panel->setParent($site->id); 	// Set it to be a child of this page -- that's how we know it's a panel belonging to this site
						$panel->setTitle($title);
						$name = $panel->generateName();
						if (!$name) $message[] = $page->drawLabel("tl_paedit_err_exists", 'A panel with that name already exists');
						else {

							$panel->setHidden('0');
							$panel->setSortOrder();					
							$panel->setTemplate(15);
							$panel->setMetaDescription('An intelligent links panel');
							$panel->create(2); // 2 = panel (content type)
							$panel->publish(true);	

							$tags = new Tags();
							$tags->updateIntelligentLinkPanelDetails($panel->getGUID(), $_POST['accuracy'], $_POST['maxlinks'], 1, $title);

							$feedback = "success";
						}
					}
					break;		
								
				// --------------------------------------------------------------------------		
				// CREATE A POLL PANEL
				case 17:
					
					$tmp = array();
					$numberText = array('one'=>1, 'two'=>2, 'three'=>3, 'four'=>4, 'five'=>5);
					
					foreach( $_POST as $key => $value ){
						if( preg_match('/poll_/',$key) ){
							$key = preg_replace('/poll_/','',$key);
							
							if( substr_count($key,'answer')>0 ){
								if( $value>'' ){
									$answerKey = preg_replace('/answer/','',$key);
									$answerKey = str_replace('_','',$answerKey);
									if( array_key_exists($answerKey,$numberText) ){
										$answerKey = $numberText[$answerKey];
										$tmp['answers'][$answerKey] = $value;
									}else{
										$tmp[$answerKey] = $value;
									}
								}
							}else{
								$tmp[$key] = $value;
							}
						}
					}
					//echo '<pre>'. print_r($tmp,true) .'</pre>';
					if (!$tmp['title']) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					if (!$tmp['question']) $message[] = $page->drawLabel("tl_paedit_err_pollques", "You must enter the question for your poll");
					if (!$tmp['response']) $message[] = $page->drawLabel("tl_paedit_err_pollresp", "You must enter some response text");
					if (!is_array($tmp['answers'])) $message[] = $page->drawLabel("tl_paedit_err_pollans", "You must enter at least one possible answer");
					if (!count($message)) {
						if ($poll->create($tmp['title'], $tmp['question'], $tmp['response'],$tmp['answers'],$tmp['default']) ) $feedback = "success";
						else $message[] = $page->drawLabel("pa_edit_err_poll_save", 'Your poll could not be saved');			
					}
					break;
					
				// --------------------------------------------------------------------------		
				// Create a functional panel
				// You can't edit these, just create them.
				case 24:
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else{
						// Create a new panel:
						$panel = new Page;
						$panel->setParent($site->id); 	// Set it to be a child of this page -- that's how we know it's a panel belonging to this site
						$panel->setTitle($title);
						$name = $panel->generateName();
						if (!$name) $message[] = $page->drawLabel("tl_paedit_err_exists", 'A panel with that name already exists');
						else {
							$panel->setHidden('0');
							$panel->setSortOrder();					
							$panel->setTemplate(24);
							$panel->setMetaDescription('A new functional panel');
		
							$panel->create(2); // 2 = panel (content type)
							// Can't publish this panel as it has no content
							if ($panel->getGUID()) {
								//$query = "UPDATE pages SET date_modified=NOW(), date_published=NOW() WHERE guid='".$panel->getGUID()."'";
								$query = "UPDATE pages SET date_modified=NOW(), date_published=NOW(), user_modified=".$user->getID().", user_published=".$user->getID()." WHERE guid='".$panel->getGUID()."'";
								$db->query($query);
							}
							$feedback = "success";
						}
					}
					break;
				
				// --------------------------------------------------------------------------		
				default :
					$message[]=$page->drawLabel("tl_paedit_err_template_id", "Not sure how to create panel template").'('.$panel_template_id.')';
					break;
					
			}
			if ($feedback == "success") {
				$message=$page->drawLabel("tl_paedit_suc_created", "Your panel has been created");
				$nextsteps.='<li><a href="/treeline/panels/?action=select&amp;mode=create">'.$page->drawLabel("tl_paedit_next_create", "Create a new panel").'</a></li>';
				$nextsteps.='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_pedit_next_manage", "Manage web pages").'</a></li>';
				$action = "edit";
			}
		}
		
		
		
		// --------------------------------------------------------------------------		
		// Save changes to panel atributes
		else if ($action == 'edit' && $_POST['submitted']==1 && $guid) {
			$panel = new Page();
			$panel->loadByGUID($guid);
			switch ($panel->template_id) {
		
				// --------------------------------------------------------------------------		
				// Post change to a standard panel
				case 6: 
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else {
						$panel->setTitle($title);
						$name = $panel->generateName();
						if (!$name) $message[] = $page->drawLabel("tl_paedit_err_exists", 'A panel with that name already exists');
						else {			
							$panel->save();
							$feedback="success";
						}
					}
					break;
					
				// --------------------------------------------------------------------------		
				// Post changes to an RSS panel attributes					
				case 7:
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else if(!$treeline_panelcontent) $message[] = $page->drawLabel("tl_paedit_err_RSSURL", 'You need to specify the full URL for the RSS feed');
					else {
						$content = new HTMLPlaceholder();
						$content->load($guid,'panelcontent');
						$content->name = 'panelcontent';
		
						//$old_title = $panel->getTitle();
						$panel->setTitle($title);
						//if ($title != $old_title) {
							$name = $panel->generateName();
							if (!$name) $message[] = $page->drawLabel("tl_paedit_err_exists", 'A panel with that name already exists');
						//}
						if (!count($message)) {
							$content->save();
							$panel->save();
							$panel->publish(); // RSS panels have no content
							$feedback = "success";
						}
					}
					break;
				
				// --------------------------------------------------------------------------		
				// Post change to an intelligent panel
				case 15: 
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else {
						$panel->setTitle($title);
						$name = $panel->generateName();
						if (!$name) $message[] = $page->drawLabel("tl_paedit_err_exists", 'A panel with that name already exists');
						else {			
							$tags = new Tags();
							$tags->updateIntelligentLinkPanelDetails($guid, $_POST['accuracy'], $_POST['maxlinks'], 1, $title);
							$panel->save();
							$panel->publish(true);
							
							$feedback="success";
						}
					}
					break;

					
				// --------------------------------------------------------------------------		
				// Save changes to a poll panel
				case 17:		
					//echo '<pre>'. print_r($_POST,true) .'</pre>';
					$tmp = array();
					$numberText = array('one'=>1, 'two'=>2, 'three'=>3, 'four'=>4, 'five'=>5);
					
					foreach( $_POST as $key => $value ){
						if( preg_match('/poll_/',$key) ){
							$key = preg_replace('/poll_/','',$key);
							
							if( substr_count($key,'answer')>0 ){
								if( $value>'' ){
									$answerKey = preg_replace('/answer/','',$key);
									$answerKey = str_replace('_','',$answerKey);
									if( array_key_exists($answerKey,$numberText) ){
										$answerKey = $numberText[$answerKey];
										$tmp['answers'][$answerKey] = $value;
									}else{
										$tmp[$answerKey] = $value;
									}
								}
							}else{
								$tmp[$key] = $value;
							}
						}
					}
					//echo '<pre>'. print_r($tmp,true) .'</pre>';
					if (!$tmp['title']) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					if (!$tmp['question']) $message[] = $page->drawLabel("tl_paedit_err_pollques", "You must enter the question for your poll");
					if (!$tmp['response']) $message[] = $page->drawLabel("tl_paedit_err_pollresp", "You must enter some response text");
					if (!is_array($tmp['answers'])) $message[] = $page->drawLabel("tl_paedit_err_pollans", "You must enter at least one possible answer");
					if (!count($message)) {
						if( $poll->update($guid,$tmp['title'], $tmp['question'], $tmp['response'],$tmp['answers'],$tmp['default']) ) {
							$feedback = "success";
							$panel->save();
							$panel->publish(true);
						}
						else $message[] = $page->drawLabel("tl_paedit_err_fail_save", 'Your changes could not be saved');			
					}
					break;

				// Update functional panel title only
				case 24:
					if (!$title) $message[] = $page->drawLabel("tl_paedit_err_title", 'Please enter a title for this panel');
					else {
						$panel->setTitle($title);
						$panel->save();
						// Can't publish this panel as it has no content
						//print "saved panel(".$panel->getGUID().") now publish<br>\n";
						if ($panel->getGUID()) {
							$query = "UPDATE pages SET date_modified=NOW(), date_published=NOW(), user_modified=".$user->getID().", user_published=".$user->getID()." WHERE guid='".$panel->getGUID()."'";
							$db->query($query);
						}
						$feedback="success";
					}
					break;
			
				default :
					$message[]=$page->drawLabel("tl_paedit_err_save_temp", "Not sure how to post changes to a panel type")."(".$panel->template_id.")";
					break;
			}
			if ($feedback == "success") {
				$message[]=$page->drawLabel("tl_paedit_suc_saved", "Your panel details have been saved");
				$nextsteps.='<li><a href="/treeline/panels/?action=select&amp;mode=create">'.$page->drawLabel("tl_paedit_next_create", "Create a new panel").'</a></li>';
				$nextsteps.='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_pedit_next_manage", "Manage web pages").'</a></li>';
				if ($tasks->count>0) $nextsteps.='<li><a href="/treeline/tasks/">'.$page->drawLabel("tl_pedit_next_tasks", "View my tasks list").'</a></li>';
				$guid = '';
			}
			
		}
		
		
		// --------------------------------------------------------------------------		
		else if ($action == 'publish') {
			$panel = new Page();
			$panel->loadByGUID($guid);
			$panel->publish();

			// What do we want to do next ?			
			$nextsteps.='<li><a href="/treeline/panels/?action=select&amp;mode=create">'.$page->drawLabel("tl_paedit_next_create", "Create a new panel").'</a></li>';
			$nextsteps.='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_pedit_next_manage", "Manage web pages").'</a></li>';
			if ($tasks->count>0) $nextsteps.='<li><a href="/treeline/tasks/">'.$page->drawLabel("tl_pedit_next_tasks", "View my tasks list").'</a></li>';

			$action="";
			$guid="";
			
		}
		
		
		// --------------------------------------------------------------------------		
		else if ($action == 'delete' && $guid) {
			$newPage = new Page();
			$newPage->loadByGUID($guid);
			
			// if we have a poll panel...
			if($newPage->template_id==17){
				$poll = new Poll($guid);
				$poll->delete($guid);				
			}
			else {
				$newPage->delete();
			}

			// Intelligent panel needs to come out of the links table too.
			if ($newPage->template_id==15) {
				$query = "DELETE FROM tags_intelligent_link_panels WHERE guid = '$guid' LIMIT 1";
				$db->query($query);
			}

			// What do we want to do next ?			
			$nextsteps='<li><a href="/treeline/panels/?action=edit">'.$page->drawLabel("tl_paedit_next_manage", "Manage more panels").'</a></li>';
			$nextsteps.='<li><a href="/treeline/panels/?action=select&amp;mode=create">'.$page->drawLabel("tl_paedit_next_create", "Create a new panel").'</a></li>';
			$action="edit"; 
			$guid="";
		}
		

		// --------------------------------------------------------------------------		
		// Reject panel edits
		// If we do not want the current edit we just remove the entry completely from content
		else if ($action == 'reject') { 

			$panel = new Page();
			$panel->loadByGUID($guid);
			if (!$panel->rejectedits()) {
				$message[]=$page->drawLabel("tl_paedit_err_reject", "Failed to reject edits to the current version of this panel");
				$feedback="error";
			}

			// What do we want to do next ?			
			$nextsteps='<li><a href="/treeline/panels/?action=edit">'.$page->drawLabel("tl_paedit_next_manage", "Manage more panels").'</a></li>';
			$nextsteps.='<li><a href="/treeline/panels/?action=select&amp;mode=create">'.$page->drawLabel("tl_paedit_next_create", "Create a new panel").'</a></li>';
			if ($tasks->count>0) $nextsteps.='<li><a href="/treeline/tasks/">'.$page->drawLabel("tl_pedit_next_tasks", "View my tasks list").'</a></li>';

			$action="";
			$guid='';
		}
		
		

		// --------------------------------------------------------------------------		
		else if($action=='createpoll'){
			// ADD POLL
			//echo '<pre>'. print_r($_POST,true) .'</pre>';
		}
		
		
	}
	// end if $_POST
	else {
	
		if ($action=="saved") {
			$message[] = $page->drawLabel("tl_paedit_err_saved", "Panel saved successfully");
			$feedback="success";
			
			if ($guid) $nextsteps='<li><a href="/treeline/panels/?action=publish&guid='.$guid.'">'.$page->drawLabel("tl_paedit_next_publish", "Publish this panel now").'</a></li>';
			$nextsteps.='<li><a href="/treeline/panels/?action=create">'.$page->drawLabel("tl_paedit_next_create", "Create a new panel").'</a></li>';
			$action = "edit";
			$guid = '';
		}
		
		else if ($action == "discarded") {
			$message[] = $page->drawLabel("tl_paedit_err_nosave", "Your changes were not saved");
			$action = "edit";
			$guid = '';
		}
		
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
	.optional {
		font-size:smaller;
		color:#999;
	}
	.url {
		font-size:smaller;
		color:#66;
		font-family:Arial, Helvetica, sans-serif;
	}
	
	
	/*
	form fieldset fieldset{
		position:relative;
	}
	form fieldset fieldset p {
		margin-bottom:10px;
	}
	form fieldset fieldset label#poll_default {
		font-weight:normal;
		width:auto;
		position:absolute;
		top:35px;
		left:33.5em;
	}
	form fieldset fieldset input.radio {
		width:auto;
		margin-left:2em;
	}
	*/
	
	fieldset#answers p.instructions{
		margin-bottom: 2em;
	}
	
	fieldset#answers label#poll_default{
		clear: none;
		float: left;
		font-weight: normal;
		margin: -1.5em 0 0;
		width: 30px;
	}
	
	
	fieldset#answers input{
		width: 150px;
	}
	
	fieldset#answers input.checkbox{
		clear: none;
		float: left;
		margin-left: 10px;		
		width: 1em;
	}
	
		fieldset#answers input#poll_default_answer1{
			
		}
	
	
	
	div.styleOption {
		float: left;
		margin: 0px 20px 10px 0;
		padding: 102px 0 0;
	}
	
	div.styleOption *{
		float: none;
		margin: 0 !important;
	}
	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	//$pageTitleH2 = ($action) ? 'Panels : '.ucwords(str_replace('rss',' RSS',$action)) : 'Panels';
	//$pageTitle = ($action) ? 'Panels : '.ucwords(str_replace('rss',' RSS',$action)) : 'Panels';
	// Page title	
	//$pageTitleH2 =  ucfirst($page->drawLabel("tl_generic_panels", 'Panels'));
	//$pageTitleH2 .= ($action)?' : '.ucfirst($page->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action)))):'';
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("panels", $action);
	//print "action($action)<br>\n";
	
	if ($action=="select" || substr($action, 0, 6)=="create") $pageClass = 'create-content';
	else $pageClass="edit-content";
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
	
	// thickBox preview URL variables
	$thickBoxURL = '&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width='.$site->getConfig("site_page_width");
		
?>
<div id="primarycontent">
<div id="primary_inner">
<?php

echo drawFeedback($feedback,$message);

if ($nextsteps) echo treelineList($nextsteps, ucfirst($page->drawLabel("tl_generic_next_steps", "Next steps")), "blue");
	
if ($action == 'select') { 
	$panel_opts = '';
	$query = "SELECT template_id AS id, template_title AS title 
		FROM pages_templates 
		WHERE template_type=2 AND user_selectable=1
		ORDER BY template_title
		";
	if ($results = $db->get_results($query)) {
		foreach ($results as $result) {
			$panel_opts.='<option value="'.$result->id.'">'.$page->drawLabel("tl_paedit_".str_replace(" ", "-", $result->title), $result->title).'</option>';
		}
	}

	$page_html = '
    <form id="treeline" action="'.$thisURL.($DEBUG?'?debug':"").'" method="post">
	<fieldset>
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="guid" value="'.$guid.'" />
		<input type="hidden" name="mode" value="'.$mode.'" />
		<p class="instructions">'.$page->drawLabel("tl_paedit_select_message", "Please select a panel type from the list below").':</p>
		<fieldset class="field">
			<label for="type">'.$page->drawLabel("tl_paedit_field_type", "Panel Type").':</label>
			<select name="type" id="type">
				'.$panel_opts.'
			</select>
		</fieldset>
		<fieldset class="buttons">		
			<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_submit", "Submit")).'" />
		</fieldset>
    </fieldset>
    </form>
	';
    echo treelineBox($page_html, $page->drawLabel("tl_paedit_select_title", "Select Panel Type"), "blue");
} 
// ----------------------------------------------------------


// ----------------------------------------------------------
// CREATE A NEW STANDARD PANEL
// ----------------------------------------------------------
else if ($action == 'create') { 
	if (!$template_id && $_SERVER['REQUEST_METHOD']=="POST") $template_id = $_POST['type'];
	$page_html = '
    <form id="treeline" method="post">
    <fieldset>
	';
	//print "Got template_id($template_id) POST(".print_r($_POST, true).")<br>\n";
	switch ($template_id) {
		case 6:
			include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addPanels.php");
			break;
		// Create an RSS panel
		case 7: 
			include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addRSSPanel.php");
			break;
		// Create an intelligent link panel
		case 15: 
			$tags = new Tags();
			$tags->setMode("edit");
			$page_html .= '
			<p class="instructions">'.$page->drawLabel("tl_paedit_intel_message", "Please enter a title for your panel and set the accuracy and numnber of links you would like displayed").'</p>
			'.$tags->drawRelatedContentLinks().'
			<fieldset class="buttons">		
				<input type="hidden" name="type" value="'.$template_id.'" />
				<input type="hidden" name="action" value="create" />
				<input type="hidden" name="submitted" value="1" />
				<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_save", "Save")).'" />
			</fieldset>
			';
			break;
		// Create a poll panel
		case 17: 
			include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditPollPanels.php");
			break;
		// Create a functional panel
		case 24: 
			$page_html .= '
				<fieldset>
					<input type="hidden" name="action" value="'.$action.'" />
					<input type="hidden" name="guid" value="'.$guid.'" />
					<input type="hidden" name="mode" value="'.$mode.'" />
					<input type="hidden" name="submitted" value="1" />
					<input type="hidden" name="type" value="'.$template_id.'" />
					<p class="instructions">'.$page->drawLabel("tl_paedit_func_message1", "To create a functional panel just enter the title. All code for this panel must be added manually.").'</p>
					<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
					<input type="text" name="title" id="title" value="'.($title?$title:$panel->title).'"/><br />
					<fieldset class="buttons">		
						<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_save", "Save")).'" />
					</fieldset>
				</fieldset>
				';
			break;				
		// Invalid panel type
		default:  
			$page_html.='<p>'.$page->drawLabel("tl_paedit_err_template_id", "Not sure how to create panel template").'('.$template_id.')</p>';
			break;
	}
	$page_html.='
    </fieldset>
    </form>
	';
    echo treelineBox($page_html, $page->drawLabel("tl_paedit_create_title", "Create content"), "blue");
} 
// ----------------------------------------------------------


// ----------------------------------------------------------
// EDIT A STANDARD PANEL 
// ----------------------------------------------------------
else if ($action == 'edit' && $guid) { 
	$panel = new $page;
	$panel->loadByGUID($guid);

	$page_html = '
    <form id="treeline" method="post">
	<fieldset>
	';

	switch ($panel->template_id) {
		// Edit standard panel
		case 6: 
			$page_html .= '
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<input type="hidden" name="mode" value="'.$mode.'" />
			<input type="hidden" name="submitted" value="1" />
			<p class="instructions">'.$page->drawLabel("tl_paedit_edit_message", "To edit the details of this panel, complete the form below and press submit").'</p>
			<div>
				<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
				<input type="text" name="title" id="title" value="'.$panel->title.'"/>
			</div>
			<fieldset>
				<legend>'.$page->drawLabel("tl_paedit_field_app", "Appearance").':</legend>
			';
		
			//print "post style(".$_POST['style'].") panel style(".$panel->style_id.")<br>\n";
			$currentStyle = ($_POST['style']) ? $_POST['style'] : $panel->style_id;
			$currentStyle = ($currentStyle) ? $currentStyle : 8;
			$page_html.= $panel->drawStyleList($currentStyle, 6);	// The six means draw the styles availble for panels (which have a template id of 6)
					 
			$page_html.='
			</fieldset> 
			
			<fieldset class="buttons">		
				<input type="submit" class="submit" value="'.$page->drawLabel("tl_paedit_but_saveed", "Save attributes").'" />
			</fieldset>
			';
			break;
			
		// Edit an RSS panel
		case 7: 
			include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/editRSSpanel.php");
			break;
			
		// Edit an intelligent link panel
		case 15: 
			$tags = new Tags();
			$tags->setMode("edit");
			$page_html .= '
			<p class="instructions">'.$page->drawLabel("tl_paedit_intel_message", "Please enter a title for your panel and set the accuracy and numnber of links you would like displayed").'</p>
			'.$tags->drawRelatedContentLinks($guid).'
			<fieldset class="buttons">		
				<input type="hidden" name="action" value="edit" />
				<input type="hidden" name="submitted" value="1" />
				<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_save", "Save")).'" />
			</fieldset>
			';
			break;
		// Edit a poll panel
		case 17: 
			include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditPollPanels.php");
			break;
		// Create a functional panel
		case 24: 
			$page_html .= '
				<fieldset>
					<input type="hidden" name="action" value="'.$action.'" />
					<input type="hidden" name="guid" value="'.$guid.'" />
					<input type="hidden" name="mode" value="'.$mode.'" />
					<input type="hidden" name="submitted" value="1" />
					<p class="instructions">'.$page->drawLabel("tl_paedit_func_message2", "All code for this panel must be added manually, you can only change the panel title here.").'</p>
					<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
					<input type="text" name="title" id="title" value="'.($title?$title:$panel->title).'"/><br />
					<fieldset class="buttons">		
						<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_save", "Save")).'" />
					</fieldset>
				</fieldset>
				';
			break;				
		// Invalid panel type
		default:  
			$page_html.='<p>'.$page->drawLabel("tl_paedit_ederr_template_id", "Not sure how to modify panel template").'('.$template_id.')</p>';
			break;
			
	}

	$page_html.='
	</fieldset>
    </form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_paedit_edit_title", "Edit panel")." : ".$panel->getTitle(), "blue");

} 


else if ($guid && $action == 'delete') { 

    $panel = new $page;
    $panel->loadByGUID($guid);
	$page_html = '
        <form id="treeline" method="post">
        <fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<input type="hidden" name="mode" value="'.$mode.'" />
			<p class="instructions">'.$page->drawLabel("tl_paedit_delete_confirm", "You are about to delete this panel. Are you sure?").'</p>
			<p class="instructions">'.$page->drawLabel("tl_paedit_delete_preview", "To preview this panel first").', <a href="'.$panel->drawLink().'?mode=preview" target="_blank" title="'.$panel->getTitle().' Panel preview">'.$page->drawLabel("tl_generic_press_here", "press here").'</a>.</p>			
			<fieldset class="buttons">		
				<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_delete", "Delete").'" />
			</fieldset>
		</fieldset>
		</form>
		';
	echo treelineBox($page_html, $page->drawLabel("tl_paedit_delete_title", "Delete panel")." : ".$panel->getTitle(), "blue");
}	

else if ($guid && $action == 'publish') {
	
	/*
	if (!$guid) {
		?><p>To publish a panel, please select it from the list below below:</p><?php
		//echo $treeline->drawPublishablePanelsByParent($page->getGUID());
		echo $panel->drawPagePublishableList($thispage, 'panels');
	} 
	*/
	$panel = new $page;
	$panel->loadByGUID($guid);

	if($treeline->isContentPublishable($guid, 'panelcontent')) {
		$page_html = '
		<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<input type="hidden" name="mode" value="'.$mode.'" />
				<p class="instructions">'.$page->drawLabel("tl_paedit_publish_message", "You are about to publish this panel. Are you sure?").'</p>
				<p class="instructions">'.$page->drawLabel("tl_paedit_delete_preview", "To preview this panel first").', <a href="'.$panel->drawLink().'?mode=preview" target="_blank">'.$page->drawLabel("tl_generic_click_here", "click here").'</a>.</p>			
			<fieldset class="buttons">		
				<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_publish", "Publish").'" />
			</fieldset>
		</fieldset>
		</form>
		';
	}
	else {
		// page isn't publishable 
		$page_html='
		<p>'.$page->drawLabel("tl_paedit_err_no_publish", "This panel has not been edited recently and so there are no changes to publish").'</p>
		<p><a href="/treeline/panels/?action=publish">'.$page->drawLabel("tl_paedit_other_publish", "View other publishable panels").'</a></p>
		';
	}
	echo treelineBox($page_html, $page->drawLabel("tl_paedit_publish_title", 'Publish panel').' : '.$panel->getTitle(), "blue");
} 

// Reject a publishable panel
else if ($guid && $action == 'reject') { 
	
	// Show individual reject form
	$panel = new $page;
	$panel->loadByGUID($guid); 

	$page_title = $page->drawLabel("tl_paedit_reject_title", 'Reject edits to panel').' : '.$panel->getTitle();

	// page is rejectable
	if($treeline->isContentPublishable($guid, 'panelcontent')) {
		$page_html = '
        <form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
        <fieldset>
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="guid" value="'.$guid.'" />
            <p class="instructions">'.$page->drawLabel("tl_paedit_reject_confirm", "You are about to reject changes to this panel. Are you sure? All changes to the page since it was last published will be permenantly deleted").'</p>
            <p class="instructions">'.$page->drawLabel("tl_paedit_delete_preview", "To preview this panel first").', <a href="'.$panel->drawLink().'?mode=preview" target="_blank">'.$page->drawLabel("tl_generic_click_here", "click here").'</a></p>
            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.$page->drawLabel("tl_paedit_but_reject", "Yes, reject it").'" />
            </fieldset>
        </fieldset>
        </form>
		';
	} 
	else { 
		// page isn't publishable 
		$page_html='
		<p>'.$page->drawLabel("tl_paedit_err_changed", "This panel has not been edited recently and so there are no changes to reject").'</p>
		<p><a href="/treeline/panels/?action=publish">'.$page->drawLabel("tl_paedit_other_publish", "View other publishable panels").'</a></p>
		';
		//$page_html='<h3>'.$panel->getTitle().' '.$page->drawLabel("tl_paedit_not_publish", "is not publishable").'</h3>';
	} 
	echo treelineBox($page_html, $page_title, "blue");
}

else if ($guid && $action=='editrss'){ 
	
	$panel = new Page();
	$panel->loadByGUID($guid);
	$content = new HTMLPlaceholder();
	$content->load($guid,'panelcontent');
	$page_html = '
	    <form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
        <fieldset>
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="guid" value="'.$guid.'" />
            <input type="hidden" name="mode" value="'.$mode.'" />
            <p class="instructions">To edit a new RSS panel, please enter the full address (url) of the feed:</p>
			<label for="title">Title:</label>
			<input type="text" name="title" id="title" value="'.($title?$title:$panel->title).'"/><br />
			<label for="treeline_content">Feed URL:</label>
			<input type="text" name="treeline_panelcontent" id="treeline_panelcontent" value="'.($treeline_panelcontent?$treeline_panelcontent:$content->content).'"/>
            <div id="tagsElement" class="hasHelp">
	';
	//include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php";
	$page_html.=$tags_html;
    $page_html.='
           	</div>
			<fieldset class="buttons">		
            	<input type="submit" class="submit" value="Save" />
            </fieldset>
        </fieldset>
     	</form>
	';
	
		
	$rssFeed = ($treeline_panelcontent)?$treeline_panelcontent:$content->content;
	//print "check feed url($rssFeed)<br>\n";
    $rssData = drawRSSFeed($rssFeed); 
	if ($rssData) {
		//print "got data($rssData)<br>\n";
		$page_html.='<h3>RSS Panel Content preview</h3>'.$rssData;
	}
	else if ($rssFeed) $page_html.='<p>This RSS feed returned no data</p>';
	
	echo treelineBox($page_html, "Edit RSS panel properties", "blue");
}

else if (!$guid || $action=="list") {
	?>
	<h2 class="pagetitle rounded"><?=$page->drawLabel("tl_paedit_find_title", "Please select the panel you would like to edit from the list below")?></h2>
	<?php
	
	if($view == 'map'){ 
		$page_html = ' 
		<p>'.$page->drawLabel("tl_paedit_find_message", "To edit a panel, please select it from the list below below or go back to").' <a href="'.$_SERVER['PHP_SELF'].'?action=edit">'.$page->drawLabel("tl_paedit_form_view", "form view").'</a>.</p>
		'.$treeline->drawEditablePanelsByParent($page->getGUID(), $site->id);
		echo treelineBox($page_html, $page->drawLabel("tl_paedit_find_panel", "Find panel"), "blue");
	}
	else { 
		$page_html = '
        <form id="treeline" action="/treeline/panels/'.($DEBUG?'?debug':"").'" method="post">
        <fieldset>
			<input type="hidden" name="action" value="search" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<input type="hidden" name="mode" value="'.$mode.'" />
			<p class="instructions">'.$page->drawLabel("tl_paedit_panel_nofind", "Cannot find the panel you need? Try").' <a href="'.$_SERVER['PHP_SELF'].'?action=search&amp;view=map">'.$page->drawLabel("tl_paedit_list_view", "List view").'</a></p>
			<div>
				<label for="keywords">'.$page->drawLabel("tl_paedit_field_search", "Search for").': </label>
				<input type="text" name="keywords" id="keywords" value="'.$keywords.'" />
			</div>
			<div>
				<label for="category">'.$page->drawLabel("tl_paedit_field_searchb", "Search by").':</label>
				<select name="category" id="category">
					<option value="">'.$page->drawLabel("tl_generic_select", "Select").'</option>
					<option value="title" '.($category=='title'?'selected="selected"':"").'>'.$page->drawLabel("tl_generic_title", "Title").'</option>
					<option value="content" '.($category=='content'?'selected="selected"':"").'>'.$page->drawLabel("tl_generic_content", "Content").'</option>
				</select>
			</div>
			<input type="hidden" name="findcat" value="1" />
			<fieldset class="buttons">		
				<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_search", "Search").'" />
			</fieldset>
		 </fieldset>
         </form>
		 ';

		echo treelineBox($page_html, $page->drawLabel("tl_paedit_find_panel", "Find panel"), "blue");
		
		if ($action=="search") {
			if(!$keywords && $category){
				echo drawFeedback('error','You need to specify keywords to search with.');
			}
		}

		if( (!$category && !$keywords) || ($category && $keywords) ){
			echo $page->drawPageList($thispage, $action, $category, $keywords, $page->getGUID(), 1, $guid);
		}
	}
}

?>
</div>
</div>

<?php 

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 

?>