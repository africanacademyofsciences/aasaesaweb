<?php

//ini_set("display_errors", true);
//error_reporting(E_ALL ^ E_NOTICE);

	$http = "http";
	if ($_SERVER['HTTPS']=="on") $http.="s";
	//print "<!-- Set http  to($http) -->\n";

	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");
	// Set the debugging on
	$DEBUG = (read($_GET,'debug',false) !== false) ? true : false;
	
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/placeholder.class.php");		
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/menu.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/user.class.php");	
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/search.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/site.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/tags.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/image.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/tasks.class.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/website.class.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/help.class.php");

	session_start();
	
	
	// Default to viewing Treeline in English
	if (!$_SESSION['treeline_language']) $_SESSION['treeline_language']=$_COOKIE['tl_lang']?$_COOKIE['tl_lang']:"en";
	if ($_SERVER['REQUEST_METHOD']=="GET" && $_GET['action']=="lang" && $_GET['lang']) {
		$_SESSION['treeline_language']=$_GET['lang'];
		setcookie('tl_lang', $_SESSION['treeline_language'], time()+60*60*24*7);
	}
	if ($row = $db->get_row("SELECT title, title_local FROM languages WHERE abbr='".$_SESSION['treeline_language']."'")) {
		$_SESSION['treeline_language_title']=$row->title;
		$_SESSION['treeline_language_title_local']=$row->title_local;
	}
	

	/* Remove the gallery page guid from the session
	so that the image picker doesn't think that it is
	still sending images to a gallery, when it should be
	inserting them into tiny mce as usual. */
	unset($_SESSION['gallery_page_guid']);
	
	$request = read($_SERVER,'REQUEST_URI','');
	$qs = read($_SERVER,'QUERY_STRING',false);

	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
	
	$page = new Page();
	$labels = array();
	
	$help = new Help();
	
	$siteID = $_SESSION['treeline_user_site_id'];
	$site = new Site($siteID);	

	$storeVersion = "v".$site->getConfig("setup_store");


	//print "View TL in (".$_SESSION['treeline_language']." title(".$_SESSION['treeline_language_title']."))<Br>\n";
	if ($site->id>0) {
		// Generate a list of translated labels for this site.
		$labels=$page->getTranslations($site->id, $_SESSION['treeline_language'], 2);
		$siteData = $db->get_row("select * from get_site_details where msv=".$site->id);
	}


	$tasks = new Tasks($site->id, $_SESSION['treeline_user_id']);
	$db->flush();



?>