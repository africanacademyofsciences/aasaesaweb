<?php
ini_set("display_errors", 1);
error_reporting(1);

chdir(dirname($_SERVER['PHP_SELF']));

$msg = '';
$redirectURL = $_GET['hr'];

if (!$redirectURL) $redirectURL = "/";
else {

	$outbox_id = $_GET['oid'];
	if ($outbox_id>0) {
		// Newsletter includes hopefully this will give us a db variable.
		include(getcwd()."/../../treeline/newsletters/newsinc.php");
		
		// 1 - Get the newsletter id
		if ($row = $db->get_row("SELECT newsletter_id, member_id FROM newsletter_outbox WHERE id = ".$outbox_id." LIMIT 1")) {
		
			$newsletter_id = $row->newsletter_id;
			$member_id = $row->member_id;
		
			// 2 - Get the campaign ID or create a new one.
			$campaign_id = $db->get_var("SELECT id FROM campaigns WHERE newsletter_id = ".$newsletter_id);
			if (!$campaign_id) {
				$query ="INSERT INTO campaigns 
					(newsletter_id, title, date_created, date_sent, mail_count)
					VALUES 
					($newsletter_id, 'Newsletter:$newsletter_id', NOW(),
					(SELECT send_date FROM newsletter WHERE id=$newsletter_id), 
					(SELECT count(*) FROM newsletter_outbox WHERE newsletter_id=$newsletter_id)
					)";
				if (!$db->query($query)) $msg.="Failed to create new campaign($query) \n";
				else $campaign_id = $db->insert_id;
			}
			
			//$msg.="\n";
			
			//$msg.="Check red($redirectURL == http://unsubs:oid=".$outbox_id.")\n";
			$unsub=0;
			if ($redirectURL=='http://unsubs:oid='.$outbox_id) {
				$redirectURL = "/enewsletters/?oid=".$outbox_id."&mid=".$member_id;
				$unsub = 1;
			}

			if ($campaign_id>0) {
				$query = "INSERT INTO campaign_link_stats
					(campaign_id, link, date_clicked, unsub, outbox_id)
					VALUES
					($campaign_id, '$redirectURL', NOW(), ".($unsub+0).", $outbox_id)
					";
				if (!$db->query($query)) {
					// Not really the end of the world as we only care that they click it once.
					// multiple clicks produce a failure here due to table indexing. 
					// We could add a counter instead but I don't think we need to know how many
					// times a person clicked each link.
					//$msg.="Failed to add link ($query) \n";
				}
			}
			else $msg.="Failed to get campaign ID \n";
			
		}
		else $msg.='Failed to collect newsletter_id for outbox_id '.$outbox_id."\n";
	}
	else $msg.="link.php called with no oid \n";
}	

if ($msg) err($msg);

header("location: $redirectURL\n\n");
exit(0);

function err($s) {
	mail("phil.redclift@ichameleon.com", "news just got a thingy", getcwd()."\n\n".$s);
}

?>