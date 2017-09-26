<?php 
ob_start(); 

$query="select g.id as gallery_id, g.main_image_id, g.title, g.description as text, 
	gi.description as description,
	concat(gi.gallery_id, '/b_', gi.id,'.', gi.image_extension) as filename, gi.title as image_title, gi.caption, gi.description 
	FROM galleries g
	INNER JOIN gallery_images gi on g.id=gi.gallery_id 
	where g.id = $gallery_id
	AND gi.id=g.main_image_id
	limit 1";
//print "$query<br>\n";
if ($row = $db->get_row($query)) {
	$firstName = $row->filename;
	$firstDesc = $row->description;
	
	$query="SELECT gi.caption, gi.description, 
		concat(gi.gallery_id, '/b_', gi.id,'.', gi.image_extension) as filename 
		FROM galleries g
		INNER JOIN gallery_images gi ON g.id = gi.gallery_id
		WHERE g.id = $gallery_id
		AND gi.id != g.main_image_id
		ORDER BY gi.sort_order
		";
	//print "$query<br>\n";
	$results = $db->get_results($query);
}
?>

<div class="mobile-gallery">

<script type="text/javascript">
	imgArray[0]="/silo/images/galleries/<?=$firstName?>";
	imgText[0]="<?=addslashes($firstDesc)?>";
	<?php
	$i=1;
	if (count($results) && is_array($results)) {
		foreach ($results as $result) {
			print '	imgArray['.$i.']="/silo/images/galleries/'.$result->filename.'";'."\n";
			print '	imgText['.$i.']="'.addslashes($result->description).'";'."\n";
			$i++;
		}
	}
	?>
</script>

<div class="swipe-element iframe-rwd" 
	id="picture-frame" 
    ontouchcancel="touchCancel(event);" 
    ontouchend="touchEnd(event);" 
    ontouchmove="touchMove(event);" 
    ontouchstart="touchStart(event,'picture-frame');" 
    style="background-image: url('/silo/images/galleries/<?=$firstName?>')">
</div>
<div class="picture-controls">
	<p id="pictureText" class="description"><?=$firstDesc?></p>
    <p class="controls clearfix">
	<a class="picture-controls-previous" href="javascript:swipe('picture-frame', 0);">Prev</a>
	<a class="picture-controls-next" href="javascript:swipe('picture-frame', 1);">Next</a>
    </p>
</div>
<div style="clear: both;"></div>
    

</div>

<?php
$replace .= ob_get_contents();
ob_end_clean();
?>