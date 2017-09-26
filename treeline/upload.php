<?

	ini_set("display_errors", 1);
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/file.class.php");	

	$file = new File;
	$file->site_id=$site->id;

	$action = read($_REQUEST,'action','');

	$uploaded = array();

	$destination = $_SERVER['DOCUMENT_ROOT']."/silo/";
	$destination_directory = "files/";
	$destination_directory = "tmp/";

	$message = array();
	$feedback = "error";
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
		//print "Posted[".print_r($_FILES, 1)."]<br>\n";
		if (is_array($_FILES['file']) && count($_FILES['file'])) {

			$destination .= $destination_directory;
			foreach ($_FILES['file']['name'] as $key => $name) {
				//print "k($key) => $name<br>\n";
				$thisfile['name'] = $_FILES['file']['name'][$key];
				$thisfile['tmp_name'] = $_FILES['file']['tmp_name'][$key];
				$thisfile['type'] = $_FILES['file']['type'][$key];
				$thisfile['error'] = $_FILES['file']['error'][$key];
				$thisfile['size'] = $_FILES['file']['size'][$key];
				$thisfile['extension'] = substr($name, -3, 3);
				$thisfile['uerr'] = '';
				if (!$file->setType($thisfile['type'], $thisfile['extension'])) {
					$thisfile['uerr'] = "Files of that type are not allowed[".$thisfile['type']."]";
				}
				//print "This file[".print_r($thisfile, 1)."]<br>\n";
				//print "move_uploaded_file(".$thisfile['tmp_name'].", ".$destination.$name.")<br>\n";
				if (!move_uploaded_file($thisfile['tmp_name'], $destination.$name)) {
					//print "Failed to move file<br>\n";
				}
				else {
					$uploaded[] = $name;
					print "Uploaded - $name<br>\n";
				}
			}
			exit();
		}
		
	}

	// PAGE specific HTML settings
	
	$css = array('forms'); // all CSS needed by this page
	$extraCSS = '
	
#upload_progress {
	margin-bottom: 20px;
}

#uploaded:empty {
	margin-bottom: 0;
}
	'; // extra on page CSS
	
	$js = array('upload'); // all external JavaScript needed by this page
	$extraJS = '

	
'; // extra on page JavaScript
	
	//// Page title	
	//$pageTitleH2 = ($action) ? 'Settings : '.ucwords($action) : 'Settings';
	//$pageTitle = ($action) ? 'Settings : '.ucwords($action) : 'Settings';
	$pageTitleH2 = $pageTitle = "Large file upload";
	$pageClass = 'files';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php 
	if($user->drawGroup() == 'Superuser') { 
	
		echo drawFeedback($feedback,$message);

		if ($action == 'create') { //Create settings
    
		} 
		// -------------------------------------------------------------
		// Edit settings 
        else if ($action == 'edit') { 
		} 
		
		else {
		
			$page_html = '
<p class="instructions">This upload function can be used to upload files directly to the '.$destination_directory.' directory in the silo. It will only work in Firefox and Chrome (and just maybe IE10+)</p>
<div id="upload_progress">
</div>
<div> 
	<form id="" action="" method="post" enctype="multipart/form-data">
		<div>
			<input type="file" name="file[]" id="file" multiple="multiple" />
			<input type="submit" name="action" id="submit" value="Upload" />
		</div>
	</form>
</div>
		
			
			';
			
			echo treelineBox($page_html, "Select files to upload", "blue");
		}
		
	}
	 
	else {
	} 

	?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>