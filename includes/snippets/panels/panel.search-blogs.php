<?php
global $blogguid;
if (!$pageGUID) global $pageGUID;

if (!$blogguid) {
	$blogguid = $db->get_var("SELECT parent FROM pages WHERE guid = '$pageGUID'");
}
//$bloglink = $page->drawLinkByGUID('55c33a5b0c6e1');
?>

<div class="panel-heading">Search blogs</h3></div>
<div class="panel-body">

	<form id="blog-search" method="get" action="<?=$site->link?>search/">
    	<input type="hidden" name="blogguid" value="<?=$blogguid?>" />
		<div class="form-group form-group-sm">
			<div class="input-group input-group-sm">
				<input type="text" name="keywords" class="form-control" placeholder="Search blogs">
				<span class="input-group-btn">
				<input class="btn btn-link" type="submit" value="&#xf4a4;" style="font-family:ionicons; font-weight:100;">
				</span>
			</div>
		</div>
	</form>
</div>
