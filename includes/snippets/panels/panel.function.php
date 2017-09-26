<div id="panel-<?=$page->name?>" class="panel <?=$page->style?>">
<?php
	$panel_template = $_SERVER['DOCUMENT_ROOT']."/includes/snippets/panels/panel.".strtolower(($page->getName())).".php";
	//print "look for template($panel_template)<br>\n";
	if (file_exists($panel_template)) include($panel_template);
	else {
		?>
		<h3><?=$page->drawTitle()?></h3>
        <p>Panel template does not exist for this panel</p>
        <?php
	}
?>
</div>

