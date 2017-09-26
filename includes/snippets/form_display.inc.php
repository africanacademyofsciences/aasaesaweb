<?php

if ($form_mode=="PREVIEW") {
	$form_data_id=read($_GET, "fdid", 0);
}
else {
	$member_id = $_SESSION['member_id'];
}

global $hide_submit_button_just_this_once;

if (!$form_id) $form_id=$form->id;


//print "collect fields on this form($form_id) data($form_data_id)<br>\n";
if ($results = $form->getFieldList($form_data_id)) {
	
	$block_count=-1; $block=array();
	//if ($results = $db->get_results($query)) {
	foreach ($results as $result) {
		//print "cur($current_id) this block(".$result->block_id.")<br>\n";
		if ($current_id!=$result->block_id) {
			$current_id=$result->block_id;
			$block_count++;
		}
		$blocks[$block_count]['block_id']=$result->block_id;
		$blocks[$block_count]['title']=$result->title;
		$blocks[$block_count]['html'].=$form->field->drawField($result);
		$blocks[$block_count]['fields']++;
		if ($form->field->enctype && !$form->enctype) $form->enctype=$form->field->enctype;
	}
	//}	

	$replace = '
    <form method="POST" id="form_'.$form_id.'" class="formbuilder std-form" '.($form->enctype=="form-data"?'enctype="multipart/form-data"':"").'>
    <fieldset class="border" id="section-1">
        <input type="hidden" name="data_id" value="'.($form_data_id+0).'" />
        <input type="hidden" name="fid" value="'.($form_id+0).'" />
        <input type="hidden" name="member_id" value="'.($member_id?$member_id:($_SESSION['member_id']+0)).'" />
		<input type="hidden" name="treeline" value="process-form" />
	';        

	//print "got count($block_count) total(".count($blocks).")<br>\n";
	//print_r($blocks);
	for($i=0; $i<=$block_count; $i++) { 
		//print "Got bid(".$_GET['bid'].") this block(".$blocks[$i]['block_id'].")<br>\n";
		if (!$_GET['bid'] || $_GET['bid']==$blocks[$i]['block_id']) {
			$replace .= '<fieldset id="block-'.$blocks[$i]['block_id'].'">
<legend>'.$blocks[$i]['title'].'</legend>
'.$blocks[$i]['html'].'
</fieldset>
';		
		}
	}
		
	if ($form_mode=="PREVIEW" || $hide_submit_button_just_this_once) ;
	else { 
	$replace .='
	<fieldset>
		<label form="f_submit" style="visibility:hidden">Submit</label>
		<button type="submit" id="f_submit" class="btn btn-lg btn-primary pull-right" name="action">'.($form->submit_text?$form->submit_text:"Submit").'</button>
	</fieldset>
	';
	}
	/* 
	if ($form->add_news) { 
		$replace.='
		<p><input type="checkbox" class="checkbox" name="no_news" value="1" <?=(($ef_no_news==1)?"checked":"")?> /> We would like to keep you informed of future events and <?=$site->name?> news. If you prefer not to be contacted please tick here.</p>
		';
	}
	*/
	$replace.='
	</fieldset>
	</form>
	';
}    

?>