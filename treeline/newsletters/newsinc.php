<?php

if (!$_SERVER['DOCUMENT_ROOT']) {
	$tmp = strpos(getcwd(), "treeline");
	if ($tmp>0) $_SERVER['DOCUMENT_ROOT']=substr(getcwd(), 0, ($tmp-1));
}
//print "got root(".$_SERVER['DOCUMENT_ROOT'].")<bR>\n"; exit();

// Do we already have a connection to the database???
if (!$db) {

	// Lets try to connect to the database using ezsql?
	$ezClass	= $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php";
	$ezCore		= $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL/ez_sql_core.php";
	$ezSettings = $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.conn.php";

	if (!file_exists($ezClass)) die("Could not locate database gateway ($ezClass) \n");
	if (!file_exists($ezCore)) die("Could not locate database core ($ezCore) \n");
	if (!file_exists($ezSettings)) die("Could not locate database settings ($ezSettings) \n");

	/*
	print "<!--
use gateway($ezClass) 
Settings($ezSettings)
-->
"; 
	*/
	include_once ($ezClass);
}

$query = "SELECT name, `value` FROM config";
//print "$query<br>\n"; 
if($results = $db->get_results($query)) {
	
	
	foreach($results as $result) {
		//echo "<pre>".print_r($m)."</pre>";
		if ($result->name == "site_name") $site_name = $result->value;
		if ($result->name == "site_url") $site_url = $result->value;
		if ($result->name == "newsletter_from_email") $contact_recipient_email = $result->value;
		if ($result->name == "min_revision_id") $min_revision_id=$result->value+0;
		if ($result->name == "contact_tech_email") $tech_email=$result->value;
	}
	$msv = 0;	// Force newsletter process to load all sites.
	$email = $site_name.' <'.$contact_recipient_email.'>';
	$url = $site_url;
	$siteName = $site_name;

	//$summary.="email($email) \n url($url) \n site($siteName) \n min_revision_id($min_revision_id) \n tech_email($tech_email) \n";
}
else $summary.="Failed to get newsletter config data \n";

define('NEWSLETTER_FROM_EMAIL', $email);
define('SERVER_NAME', $url);
define('SITE_NAME', $siteName); 
define('ALERT_EMAIL', "phil.redclift@ichameleon.com"); 

///print $msg;

?>