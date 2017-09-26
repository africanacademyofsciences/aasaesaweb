<?php

//ini_set("dispaly_errors", true);
//error_reporting(E_ALL);

$panelGUID = $page->getGUID();
//print "mode($mode) pagemode(".$page->getMode().") template(".$page->getTemplate().") tt(".$page->template_type.") guid(".$panelGUID.")<br>";// exit;

// Add an panels that should be possible to edit inline
$editable_panels = array("panel.php");

$template = $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panels/".$page->getTemplate();
if (file_exists($template)) {

	// If we have a post action process that first
	// Lots of panels types won't have a post action so we don't worry if it does not exist
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$template_post = $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panels/post/".$page->getTemplate();
		if (file_exists($template_post)) include($template_post);
	}
		
	//print "show panel in mode(".$page->getMode()." - $mode) action($action)<br>";
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	$fullpagepanelpreviewmode = $page->getMode() != 'view' && $mode=="preview" && !$previewMsgShown;
	//print "fpppm($fullpagepanelpreviewmode) pm(".$page->getMode().") m(".$mode.") preview shown already($previewMsgShown)<br>\n";	

	// ------------------------------------------------------------------------------
	// START PREVIEW/EDIT/VIEW MODE
	// ------------------------------------------------------------------------------
	if ($fullpagepanelpreviewmode){ // PREVIEW MODE: SHOW PROPER HTML
		$pageClass = 'panel'; // used for CSS usually
		$css = array('page','3col','editMode'); // all attached stylesheets
		$pagetitle = "Panel preview page";
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
		include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
		?>
        <div class="main-content">
            <div class="container">
                <div id="primarycontent">
                    <p>This page doesn't have any content, its purpose is to highlight what the panel (left panels also appear on right - just for preview) will look like in context.</p>
                    <p>By seeing this so-called 'Dummy content' next to your new panel you should get a better feeling as to how the panel will look on your website.</p>
                </div>
                
                <div class="sidebar" id="secondarycontent">
		<?php
	}
	// ------------------------------------------------------------------------------

	
	// ------------------------------------------------------------------------------
	// INLINE EDIT - NEED TO WRAP THE ENTIRE PANEL IN A CONTAINER
	// ------------------------------------------------------------------------------
	if ($page->getMode()=="inline") {
		?>
		<div class="panel-wrapper" id="panel-wrap-<?=$panelGUID?>" >
		<ul class="panel-edit">
        	<?php
			$custom = $page->template_id==23;
			//print "drawPanel template(".$page->template_id.")<br>\n";
			if (in_array($page->getTemplate(), $editable_panels)) {
				?>
				<li class="edit" id="<?=$panelGUID?>-edit"><a href="javascript:toggle_edit('<?=$panelGUID?>');" title="<?=$page->getLabel("tl_pedit_page_edpanel")?>">edit</a></li>
				<li class="unedit" id="<?=$panelGUID?>-unedit" style="display:none;"><a href="javascript:setTarget(0);setAction('discard');" title="<?=$page->getLabel("tl_pedit_page_discard")?>">Discard changes</a></li>
				<li class="rejedit" id="<?=$panelGUID?>-rejedit" style="display:none;"><a href="javascript:alert('<?=$page->getLabel("tl_pedit_page_editbusy")?>');" title="<?=$page->getLabel("tl_pedit_page_noedit")?>">This panel cannot be editted at this time</a></li>
               	<?php
			}
			else {
				?>
				<li class="noedit" id="noedit"><a href="javascript:alert('<?=$page->getLabel("tl_pedit_page_tledit")?>');" title="<?=$page->getLabel("tl_pedit_page_unedit")?>">blank</a></li>
                <?php
			}
			?>
            <li class="moveup"><a href="javascript:swapNodes('panel-wrap-<?=$panelGUID?>', 1);" title="<?=$page->getLabel("tl_pedit_page_moveup")?>">move up</a></li>
            <li class="moveup-hidden" style="display:none;"><a href="javascript:alert('<?=$page->getLabel("tl_pedit_page_movesave")?>');" title="<?=$page->getLabel("tl_pedit_page_moveup")?>">move up</a></li>
            <li class="movedown"><a href="javascript:swapNodes('panel-wrap-<?=$panelGUID?>', 0);" title="<?=$page->getLabel("tl_pedit_page_movedown")?>">move down</a></li>
            <li class="movedown-hidden" style="display:none;"><a href="javascript:alert('<?=$page->getLabel("tl_pedit_page_movesave")?>');" title="<?=$page->getLabel("tl_pedit_page_movedown")?>">move down</a></li>
			<li class="drag"></li>
			<li class="delete"><a href="javascript:delete_panel('<?=$panelGUID?>', <?=$custom?1:0?>);" title="<?=$page->getLabel("tl_pedit_page_delete")?>">delete</a></li>
		</ul>
		<?php	
	}
	// ------------------------------------------------------------------------------

	//print "Panel include($template) style(".$page->getStyle().")<br>\n";
	include($template);


	// ------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------
	if ($page->getMode()=="inline") {
		?>
		</div>
		<?php	
	}
	// ------------------------------------------------------------------------------

	// ------------------------------------------------------------------------------
	// PREVIEW MODE: CLOSE PROPER HTML
	// Close containing div we opened above:
	// ------------------------------------------------------------------------------
	else if ($fullpagepanelpreviewmode) {
		?>
        </div>
        </div>
        </div>
        <?php
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
	}
	// ------------------------------------------------------------------------------

}
else echo '<p>Panel template('.$template.') does not exist</p>';
	
?>