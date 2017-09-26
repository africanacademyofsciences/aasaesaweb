<?php

require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");

ini_set("display_errors", 1);

function pplog($s) {
	global $db, $testing, $pesapalTrackingId;
	$query = "INSERT INTO pesapal_history 
		(tracking_id, added, description) 
		VALUES
		('$pesapalTrackingId', NOW(), '".$db->escape($s)."')
		";
	$db->query($query);
	print "$query<br>\n";
}

$testing = false;
$testip = "80.0.182.170";

// Should we go into test mode
$ip = $_SERVER['REMOTE_ADDR'];
if ($ip == $testip) {
	$testing = true;
	pplog("Pesapal IPN: running in test mode");
}

$test_key = 'wwdeds7jPSiFy+aFiMilsaWzDGlei6wV';
$test_secret = 'OVLCQOt9e3Y3StIA9jSOtrBDr7w=';
$test_link = 'http://demo.pesapal.com/api/querypaymentstatus';

$live_key = 'gJ3ugERFXuavRDBKgzdKbLkyb+8aeXU0';
$live_secret = 'KftEDX1yit9UBv7C8FethUTDu7k=';
$live_link = "https://www.pesapal.com/api/querypaymentstatus";

$callback = "http://aasciences.ac.ke/thank-you/";
$ipn = "aasciences.ac.ke/store/pesapal/Pesapal-ipn-listener-php";

include_once($_SERVER['DOCUMENT_ROOT']."/store/pesapal/OAuth.php");


$consumer_key = $live_key;
if ($testing) $consumer_key = $test_key;

$consumer_secret = $live_secret; 
if ($testing) $consumer_secret = $test_secret; 

$statusrequestAPI = $live_link;
if ($testing) $statusrequestAPI = $test_link;

if ($testing) print "Connect key($consumer_key) sec($consumer_secret) link($statusrequestAPI)<br>\n";


// Parameters sent to you by PesaPal IPN
$pesapalNotification=$_GET['pesapal_notification_type'];
$pesapalTrackingId=$_GET['pesapal_transaction_tracking_id'];
$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];

if (!$pesapalNotification && $testing) $pesapalNotification="CHANGE";
if (!$pesapalTrackingId && $testing) $pesapalTrackingId = uniqid();

pplog("notify($pesapalNotification) Merchant reference($pesapal_merchant_reference)");

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
	if ($testing) print "===============<br>".$response."<br>====================<br>\n";
	
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$raw_header  = substr($response, 0, $header_size - 4);
	$headerArray = explode("\r\n\r\n", $raw_header);
	if ($testing) print "Header(".print_r($headerArray, 1).")<br>\n";
	$header = $headerArray[count($headerArray) - 1];

	//transaction status
	$elements = preg_split("/=/",substr($response, $header_size));
	$status = $elements[1];
	
	curl_close ($ch);
	
	//UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH pesapal_transaction_tracking_id $pesapalTrackingId
	if(1)
	{
		$resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
		if ($testing) print "response(".$resp.")<br>\n";

		ob_start();
		echo $resp;
		ob_flush();
		exit;
	}
}
else {
	pplog("Notification type or tracking ID not valid");
}
?>