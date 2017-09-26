<?php
	ini_set("display_errors", 1);
	
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/gmaps.class.php");	

	//$page = new Page();
	
	$action = read($_REQUEST,'action','edit');
	$guid = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'guid', '');
	$marker_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'mid', 0);
	$thispage = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'page', 1);
	$keywords = $_POST?$_POST['keywords']:$_GET['keywords'];
	
	$message = array();
	$feedback = read($_REQUEST,'feedback','error');

	$nextsteps='';

	$map = new GMaps();
	if ($marker_id) {
		$query = "SELECT parent FROM googlemap_marker WHERE id=".$marker_id;
		//print "get guid($query)<br>\n";
		$guid = $db->get_var($query);
	}
	if ($guid) $map->loadByGUID($guid);

	//print "loaded map($guid) marker($marker_id)<br>\n";
		
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		if ($action == 'create') {
			if (!$map->create()) $message = $map->errmsg;
			else {
				$message = "Your map has been created.";
				$feedback="success";
				$action="edit";
				$guid = $map->guid;
			}
		} 
		
		else if ($action == "create-marker" && guid) {
			if (!$map->marker->create()) $message = $map->marker->errmsg;
			else {
				$message = "Your marker has been created.";
				$feedback="success";
				$action = "edit-marker";
			}
		}
		
		else if ($action == 'edit' && $guid) {
			if (!$map->save()) $message = $map->errmsg;
			else {
				$message = "Your map has been saved.";
				$feedback="success";
			}

		}

		else if ($action == "edit-marker" && $guid && $marker_id>0) {
			if (!$map->marker->save($marker_id)) $message = $map->marker->errmsg;
			else {
				$message = "Your marker has been saved.";
				$feedback="success";
				$marker_id = 0;
			}
		}
		
		
		else if ($action == 'delete') {
			if (!$map->delete($guid)) $message = $map->errmsg;
			else {
				$message = "Your map has been deleted.";
				$feedback="success";
				$action="edit";
				$guid = '';
			}
		}

		else if ($action == "delete-marker" && $marker_id) {
			if (!$map->marker->delete($marker_id)) $message = $map->errmsg;
			else {
				$message = "Your marker has been deleted.";
				$feedback="success";
				$action="edit-marker";
				$marker_id = 0;
			}
		}
		
		else if ($action=="edit" && $_POST['findmap']) ;	// Do nothing as just searching
		
		else $message[]="Action($action) posted but nothing to do guid($guid) marker($marker_id)";						

	}

	else {
	
	}
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title
	$pageTitleH2 = ($action) ? 'Google maps : '.ucwords(str_replace("-", " ", $action)) : 'Google maps';
	$pageTitle = $pageTitleH2;
	
	$pageClass = 'gmaps';

	
	if ($guid) $map->loadByGUID($guid);
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');

?>
<div id="primarycontent">
<div id="primary_inner">
<?php
echo drawFeedback($feedback,$message);
if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");

if ($action == 'create') { 
	echo treelineBox($map->drawForm($action), "Create a new map", "blue");
} 
else if ($action == 'create-marker' && $guid) { 
	echo treelineBox('<p><a href="/treeline/googlemap/?action=edit">Search maps</a></p>'.$map->drawForm($action), 'Edit map : '.$map->title, "blue");
	echo treelineBox($map->marker->drawForm($action), "Create a new marker", "blue");
	echo $map->marker->drawMarkersList();
	echo $map->drawMap($true);
} 
else if ($action == 'edit-marker' && $guid) { 
	echo treelineBox('<p><a href="/treeline/googlemap/?action=edit">Search maps</a></p>'.$map->drawForm($action), 'Edit map : '.$map->title, "blue");
	if ($marker_id>0) {
		echo treelineBox($map->marker->drawForm($action, $marker_id), "Edit marker", "blue");
	}
	else echo treelineBox('<p><a href="/treeline/googlemap/?action=create-marker&guid='.$guid.'">Create a new marker</a></p>'.$map->marker->drawMarkersList(), 'Markers for this map', "blue");
	echo $map->drawMap(true);
}
// ---------------------------------------------------------
// EDIT USERS.
else if ($action == 'edit' || $action == "preview") {

	if ($guid) { 
		echo treelineBox('<p><a href="/treeline/googlemap/?action=edit">Search maps</a></p>'.$map->drawForm($action), 'Edit map : '.$map->title, "blue");
		if ($action!="preview") echo treelineBox('<p><a href="/treeline/googlemap/?action=create-marker&guid='.$guid.'">Create a new marker</a></p>'.$map->marker->drawMarkersList(), 'Markers for this map', "blue");
		echo $map->drawMap(true);
	}
	else {
		$page_html='
		<p><a href="/treeline/googlemap/?action=create">Create a new map</a></p>
		<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="findmap" value="1" />
				<p class="instructions">Find the map you want to '.$action.'</p>
				<label for="search_field">Search for:</label>
				<input type="text" name="keywords" id="search_field" value="'.$keywords.'" /><br />
				<fieldset class="buttons">
					<input type="submit" class="submit" value="Search" />
				</fieldset>
			</fieldset>
		</form>
		';	

		echo treelineBox($page_html, ucfirst($action).' a map', "blue");
	
		?>
		<h2>Recently added maps</h2>
		<?=$map->drawMapsList($thispage, $keywords)?>
        <?php
	}
}
// ---------------------------------------------------------

// DELETE A MAP
else if($guid && $action=='delete-marker' && $marker_id>0){ 
	$map->marker->loadByID($marker_id);
	//$page_html = '<p><a href="/treeline/googlemap/?action=edit">Search maps</a></p>'.$map->drawForm($action);
	$page_html .='
    <form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
    <fieldset>
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="guid" value="'.$guid.'" />
		<input type="hidden" name="mid" value="'.$marker_id.'" />
		<p class="instructions">Are you sure you want to delete the marker <strong>'.$map->marker->title.'</strong>?</p>
		<fieldset class="buttons">
			<input type="submit" class="submit" value="Confirm delete" />
		</fieldset>
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, "Delete this marker", "blue");
	echo treelineBox('<p><a href="/treeline/googlemap/?action=edit">Search maps</a></p>'.$map->drawForm($action), 'Edit map : '.$map->title, "blue");
	echo treelineBox($map->marker->drawForm($action), "Create a new marker", "blue");
	echo $map->drawMap($true);
}
else if($guid && $action=='delete' ){ 

	$page_html='
    <form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
    <fieldset>
        <input type="hidden" name="action" value="'.$action.'" />
        <input type="hidden" name="guid" value="'.$guid.'" />
        <p class="instructions">Are you sure you want to delete the map <strong>'.$map->title.'</strong>?</p>
        <fieldset class="buttons">
            <input type="submit" class="submit" value="Confirm delete" />
        </fieldset>
   </fieldset>
   </form>
   ';
   echo treelineBox($page_html, "Delete this map", "blue");
   
} 

?>
</div>
</div>

<?php 
echo $page->initCKE();
?>
<script type="text/javascript">
	CKEDITOR.replace('f_content', {toolbar : 'contentStandard', width: '500px', height: '300px' });
</script>
<?php
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>