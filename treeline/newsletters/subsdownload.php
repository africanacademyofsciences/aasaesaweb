<?php

//ini_set("display_errors", 1);

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");

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

	$from="FROM members m 
		LEFT JOIN newsletter_user_preferences nup ON m.member_id = nup.member_id
		LEFT JOIN newsletter_preferences np ON nup.preference_id = np.preference_id
		WHERE np.site_id=".$site->id."
		AND np.deleted=0 
		";
	$query="SELECT count(*) ".$from;
	//print "$query<br>\n";
	if($db->query($query)) $subs_count = $db->get_var();

	$query = "SELECT email, concat(firstname, ' ', surname) as name, date_added, np.preference_title
		".$from."
		ORDER BY np.preference_title, date_added ASC
		";
	//print "$query<Br>";

	if($results = $db->get_results($query)) {

		$thisCSVOutput = "email,fullname,date_changed,preferences\r\n";

		foreach($results as $c){
			$firstColDone = false;
			$thisRow = "";
			foreach($c as $v){
				if($firstColDone){
					$thisRow .= ",";
				}else{
					$firstColDone = true;
				}
				$val = stripslashes($v."" == "" ? "" : trim($v));
				$val = str_replace("\"", "\"\"", $val);

				$thisRow .= "\"" . $val . "\"";
			}
			$thisRow .= "\r\n";
			$thisCSVOutput .= $thisRow;
		}

		$filePath = $_SERVER['DOCUMENT_ROOT']."/silo/tmp/";
		$fileName = createRandomCSVFilename($siteID);
		
		//print "try to open ($filePath $fileName)<br>";		
		if ($fp = @fopen($filePath.$fileName, "w")) {
			fwrite($fp, $thisCSVOutput);
			fclose($fp);
			
			$message[]=$page->drawLabel("tl_nl_dl_written", 'The subscriber database has been written to a CSV file');
			$link = "/silo/tmp/".$fileName;
		}
		else $message[] = 'Unable to create CSV file('.$fileName.')';

	}
	else $message[]=$page->drawLabel("tl_nl_dl_nosubs", "No subscribers found");

	// PAGE specific HTML settings
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("newsletters", "Download subscribers");
	$pageClass = 'newsletters';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	

?>
<div id="primarycontent">
<div id="primary_inner">

	<?php
    echo drawFeedback($feedback,$message);

    if($subs_count == 0) {
		$page_title = $page->drawLabel("tl_nl_dl_errtitle", 'Sorry there are no records in the mailing list');
		$page_html = $page->drawLabel("tl_nl_dl_errmsg1", 'No subscribers were found for this site');
	}
    else {
		$page_title = $subs_count.' '.$page->drawGeneric("record".($subs_count==1?"":"s")).' '.$page->drawLabel("tl_nl_dl_foundtot", "found in the mailing list");

		$page_html = '
		<p>'.$page->drawLabel("tl_nl_dl_msg1", "Click to open or to download this file right-click on the link below and choose the save file option").'</p>
		<p><a href="'.$link.'" target="_blank" title="Download susbscribers file">'.$fileName.'</a> ('.$subs_count.' '.$page->drawGeneric("record".($subs_count==1?"":"s")).')</p>
		<!--
		<p>Please note that for security reasons, the file will only be available for 1 hour. After that time it will be automatically deleted. But you can recreate it as often as you like</p>
		<p>To refresh the CSV file <a href="/treeline/newsletters/subsdownload/?refresh=1">click here</a></p>
		-->
		';
	}
	echo treelineBox($page_html, $page_title, "blue");
	?>
    
</div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>
