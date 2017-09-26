<?

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
	// Make sure no direct/unauthorised access to this page
	if ($_SESSION['treeline_user_group']!="Superuser") redirect("/treeline/");
	
	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline/");
	
	$guid = read($_REQUEST,'guid','');
		
	$message = array();
	$feedback = read($_REQUEST,'feedback','error');

	$title = read($_POST,'title','');
	
	$nextsteps='';
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$section_template = read($_POST,'section_template',11);
	
		// ---------------------------------------------------------------------
		// CREATE A NEW SECTION
		if ($action == 'create') {
			
			if(!$title) $message[] = $page->drawLabel("tl_sect_err_title", 'Your section needs to have a title');
			else{
				//// The name it's likelt to find matching is not this page, then generate a name...
				$name = $treeline->generateName($site->id, $title);
				if ($site->id==1 && $site->checkSiteName($name)) {
					$message[] = $page->drawLabel("tl_sect_err_micro1", "A microsite exists with that name");
					$message[] = $page->drawLabel("tl_sect_err_micro2", "You cannot create pages on this site that have the same name as any microsites");
				}
				else if(!$name) $message[] = $page->drawLabel("tl_sect_err_titexist", "Another section exists with this title");
				else {
					if($treeline->saveSection('',$name,$title,$section_template)) {
						// What do we want to do next ?		
						if ($_SESSION['treeline_user_group']!="Author") {
							$nextsteps.='<li><a href="/treeline/sections/?action=edit">'.$page->drawLabel("tl_sect_next_edit", "Edit site sections").'</a></li>';
							$nextsteps.='<li><a href="/treeline/sections/?action=create">'.$page->drawLabel("tl_sect_next_create", "Create another section").'</a></li>';
						}
						$nextsteps.='<li><a href="/treeline/pages/?action=create">'.$page->drawLabel("tl_sect_next_createweb", "Create a new web page").'</a></li>';
						$nextsteps.='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_sect_next_managweb", "Manage web pages").'</a></li>';
						$action="";
					}
					else $message[] = $page->drawLabel("tl_sect_err_failadd", 'Your section could not be added to the system');
				}
			}
		
		}
		// ---------------------------------------------------------------------



		// ---------------------------------------------------------------------
		// EDIT A SECTION
		elseif ($action == 'edit') {
		
			$sections = array();
			foreach($_POST as $key => $value){

				if(($key != 'Save changes &raquo;') && ($key != 'action') && ($key != 'guid') ){
					$tmpKey = substr($key,0,strrpos($key,'_'));
					if( substr_count($tmpKey,'template')>0 ){
						$tmpKey = 'template';
					}
					$guid = substr($key,strrpos($key,'_')+1);
					$sections[$guid][$tmpKey] = $value;
				}


			}
			
			// Actually update stuff
			if (count($sections)) {
				$save_attempt = $save_fail = 0;
				foreach($sections as $guid => $section){
					$save_attempt++;
					if (!$treeline->saveSection($guid,$section['name'],$section['title'],$section['template'],$section['order'])) {
						$save_fail++;
					}
				}
				if (!$save_fail) {
					$message[] = $page->drawLabel("tl_generic_changes_saved", 'Your changes were saved');
					$feedback = "success";
				}
				else {
					$message[] = "Saved ".($save_attempt-$save_fail)." of $save_attempt sections";
				}
				
			}

			if ($_SESSION['treeline_user_group']!="Author") {
				$nextsteps.='<li><a href="/treeline/sections/?action=delete">'.$page->drawLabel("tl_sect_next_delete", "Delete a section").'</a></li>';
				$nextsteps.='<li><a href="/treeline/sections/?action=create">'.$page->drawLabel("tl_sect_next_create", "Create a new section").'</a></li>';
			}
			$nextsteps.='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_sect_next_managweb", "Manage web pages").'</a></li>';
					
		} 
		// END OF EDIT SECTION
		// ---------------------------------------------------------------------
				
		// Delete a section
		else if($action == 'delete'){
			if($guid){
				$treeline->deleteSection($guid);
				if ($_SESSION['treeline_user_group']!="Author") {
					$nextsteps.='<li><a href="/treeline/sections/?action=delete">'.$page->drawLabel("tl_sect_next_delete", "Delete another section").'</a></li>';
					$nextsteps.='<li><a href="/treeline/sections/?action=create">'.$page->drawLabel("tl_sect_next_create", "Create a new section").'</a></li>';
					$nextsteps.='<li><a href="/treeline/sections/?action=edit">'.$page->drawLabel("tl_sect_next_edit", "Edit site sections").'</a></li>';
				}
				$nextsteps.='<li><a href="/treeline/pages/?action=edit">'.$page->drawLabel("tl_sect_next_managweb", "Manage web pages").'</a></li>';
				$action="";
			}
			else $message[] = $page->drawLabel("tl_sect_err_faildel", 'Could not delete this section');
		}
			
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

form fieldset input {
	width: 220px;
}

/* sections specific */
input.section-name {
	width: 100px !important;
}

select {
	width: 122px !important;
}
hr{ 
	clear: both; 
	border-width: 0 0 1px;
}
	
label.sort_order, label.template{
	clear: none;
	display: inline;
	margin-left: .5em;
	width: 6em;
}
	
label.template{
	width: 4em;
}

input.sort_order{
	clear: none;
	display: inline;
	width: 2em;
}

'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 =  ucfirst($page->drawLabel("tl_generic_sections", 'Sections'));
	$pageTitleH2 .= ($action)?' : '.$page->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action))):'';
	$pageTitle = $pageTitleH2;
	
	$pageClass = 'edit-structure';
	if ($action=="create") $pageClass="create-content";
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');
		
?>
<div id="primarycontent">
<div id="primary_inner">
<?php

	echo drawFeedback($feedback,$message);

	if ($nextsteps) echo treelineList($nextsteps, $page->drawLabel("tl_generic_next_steps", "Next steps"), "blue");

	// ---------------------------------------------------------------------------
	// Create a new section
	if ($action == 'create') {
		$page_html = '
        <form id="treeline" action="/treeline/sections/?action='.$action.($DEBUG?"&amp;debug":"").'" method="post">
            <fieldset>
                <input type="hidden" name="action" value="'.$action.'" />
                <input type="hidden" name="guid" value="'.$guid.'" />
                <label for="title">'.$page->drawLabel("tl_sect_field_title", "Section title").':</label>
                <input class="section-name" type="text" name="title" id="title" value="'.$title.'" />
                <label for="section_template" class="template">'.$page->drawLabel("tl_generic_type", "Type").'</label>
                '.$treeline->drawSectionTemplates($section_template,"","section_template").'
                <fieldset class="buttons">
                	<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_save", "Save").'" />
                </fieldset>
            </fieldset>
        </form>	
		';
		$page_title= $page->drawLabel("tl_sect_create_title", "To add a top-level section, please enter the title in the field below");
		echo treelineBox($page_html, $page_title, "blue");
	}
	// ---------------------------------------------------------------------------

	
	// ---------------------------------------------------------------------------
	// EDIT A SECTION
	else if ($action == 'edit') {
		$page_html = '
		<form id="treeline" action="/treeline/sections/?action='.$action.($DEBUG?'&amp;debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			'.$treeline->drawEditableSections().'
			<!-- <p class="instructions"><strong>'.$page->drawLabel("tl_sect_no_rename", "Please note: Sections cannot be renamed as this would create broken links on your site, in search engines and on the websites that link to you").'</p> -->
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_save_changes", "Save changes").'" />
			</fieldset>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_sect_edit_title", "Reorder sections here"), "blue");
	}


	elseif ($action == 'delete' && !$guid) { 
		$page_html = '<p>'.$page->drawLabel("tl_sect_delete_msg", "Only sections that contain no pages can be deleted").'</p>';
		$page_html .= $treeline->drawDeleteableSections($site->id);
		echo treelineBox($page_html, $page->drawLabel("tl_sect_delete_title", "To delete a top-level section, please select from the list below"), "blue");
	}
	
	else if ($action == "delete" && $guid) { 
		$page_html = '
		<form id="treeline" action="/treeline/sections/?action='.$action.($DEBUG?'&amp;debug':"").'" method="post">
		<fieldset>
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="guid" value="'.$guid.'" />
			<p class="instructions">'.$page->drawLabel("tl_sect_delete_msg1", "Are you sure you want to permanently delete this section?").'<br />
			<strong>'.$page->drawLabel("tl_sect_delete_msg2", "This may affect the appearance of your website").'</strong></p>
			<input type="hidden" name="guid" value="'.$guid.'" />
			<input type="hidden" name="title" value="'.$treeline->getSectionByGUID($guid).'" />
				<input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_delete", "Delete").'" />
			</fieldset>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, ucfirst($page->drawLabel("tl_generic_delete", "Delete"))." : ".$treeline->getSectionByGUID($guid), "blue");
	}

?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>