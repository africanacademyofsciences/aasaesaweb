<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

session_start();

$refreshCSV = addslashes($_REQUEST["refresh"]);
if($refreshCSV != "1"){
	$refreshCSV = false;
}else{
	$refreshCSV = true;
}



function createRandomCSVFilename(){
	// Creates a filename for the CSV file.
	$alpha = "abcdefghijklmnopqrstuvwxyz0123456789012345678901234567890123456789";
	$filename_ok = false;
	$filename = "ch_subs_" . $alpha{rand(0, strlen($alpha) - 1)};
	for($i = 0; $i < 6; $i++){
		$filename .= $alpha{rand(0, strlen($alpha) - 1)};
	}
	return($filename . ".csv");
}

if(sizeof($results) == 0) $page_title = 'Sorry there are no records';
else $page_title = sizeof($results).' '.$page->drawGeneric('record'.(sizeof($results)== 1?"":"s")).' '.$page->drawGeneric("found");

// They have requested to refresh the CSV file
if($results){

	$thisCSVOutput = "firstname,surname,status,date_added,email \r\n";

	$num_in_db_when_downloaded = 0;
	foreach($results as $result){
		$firstColDone = false;
		$thisRow = "";
		foreach($result as $row){
			if($firstColDone){
				$thisRow .= ",";
			}else{
				$firstColDone = true;
			}
			$val = stripslashes($row."" == "" ? "" : trim($row));
			$val = str_replace("\"", "\"\"", $val);

			$thisRow .= "\"" . $val . "\"";
		}
		$thisRow .= "\r\n";
		$thisCSVOutput .= $thisRow;
	}

	// Create filename for member listing
	$localPath = "/silo/tmp/";
	$filePath = $_SERVER['DOCUMENT_ROOT'].$localPath;
	$fileName = createRandomCSVFilename();

	//print "try to open ($filePath $fileName)<br>";		
	$handle = @fopen($filePath.$fileName, "w");
	if ($handle) {
		fwrite($handle, $thisCSVOutput);
		fclose($handle);
	}
	else $fileName = "";
	
	$link = $localPath.$fileName;

}
//else print "Query fail()<br>";

if($fileName == ""){
	echo drawFeedback('error', $page->drawLabel("tl_mem_dl_nocreate", 'Unable to create CSV file').'('.$fileName.')');
}
else{
	echo drawFeedback('success', $page->drawLabel("tl_mem_dl_success", 'The subscriber database has been written to a CSV file'));
	
	$page_html = '
	<p>'.$page->drawLabel("tl_mem_dl_msg1", "To download this file right-click on the link below and choose 'Save Target As...'").'</p>
	<p><a href="'.$link.'" target="_blank" title="Download members file">'.$fileName.'</a> ('.(sizeof($results)." ".$page->drawGeneric("record".(sizeof($results)==1?"":"s"), 1)).')</p>
	';
} 

$page_html.='
<p>'.$page->drawLabel("tl_mem_dl_refresh", "To refresh the CSV file").' <a href="?action=download&amp;status='.$status.'&amp;q='.$search.'&amp;sort='.$orderBy.'&amp;refresh=1">'.$page->drawGeneric("click_here").'</a></p>
';

echo treelineBox($page_html, $page_title, "blue"); 
?>