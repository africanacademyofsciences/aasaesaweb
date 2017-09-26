<?php

/*
	Events functions
	-----------------
	
	written by: Phil Thompson phil.thompson@ichameleon.com
	when: 22/07/2007
	
	edited by:
	when:	
	
	
	
*/

function drawOrderByDropDownOptions($current = 'title_az'){
	//
		
	
		$options = array(
		'title_az' => 'Event name (A-Z)',
		'title_za' => 'Event name (Z-A)',
		'chronologically' => 'Chronologically',
		'non-chronologically' => 'Reverse chronologically',
		'newest' => 'Date added (newest)',
		'oldest' => 'Date added (oldest)'
		);
	
	$html = '';
	
	foreach($options as $value => $text){
		unset($preSelected);
		
		if($current == $value){
			$preSelected = ' selected="selected"';
		}
		$html .= '<option value="'.$value.'"'.$preSelected.'>'.$text.'</option>'."\n";
	}
	
	return $html;
}

function drawStatusDropDownOptions($current = 'all'){
	//
	
	$options = array(
	'all' => 'All',
	'approved' => 'Approved events',
	'unapproved' => 'Unapproved events'
	);
	
	$html = '';
	
	foreach($options as $value => $text){
		unset($preSelected);
		
		if($current == $value){
			$preSelected = ' selected="selected"';
		}
		$html .= '<option value="'.$value.'"'.$preSelected.'>'.$text.'</option>'."\n";
	}
	
	return $html;
}