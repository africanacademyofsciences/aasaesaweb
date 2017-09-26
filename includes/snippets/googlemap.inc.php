<?php
	global $mapcounter;
	$replace = '';
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/gmaps.class.php");	
	unset($map);
	$map = new GMaps($mapcounter++);
	//$replace .= "Map GUID($googleGUID)<br>\n";
	if ($googleGUID) $map->loadByGUID($googleGUID);
	$replace .= $map->drawMap();
?>