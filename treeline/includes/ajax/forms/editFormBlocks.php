<?php

// Process stuff on the fly on this page?


// Block/Field/Form status flags.
// A - Active 	-- visible on the form
// H - Hidden 	-- hidden, valid but not shown on the form
// X - Deleted	-- As if not there but don't want to delete as may be data associated with it that 
// 				   some moron will decide they actually need at a later date.

$block_id = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST, "bid", 0);
if ($block_id) $block_title = $db->get_var("SELECT title FROM forms_blocks WHERE id=$block_id");

$field_id = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST, "ffid", 0);
if ($field_id) $form->field->loadByID($field_id);

if ($action == "editform" && 
	$_SERVER['REQUEST_METHOD']=="GET" ||
	($_SERVER['REQUEST_METHOD']==="POST" && count($message) && $feedback!="success")
	) {

	
	if (isset($_GET['addblock'])) {
		$block_form_html='
        	<!-- Add new block form -->
            <form method="post" id="form-'.$form->id.'-add-block">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="addblock" value="1" />
				<input type="hidden" name="fid" value="'.$form->id.'" />
				<label for="f_title">Block title</label>
				<input type="text" name="title" id="f_title">
				<label for="f_submit" style="visibility:hidden;">Submit</label>
				<input id ="f_submit" type="submit" class="submit" value="Add block">
			</fieldset>
			</form>
            <!-- // end new block form -->
			';
	}

	else if (isset($_GET['editblock'])) {
		$block_form_html='
        	<!-- Edit block form -->
            <form method="post" id="form-'.$form->id.'-del-block">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="editblock" value="1" />
				<input type="hidden" name="bid" value="'.$_GET['bid'].'" />
				<input type="hidden" name="fid" value="'.$form->id.'" />
				<label for="f_title">Block title</label>
				<input type="text" name="title" id="f_title" value="'.$db->get_var("SELECT title FROM forms_blocks WHERE id=".$_GET['bid']).'" />
				<label for="f_submit" style="visibility:hidden;">Submit</label>
				<input id ="f_submit" type="submit" class="submit" value="Save block">
			</fieldset>
			</form>
            <!-- // end new block form -->
			';
	}

	else if (isset($_GET['deleteblock'])) {
		$block_form_html='
        	<!-- Delete block form -->
			<p class="instructions"><strong>Warning: </strong> deleting this block will delete all associated fields and you will no longer be able to access any submitted data associated with these fields.</p>
            <form method="post" id="form-'.$form->id.'-del-block">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="deleteblock" value="1" />
				<input type="hidden" name="bid" value="'.$_GET['bid'].'" />
				<input type="hidden" name="fid" value="'.$form->id.'" />
				<label for="f_title">Block title</label>
				<input type="text" name="title" id="f_title" value="'.$block_title.'" readonly="readonly" />
				<label for="f_submit" style="visibility:hidden;">Submit</label>
				<input id ="f_submit" type="submit" class="submit" value="Delete block">
			</fieldset>
			</form>
            <!-- // end new block form -->
			';
	}

	else if (isset($_GET['addfield']) || isset($_GET['editfield'])) {
		$type = $_POST['type']?$_POST['type']:$form->field->type;
		$required = isset($_POST['required'])?$_POST['required']:$form->field->required;
		$field_form_title = (isset($_GET['addfield'])?"Add field to ":"Edit field on ").$block_title;
		$field_form_html='
        	<!-- Add new field form -->
            <form method="post" id="form-'.$block_id.'-add-field">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="'.(isset($_GET['addfield'])?'addfield':'editfield').'" value="1" />
				<input type="hidden" name="fid" value="'.$form->id.'" />
				<input type="hidden" name="bid" value="'.$block_id.'" />
				<input type="hidden" name="ffid" value="'.$field_id.'" />
				<label for="f_label">Field label</label>
				<input type="text" name="label" id="f_label" value="'.(isset($_POST['label'])?$_POST['label']:$form->field->label).'" />
			';
		if (isset($_GET['addfield'])) {
			$field_form_html.='
				<label for="f_name">Field name</label>
				<input type="text" name="name" id="f_name" value="'.(isset($_POST['name'])?$_POST['name']:'').'" />
			';
		}
		$field_form_html.='
				<label for="f_type">Field type</label>
				<select name="type" id="f_type">
					<option value="text"'.($type=="text"?' selected="selected"':'').'>Text input field</option>
					<option value="textarea"'.($type=="textarea"?' selected="selected"':'').'>Text area input box</option>
					<option value="select"'.($type=="select"?' selected="selected"':'').'>Drop down list</option>
					<option value="checkbox"'.($type=="checkbox"?' selected="selected"':'').'>Checkbox</option>
					<option value="paragraph"'.($type=="paragraph"?' selected="selected"':'').'>Plain text</option>
					<option value="radio"'.($type=="radio"?' selected="selected"':'').'>Radio button</option>
					<option value="file"'.($type=="file"?' selected="selected"':'').'>File upload</option>
					<option value="captcha"'.($type=="captcha"?' selected="selected"':'').'>Captcha test</option>
				</select>
				<label for="f_req">Required field</label>
				<input type="checkbox" id="f_req" name="required" '.($required==1?'checked="checked"':'').' value="1" />
				<label for="f_hide">Hide this field</label>
				<input type="checkbox" id="f_hide" name="hidden" '.($form->field->status=='H'?'checked="checked"':'').' value="1" />
				<label for="f_submit" style="visibility:hidden;">Submit</label>
				<input id ="f_submit" type="submit" class="submit" value="'.(isset($_GET['addfield'])?"Add field":"Update field").' field">
			</fieldset>
			</form>
            <!-- // end new block form -->
			';
		$help_text_id = 112;
		/*
		if (isset($_GET['addfield'])) {
			$field_form_html.='
				<form method="post" id="add-special-field">
				<fieldset>
					<label for="f_special">Add a special field</label>
					<select name="type" id="f_special">
						<option value="email">Newsletter signup</option>
					</select>
					<label for="f_submit" style="visibility:hidden;">Submit</label>
					<input id ="f_submit" type="submit" class="submit" value="'.(isset($_GET['addfield'])?"Add field":"Update field").' field">
				</fieldset>
				</form>
			';
		}
		*/

	}
	

	else if (isset($_GET['deletefield'])) {
		$field_form_html='
        	<!-- Delete field form -->
			<p class="instructions"><strong>Warning: </strong>you will no longer be able to access any submitted data associated with this fields.</p>
            <form method="post" id="form-'.$form->id.'-del-field">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="deletefield" value="1" />
				<input type="hidden" name="bid" value="'.$block_id.'" />
				<input type="hidden" name="fid" value="'.$form->id.'" />
				<input type="hidden" name="ffid" value="'.$field_id.'" />
				<label for="f_title">Field title</label>
				<input type="text" name="title" id="f_title" value="'.$form->field->label.'" readonly="readonly" />
				<label for="f_submit" style="visibility:hidden;">Submit</label>
				<input id ="f_submit" type="submit" class="submit" value="Delete field">
			</fieldset>
			</form>
            <!-- // end delete field form -->
			';
	}


	else if (isset($_GET['fieldoptions'])) {
		$field_form_options_html = '
        	<!-- Edit field options -->
			<p class="instructions">Use the top input boxes to add new values or delete any you no longer wish to use.</p>
            <form method="post" id="form-'.$form->id.'-field-opts">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="fieldoptions" value="1" />
				<input type="hidden" name="bid" value="'.$block_id.'" />
				<input type="hidden" name="fid" value="'.$form->id.'" />
				<input type="hidden" name="ffid" value="'.$field_id.'" />
				<label for="value-0">Option text</label>
				<input type="text" class="field-option" id="title-0" name="title-0" value="'.$_POST['title-0'].'" />
				<label for="value-0" class="option-value-label">value (optional)</label>
				<input type="text" class="field-option" id="value-0" name="value-0" value="'.$_POST['value-0'].'" />
			';
		// Collect any current options relating to this field
		$query = "SELECT id, value, title, data FROM sites_options WHERE name='field-".$field_id."' ORDER BY title";
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			foreach($results as $result) {
				$field_form_options_html .= '
					<label for="value-'.$result->id.'">Option text</label>
					<input type="text" class="field-option" id="title-'.$result->id.'" name="title-'.$result->id.'" value="'.($_POST['title-'.$result->id]?$_POST['title-'.$result->id]:$result->title).'" />
					<label for="value-'.$result->id.'" class="option-value-label">value (optional)</label>
					<input type="text" class="field-option" id="value-'.$result->id.'" name="value-'.$result->id.'" value="'.($_POST['value-'.$result->id]?$_POST['value-'.$result->id]:$result->value).'" />
				';
			}
		}
		$field_form_options_html .= '
				<label for="f_submit" style="visibility:hidden;">Submit</label>
				<input id ="f_submit" type="submit" class="submit" value="Save options">
			</fieldset>
			</form>
            <!-- // end field options form -->
			';
		
	}

}

// Collect blocks on this form.
if (!$block_form_html) {
	$query = "SELECT fb.*, 
		(
			SELECT count(*) FROM forms_fields 
			WHERE block_id=fb.id
			AND `status`<>'X'
		) AS fields
		FROM forms_blocks fb
		WHERE fb.form_id=".$form->id." 
		AND fb.`status`='A' 
		ORDER BY fb.sort_order
		";
	//print "$query<br>\n";
	
	if ($results = $db->get_results($query)) {
		foreach ($results as $result) {
			$previewlink = $editlink = $editfieldlink = $deletelink = $no_link = '<span class="no-action"></span>';

			$previewlink = '<a '.$help->drawInfoPopup("Preview this block").' class="preview" href="/treeline/forms/?fid='.$form->id.'&amp;bid='.$result->id.'&amp;action=editform&amp;previewblock"></a>';
			$editlink = '<a '.$help->drawInfoPopup("Edit block data").' class="edit" href="/treeline/forms/?fid='.$form->id.'&amp;bid='.$result->id.'&amp;action=editform&amp;editblock"></a>';
			$editfieldlink = '<a '.$help->drawInfoPopup("Edit block fields").' class="edit-form" href="/treeline/forms/?fid='.$form->id.'&amp;bid='.$result->id.'&amp;action=editform&amp;editfields"></a>';
			$deletelink = '<a '.$help->drawInfoPopup("Delete this block").' class="delete" href="/treeline/forms/?fid='.$form->id.'&amp;bid='.$result->id.'&amp;action=editform&amp;deleteblock"></a>';

			$block_form_html.='<li id="mm_'.$result->id.'" class="page-item1 no-nest sort-handle">
			<table border="0" cellpadding="0" cellspacing="0" class="tl_list" style="width:627px;"><tr>
				<td class="title"><span '.$help->drawInfoPopup("Click and drag this item to move it").'>'.($result->title.$field_count).'</span></td>
				<td class="field-count">'.($result->fields+0).'</td>
				<td class="pm-right action">'.$previewlink.$editlink.$editfieldlink.$deletelink.'</td>
				</tr>
			</table>
			</li>
			';
		}
		if ($block_form_html) $block_form_html='
		<form method="post" id="treeline">
		<fieldset>
		<input type="hidden" name="action" value="'.$action.'" />
		<input type="hidden" name="saveblocks" value="1" />
		<input type="hidden" name="fid" value="'.$form->id.'" />
		<input type="hidden" id="mm_content" name="mm_content" value="" />
		<p class="instructions">You must press submit on this page to save any changes to the block order</p>
		<div id="th">
			<p>
			<span class="title">Block name</span>
			<span class="preview">Manage</span>
			<span class="field-count">Fields</span>
			</p>
		</div>
		<div class="menu-wrap-wrap">
		<div class="menu-wrap">
			<ul id="mm" class="page-list">
			'.$block_form_html.'
			</ul>
		</div>
		</div>
		<fieldset class="buttons">
			<input type="submit" class="submit menu-submit" value="Submit" />
		</fieldset>
		</fieldset>
		</form>
		';
	}
	else $block_form_html.='<p>No blocks have been set up yet</p>';
	$block_form_html = '<p>
		<a href="/treeline/forms/?action=editform&fid='.$form->id.'&addblock">Add a new block</a>
		|
		<a href="/treeline/forms/?fid='.$form->id.'">Manage form</a>
	</p>
	'.$block_form_html;

	// ***************************************************************************	
	// If we have a block id collect field html
	if ( ($block_id && isset($_GET['editfields'])) || $field_id) {

		// Collect fields on this block
		if (!$field_form_html) {
			$query = "SELECT * FROM forms_fields WHERE block_id=".$block_id." AND `status`<>'X' ORDER BY sort_order";
			//print "$query<br>\n";
			
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
					$editlink = $deletelink = $editdetaillink = $no_link;
		
					$editlink = '<a '.$help->drawInfoPopup("Edit field data").' class="edit" href="/treeline/forms/?fid='.$form->id.'&amp;bid='.$result->block_id.'&amp;ffid='.$result->id.'&amp;action=editform&amp;editfields&amp;editfield"></a>';
					$deletelink = '<a '.$help->drawInfoPopup("Delete this field").' class="delete" href="/treeline/forms/?fid='.$form->id.'&amp;bid='.$result->block_id.'&amp;ffid='.$result->id.'&amp;action=editform&amp;deletefield"></a>';
					if ($result->type=="select" || $result->type=="radio") {
						$editdetaillink='<a '.$help->drawInfoPopup("Edit field options").' class="edit-form" href="/treeline/forms/?fid='.$form->id.'&amp;bid='.$result->block_id.'&amp;ffid='.$result->id.'&amp;action=editform&amp;fieldoptions"></a>';
					}
					$hidden = $result->status=='H'?" hidden":"";
					
					$field_form_html.='<li id="mm_'.$result->id.'" class="page-item1 no-nest sort-handle">
					<table border="0" cellpadding="0" cellspacing="0" class="tl_list" style="width: 627px;"><tr>
						<td class="title'.$hidden.'"><span '.$help->drawInfoPopup("Click and drag this item to move it").'>'.(strlen($result->label)>25?substr(strip_tags($result->label),0,22)."...":$result->label).'</span></td>
						<td class="field-type'.$hidden.'">'.$result->type.'</span></td>
						<td class="field-name'.$hidden.'">'.$result->name.'</span></td>
						<td class="field-req'.$hidden.'">'.($result->required+0).'</span></td>
						<td class="pm-right action">'.$editlink.$deletelink.$editdetaillink.'</td>
						</tr>
					</table>
					</li>
					';
				}
				if ($field_form_html) $field_form_html='
				<form method="post" id="form-fields">
				<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="savefields" value="1" />
				<input type="hidden" name="fid" value="'.$form->id.'" />
				<input type="hidden" name="bid" value="'.$block_id.'" />
				<input type="hidden" id="mm_field_content" name="mm_content" value="" />
				<p class="instructions">You must press submit on this page to save any changes to the field order</p>
				<div id="th">
					<p>
					<span class="title">Field name</span>
					<span class="preview">Manage</span>
					<span class="field-req">Req</span>
					<span class="field-name">ID</span>
					<span class="field-type">Type</span>
					</p>
				</div>
				<div class="menu-wrap-wrap">
				<div class="menu-wrap">
					<ul id="ff" class="page-list">
					'.$field_form_html.'
					</ul>
				</div>
				</div>
				<fieldset class="buttons">
					<input type="submit" class="submit menu-submit" value="Submit" />
				</fieldset>
				</fieldset>
				</form>
				';
				
			}
			else $field_form_html.='<p>No fields have been set up yet</p>';
			$field_form_html = '<p><a href="/treeline/forms/?action=editform&fid='.$form->id.'&bid='.$block_id.'&editfields&addfield">Add a new field</a></p>'.$field_form_html;

		}
	}
}

echo treelineBox($block_form_html, "Manage blocks", "blue");

if ($field_form_html) {
	echo treelineBox($field_form_html, $field_form_title?$field_form_title:($block_title?$block_title:"Manage fields"), "blue", 735, 0, $help_text_id);
	
	if ($field_form_options_html) echo treelineBox($field_form_options_html, $form->field->label." options", "blue");
}

// Should we do a block preview?
if ($_SERVER['REQUEST_METHOD']=="GET" && isset($_GET['previewblock'])) {
	?><p>Please note: This preview only shows the layout of the fields. The background colour and borders may be different once embedded into content.</p><?php
	$form->loadByID($form->id);
	$form_mode="PREVIEW";
	include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/form_display.inc.php";
	echo $replace;
}

?>