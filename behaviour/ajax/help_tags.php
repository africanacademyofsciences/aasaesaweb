<?
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");
	
	$response = 0;

	
	$q = '';
	
	if (count($_POST) > 0) {
		$q = array_shift($_POST);
	}

	$query="SELECT tag FROM tags t where t.tag LIKE '$q%' ORDER BY tag ASC limit 15";
	//$response="<ul><li>$query</li></ul>";
	if ($rows = $db_admin->get_results($query)) {
	
		$response = '<ul class="helplist">';
		foreach ($rows as $row) {
			$response .= '<li>'.$row->tag.'</li>';
		}
		$response .= '</ul>';	
	}
	
	print $response;
	
?>

	
	