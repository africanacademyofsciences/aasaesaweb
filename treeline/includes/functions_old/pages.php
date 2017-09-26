<?php

	/*
	WE WILL FIND OUT SOON ENOUGH WHERE THESE ARE USED
	
	
	



	/*
	THIS HAS BEEN MOVED TO THE PAGE CLASS 
	function getPageStyleList($template = null){
		//
		global $db;
		
		if (!is_null($template)) {
			$template = " AND template = $template";
		}
		
		$query = "SELECT * FROM pages_style WHERE user_selectable=1".$template;
		$results = $db->get_results($query);
		
		return $results;
	}
	*/
	
/*
MOVE TO PAGE CLASS
	function drawStyleHeadLinks($selected = NULL){
		global $page;
		$results = $page->getPageStyleList();
		
		$html = '';
		
		if($results){
			
			foreach($results as $result){
				unset($preSelected);
				if($result->style_css != $selected && $result->style_id != $selected){
					$preSelected = 'alternate ';
				}
				$html .= '<link href="/style/'.$result->style_css.'.css" rel="'.$preSelected.'stylesheet" type="text/css" title="'.$result->style_title.'" media="screen, projection" id="CSS'.$result->style_id.'" />'."\n";
			}
		}
		
		return $html;
	}

*/	

	/*
	MOVED TO THE TREELINE CLASS
	function drawStyleSwitcherMenu($selected = NULL, $template = null, $disabled=false){
		//
		
		if ($disabled) return '';
		$results = getPageStyleList($template);
	
		
		$html = "\n";
		
		if($results){
			$html .= '<fieldset id="styleSwitcher">'."\n";
			$html .= '<label for="style">Choose layout:</label> '."\n";
			$html .= '<select name="style" id="style">'."\n";
			foreach($results as $result){
				unset($preSelected);
				if($result->style_id == $selected){
					$preSelected = ' selected="selected"';
				}
				$html .= '<option value="'.$result->style_id.'"'.$preSelected.'>'.$result->style_title.'</option>'."\n";
			}
			$html .= '</select>'."\n";
			$html .= '</fieldset>'."\n";
		}
		
		return $html;
	}
	*/

?>