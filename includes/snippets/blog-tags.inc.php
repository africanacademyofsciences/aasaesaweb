<?php 
if (!$tag_type) $tag_type=1; // default to page
if( isset($tags) && is_object($tags) ){
	$tagsHTML=$tags->drawTags($pageGUID, "bloglist"); 
	
	// show placeholder tags in edit mode: This needs moving to the class 
	if($mode == 'edit') { 
		?>
        <div class="info-tags">
            <ul>
                <li><a href="#" title="unclickable">tag placeholder</a></li>
                <li><a href="#" title="unclickable">tag placeholder</a></li>
                <li><a href="#" title="unclickable">tag placeholder</a></li>
            </ul>
        </div>
		<?php 
	}
	else {
		echo $tagsHTML;
	}
}
?>
        
