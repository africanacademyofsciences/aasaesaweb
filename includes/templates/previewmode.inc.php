<?php

	// Preview mode
	// Set all content areas into preview mode.
	//print "mode($mode) action($action)<br>\n";
	if ($mode=="preview" || strtolower($action)=="preview") {

		// Maybe should save the page content to the user table.
		// Not sure how to deal with panel changes though?
		// Maybe I dont actually need to save anything 
		// hmmm...
		//$content->save();
		//$page->save();
		//$panels->save();
		if ($_POST['style']) {
			if ($page->getTemplate()=="panel.php") ;
			else {
				$stylecss=$db->get_var("SELECT style_css FROM pages_style WHERE style_id=".$_POST['style']);
				$page->setStyle($stylecss);
			}
		}
		
		$page->setMode("preview");
		$mode="preview";
		if (is_object($jumbo)) $jumbo->setMode($mode);
		if (is_object($header_img)) $header_img->setMode($mode);
		if (is_object($tags)) $tags->setMode($mode);
		if (is_object($content1)) $content1->setMode($mode);
		if (is_object($content2)) $content2->setMode($mode);
		if (is_object($content3)) $content3->setMode($mode);
		
		if (is_object($content)) {
			$content->setMode($mode);
			// Would you credit this ????
			// Preview mode fails to show media content because \ chars appear before " as part of 
			// tinyMCE post process. To keep it treeliney I've used the same system as used
			// to write the data to the database. (which works by luck rather than skill :o( )
			$content->setContent(str_replace('\"', '"', $content->draw()));
		}
		
		if (is_object($panels)) $panels->setMode($mode);
		
		$showPreviewMsg=true;
		

	}
?>