<?
	
	// Please note that before spending hours debugging this file that MHHE has not 
	// added any tags to any links pages.
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");
	
	$response = '';

	
	$q = '';
	$msv=$_GET['msv'];
	
	if (count($_POST) > 0) {
		$q = array_shift($_POST);
	}

	$query="SELECT tag FROM tags t 
		WHERE tag LIKE '$q%' 
		AND msv=$msv
		GROUP BY tag
		ORDER BY tag ASC 
		limit 15";
	//$response.="<li>$query</li>";
	
	//mail("phil.redclift@ichameleon.com", "mhhe scripta", $query);
	//print "$query<Br>";
	//$response.='<ul><li>'.$query."</li></ul>"; print $response; exit;
	
	if ($rows = $db->get_results($query)) {
		foreach ($rows as $row) {
			$response.= '<li>'.htmlentities($row->tag)."</li>\n";
		}
	}
	$response="<ul>".$response."</ul>";
	print $response;
	
?>