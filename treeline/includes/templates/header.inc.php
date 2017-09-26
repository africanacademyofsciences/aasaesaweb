<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=($_SESSION['treeline_user_encoding']?$_SESSION['treeline_user_encoding']:"iso-8859-1")?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="lang" content="<?=($_SESSION['treeline_language']?$_SESSION['treeline_language']:"en")?>" />
<title>Treeline | <?=($pageTitle == 'Home')?$pageTitle:$pageTitle?></title>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/commonCSS.inc.php"); ?>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/commonJS.inc.php"); ?>
<link rel="Shortcut Icon" href="/treeline/favicon.ico" type="image/x-icon" />
</head>
<body id="treelineCMS" class="<?=$pageClass?>">
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/accessnav.inc.php"); ?>
<div id="holder">
  <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/topcurve.inc.php"); ?>
  <div id="holder_inner">
  	<div id="header">
		<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/branding.inc.php"); ?>
        <h2 id="pagetitle"><?=$pageTitleH2?></h2>
        <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/userinfo.inc.php"); ?>
    </div>
    <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/treeline_message.inc.php"); ?> 
    <div id="midholder">
	    <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/menu.inc.php"); ?>
