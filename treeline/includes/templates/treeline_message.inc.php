<?php 

// We are not totally sure where we are picking up this information from yet.
// 
// So for now lets assume if its here we show it.
$html = '';

if ($_SESSION['show_tl_message']) {

	$query = "SELECT description FROM announcement WHERE 
		".($cid>0?"cid=$cid OR ":"")."
		cid=0
		ORDER BY cid DESC , added DESC";
	//print "$query<br>\n";

	if ($results = $db_admin->get_results($query)) {
		foreach($results as $result) {
			$tl_message.=$result->description;
		}
		if($tl_message){
			$ticker_script = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/treeline/behaviour/pause_marquee.js'); 
			$html .= str_replace("@@TICKER@@", $tl_message, $ticker_script);
		}
		if ($html) {
			?>
	<table id=announcement-table>
	<tr>
		<td class="ann-title" nowrap><?=$page->drawLabel("tl_head_announce", "Treeline Announcements")?></td>
		<td class="ann-ticker"><?=$html?></td>
	</tr>
	</table>
			<?php
		}
	
		// Dont show the message again this time.
		$_SESSION['show_tl_message']=false;
	}
}
unset($html);

?>