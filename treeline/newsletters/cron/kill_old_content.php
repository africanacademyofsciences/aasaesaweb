<?php
/*

Run from crontab once a month

You can run this safely from the command line at any point just by going to the 
/server/treeline/newsletter/cron directory and typing
php kill_old_content.php
*/
$testing=true;

$msg=date("d:m:Y H:i")." : Kill content summary $site_name \n------------------------------------------------------ \n".$msg;

chdir(dirname($_SERVER['PHP_SELF']));

include("../newsinc.php");


// 1 - Deletes all content with a revision_id less than -20 (configurable?)
// ========================================================================
if ($min_revision_id > 0) $min_revision_id=-$min_revision_id;
if (!$min_revision_id || $min_revision_id > -10) $min_revision_id=-20;

// That all looks perfectly safe but one final check as we really would not want this going wrong
$msg.="1 - Delete revisions for min ($min_revision_id) \n";
if ($min_revision_id < -9) {

	$msg.="Remove all content with revision_id less than $min_revision_id \n";
	
	$query = "delete from content where revision_id < $min_revision_id";
	$msg.=$query."\n";
	
	$db->query($query);
	if (mysql_affected_rows()>0) $msg.= "Deleted ".mysql_affected_rows()." records from content \n";
	else $msg.="No records deleted \n";

}



// 2 - Remove old page locks
// =========================
$query = "SELECT id, lock_guid, lock_time FROM users where lock_time < NOW() - INTERVAL 3 HOUR LIMIT 3";
$msg.="\n 2 - Remove old page locks\n";
$removed=0;
if ($results = $db->get_results($query)) {
	foreach ($results as $result) {
		$msg.="User ".$result->id." has a lock on page(".$result->lock_guid.") started ".$result->lock_time."\n";
		$query  = "UPDATE users SET lock_guid='', lock_time = NULL WHERE id=".$result->id;
		$db->query($query);
		$removed++;
	}
}
$msg.="Removed $removed lock(s)\n";



// 3 - Remove old CSV files from tmp directories
//$dir = "../db/";
$msg.="\n 3 - Blat old temporary files\n";
$dir_list = array();
$dir_list[0] = array('name'=>$_SERVER['DOCUMENT_ROOT']."/silo/tmp/", 'ext'=>'csv', 'time'=>(60*60));	// Remove csvs from over an hour ago
$dir_list[1] = array('name'=>$_SERVER['DOCUMENT_ROOT']."/silo/pdf/", 'ext'=>'pdf', 'time'=>(60*60*4));	// Remove pdfs from over 4 hours ago
$dir_list[2] = array('name'=>$_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/cron/", 'ext'=>'log', 'time'=>(60*60*24*30));	// Remove logs if they are over a month old

foreach ($dir_list as $tmp) {
	$dir = $tmp['name'];
	$del_ext = $tmp['ext'];
	$delay = $tmp['time'];

	//$msg.="Dir($dir) del($del_ext) from($delay) \n";

	$dh  = opendir($dir);
	$removed = $idx = 0;

	while (false !== ($filename = readdir($dh))) {
		if($filename != ".." && $filename != "."){
		  $files[] = $filename;
	
			$ext = substr($filename, -3, 3);
			if ($ext ==  $del_ext) {
				$thisfile = $dir.$filename;
				if (!file_exists($thisfile)) $msg.="File($thisfile) does not exist?? \n";
				else {
					$timediff = (time() - fileatime($thisfile));
					if($timediff > $delay){
						$msg.=date("d-m-Y H:i", time())."unlink($thisfile) created(".date("d-m-Y H:i", fileatime($thisfile)).")\n";
						unlink($thisfile);
						$removed++;
					}
					else $msg.="File($thisfile) is only ($timediff < $delay) seconds old \n";
				}
				$idx++;
			}
		}
	}
	$msg.="Removed($removed of $idx) $del_ext files from $dir \n";
}


// 4 - Do email bounce processing
/*
if ($client_id>0) {
	$msg.="Do bounce processing for ".$siteName."\n";
	$query = "SELECT * FROM newsletter_bounce nb
		LEFT JOIN newsletter_bounce_type nbt ON nb.bounce = nbt.id
		WHERE nb.cid=$client_id 
		AND nb.proc=0";
	$msg.=$query."\n";
	if ($results = $db_admin->get_results($query)) {
		foreach ($results as $result) {
			$outbox_id = $result->nob;
			$bounce = $result->bounce;
			$soft = $result->soft;
			if ($outbox_id > 0) {
				// Set this bounce reason for this newsletter
				$query = "UPDATE newsletter_outbox SET bounced=".($bounce+0)." WHERE id = $outbox_id";
				$msg .= $query."\n";
				$db->query($query);
				// We have logged the bounce cause.
				if (!$db->last_error) {
					// Mark this record as processed,
					$query = "UPDATE newsletter_bounce SET proc=1 WHERE id=".($nbid+0);
					//$msg.="Update the proc table($query)\n";
					$db_admin->query($query);

					// If a hard bounce then don't mail this member again.
					if (!$soft && $bounce>1) {
						if ($member_id = $db->get_var("SELECT member_id FROM newsletter_outbox WHERE id=".$outbox_id)) {
							$msg .= "Remove member($member_id) from all newsletters \n";
							$query = "DELETE FROM newsletter_user_preferences WHERE member_id = ".$member_id;
							$db->query($query);
						}
						else $msg .= "Failed to get member ID\n";
					}
				}
				else $msg.="Failed to update outbox($query) \n";
			}
			else $msg.="Failed to get outbox ID \n";
		}
	}
}
*/



if ($msg) {
	print $msg;
	
	$headers = "From: $site_name web server <$tech_email> \r\n";
	$headers .= 'X-Mailer: Treeline v3 using PHP/'.phpversion();
	mail(ALERT_EMAIL, "kill content for ".$site_name, $msg, $headers);
}




?>