<?php

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/media.class.php");	

	$tags = new Tags($site->id, 6);// TAGS
	
	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/");
	$guid = read($_REQUEST,'guid','');

	$feedback = read($_REQUEST,'feedback',"error");
	$message = array();

	$title = read($_POST,'title','');
	$oldtitle = read($_POST,'oldtitle','');
	$description = read($_POST,'description','');
	$code = read($_POST,'code','');
	$shared = read($_POST, "shared", "");
	$resource = read($_POST, "resource", 0);
	$respond = read($_POST, "respond", 0);

	$ssearch = read($_REQUEST, "q", "");
	
	$category = read($_REQUEST,'category','xx');	
	$newcategory = read($_POST,'newcategory','');
	$subcategory=read($_POST, 'subcategory', 'xx');
	$newsubcategory=read($_POST, 'newsubcategory', '');
	//print "got category($category) sub($subcategory)<br>";

	$findcat = read($_POST,'findcat',false);
	
	// Create a new file:
	$media = new media;
	
	// Pagination
	$thispage = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'p',1);
	$media->setPage($thispage);
	
	// ****************************************
	// PROCESSING ANY POST ACTION  ************	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		//print "got post action($action)<br>\n";
		
		// Need to check for tag adding and ensure no other actions are processed
		// We actually add tags in the /treeline/includes/ajax/forms/addEditTags.php file
		if ($_POST['tagaction']) {
			;
		}
		
		// Create a new media
		else if ($action == 'create') {
			if (!$title) $message[] = $page->drawLabel("tl_media_err_title", 'Please enter a title for this media code block');
			else if ($category == 'xx' && $newcategory == '') $message[] = $page->drawLabel("tl_media_err_category", 'Please select a category, or add a new category');
			else {
				$media->setTitle($title);
				$media->setName(generateName($title, "media"));
				if (!$media->getName()) $mssage[] = $page->drawLabel("tl_media_err_exists", "Media with this title already exists");
				else {
					$media->setDescription($description);
					$media->setCode($code);
					$media->setShared($shared);
					$media->setResource($resource);
					$media->setRespond($respond);
					$media->setCategory($category, $newcategory);
					$media->setSubcategory($subcategory, $newsubcategory);
	
					if (!$media->create()) $message[] = "Failed to create your media code block";
					else {	
						// Add tags
						//$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
						//$tags->addTagsToContent($media->getGUID(), str_replace(", ",",",$tagslist));						
	
						$nextsteps='<li><a href="/treeline/media/?action=create">'.$page->drawLabel("tl_media_next_create", "Add another media code block to the library").'</a></li>';
						$action="edit";
						$guid = '';
						unset($category, $subcategory);
					}
				}
			}
		}
		
		// Edit a file in the file or media libraries
		else if ($action == 'edit') {
			if(!$findcat){
				$media->loadByGUID($guid);
				
				// Check we have a title
				// We no longer allow names to be changed on files since
				// it breaks content and messes everything up.
				if (!$title) $message[] = $page->drawLabel("tl_media_err_title", 'Please enter a title for this code block');
				else if ($category == 'xx') $message[] = $page->drawLabel("tl_media_err_category", 'Please select a category, or add a new category');
				else {
					// Set the media data
					if ($title != $oldtitle) {
						$media->setTitle($title);
						$media->setName(generateName($title, "media"));
						if (!$media->getName()) $message[]=$page->drawLabel("tl_media_err_exists", "Media with this title already exists");
					}
					if (!$message) {
						$media->setDescription($description);
						$media->setCode($code);
						$media->setShared($shared);
						$media->setResource($resource);
						$media->setRespond($respond);
						$media->setCategory($category, $newcategory);
						$media->setSubcategory($subcategory, $newsubcategory);
							
						if (!$media->save($name)) $message[] = "Failed to save media code";
						else {		
							// add tags
							//$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
							//$tags->addTagsToContent($guid, str_replace(", ",",",$tagslist));						
							$nextsteps='<li><a href="/treeline/media/?action=create">'.$page->drawLabel("tl_media_next_create", "Add another media code block to the library").'</a></li>';
							$guid='';
							unset($category, $subcategory);
						}
					}
				}
			}
		}
		
		// Actually delete a media block
		else if ($action == 'delete' && !$findcat) {
			if($media->delete($guid)){
				$nextsteps='<li><a href="/treeline/media/?action=create">'.$page->drawLabel("tl_media_next_create", "Add another media code block to the library").'</a></li>';
				// If its shared media we need to inform everybody.
				if ($media->shared) $tasks->notify("shared-delete", array("TYPE"=>"media","NAME"=>$media->getTitle()));
			}
			else $message[] = $page->drawLabel("tl_media_err_delete", 'Media could not be deleted');
			$action="edit";
			$guid="";
		}
		
	}
	// END OF ACTION PROCESSING ************	

	// PAGE specific HTML settings
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

form#treeline {
	float:left;
}

'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = '

var subcats = new Array();

'.$media->drawSubcategories().'

function setSubCategory(id,t) {
	var o = document.getElementById(id).options;
	for(s=0;s<o.length;s++) {
		if (o[s].value==t) o.selectedIndex=s;
	}
}

function fillSubCategories(id,t) {
	// this needs to populate the drop-down list id=[id]
	// with the correct subcategories --
	// probably taken from the database and written out into this function
	var o = document.getElementById(id).options;
	o.length = 1;
	i = 1;
	for (s=0;s<subcats.length;s++) {
		if (subcats[s][1] == t) {
			// if the parent of the item in the array is the same as the value of the item selected from the category <select>
			o[i] = new Option();
			o[i].value = subcats[s][0];
			o[i].text = subcats[s][2];
			i++;
		}
	}
}	

'; 
	//print "Got action($action) guid($guid) media($media:".$file->getMedia().")<br>\n";
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("media", $action);
	$pageClass = 'media';
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
	<?php
    echo drawFeedback($feedback,$message);
    
    if ($nextsteps) echo treelineList($nextsteps, $page->drawLabel("tl_generic_next_steps", "Next steps"), "blue");
  
    /*** DEBUG *****/
    //echo 'POST '.ini_get('post_max_size').'<br />';
    //echo 'UPLOAD '.ini_get('upload_max_filesize');
    if ($action == 'create') { 

        if (($subcategory+0)>0) {				
            $extraBottomJS .= '
            fillSubCategories("subcategory", '.$category.');
            setSubCategory("subcategory", '.$subcategory.');
            ';
        }
  
  		$page_html='
		<form id="treeline" enctype="multipart/form-data" action="/treeline/media/'.($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">'.$page->drawLabel("tl_media_crea_msg1", "To upload a new media block to the library, please complete the form below").'</p>
			<label for="title">'.$page->drawGeneric("title", 1).'</label>
			<input type="text" name="title" id="title" value="'.$title.'" /><br />

			<label for="f_code">'.$page->drawGeneric("code", 1).':</label>
			<textarea name="code" id="f_code">'.$code.'</textarea><br />
			
			<label for="f_description">'.$page->drawGeneric("description", 1).':</label>
			<textarea name="description" id="f_description">'.$description.'</textarea><br />
			';
			
		// Only allow file sharing if the sites has microsites		
		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").'</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';
		
		// Adding tags disabled on file upload as we cannot avoid losing the uploaded file
		include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php"; 
		$page_html.=$tags_html;
		
		$page_html.='            
			<label for="category">'.$page->drawGeneric("category", 1).':</label>
			<select name="category" id="category" onChange="fillSubCategories(\'subcategory\',this.value);">
				<option value="xx">'.$page->drawGeneric("select", 1).'</option>
			  	'.$media->drawCategories($category).'
			</select><br />
			<label for="title"><em style="font-weight: normal; font-style: italic">'.$page->drawLabel("tl_img_field_addcat", "Or add category").'</em>:</label>
			<input type="text" name="newcategory" id="newcategory" value="'.$newcategory.'" />
			<br />
	
			<label for="subcategory">'.$page->drawGeneric("subcategory", 1).':</label>
			<select name="subcategory" id="subcategory">
				<option value="xx">'.$page->drawGeneric("select").'</option>
			</select><br />
			<label for="newsubcategory"><em style="font-weight: normal; font-style: italic">'.$page->drawLabel("tl_img_field_addsubcat", 'Or add subcategory').'</em>:</label>
			<input type="text" class="text" name="newsubcategory" id="newsubcategory" value="'.$newsubcategory.'" /><br />
		';
		
		if ($site->config['setup_resources']) { 
			$page_html.='
				<input type="checkbox" class="checkbox" name="resource"'.($resource?' checked="checked"' : '').' value="1" />
				<label for="resource" class="checklabel" style="width:240px;">'.$page->drawLabel("tl_img_field_showres", "Show in resources section").'</label><br />
			';
		} 

		$page_html.='
			<div class="field">
				<input type="checkbox" id="f_respond" class="checkbox" name="respond"'.($respond?' checked="checked"' : 'checked="checked"').' value="1" />
				<label for="f_respond" class="checklabel" style="width:240px;">'.$page->drawLabel("tl_img_field_respond", "Make responsive").'</label><br />
			</div>
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawGeneric("create", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		
		echo treelineBox($page_html, "Upload new media", "blue");
    }

	else if ($guid && $action=='edit') { 

		$media->loadByGUID($guid);
		if (!$_POST['shared']) $shared=$media->shared;

		$extraBottomJS .= '
			fillSubCategories("subcategory", '.$media->getCatID().');
			setSubCategory("subcategory", '.$media->getSubcatID().');
		';
		
		?><h2 class="pagetitle rounded"><?=$page->drawLabel("tl_media_edit_header", "Modify media attributes")?></h2><?php 		
		
		$page_html = '
		<form id="treeline" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<label for="title">'.$page->drawGeneric("title", 1).':</label>
			<input type="hidden" name="oldtitle" id="oldtitle" value="'.$media->title.'" />
			<input type="text" name="title" id="title" value="'.($title ? $title : $media->title).'"/><br />

			<label for="f_code">'.$page->drawGeneric("code", 1).':</label>
			<textarea name="code" id="f_code">'.($code ? $code : $media->code  ).'</textarea><br />
			
			<label for="description">'.$page->drawGeneric("description", 1).':</label>
			<textarea name="description">'.($description ? $description : $media->description  ).'</textarea><br />
		';

		// Only allow file sharing if the sites has microsites		
		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").':</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';
		
		include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php";
		$page_html.=$tags_html;

		$page_html.='
			<label for="category">'.$page->drawLabel("tl_img_field_movecat", "Move to category").':</label>
			<select name="category" id="category" onChange="fillSubCategories(\'subcategory\',this.value);">
				<option value="xx">'.$page->drawGeneric("select", 1).'</option>
				'.$media->drawCategories(($category!='xx')?$category:$media->catid).'
			</select><br />

			<label for="subcategory">'.$page->drawGeneric("subcategory", 1).':</label>
			<select name="subcategory" id="subcategory">
				<option value="xx">'.$page->drawGeneric("select", 1).'</option>
			</select><br />
		';

		if ($site->config['setup_resources']) { 
			$page_html.='
				<input type="checkbox" class="checkbox" id="f_resources" name="resource" '.(($_POST?$resource:$media->resource)?' checked="checked"' : '').' value="1" />
				<label for="f_resources" class="checklabel" style="width:240px;">'.$page->drawLabel("tl_img_field_showres", "Show in resources section").'</label><br />
			';
		} 
		
		$page_html .= '
			<div class="field">
				<input type="checkbox" id="f_respond" class="checkbox" name="respond"'.(($_POST?$respond:$media->respond)?' checked="checked"' : '').' value="1" />
				<label for="f_respond" class="checklabel" style="width:240px;">'.$page->drawLabel("tl_img_field_respond", "Make responsive").'</label><br />
			</div>
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		
		echo treelineBox($page_html, $page->drawLabel("tl_media_edit_title", "Edit media")." : ".$media->title, "blue");
    }
	else if ($guid && $action == 'delete') {
	
		$media->loadByGUID($guid);
		
		$page_html = '
        <form id="treeline" enctype="" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			'.($media->shared?'<p class="instructions">'.$page->drawLabel("tl_media_del_shared", "You are about to delete a shared media block. Please note that this action could result in broken links on other sites that use this shared resource").'</p>':"").'
            <p>'.$page->drawLabel("tl_media_del_msg1", "You are about to delete this media, are you sure?").'</strong></p>
            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.$page->drawGeneric("delete", 1).'" />
            </fieldset>
        </fieldset>
    	</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_media_del_title", "Confirm media delete")." : ".$media->title, "blue");
 	}
	// If we didnt find anything to do and we dont have a guid passed then just show selectable files.
	else if ( !$guid ) {

		?><h2 class="pagetitle rounded"><?=$page->drawLabel("tl_media_list_header", "Search for media to manage")?></h2><?php 		
		
		$page_html = '
			<p><a href="/treeline/googlemap/">'.$page->drawLabel("tl_media_list_google", "Manage google maps").'</a></p>
          	<form id="treeline" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
            <fieldset>
                <input type="hidden" name="action" value="'.$action.'" />
                <input type="hidden" name="guid" value="'.$guid.'" />

                <label for="ssearch">'.$page->drawGeneric("keywords", 1).':</label>
                <input type="text" class="text" name="q" id="f_ssearch" value="'.$ssearch.'" />

                <label for="category">'.$page->drawGeneric("category", 1).':</label>
                <select name="category" id="category">
                	<option value="xx">'.$page->drawGeneric("select", 1).'</option>
                  	'.$media->drawCategories($category).'
                </select><br />
                <input type="hidden" name="findcat" value="1" />
                <fieldset class="buttons">
                    <input type="submit" class="submit" value="'.$page->drawGeneric("search", 1).'">
                </fieldset>
            </fieldset>
          	</form>
 		';
		echo treelineBox($page_html, $page->drawLabel("tl_media_list_title", "Filter by category or select media from the list below below"), "blue");
		
		echo $media->drawList($thispage, $action, ($category=='xx')?'':$category, $ssearch);
	}

	// Erm, got a guid and action but didnt find anything to process it???	
	else {
		print "eek, got guid($guid) and action($action) but could not process<br>\n";
		?><p>Please go back and try again.</p><?php 
	}

	?>
</div>
</div>

<?php 
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>