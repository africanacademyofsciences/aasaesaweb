<?php
	$page_html.='
	<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
	<fieldset>
        <legend>'.$page->drawLabel("tl_paedit_RSS_create_title", "Create RSS panel").'</legend>
        <input type="hidden" name="action" value="'.$action.'" />
        <input type="hidden" name="guid" value="'.$guid.'" />
        <input type="hidden" name="mode" value="'.$mode.'" />   
		<input type="hidden" name="submitted" value="1" />
		<input type="hidden" name="type" value="'.$template_id.'" />
        <p class="instructions">'.$page->drawLabel("tl_paedit_RSS_create_msg", "To create a new RSS panel, please enter the full address (url) of the feed").'</p>
        <div>
			<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
			<input type="text" id="title" name="title" value="'.$title.'" />
        </div>
        <div>
			<label for="treeline_content">'.$page->drawLabel("tl_paedit_RSS_field_URL", "Feed URL").':</label>
			<input type="text" id="treeline_content" name="treeline_panelcontent" value="'.$treeline_panelcontent.'" />
        </div>

		<fieldset class="buttons">		
        	<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_save", "Save")).'" />
        </fieldset>
    </fieldset>
	</form>
	';
	//echo treelineBox($page_html, "Create RSS panel", "blue");
?>