<?php

// TESTING
// You can test this script in a browser by adding ?force=1 to the demo page URL.

require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/store/pesapal/functions.php");
include_once($_SERVER['DOCUMENT_ROOT']."/store/pesapal/OAuth.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/image.class.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/page.class.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/site.class.php");

ini_set("display_errors", 1);


//pplog($_SERVER["SCRIPT_FILENAME"]);
$f = explode("/", $_SERVER["SCRIPT_FILENAME"]);
$fn = array_pop($f);
$testing = $fn=="pesapal-demo-ipn.php";
$override = false;
pplog("Process DF[".($testing?1:0)."]: $fn");

$test_key = 'wwdeds7jPSiFy+aFiMilsaWzDGlei6wV';
$test_secret = 'OVLCQOt9e3Y3StIA9jSOtrBDr7w=';
$test_link = 'http://demo.pesapal.com/api/querypaymentstatus';
//$test_link = 'http://demo.pesapal.com/api/querypaymentdetails';

$live_key = 'gJ3ugERFXuavRDBKgzdKbLkyb+8aeXU0';
$live_secret = 'KftEDX1yit9UBv7C8FethUTDu7k=';
$live_link = "https://www.pesapal.com/api/querypaymentstatus";

$consumer_key = $live_key;
if ($testing) $consumer_key = $test_key;

$consumer_secret = $live_secret; 
if ($testing) $consumer_secret = $test_secret; 

$statusrequestAPI = $live_link;
if ($testing) $statusrequestAPI = $test_link;

//if ($testing) print "Connect key($consumer_key) sec($consumer_secret) link($statusrequestAPI)<br>\n";

// Save get parameters
$tmp = '';
foreach ($_GET as $k=>$v) $tmp .= $k."=".$v.";";
//if ($tmp) pplog("GET: ".$tmp);

// Parameters sent to you by PesaPal IPN
$pesapalNotification=$_GET['pesapal_notification_type'];
$pesapalTrackingId=$_GET['pesapal_transaction_tracking_id'];
$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];

// Forced testing....
if ($testing && $_GET['force']==1) {
	$pesapalNotification="CHANGE";
	$pesapalTrackingId="manual override";
	$pesapal_merchant_reference="56718fd61b351M2";
	$pesapal_merchant_reference="588b8168aa87cM10";
	$override = true;
	pplog("Data override enabled");
}


$mp = strrpos($pesapal_merchant_reference, "M");

if ($mp>0) {
	$reference = substr($pesapal_merchant_reference, 0, $mp);
	$member_id = substr($pesapal_merchant_reference, $mp+1);
	
	if (!$pesapalNotification && $testing) $pesapalNotification="CHANGE";
	if (!$pesapalTrackingId && $testing) $pesapalTrackingId = uniqid();

	pplog("notify($pesapalNotification) TrackingID($pesapalTrackingId) ref($pesapal_merchant_reference)");
	
	if($pesapalNotification=="CHANGE" && $pesapalTrackingId!='')
	{
	   $token = $params = NULL;
	   $consumer = new OAuthConsumer($consumer_key, $consumer_secret);
	   $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
	
	   //get transaction status
	   $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
	   $request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
	   $request_status->set_parameter("pesapal_transaction_tracking_id",$pesapalTrackingId);
	   $request_status->sign_request($signature_method, $consumer, $token);
	
	   $ch = curl_init();
	   curl_setopt($ch, CURLOPT_URL, $request_status);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   curl_setopt($ch, CURLOPT_HEADER, 1);
	   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	   if(defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True')
	   {
		  $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
		  curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
		  curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		  curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
	   }
	
		$response = curl_exec($ch);
		if (!pplog("CR: ".$response)) {
			pplog($db->last_error);
		}
		//if ($testing) print "===============<br>".$response."<br>====================<br>\n";
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$raw_header  = substr($response, 0, $header_size - 4);
		$headerArray = explode("\r\n\r\n", $raw_header);
		//if ($testing) print "Header(".print_r($headerArray, 1).")<br>\n";
		$header = $headerArray[count($headerArray) - 1];
	
		//transaction status
		$elements = preg_split("/=/",substr($response, $header_size));
		$status = $elements[1];
		curl_close ($ch);
		
		//UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH 
		//pesapal_transaction_tracking_id $pesapalTrackingId
		$logID = pplog("Transaction status: $status");
		if ($logID>0) {

			$data=array("REFERENCE"=>$reference);
			
			// Find out what kind of payment this is
			$query = "SELECT * FROM pesapal_history WHERE reference='$reference' AND member_id=$member_id AND description like '%PPT:%' LIMIT 1";
			if ($row = $db->get_row($query)) {
				$tmp = explode(":", $row->description);
				foreach ($tmp as $i=>$testtype) {
					if (trim($testtype)=="PPT") {
						$pptype = trim($tmp[$i+1]);
						$ppamount = trim($tmp[$i+2])+0;
						$ppdesc = trim($tmp[$i+3]);
						pplog("Type($pptype) amount($ppamount) desc($ppdesc)");
						break;
					}
				}
			}
	
			//Assume all payments via main site
			$site = new Site(1);
			$cur = $site->getConfig("pesapal_currency");
			if ($cur=="$") $cur="USD";
			$data['AMOUNT'] = $cur.number_format($ppamount, 2);
			
			if ($status=="COMPLETED") {
				
				// Check if we have already processed this payment
				$query = "SELECT id FROM pesapal_history WHERE reference='$reference' AND id<>$logID AND description='".($testing?"TEST: ":"")."Transaction status: COMPLETED'";
				$procID = $db->get_var($query);
				//pplog("Check[$procID] if processed: $query");
				
				if (!$procID) {

					if ($pptype) {
						
						switch ($pptype) {
			
							// If this is a fellow membership payment then update their renewal date
							case "FELLOW":
								$fuemail = "FELLOW-PAYMENT";
								$query = "SELECT email, CONCAT(firstname, ' ', surname) AS fullname,
									DATE_FORMAT(paid_date, '%Y') AS year
									FROM members 
									WHERE member_id = ".$member_id;
								pplog($query);
								if ($row = $db->get_row($query)) {
									$payer_email = $row->email;
									$data['FULLNAME'] = $row->fullname;
									$year = $row->year;
									$thisyear = date("Y", time());
								}
								//pplog("Got amount($ppamount) cur(".$site->getConfig("pesapal_currency").") data(".$data['AMOUNT'].")");
								
								// Check this is a renewal or a join fee
								if ($ppamount == $site->getConfig("pesapal_fellow_join")) {
									// Date paid is either 1/1/Current year
									// or if their current paid date is already 1/1/current year(or above) then 1/1/next year
									if ($year>=$thisyear) $date = ($year+1).'-01-01';
									else $date = $thisyear.'-01-01';
								}
								else if ($ppamount == $site->getConfig("pesapal_fellow_renew")) {
									$years = $ppamount / $site->getConfig("pesapal_fellow_renew");
									pplog("Renewal for $years years based on $ppamount / ".$site->getConfig("pesapal_fellow_renew"));
									$date = "paid_date + INTERVAL $years YEAR";
								}
								
								/*
								// Check this is a renewal or a join fee
								if ($ppamount == $site->getConfig("pesapal_fellow_join")) $date = "NOW()";
								else if ($ppamount == $site->getConfig("pesapal_fellow_renew")) $date = "paid_date + INTERVAL 1 YEAR";
								
								// Date paid is either 1/1/Current year
								// or if their current paid date is already 1/1/current year(or above) then 1/1/next year
								if ($year>=$thisyear) $date = ($year+1).'-01-01';
								else $date = $thisyear.'-01-01';
								*/
								
								$query = "UPDATE members SET paid_date = '".$date."' WHERE member_id = ".$member_id;
								$db->query($query);
								pplog($query);
								break;
								
						}
						
					}
					else {
						pplog("Payment not processed, failed to locate payment type");
						pplog($query);
					}
				}
				else {
					pplog("Transaction already processed");
				}
			}
			
			// Transaction registered at pesapal, need to await another response
			else if ($status == "PENDING") {
			}
			
			// Transaction was declined. All my tests seem to be
			else if ($status =="FAILED") {
				//pplog("Payment failed, should we email notification - probably");
				$query = "SELECT id FROM pesapal_history WHERE reference='$reference' AND id<>$logID AND description='".($testing?"TEST: ":"")."Transaction status: FAILED'";
				//pplog("Check[$procID] if processed: $query");
				$procID = $db->get_var($query);
				if ($procID) pplog("Transaction already processed");
				else {
					if ($pptype=="FELLOW") {
						$fuemail = "FELLOW-DECLINED";
						$query = "SELECT email, CONCAT(firstname, ' ', surname) AS fullname,
							DATE_FORMAT(paid_date, '%Y') AS year
							FROM members 
							WHERE member_id = ".$member_id;
						pplog($query);
						if ($row = $db->get_row($query)) {
							$payer_email = $row->email;
							$data['FULLNAME'] = $row->fullname;
							pplog("Got email($payer_email) fullname(".$data['FULLNAME'].")");
						}
						else pplog("Failed to get member data");
					}
				}
			}
			else {
				pplog("Status[$status] not configured - No processing required");
			}
			
			include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
			include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/newsletter.class.php");
			include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/email/htmlMimeMail.php");
			$newsletter = new Newsletter();
			
			$admin_email = "info@aasciences.ac.ke";
			$tech_email = "phil@treelinesoftware.com";
			
			if ($fuemail) {
				pplog("Sending $fuemail to $payer_email and Admin:$admin_email Tech:$tech_email");		
				if ($payer_email) $newsletter->sendText($payer_email, $fuemail, $data, false);
				if ($admin_email) $newsletter->sendText($admin_email, $fuemail, $data, false);
				if ($tech_email) $newsletter->sendText($tech_email, $fuemail, $data, false);
				//pplog("Send data(".print_r($data, 1).")");
			}

		}
		else {
			pplog("Failed to log transaction($query)");
			pplog($db->last_error);
		}

		// Notify pesapal we have processed their transction
		$resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
		//if ($testing) print "response(".$resp.")<br>\n";
		ob_start();
		echo $resp;
		ob_flush();
		exit;

	}
	else {
		pplog("Notification type or tracking ID not valid");
	}
}
else {
	pplog("Failed to get reference number and member ID:$pesapal_merchant_reference");
}



?>