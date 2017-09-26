<?php
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'panelcontent');
	
	
	// START PREVIEW/EDIT/VIEW MODE
	if ($page->getMode() != 'view' && $mode=="preview"){ // PREVIEW MODE: SHOW PROPER HTML
		//$page->loadByGUID($pageGUID);
		$pageClass = 'panel'; // used for CSS usually
		//$panelStyle = $page->getStyle();
	
		$css = array('page','3col','editMode'); // all attached stylesheets

		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Panel - <?=$page->drawTitle()?></title>
<style type="text/css">
    @import url("/style/reset.css");
    @import url("/style/yui_fonts.css");
    @import url("/style/global.css");
    @import url("/style/page.css");
    @import url("/style/3col.css");
    @import url("/style/scheme/palate01.css");
	<?php
	if (file_exists($_SERVER['DOCUMENT_ROOT']."/style/microsite/scheme".$site->id.".css") && !$_SESSION['palate']) echo '@import url("/style/microsite/scheme'.$site->id.'.css");';
	else if ($palateNumber>1) echo '@import url("/style/scheme/palate'.(($palateNumber<10?"0":"").$palateNumber).'.css");';		
	if ($_GET['palate']>0) echo '@import url("/style/scheme/palate'.(($_GET['palate']<10?"0":"").$_GET['palate']).'.css");';
	?>
    body{background: #FFF;}
</style>
<?php //include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/commonCSS.inc.php'); ?>
<script type="text/javascript" src="/behaviour/jquery.js"></script>
<script type="text/javascript" src="/behaviour/jquery.corner.js"></script>
<script type="text/javascript">
$(window).load(function()
{
	// -------------------- Keep large images within the design --------------------
	
	$("#secondarycontent div.rounded").each(function()
	{	
		$(this).corner("7px");
	});

});
</script>
</head>
        
<body>	
<div id="breadcrumb">Breadcrumb trail</div>
<div id="sidebar">
	<ul class="submemu">
    	<li><a class="level-1" href="#">Sub-section</a></li>
        <li><a class="level-1" href="#">Sub-section</a></li>
        <li class="subon"><a class="level-2" href="#">Sub-section</a>
       	<li><a class="level-1" href="#">Sub-section</a></li>
        <li><a class="level-1" href="#">Sub-section</a></li>
        <li><a class="level-1" href="#">Sub-section</a></li>
        <li><a class="level-1" href="#">Sub-section</a></li>
    </ul>
</div>

<div id="contentholder">
    <h1 class="pagetitle">Dummy content</h1>
    
    <div id="primarycontent">
        <p>This page doesn't have any content, its purpose is to highlight what the panel (left panels also appear on right - just for preview) will look like in context.</p>
        <p>By seeing this so-called 'Dummy content' next to your new panel you should get a better feeling as to how the panel will look on your website.</p>
    </div>
    
    <div id="secondarycontent">

	<?php
	}
	
?>

<!-- SHOW ACTUAL PANEL CONTENT -->
<div class="panel rounded <?=$panelStyle?>">
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

<?php
	// PREVIEW MODE: CLOSE PROPER HTML
	// Close containing div we opened above:
	if ($page->getMode() != 'view' && $mode=="preview") {
	?>

    </div>
</div>

</body>
</html>

	<?php	
	}
?>