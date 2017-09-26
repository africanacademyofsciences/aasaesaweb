<?php

	ini_set("display_errors", "yes");
//error_reporting(E_ALL);


	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline_object.class.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/galleries/includes/gallery.class.php");
	
	$tags = new Tags($site->id, 5);// TAGS
	
	$gallery = new TO_Gallery();
	$gallery->set("msv", $_SESSION['treeline_user_site_id']);
	
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	$search = read($_REQUEST,'q',NULL);
	$status = read($_REQUEST,'status','all');
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = 20;

	$action = read($_GET, 'action', null);
	$gallery_id = read($_REQUEST, 'id', null);
	
	$q = $db->escape($_REQUEST['q']);
	$gtype = $db->escape($_REQUEST['type']);

	$nextsteps='';
	
	// All images uploaded return to organise images page.
	if  (strtolower($_GET['uploader']) == 'finished')
	{
		$nextsteps.='<li><a href="/treeline/galleries/?action=uploader&id='.$_GET['id'].'">'.$page->drawLabel("tl_gall_next_upload", "Upload some more images to this slideshow").'</a></li>';
		$nextsteps.='<li><a href="/treeline/galleries/?action=organise&id='.$_GET['id'].'">'.$page->drawLabel("tl_gall_next_organise", "Organise images in this slideshow").'</a></li>';
		$nextsteps.='<li><a href="/treeline/galleries/?action=edit&id='.$_GET['id'].'">'.$page->drawLabel("tl_gall_next_edit", "Edit slideshow details").'</a></li>';
		$nextsteps.='<li><a href="/treeline/galleries/?action=create">'.$page->drawLabel("tl_gall_next_create", "Create a new slideshow").'</a></li>';
		//$nextsteps.='<li><a href="/treeline/galleries/?action=uploader&id='.$_GET['id'].'">Upload some more images to this slideshow</a></li>';
		//$nextsteps.='<li><a href="/treeline/galleries/?action=organise&id='.$_GET['id'].'">Organise images in this slideshow</a></li>';
		//$nextsteps.='<li><a href="/treeline/galleries/?action=edit&id='.$_GET['id'].'">Edit slideshow details</a></li>';
		//$nextsteps.='<li><a href="/treeline/galleries/?action=create">Create a new slideshow</a></li>';
		$action='';
	}
	
	
	// If any of these important actions
	else if ($gallery_id && ($action == 'upload' || $action == 'organise' || $action == 'edit' || $action == 'delete'))
	{
		//print "load gallery $action<br>\n"; exit();
		// Try to load the gallery in question
		$loaded = $gallery->load($gallery_id);
		
		//print "load gallery($gallery_id) tags (".$tags->drawTags($gallery_id, 'list', 4).")<br>";
		$gallery->tags=$tags->drawTags($gallery_id, "list");
		
		if ($action == 'delete') 
		{
			$gallery->delete();
			$action="";
		}
		else if ($action == "organise" && $_SERVER['REQUEST_METHOD']=="POST") {
			$images = $_POST['gi'];
			// Because of the way radio buttons work, we have to do this:
			$images[$_POST['main_gallery_image']]['main_gallery_image'] = true;
			
			$gallery->set_images($images);
			$gallery->update_images();
			
			$nextsteps.='<li><a href="/treeline/galleries/?action=uploader&id='.$gallery->get("id").'">'.$page->drawLabel("tl_gall_next_upload", "Upload some more images to this slideshow").'</a></li>';
			$nextsteps.='<li><a href="/treeline/galleries/?action=edit&id='.$gallery->get("id").'">'.$page->drawLabel("tl_gall_next_edit", "Edit slideshow details").'</a></li>';
			$nextsteps.='<li><a href="/treeline/galleries/?action=create">'.$page->drawLabel("tl_gall_next_create", "Create a new slideshow").'</a></li>';
			$action="";
		}		
		else if (!$loaded) $message[]=$page->drawLabel("tl_gall_err_loadfile", "Failed to load slideshow");
	}
	
	// Create a new gallery.
	else if ($action=="create") {
		$pageClass="create-content";
	}
	
	
	// PAGE specific HTML settings
	$css = array('forms','tables', 'galleries'); // all CSS needed by this page

	$extraCSS = '	
div.gimg {
	clear:both;
	padding-top:2em
}
div.gimg img {
	float:left;
	margin-right:15px;
	border:1px solid #dce1e1;
	padding:2px
}
div.fields p.sort_order input {
	width:auto
}
div.fields label {
	width:100px
}
div.fields {
	float:left
}
div.fields * {
	padding:0;
	margin:0 0 .6em 0;
	font-size:11px
}
div.fields input, div.fields textarea {
	padding:2px
}
div.fields input.checkbox {
	margin-left:100px;
	font-size:110% !important
}
#gallery_form p {
	clear:both
}
form fieldset fieldset.wider {
	padding-left: 215px;
}
';


	$js = array(); // all external JavaScript needed by this page
	$extraJS = '

	'; // extra on page JavaScript

	if ($action=="uploader") {
		$js[]="swfupload";
		$js[]="swfsetup";
		$extraJS = '
		var completed = "'.$page->drawLabel("tl_gall_upl_completed", "Completed").'";
		var upload_file = "'.$page->drawLabel("tl_gall_upl_upload", "Upload file").'";
		';
		$extraOnloadJS.=' 
swfu = new SWFUpload(swfsetup('.$gallery_id.', \'/treeline/galleries/upload_process\')); 
';
	}
	
	// Page title	
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("galleries", $action);
	if (!$pageClass) $pageClass = 'edit-content';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
<div id="primary_inner">

<?php
$total = 0;
$results = null;


if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");
// --------------------------------------------------------
// No action required so show a list of available galleries
// --------------------------------------------------------
if (!$action || $action=="delete") {

	echo drawFeedback($feedback,$message);
	
	$page_html='

	<form id="filterForm" action="/treeline/galleries/" method="post">
	<fieldset>
		<p><a href="/treeline/galleries/?action=create">'.$page->drawLabel("tl_gall_find_create", "Create a new slideshow").'</a></p>
		<p>
			<label for="q">'.$page->drawLabel("tl_gall_field_search", "Search by title").':</label>
			<input type="text" name="q" id="q" value="'.$search.'" /><br />
		</p>
		<p style="clear:both">
		'.$gallery->draw_gallery_types_select_box($gtype).'</p>
		<fieldset class="buttons">
			<input type="submit" class="submit" name="submitFilter" value="'.$page->drawGeneric("filter", 1).'" />
		</fieldset>
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_gall_find_title", "Search for slideshows"), "blue");
	
	if ($gtype) $filter = "AND type = '$gtype'";
	$query = "SELECT g.*, count(gi.gallery_id) as count 
		FROM galleries g
		LEFT JOIN gallery_images gi ON g.id=gi.gallery_id
		WHERE msv=".$gallery->get('msv')."
		AND g.title LIKE '%{$q}%'
		$filter
		GROUP BY g.id
		ORDER BY title ASC
		";
	//print "$query<br>";
	$results = @$db->get_results("$query LIMIT ".getQueryLimits($perPage, $currentPage), ARRAY_A);
	
	$total = @$db->query($query);
	$total = $db->num_rows;
	
	if ($results) { // results exists
		?>
		<table class="tl_list">
		<caption><?php echo getShowingXofX($perPage, $currentPage, sizeof($results), $total); ?> <?=$page->drawGeneric("slideshows", 1)?></caption>
		<thead>
		<tr>
            <th scope="col"><?=$page->drawGeneric("title", 1)?></th>
            <th scope="col"><?=$page->drawGeneric("status", 1)?></th>
            <th scope="col"><?=$page->drawGeneric("count", 1)?></th>
            <th scope="col"><?=$page->drawLabel("tl_gall_find_manage", "Manage slideshow")?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($results as $result) { // loop through and show results
			$status="Pending";
			if ($result['live']==1) $status="Live";
			?>
            <tr>
            <td><?=$result['title']?></td>
            <td><?=$page->drawGeneric(strtolower($status), 1)?></td>
            <td><?=($result['count']+0)?></td>
            <td class="action">
            	<a class="reject" <?=$help->drawInfoPopup($page->drawLabel("tl_gall_act_upload", "Upload images"))?> href="?action=uploader&amp;id=<?=$result['id']?>">Upload images</a>
				<a class="preview" <?=$help->drawInfoPopup($page->drawLabel("tl_gall_act_organise", "Organise images"))?> href="?action=organise&amp;id=<?=$result['id']?>">Organise/delete images</a> 
                <a class="edit" <?=$help->drawInfoPopup($page->drawLabel("tl_gall_act_edit", "Edit slideshow detail"))?> href="?action=edit&amp;id=<?=$result['id']?>">Edit details</a>
                <a class="delete" <?=$help->drawInfoPopup($page->drawLabel("tl_gall_act_delete", "Delete this slideshow"))?> href="?action=delete&amp;id=<?=$result['id']?>" onclick="return confirm('Are you sure you want to delete this slideshow AND all its associated images?')">Delete this gallery</a>
			</td>
            </tr>
			<?php
        }
		?>	
        </tbody>
        </table>
        <?php
		echo drawPagination($total, $perPage, $currentPage);
	}
	else
	{
		?><p><?=$page->drawLabel("tl_gall_err_noslide", "No slideshows found")?></p><?php
	}

}
// --------------------------------------------------------
// Are we creating or editing a gallery?
// --------------------------------------------------------
else if ($action == 'create' || ($action == 'edit' && $gallery_id)) {

	// We have just added a new tag or removed a tag so we dont need to process the form just
	if ($_POST['tagaction']) {
		$form = $_POST;
	}
	// Form has been posted try to save or create the gallery
	else if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$gallery->set_fields($_POST);			
		$gallery->set('live', (int)$_POST['live']);
		$gallery->set('memberonly', (int)$_POST['memberonly']);
		$gallery->set("sort_order", $_POST['sort_order']+0);
		$form = $_POST;
		if ($action=="create" && !$gallery->get("date_created")) {
			$gallery->set("date_created", date("Y-m-d", time()));
			$gallery->set("guid", uniqid());
		}
	
		if (strlen($form['title']) < 2)	$errors[] = $page->drawLabel("tl_gall_err_title", 'Please specify a gallery title');
		if (!$form['description'])		$errors[] = $page->drawLabel("tl_gall_err_desc", 'Please enter a description');
		
		if ($errors) echo drawFeedback('error', $errors);
		else {
		
			// If we are attempting to set this gallery live then make sure it has a main image id......?
			if ($gallery->get("live")>0 && $gallery->get("main_image_id")<1) {
				$main_image_id=$db->get_var("select id from gallery_images where gallery_id=".$gallery->get("id")." limit 1")+0;
				$gallery->set("main_image_id", $main_image_id);
			}
			$gallery->save();

			$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
			$tags->addTagsToContent($gallery->get("guid"), str_replace(", ",",",$tagslist));						

			$nextsteps.='<li><a href="/treeline/galleries/?action=uploader&amp;id='.$gallery->get("id").'">'.$page->drawLabel("tl_gall_next_upload", "Upload some images to this slideshow").'</a></li>';
			if ($action=="create") {
				$nextsteps.='<li><a href="/treeline/galleries/?action=create">'.$page->drawLabel("tl_gall_next_create", "Create a new slideshow").'</a></li>';
				$nextsteps.='<li><a href="/treeline/galleries/">'.$page->drawLabel("tl_gall_next_manage", "Manage slideshows").'</a></li>';
			}
			else {
				$nextsteps.='<li><a href="/treeline/galleries/?action=organise&amp;id='.$gallery->get("id").'">'.$page->drawLabel("tl_gall_next_organise", "Organise images in this slideshow").'</a></li>';
				$nextsteps.='<li><a href="/treeline/galleries/">'.$page->drawLabel("tl_gall_next_manage", "Manage slideshows").'</a></li>';
			}
			
		}
			
	}
	// We are editing the gallery and have not yet posted any data so retrieve the 
	// gallery data from the loaded gallery
	else if ($gallery_id) $form = $gallery->get_fields();

	//print "loaded form(".print_r($form, 1).")<br>\n";
	$guid = $form['guid'];
	
	if ($nextsteps) echo treelineList($nextsteps, $page->drawGeneric("next_steps", 1), "blue");
	else {
		?>
		<h2 class="pagetitle rounded"><?=($action=='create'?$page->drawLabel("tl_gall_crea_header", 'Create a new slideshow'):$page->drawLabel("tl_gall_edit_title", 'Edit slideshow attributes'))?></h2>
		<?php
		
		$page_html = '
		<form action="/treeline/galleries/?action='.$action.'" method="post" id="gallery_form">
		<fieldset>
			<div class="field">
				<label for="title">'.$page->drawGeneric("title", 1).':</label>
				<input type="text" size="40" maxlength="100" name="title" id="title" value="'.$form['title'].'" />
			</div>
			'.$gallery->draw_gallery_types_select_box().'
			<div class="field">
				<label for="description">'.$page->drawGeneric("description", 1).':</label>
				<textarea rows="5" cols="40" name="description" id="description">'.$form['description'].'</textarea>
			</div>
			<div class="field">
				<label for="f_pageguid">'.$page->drawGeneric("page", 1).':</label>
				'.$gallery->drawSelectPageList().'
			</div>
		';
		include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php";
		$page_html.=$tags_html;

		$page_html.='
			<div class="field">
				<label for="members-only">'.$page->drawLabel("tl_gall_crea_member", "Members only").':</label>
				<input type="checkbox" class="checkbox" name="memberonly" id="members-only" value="1" '.($form['memberonly']?' checked="checked"':'').' style="margin-left: 0px; clear:none;" />
			</div>
		';
	

		// --------------------------------------------------------------
		// Set gallery sort order and status.
		// If we are editing a gallery allow the option to switch it on/change its order
		if ($action == 'edit') { 
			$page_html.='
			<p>
				<label for="sort_order">'.$page->drawGeneric("sort_order", 1).':</label>
				<input type="text" size="4" maxlength="10" name="sort_order" id="sort_order" value="'.$form['sort_order'].'" style="width:50px" />
			</p>
			<p>
			';
			// We only allow publishers and superusers to switch galleries on/off
			if ($user->drawGroup()!="Author") $page_html.='
				<label for="sort_order">'.$page->drawGeneric("status", 1).':</label>
				<input type="checkbox" class="checkbox" name="live" id="live" value="1" '.($form['live']?' checked="checked"':'').' style="margin-left: 0px; clear:none;" />
				<label for="live" class="checklabel">'.($form['live']?$page->drawGeneric("live", 1):$page->drawLabel("tl_gall_crea_status", "Make live")).'</label>
				';
			else $page_html.='
				<label for="sort_order" style="margin:0;">Status</label>
				This gallery is currently '.($form['live']?$page->drawGeneric("live"):$page->drawGeneric("offline")).'
				';
			$page_html.='</p>';
		}
		// Create the gallery with default order and not live. 
		else { 
			$page_html.='
			<input type="hidden" name="sort_order" value="'.$db->get_var("SELECT MAX(sort_order)+1 FROM galleries").'" />
			<input type="hidden" name="live" value="0" />
			';
		} 
		// --------------------------------------------------------------
	
		$page_html.='            
			<fieldset class="buttons">
				<input type="hidden" name="id" value="'.$gallery->get('id').'" />
				<input type="submit" class="submit" value="'.$page->drawGeneric('save', 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_gall_crea_title", "Enter slideshow details"), "blue");
	}
}
// --------------------------------------------------------
// The frilly new uploader way of doing things ....
// --------------------------------------------------------
else if ($action == 'uploader') {
	// Check all directories exist and are writeable
	$gallery_dir = $_SERVER['DOCUMENT_ROOT'].'/silo/images/galleries';
	$fp = substr(sprintf('%o', fileperms($gallery_dir)), -4);
	if ($fp=="0777") {
		$gallery_dir .= '/'.$gallery_id;
		//print "check gallery dir($gallery_dir)<br>\n";
		if (!file_exists($gallery_dir))
		{
			@mkdir($gallery_dir);
			@chmod($gallery_dir, 0755);
		}
		if (file_exists($gallery_dir)) {
		
			$page_html = '
			<!-- Multiple uploader form -->
			<div id="uploader">
				<div id="upload-info"></div>
				<div id="upload-buttons">
					<div id="upload-button" class="uploader-button"></div>
					<button onclick="javascript:swfCancelUpload();" id="upload-cancel" class="uploader-button cancel" style="margin-left: 20px;">'.$page->drawGeneric("cancel", 1).'</button>
					<div id="progress-bar"><span id="progress-span"></span></div>
				</div>
			</div>
			<!-- // End of multiple uploader html -->
			';
			
			echo treelineBox($page_html, $page->drawLabel("tl_gall_upl_title", "Select images to upload"), "blue");
			?>
			<button class="submit" name="finished" id="b_finished" onclick="location='/treeline/galleries/?uploader=finished&id=<?=$gallery_id?>';" style="float: right;"><?=$page->drawGeneric("finished", 1)?></button>
			<?php
		}	
		else print $page->drawLabel("tl_gall_err_createdir", "Failed to create gallery dir, cannot upload images")."<br>\n";
	}
	else print $page->drawLabel("tl_gall_err_uplperm" ,"gallery dir has incorrect permissions set you will not be able to upload")."<br>\n";
	
}
// --------------------------------------------------------
// Organise gallery images
// --------------------------------------------------------
else if ($action == 'organise') {

	$p = ($_SERVER['REQUEST_METHOD'] == 'POST');
	$images = $gallery->get_images();
	if ($images) {
		$page_html.='
		<form action="" method="post">
		<fieldset style="width:90%">
		';
		$main_gallery_image	= ($p) ? $_POST['main_gallery_image'] : $gallery->get('main_image_id');
		// No main gallery image set, use the first one in the gallery
		if (!$main_gallery_image) $main_gallery_image = $images[0]['id'];
		
		$i=0;
		foreach ($images as $f) {

			$i++;
			$u = $f['id'];
			$img_id			= $f['id'];
			$img_title		= ($p) ? $_POST['title'][$u]		: htmlentities($f['title']);
			$img_credit		= ($p) ? $_POST['credit'][$u]		: htmlentities($f['credit']);
			$img_description= ($p) ? $_POST['description'][$u]	: htmlentities($f['description']);
			$img_sort_order	= ($p) ? $_POST['sort_order'][$u]	: htmlentities($f['sort_order']);
			$img_sort_order	= (!$img_sort_order) ? $i : $img_sort_order;
			$img_name = $u.'.'.$f['image_extension'];
			$page_html.='	
			<div class="gimg">
				<img src="/silo/images/galleries/'.$gallery->get('id').'/t_'.$img_name.'" alt="'.$img_title.'" />
				<div class="fields">
					<input type="hidden" name="gi['.$u.'][id]" value="'.$u.'" />
					<p class="sort_order">
						<label for="s'.$u.'">'.$page->drawGeneric("sort_order", 1).'</label>
						<input type="text" name="gi['.$u.'][sort_order]" id="s'.$u.'" size="4" maxlength="10" value="'.$img_sort_order.'" />
					</p>
					<p style="clear:both">
						<label for="t'.$u.'">'.$page->drawGeneric("title", 1).'</label>
						<input type="text" name="gi['.$u.'][title]" id="t'.$u.'" size="20" maxlength="255" value="'.$img_title.'" />
					</p>
					<p>
						<label for="c'.$u.'">'.$page->drawGeneric("credit", 1).'</label>
						<input type="text" name="gi['.$u.'][credit]" id="c'.$u.'" size="20" maxlength="255" value="'.$img_credit.'" />
					</p>
					<p>
						<label for="d'.$u.'">'.$page->drawGeneric("description", 1).'</label>
						<textarea rows="3" cols="60" name="gi['.$u.'][description]" id="d'.$u.'">'.$img_description.'</textarea>
					</p>
					<p>
						<input type="checkbox" name="gi['.$u.'][marked_for_deletion]" id="m'.$u.'" value="1" class="checkbox" />
						<label for="m'.$u.'" class="checklabel">'.$page->drawGeneric("delete", 1).'</label>
					</p>
					<p>
						<input type="radio" name="main_gallery_image" id="g'.$u.'" value="'.$u.'" class="checkbox"
						'.(($img_id == $main_gallery_image) ? 'checked="checked"' : '').' />
						<label for="g'.$u.'" class="checklabel">'.$page->drawLabel("tl_gall_org_mainimg", "Main slideshow image").'</label>
					</p>
				</div>
			</div>
			';
		} 
		
		$page_html.='
			<fieldset class="buttons wider">
				<br />
				<input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_gall_org_title", "Organise this slideshow"), "blue");
	}
	else {
		$page_html = '
			<p>There are no images in this slideshow</p>
			<ul>
				<li><a href="/treeline/galleries/?action=uploader&amp;id='.$gallery_id.'">Upload some images</li>
			</ul>
		';
		echo treelineBox($page_html, "Next steps", "blue");
	}
}
// This should never happen but would indicate its been called with
// an invalid action parameter
else {
	redirect('/treeline/galleries/');
}
?>
		  
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>
	 