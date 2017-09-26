<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
//require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");

include "includes/email/htmlMimeMail.php";

session_start();

global $siteID;

$nid = $_REQUEST['id'];
$mode = $_REQUEST['mode'];

$nextPage = "newssend/?id=$nid";

if($_POST["test_send"]){

	$page=new Page();
	$newsletter = new newsletter($nid);
	$newsletter->labels=$page->getTranslations($_SESSION['treeline_user_site_id'], $_SESSION['treeline_user_language']);

	//print "2(".$newsletter->html_text2.") 3(".$newsletter->html_text3.")<br>";
	
	if ($newsletter->validate()) {
		
		// Prep Email texts
		$strHTMLEmail = $newsletter->getHTMLEmail();
		$strHTMLPlain = $newsletter->getPlainEmail();

		// Strip junk, leaving just semi-colon seperated email addys
		$strEmailList = str_replace(" ", "", $_POST["nl_test_send_emails"]);
		$strEmailList = str_replace("\r", "", $strEmailList);
		$strEmailList = str_replace("\n", ";", $strEmailList);

		$emails = explode(";", $strEmailList);
		$sent = 0;

		$mail = new htmlMimeMail();

		for($n = 0; ($n < count($emails)) && ($sent < 10); $n++){

			$e = $emails[$n];

			if(trim($e) != ""){
			
				if($newsletter->validateEmail($e)){
					$strHTMLEmail = $newsletter->setUnsubscribe($strHTMLEmail, $e);

					// Email checks out, send the test mail
					$mail->setFrom(NEWSLETTER_FROM_EMAIL);
					$mail->setReturnPath(NEWSLETTER_FROM_EMAIL);
					$mail->setSubject('Test: '.$newsletter->subject);
	
					$mail->setHtml($strHTMLEmail, $strHTMLPlain, null);
					$mail->is_built = false;
					$result = $mail->send(array($e));
//print "got mail result($result)<br>"; exit();
					$sent++;
					
					// Should log this from/to/nid/date/TEST somewhere???
				}
			}
		}
		$nextPage.="&test=$sent&mode=".$mode;
		
	}else {
		// News letter was not valid. Really it should not be possible to get here with an invalid email.
	}

}else if($_POST["send_now"]){
	// Send now - set up the mailing list.
	
	$newsletter = new newsletter($nid);

	if($newsletter->validate()){


		// Take all email addresses from the mailing list and add them to the outbox table
		// First add all preference related member ids....
		$strSQL = "INSERT INTO newsletter_outbox (newsletter_id, member_id)
			SELECT '".$newsletter->id."', m.member_id 
			FROM newsletter_user_preferences nup
			LEFT JOIN members m ON m.member_id = nup.member_id 
			WHERE nup.preference_id in ( 
				SELECT preference_id
				FROM newsletter_send_preferences nsp
				WHERE newsletter_id = '".$newsletter->id."'
				)
            GROUP BY m.email ";
		//print "$strSQL<br>";
		$db->query($strSQL);

		// Next we need to add any event subscribers if there are any.
		$query = "INSERT INTO newsletter_outbox(newsletter_id, email)
			SELECT ".$newsletter->id.", email
			FROM event_entry ee 
			LEFT JOIN event_entry_data eed ON ee.id=eed.entry_id
			WHERE ee.registered>0 AND eed.email IS NOT NULL 
			AND ee.event_guid in (
				SELECT event_id 
				FROM newsletter_send_preferences nsp
				WHERE newsletter_id=".($newsletter->id+0)."
				AND event_id IS NOT NULL
				)
			GROUP by eed.email ";
		//print "$query<br>\n";
		$db->query($query);

		// Update the Newsletter record so it no longer shows as sendable
		$query =  "UPDATE newsletter " .
			"SET done = 1, " .
			"send_date = '" .date("Y/m/d H:i:s", time()). "' " .
			"WHERE id = " . $newsletter->id;
		//print "$query<br>";
		$db->query($query);
		$nextPage = "newsbrowse/";

	}

}
else if($_POST["send_later"]){

	// Save the newsletter and return to the main screen
	$nextPage = "newsbrowse/";

}
else if($_POST["edit"]){

	// Go back and edit the newsletter
	$nextPage = "newsedit/?action=edit&id=$nid";

}


$redirectURL="Location: http://" . $_SERVER['HTTP_HOST']
				 . dirname($_SERVER['PHP_SELF'])
				 . (dirname($_SERVER['PHP_SELF']) == "/" ? "" : "/") . $nextPage;
//print "would go to($redirectURL)<br>";
header($redirectURL);


?>