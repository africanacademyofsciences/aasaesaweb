<?php
//ini_set("display_errors", 1);
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/site.class.php");
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/placeholder.class.php");

// Need to load the site object for encoding
$guid = $_GET['guid'];
$query = "SELECT msv FROM pages where guid='$guid'";
$msv = $db->get_var($query);
if ($msv>0) {
	$site = new Site($msv);
	$siteData = $db->get_row("SELECT * FROM get_site_details WHERE msv=".$msv);
	//echo "site(".print_r($siteData, true).")<br>\n";
	
	$mode = "edit";
	$content = new HTMLPlaceholder();
	$content->setMode("edit");
	$content->load($guid, 'panelcontent');
	echo $content->draw("mcePanelEditor");
}
?>