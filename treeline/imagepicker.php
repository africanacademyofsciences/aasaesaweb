<?






	// IMPORTANT NOTE -=-------
	
	
	// THIS FILE IS NOT USED ANY LONGER, THE REAL ONE IS LOCATED AT:
		// /treeline/includes/tiny_mc3/jscripts/tiny_mce/plugins/new_image/dialog.php
	
	// IMPOROTANT NOTE -------------





























	session_start();
	
	$siteID = $_SESSION['treeline_user_site_id'];
		

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");
	// Set the debugging on
	$DEBUG = (read($_GET,'debug',false) !== false)?true:false;	
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");
	
		
	

	// Now, should we rewrite this as an imagepicker class? It's only ever used here, so it seems unnecessary...

	// We're either listing all images, or selecting one [at the correct size] to insert:
	$action = read($_REQUEST,'action','list');

	$category = read($_REQUEST,'category','xx');	
	$search = read($_REQUEST,'search','');
	$source = read($_REQUEST,'source',0);

	if ($action == 'list') {
	
		$perpage = 8;
		$page = read($_REQUEST,'page',1);	
		
		$previous = read($_REQUEST,'previous',false);
		$next = read($_REQUEST,'next',false);				
	
		// This indicates whether or not we need a "next page" option
		$nextpage = false;
	
		if ($previous) {
			// if we're trying to get back to the previous page
			$page = $page-1;
		}
		else if ($next) {
			// If we're trying to go to the next page
			$page = $page+1;
		}

	}
	else if ($action == 'select') {
		$name = read($_REQUEST,'name','');	
	}

	function drawCategories($category) {
		global $db,$siteID;
		
		$query = "SELECT ic.title as category
					FROM images i
					LEFT JOIN images_categories ic ON i.category=ic.id
					WHERE i.site_id='$siteID'
					GROUP BY ic.title
					ORDER BY ic.title";
		//echo $query;
		$html = '';
		
		if( $categories = $db->get_results($query) ){
			foreach ($categories as $c) {
				$selected = ($c->category == $category)?'selected="selected"':'';
				$html .= "\t".'<option value="'.htmlentities($c->category).'" '.$selected.'>'.htmlentities($c->category).'</option>'."\n";
			}
		}
		return $html;
	}
	
	function drawImages($category) {
		global $db,$siteID;
		global $page, $perpage;
		global $category,$search;
		
		global $nextpage;
		
		$query = "SELECT i.guid, i.name, i.extension, i.title, s.filename, s.width, s.height 
					FROM images i 
					LEFT JOIN images_categories ic ON i.category=ic.id
					LEFT JOIN images_sizes s ON s.guid = i.guid";
		
		if ($search) {
			// Note that search terms take precendence over the category -- it isn't currently possible to search on both
			$query .= " WHERE i.title like '%".$search."%'";
		}
		else if ($category != 'xx') {
			$category = $db->escape($category);
			$query .= " WHERE ic.title = '$category'";
		}

	
		$query .= " GROUP BY s.guid ORDER BY i.title LIMIT ".($page-1)*$perpage.", ".($perpage+1);
		// Note that we select one more image than we require. This is used to ascertain whether we need a "next page".

		$html = '';
		
		if ($images = $db->get_results($query)) {
			if (count($images) > $perpage) {
				// If we've got more images from the database than we need, we've effectively pulled the "extra" image above
				// This indicates that we need a "next page", but we don't need this extra image -- so get rid of it
				array_pop($images);
				$nextpage = true;
			}
			$max = 100;
			foreach ($images as $image) {
				$formfactor = ($image->width > $image->height) ? 0 : 1;

				if($formfactor==0){
					$scale = ($max/$image->width);
					$new_width = $max;
					$new_height = round($image->height * $scale);
				}else{
					$scale = ($max/$image->height);
					$new_height = $max;
					$new_width = round($image->width * $scale);		
				}
				$html .= '<div class="image">'."\n";
				$html .= '<div class="frame"><a href="imagepicker.php?action=select&amp;name='.urlencode($image->name).'"><img src="/silo/images/'.$image->filename.'" alt="'.$image->title.'" title="Use '.$image->title.'" width="'. $new_width .'" height="'. $new_height .'" /></a></div>'."\n";
				$html .= '<div class="name">'.$image->title.'</div>'."\n";
				$html .= '<a href="imagepicker.php?action=select&amp;name='.urlencode($image->name).'">Use this image</a>';
				$html .= '</div>'."\n";
			}
		}
		else {
			$html .= '<p class="alert">No images match your search!</p>'."\n";
		}
		return $html;
	}
	

	function getImageTag($w, $h) {
		$img_tag='';
		print "get tag line for $w, $h<br>";
		if($w == 190 && $h == 92) $img_tag = 'This size is perfect for homepage panels or right hand panels';
		else if($w == 230) $img_tag='This image is the correct width for right panel buttons';
		else if($w == 204) $img_tag='This image is the correct width for left panel buttons';
		else if($h == 204) $img_tag='This image is the correct height for landing page or multimedia page headings';
		else if($w == 446) $img_tag='This iimage is the best size for 3 col layout center columns';

		if ($img_tag) $img_tag = '<strong>'.$img_tag.'</strong>';
		return $img_tag;
	}
	
	function drawSizes($name) {
		global $db;
		
		$query = "SELECT * FROM images i LEFT JOIN images_sizes s ON s.guid=i.guid WHERE i.name = '$name' ORDER BY s.width, s.height";
		$images = $db->get_results($query);
		
		$html = '';
		foreach ($images as $image) {
			$img_tag= getImageTag($image->width, $image->height);
			$link = "ImageDialog.insert('/silo/images/".$image->filename."','test title')";
			$html .= '<div class="size">';
			//$html .= '	<a href="#" onclick="insertImage(\''.$image->filename.'\',\''.$db->escape($image->description).'\',\''.$image->width.'\',\''.$image->height.'\')" title="Insert '.$image->description.'"><img src="/silo/images/'.$image->filename.'" alt="'.$image->description.'" width="'.$image->width.'" height="'.$image->height.'"/></a>';
			$html .= '	<a href="#" onclick="'.$link.'" title="Insert '.$image->description.'"><img src="/silo/images/'.$image->filename.'" alt="'.$image->description.'" width="'.$image->width.'" height="'.$image->height.'"/></a>';
			$html .= '	<div class="details">';
			$html .= '		<p>'.$image->width.' x '.$image->height.'<br /><a href="#" onclick="'.$link.'">Use this image size</a>';
			if ($img_tag) $html.='<br />'.$img_tag;
			$html .= '</p>'."\n";
			$html .= '	</div>';
			$html .= '</div>';
		}
		return $html;

	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="robots" content="noindex,nofollow" />
<title>Treeline | Select an image</title>
<style type="text/css">
@import url('/treeline/style/global.css');
@import url('/treeline/style/imagePicker.css');
</style>
	<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce_popup.js"></script>
	<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/utils/mctabs.js"></script>
	<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/utils/form_utils.js"></script>
	<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/utils/validate.js"></script>
<script type="text/javascript" src="/treeline/behaviour/imagePick3.js"></script>
<script type="text/javascript">
<?php

// We are adding this existing image to the gallery in the session
if ($_SESSION['gallery_page_guid'] && $action == 'select') {
	
	$image_guid = $db->get_var("SELECT guid FROM images WHERE name = '{$_REQUEST['name']}'");
	echo <<<EOT

	window.opener.location = "/treeline/galleries/?action=add_existing&page_guid={$_SESSION['gallery_page_guid']}&image_guid=$image_guid";
	tinyMCEPopup.close();
	
EOT;
}
?>

	function setCategory(t) {
		if (t.value != 'xx') {
			document.location = 'imagepicker.php?category=' + t.value;
		} else {
			document.location = 'imagepicker.php';
		}
	}
	
	function resize(){
		window.resizeTo(725,625);
		return true;
	}
</script>
</head>
<body id="treeline" class="imagePicker" style="margin: 0px" <?php if ($action == 'select') { ?>onload="tinyMCEPopup.executeOnLoad('init();'); resize()"<?php }else{ ?>onload="resize()"<?php } ?>>
<form action="imagepicker.php" method="get" id="imagepicker">
<fieldset>
	<input type="hidden" name="action" value="<?=$action?>" />

	<div id="structure">
		<div id="inside_structure">
			<img src="/treeline/img/imagepicker/images.gif" alt="Images" style="margin: 0px 34px 0px 10px; float: left" />
			<?php if ($action == 'list') { ?>
			<div style="float: left">
				<div class="field">
					<label for="category">Browse by category</label>
					<select name="category" onchange="setCategory(this)">
						<option value="xx">All categories</option>
						<?=drawCategories($category)?>
					</select>
					<input type="button" class="button" name="browse" value="Browse" />
				</div>
				<div class="field">
					<label for="search">Search by image name</label>
					<input type="text" class="text" name="search" value="<?=$search?>" />
					<input type="submit" class="button" name="submit" value="Search" />
				</div>				
			</div>	
			<?php } ?>
		</div>
		<div id="content">
<?php if ($action == 'list') { ?>

		<input type="hidden" name="page" value="<?=$page?>" />
			<div id="gallery">
				<?=drawImages($category)?>
			</div>
			<div id="controls">
				<?php if ($page > 1) { ?>
					<input type="submit" id="previous" class="hi" name="previous" value="Previous page" />
				<?php } else { ?>
					<input type="button" id="previous" class="lo" name="previous" value="Previous page" disabled="disabled" />
				<?php } ?>
				<?php if ($nextpage) { ?>
					<input type="submit" id="next" class="hi" name="next" value="Next page" />
				<?php } else { ?> 
					<input type="button" id="next" class="lo" name="next" value="Next page" disabled="disabled" />				
				<?php } ?>
			</div>
		

<?php } else if ($action == 'select') { ?>

		<!-- TinyMCE fields: -->
		<input type="hidden" name="align" value="" />
		<input type="hidden" name="src" value="" />
		<input type="hidden" name="alt" value="" />
		<input type="hidden" name="border" value="" />
		<input type="hidden" name="vspace" value="" />
		<input type="hidden" name="hspace" value="" />
		<input type="hidden" name="width" value="" />
		<input type="hidden" name="height" value="" />
		<!-- end of TinyMCE fields -->
		<div style="padding: 0 20px 20px;float:left;">
			<p style="font-weight: bold;">Choose which image size to use.</p>
			<?=drawSizes($name)?>
		</div>
<?php } ?>
		</div>
	</div>
</fieldset>    
</form>
</body>
</html>