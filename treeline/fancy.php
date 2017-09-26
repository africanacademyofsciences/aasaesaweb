<?php

	ini_set("display_errors", 1);

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/fancy.class.php");	


	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/");
	$guid = read($_REQUEST,'guid','');

	$feedback = read($_REQUEST,'feedback',"error");
	$message = array();

	$title = read($_POST,'title','');
	$description = read($_POST,'description','');
	$code = read($_POST,'code','');
	$codetype = read($_POST, 'codetype', '');
	$shared = read($_POST, "shared", "");

	$ssearch = read($_REQUEST, "q", "");
	
	// Create a new file:
	$fancy = new fancy;
	
	// Pagination
	$thispage = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'p',1);
	$fancy->setPage($thispage);
	
	// ****************************************
	// PROCESSING ANY POST ACTION  ************	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		//print "got post action($action)<br>\n";
		
		// Create a new fancy
		if ($action == 'create') {
			if (!$title) $message[] = "Please enter a title for this code block";
			else {
				$fancy->setTitle($title);
				$fancy->setDescription($description);
				$fancy->setCode($code, $codetype);
				$fancy->setShared($shared);
				if (!$fancy->create()) $message[] = "Failed to create your code block";
				else {	
					$nextsteps='<li><a href="/treeline/fancy/?action=create">Add another code block to the library</a></li>';
					$action="edit";
					$guid = '';
					unset($category, $subcategory);
				}
			}
		}
		

		// Edit a file in the file or fancy libraries
		else if ($action == 'edit') {
			if(!$findcat){
				$fancy->loadByGUID($guid);
				
				// Check we have a title
				// We no longer allow names to be changed on files since
				// it breaks content and messes everything up.
				if (!$title) $message[] = 'Please enter a title for this code block';
				else {
					$fancy->setTitle($title);
					$fancy->setDescription($description);
					$fancy->setCode($code, $codetype);
					$fancy->setShared($shared);
					if (!$fancy->save()) $message[] = "Failed to save code block";
					else {		
						$nextsteps='<li><a href="/treeline/fancy/?action=create">Add another code block to the library</a></li>';
						$guid='';
						unset($category, $subcategory);
					}
				}
			}
		}
		
		// Actually delete a fancy block
		else if ($action == 'delete' && !$findcat) {
			if($fancy->delete($guid)){
				$nextsteps='<li><a href="/treeline/fancy/?action=create">Add another code block to the library</a></li>';
			}
			else $message[] = 'Code block could not be deleted';
			$action="edit";
			$guid="";
		}
		
	}
	// END OF ACTION PROCESSING ************	

	// PAGE specific HTML settings
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

p.instructions {
	display: none;
}
form#treeline {
	float:left;
}

'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = '

'; 
	//print "Got action($action) guid($guid) fancy($fancy:".$file->getMedia().")<br>\n";
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("fancy code", $action);
	$pageClass = 'fancy';
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

  		$page_html='
		<form id="treeline" enctype="multipart/form-data" action="/treeline/fancy/'.($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">To upload a new block to the library, please complete the form below</p>
			<label for="title">'.$page->drawGeneric("title", 1).'</label>
			<input type="text" name="title" id="title" value="'.$title.'" /><br />

			<label for="f_description">'.$page->drawGeneric("description", 1).':</label>
			<textarea name="description" id="f_description">'.$description.'</textarea><br />

			'.$fancy->drawTypes($codetype).'
			
			<div>
				<label for="f_code">'.$page->drawGeneric("code", 1).':</label>
				<div style="float: left; width: 400px;">
					<textarea name="code" id="f_code">'.$code.'</textarea>
				</div>
			</div>
			<br />
			';
			
		// Only allow file sharing if the sites has microsites		
		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").'</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';

		$page_html.='
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawGeneric("create", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		
		echo treelineBox($page_html, "Upload new code block", "blue");
    }

	else if ($guid && $action=='edit') { 

		$fancy->loadByGUID($guid);
		if (!$_POST['shared']) $shared=$fancy->shared;

		?><h2 class="pagetitle rounded">Modify code block attributes</h2><?php 		
		
		//disabled="disabled"
		$page_html = '
		<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<label for="title">'.$page->drawGeneric("title", 1).':</label>
			<input type="text" name="title" id="title" value="'.($title ? $title : $fancy->title).'"/><br />
			
			<label for="embed">Embed</label>
			<input type="text" name="embed" value="@@FANCY-'.$guid.'@@"  />
			
			<label for="description">'.$page->drawGeneric("description", 1).':</label>
			<textarea name="description">'.($description ? $description : $fancy->description  ).'</textarea><br />

			'.$fancy->drawTypes($codetype?$codetype:$fancy->codetype).'
			
			<div>
				<label for="f_code">'.$page->drawGeneric("code", 1).':</label>
				<div style="float: left; width: 400px;">
					<textarea name="code" id="f_code">'.($code ? $code : $fancy->code).'</textarea>
				</div>
			</div>
			<br />
			';

		// Only allow file sharing if the sites has microsites		
		if ($site->getConfig("setup_microsites")) {
			$page_html.='
			<label for="f_sharing">'.$page->drawLabel("tl_img_field_share", "Share this").':</label>
			<input type="checkbox" name="shared" id="f_sharing" style="width:auto;" value="1" '.($shared==1?'checked="checked"':"").' />
			';
		}
		else $page_html.='<input type="hidden" name="shared" value="0" />';
		
		$page_html .= '
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		
		echo treelineBox($page_html, "Edit code: ".$fancy->title, "blue");
    }
	else if ($guid && $action == 'delete') {
	
		$fancy->loadByGUID($guid);
		
		$page_html = '
        <form id="treeline" enctype="" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			'.($fancy->shared?'<p class="instructions">You are about to delete a shared code block. Please note that this action could result in broken links on other sites that use this shared resource</p>':"").'
            <p>You are about to delete this code block, are you sure?</strong></p>
            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.$page->drawGeneric("delete", 1).'" />
            </fieldset>
        </fieldset>
    	</form>
		';
		echo treelineBox($page_html, "Confirm code block delete: ".$fancy->title, "blue");
 	}
	// If we didnt find anything to do and we dont have a guid passed then just show selectable files.
	else if ( !$guid ) {

		?><h2 class="pagetitle rounded">Search for code block to manage</h2><?php 		
		
		$page_html = '
          	<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
            <fieldset>
                <input type="hidden" name="action" value="'.$action.'" />
                <input type="hidden" name="guid" value="'.$guid.'" />

                <label for="ssearch">'.$page->drawGeneric("keywords", 1).':</label>
                <input type="text" class="text" name="q" id="f_ssearch" value="'.$ssearch.'" />

                <fieldset class="buttons">
                    <input type="submit" class="submit" value="'.$page->drawGeneric("search", 1).'">
                </fieldset>
            </fieldset>
          	</form>
 		';
		echo treelineBox($page_html, "Search by keyword or select code block from the list below below", "blue");
		
		echo $fancy->drawList($thispage, $action, ($category=='xx')?'':$category, $ssearch);
	}

	// Erm, got a guid and action but didnt find anything to process it???	
	else {
		print "eek, got guid($guid) and action($action) but could not process<br>\n";
		?><p>Please go back and try again.</p><?php 
	}

	?>
</div>
</div>

<script type="text/javascript">
function togglecode(v) {
	var a = document.getElementsByClassName("instructions");
	for (i=0; i<a.length; i++) a[i].style.display = "none";
	if (v) {
		var f = document.getElementById("instruction-"+v);
		f.style.display="block";
	}
}
</script>

<?php
if ($action=="create" || ($action=="edit" && $guid)) {
	?>
	<script type="text/javascript" src="/treeline/includes/ckeditor/ckeditor.js"></script>
    <script type="text/javascript">
        CKEDITOR.replace('f_code', { toolbar : 'contentStandard', height: '400px', width: '400px', stylesSet: 'fancy' });
    </script>
	<?php
}

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>