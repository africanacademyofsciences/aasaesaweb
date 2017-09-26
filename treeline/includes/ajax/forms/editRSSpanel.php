<?php
	$content = new HTMLPlaceholder();
	$content->load($guid,'panelcontent');
	$page_html .= '
        <fieldset>
            <input type="hidden" name="action" value="edit" />
            <input type="hidden" name="guid" value="'.$guid.'" />
            <input type="hidden" name="mode" value="'.$mode.'" />
			<input type="hidden" name="submitted" value="1" />
            <p class="instructions">'.$page->drawLabel("tl_paedit_RSS_message1", "To edit an RSS panel, please enter the full address (url) of the feed").'</p>
			<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
			<input type="text" name="title" id="title" value="'.($title?$title:$panel->title).'"/><br />
			<label for="treeline_content">'.$page->drawLabel("tl_paedit_RSS_field_URL", "Feed URL").':</label>
			<input type="text" name="treeline_panelcontent" id="treeline_panelcontent" value="'.($treeline_panelcontent?$treeline_panelcontent:$content->content).'"/>
			<fieldset class="buttons">		
            	<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_save", "Save")).'" />
            </fieldset>
        </fieldset>
	';
	
		
	$rssFeed = ($treeline_panelcontent)?$treeline_panelcontent:$content->content;
	//print "check feed url($rssFeed)<br>\n";
    $rssData = drawRSSFeed($rssFeed); 
	if ($rssData) {
		//print "got data($rssData)<br>\n";
		$page_html.='<h3>'.$page->drawLabel("tl_paedit_RSS_preview", "RSS Panel Content preview").'</h3>'.$rssData;
	}
	else if ($rssFeed) $page_html.='<p>'.$page->drawLabel("tl_paedit_RSS_no_data", "This RSS feed returned no data").'</p>';
?>