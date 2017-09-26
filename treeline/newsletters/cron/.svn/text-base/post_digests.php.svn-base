<?php

//$summary = date("d-m-Y H:i")." : running \n";
$testing = false;

chdir(dirname($_SERVER['PHP_SELF']));

include("../newsinc.php");

$count = $err_count = 0;

// Set this to a valid new date. 
// This can be an expression which resolves to a date 
// or you can use PHP to format the new date as "YYYY:MM:DD";
$mysql_interval = 'NOW()+INTERVAL 1 HOUR';
// If you want to reschedule for a set time each cycle based on the time originally
// Set in the newsletter table use the first format below. If you prefer to reschedule for 
// a time based on the interval. This is very useful if you need to reschedule every so many 
// minutes or hours.
//$mysql_time_interval = "DATE_FORMAT(added_date, '%H:%i:%s')";		// Send same time every day
$mysql_time_interval = "DATE_FORMAT($mysql_interval, '%H-%i-%s')";	// Send at time based on current time.

// Find out when the next digest is due.
$query = "SELECT id, 
	added_date, 
	UNIX_TIMESTAMP(added_date) AS added,
	UNIX_TIMESTAMP(NOW()) AS now,
	CONCAT(DATE_FORMAT($mysql_interval, '%Y-%m-%d'), ' ', $mysql_time_interval) as new_date,
	msv
	FROM newsletter WHERE text3='DIGEST' AND `status`='N' 
	ORDER BY id ASC
	LIMIT 1";
$summary.="Check if due ($query) \n";
if ($row = $db->get_row($query)) {
	$seconds_till_due = $row->added - $row->now;
	$summary.= date("d-m-Y H:i")." : due(".$row->added.") now(".$row->now.") in (".$seconds_till_due.") seconds \n";
	//$summary.="  send newsletter(".$row->id.") \n";
	//$summary.="  send newsletter(".$row->new_date.") \n";

	if ($seconds_till_due < 0) {

		// Reschedule the digest - FIRST.
		// In case there are problems we don't want to keep trying over and over again.
		$new_date = $row->new_date; 		// Last chance to override
		//$new_date = date("Y-m-d H:i:s", time()+(3600*24));		// Do it every two hours for now.
		$query = "UPDATE newsletter SET added_date = '".$new_date."' WHERE id = ".$row->id;
		$summary.="$query \n";
		if (!$db->query($query)) {
			//print "Failed to update newsletter table :oO \n";
			$summary.="Failed to update the newsletter table - THIS IS VERY VERY BAD \n";
			$err_count++;
		}
		else {

			// A digest needs to be sent out now.
			//$summary.= date("d-m-Y H:i")." : Send the digest \n";	
		
			// Go through the newsletter_digest_categories table and find all members that exist in the table 
			// then add them to the newsletter_outbox table.
			// post_newsletters.php should do the rest next time it kicks in :o)
			$query = "SELECT DISTINCT member_id FROM newsletter_user_preferences WHERE preference_id=".$digest_preference_id;	
			//print "$query \n";
			//$summary.="$query \n";
			if ($results = $db->get_results($query)) {
				foreach($results as $result) {
					$query = "INSERT INTO newsletter_outbox (newsletter_id, member_id) VALUES (".($row->id+0).", ".($result->member_id+0).") ";
					//print "$query<Br>\n";
					if (@$db->query($query)) {
						$count++;
						$summary.=date("d-m-Y H:i")." : $query \n";
					}
				}
				
			}
			else {
				$summary .= "Nobody at all is signed up for digests. This is a bit unlikely \n";
				$err_count++;
			}
			
			// Record a summay in the digests table if we can be bothered.
			$query = "INSERT INTO newsletter_digest (total, msv) VALUES (".($count+0).", ".($row->msv+0).") ";
			$summary.= date("d-m-Y H:i")." : $query \n";
			if (!$db->query($query)) {
				$summary.="Failed to log digest \n";
				$err_count++;
			}
		}
	}
}
else {
	$summary.= date("d-m-Y H:i", time())." - Failed to check next due time \n";
	$err_count++;
}


//$summary.=date("d-m-Y H:i", time())." - Sending digest summary ".($count+0)." scheduled \n";
if ($summary) {
	//print nl2br($summary);
	print $summary;

	if ($err_count>0 || $testing) {
		if (!$site_name) $summary.= "\n------------\nFROM :".getcwd()."\n-----------\n";
		$headers = "From: $site_name web server <$tech_email> \r\n";
		$headers .= 'X-Mailer: Treeline v3 using PHP/'.phpversion();
		mail(ALERT_EMAIL, "Digest processing summary", $summary, $headers);
	}
}
exit;

?>