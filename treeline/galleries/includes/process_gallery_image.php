<?php
defined('VALID_INCLUDE') or die;

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ezSQL.class.php');
global $db;

$i = pathinfo($file_name);
$image_title = $i['filename'];
$image_extension = strtolower($i['extension']);
$gallery_id = $db->escape($_POST['gallery_id']);

if ($gallery_id>0) {
	$query="SELECT MAX(sort_order) FROM gallery_images WHERE gallery_id = $gallery_id";
	$max_sort_order = (int)$db->get_var($query)+1;

	$query = "SELECT main_image_id FROM galleries WHERE id=$gallery_id";
	$msg.="Check for a main image($query)<br>\n";
	if (!$db->get_var($query)) {
		$set_main_image=true;
		$msg.="set main image id for this gallery";
	}
	$msg.="set sort order $max_sort_order \n";

	$query = "INSERT INTO gallery_images 
		(gallery_id, title, image_extension, sort_order)
		VALUES 
		($gallery_id, '".$db->escape($image_title)."', '".$db->escape($image_extension)."', $max_sort_order)";
	$msg.="run insert image($query)<br>\n";
	$db->query($query);
	
	$image_number = $db->insert_id;
	$image_name = $image_number.'.'.$image_extension;
	$msg.="got image name($image_name)\n";

	// If the main image id is not set then make it this image.
	if ($set_main_image) {
		$db->query("UPDATE galleries SET main_image_id=$image_number WHERE id=".$gallery_id);
	}
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/image.class.php');

	$image = new Image;
	$image->loadFromFile($original_file);
	$image->resize('800');
	$save_file = $save_path.'b_'.$image_name;
	$msg.="Save file $save_file<br>\n";
	if (!$image->write($save_file)) $msg.="Failed to write file<br>\n";
	
	$image = new Image;
	$image->loadFromFile($original_file);
	$image->crop_and_resize('203xc153');
	$save_file= $save_path.'t_'.$image_name;
	$msg.="Save file $save_file<br>\n";
	if (!$image->write($save_file)) $msg.="Failed to write file<br>\n";
	
	$image->crop_and_resize('60xc40');
	$save_file = $save_path.'m_'.$image_name;
	$msg.="Save file $save_file<br>\n";
	if (!$image->write($save_file)) $msg.="Failed to write file<br>\n";
	
	@unlink($original_file);
}
else $msg.="Process gallery image no image id passed";

if ($msg) {
	/*
	$fp=fopen($_SERVER['DOCUMENT_ROOT']."/log.txt", "at");
	fputs($fp, "\n\n".date("dmY H:i:s", time())."\n".$msg);
	fclose($fp);
	*/
	//mail("phil.redclift@ichameleon.com", $site->name." gallery upload processed", $msg);
}


?>