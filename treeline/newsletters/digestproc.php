<?php

	ini_set('display_errors','2');
	ini_set('display_startup_errors','1');
	error_reporting (E_ALL); 

include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/digest.class.php");

include "includes/email/htmlMimeMail.php";

session_start();
if (!isset($_SESSION['digestContent'])){
	$_SESSION['digestContent'] = $_POST['digestContent'];
} else if ($_POST['digestContent'] != null){
	$_SESSION['digestContent'] = $_POST['digestContent'];
}
//$_SESSION['digestContent'] = mysql_real_escape_string($_POST['digestContent']);
$nextPage = "digestedit/?action=".$_POST['action'];
$digestContent = mysql_real_escape_string($_POST['digestContent']);

//print_r($_SESSION); exit;
//First work out the time length for either events or news

$timeValue = 0;
$error = 0;

if($_POST["action"] == "News"){
		
		$timeValue = getPastLength($_POST['timeLength']);

		$newsData = getNewsItems($timeValue);		
		
} else if($_POST["action"] == "Opportunities"){
		
		$timeValue = getPastLength($_POST['timeLength']);
		
		$oppData = getOppItems($timeValue);		
}  

else if ($_POST["action"] == "Events"){
		
		$timeValue = getFutureLength($_POST['timeLength']);
		
		$eventData = getEventItems($timeValue);
		
		//print_r($data);
		
}

if (!$timeValue ){ //Then it must be for all digest options

		$newsLength = getPastLength($_POST['newsLength']);
		$opportunityLength = getPastLength($_POST['opportunityLength']);
		$eventLength = getFutureLength($_POST['eventLength']);
				
		$newsData = getNewsItems($newsLength);
		$oppData = getOppItems($opportunityLength);
		$eventData = getEventItems($eventLength);
		
		//echo "Opportunity: ".$opportunityLength." Event: ".$eventLength. " News: ".$newsLength;	
}

$textAll = "";
		
if ($newsData != null){
	$title = "News";
	$textNews = drawNews($newsData, $title);
	$textAll .= $textNews;
} else {
	$newsData = false;
	//echo "No news data" . $newsData;
}
	
if ($oppData != null) {
	$title = "Opportunities";
	$textOpp = drawOpp($oppData, $title);
	$textAll .= $textOpp;
} else {
	$oppData = false;
	//echo "No opp data". $oppData;
}

if ($eventData != null){
	$title = "Events";
	$textEvents = drawEvents($eventData, $title); 
	$textAll .= $textEvents;			
} else {
	$eventData = false;
	//echo "No event data" . $eventData;
}

if($_POST['action'] == "All"){
		
	$text = $textAll;
			
} else {
	if ($textNews != null && $_POST['action'] == "News"){
		$text = $textNews;
	} else if ($_POST['action'] == "News") {
		//$text = null;
		$nextPage.="&message=No items can be found for news. Try a different date.";
	}
		
	if ($textOpp != null && $_POST['action'] == "Opportunities"){
		$text = $textOpp;
	} else if ($_POST['action'] == "Opportunities"){
		//$text = null;
		$nextPage.="&message=No items can be found for opportunities.. Try a different date.";
	}
			
	if ($textEvents != null && $_POST['action'] == "Events"){
		$text = $textEvents;
	} else if ($_POST['action'] == "Events"){
		//$text = null;
		$nextPage.="&message=No items can be found for events. Try a different date.";
	}
}

//Could probably put the two above in one if statement maybe.

if($_POST["test_send"]){

		if ($text){
			
			$text = $digestContent . $text;
			
			//echo $text;
			
			//Send newsletter - put in newsletter text.
			
			$newsletter = new newsletter(null, $text);
			
			// Prep Email texts - Get news content here! Call functions from newsletter?
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
						//$strHTMLEmail = $newsletter->setUnsubscribe($strHTMLEmail, $e);
						
						// Email checks out, send the test mail
						$mail->setFrom(NEWSLETTER_FROM_EMAIL);
						$mail->setReturnPath(NEWSLETTER_FROM_EMAIL);
						$mail->setSubject('Test: '.$_POST["nl_test_send_emails"].' digest');
		
						$mail->setHtml($strHTMLEmail, $strHTMLPlain, null);
						$mail->is_built = false;
						$result = $mail->send(array($e));
	//print "got mail result($result)<br>"; exit();
						$sent++;
						
						// Should log this from/to/nid/date/TEST somewhere???
					}
				}
			}
			$nextPage.="&test=$sent";
			
		} else { 
			$nextPage.="&message=No items can be found. Try a different date.";
		}
	
	
}else if($_POST["send_now"]){
	
				
		$action = $_POST['action'];
		
		//Check that some data will actually be sent in the Newsletter Digest.
		
		if (!$text){
			$nextPage.="&message=No items can be found. Try a different date.";
		} else { //Data found
			
			if ($action == 'All'){
				$allTimes = array($newsLength, $opportunityLength, $eventLength);
				$timeValue = serialize($allTimes);
			} 
			
			$addDigest = "INSERT INTO newsletter_digest (digestType, digestTime, digestContent) ".
					  	"VALUES ( '$action', '$timeValue', '$digestContent' )";
			//echo $addDigest; exit;
			$db->query($addDigest);     
			$digestId = $db->insert_id;            
				
			$strSQL = "INSERT INTO newsletter_outbox (digest, newsletter_subscription_id) ".
					  	"SELECT '$digestId', s.id ".
					  	"FROM newsletter_subscription s ".
					  	"WHERE s.opted_in = 1 ".
			            "GROUP BY s.email";
					  
				//echo $strSQL; exit;
			$db->query($strSQL);
								// Update the Newsletter record so it no longer shows as sendable
			$nextPage = "../digestedit/";
			$nextPage.="?success=1";	
			$_SESSION['digestContent'] = null;
			unset($_SESSION['digestContent']);
			
		}
	}



header("Location: http://" . $_SERVER['HTTP_HOST']
						 . dirname($_SERVER['PHP_SELF'])
						 . (dirname($_SERVER['PHP_SELF']) == "/" ? "" : "/") . $nextPage);


?>