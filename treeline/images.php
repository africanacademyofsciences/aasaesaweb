<?php

	//ini_set("display_errors", 1);
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	

	$tags = new Tags($site->id, 2);// TAGS

	$action = read($_REQUEST,'action','');
	if (!$action) { // No action: send back to Treeline home: Why?
		header("Location: /treeline/");
	}
	$guid = read($_REQUEST,'guid','');

	$message = array();
	$feedback = read($_REQUEST,'feedback','error');
	
	$title = read($_POST,'title','');
	$oldtitle = read($_POST,'oldtitle','');
	
	$credit = read($_POST,'credit','');
	$description = read($_POST,'description','');
	$shared = read($_POST, 'shared', '');

	$category = read($_REQUEST,'category','xx');
	$newcategory = read($_POST,'newcategory',false);
	if ($category=='xx' && isset($_GET['cat']) && $_SERVER['REQUEST_METHOD']=="GET") $category=$_GET['cat'];

	$subcategory=read($_REQUEST, 'subcategory', 'xx');
	$newsubcategory=read($_POST, 'newsubcategory', '');
	if ($subcategory=='xx' && isset($_GET['subcat']) && $_SERVER['REQUEST_METHOD']=="GET") $subcategory=$_GET['subcat'];

	$resource = read($_POST, 'resource', 0);
	
	$findcat = read($_POST,'findcat',false);

	$upload = read($_FILES,'image',false);

	// Create a new image:
	$image = new Image;
		
	$thispage = read($_REQUEST,'p',1);
	$image->setPage($thispage);

	//print "load cat($category) subcat($subcategory) page($thispage)<br>\n";



	$feedback = 'error';
	$nextsteps='';

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		
		// Need to check for tag adding and ensure no other actions are processed
		// We actually add tags in the /treeline/includes/ajax/forms/addEditTags.php file
		if ($_POST['tagaction']) {
			;
		}
		else if ($action == 'create') {
			if (!$title) $message[] = $page->drawLabel("tl_img_err_title", 'Please enter a title for this image');
			else {
				$upload_error=$image->uploadImage($upload, $title, $credit, $description, $shared, $category, $subcategory, $newcategory, $newsubcategory, $resource);
				if (!$upload_error || $upload_error==1) {
					$imgGUID = $image->master_guid;

					$image->close();
					
					$nextsteps.='<li><a href="/treeline/images/?action=create">'.$page->drawLabel("tl_img_next_upload", "Upload an image to the library").'</a></li>';
					$action='edit';
					$guid="";
					unset($category); unset($subcategory);
				}
				//else $message[] = $page->drawLabel("tl_".str_replace(" ", "-", substr($upload_error, 0, 20)), $upload_error);
				else $message[] = $upload_error;
			}
		}

		// ----------------------------------------------------------
		// Edit image properties
		else if ($action == 'edit') {
			if(!$_POST['findcat']){
				if (!$title) $message[] = $page->drawLabel("tl_img_err_title", 'Please enter a title for this image');
				else if ($category == 'xx') $message[] = $page->drawLabel("tl_img_err_category", 'Please select a category for this image');
				else {

					$image->loadImageByGUID($guid);
					$image->setTitle($title);
					$image->setCategory($category);
					$image->setSubcategory($image->getCategory(), $subcategory);
					$credit = read($_POST,'credit','');
					$image->setCredit($credit);
					$image->setSharing($shared);
					$image->setDescription($description);
					$image->setResource($resource);

					$name = $image->getName();	// Store the old name
					// This should really recheck the name if we change categories too?
					if ($title != $oldtitle) {
						$name = $image->generateName();
					}
					if (!$name) $message[] = $page->drawLabel("tl_img_err_exists", 'An image with that name already exists in that category/subcategory');
					else {			
						$image->save($guid);
						// add tags
						$tagslist=$tags->drawAdminTags(read($_REQUEST, "pagetagslist", ','));
						$tags->addTagsToContent($guid, str_replace(", ",",",$tagslist));						

						$nextsteps.='<li><a href="/treeline/images/?action=create">'.$page->drawLabel("tl_img_next_upload", "Upload an image to the library").'</a></li>';
						$guid="";
						unset($category); unset($subcategory);
					}
				}
			}
		}
		// ----------------------------------------------------------

		else if ($action == 'delete') {
			if(!$_POST['findcat']){
				if($image->delete($guid)){

					$action="edit"; $guid='';
					$nextsteps='<li><a href="/treeline/images/?action=create">'.$page->drawLabel("tl_img_next_upload", "Upload an image to the library").'</a></li>';

					// Do we need to do a monster email???
					if ($image->shared) {
						//print "that was a shared image, need to tell everybody";
						$tasks->notify("shared-delete", array("TYPE"=>"image","NAME"=>$image->title));
					}
				}
				else{
					$message[] = $page->drawLabel("tl_img_err_delete", "There was a problem deleting your image. Please try again");
				}
			}
		}				
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page

	
	$extraJS = '

var subcats = new Array();

'.$image->drawSubcategories().'

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
//	selectoptions = menu.options;
}	

'; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 =  ucfirst($page->drawLabel("tl_generic_images", 'Images'));
	$pageTitleH2 .= ($action)?' : '.$page->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action))):'';
	$pageTitle = $pageTitleH2;
	

	$pageClass = 'images';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php
	echo drawFeedback($feedback,$message);
	if ($nextsteps) echo treelineList($nextsteps, $page->drawLabel("tl_generic_next_steps", "Next steps"), "blue");

	if ($action == 'create') { 

		if ($subcategory>0) {				
			$extraJS .= '
			fillSubCategories("subcategory", '.$category.');
			setSubCategory("subcategory", '.$subcategory.');
			';
		}
		foreach($image->extensions as $ext) $validImage.=strtoupper($ext).',';
		$maxhw = $image->upload_max_width;
		$page_html='
		<form id="treeline" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">To upload a new image to the image-library, please complete the form below. Images must be '.$validImage.' and maximum width or height of '.$maxhw.' pixels</p>

			<label for="image">'.$page->drawLabel("tl_img_crea_select", "Select an image").':</label>
			<input type="file" class="text" name="image" id="image" value="'.$upload['name'].'" /><br />     

			<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
			<input type="text" class="text" name="title" id="title" value="'.$title.'" /><br /> 

			<label for="description">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
			<textarea name="description" id="description">'.$description.'</textarea><br />  

			<label for="credit">'.ucfirst($page->drawLabel("tl_generic_credit", "Credit")).': <span style="font-weight:normal; font-style: italic">'.$page->drawLabel("tl_generic_optional", "optional").'</span></label>
			<input type="text" class="text" name="credit" id="credit" value="'.$credit.'" /><br />    
			';
		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").':</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';
		$page_html.='
			<label for="category">'.ucfirst($page->drawLabel("tl_generic_category", "Category")).':</label>
			<select name="category" id="category" onChange="fillSubCategories(\'subcategory\',this.value);">
			  <option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
			  '.$image->drawCategories($category).'
			</select><br />
			<label for="newcategory"><em style="font-weight: normal; font-style: italic">'.$page->drawLabel("tl_img_field_addcat", "Or Add category").':</em></label>
			<input type="text" class="text" name="newcategory" id="newcategory" value="'.$newcategory.'" />
			<br />

			<label for="subcategory">'.ucfirst($page->drawLabel("tl_generic_subcategory", "Subcategory")).':</label>
			<select name="subcategory" id="subcategory">
			  <option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
			</select><br />
			<label for="newsubcategory"><em style="font-weight: normal; font-style: italic">'.$page->drawLabel("tl_img_field_addsubcat", "Or add subcategory").':</em></label>
			<input type="text" class="text" name="newsubcategory" id="newsubcategory" value="'.$newsubcategory.'" />
			';

		if ($site->getConfig('setup_resources')) { 
			$page_html.='
				<input type="checkbox" id="f_resources" class="checkbox" value="1" name="resource"'.($resource ? ' checked="checked"' : '').' />
				<label for="f_resources" class="checklabel" style="width:240px;">'.$page->drawLabel("tl_img_field_showres", "Show in resources section").'</label><br />
			';
		} 

		$page_html.='			
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_upload", "Upload")).'" />
			</fieldset>
		</fieldset>
		</form>
		';

		
		echo treelineBox($page_html, $page->drawLabel("tl_img_crea_title", "Upload a new image"), "blue");
	} 
	
	
	
	else if ($action=="edit" && $guid) {
		$thisImg = new Image();
		$thisImg->loadImageByGUID($guid);
	
		if (!$_POST['shared']) $shared=$thisImg->shared;
		
		$extraBottomJS = 'fillSubCategories("subcategory", '.$thisImg->getCategory().');';
		if ($thisImg->getSubcategory()>0) {
			$extraBottomJS .= 'setSubCategory("subcategory", '.$thisImg->getSubcategory().');';
		}

		$page_html='
		<form id="treeline" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">'.$page->drawLabel("tl_img_edi_msg", "To edit the details of this file, complete the form below and press save").'</p>

			<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
			<input type="hidden" name="oldtitle" id="oldtitle" value="'.$thisImg->title.'" />
			<input type="text" class="text" name="title" id="title" value="'.($title?$title:$thisImg->title).'" /><br />      
			
			<label for="description">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
			<textarea name="description" id="description">'.($description?$description:$thisImg->description).'</textarea><br />
			';
		// Don't tag images by default as there is no point at all.
		if ($tag_images) {
			include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php";
			$page_html.=$tags_html;
		}
		
		$page_html.='
			<label for="credit">'.ucfirst($page->drawLabel("tl_generic_credit", "Credit")).': <em style="font-weight:normal;">'.$page->drawLabel("tl_generic_optional", "optional").'</em></label>
			<input type="text" class="text" name="credit" id="credit"value="'.($credit?$credit:$thisImg->credit).'" /><br />      
			';

		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").'</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';

		$page_html.='
			<div>
			<label for="category">'.$page->drawLabel("tl_img_field_movecat", "Move to category").':</label>
			<select name="category" id="category"  onChange="fillSubCategories(\'subcategory\',this.value);">
				<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
				'.$image->drawCategories($category!='xx'?$category:$thisImg->category).'
			</select>
			</div>

			<label for="subcategory">'.ucfirst($page->drawLabel("tl_generic_subcategory", "Subcategory")).':</label>
			<select name="subcategory" id="subcategory">
				<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
			</select><br />
			';

		if ($site->getConfig('setup_resources')) { 
			$page_html.='
				<div>
				<input type="checkbox" id="f_resources" class="checkbox" value="1" name="resource" '.($_POST?$resource:$thisImg->resource? 'checked="checked"' : '').' />
				<label for="f_resources" class="checklabel" style="width:240px;">'.$page->drawLabel("tl_img_field_showres", "Show in resources section").'</label>
				</div>
			';
		} 

		$page_html.='
			<div>
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_save", "Save")).'" />
			</fieldset>
			</div>
			';
		

		// Lets get the filename of a sensibly sized version of the image,
		$query = "SELECT filename, width, height FROM images_sizes where guid='".$thisImg->guid."' AND width<680 ORDER BY width DESC LIMIT 1";
		//print "$query<br>\n";
		if ($row = $db->get_row($query)) {
			$filepath = $row->filename;
			$width=$row->width; 
			$height=$row->height;
		}
		else {
			$filepath = $thisImg->imgsrc;
			$width = $thisImg->width;
			$height = $thisImg->height;
			if (!preg_match("/(.*)\.(.*?)/", $thisImg->filename)) $filepath=$thisImg->filename.".".$thisImg->extension;
		}		
		$page_html.='				
			<p><img src="/silo/images/'.$filepath.'" width="'.$width.'" height="'.$height.'" alt="'.$thisImg->title.'" title="'.$thisImg->title.'" /></p>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_img_edi_title", 'Edit image').' : '.$thisImg->o_filename, "blue");
	}
                
	else if ($action == 'delete' && $guid) {
		$thisImg = new Image();
		$thisImg->loadImageByGUID($guid);
		$page_html = '
<form id="treeline" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
<fieldset>
	<input type="hidden" name="action" value="'.$action.'" />
	<input type="hidden" name="guid" value="'.$guid.'" />
	'.($thisImg->shared?'<p class="instructions">'.$page->drawLabel("tl_img_del_shared", "You are about to delete a shared image. Please note that this action could result in missing images on other sites that use this shared resource").'</p>':"").'
	<p>'.$page->drawLabel("tl_img_del_msg", "You are about to delete this image from the image library. Are you sure?").'</strong></p>

	<fieldset class="buttons">
		<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_delete", "Delete")).'" />
	</fieldset>
</fieldset>
</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_img_del_title", 'Delete image').' : '.$thisImg->getTitle(), "blue");
	} 
	
	// Show image search options.
	else if (!$guid) { 

		//print "edit got cat($category) sub($subcategory)<br>";
		if ($category>0) {				
			$extraBottomJS='fillSubCategories("subcategory", '.$category.');';
			if ($subcategory>0) {
				$extraBottomJS.='setSubCategory("subcategory", '.$subcategory.');';
			}
		}
        
		// Locate an image to edit/manage??        
		$page_html='
		<form id="treeline" enctype="multipart/form-data" action="/treeline/images/?action=edit'.($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="p" value="1" />
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">'.$page->drawLabel("tl_img_edi_msg1", "To edit image details, move or delete an image, please select it from the list below below").'</p>
			<label for="category">'.ucfirst($page->drawLabel("tl_generic_category", "Category")).':</label>
			<select name="category" id="category" onChange="fillSubCategories(\'subcategory\',this.value);">
				<option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
				'.$image->drawCategories($category).'
			</select><br />
			<input type="hidden" name="findcat" value="1" />

			<label for="subcategory">'.ucfirst($page->drawLabel("tl_generic_subcategory", "Subcategory")).':</label>
			<select name="subcategory" id="subcategory">
			  <option value="xx">'.ucfirst($page->drawLabel("tl_generic_select", "Select")).':</option>
			</select><br />

			<fieldset class="buttons">
			  <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_search", "Search")).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_img_edi_title1", "Locate image to manage"), "blue");
		$category = ($category=='xx')?'':$category;
		echo $image->drawImageList($thispage, $category, $subcategory);
	} 


?>  
</div>
</div>

<?php
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>