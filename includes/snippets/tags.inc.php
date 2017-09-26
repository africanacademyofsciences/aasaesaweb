<?php 
if (!$tag_type) $tag_type=1; // default to page
if( isset($tags) && is_object($tags) ){
	$tagsHTML=$tags->drawTags($pageGUID, "linklist"); 
	
	// show placeholder tags in edit mode: This needs moving to the class 
	if($mode == 'edit') { 
		?>
		<div class="tags">
		<ul>
			<li><i class="ion-ios-pricetag-outline"></i></li>
			<li><a href="#" title="unclickable">tag placeholder</a></li>
			<li><a href="#" title="unclickable">tag placeholder</a></li>
			<li><a href="#" title="unclickable">tag placeholder</a></li>
			<li class="last"><a href="#" title="unclickable">tag placeholder</a></li>
			<li><a href="#">all tags</a></li>
		</ul>
        </div>
		<?php 
	} 
	else echo $tagsHTML;
}
?>
        
