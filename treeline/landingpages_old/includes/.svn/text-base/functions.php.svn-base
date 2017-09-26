<?php


function drawStyleCheckboxes($type, $current = NULL){
	//
	global $db;
	$html = '';
	
	if($type == 'panel'){
		$options = array(
			'Default'=>1,
			'Light (with border)'=>2,
			'Default (without border)'=>3,
			'Contrast'=>4
		);
	}
	else if($type  == 'page'){
		$options = array(
			'Style 1'=>1,
			'Style 2'=>2
		);
	}
	
	
	
	foreach($options as $option => $value){

		$preSelected = ($current == $value) ? ' checked="checked"' : '';
		
		$html .= '<div class="styleOption" style="background: url(/treeline/img/landingpanels/'.$type.$value.'.gif) 0 0 no-repeat; float: left; margin: 0 10px 10px 0; min-width: 102px; padding-top: 74px;">'."\n";
		$html .= '<input type="radio" style="margin: .5em 0;" class="checkbox" name="style" id="style_'.$value.'" value="'.$value.'"'.$preSelected.' />'."\n".'
		<label for="style_'.$value.'" class="checklabel" >'.$option.'</label> <br /></div>'."\n";
	}
	
	return $html;
}

function drawBorderCheckboxes($current = NULL){
	//
	global $db;
	$html = '';
	
	$options = array(
		'Yes'=>1
	);
	
	
	
	foreach($options as $option => $value){

		$preSelected = ($current == $value) ? ' checked="checked"' : '';
		
		$html .= '<input type="checkbox" class="checkbox" name="style" id="style_'.$value.'" value="'.$value.'"'.$preSelected.' />'."\n".'
		<label for="style_'.$value.'" class="checklabel" >'.$option.'</label><br />'."\n";
	}
	
	return $html;
}

?>
