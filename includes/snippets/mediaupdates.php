<?php

	$query = "SELECT f.guid, f.title, f.description, f.name, f.extension, fc.title category,
				IF(date_modified>date_created, date_modified, date_created) order_date
				FROM files f
				LEFT JOIN filetypes_extensions fe ON f.extension=fe.title
				LEFT JOIN filetypes ft ON fe.filetype_id=ft.filetype_id
				LEFT JOIN filetypes_categories fc ON ft.category_id=fc.category_id
				WHERE (fc.title='Video' OR fc.title='Audio') 
				AND f.site_id=$siteID
				
				UNION
				
				SELECT guid, title, meta_description description, name, IF(guid>0,null,null) extension, 
				IF(guid>0,'Gallery',null) category, IF(date_modified>date_created, date_modified, date_created) order_date
				FROM pages
				WHERE msv=$siteID AND template=18
				
				ORDER BY order_date DESC
				";
			//echo nl2br($query);
	if( $mediaitems = $db->get_results($query) ){
		$content =  '<ul class="multimedia">'."\n";
		foreach( $mediaitems as $item){
			if( $item->category!='Gallery' ){
				$content .= "\t".'<li class="'. strtolower($item->category) .'"><a href="/media-player/?guid='. $item->guid .'" title="'. html_entity_decode($item->description) .'">'. html_entity_decode($item->title) .'</a></li>'."\n";
			}else{
				$link = $page->drawLinkByGUID($item->guid);
				$content .= "\t".'<li class="'. strtolower($item->category) .'"><a href="'. $link .'" title="'. html_entity_decode($item->description) .'">'. html_entity_decode($item->title) .'</a></li>'."\n";
			}
		}
		$content .= '</ul>'."\n";
	}else{
		$content = '<p>There are no media updates to display.</p>'."\n";
	}
?>


<div class="panel">
    <?= ($siteID!=$pageGUID) ? '<h3>'.$page->drawTitle().'</h3>' : '<h2>Media updates</h2>'; ?>
    <?=$content?>
</div>