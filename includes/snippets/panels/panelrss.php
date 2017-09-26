<?php
$content = new HTMLPlaceholder();
$content->load($page->getGUID(), 'panelcontent');
?>
<div class="panel <?=$panelStyle?>">
<h3><?=$page->drawTitle()?></h3>
<?php
$rssData = drawRSSFeed($content->draw(), false, 5);
if ($rssData) echo $rssData;
else {
	?>
	<p>No data returned by this feed</p>
	<?php
	//echo '<p>No data for feed('.$content->draw().')</p>';
}

?>
</div>

