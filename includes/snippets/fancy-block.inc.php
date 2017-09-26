<?php

$query = "SELECT code, codetype FROM fancy WHERE guid='$fancy_guid'";
//print "get data from feed($query)<br>\n";
if ($row = $db->get_row($query)) {
	$replace = '';


	switch ($row->codetype) {

		case "icon": 
			include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/fancy-icon.inc.php");
			break;

		case "colbox": 
			include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/fancy-colbox.inc.php");
			break;
			
		default: $replace = '<p>Fancy code block('.$row->codetype.') is not supported</p>'."\n";
			break;			
	}
	
}

?>