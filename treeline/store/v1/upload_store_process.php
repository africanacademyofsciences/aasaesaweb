<?php

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ezSQL.class.php');
include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/image.class.php');

error_reporting(0); // switch this off in case the image.class start throwing warnings.
$gallery_debug=false;
//$gallery_debug=true;

logit("======================");
logit("Started upload-process");

// Extract data from the post array
logit("Show POST params");
foreach ($_POST as $k=>$v) {
	logit("k($k) = v($v)");
		//print "k($k) = v($v) <br>\n";
	if ($k=="gallery_id") {
		$product_name=$v;
		$query = "select product_id FROM store_products where name = '$product_name'";
		//print "Get prod id ($query)";
		$productID = $db->get_var($query);
	}
	if ($k=="version") $storeVersion = $v;
}

// Extract uploaded file details
// There is only 1 file passed each time really so no need to loop this
logit("Show file params");
foreach ($_FILES as $file_array_name=>$filedata) {
	foreach ($filedata as $k => $v) {
		logit("k($k) = v($v)");
		//print "k($k) = v($v) <br>\n";
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

logit("Got product id($productID)");
logit("Upload from($original_file)");
logit("To image ".$image_title.".".$image_extension);

//print "Got product($productID)<br>\n";
if ($productID>0) {

	if (file_exists($original_file)) {
	
		// Insert product image data to database.
		$query = "SELECT MAX(sort_order)+1 FROM store_products_images WHERE product_id = '$productID'";
		logit ("get max($query)");
		$max_sort_order = (int)$db->get_var($query);

		$query = "INSERT INTO store_products_images 
			(product_id,sort_order,caption,img_extension) 
		VALUES 
			($productID, $max_sort_order, '', '".$db->escape($image_extension)."')
			";
		$db->query($query);

		$image_number = $db->insert_id;
		logit("create image($image_number)");
		if ($image_number>0) {

			$image_name = $image_number.'.'.$image_extension;
			logit("got image name($image_name)");
	
			$save_path = $_SERVER['DOCUMENT_ROOT']."/store/".$storeVersion."/images/".$productID."/";
			//print "save path($save_path)<br>\n";
			if (!file_exists($save_path)) {
				//print "directory does not exist!!!<br>\n";
				@mkdir($save_path);
				//if (!file_exists($save_path)) print "directory still does not exist!!!<br>\n";
			}
	
			// Save the main image width 800px
			$main_image_width = 800;
			$image = new Image;
			//print "load from ($original_file)<br>";
			$image->loadFromFile($original_file);
			$image->resize($main_image_width);
			$save_file = $save_path.$image_name;
			logit("Save $original_file to $save_file");
			if (!$image->write($save_file)) {
				print "err - failed to save at ".$main_image_width."px to file(".$save_file.")";
				logit("Failed to save file($save_file)");
			}
			else {
				// Save the wide store sized copy
				unset($image);
				$image = new Image;
				$image->loadFromFile($original_file);
				$image->resize('245x0');
				$save_file= $save_path.$image_number.'_large.'.$image_extension;
				logit("Save $original_file to $save_file");
				if (!$image->write($save_file)) print "Err - Failed to save large image";
				else {

					// Save the wide store sized copy
					unset($image);
					$image = new Image;
					$image->loadFromFile($original_file);
					$image->resize('90x0');	
					$save_file= $save_path.$image_number.'_small.'.$image_extension;
					logit("Save $original_file to $save_file");
					if (!$image->write($save_file)) print "Err - Failed to save small image";
					else {
					
						//print "saved image at 90x0<br>";
						
						// Save a homepage size copy of the image too
						//unset($image);
						//$image = new Image;
						//$image->loadFromFile($original_file);
						//$image->crop_and_resize('90xc145');
						//$save_file= $save_path.$image_number.'_hmp.'.$image_extension;
						//if (!$image->write($save_file)) print "Err - Failed to homepage thumb(".$save_file.")";
						
						// Save a tiny baby image for something or other
						// probably not need by this treeline
						unset($image);
						$image = new Image;
						$image->loadFromFile($original_file);
						$image->resize('40x0');
						$save_file= $save_path.$image_number.'_thumb.'.$image_extension;
						logit("Save cropped file to $save_file");
						if (!$image->write($save_file)) print "Err - Failed to save thumb(".$save_file.")";
						else {
							print "done";
						}
					}
				}
			}
			@unlink($original_file);
		}
		else print "Err - Failed to add record to database($query)";
	}
	else {
		logit("No file found to upload");
		print "err - no file";
	}
}
else {
	logit("Process no product ID passed");
	print "err - no product ID";
}


function logit($s) {
	global $gallery_debug;
	if ($gallery_debug) {
		$msg = date("d-m-Y H:i:s", time())." - ".$s."\n";
		$fp = fopen($_SERVER['DOCUMENT_ROOT']."/treeline/store/upload_log.txt", "at");
		if ($fp) {
			fputs($fp, $msg);
			fclose($fp);
		}
		else {
			print "Failed to open log file<br>\n";
			mail("phil.redclift@ichameleon.com", "Gallery debug info", $msg);
		}
	}
}


?>