<?php
defined('VALID_INCLUDE') or die;

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ezSQL.class.php');
global $db;

$i = pathinfo($file_name);
$image_title = $i['filename'];
$image_extension = strtolower($i['extension']);
$product_id = $db->escape($_POST['product_id']);
$productName = $db->escape($_POST['product']);

$max_sort_order = (int)$db->get_var("SELECT MAX(sort_order)+1 FROM store_products_images WHERE product_id = '$product_id'");

$query = "INSERT INTO store_products_images (product_id,sort_order,caption,img_extension) 
			VALUES ($product_id,$max_sort_order,'','".$db->escape($image_extension)."')";
$db->query($query);

$image_number = $db->insert_id;
$image_name = $image_number.'.'.$image_extension;

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/image.class.php');

$image = new Image;
$image->loadFromFile($original_file);
$image->resize('300','min');
$image->write($save_path.$image_name);

$image->resize('190','min'); // product view uses 180*180 boxes
$image->write($save_path.$image_number.'_m.'.$image_extension);

$image->resize('90','min'); // list view uses 80*80 boxes
$image->write($save_path.$image_number.'_sm.'.$image_extension);

$image->resize('45','min'); // related products uses 40*40 boxes
$image->write($save_path.$image_number.'_vsm.'.$image_extension);

@unlink($original_file);
//redirect('/treeline/store/inventory.php?action=organise&product=$productName');
?>