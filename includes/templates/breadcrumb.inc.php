<?php
$sep = "&gt;";
$sep = '<i class="fa fa-angle-right"></i>';
if($pageGUID == $siteID) {
	//print "running homepage";
	//$breadcrumb = $breadcrumb->draw();
}
else if( isset($storeBreadcrumb) && $storeBreadcrumb>'' ){
	$breadcrumb = '<a href="/">Home</a> '.$sep.' <a href="'. $storeURL .'">Shop</a>'.$storeBreadcrumb;
}
else if( $location[0]=='shopping-basket' ){
	$mode = read($_REQUEST,'mode',false);
	if( !$mode ) {
		$breadcrumb = '<a href="/">Home</a> '.$sep.' <a href="'. $storeURL .'">Shop</a> '.$sep.' Shopping Basket';
	}
	else{
		$modeTxt = str_replace('_',' ',$mode);
		$modeTxt = ucwords($modeTxt);
		$breadcrumb = '<a href="/">Home</a> '.$sep.' <a href="'. $storeURL .'">Shop</a> '.$sep.' ';
		$breadcrumb .= '<a href="'. $storeURL .'/shopping-basket">Shopping Basket</a> '.$sep.' ';
		$breadcrumb .= $modeTxt;
	}
}
else if ($location[0]=="blogs") {
	$breadcrumb = '<a href="/">Home</a> '.$sep.' <a href="'. $site->link .'blogs">Blogs</a>';
}
else {
	$breadcrumb = $page->drawBreadcrumb($pageGUID);
}


if ($mode=="edit" || $mode=="preview") $breadcrumb = ' <a href="#">section</a> '.$sep.' <a href="#">page title</a>';
if ($breadcrumb) {
	if ($site->id == 18)
	{
		?>
		<span class="papertrail hidden-xs">
			<?=$breadcrumb?>
		</span>
		<?php
	}
	else
	{
	?>
	<span class="papertrail pull-right hidden-xs">
		<?=$breadcrumb?>
	</span>
	<?php
	}
}
?>