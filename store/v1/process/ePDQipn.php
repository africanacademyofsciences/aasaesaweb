<?php
//ini_set("display_errors", 1);
//error_reporting(1);

report ("Started ePDQ processing");


$testing = false;

require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/functions.php");

$forceLIVEDB=true;
$summary = "<br>\n";
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/site.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/event.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/image.class.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/order.class.php");

include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/newsletter.class.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/email/htmlMimeMail.php");


require_once($_SERVER['DOCUMENT_ROOT']."/treeline/store/includes/basket.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/store/includes/store.class.php");


report("Script has been accessed by (".$_SERVER['REQUEST_METHOD'].") ");


if (!strcmp(getenv("REQUEST_METHOD"),"POST")) {

	report("POST ( \n ".print_r($_POST, true)." \n )");
	$cartID=$_POST['oid'];
	$total=$_POST['total'];
	$clientid = $_POST['clientid'];
	$status = $_POST['transactionstatus'];
	
	$msg.="OrderID - $cartID\n";
	$msg.="Transaction Status - $status\n";
	$msg.="Total - $total\n";
	$msg.="ClientID - $clientid\n";
	$msg.="Transaction Time Stamp - ".$_POST['datetime']."\n";
	$msg.="ECI Status - ".$_POST['ecistatus']."\n";
	$msg.="Card Prefix - ".$_POST['cardprefix']."\n";
	report($msg);
}

//report("SERVER : ".print_r($_SERVER, true));
//report("ENV : ".print_r($_ENV, true));
//report("POST : ".print_r($_POST, true));
//report("GET : ".print_r($_GET, true));

// Check the transaction is not a hack attempt
if (!$cartID && $testing) $cartID='4b20e297cbeaa';

if ($cartID) {


	$query="SELECT so.member_id, so.msv, sv.contact_email,
		concat(m.firstname, ' ', m.surname) as fullname, m.email
		FROM store_orders so
		LEFT JOIN sites_versions sv on so.msv = sv.msv
		LEFT JOIN members m ON m.member_id = so.member_id
		WHERE so.order_id='$cartID' AND so.status=0 LIMIT 1";
	report("collect admin data($query)<br>\n");
	if ($row = $db->get_row($query)) {

		$member_id = $row->member_id;
		$msv = $row->msv;
		$receiver_email = $row->contact_email;
		$payer_email = $row->email;
		$payer_name = $row->fullname;
		// Check if any events on this order and get admin email addresses?
		
		// Always log transactions so we can refer back to them
		$transaction_id=log_transaction($cartID);
		
		if (transaction_okay($transaction_id, $cartID)==1) {
		
			$paid = ($status == 'Success')?1:0;
			report("got status($paid)", false);
			
			if ($paid) {
			
				$site = new Site($msv);
				$store = new Store();
				$basket = new Basket($cartID);
				$basket->cartToOrder($basket->cartID);
				$store->updateStock($basket);
				
				report("Processed transaction");
				
				if( is_array($basket->basket) && count($basket->basket)>0 ) {
		
					if ($receiver_email) {
						// Admin inform about purchase
						$newsletter = new Newsletter();
						$data=array("ORDER-ID"=>$cartID);
						$newsletter->sendText($receiver_email, "STORE-PAYMENT", $data, false);
						report("Sent admin email to($receiver_email)");
						
						//$receiver_email = "phil.redclift@ichameleon.com";
						//$newsletter->sendText($receiver_email, "STORE-PAYMENT", $data, false);
						//report("Sent admin email to($receiver_email)");

						$newsletter = new Newsletter();
						$data=array("ORDER-TABLE"=>$basket->drawProductSummary());
						$newsletter->sendText($payer_email, "STORE-PURCHASE", $data, false);
						report("Sent purchase email to($payer_email)");
					}
						
				}
				
				if ($basket->donation['value']) {
					$query = "UPDATE store_donate_page SET total=total+".$basket->donation['value'];
					$db->query($query);
					report ($query);

					if ($payer_email) {
						$newsletter = new Newsletter();
						$data=array("FULLNAME"=>$payer_name);
						$newsletter->sendText($payer_email, "STORE-DONATION", $data, false);
						report("Sent donation email to($payer_email)");
					}
					else report ("No donator email to send thanks message to");
				}
		
				// Send events emails that relate to this order.
				if( is_array($basket->events) && count($basket->events) ){ 
					$entries_sent = array();
					foreach( $basket->events as $event ){ 
						//print "Sent event(".print_r($event, true).")<br>\n";
						//print "Check if id(".$event->entry_id.") is in the array(".print_r($entries_sent, true).") answer(".(in_array($entry->id, $entries_sent)?"yes":"NO").")<br>\n";
						if (!in_array($event->entry_id, $entries_sent)) {
							$entries_sent[]=$event->entry_id; 		// Only send one email per entry submission
							unset($ev_obj);
							$ev_obj = new Event($event->guid);
							report("Notify(".$event->entry_id.", ".($event->payment_type=="10% Online"?"paid10":"paid").")");
							$ev_obj->Notify($event->entry_id, ($event->payment_type=="10% Online"?"paid10":"paid"));
						}
					}
				}		
			}
			else if ($status!="DECLINED") report("Payment was status ($status) cannot be processed");
		}
		else report("Invalid transaction data");
	}
	else report("Failed to collect order data - $query");
}
else report ("No order ID returned from gateway");	

	

report ("Finished", true);


function transaction_info($id, $info) {
	global $db;
	if ($id>0) {
		$query="update store_payment set payment_info='$info' WHERE id=$id";
		report ("add info($query)");
		$db->query($query);
	}
}

function transaction_okay($id, $cartID) {
	global $db, $report_msg, $testing;
	report ("transaction_okay($id, $cartID)");
	$status = 0;
	$query="select `status`, member_id from store_orders where order_id='$cartID' limit 1";
	report("check okay($query)");
	if ($row=$db->get_row($query)) {
		$status = $row->status+0;
		$member_id=$row->member_id+0;
		if ($status>0) {
			transaction_info($id, "Invalid order status[".$status."]");
			return false; 	// always return false, even in test mode
		}
		else if ($member_id>0) return 1;
		else transaction_info($id, "Invalid member id[".$member_id."] on order record");
	}
	else transaction_info($id, "Could not find this transaction in the orders table");
	return $testing;	// 0 - transaction does not exist / 1 - valid / >1 - already paidup?
}

/*
ePDQ returns
    [transactionstatus] => DECLINED
    [total] => 0.01
    [clientid] => 45855
    [oid] => 4b20e297cbeaa
    [datetime] => Dec 10 2009 17:01:51
    [chargetype] => Auth
    [ecistatus] => 1
    [cardprefix] => 4
*/
function log_transaction($cartID)
{	
	global $db, $report_msg;
	// All transactions should be logged so we can refer back to the data and check the txn_id is valid etc... 
	$query="INSERT INTO store_payment 
		(added, order_id, payment_status, total, clientid, transtime, type, ecistat, cardprefix)
		VALUES 
		(NOW(), '".$cartID."', 
		'".$db->escape($_POST['transactionstatus'])."', 
		'".$db->escape($_POST['total'])."', 
		'".$db->escape($_POST['clientid'])."', 
		'".$db->escape($_POST['datetime'])."',
		'".$db->escape($_POST['chargetype'])."',
		".($_POST['ecistatus']+0).", ".($_POST['cardprefix']+0).")";
	report("update paypal($query)");
	if (!$db->query($query)) {
		report("Failed to add new record");
		return 0;
	}
	$transID = $db->insert_id;
	return $transID;
}


function report($s="", $send=false) {
	global $report_msg, $db, $testing;

	//$db->query("insert into xxx (tmp) values ('report-".$db->escape($s)."\n\n".$db->escape($report_msg)."')");
	$report_msg.=$s."\n";
	if ($testing && $send) print nl2br($report_msg);
		
	if ($send) {
		$report_email="phil.redclift@ichameleon.com"; 	// Add valid email_report address to get a copy of whats going on.
		//$report_email="";		// Uncomment to disable email reporting.
		$subject = "ePDQ transaction report";
		if ($report_email && $report_msg) {
			mail($report_email, $subject, getcwd()."\n".$report_msg);
		}
	}
}

?>