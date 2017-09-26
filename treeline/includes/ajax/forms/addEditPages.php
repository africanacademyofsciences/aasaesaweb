<?php
// if edit mode
if($action == 'edit' && !$_POST){
	// Edit mode should have a page's details so display that instead of the generic (so we can use just 1 form)
	$title = ($_POST['title']) ? $_POST['title'] : $newPage->title;
	$parent = $newPage->getSectionByPageGUID($guid);
	$template = ($_POST['template']) ? $_POST['template'] : $newPage->template_id;
	$style = ($_POST['style']) ? $_POST['style'] : $newPage->style_id;
	$tagslist = ($_POST['tagslist']) ? $_POST['tagslist'] : $tags->drawTags($guid);
	$shorturl = ($_POST['shorturl']) ? $_POST['shorturl'] : $newPage->shorturl;
	$hidden = ($_POST['hidden']) ? $_POST['hidden'] : $newPage->hidden;
	$offline = ($_POST['offline']) ? $_POST['offline'] : $newPage->offline;
	$private = ($_POST['private']) ? $_POST['private'] : $newPage->private;
	$comment = ($_POST['comment']) ? $_POST['comment'] : $newPage->comment;
	$meta_desc = ($_POST['description']) ? $_POST['description'] : $newPage->getMetaDescription();
	$news_display = ($_POST['news_display']) ? 1 : $newPage->newsDisplay;
	$page_type = ($_POST['page_type']) ? $_POST['page_type'] : $newPage->type;
	$robots = $_POST?$_POST['robots']:$newPage->robots;

	// Current resources to show.
	$resource_list = ($_POST?$_POST['resource-type']:$result->resource_types);
}


if ($action=="create") { 

} 


?>

<form id="treeline" name="treeline" action="<?=$_SERVER['REQUEST_URI']?><? if ($DEBUG) echo '?debug'?>" method="post">
<fieldset>

    <input type="hidden" name="action" value="<?=$action?>" />
    <input type="hidden" name="guid" value="<?=$guid?>" />
    <input type="hidden" name="post_action" value="" />

	<?php 

	$submit_html='<fieldset class="buttons">';
	if ($action=="edit") { 
		$submit_html.='<input type="button" class="submit" name="button-action" value="'.$page->drawLabel("tl_pedit_but_saveatt", "Save attributes").'" onclick="setAction(\'Save attributes\');" />';
		// If this page is being edited or certain template types we cant edit content....
		if ($template==4);						// Cant edit the news index page
		else if ($template==12);				// Cant edit the member login page
		else if ($template==16);				// Cant edit the resources page
		else if ($template==21);				// Cant edit the forum main page
		else if ($template==28);				// Cant edit the forum media player page
		else if ($template==29);				// Cant edit the blogs index page
		else if ($template==68);				// Cant edit a gallery page
		else if ($page->lockedForEdit($guid));	// Cant edit it if its locked
		else $submit_html.='<input type="submit" class="submit" name="button-action" value="'.$page->drawLabel("tl_pedit_but_saveed", "Save and Edit").'" style="clear:none;margin-left:20px;" />';
	} 
	else { 
		//$submit_html.='<input type="submit" class="cancel" name="button-cancel" value="'.ucfirst($page->drawLabel("tl_generic_cancel", "cancel")).'" />';
		$submit_html.='<input type="submit" class="submit" name="button-action" value="'.ucfirst($page->drawLabel("tl_pedit_but_next", "Next step")).'" style="clear:none;" />';
	}
	$submit_html.='</fieldset>';


	// -------------------------------------------------------
	// Show the page title
    if ($action=='create') { 
        ?><h2 class="pagetitle rounded"><?=$page->drawLabel("tl_generic_step", "Step")?> 2 <?=$page->drawLabel("tl_generic_of", "of")?> 4: <?=$page->drawLabel("tl_pedit_main_title", "Set up the attributes of this page")?>.</h2><?php 
    } 
    else if ($action=='edit') { 
        ?><h2 class="pagetitle rounded"><?=$page->drawLabel("tl_pedit_attib_title", "Edit page attributes for")?> <?=$title?></h2><?php 
    } 



	// -------------------------------------------------------
	// Show a list or available page types in a pretty box.
	// you can only select a page type when you create a new page
	$page_html='';
	if(!$newPage->locked && $action=="create") { 

		$exclude = array("Contact page", "Send page to a friend");
		if (!$site->getConfig("setup_petition")) $exclude[]="Petition";
		
		$box_title=$page->drawLabel("tl_pcreate_title1", 'Choose what type of page you want to create');
		//Create function that gets page template options
		//if ($_SESSION['treeline_user_site_id']==1 && $_SESSION['treeline_user_language']=='en');
		//else $exclude[]="Events page";
		$page_html=$page->drawRadioTemplatesList($exclude, $template);
		if ($page_html) {
			$page_html='<p>'.$page->drawLabel("tl_pcreate_message", "Click the box next to the type of page you want to create. NOTE: You cannot change this later").'</p><div id="dtemplate_holder">'.$page_html.'</div>';
			echo treelineBox($page_html, $box_title, "blue", 0, 0, 90);
		}
	}
	if (!$page_html) {
		?><input type="hidden" name="template" value="<?=$template?>" /><?php
	}
	
	echo '<a name="tagslist" class="tags-jump">&nbsp;</a>';
	
	// -------------------------------------------------------
	// Set up main data for page
	$page_html='';
	$tmp = ($action=="create"?"Choose":"Amend");
	$box_title=$page->drawLabel("tl_pedit_label_".strtolower($tmp), $tmp." names, labels and tags for this page");
	$page_html='
<div>
	<label for="title">'.$page->drawLabel("tl_pedit_field_title", "Page title").$help->drawSmallPopupByID(90).'</label>
	<input type="text" name="title" id="title" value="'.html_entity_decode($title).'" />
</div>
<div>
	<label for="parent">'.$page->drawLabel("tl_pedit_field_site", "Site section").$help->drawSmallPopupByID(90).'</label>
	<select name="parent" id="parent">
		<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
		'.$treeline->drawSelectPagesByParent($site->id,$parent,$site->id,array(4,11,75), array('')).'
	</select><br />
</div>
';
	include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php";
	$page_html.=$tags_html.'
<div>
	<label for="description">'.$page->drawLabel("tl_pedit_field_desc", "Add a description").$help->drawSmallPopupByID(90).'</label>
	<textarea name="description" id="description" cols="30" rows="4">'.($meta_desc?html_entity_decode($meta_desc):"").'</textarea>
</div>
';
	
	// Default content pages to 2 column layout in create mode
	if ($mode=="create") $page_html.='<input type="hidden" name="style" value="2" />';
	
	echo treelineBox($page_html.$submit_html, $box_title, "blue", 0, 0, 90);		


	// *************************************************
	// Set up page specific options
	$page_html='';
	if(!$newPage->locked) {
	
		// -------------------------------------------------------
		// Set up the events form and show it if needed.
		if ($site->getConfig("setup_events")) {
			$page_html=$event->drawForm($_SERVER['REQUEST_METHOD']=='POST'?$_POST:array(), $template);
			echo treelineBox($page_html, $page->drawLabel("tl_pedit_event_title", "Please enter settings for your event"), '', 0, 0, 90, 'event_treelineBox', ($template==19?"":"display:none;"));
		}
		else echo '<div id="event_treelineBox"></div>';
		
		// -------------------------------------------------------
		// Set up the resources form and show it if needed.
		if ($site->getConfig("setup_resources")) {
			$page_html='
			<div>
				<label for="f_resource_type">Show resources'.$help->drawSmallPopupByID(90).'</label>
				'.$resource->drawResourceTypes('resource-list', $meta_desc).'
			</div>
			';
			echo treelineBox($page_html, $page->drawLabel("tl_pedit_resource_title", "Please enter settings for this resources page"), '', 0, 0, 90, 'resource_treelineBox', ($template==16?"":"display:none;"));
		}
		else echo '<div id="resource_treelineBox"></div>';
		
		// -------------------------------------------------------
		// Set up the petition form and show it if needed.
		if ($site->getConfig("setup_petition")) {
			$page_html=$petition->drawForm($_SERVER['REQUEST_METHOD']=='POST'?$_POST:array(), $template);
			echo treelineBox($page_html, $page->drawLabel("tl_pedit_petition_title", "Please enter settings for your petition"), '', 0, 0, 90, 'petition_treelineBox', ($template==22?"":"display:none;"));
		}
		else echo '<div id="petition_treelineBox"></div>';
		
	}
	// *************************************************
    
    
	// -------------------------------------------------------
	// Create the little extras box.
	$page_html='
	<div>
		<label for="shorturl">'.$page->drawLabel("tl_pedit_field_short", "Web shortcut").$help->drawSmallPopupByID(90).'</label>
		<em class="url">http://'.$site->url.'/</em>
		<input type="text" name="shorturl" id="shorturl" value="'.$shorturl.'" />
		<em class="optional">['.$page->drawLabel("tl_generic_optional", "optional").']</em><br />
	</div>
	<div>
		<input type="checkbox" class="checkbox" id="hidden" name="hidden" '.($hidden?' checked="checked"':'').'/>
		<label for="hidden" class="checklabel">'.$page->drawLabel("tl_pedit_field_hide", "Hide this page?").$help->drawSmallPopupByID(90).'</label>
	</div>
	<div>
		<input type="checkbox" class="checkbox" id="f_robots" name="robots" '.($robots?' checked="checked"':'').'/>
		<label for="f_robots" class="checklabel" style="width: 200px;">Hide from search engines?'.$help->drawSmallPopupByID(90).'</label>
	</div>
	<div>
		<input type="checkbox" class="checkbox" id="f_offline" name="offline" '.($offline?' checked="checked"':"").' />
		<label for="f_offline" class="checklabel">'.$page->drawLabel("tl_pedit_field_offline", "Take offline?").$help->drawSmallPopupByID(90).'</label>
	</div>
	';
	if ($site->getConfig('setup_members_area')) { 
		$query = "SELECT id, title FROM member_types ORDER BY sort_order";
		$memberTypeOptions = '';
		if ($results = $db->get_results($query)) {
			foreach($results as $result) {
				$memberTypeOptions.='<option value="'.$result->id.'"'.($result->id==$private?' selected="selected"':"").'>'.$page->drawLabel("tl_memt_".$result->title, $result->title).'</option>';
			}
		}
		$page_html.='
	<div>
		<label for="f_private">'.$page->drawLabel("tl_pedit_field_member", "Members only?").$help->drawSmallPopupByID(90).'</label>
		<select class="checkbox" id="f_private" name="private">
			<option value="0">'.$page->drawTitle("tl_pedit_field_visible", "Visible to all").'</option>
			'.$memberTypeOptions.'
		</select>
	</div>
		';
	}
	if ($site->getConfig('setup_comments')) { $page_html.='
	<div>
		<input type="checkbox" class="checkbox" id="f_comment" name="comment" '.($comment?' checked="checked"':'').' />
		<label for="f_comment" class="checklabel">'.$page->drawLabel("tl_pedit_field_comment", "Allow comments?").$help->drawSmallPopupByID(90).'</label>
	</div>
	';
	}
	$page_html.=$submit_html;
	
	echo treelineBox($page_html, $page->drawLabel("tl_pedit_optional_title", "Optional extras"), '', 0, 0, 90);
	
 	?>
</fieldset>
</form>