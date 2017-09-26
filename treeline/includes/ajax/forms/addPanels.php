<?php
	// Add default instructions
	$instruction_class="instructions";
	if (!$instructions) {
		$instructions = $page->drawLabel("tl_paedit_create_message", 'To create a new panel, please complete the form below');
	}	
	if (!$button_text) $button_text = $page->drawLabel("tl_paedit_but_savecon", "Save and Create Content");
	
	$page_html .= '
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="guid" value="'.$guid.'" />
		<input type="hidden" name="mode" value="'.$mode.'" />
		<input type="hidden" name="type" value="'.$template_id.'" />
		<input type="hidden" name="submitted" value="1" />
		<p class="'.$instruction_class.'">'.$instructions.'</p>
		<div>
			<label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
			<input type="text" name="title" id="title" class="text" value="'.$title.'"/>
		</div>
		<fieldset>
			<legend>'.$page->drawLabel("tl_paedit_field_app", "Appearance").':</legend>
	';
	$currentStyle = ($_POST['style']) ? $_POST['style'] : $page->style_id;
	$currentStyle = ($currentStyle) ? $currentStyle : 8;
	$page_html.=$page->drawStyleList($currentStyle, 6);	// The six means draw the styles availble for panels (which have a template id of 6)
	$page_html.='
		</fieldset>    
		<div id="tagsElement" class="">
	';
	//include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/addEditTags.php";
	$page_html.=$tags_html.'
		</div>
		<fieldset class="buttons">		
			<input type="submit" class="submit" value="'.$button_text.'" />
		</fieldset>
	';



?>