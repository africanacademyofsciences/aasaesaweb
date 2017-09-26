<?php

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/file.class.php");	

	$tags = new Tags($site->id, 3);// TAGS
	
	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/");
	$guid = read($_REQUEST,'guid','');


	// Is this upload part of the page edit process or within Treeline
	$inline = read($_REQUEST, "inline", 0);
	//print "Got action($action) guid($guid)<br>\n";

	//print "max(".ini_get("post_max_size").")<br>";
		
	$feedback = read($_REQUEST,'feedback','error');	
	$message = array();
	
	$title = read($_POST,'title','');
	$oldtitle = read($_POST,'oldtitle','');
	$description = read($_POST,'description','');
	$shared = read($_POST, "shared", "");

	$ssearch = read($_REQUEST, "q", "");
	
	$category = read($_REQUEST,'category','xx');	
	$newcategory = read($_POST,'newcategory','');
	$subcategory=read($_POST, 'subcategory', 'xx');
	$newsubcategory=read($_POST, 'newsubcategory', '');
	//print "got category($category) sub($subcategory)<br>";

	//$resource = read($_POST,'resource',0);
	//$resource = ($resource=='on') ? 1 : 0;	// This sets resource to 1 when resource == 0???
	$resource = 0;
	if ($_POST['resource']=="on") $resource=1;
	
	$findcat = read($_POST,'findcat',false);
	
	
	// Create a new file:
	$file = new File;
	$file->site_id=$site->id;
	
	$thispage = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'p',1);
	$file->setPage($thispage);
	
	// ****************************************
	// PROCESSING ANY POST ACTION  ************	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		// Need to check for tag adding and ensure no other actions are processed
		// We actually add tags in the /treeline/includes/ajax/forms/addEditTags.php file
		if ($_POST['tagaction']) {
			;
		}
		
		// Create a new file 
		// Need to copy the file and add it to the database
		// Optionally uploading an image to go with the file.
		else if ($action == 'create') {
			if (!$title) $message[] = $page->drawLabel("tl_file_err_title", 'Please enter a title for this file');
			else if (strlen($title)>99) $message[] = $page->drawLabel("tl_file_err_long", 'Your filename is too long. Please enter a title less than 100 characters');
			else if ($category == 'xx' && $newcategory == '') $message[] = $page->drawLabel("tl_file_err_cat", 'Please select a category for this file, or add a new category');
			else {
				$file->setTitle($title);
				$file->setDescription($description);
				$file->setShared($shared);
				$name = $file->generateName();
				if (!$name) {
					$message[] = $page->drawLabel("tl_file_err_exists", 'A file with that name already exists in the library');
				}	
				else {
					if ($category == 'xx') {
						$categoryOK = $file->setCategory($newcategory);
					}
					else {
						$file->catid=$category;
						$categoryOK = true;
					}
					
					// Check that we're not creating a duplicate category					
					if (!$categoryOK) $message[] = $page->drawLabel("tl_file_err_catexist", 'A category with that name already exists');
					else {

						if ($subcategory>0) $file->subcatid=$subcategory;
						else $file->setSubcategory($newsubcategory);

						// Now -- take the file that's been uploaded, check it's there, set the original name, filesize and mime
						$upload = read($_FILES,'file',false);
						if (!$upload) {
							$message[] = $page->drawLabel("tl_file_err_problem", 'There was a problem uploading your file. It may be too large');
						}
						else if ($error = $file->getUploadError($upload['error'])) {
							$message[] = $page->drawLabel("tl_".str_replace(" ", "-", substr($error, 0, 20)), $error); 
						}
						else {
							if (!$file->setType($upload['type'], substr($upload['name'], -3, 3))) {
								//print_r($upload);
								$message[] = $page->drawLabel("tl_file_err_type", 'Files of that type are not allowed').'['.$upload['type'].']';
							}
							else {
								
								if (!$file->setSize($upload['size'])) {
									$message[] = $page->drawLabel("tl_file_err_size", 'Files of that size are not allowed');
								}
								else {

									// If we were sent a file then upload it...
									$upload_img = read($_FILES,'image_file',false);
									if ($upload_img['name']) {
										$editImage = new Image();
										// We have to send the category names as image categories will be different from file categories
										$query="select title from filecategories where id=".$file->catid;
										//print "$query<br>";
										$cat_title=$db->get_var($query);
										$query="select title from filecategories where id=".$file->subcatid;
										//print "$query<br>";
										$subcat_title=$db->get_var($query);
										$message = $editImage->uploadImage($upload_img, $title, '', '', 0, 0, 0, $cat_title, $subcat_title, 0, true);
										if ($message==1) {
											$file->img_guid=$editImage->master_guid;
											$message=''; // Clear upload success flag....
										}
									}

									if (!$message) {
										if ($error = $file->write($upload['tmp_name'])) {
											$message[] = $page->drawLabel("tl_".str_replace(" ", "-", substr($error, 0, 20)), $error); 
										}
										else {
	
	
											// This updates the database: And does what?
											if( in_array($file->extension, array('doc','pdf') ) ){
												$fileloc = $_SERVER['DOCUMENT_ROOT'] .'/silo/files/'. $file->name.'.'.$file->extension;
												if ($file->size<500000) {
													$content = $file->generateKeywords($fileloc);
													$file->setContent( $content );
												}
											}
											
											$file->setResource($resource);
											$file->create();
											
											// Add tags
											// Tags cannot be added when you upload a file
											//$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
											//$tags->addTagsToContent($guid, str_replace(", ",",",$tagslist));						
											
											$nextsteps='<li><a href="/treeline/files/?action=create">'.$page->drawLabel("tl_file_next_create", "Add another new file to the library").'</a></li>';
											$action="edit";
											$guid = '';
											unset($category);
										}
									}
								}
							}
						}					
					}
				}
			}
		}
		
		// Edit a file in the file library
		else if ($action == 'edit') {
			if(!$findcat){
				$editFile = new File();
				$editFile->loadFileByGUID($guid);
				
				// Check we have a title
				// We no longer allow names to be changed on files since
				// it breaks content and messes everything up.
				if (!$title) $message[] = $page->drawLabel("tl_file_err_title", 'Please enter a title for this file');
				else if (strlen($title)>99) $message[] = $page->drawLabel("tl_file_err_long", 'Your filename is too long. Please enter a title less than 100 characters');
				else {
					// Set the file category
					if($newcategory){
						$categoryOK = $editFile->setCategory($newcategory);
					}
					else if($category != 'xx') {
						$editFile->catid=$category;
						$categoryOK = true;
					}
					else{
						$categoryOK = false;
					}
					
					if (!$categoryOK) {
						// Check that we're not creating a duplicate category
						$message = 'A category with that name already exists';
					}else{
						if ($subcategory>0) {
							$editFile->subcatid=$subcategory;
						}
						
						$editFile->setTitle($title);
						if( $title!=$oldtitle ){
							$name=$editFile->getName();
							$editFile->generateName();
						}
						$editFile->setDescription($description);
						$editFile->setShared($shared);
						
						// If we were sent a file then upload it...
						$upload_img = read($_FILES,'image_file',false);
						if ($upload_img['name']) {				
							$editImage = new Image();
							// We have to send the category names as image categories will be different from file categories
							$query="select title from filecategories where id=$category";
							//print "$query<br>";
							$cat_title=$db->get_var($query);
							$query="select title from filecategories where id=$subcategory";
							//print "$query<br>";
							$subcat_title=$db->get_var($query);
							$message = $editImage->uploadImage($upload_img, $title, '', '', 0, 0, 0, $cat_title, $subcat_title, 0, true);
							if ($message==1) {
								$editFile->img_guid=$editImage->master_guid;
								$message=''; // Clear upload success flag....
							}
						}
						
						if (!$message) {
							$editFile->setResource($resource);	
							//print "set resoruce to ($resource)<br>";
							$editFile->save($name);
							
							// add tags
							$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
							$tags->addTagsToContent($guid, str_replace(", ",",",$tagslist));						

							$nextsteps='<li><a href="/treeline/files/?action=create">'.$page->drawLabel("tl_file_next_create", "Add a new file to the library").'</a></li>';
							$guid='';
							unset($category);
						}
					}
				}
			}
		}
		
		// Actually delete a file
		else if ($action == 'delete') {
			if(!$findcat){
				if($file->delete($guid)){
					//redirect('/treeline/?section=delete&'.createFeedbackURL('success','Your file has been deleted'));
					$nextsteps='<li><a href="/treeline/files/?action=create">'.$page->drawLabel("tl_file_next_create", "Create a new file").'</a></li>';
					// If its a shared file we need to inform everybody.
					if ($file->shared) {
						//print "that was a shared image, need to tell everybody";
						$tasks->notify("shared-delete", array("TYPE"=>"file","NAME"=>$file->title));
					}
					$action="edit";
					$guid="";
					
				}
				else $message[] = $page->drawLabel("tl_file_del_err", 'Your file could not be deleted');
			}
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

'.$file->drawSubcategories().'

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

'; // extra on page JavaScript
	//print "Got action($action) guid($guid)<br>\n";
	
	// Page title	
	$pageTitleH2 =  ucfirst($page->drawLabel("tl_generic_files", 'Files'));
	$pageTitleH2 .= ($action)?' : '.$page->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action))):'';
	$pageTitle = $pageTitleH2;
	
	$pageClass = 'files';
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
	<?php
    echo drawFeedback($feedback,$message);
    
    if ($nextsteps) echo treelineList($nextsteps, $page->drawLabel("tl_generic_next_steps", "Next steps"), "blue");
  
    if ($action=="create") { 

        if (($subcategory+0)>0) {				
            $extraBottomJS .= '
            fillSubCategories("subcategory", '.$category.');
            setSubCategory("subcategory", '.$subcategory.');
            ';
        }
  
  		$page_html='
		<form id="treeline" enctype="multipart/form-data" action="/treeline/files/'.($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="MAX_FILE_SIZE" value="80000000" />
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">'.$page->drawLabel("tl_file_crea_msg1", "To upload a new file to the library, please complete the form below").'</p>
			<label for="file">'.$page->drawLabel("tl_file_crea_select", "Select a file").':</label>
			<input type="file" name="file" id="file" /><br />
			';
		$page_html.='			
			<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
			<input type="text" name="title" id="title" value="'.$title.'" /><br />

			<label for="description">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
			<textarea name="description">'.$description.'</textarea><br />
			';
		// Only allow file sharing if the sites has microsites		
		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").':</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';
		
		// Adding tags disabled on file upload as we cannot avoid losing the uploaded file
		//include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php"; 
		
		$page_html.='            
			<label for="category">'.ucfirst($page->drawLabel("tl_generic_category", "Category")).':</label>
			<select name="category" id="category" onChange="fillSubCategories(\'subcategory\',this.value);">
				<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
			  	'.$file->drawCategories($category).'
			</select><br />
			<label for="title"><em style="font-weight: normal; font-style: italic">'.$page->drawLabel("tl_img_field_addcat", "Or add category").'</em>:</label>
			<input type="text" name="newcategory" id="newcategory" value="'.$newcategory.'" />
			<br />
			';
		$page_html.='
			<label for="subcategory">'.ucfirst($page->drawLabel("tl_generic_subcategory", "Subcategory")).':</label>
			<select name="subcategory" id="subcategory">
				<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
			</select><br />
			<label for="newsubcategory"><em style="font-weight: normal; font-style: italic">'.$page->drawLabel("tl_img_field_addsubcat", "Or add subcategory").'</em>:</label>
			<input type="text" class="text" name="newsubcategory" id="newsubcategory" value="'.$newsubcategory.'" /><br />
		';
		
		if ($site->config['setup_resources']) { 
			$page_html.='
				<input type="checkbox" class="checkbox" name="resource"'.($resource ? ' checked="checked"' : '').' />
				<label for="resource" class="checklabel" style="width:240px;">'.$page->drawLabel("tl_img_field_showres", "Show in resources section").'</label><br />
			';
		} 
			
		$page_html.='
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_create", "Create")).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		
		echo treelineBox($page_html, $page->drawLabel("tl_file_crea_title", "Upload a new file"), "blue");
    }

	else if ($guid && $action == 'edit') { 

		$thisFile = new File();
		$thisFile->loadFileByGUID($guid);

		if (!$_POST['shared']) $shared=$thisFile->shared;

		$extraBottomJS .= '
			fillSubCategories("subcategory", '.$thisFile->getCategory().');
			setSubCategory("subcategory", '.$thisFile->getSubcategory().');
		';
		
		?><h2 class="pagetitle rounded">Modify file attributes</h2><?php 		
		
		$page_html = '
		<form id="treeline" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
			<input type="hidden" name="oldtitle" id="oldtitle" value="'.$thisFile->title.'" />
			<input type="text" name="title" id="title" value="'.($title ? $title : $thisFile->title).'"/><br />

			<label for="description">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
			<textarea name="description">'.($description ? $description : $thisFile->description  ).'</textarea><br />
		';

		// Only allow file sharing if the sites has microsites		
		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").'</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';
		
		include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php";
		$page_html.=$tags_html;

		$page_html.='
			<label for="category">'.$page->drawLabel("tl_file_edit_movecat", "Move to category").':</label>
			<select name="category" id="category" onChange="fillSubCategories(\'subcategory\',this.value);">
				<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
				'.$thisFile->drawCategories(($category!='xx')?$category:$thisFile->category).'
			</select><br />

			<label for="subcategory">'.ucfirst($page->drawLabel("tl_generic_subcategory", "Subcategory")).':</label>
			<select name="subcategory" id="subcategory">
				<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
			</select><br />
		';
		
		if ($site->config['setup_resources']) {
			$page_html.='
				<input type="checkbox" id="f_resources" class="checkbox" name="resource"'.($thisFile->resource ? ' checked="checked"' : '').' />
				<label for="f_resources" class="checklabel" style="width: 240px;">'.$page->drawLabel("tl_img_field_showres", "Show in resources section").'</label><br />
			';
		}
		
		$page_html.='    
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_save", "Save").'" />
			</fieldset>
		</fieldset>
		</form>
		';
		
		echo treelineBox($page_html, $page->drawLabel("tl_file_edit_title", "Edit file")." : ".$thisFile->title, "blue");
    }
	else if ($guid && ($action == 'delete' || $action=="delete")) {
	
		$thisFile = new File();
		$thisFile->loadFileByGUID($guid);
		//print "loaded file shared(".$thisFile->shared.")<br>\n";
		$page_html = '
        <form id="treeline" enctype="" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			'.($thisFile->shared?'<p class="instructions">'.$page->drawLabel("tl_file_del_msgshare", "You are about to delete a shared file. Please note that this action could result in broken links on other sites that use this shared resource").'</p>':"").'
            <p>'.$page->drawLabel("tl_file_del_msg", "You are about to delete this file, are you sure?").'</strong></p>
            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_delete", "Delete")).'" />
            </fieldset>
        </fieldset>
    	</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_file_del_title", "Confirm file delete")." : ".$thisFile->title, "blue");
 	}
	// If we didnt find anything to do and we dont have a guid passed then just show selectable files.
	else if ( !$guid ) {

		?><h2 class="pagetitle rounded"><?=$page->drawLabel("tl_file_list_heading", "Search for a file to manage")?></h2><?php 		
		
		$page_html = '
          	<form id="treeline" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
            <fieldset>
                <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
                <input type="hidden" name="action" value="'.$action.'" />
                <input type="hidden" name="guid" value="'.$guid.'" />

                <label for="ssearch">'.ucfirst($page->drawLabel("tl_generic_keywords", "Kewords")).':</label>
                <input type="text" class="text" name="q" id="f_ssearch" value="'.$ssearch.'" />

                <label for="category">'.ucfirst($page->drawLabel("tl_generic_category", "Category")).':</label>
                <select name="category" id="category">
                	<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
                  	'.$file->drawCategories($category).'
                </select><br />
                <input type="hidden" name="findcat" value="1" />
                <fieldset class="buttons">
                    <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_search", "Search")).'">
                </fieldset>
            </fieldset>
          	</form>
 		';
		echo treelineBox($page_html, $page->drawLabel("tl_file_list_title", "Filter by category or select a file from the list below below"), "blue");
		
		echo $file->drawFileList($thispage,$action,($category=='xx')?'':$category, $ssearch);
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