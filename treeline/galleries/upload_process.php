<?php

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ezSQL.class.php');
include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/image.class.php');
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");


error_reporting(0); // switch this off in case the image.class start throwing warnings.
$gallery_debug=false;

session_start();

//print "======================<br>Started upload-process<br>\n";
$page = new Page();
$labels=$page->getTranslations(1, $_SESSION['treeline_language'], 2);

// Extract data from the post array
//print "Show POST params<br>\n";
foreach ($_POST as $k=>$v) {
	//print "k($k) = v($v)<br>\n";
	if ($k=="gallery_id") $gallery_id=$v;
	//if ($k=="language") $_SESSION['treeline_language']=$v;
}

// Extract uploaded file details
// There is only 1 file passed each time really so no need to loop this
foreach ($_FILES as $file_array_name=>$filedata) {
	foreach ($filedata as $k => $v) {
		//print "k($k) = v($v)<br>\n";
		if ($k=="name") {
			if (preg_match("/(.*)\.(.*)/", $v, $reg)) {
				$image_title = $reg[1];
				$image_extension = $reg[2];
			}
			//print "Got $image_title, $image_extension from $v<br>";
		}
		if ($k=="tmp_name") {
			$upload_file = $v;
		}
	}
}
// Upload the tmp_file (we'll delete it later
$original_file = $_SERVER['DOCUMENT_ROOT']."/silo/tmp/upload".date("dmYHis", time()).".".$image_extension;
move_uploaded_file($upload_file, $original_file);
//print "copy from $upload_file to $original_file<br>\n";

/*
logit("Got gallery ID($gallery_id)");
logit("Upload from($original_file)");
logit("To image ".$image_title.".".$image_extension);
*/

if ($gallery_id>0) {

	if (file_exists($original_file)) {
	
		$query="SELECT MAX(sort_order) FROM gallery_images WHERE gallery_id = $gallery_id";
		$max_sort_order = (int)$db->get_var($query)+1;
		//logit("set sort order $max_sort_order");
	
		$query = "SELECT main_image_id FROM galleries WHERE id=$gallery_id";
		//$msg.="Check for a main image($query)<br>\n";
		if (!$db->get_var($query)) {
			$set_main_image=true;
			//$msg.="set main image id for this gallery";
		}
	
		$query = "INSERT INTO gallery_images 
			(gallery_id, title, image_extension, sort_order)
			VALUES 
			($gallery_id, '".$db->escape($image_title)."', '".$db->escape($image_extension)."', $max_sort_order)";
		//logit("run insert image($query)");
		if ($db->query($query)) {
		
			$image_number = $db->insert_id;
			$image_name = $image_number.'.'.$image_extension;
			//$msg.="got image name($image_name)\n";
	
			// If the main image id is not set then make it this image.
			if ($set_main_image) {
				$query = "UPDATE galleries SET main_image_id=$image_number WHERE id=".$gallery_id;
				//logit("Set main image($query)");
				$db->query($query);
			}
			
			$save_path = $_SERVER['DOCUMENT_ROOT']."/silo/images/galleries/".$gallery_id."/";
	
			// Save the main image width 800px
			$main_image_width = 800;
			$image = new Image;
			//print "load from ($original_file)<br>";
			$image->loadFromFile($original_file);
			$image->resize($main_image_width);
			$save_file = $save_path.'b_'.$image_name;
			//logit("Save $original_file to $save_file");
			if (!$image->write($save_file)) {
				print "err - failed to save at ".$main_image_width."px";
				//logit("Failed to save file($save_file)");
			}
			else {
				// Save the admin sized copy
				$image = new Image;
				$image->loadFromFile($original_file);
				$image->crop_and_resize('203xc153');
				$save_file= $save_path.'t_'.$image_name;
				//logit("Save $original_file to $save_file");
				if (!$image->write($save_file)) print "Err - Failed to save admin thumbnail";
				else {
					// Save a tiny baby image for something or other
					// probably not need by this treeline
					$image->crop_and_resize('60xc40');
					$save_file = $save_path.'m_'.$image_name;
					//logit("Save cropped file to $save_file");
					if (!$image->write($save_file)) print "Err - Failed to save tiny thumb";
					else print $page->drawGeneric("done");
				}
			}
			@unlink($original_file);
		}
		else print "Err - Failed to add record to database";
	}
	else print "err - no file";
}
else print "err - no gallery ID";

?>