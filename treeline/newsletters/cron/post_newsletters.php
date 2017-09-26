<?php

$live=true;
$summary='';

//print "---------------------------------\n";
//print $_SERVER['PHP_SELF']."<br>\n";
//print dirname($_SERVER['PHP_SELF'])."<br>\n";
chdir(dirname($_SERVER['PHP_SELF']));

error_reporting(E_ALL ^ E_NOTICE);

// Newsletter includes
include("../newsinc.php");
include("../includes/newsletter.class.php");
include ("../includes/email/htmlMimeMail.php");

// Treeline includes
include("../../includes/image.class.php");
include ("../../includes/page.class.php");
include("../../includes/site.class.php");


// Send newsletter emails
// This script is called from CRONTAB only - it is not run from the site at all.
// Sends up to 500 emails per cycle - cycle frequency set by crontab
// NOTE -- currently reduced to FIFTY to ensure nothing goes haywire [we'll also reduce the crontab frequency]
$send_limit=10;


// This query needs to be update to include event subscribers.
$query = "SELECT n.id, n.newsletter_id, n.failed, 
	n.email as event_email, n.digest,
	m.email AS email,
	nl.subject, nl.text, nl.text2, nl.text3, 
	nl.msv, sv.language
	FROM newsletter_outbox n
	LEFT JOIN members m ON n.member_id=m.member_id
	JOIN newsletter nl ON n.newsletter_id = nl.id
	INNER JOIN sites_versions sv on nl.msv=sv.msv
	WHERE n.date_sent = '0000-00-00 00:00:00'
	AND failed = 0
	AND n.newsletter_id != 0
	ORDER BY nl.msv ASC, nl.id ASC, n.id ASC
	LIMIT 0, $send_limit";
$summary.=$query."\n";

$total = 0; 
$sent = 0;

$site=new site();

//print "$query<br>\n";

if($results = $db->get_results($query, "ARRAY_A")) {

	$thisNewsletter = NULL; // Store the Newsletter Object
	$thisNewsletterId = NULL; // Store the Newsletter ID
	$strHTMLEmail = "";
	$strHTMLPlain = "";
	//$summary.='Starting newsletter run process...'."\n";

	foreach($results as $m) {

		
		//$summary.="Got a record to send id=".$m['newsletter_id']." current id(".$thisNewsletterId.")\n";
		$total++;
		$m['date_sent']  = date("Y/m/d H:i:s", time());

		// Check if we are moving onto a new site?
		if ($m['msv']!=$site->id) {
			// We may need to re-define some of the set variables here?
			//$summary.="This newsletter is not from site 1(".$m['msv'].") needs DEFINED parameters changing!! \n";
			$site->loadBySiteID($m['msv']);
		}

		// Check if we are moving on to a new newsletter
		if($thisNewsletterId != $m['newsletter_id']){
		
			// New newsletter, go get data
			$thisNewsletterId = $m['newsletter_id'];
			//$summary.="Sending newsletter($thisNewsletterId) \n";
			
			$thisNewsletter = new newsletter($thisNewsletterId);
			$thisNewsletter->compileDigestPageHTML($m['digest']);

			$thisPage = new Page();

			$siteLang=$m['language'];
			$thisNewsletter->labels=$thisPage->getTranslations($msv, $siteLang);

			$thisNewsletter->subject = stripslashes($m['subject']);
			$thisNewsletter->html_text = stripslashes($m['text']);
			$thisNewsletter->html_text2 = stripslashes($m['text2']);
			$thisNewsletter->html_text3 = stripslashes($m['text3']);
			$thisNewsletter->plain_text = stripslashes($m['text']);
			$thisNewsletter->plain_text2 = stripslashes($m['text2']);
			$thisNewsletter->plain_text3 = stripslashes($m['text3']);

			
			// Prep Email texts
			$strHTMLEmail = $thisNewsletter->getHTMLEmail();
			$strHTMLPlain = $thisNewsletter->getPlainEmail();
			$strHTMLEmail = $thisNewsletter->setUnsubscribe($strHTMLEmail, $m['email']?$m['email']:$m['event_email']);
		}

		// Send email.
		if($thisNewsletter->isValid() && $site->id==$m['msv']){

			$mail = new htmlMimeMail();
			$thisemail = $m['email']?$m['email']:$m['event_email'];

			// Get std_data array, have to set them to null even if we don't get a member which 
			// we shold here.
			$std_data = array();
			$query = '';
			if ($m['email']) $query = "SELECT title, firstname, surname FROM members WHERE email='".$m['email']."' LIMIT 1";
			else $query = "SELECT title, forenames AS firstname, surname FROM event_entry_data WHERE email='".$m['event_email']."' LIMIT 1";
			$row=$db->get_row($query);
			//$summary.="get data($query)<br>\n";
			$std_data['/@@TITLE@@/']=$row->title;
			$std_data['/@@FIRSTNAME@@/']=$row->firstname;
			$std_data['/@@SURNAME@@/']=$row->surname;
			$std_data['/@@FULLNAME@@/']=$row->title." ".$row->firstname." ".$row->surname;
			
			// Update plain text email values.
			$data = $std_data;
			// Unsubscribe link
			$data['/email=xxx/']="oid=".$outbox_id;
			// Link counter
			//$data['/http:\/\/(.*?)[ |\\n|\\r]/']=$site->root.'treeline/newsletters/link.php?oid='.$outbox_id.'&hr=http://$1"';
			///$data['/http:\/\/(.*?)[ |\\n|\\r]/']=$site->root.'treeline/newsletters/link.php?hr=http://$1"';
			$newHTMLPlain = $thisNewsletter->setData($data, $strHTMLPlain);
			
			unset($data);	// Dont do this for html as it kills images etc.
			$data = $std_data;
			// Unsubscribe link
			$data['/email=xxx/']="oid=".$outbox_id;
			// Email delivery counter. (we can only count html email as rely on image views)
			// To se this up you must set up /img/email/emailtrack.php to run instead of your image png.
			$data["/tracker-image/"]="tracker.png?oid=".$outbox_id;
			// Link counter
			//$data['/href="(.*?)"/']='href="'.$site->root.'treeline/newsletters/link.php?oid='.$outbox_id.'&hr=$1"';
			$newHTMLEmail = $thisNewsletter->setData($data, $strHTMLEmail);

			// Email checks out, send the test mail
			$tmp_from_addr=$site->name."<".($site->contact['email']?$site->contact['email']:NEWSLETTER_FROM_EMAIL).">";
			$mail->setFrom($tmp_from_addr);	
			$mail->setReturnPath($site->name.'<bounce@rack.ichameleon.com>');	
			
			$mail->setHeader("X-NEWSENDID", $outbox_id);
			$mail->setHeader("X-TLCLIENTID", $client_id);
			
			unset($data);
			$data['/@@SITENAME@@/']=$site->title;
			$mail->setSubject($thisNewsletter->setData($data, $thisNewsletter->subject));

			if (($sent+1)%60==0) {
				$summary.="Added BCC to alert email. \n";
				$mail->setBcc(ALERT_EMAIL);			
			}

			$mail->setHtml($newHTMLEmail, $newHTMLPlain, NULL);
			$mail->is_built = false;
			
			//$summary.="Sending newsletter(".($live?"YES":"NO").") to ".($m['email']?$m['email']:$m['event_email'])."\n";
			if ($live) $mail->send(array($thisemail));
			else {
				$summary.="would have of sent to (".$m['email'].")\n";
				//$summary.="content(".$strHTMLEmail.") \n";
			}

			$m['failed'] = 0;
			
			$sent++;

		}
		else if (!$thisNewsletter->isValid()) {
			// Some error with Newsletter - fail the record.
			$summary.="Newsletter($thisNewsletterId) is invalid \n";
			$m['failed']=1;
		}
		else {
			$summary.='We dont appear to have a valid site loaded????';
			$m['failed']=1;
		}

		// Update DB to show that email has gone.
		$query = 
			"UPDATE newsletter_outbox " .
			"SET failed = " . $m['failed'] . ", " .
			"date_sent = '" . $m['date_sent'] . "' " .
			"WHERE id = " . $m['id'];
		//print "would update($query)\n";
		if ($live) $db->query($query);

	}
}


if ($summary) {
	$summary.=date("d-m-Y H:i", time())." - Sent $sent of $total \n";
	//print nl2br($summary);
	print $summary;

	$headers = "From: $site_name web server <$tech_email> \r\n";
	$headers .= 'X-Mailer: Treeline v3 using PHP/'.phpversion();
	mail(ALERT_EMAIL, "Newsletter send summary", getcwd()."\n\n".$summary, $headers);
}
exit;

?>
