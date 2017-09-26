<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	$tags=new Tags($site->id);
	
	$message = array();
	$feedback = read($_REQUEST,'feedback','error');
	
	$action = read($_REQUEST,'action','');
	$search = read($_REQUEST,'q',NULL);
	
	if (!$search && !$action) $action="all";
	
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = read($_REQUEST,'show',10);
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$extraJS = ''; // extra on page JavaScript
	
	
	if ($_SERVER['REQUEST_METHOD']=="POST") {

		$feedback="error";	// Lets be pessimistic to begin with ...
		
		if($_POST['createtag'] == 'Create'){ // Create tag	
			if (!$tags->addTag($_POST['newtag'])) $message = $tags->error;
			else {
				$action="all";
				$message[]=$page->drawLabel("tl_tag_crea_success", "Your new tag was created");
			}
		}
		
		else if ($_POST['upload'] == 'Upload') {
			if ($_FILES['file']['size']>0) {
				if (
					$_FILES['file']['type']=="text/csv" || 
					$_FILES['file']['type']=="text/text" ||
					$_FILES['file']['type']=="text/plain" ||
					$_FILES['file']['type']=="text/comma-separated-values") {
					if (!$tags->uploadFromCSV($_FILES['file'])) {
						$message=$tags->error;
					}
					else {
						$feedback="success";
						$action='all';
						if ($tags->error) $message=$tags->error;
					}
				}
				else $message[]=$page->drawLabel("tl_tag_upl_badfile", "Uploaded filetype is not compatible. Please contact technical support or use the bug report function if you believe this file contains the correct formatting")."[".$_FILES['file']['type']."]";
			}
			else $message[]=$page->drawLabel("tl_tag_upl_nofile", "No file was uploaded");
		}
	}
	else {
			
		if ($action=="delete" && $_GET[id]>0) {
			$query="select count(*) as count FROM tag_relationships tr LEFT JOIN pages p ON tr.guid=p.guid WHERE tag_id=".$_GET['id']." and p.guid=tr.guid";
			//print "$query<br>";
			$count=$db->get_var($query);
			if ($count && !$_GET['c']==1) {
				$feedback="notice";
				$message[]=$page->drawLabel("tl_tag_del_msg1", "This tag has resources connected to it already");
				$message[]='<a href="?action=delete&amp;id='.$_GET['id'].'&amp;c=1">'.$page->drawGeneric("press_here", 1).'</a> '.$page->drawLabel("tl_tag_del_msg2", "if you are sure you want to delete this tag");
			}
			else {
				$query="delete from tags where id=".$_GET['id'];
				//print "$query<br>";
				if ($db->query($query)) {
					
					// We should remove it from the relationships too.
					$db->query("delete from tag_relationships where tag_id=".$_GET['id']);
	
					$action="";
					$action="all";
					$search = '';
					$message[]=$page->drawLabel("tl_tag_del_success", "This tag and all connections to it have been deleted");
					$feedback = "success";
				}
				else $message[]=$page->drawLabel("tl_tag_err_delfail", "Tag could not be removed");
			}
		}
	}
	
	// Page title	
	//$pageTitleH2 = ($action)? 'Tag library : '.ucwords($action) : "Tag library";
	//$pageTitle = $pageTitleH2;
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("Tag library", $action);
	
	$pageClass="edit-assets";
	if ($action=="create") $pageClass = 'create-content';
	
	// Get list of subscribed events
	if ($tag_id>0) {
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				$tag_html.='<li>'.$result->tag.' <a href="/treeline/tags/?tag_id='.$result->id.'&action=stuff&fund-guid='.$result->guid.'">stuff</a></li>';
			}
		}
	}
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
	
    if($user->drawGroup()!='Superuser' && $action=="delete"){  
		$feedback="error";
		$message[]="You do not have sufficient access rights to delete items from the tag library";
		$action="";
		$search="";
	}
	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php

    echo drawFeedback($feedback,$message);
    if ($nextsteps) echo treelineList($nextsteps, $page->drawGeneric("next_steps"), "blue");

	// --------------------------------------------------------------------
	// Create tag	
	if($action == 'create'){ 
		$page_html='
		<form id="CreateTagForm" action="/treeline/tags/" method="post">
			<fieldset>
				<input type="hidden" name="action" value="create" />
				<input type="hidden" name="createtag" value="Create" />
				<legend>'.$page->drawLabel("tl_tag_crea_single", "Create a single tag").'</legend>
				<label for="f_newtag">'.$page->drawLabel("tl_tag_field_new", "New tag").':</label>
				<input type="text" name="newtag" id="f_newtag" value="" /><br />
				<fieldset class="buttons">
					
					<input type="submit" class="submit" value="'.$page->drawGeneric("create", 1).'"  />
				</fieldset>
			</fieldset>
		</form>
		
		<form id="UploadTagForm" action="/treeline/tags/" method="post" enctype="multipart/form-data" enctype="multipart/form-data">
		<fieldset>
			<input type="hidden" name="action" value="create" />
			<input type="hidden" name="upload" value="Upload" />
			<legend>'.$page->drawLabel("tl_tag_crea_upload", "or upload tags from a CSV file").'</legend>
			<label for="f_file">'.$page->drawGeneric("file", 1).':</label>
			<input type="file" name="file" id="f_file" />
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawGeneric("upload", 1).'"  />
			</fieldset>
		</fieldset>
		</form>
		';
	
		echo treelineBox($page_html, $page->drawLabel("tl_tag_crea_title", "Create new tags"), "blue", 0, 0, 90);
		
	} 
		
		
	// --------------------------------------------------------------------
	/*
	else if ($action=="delete") {
		$page_html = '

		<!-- <p><a href="?action=all">Show all tags</a></p> -->
		
		<form id="filterForm" action="/treeline/tags/" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<legend>Find tag to delete</legend>
				<label for="q">Search for:</label>
				<input type="text" name="q" id="q" value="'.$search.'" /><br />
				<fieldset class="buttons">
					<input type="submit" class="submit" name="submitFilter" value="Filter" />
				</fieldset>
			</fieldset>
		</form>
		';
		echo treelineBox($page_html, "Delete a tag", "blue");                

	}
	*/	

	// --------------------------------------------------------------------
	//print "action($action) search($search)<br>";
	if ($search) {
		$query="SELECT t.id FROM tags t 
			LEFT JOIN tag_relationships tr ON t.id=tr.tag_id
			LEFT JOIN pages p on tr.guid=p.guid
			WHERE t.msv=".$site->id." AND t.tag LIKE '%$search%'";			
		//print "$query<br>";
		$db->get_var($query);
		$total=$db->num_rows;
		$query="SELECT t.id, tag, p.title, tr.type_id, tr.guid, tt.title AS tag_type 
			FROM tags t 
			LEFT JOIN tag_relationships tr ON t.id=tr.tag_id
			LEFT JOIN pages p on tr.guid=p.guid
			LEFT JOIN tag_types tt on tr.type_id=tt.id
			WHERE t.msv=".$site->id." AND t.tag LIKE '%$search%'
			LIMIT ".(($currentPage-1)*$perPage).", $perPage";			
		//print $query."<Br>";
		$results=$db->get_results($query);
		if($results){ // results exists
			
			$page_html = '<table class="treeline">
			<caption>'.getShowingXofX($perPage, $currentPage, sizeof($results), $total).' '.$page->drawLabel("tl_tag_srch_matches", "pages containing matching tags").'</caption>
			<thead>
				<tr>
					<th scope="col">'.$page->drawGeneric("tag", 1).'</th>
					<th scope="col">'.$page->drawGeneric("page", 1).'</th>
					'.(($action=="delete" || 1)?'<th scope="col">'.$page->drawGeneric("delete").'</th>':'').'
				</tr>
			</thead>
			<tbody>
			';
			
			foreach($results as $result){ // loop through and show results
				$page_html.='<tr>';
				if ($result->tag != $currentTag) {
					$currentTag=$result->tag;
					$page_html.='<td>'.$result->tag.'</td>';
					$page_html.='<td>';
					if($result->title) $page_html.=$result->title;
					else if ($result->type_id==1) $page_html.=$page->drawLabel("tl_tag_page_noexist", "Page does not exist")."[".$result->guid."]";
					else if ($result->type_id>1) $page_html.=$result->tag_type." ".$page->drawGeneric("resource")."[".$result->guid."]";
					else $page_html.=$page->drawLabel("tl_tag_srch_notused", "tag is not used");
					$page_html.'</td>';
					if ($action=="delete" || 1) { 
						$page_html.='<td class="action delete"><a href="?id='.$result->id.'&amp;action=delete&amp;q='.$search.'" '.$help->drawInfoPopup($page->drawLabel("tl_tag_act_delete", "Delete this tag")).'>'.$page->drawLabel("tl_tag_srch_delete", "Delete this tag").'</a></td>';
					} 
				} 
				else {
					$page_html.='<td>&nbsp;</td>';
					$page_html.='<td>';
					if($result->title) $page_html.=$result->title;
					else if ($result->type_id==1) $page_html.="Page does not exist[".$result->guid."]";
					else $page_html.=$result->tag_type." resource[".$result->guid."]";
					$page_html.='</td>';
					if ($action=="delete" || 1) $page_html.='<td>&nbsp;</td>';
				}
				$page_html.='</tr>';
			} 
			// end foreach result loop
			$page_html.='</tbody></table>';
			
			echo treelineBox($page_html, $page->drawLabel("tl_tag_srch_title", "Showing tags"), "blue");
			
			$currentURL="?q=".$search;
			echo drawPagination($total, $perPage, $currentPage, $currentURL);

			if (!$action=="delete") {
				//$nextsteps='<li><a href="/treeline/tags/?action=create">'.$page->drawLabel("tl_tag_next_create", "Create a new tag").'</a></li>';
				//if ($_SESSION['treeline_user_group']!="Author") $nextsteps.='<li><a href="/treeline/tags/?action=delete">Delete a tag from the tag library</a></li>';
				//echo treelineList($nextsteps, $page->drawGeneric('next_steps', 1), 'blue');
				$action="all";	// Show the full tags list too.
			}
		}
		else{ // results
			?><p><?=$page->drawLabel("tl_tag_srch_notags", "There are no tags")?></p><?php
		}
	}
			

	// --------------------------------------------------------------------
	if ($action=="all") {	
		$page_html='';
		$query="SELECT tag, count(tag_id) AS count FROM tags t
			LEFT JOIN tag_relationships tr ON t.id=tr.tag_id
			WHERE t.msv=".$site->id."
			GROUP by t.tag
			ORDER by t.tag";
		//print "$query<br>";
		if ($results=$db->get_results($query)) {
			$total = $db->num_rows;
			foreach ($results as $result) $page_html.= ($i++>0?", ":"").'<a href="?q='.$result->tag.'">'.$result->tag.'('.$result->count.')</a>';
			$page_html='
				<p>'.$page->drawLabel("tl_tags_list_found", "Found a total of").' '.$total.' '.$page->drawLabel("tl_tags_list_inlibrary", "tags in the tag library").'</p>
				<p>'.$page_html.'</p>
				';
		}
		else $page_html='<p>'.$page->drawLabel("tl_tag_list_notags", "There are no tags").'</p>';
		$page_html = '<p><a href="/treeline/tags/?action=create">'.$page->drawLabel("tl_tag_list_create", "Create new tags").'</a></p>'.$page_html;
		echo treelineBox($page_html, $page->drawLabel("tl_tag_list_title", "Listing all tags"), "blue");			
	}
		
	?>
</div>
</div>

<?php 

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 

?>
	
    