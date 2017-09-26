<?php

// Destination of money
$receiver_email = 'finance@dep.org.uk';
//define('RECEIVER_EMAIL', 'finance@dep.org.uk');

$report_msg='';
$testing=false;
$testing=true;

if ($testing) {
	$receiver_email = 'phil.redclift@ichameleon.com';
}

report("Paypal Transaction report - ".date("Y-m-d H:i", time())."\n");

// Read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

report("Post data....");

foreach ($_POST as $key => $value)
{
	$value = urlencode(stripslashes($value));
	$req  .= "&$key=$value";
	report("Received $key = $value");
}
report(" ");

$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

//$fp = fsockopen('www.sandbox.paypal.com', 80, $errno, $errstr, 30);	// (sandbox)
$fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);		// (live!)

if (!$fp)
{
	die('Error connecting to paypal.com');
}
else
{
	// Post back to PayPal system to validate
	report("Post back to paypal \n");
	report("req($req)"."\n");
	
	fputs($fp, $header . $req);
	
	// Read paypal's response
	while (!feof($fp))
	{
		$res = fgets($fp, 1024);
	}
	
	report("got result($res)"."\n");
	if (strcmp($res, "VERIFIED")==0 || $testing)
	{
		require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/page.class.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/site.class.php");
		//require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/order.class.php");
		
		include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
		include($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/newsletter.class.php");
		include($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/email/htmlMimeMail.php");


		require_once($_SERVER['DOCUMENT_ROOT']."/treeline/store/includes/basket.class.php");
		require_once($_SERVER['DOCUMENT_ROOT']."/treeline/store/includes/store.class.php");
		
		
		// Check the transaction is not a hack attempt
		$cartID=$_POST['custom'];
		$query="SELECT member_id, msv FROM store_orders 
			WHERE order_id='$cartID' AND status=0 LIMIT 1";
		if ($row = $db->get_row($query)) {
			$member_id = $row->member_id;
			$msv = $row->msv;
		}
		if (!$member_id) report("Failed to collect member id - $query");
		if (!$msv) {
			report("Failed to collect site version - $query");
			$msv=1;
		}

		// Always log transactions so we can refer back to them
		$transaction_id=log_transaction($cartID);

		if (transaction_okay($transaction_id, $cartID)==1 || $testing) {
		
			
			$paid = ($_POST['payment_status'] == 'Completed');
			report("got status($paid)", false);
			
			if ($paid) {
				$site = new Site($msv);
				$store = new Store();
				$basket = new Basket($cartID);
				$basket->cartToOrder($basket->cartID);
				$store->updateStock($basket);
				
				report("Processed transaction");
				
				if( is_array($basket->basket) && count($basket->basket)>0 ) {

					// Admin inform about purchase
					$newsletter = new Newsletter();
					$data=array("order_id"=>$cartID);
					$newsletter->sendText($receiver_email, "STORE_PAYMENT", $data, false);

					/*
					// We only need to add the delivery address if there are real items on the order
					$addr_id = $store->setDeliveryAddress($member_id, $db->escape($_POST['address_street']),
						$db->escape($_POST['address_city']), $db->escape($_POST['address_state']),
						$db->escape($_POST['address_zip']), $db->escape($_POST['address_country']),
						$db->escape($_POST['address_country_code']));
						
					report ("Set delivery address to ($addr_id)", true);
					if (!$addr_id) {
						report("Failed to set delivery address", true);
						//report("Failed to set delivery address ".$db->escape($_POST['address_street']).",".$db->escape($_POST['address_city']).",".$db->escape($_POST['address_state']).",".$db->escape($_POST['address_zip']).",".$db->escape($_POST['address_country']).",".$db->escape($_POST['address_country_code']));
					}
					else {
						$query="update store_orders set shipping_addr_id=$addr_id where order_id='$cartID'";
						report("add shipping addy($addr_id) q($query)");
						$db->query($query);
					}
					*/
				}
			}
			
		}
		else {
			report("Invalid transaction data");
		}
	}
	elseif (strcmp($res, "INVALID") == 0)
	{
		//die('The response from IPN was: '.$res);
		report("The response from IPN was: ".$res);
	}
	else
	{
		report("PayPal IPN is down!");
	}
}
fclose ($fp);


report("", true);


// --------------------------- USEFUL FUNCTIONS ---------------------------
function transaction_info($id, $info) {
	global $db;
	if ($id>0) {
		$query="update paypal set payment_info='$info' WHERE id=$id";
		report ("add info($query)");
		$db->query($query);
	}
}

function transaction_okay($id, $cartID) {
	global $db, $report_msg;
	$status = 0;
	$query="select status, member_id from store_orders where order_id='$cartID' limit 1";
	report("check okay($query)");
	if ($row=$db->get_row($query)) {
		if ($row->status>0) transaction_info($id, "Invalid order status[".$row->status."]");
		else if ($row->member_id>0) return 1;
		else transaction_info($id, "Invalid member id[".$row->member_id."] on order record");
	}
	else transaction_info($id, "Could not find this transaction in the orders table");
	return 0;	// 0 - transaction does not exist / 1 - valid / >1 - already paidup?
}

function log_transaction($cartID)
{	
	global $db, $report_msg;
	// All transactions should be logged so we can refer back to the data and check the txn_id is valid etc... 
	$query="INSERT INTO paypal 
		(txn_id, txn_date, order_id, payer_email, payment_status, 
		first_name, last_name, payer_id, mc_gross, mc_fee)
		VALUES 
		('".$db->escape($_POST['txn_id'])."', NOW(), '".$cartID."', 
		'".$db->escape($_POST['payer_email'])."', '".$db->escape($_POST['payment_status'])."', 
		'".$db->escape($_POST['first_name'])."', '".$db->escape($_POST['last_name'])."',
		'".$db->escape($_POST['payer_id'])."',
		".($_POST['mc_gross']+0).", ".($_POST['mc_fee']+0).")";
	report("update paypal($query)");
	if (!$db->query($query)) {
		report("Failed to add new record");
		return 0;
	}
	return $db->insert_id;
}

function report($s="", $send=false) {
	global $report_msg, $db, $testing;

	//$db->query("insert into xxx (tmp) values ('report-".$db->escape($s)."\n\n".$db->escape($report_msg)."')");
	$report_msg.=$s."\n";
	if ($testing && $send) print $report_msg;
		
	if ($send) {
		$report_email="phil.redclift@ichameleon.com"; 	// Add valid email_report address to get a copy of whats going on.
		//$report_email="";		// Uncomment to disable email reporting.
		$subject = "PayPal transaction report";
		if ($report_email && $report_msg) {
			mail($report_email, $subject, $report_msg);
		}
	}
}





?>