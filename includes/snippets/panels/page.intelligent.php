<!--INTELLIGENT LINKS PANEL -->
<?php
$tags = new Tags();
$tags->setMode("view");
$link_content = $tags->drawRelatedContentLinks($panelGUID);
if ($link_content) {
	?>
    <div id="panel-<?=$panelGUID?>" class="panel <?=$page->getStyle()?>">
    <?=$link_content?>
    </div>
    <?php
}
?>
