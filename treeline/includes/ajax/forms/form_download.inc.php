<?php

$testing = $_GET['testing']==1;
$refreshCSV = $_GET['refresh']==1;
$page_html = '';

function createRandomCSVFilename($site_id="1"){
	// Creates a filename for the CSV file.
	$alpha = "abcdefghijklmnopqrstuvwxyz0123456789012345678901234567890123456789";
	$filename_ok = false;
	$filename = "ch_subs_" . $site_id. "_" . $alpha{rand(0, strlen($alpha) - 1)};
	for($i = 0; $i < 6; $i++){
		$filename .= $alpha{rand(0, strlen($alpha) - 1)};
	}
	return($filename . ".csv");
}

	/*
	$query = "SELECT count(distinct fd.id) FROM forms_data fd 
		LEFT JOIN forms_values fv ON fd.id=fv.data_id
		WHERE fv.data_id>0
		AND fd.form_id=".$form->id;
	//print "$query<br>\n";
	$entry_count = $db->get_var($query);
	*/
	$query = "SELECT count(distinct fd.id) FROM forms_data fd 
		LEFT JOIN forms_values fv ON fd.id=fv.data_id
		LEFT JOIN forms_fields ff ON ff.id = fv.field_id
		LEFT JOIN forms_blocks fb ON fb.id = ff.block_id
		WHERE fv.data_id>0
		AND fb.`status`='A'
		AND ff.`status`='A'
		AND fd.form_id=".$form->id;
	if ($testing) print "Get data count: $query<br>\n";
	$entry_count = $db->get_var($query);
	
	if($entry_count == 0) $page_title = 'Sorry there are no submissions for this form.';
	else $page_title = $entry_count.' entr'.($entry_count==1?"y":"ies").' found in the database.';


    echo drawFeedback($feedback,$message);


$csvFilename = "";
if($refreshCSV){


	$from="forms_data fd
		LEFT JOIN forms_values fv ON fd.id=fv.data_id
		LEFT JOIN forms_fields ff ON fv.field_id=ff.id
		LEFT JOIN forms_blocks fb ON fb.id = ff.block_id
		WHERE fv.id is not null 
		AND fb.`status`='A'
		AND ff.`status`='A'
		AND fd.form_id=".$form->id." ";

	$select = "SELECT fd.id AS ref, fd.added AS added, ff.id, fd.member_id, ff.name, ff.type, ff.label, fv.value FROM ";
	//$select = "SELECT fd.id AS ref, DATE_FORMAT(fd.added, '%Y/%m/%d %H:%i') AS added, ff.id, fd.member_id, ff.name, ff.type, ff.label, fv.value FROM ";
	$order="ORDER BY fd.added DESC, fv.data_id DESC, fb.sort_order, ff.sort_order ";
	
	// Get the field listing 
	$query = "SELECT distinct ff.name FROM ".$from."ORDER BY fb.sort_order, ff.sort_order ";
	if ($testing) print "Get header: $query<br>\n";
	if ($results = $db->get_results($query)) {
		$i=0;
		foreach ($results as $result) {
			//print "Got field ".$result->name."<br>\n";	
			$header[$i]=$result->name;
			$i++;
		}
		$ff = $header[0];
		$lf = $header[$i-1];
		
		if ($testing) {
			print "Count[$i] fields ".print_r($header, true)."<br>\n";
			print "First field[$ff] to last field[$lf]<br>\n";
		}
		
		// If we have some fields to check then get the values
		if ($i>0) {

			$query=$select.$from.$order;
			if ($testing) print "Get values: $query<br>\n";
			$fcounter = 0;
			if($results = $db->get_results($query)){
				
				$outputCSV = '';
				$num_in_db_when_downloaded = 0;
				$current_index=$this_index=0;
				$row=array();
				
				// Loop through every value entered in every field
				foreach($results as $result){
					
					if ($testing) print "<br>\n-----------------<br>\n$fcounter: result(".print_r($result, 1).")<br>\n";
					if ($result->name==$ff) {
						if ($testing) print "First field located<br>\n";
						$fcounter = 0;
						$fields = "`added`, ";
						$values = "'".$result->added."', ";
						$insert = '';
						$foundff=true;
						$current_index = 0;
					}
					
					// If we have located the first field in a row then process this value
					if ($foundff) {
						for($j=0; $j<$i; $j++) {		
							
							//print "Check name(".$result->name."==".$header[$j].")<br>\n";
							if ($result->name == $header[$j]) {
								if ($testing) print "Save ".$result->name." in row[$j] ".$result->value."<br>\n";
								$row[$j]=$result->value;
								
								// Save data to add to database
								switch ($result->type) {
									case "textarea":
									case "select":
									case "text":
										$fields .= "`".strtolower($result->name)."`, ";
										$values .= "'".$db->escape($result->value)."', ";
										if (!$table) $tcreate .= "`".strtolower($result->name).'` text,'."\n";
										break;
									case "file":
										$fields .= "`".strtolower($result->name)."`, ";
										if ($result->value) $values .= "'http://".$_SERVER['HTTP_HOST']."/silo/files/forms/".$db->escape($result->value)."', ";
										else $values .= "'', ";
										if (!$table) $tcreate .= "`".strtolower($result->name).'` text,'."\n";
										break;
									default :
										if ($testing) print "Not sure how to add(".print_r($result, 1).") to the database<br>\n";
										break;
								}
							
								// Last field, write the CSV/data
								if ($result->name==$lf) {
									
									if ($testing) print "Add row to CSV:".print_r($row, true)."<br>\n";
									$thisRow = '';
									for ($k=0; $k<$i; $k++) {
										$thisRow .= '"'.$row[$k].'",';
									}
									$thisRow = '"'.$result->ref.'","'.$result->added.'",'.$thisRow.'"'.($result->member_id+0).'"'."\r\n";
									if ($testing) print "Add row($thisRow)<br>\n";
									$outputCSV .= $thisRow;
									
									$num_in_db_when_downloaded++;
									$row = array();
									$foundff = false;
									
									if (!$table) {
										// Delete old versions of this form data
										$tableroot = "form".$form->id."_";
										$qtexists = "SHOW TABLES LIKE '$tableroot%'";
										if ($testing) print "Delete old tables($qtexists)<br>\n";
										if ($texists = $db->get_results($qtexists)) {
											foreach ($texists as $texist) {
												foreach ($texist as $k=>$v) {
													if (substr($k, 0, 10)=="Tables_in_" && substr($v, 0, strlen($tableroot))==$tableroot) {
														if ($testing) print "Remove table($v)<br>\n";
														$tremove = "DROP TABLE $v";
														//if ($testing) print "$tremove<br>\n";
														$db->query($tremove);
													}
													//print "Got k($k) v($v)<br>\n";
												}
											}
										}
			
										// Create the table
										$table = $tableroot.date("YmdHi", time());
										$qcreate = "CREATE TABLE $table (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `added` DATETIME, ".substr($tcreate, 0, -2).")";
										if ($testing) print "Create table $qcreate<br>\n";
										$db->query($qcreate);
										if ($db->last_error) print "Error(".$db->last_error.") $qcreate<br>\n";
										else {
											if ($testing) print "Table created<br>\n";
											$tcreated = true;
										}
										
									}
									if ($table && $tcreated) {
										$insert = "INSERT INTO $table (".substr($fields, 0, -2).") VALUES (".substr($values, 0, -2).")";
										//if ($testing) print "Write to database($insert)<br>\n";
										if (!$db->query($insert)) {
											print '<p class="error">Problem writing to database('.$insert.')</p>'."\n";
										}
										else if ($testing) print "Record added to table($table)<br>\n";
									}
								}
								
							}
						}
					}
					$fcounter++;
					
				}

				// Write the header 
				for ($j=0; $j<$i; $j++) {
					$header_line .= '"'.$header[$j].'",';
				}
				$outputCSV = '"REF","DATE",'.$header_line.'"MEMBER"'."\r\n".$outputCSV;

				// Write the file		
				$localPath = "/silo/tmp/";
				$filePath = $_SERVER['DOCUMENT_ROOT'].$localPath;
				
				$fileName = createRandomCSVFilename($site->id);
				//print "Open file(".($filePath.$fileName).")<br>\n";
				$handle = @fopen($filePath.$fileName, "w");
				if ($handle) {
					fwrite($handle, $outputCSV);
					fclose($handle);
				}
				else $fileName = "";
				
				//$link="/treeline/newsletters/download/?id=".$fileName;
				$link = $localPath.$fileName;
		
			}
		}	
	}
	else print "No results found for this form<br>\n";
	
	//else print "Query fail()<br>";
	
	if($fileName == ""){
		echo drawFeedback('error', 'Unable to create CSV file('.$fileName.')');
	}
	else{
		echo drawFeedback('success', 'The form entries have been written to a CSV file');

	$page_html = '
<p>To download this file right-click on the link below and choose \'Save Target As...\'.</p>
<p><a href="'.$link.'" target="_blank" title="Download entries file">'.$fileName.'</a> ('.$num_in_db_when_downloaded.' entr'.($num_in_db_when_downloaded==1?"y":"ies").')</p>
<p><strong>Please note that for security reasons, the file will only be available for 1 hour. After that time it will be automatically deleted. But you can recreate it as often as you like.</strong></p>
	'.$thisCSVOutput;
	}

$page_html.='
<p>To refresh the CSV file <a href="/treeline/forms/?fid='.$form->id.'&amp;action='.$action.'&amp;refresh=1">click here</a>.</p>
';

}
else {

	$page_html ='
		<p>To download the entries database, you first need to create the CSV file.</p>
		<p>To create the CSV file <a href="/treeline/forms/?fid='.$form->id.'&amp;action='.$action.'&amp;refresh=1">click here</a>.</p>
	';
	
}
echo treelineBox($page_html, $page_title, "blue");
?>
