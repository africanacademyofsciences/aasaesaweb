<?php
//ini_set("display_errors", 1);

include($_SERVER['DOCUMENT_ROOT']."/preferences.php");
//Page title
if(!$pageTitle){
	// 'Home' should be site tagline
	$pageTitle = $pageGUID == $site->id ? $site->properties['tagline']:$page->drawTitle();
	$parent = $page->getParent();
	$pageTitle .= ($parent && $parent!=$site->id) ? " | ".$page->drawTitleByGUID($parent) : "";
	$pageTitle = (isset($storeBreadcrumb) && $storeBreadcrumb>'') ? strip_tags(substr($storeBreadcrumb,2)) . ' | '. $pageTitle : $pageTitle;
}

if ($_COOKIE['graphics_mode'] == 'low'){
	$css[] = 'lowgraphics';
}

if (!$global_canonicalURL) $global_canonicalURL = $site->link.substr($request,1).($qs?"?".$qs:"");
	
	if ($site->id == 18)
	{
		$site->path = '/includes/html/';
	}
//if ($page->getMode()!="edit") $extraCSS.=$header_img->formatAsBranding();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=$site->properties['site_title']?> | <?= ($pageTitle == 'Home') ? $site->properties['tagline'] : html_entity_decode($pageTitle) ?></title>

<meta property="og:title" content="<?=$page->getTitle()?>" />
<meta property="og:type" content="website" />
<meta http-equiv="Content-Language" content="<?= $siteData->language ?>" />
<meta property="og:site_name" content="<?=$site->title?>" />
<link rel="canonical" href="<?=$global_canonicalURL?>" />
<meta property="og:url" content="<?=$global_canonicalURL?>" />
<?php 
// Meta description
if($meta_description || $global_meta_desc) {
?><meta name="description" content="<?=($meta_description?$meta_description:$global_meta_desc)?>" />
<meta property="og:description" content="<?=($meta_description?$meta_description:$global_meta_desc)?>" />
<?php
}
else if($page->getMetaDescription()) { 
?><meta name="description" content="<?=$page->drawMeta('description')?>" />
<meta property="og:description" content="<?=$page->drawMeta('description')?>" />
<?php 
}
// Meta tags
if($tags && $tags->drawTags($page->getGUID())) { 
?><meta name="keywords" content="<?= $tags->drawTags($page->getGUID(),'list')?>" />
<?php 
} 
else if ($global_meta_keyw) {
?><meta name="keywords" content="<?=$global_meta_keyw?>" />
<?php 
} 

// Members only page or no robots in attributes
if ($page->private>0 || $page->robots>0) { 
?><meta name="robots" content="noindex, nofollow" />
<?php 
} 
// Search results and paginated pages
else if ($global_paginated || $global_searchresult) {
?><meta name="robots" content="noindex, follow" />
<?php
}
// Standard content page
else {
?><meta name="robots" content="index, follow" />
<?php
}
?>

<link rel="home" title="Home" href="http://<?=$site->link?>" />


<?php if($mode=='view'){  // RSS ?>
<link href="/rss/" rel="alternate" type="application/rss+xml" title="<?=$site->title?> - Latest Updates" />
<?php  } 
	if ($page->getMode() == 'edit' || $mode == 'edit') {
		// Should this be part of the treeline class?
		echo "\n".'<link rel="stylesheet" type="text/css" href="/treeline/style/editMode.css" media="all" />'."\n";
	}
if ($global_redline==1) { ?>
<script id="redline_js" type="text/javascript">var redline = {}; redline.project_id = 230066145;var b,d;b=document.createElement("script");b.type="text/javascript";b.async=!0;b.src=("https:"===document.location.protocol?"https://data":"http://www")+'.redline.cc/assets/button.js';d=document.getElementsByTagName("script")[0];d.parentNode.insertBefore(b,d);</script>
<?php 
}
?>


<!-- Bootstrap -->
<link href="<?=$site->path?>css/bootstrap.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?=$site->path?>css/animate.css">
<link rel="stylesheet" href="<?=$site->path?>css/owl.carousel.css">
<link rel="stylesheet" href="<?=$site->path?>css/owl.theme.default.css">
<link rel="stylesheet" href="<?=$site->path?>css/bootstrap-select.css">
<link rel="stylesheet" href="<?=$site->path?>css/theme.css">
<link rel="stylesheet" href="<?=$site->path?>css/custom.css">
<link rel="stylesheet" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

<?php
	if ($site->id == 18)
	{
?>
	<link rel="stylesheet" href="<?=$site->path?>css/aesa.css">
	<link href="https://raw.githubusercontent.com/daneden/animate.css/master/animate.css" rel="stylesheet" />
<?php
	}
?>

<link rel="stylesheet" href="/style/font-awesome.min.css">
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) --> 
<script src="<?=$site->path?>js/jquery.js"></script> 


<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/commonCSS.inc.php'); 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/commonJS.inc.php');
?>

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
<script type="text/javascript">var switchTo5x=true;</script>
<!--<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>-->
<!--<script type="text/javascript">stLight.options({publisher: "6ca9615d-9634-4d9b-bab3-a35e1984efb0", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>-->
    
</head>
<?php 
	// Avoid invliad body ID throwning invalid id warnings.
	$body_id=str_replace(" ","-",str_replace('/','-',$site->url));
	if (substr($body_id,0,1)=="-") $body_id = substr($body_id, 1);
?>
<body id="<?=$body_id?>" class="<?=($location[0])?setClass($location[0]).' ':''?><?=$pageClass?><?=($page->getMode() != 'view') ? ' '.$page->getMode() : ''; ?>" style="<?=($testing?"background-color:#ffc;":"")?>" >
<?php 
//include($_SERVER['DOCUMENT_ROOT']."/includes/templates/stats-top.inc.php"); 
include($_SERVER['DOCUMENT_ROOT']."/includes/html/18/stats-top.inc.php"); 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/previewModeTop.inc.php'); 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/editModeTop.inc.php');
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/shortcuts.inc.php'); 
//include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/media-test.inc.php');
?>
<!-- menu -->
<?php
include($_SERVER['DOCUMENT_ROOT'].'/includes/html/18/menu.inc.php');
//include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
?>

<div class="content">

    
