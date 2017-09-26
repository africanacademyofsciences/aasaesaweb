<?php

$gallery_id	= $_GET['gallery'];
$image_id	= $_GET['image'];

$viewing_galleries	= (!$gallery_id && !$image_id);
$viewing_gallery	= ($gallery_id && !$image_id);
$viewing_image		= ($gallery_id && $image_id);


// Page specific options

$pageClass = 'page'; // used for CSS usually

$css = array('page', 'lytebox'); // all attached stylesheets



if($page->style != NULL){
	$css[] = $page->style;
}
$extraCSS = ''; // extra page specific CSS

$js = array('jquery', 'lytebox'); // all atatched JS behaviours

$extraJS = '

/* The thumbnail hyperlinks are to a page that displays the image.
We need to change these links to point to the exact image src
before lytebox initialises, so that it knows what to show in its box. */

$(document).ready(function()
{
	$("a.lyteshow").each(function()
	{
		var thumbSrc = $(this).children("img").attr("src");
		var imageSrc = thumbSrc.replace(/t_/, "b_");
		
		$(this).attr("href", imageSrc);
	});
});


'; // etxra page specific  JS behaviours

$currentPage = read($_REQUEST,'page',1); // pagination value
$perPage = 12;



// Needed to add this so that the page worked
$tags = new Tags($site->id, 5);

$tags->setMode($page->getMode());
$header_img = new HTMLPlaceholder();
$header_img->load($siteID, 'header_img');
if (!$header_img->draw()) {
	$header_img->load($siteData->primary_msv, 'header_img');
	if (!$header_img->draw()) {
		$header_img->load(1, 'header_img');
	}
}
$header_img->setMode("view");



include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	

?>	


	<div id="primarycontent">
	
<div id="landing" class="level-1">
	<div class="landing-panel first">
		<h3><a href="#">Click to see this thing</a></h3>
		<p>
			<a href="#">
				<img src="http://magref/silo/images/homepage-block-1_190x92.jpg" border="0" alt="" width="190" height="92" />
			</a>
		</p>
		<p>
			Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Mauris nisi
			<a href="#">dui, ultrici</a>es ac, lacinia ut, imperdiet in, mi. Quisque ac eros. Quisque quam.
		</p>
	</div>
	<div class="landing-panel">
		<h3>Header for this item</h3>
		<p>
			Mauris nisi dui, ultricies ac, lacinia ut, imperdiet in, mi. Quisque ac eros. Quisque quam.</p>
	</div>
</div>
	
	
			
			<?php
			// ----------------------------- Viewing all galleries -----------------------------
			if ($viewing_galleries) {
			
				?><h1>Galleries</h1><?php
			
				/* Get all the galleries and their details,
				if there is no main image, choose the first. */
				$galleries_query = 
				"
					SELECT
					id, type, sort_order, title,
					IF (main_image_id, main_image_id, (
						SELECT MIN(id)
						FROM gallery_images
						WHERE gallery_images.gallery_id = galleries.id
					)) AS main_image_id
					FROM galleries
					WHERE ((
						SELECT COUNT(id) 
						FROM gallery_images
						WHERE gallery_id = galleries.id
					) > 0)
					AND live = 1
					ORDER BY sort_order
				";
				
				$galleries = $db->get_results("$galleries_query LIMIT ".getQueryLimits($perPage, $currentPage));
				$total = $db->query($galleries_query);
				$total = $db->num_rows;
				
				if ($galleries)
				{
					foreach ($galleries as $gallery)
					{					
						if ($gallery->main_image_id)
							$main_image = '/silo/images/galleries/'.$gallery->id.'/t_'.$gallery->main_image_id.'.jpg';
						else
							$main_image = '/img/no_picture_big.gif';
					?>
					<div class="gallery_box">
						<div class="big_image">
							<div class="tape tr"></div>
							<a href="/galleries/?gallery=<?=$gallery->id?>" title="View gallery">
								<img src="<?=$main_image?>" alt="" />
							</a>
							<div class="tape bl"></div>
						</div>
						<h5><?=htmlentities($gallery->title)?></h5>
					</div>
			<?php
					}
					echo drawPagination($total, $perPage, $currentPage, '/galleries/');
				}
				else
				{
					?>
					<div class="feedback error">
					<h3>Error</h3>
					There are no image galleries
					</div>
					<?php
				}				
			}
			
			// ----------------------------- Viewing a specific gallery -----------------------------
			elseif ($viewing_gallery)
			{
				$gallery = $db->get_row("SELECT * FROM galleries WHERE id = ".$db->escape($gallery_id)." AND live = 1");
				
				if (!$gallery) redirect('/galleries/');
			
				?><h1 class="compact"><?=htmlentities($gallery->title)?></h1>
				<p class="compact">&lsaquo; <a href="/galleries/">View all galleries</a></p>
				<p><?=nl2br(htmlentities($gallery->description))?></p><?php
				
				$gallery_images = $db->get_results
				("
					SELECT *
					FROM gallery_images
					WHERE gallery_id = ".$db->escape($gallery_id)."
					ORDER BY sort_order ASC
				");
				
				if ($gallery_images)
				{
					foreach ($gallery_images as $image) 
					{
						$image_title = htmlentities($image->title);
						$image_description = htmlentities($image->description);
					?>
					<div class="gallery_box">
						<div class="big_image">
							<div class="tape tr"></div>
							<a href="/galleries/?gallery=<?=$gallery_id?>&amp;image=<?=$image->id?>" class="lyteshow" rel="lyteshow"
							title="<?=$image_description?>">
								<img src="/silo/images/galleries/<?=$gallery_id?>/t_<?=$image->id?>.jpg" alt="<?=$image_title?>" />
							</a>
							<div class="tape bl"></div>
						</div>
						<h5><?=$image_title?></h5>
					</div>
			<?php
					}
				}
				else
				{
					?>
					<div class="feedback error">
					<h3>Error</h3>
					There are no images in this gallery
					</div>
					<?php
				}
			}
			
			
			// ----------------------------- Viewing a specific image within a gallery -----------------------------
			elseif ($viewing_image)
			{				
				$image = $db->get_row("SELECT * FROM gallery_images WHERE id = ".$db->escape($image_id));
				
				$image_title = htmlentities($image->title);
				$image_description = nl2br(htmlentities($image->description));
				
				$image_src = '/silo/images/galleries/'.$gallery_id.'/b_'.$image_id.'.jpg';		

				if (!file_exists($_SERVER['DOCUMENT_ROOT'].$image_src)) redirect('/galleries/');
				?>
				<h1><?=$image_title?></h1>
				
				<?php if ($image_description) { ?>
				<p><?=$image_description?></p>
				<?php } ?>
				
				<img src="<?=$image_src?>" alt="<?=$image_title?>" class="border1" width="100%" />
				<p>&lsaquo; <a href="/galleries/?gallery=<?=$gallery_id?>">Back to gallery</a></p>
				
				<?php
			}
			?>
			


<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>
