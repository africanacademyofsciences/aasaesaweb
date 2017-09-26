<?php

	$pcontent = new HTMLPlaceholder();
	$pcontent->setMode($page->getMode());
	$pcontent->load($panelGUID, 'panelcontent');


	//print "load in mode(".$page->getMode().")<br>\n";
	
	// ------------------------------------------------------------------------------
	// Panel edit mode via Treeline
	// ------------------------------------------------------------------------------
	if ($page->getMode() == 'edit') { 
		global $extraJSbottom;
		require_once ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/help.class.php");
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Panel - <?=$page->drawTitle()?></title>
        <style type="text/css">
            @import url("/style/reset.css");
            @import url("/style/yui_fonts.css");
            @import url("/style/global.css");
            @import url("/treeline/style/editMode.css");
            body{background: #FFF;}
        </style>
        </head>
        
		<body>
		<?php
		
		// EDIT MODE: SHOW EDIT FORM
		// $dbqs = ($DEBUG)?'?debug':'';
		$dbqs = '';
		$frm_action = $_SERVER['REQUEST_URI'].$dbqs;
		//$referer = read($_SERVER,'HTTP_REFERER','');
		// If we're debugging, apend ?debug to the form post
		echo $page->initCKE();
		echo '
		<form id="treeline_edit" action="'.$frm_action.'" method="post">
		';
	
		// Add the panel toolbar:	
		//echo $page->drawPanelToolbar();
		$currentStyle = ($_POST['style']) ? $_POST['style'] : $page->style_id;
		echo $page->drawToolbar($currentStyle, false, 'panel');
		
		echo '<input type="hidden" name="action" value="save" />
		<input type="hidden" name="mode" value="'.$page->getMode().'" />
		<input type="hidden" name="referer" value="'.$referer.'" />
		<input type="hidden" name="title" value="'.$page->drawTitle().'" />
		<input type="hidden" name="post_action" value="" />
		<div id="panel-tl-editor" style="margin:0 auto; clear:both; width:300px;">
		'; 
	}	
	// ------------------------------------------------------------------------------



	// ------------------------------------------------------------------------------
	// Trying to edit this panel on the page itself
	// ------------------------------------------------------------------------------
	else if ($page->getMode()=="inline") {
		$currentStyle = ($_POST['style-'.$panelGUID]) ? $_POST['style-'.$panelGUID] : $page->style_id;
		//print "current style($currentStyle)<br>\n";
		$global_edhtml='
		<div id="panel-editor-'.$panelGUID.'" class="panel-editor" style="display:none;">
			<fieldset class="panel-title">
				<input type="hidden" name="xtitle-'.$panelGUID.'" value="'.$page->drawTitle().'" />
				<input type="text" class="text" name="title-'.$panelGUID.'" value="'.$page->getTitle().'" />
			</fieldset>
			<fieldset class="panel-style">
				';
		$global_edhtml.=$page->drawStyleSwitcherMenu($currentStyle, 6);
		$global_edhtml.='
			</fieldset>
			<div id="panel-content-'.$panelGUID.'" class="panel-content">
		';
		//$global_edhtml.=$pcontent->draw("mcePanelEditor");
		$global_edhtml.='
			</div>
			<fieldset class="buttons">
				<input type="button" class="button" value="'.ucfirst($page->getLabel("tl_generic_save", true)).'" id="btn_save-'.$panelGUID.'" onclick="javascript:setTarget(0);setAction(\'Save\')" />
			</fieldset>
		</div>
		';
		echo $global_edhtml;
		$pcontent->setMode("inline-view");
	}
	// ------------------------------------------------------------------------------

	// ------------------------------------------------------------------------------
	// Trying to edit this panel from the panel manager interface
	// ------------------------------------------------------------------------------
	else if ($page->getMode() == "inline-edit") {
		echo $page->drawPanelTinyMCE();
		echo '<form id="treeline_edit" method="post">
<input type="hidden" name="action" value="save" />
<input type="hidden" name="panelguid" value="'.$thispanel.'" />
<input type="hidden" name="mode" value="'.$page->getMode().'" />
<input type="hidden" name="title" value="'.$page->drawTitle().'" />
';
	}
	// ------------------------------------------------------------------------------

	
	// ------------------------------------------------------------------------------
	// SHOW ACTUAL PANEL CONTENT
	// ------------------------------------------------------------------------------
	$pcontentv = validateContent($pcontent->draw("mcePanelEditor"));
	switch ($page->style) {
		case "panel_2":
			$widgetClass = "widget-orange widget-dark";
			break;
			
		default:
			//print "Style(".$page->style." ".$page->stylecss.")<br>\n";
			break;
	}
	?>
	<!-- SHOW ACTUAL PANEL CONTENT -->
	<div id="panel-<?=$panelGUID?>" class="panel <?=$widgetClass?> <?=$page->getStyle()?>">
    <?php
	if ($pcontent->getMode()=="view" && !$pcontent->draw()) ;
	else {
		$maxlen = 150;
		?>
            <div class="panel-heading"><?=(substr($page->getTitle(), 0, $maxlen).(strlen($page->getTitle())>$maxlen?"...":""))?></div>
            <div class="panel-body">
                <?=$pcontentv?>
            </div>
		<?php
	}
	?>
	</div>
    <?php


	// ------------------------------------------------------------------------------
	// EDIT MODE: CLOSE FORM AND BASIC HTML
	// Close the form [and containing div] we opened above:
	// ------------------------------------------------------------------------------
	if ($page->getMode() == 'edit') {
		?>
	   	</div>
    	</form>
		<script type="text/javascript">
			CKEDITOR.replace('treeline_panelcontent-<?=$panelGUID?>', { toolbar : 'contentPanel', height: '250px' });
	
			function editorNotes(guid) {
				var settings="scrollbars=yes,top=100,left=200,height=500,width=346,directories=no,location=no,resizeable=no";
				var newwindow = window.open("/treeline/ednotes/?guid="+guid, "editwin", settings);
				if (window.focus) { newwindow.focus(); }	
			}
        </script>
        </body>
	    </html>
		<?php
	}
	// ------------------------------------------------------------------------------


	// ------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------
	else if ($page->getMode()=="inline-edit") {
		?>
        <fieldset>
        <input type="submit" class="submit" value="Save" />
        </fieldset>
        </form>
        <?php
	}
	// ------------------------------------------------------------------------------
	


?>