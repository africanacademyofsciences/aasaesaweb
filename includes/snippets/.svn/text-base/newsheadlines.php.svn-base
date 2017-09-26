<?php 
	/*
		News Headlines
	*/

	$query = "SELECT p.*, date_format(p.date_created, '%d-%m-%y') as showdate FROM pages p
				LEFT OUTER JOIN pages_news_display pnd ON p.guid=pnd.guid
				WHERE p.msv=". $siteID ." AND pnd.show=1 ORDER by p.date_created DESC LIMIT 5";
				
	//print "$query<br>";
	if( $newsitems = $db->get_results($query) ){
		$content= '<ul id="newslist" style="padding-right:0px;">'."\n";
		foreach( $newsitems as $item){
			$content .= "\t".'<li><span class="newsdate">'.$item->showdate.'</span><a href="'. $page->drawLinkByGUID($item->guid) .'">'. $item->title .'</a></li>'."\n";
		}
		$content .=  '</ul>'."\n";
	}
	else{
		$content =  '<p>There are no news updates to display.</p>';
	}
	

?>
<div class="panel">
    <?= ($title = $page->drawTitle()) ? '<h3>'.$title.'</h3>' : '<h2>Latest news</h2>'; ?>
    <?=$content?>
</div>
