<?php

//ini_set("display_errors", 1);
//error_reporting(E_ALL);

	include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
	$emailOut = new Newsletter;

	// If we try to get in the store outside a secure connection 
	// Redirect to the secure version
	if ($store->config['use_https']) {
		if( $_SERVER['SERVER_PORT']!=443 && 
			!($_SERVER['HTTP_HOST']=="dep" || $_SERVER['HTTP_HOST']=='dep.ichameleon.com') 
		  ){ // replace with a regex
			$link = substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'],':') );
			//echo 'https://'. $link . $_SERVER['REQUEST_URI'] .'<br />';
			header('Location: https://'. $link . $_SERVER['REQUEST_URI']);
		}
	}
	
	
	//print "subscribe(".$_REQUEST['subscribe'].") member_id(".$_COOKIE['member_id'].")<br>\n";
	if ($_REQUEST['subscribe']==1 && $_COOKIE['memberID']>1) {
		$query = "SELECT preference_id FROM newsletter_preferences WHERE site_id=".$site->id." AND deleted=0 ORDER by preference_id";
		//print "$query <br>\n";
		$main_preference_id = $db->get_var($query);
		if ($main_preference_id>0) {
			// Not sure how efficient this is but the indexes will ensure it fails if they are already subscribed.
			$query="insert into newsletter_user_preferences 
				(member_id, preference_id) 
				values 
				(".$_COOKIE['memberID'].", $main_preference_id)";		
			//print "$query <br>\n";
			@$db->query($query);
		}
	}
	
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');
	$stage = read($_REQUEST,'stage',false);

	// Paypal specific fudge
	if ($_GET['payment_status']=="Completed") $stage = "complete";
	

	$field_error = array();
	
	$tags = new Tags();

	if( $basket->getGrandTotal()==0 ){
		$_POST = false;
		$mode = 'emptyBasket';
		$message[] = 'You have nothing in your shopping basket';
	}	
	//print "got total (".$basket->getGrandTotal().") stage($stage) mode($mode)<br>\n";
	
	if( isset($_POST) && $_POST ) {

		extract($_POST);
		//print "Stage($stage)<br>\n";
		if ($stage) {
			
			switch( $stage ){
			
				// *********************************************************
				// CONFIRM
				case 'confirm':
					
					if( isset($addressBookUsed) && $addressBookUsed>'' && $addressBook>0 ){
						// looks like we're using an existing address - add to the order
						if( $basket->addOrderAddress( 'delivery', $_COOKIE['cartID'], $addressBook, $_COOKIE['memberID'] ) ){
							$stage = 'payment'; // we no longer need the second half of the 'confirm' stage
						}
						else{
							$message[] = 'There was a problem with attaching this address to your order';
						}	
					}
					else {
						// this tells us that we're really using a new delivery address
						// we need to verify then add to their address book and link to this order
						$address = array();
						foreach($_POST as $key => $value){
							if( preg_match('/del_[_a-z]/',$key) ){
								$key = substr($key,4);
								$address[$key] = $value;
							}
						}
						
						if( $addressBook = $account->createAddress($_COOKIE['memberID'], $address) ){
							if( $basket->addOrderAddress( 'delivery', $_COOKIE['cartID'], $addressBook, $_COOKIE['memberID'] ) ){
								$stage = 'payment'; // we no longer need the second half of the 'confirm' stage
							}
							else $message[] = 'There was a problem with attaching this address to your order';							
						}
						else $message[] = 'There was a problem with adding your address';
					}
					break;


				// *********************************************************
				// MAKE PAYMENT	
				// Only used if we collect CC data and process 
				// credit cards on this site.
				case 'payment':
				
					if( isset($makePayment) && $makePayment>'' && $store->confg['collect-cc-data'] ){
						//echo '<pre style="color:#fff">'. print_r($_POST,true) .'</pre>';
	
						$pmsg[]="Processing transaction ".$basket->cartID;
						// Validation		
														
						// CARD
						if( (!$ccName[0] || $ccName[0]<='') || (!$ccName[1] || $ccName[1]<='') ){
							// no cardholder name
							$message[] = 'You need to specify the <strong>cardholder\'s name</strong>';
							$field_error[] = 'ccName';
						}
						if( !$ccType || $ccType<='' ){
							// card type not specified
							$message[] = 'You need to select a <strong>card type</strong>';
							$field_error[] = 'ccType';			
						}
						if( !$ccNumber || $ccNumber<='' ){
							// card type not specified
							$message[] = 'You need to select a <strong>card number</strong>';
							$field_error[] = 'ccNumber';			
						}
						if( strlen($ccNumber)<16 && strlen($ccNumber)>19 ){
							// card number isn't long enough...
							$message[] = 'Your <strong>card number</strong> doesn\'t appear to be long enough';
							$field_error[] = 'ccNumber';
						}
						//print_r($ccExDate);
						if( $ccExDate[1]==date('Y') && $ccExDate[0]<date('m') ){
							// end date is before this month
							$message[] = 'It appears that your card has already <strong>expired</strong>';
							$field_error[] = 'ccExDate';
							$field_error[] = 'ccExDate';
						}else{
							$ccExpiry = ($ccExDate[0]<10 ? '0'.$ccExDate[0] : $ccExDate[0]) .'/'. substr($ccExDate[1],2);
						}
						if( strlen($ccCVV)<3 || $ccCVV<=0 ){
							// valid CVV?
							$message[] = 'Your <strong>security code</strong> seems to be invalid';
							$field_error[] = 'ccCVV';
						}
						//Issue Number validation
						
						if ($ccType != "" && $ccType == "vd"){
							if (!is_numeric($card_switch_issue)){
								$message[] = 'Your <strong>issue number</strong> seems to be invalid';
								$field_error[] = 'ccIssue';
							}
						}
						
						if( ($ccType != "" && $ccType == "vd") && ($ccStDate[1]==date('Y') && $ccStDate[0]>date('m')) ){
							// start date is after the current date - therefore not valid yet
							$messages[] = 'The <strong>start date</strong> you\'ve specified is after this month and is not yet valid';
							$field_error[] = 'ccStDate';
						}else{
							$ccStart = ($ccStDate[0]<10 ? '0'.$ccStDate[0] : $ccStDate[0]) .'/'. substr($ccStDate[1],2);
						}
											
						
						// Address
						if( !isset($addressBook) || $addressBook<0 || $addressBook<='' ){
							if( !$bill_house || $bill_house<='' ){
								// house & street
								$message[] = 'Your need to enter your <strong>house/flat name or number</strong>';
								$field_error[] = 'bill_house';			
							}
							if( !$bill_street || $bill_street<='' ){
								// house & street
								$message[] = 'Your need to enter your <strong>street</strong>';
								$field_error[] = 'bill_street';			
							}
							if( !$bill_town_city || $bill_town_city<='' ){
								// town/city
								$message[] = 'Your need to enter your <strong>town or city</strong>';
								$field_error[] = 'bill_town_city';			
							}
							if( !$bill_country_id || $bill_country_id<='' ){
								$message[] = 'Your need to enter the <strong>country</strong> field in your address';
								$field_error[] = 'bill_country_id';			
							}
							if( !$bill_post_code || $bill_post_code<='' ){
								// postcode
								$message[] = 'Your need to enter your <strong>post code</strong>';
								$field_error[] = 'bill_post_code';
							}
						}
		
		
						if( !$message || count($message)==0 ){
						// do actual payment processing here...
						
							// how with the IMA handle it?
							
								// what do we need to store?
								//- address (from address book or new one)
								//- date order completed
								//- status set to 1 - pending order
							if( $addressBook ){
								// add the ID of this address to the order...
								if( !$basket->addOrderAddress( 'billing', $_COOKIE['cartID'], $addressBook, $_COOKIE['memberID'] ) ){
									$feedback = 'error';
									$message = 'There was a problem with attaching this address to your order';							
								}							
							}else{
								$address = array('title'=>'Billing address');
								foreach($_POST as $key => $value){
									if( preg_match('/bill_[_a-z]/',$key) ){
										$key = substr($key,5);
										$address[$key] = $value;
									}
								}
								if( $addressBook = $account->createAddress($_COOKIE['memberID'],$address) ){
									if( !$basket->addOrderAddress( 'billing', $_COOKIE['cartID'], $addressBook, $_COOKIE['memberID'] ) ){
										$feedback = 'error';
										$message = 'There was a problem with attaching this address to your order';							
									}				
								}else{
									$feedback = 'error';
									$message = 'There was a problem with adding your address';					
								}	
							}					
							
						// process payment...
						if( !$message ){
						
							// Do gift aid stuff, here first for testing
							//print "<!-- add gift aid (".$_REQUEST['giftaid'].") don(".$basket->totals['donation'].") spons(".$basket->totals['sponsorships'].") ) -->\n";
							$gift_table='';
							if ($basket->totals['donation']>0) $gift_table="donations";
							else if ($basket->totals['sponsorships']>0) $gift_table="sponsorships";
							if ($gift_table && $_REQUEST['giftaid']>'') {
								//print "<!-- add gift aid to($gift_table) don(".$basket->totals['donation'].") spons(".$basket->totals['sponsorships'].") ) -->\n";
								$query="update store_orders_".$gift_table." set use_gift_aid=1 where order_id='".$basket->cartID."'";
								//print "<!-- $query --> \n";
								$db->query($query);
							}
	
							$grandTotal = ($basket->total>0) ? $basket->getGrandTotal()+$basket->getPostageAndPacking() : $basket->getGrandTotal();
							include($_SERVER['DOCUMENT_ROOT'] .'/treeline/includes/ePDQ.class.php');
							include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/net.class.php");
							$epdq = new EPDQc();
							$epdq->setCardNumber($ccNumber);
							$epdq->setEndDate($ccExpiry);
							$epdq->setCvv2($ccCVV);
							$epdq->setTransactionType("Auth");
							$epdq->setAmount($grandTotal*100);
							$epdq->setOrderId($basket->cartID);		
							$epdq->setEmailAddress($account->properties->email);
							// name
							$epdq->setFirstName($ccName[0]);
							$epdq->setLastName($ccName[1]);
							//if( $addressBook ){
							
							
							if( $addr = $basket->getAddress('billing',$_COOKIE['cartID'])  ){
								//echo 'Got address!<br />';
								$epdq->setStreet1($addr->house .' '. $addr->street);
								$epdq->setStreet2($addr->locality);
								$epdq->setCity($addr->town_city);
								$epdq->setStateProv($addr->county);
								$epdq->setCountry($addr->country_iso);
								$epdq->setPostalCode($addr->post_code);
							}
							//}else{
							//	$epdq->setStreet1($bill_street);
							//	$epdq->setPostalCode($bill_post_code);						
							//}
							
							if( $ccIssue != ""){
								$epdq->IssueNumber = $ccIssue;
							}
							if( $ccStDate[0] != "" && $ccStDate[1] != ""){
								$epdq->StartDate = $ccStart;
							}
							
							//echo $epdq->BuildOrderDoc();
							//exit();
							
							if($epdq->getErrormessage != '') {
								// display your error
								// What error should we show here??
								$feedback = 'error';
								$pmsg[]="PDQ ERROR = ".$epdq->getErrormessage;
								$message = 'There was a problem processing you payment.  Please try again or consult you bank.';
							} else {
							
								$pmsg[]="About to process transaction(".$basket->cartID.") in mode(".$epdq->getMode().")\n";
								$epdq->ProcessTransaction();
								$pmsg[]="ProcessTransaction() returned ".$epdq->getCcErrorCode()." \n";
								
								unset($SUCCESS);
								unset($_REQUEST['SUCCESS']);
								$SUCCESS = false;
								$errorcode = (int)$epdq->getCcErrorCode();
								if ( $errorcode<>1) {
									//echo "ePDQ:ProcessTransaction():Payment Authorisation Failed - Please check you card details.";
									$feedback = 'error';
									$message[] = 'Sorry, you payment was <strong>not authorised</strong>. Please check you card details and try again.';
									$field_error[] = 'ccNumber';
									// handle failed txn here
								} else {
									$SUCCESS = true;
								// if successful...
									$basket->cartToOrder($basket->cartID);
									$store->updateStock($basket);
	
								
									// send out receipt
									$memberName = $account->properties->firstname.' '.$account->properties->surname;
									$to = $memberName.'<'. $account->properties->email .'>';
									$storeEmail = $site->config['contact_recipient_email'];
									$from = 'From: '.$site->name.' Shop <'. $storeEmail .'>';
									$subject = 'Receipt for purchases from the '.$site->name.' store';
									$headers = $from."\r\n";
									$headers .= "Return-Path: $storeEmail\r\n";
									$headers .= "Reply-To: $storeEmail\r\n";
									$headers .= "X-Mailer: Treeline v3"; // Treeline branding everywhere!;
									
									$writeBasket = "";
									for($i=1;$i<60;$i++){
										$writeBasket .= '=';
									}
									$writeBasket .= "\n";
									foreach( $basket->basket as $item ){
										//$writeBasket .= $item->title ."\t". $item->quantity ."\t�". $item->price ."\n";
										$writeBasket .= $item->quantity .' x '. $item->title .' (�'. $item->price .' each)'."\n";
									}
									if( $basket->total>0 ){
										$writeBasket .= "Total: �". number_format($basket->total,2) ."\n";
										$writeBasket .= "Postage and Packaging: �". number_format($basket->pandp,2) ."\n";
									}
									if( $basket->totals['donation']>0 ){
										$writeBasket .= "\nDonation of �". number_format($basket->totals['donation'],2) ."\n";
									}
									if( is_array($basket->events) && count($basket->events)>0 ){
										$eventCount = count($basket->events);
										$writeBasket .= "\nEvent place". ($eventCount<>1 ? 's' : '') .": �".  $basket->totals['events']."\n";
									}
									if( is_array($basket->sponsorships) && count($basket->sponsorships)>0 ){
										$sponsorshipCount = count($basket->sponsorships);
										$writeBasket .= "\nSponsorship". ($sponsorshipCount<>1 ? 's' : '') .": �".  $basket->totals['sponsorships']."\n";
									}								
									for($i=1;$i<60;$i++){
										$writeBasket .= '=';
									}					
									
									//$msg = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/store/emails/receipt.txt');
									//$msg = str_replace('[[name]]',$memberName,$msg);
									//$msg = str_replace('[[orderID]]',$basket->cartID,$msg);
									//$msg = str_replace('[[total]]',$grandTotal,$msg);
									//$msg = str_replace('[[basket]]',$writeBasket,$msg);
									
									//echo "mail($to,$subject,$msg,$headers)<br />";
									
									//if( mail($to,$subject,$msg,$headers) ){
									if( $emailOut->sendText($account->properties->email, "STORE_RECEIPT", array("NAME"=>$memberName, "ORDERID"=>$basket->cartID, "TOTAL"=>number_format($grandTotal,2), "BASKET"=>nl2br($writeBasket))) ){
										$feedback = 'success';
										$message = 'Your receipt has been sent to your registered email address';
										
										// if donation
										if( $basket->totals['donation']>0 ){
											$emailOut->sendText($account->properties->email, "STORE_DONATION", array("NAME"=>$memberName, "AMOUNT"=>'�'.$basket->totals['donation'], "DATE"=>date('jS F Y')));
										}
										
										// if events
										if( count($basket->events)>0 ){
											include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/event.class.php');
											$user = new User();
											$user->loadByID(1);
											foreach( $basket->events as $e ){ 
												//echo '<pre style="color:#fff">'. print_r($event,true) .'</pre>';
												$event = new Event($e->guid);
												$event->addMember($_COOKIE['memberID']);
												$pp_guid = $db->get_var("SELECT pp_guid FROM event_entry where event_guid='". $e->guid ."' AND member_id=". $_COOKIE['memberID']);
												//echo "SELECT pp_guid FROM event_entry where event_guid='". $e->guid ."' AND member_id=". $_COOKIE['memberID'].'<br />';
												$memberName = $db->get_var("SELECT firstname FROM members WHERE member_id='". $_COOKIE['memberID'] ."'");
												//$ppLink = 'http://maginternational.org/'.$page->drawLinkByGUID($pp_guid);
												$ppLink = $page->drawLinkByGUID($pp_guid);
												$emailOut->sendText($account->properties->email, "PARTICIPATE", array("FIRST"=>$memberName, "TITLE"=>$e->title, "REGISTRATIONFEE"=>$e->price,"PERSONALPAGELINK"=>$ppLink));
											}
										}
										
										
										// if sponsorships
										if( count($basket->sponsorships)>0 ){
											include_once($_SERVER['DOCUMENT_ROOT'].'treeline/includes/event.class.php');
											foreach( $basket->sponsorships as $s ){ 
												$pp_guid = $db->get_var("SELECT pp_guid FROM event_entry where event_guid='". $s->event_id ."' AND member_id=". $s->member_id);
												$ppLink = 'http://maginternational.org/'.$page->drawLinkByGUID($pp_guid);
												$emailOut->sendText($account->properties->email, "STORE_SPONSOR", array("PARTICIPANT"=>$s->member_name, "EVENT"=>$s->event_title, "AMOUNT"=>'�'.$s->amount,"PERSONAL_PAGE"=>$ppLink));
											}
										}									
										
										
										// then go to the completion stage...
										$stage='complete';
										
									}else{
										$feedback = 'error';
										$message = 'The system experienced a problem when sending your receipt by email.';
									}
	
									// this is where we should kill the cookie!
									setcookie('cartID',FALSE,time()-42000,'/',$_SERVER['HTTP_HOST']);
									unset($_COOKIE['cartID']);
									
								}
							} // end if no message
	
							}
						}else{
							$feedback = 'error';
						}
						
						if ($pmsg || $message) {
							foreach ($pmsg as $tmp) $sendtmp.=$tmp."\n";
							foreach ($message as $tmp) $sendtmp.=$tmp."\n";
							mail("phil.redclift@ichameleon.com", $site->name." pdq trans", $sendtmp);
						}
							
					
					}
					
				
					//echo '<pre>'. print_r($_POST,true) .'</pre>';
					//exit();
					break;
				
				case 'complete':
					if( $memberTel || $orderNote ){
						$basket->addOrderNote($cartID,$orderNote,$_COOKIE['memberID'],$memberTel);
						$feedback = 'success';
						$message = 'Your note and/or phone number has been added to your order';
						$noteSubmitted = true;
					}
					break;
					
			}// end switch $stage

		} // end of if ($stage)

		// ****************************************************************
		// Any other actions we might need to process?
		// existing member
		if($cust_email && $cust_pass && $login) {

			$_COOKIE['memberID']='';	// If someone is logged in get rid of them
			
			if( $account->validate($cust_email,$cust_pass) ) {
				//echo '<pre>'. print_r($account->properties,true) .'</pre>';
				setcookie('memberID',$account->properties->member_id,$expires,'/');
				
				// Add this member to the shopping cart
				$query = "UPDATE store_orders SET member_id = ".$account->properties->member_id." WHERE order_id='".$basket->cartID."'";
				$db->query($query);
				//print "$query<br>\n";
				
				$mode = "elog";
				$feedback = 'success';
				$message = 'Welcome back!<br />You\'ve successfully logged in.';
				$stage = "confirm"; 	// Confirm delivery address
				
				if (!is_array($basket->basket)) $stage = 'payment';					// No physical items nothing to deliver
				if (!$store->config['collect-delivery-addr']) $stage = "payment"; 	// We dont collect this data here.
				//print_r($basket->basket);
				//print "logged in, deliver(".$store->config['collect-delivery-addr'].") got to stage $stage<br>\n";
			}
			else $message[] = 'Your email and/or password were not found in our system';
		}
		
		// ****************************************************************
		// register a new member
		else if( $mode=='register' && $register>'' ){

			$mode = '';
			$_COOKIE['memberID']='';	// If someone is logged in get rid of them
			
			// Check if the email address already exists.
			if ($account->memberExists($cust_email)) {
				$message[] = 'An account already exists with that email address';
				$message[] = 'Would you like a <a href="'.$storeURL.'/checkout/?mode=reminder&email='.$cust_email.'">password reminder</a> sent by email';
				$accountExists=true;
			}
			// Check all entered data is valid
			else {

				$fields_required = array('cust_title'=>"Title", 
					'cust_fname'=>"Firstname",'cust_lname'=>"Surname",
					'cust_email'=>"Email address",
					'cust_pass'=>"Password",'cust_cpass'=>"Confirm password");
				// first, check we have what we need...
				foreach( $fields_required as $field=>$fieldname ){
					if( $$field<='' ){
						$field_error[] = $field;
						$message[] = 'Please check the '.$fieldname.' field has been completed';
					} 
				}
				if ($cust_pass!= $cust_cpass) {
					$field_error[]="cust_pass";
					$field_error[]="cust_cpass";
					$message[] = 'Your passwords do not match.  Please make sure your password and the confirmation are the same';
				}
			}
			
			
			if( !count($message) ){
				//echo 'processing registration...<br />';
				$properties->email =  $cust_email;
				$properties->firstname = $cust_fname;
				$properties->surname = $cust_lname;
				$properties->password = $cust_pass;
				$properties->cust_title = $cust_title;
				if($account->create($properties)){

					setcookie('memberID',$account->properties->member_id,$expires,'/');
					// Add this member to the shopping cart
					$query = "UPDATE store_orders SET member_id = ".$account->properties->member_id." WHERE order_id='".$basket->cartID."'";
					$db->query($query);
					
					/*
					DON'T SEND STORE SIGNUP EMAILS ANYMORE
					include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
					include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
					include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
					$emailOut = new Newsletter;
					$emailOut->sendText($account->properties->email, "STORE_REGISTER", array("NAME"=>$cust_fname));
					*/
					
					//redirect('/shop/checkout/?mode=nlog'.($_REQUEST['subscribe']==1?"&subscribe=1":""));					
					$feedback = 'success';
					$message[] = 'Your account has been created.<br />Thank you for registering.';
					$stage = "confirm";
					if (!is_array($basket->basket)) $stage = 'payment';					// No products to deliver
					if (!$store->config['collect-delivery-addr']) $stage = "payment"; 	// We dont collect this data here.
				}
				else $message[] = 'Your account details could not be saved';
			}
		}

		// ****************************************************************
		// Get a password reminder
		else if( $mode=='reminder' ){
			if( $account->memberExists($reminder_email,true) ){
				$to = $account->properties->email;
				$data = array("PASSWORD"=>$account->properties->password);
				if ($emailOut->sendText($to,"STORE-REMINDER",$data)) {
					$feedback = 'success';
					$message[] = 'Your reminder has been sent';
					$mode=false;
				}
				else $message[]="Failed to send reminder email";
			}
			else $message = 'Your email address could not be found in our database';
		}

		
	} //end IF POST...


	// Page specific options
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('2colR','../store/style/store', '../store/style/checkout','../store/style/store_panels'); // all attached stylesheets
	
	$js = array('jquery','../store/behaviour/store','page_functions'); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	

	
if( $stage=='complete' ){ 

	$addr = $basket->getAddress('billing',$basket->cartID);
	$google_analytics_extra = '
		<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
		</script>
		
		<script type="text/javascript">
		  var pageTracker = _gat._getTracker("");
		  pageTracker._initData();
		  pageTracker._trackPageview();
		
		  pageTracker._addTrans(
			"'. $basket->cartID .'",                    // Order ID
			"",                  			         	// Affiliation
			"'. number_format($basket->getGrandTotal(),2) .'",           // Total
			"",                                         // Tax
			"'. number_format($basket->pandp,2) .'",    // Shipping
			"'. $addr->town_city .'",                   // City
			"'. $addr->county .'",                      // State
			"'. $addr->country .'"                      // Country
		  );
		';
	if(is_array($basket->basket) && count($basket->basket)>0 ){
		foreach( $basket->basket as $item ){
			$google_analytics_extra .= '
				  pageTracker._addItem(
					"'. $basket->cartID .'",                    // Order ID
					"'. $item->itemID .'",                      // SKU
					"'. $item->title .'",                       // Product Name 
					"",                                         // Category
					"'. number_format($item->price,2) .'",      // Price
					"'. $item->quantity .'"                     // Quantity
				  );
				 ';
		}
	}

	$google_analytics_extra .= '
			pageTracker._trackTrans();
		</script>
		';
}
	
	
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');

$itemcount = $store->total;
?>	

<div id="midholder">
    
    <div id="contentholder">

    <h1 class="pagetitle">Checkout</h1>
    
    <div id="primarycontent">
    <?= drawFeedback($feedback,$message);?>	

    <!--//<pre><?//= print_r($_POST,true) ?></pre>
    <pre><?//= print_r($_COOKIE,true) ?></pre>//-->
    <div id="basket">
		
        <? 
		// ****************************************************
		// Sign-in or register
		if( !$stage && !$mode  ) {	 
			?>
		
			<form action="<?=$storeURL?>/checkout/" method="post" id="existing" class="checkout">
				<input type="hidden" name="cartID" value="<?= $cartID ?>" />
				<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
				<fieldset>
					<legend>Existing customers</legend>
					<p class="instructions">Please enter your registered email address and password if you've used any of our online services before</p>
                    <fielset class="field">
						<label for="cust_email">Email address</label>
						<input type="text" maxlength="150" name="cust_email" id="cust_email" value="<?=$_POST['cust_email']?>" />
                    </fieldset>
                    <fieldset class="field">
						<label for="cust_pass">Password</label>
						<input type="password" maxlength="20" name="cust_pass" id="cust_pass" value="<?=$_POST['cust_pass']?>" />
                    </fieldset>
                    <fieldset class="field">
                    	<label for="f_l_submit" style="visibility:hidden;">submit</label>
						<input id="f_l_submit" type="submit" class="submit" name="login" value="Log-in" />				
                    </fieldset>
					<a href="<?=$storeURL?>/checkout/?mode=reminder" id="forgottenPass">Forgotten your password?</a>
				</fieldset>
			</form>
			
			<form action="<?=$storeURL?>/checkout/" method="post" id="register" class="checkout">
				<input type="hidden" name="mode" value="register"/>
				<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
				<input type="hidden" name="cartID" value="<?= $cartID ?>" />
				<fieldset>
					<legend>Create a new account</legend>
					<p class="instructions">Please fill in all of these fields.</p>
                    <fieldset class="field">
                        <label for="cust_title">Title</label>
                        <select name="cust_title" id="cust_title">
                            <option>Mr</option>
                            <option>Mrs</option>
                            <option>Ms</option>
                            <option>Miss</option>
                            <option>Dr</option>
                            <option>Rev</option>
                            <option>Prof</option>
                        </select>
                    </fieldset>
                    <fieldset class="field">
                        <label for="cust_fname">First name</label>
                        <input type="text" name="cust_fname" id="cust_fname" maxlength="50" value="<?= $cust_fname ?>" class="<?= (in_array('cust_fname',$field_error) ? 'error' : '') ?>" />
                    </fieldset>
                    <fieldset class="field">
                        <label for="cust_lname">Surname</label>
                        <input type="text" name="cust_lname" id="cust_lname" maxlength="50" value="<?= $cust_lname ?>" class="<?= (in_array('cust_lname',$field_error) ? 'error' : '') ?>" />
                    </fieldset>
                    <fieldset class="field">
                        <label for="cust_email">Email address</label>
                        <input type="text" maxlength="150" name="cust_email" id="cust_email" value="<?= $cust_email ?>" class="<?= (in_array('cust_email',$field_error) ? 'error' : '') ?>" />
                    </fieldset>
                    <fieldset class="field">
                        <label for="cust_pass">Password</label>
                        <input type="password" maxlength="20" name="cust_pass" id="cust_pass" class="<?= (in_array('cust_pass',$field_error) ? 'error' : '') ?>" />
                    </fieldset>
                    <fieldset class="field">
                        <label for="cust_cpass">Confirm Password</label>
                        <input type="password" maxlength="20" name="cust_cpass" id="cust_cpass" class="<?= (in_array('cust_pass',$field_error) ? 'error' : '') ?>" />
                    </fieldset>
                    <fieldset class="field">
                    	<label for="f_c_submit" style="visibility:hidden;">submit</label>
						<input id="f_c_submit" type="submit" class="submit" name="register" value="Create Account" />	
	    			</fieldset>
				</fieldset>
			</form>
		
			<? 
		}
		
		// ****************************************************
		// check totals and proceed to payment
		else if( $stage=='confirm' ){  

			/* originally, events could be sold as items so a flag called 'physical' was used for products.
			   if we reinstate this, then we'd need to check if the basket had any items that needs shipping
			   in the condition below */
			//echo is_array($basket->basket).'<br />';
			//echo 'basket: <pre>'. print_r($basket->basket,true) .'</pre>';
			if ($store->config['collect-delivery-addr']) {
				if( is_array($basket->basket) && count($basket->basket)>0){ 
					?>
					<form action="<?=$storeURL?>/checkout/" method="post" id="deliveryaddress" class="checkout">
						<? 
						if( $addresses = $account->getDeliveryAddresses($_COOKIE['memberID']) ){ 
							?>
							<fieldset>
							<label for="addressBook">Address Book</label>
							<select name="addressBook" id="addressBook">
								<option value="">-- select address --</option>
								<? 
								foreach( $addresses as $addr ){ 
									if( $addr->title>'' ){ 
										?><option value="<?= $addr->addr_id ?>"><strong><?= $addr->title .'</strong> ('. $addr->house .' '. $addr->street .', '. $addr->town_city .', '. $addr->post_code .')' ?></option><? 
									}
									else{ 
										?><option value="<?= $addr->addr_id ?>"><?= $addr->house .' '. $addr->street .', '. $addr->town_city .', '. $addr->post_code .'' ?></option><? 
									} 
								} 
								?>
							</select>
							<button type="submit" name="addressBookUsed" value="1">Submit</button>
							</fieldset>
							<? 
						} 
						?>
						
						<fieldset>
							<input type="hidden" name="mode" value="deliveryaddress" />
							<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
							<input type="hidden" name="stage" value="confirm" />
							<input type="hidden" name="cartID" value="<?= $cartID ?>" />
							<legend>Delivery address</legend>
							<p class="instructions">We'll store this address in an address book to make ordering faster in the future.<br />  
							Please give your address a title to make it easier to find.</p>
							<label for="del_title">Title</label>
							<input type="text" name="del_title" id="del_title" value="" maxlength="45" />
							<label for="del_house">House/Flat name or number</label>
							<input type="text" name="del_house" id="del_house" value="" />
							<label for="del_street">Street</label>
							<input type="text" name="del_street" id="del_street" value="" />
							<label for="del_locality">Area</label>
							<input type="text" name="del_locality" id="del_locality" value="" />
							<label for="del_town_city">Town / City</label>
							<input type="text" name="del_town_city" id="del_town_city" value="" />
							<label for="del_county">County / Region</label>
							<input type="text" name="del_county" id="del_county" value="" />
							<label for="del_post_code">Post Code</label>
							<input type="text" name="del_post_code" id="del_post_code" value="" />
							<label for="del_country_id">Country</label>
							<select name="del_country_id" id="del_country_id">
								<?
								$countries = $basket->getCountryZoneList();
								$selected = isset($_POST['del_country']) ? $_POST['del_country'] : 222; // default to UK
								foreach( $countries as $country ){
								?>
								<option value="<?= $country->country_id ?>"<?= ($selected==$country->country_id ? ' selected="selected"' : '') ?>><?= $country->title ?></option>
								<? } ?>
							</select>
							<button type="submit" name="confirmDelivery" value="1">Submit</button>
						</fieldset>
					</form>
					<? 
				}
				else print "Nothing to deliver";
			}
			else print "In confirm stage but this store does not collect delivery details.";
		}

		// ****************************************************
		// temporary payment processing 
		else if( $stage=='payment' ){ 
		
			$fields_required = array();
			array_push($fields_required, 'ccName', 'ccType', 'ccNumber', 'ccExDate', 'ccCVV');
			
			// Are we heading off somewhere sunny for payment services?
			if ($store->config['payment-gateway']) { 
				include $_SERVER['DOCUMENT_ROOT']."/store/snippets/gateway/".$store->config['payment-gateway'].".inc.php";
			}
			// Or processing it here?
			else {
				?>
				<form action="" method="post" id="paymentForm">

                <input type="hidden" name="stage" value="payment" />
                <input type="hidden" name="cartID" value="<?= $cartID ?>" />
                <input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
            
				<?php if ($store->config['collect-billing-addr']) {
                    array_push($fields_required, 'bill_house', 'bill_street', 'bill_town_city', 'bill_post_code', 'bill_country_id');
                    ?>
                    <fieldset>
                        <h3>Enter the address from your credit card</h3>
                        <? 
                        if( $addresses = $account->getDeliveryAddresses($_COOKIE['memberID']) ){ 
                            ?>
                            <fieldset>
                                <label for="addressBook">Address Book</label>
                                <select name="addressBook" id="addressBook">
                                    <option value="">-- select address --</option>
                                    <? 
                                    foreach( $addresses as $addr ){ 
                                        if( $addr->title>'' ){ 
                                            ?><option value="<?= $addr->addr_id ?>"><strong><?= $addr->title .'</strong> ('. $addr->house .' '. $addr->street .', '. $addr->town_city .', '. $addr->post_code .')' ?></option><? 
                                        }
                                        else{ 
                                            ?><option value="<?= $addr->addr_id ?>"><?= $addr->house .' '. $addr->street .', '. $addr->town_city .', '. $addr->post_code .'' ?></option><? 
                                        } 
                                    }
                                    ?>
                                </select>
                                <p id="orEnterNewAddress"><strong>Or enter a new address below</strong></p>
                            </fieldset>
                            <? 
                        } 
                        ?>
                        <div>
                        <label for="bill_house" class="<?= (in_array('bill_house',$fields_required) ? 'required' : '') ?>">House/Flat name or number</label>
                        <input type="text" name="bill_house" id="bill_house" value="<?= $bill_house ?>" class="<?= (in_array('bill_house',$field_error) ? 'error' : '') ?>" />
                        </div>
                        <div>
                        <label for="bill_street" class="<?= (in_array('bill_street',$fields_required) ? 'required' : '') ?>">Street</label>
                        <input type="text" name="bill_street" id="bill_street" value="<?= $bill_street ?>" class="<?= (in_array('bill_street',$field_error) ? 'error' : '') ?>" />
                        </div>
                        <div>
                        <label for="bill_locality" class="<?= (in_array('bill_locality',$fields_required) ? 'required' : '') ?>">Area</label>
                        <input type="text" name="bill_locality" id="bill_locality" value="<?= $bill_locality ?>" class="<?= (in_array('bill_locality',$field_error) ? 'error' : '') ?>" />
                        </div>
                        <div>
                        <label for="bill_town_city" class="<?= (in_array('bill_town_city',$fields_required) ? 'required' : '') ?>">Town / City</label>
                        <input type="text" name="bill_town_city" id="bill_town_city" value="<?= $bill_town_city ?>" class="<?= (in_array('bill_town_city',$field_error) ? 'error' : '') ?>" />
                        </div>
                        <div>
                        <label for="bill_county" class="<?= (in_array('bill_county',$fields_required) ? 'required' : '') ?>">County / Region</label>
                        <input type="text" name="bill_county" id="bill_county" value="<?= $bill_county ?>" class="<?= (in_array('bill_county',$field_error) ? 'error' : '') ?>" />
                        </div>
                        <div>
                        <label for="bill_post_code" class="<?= (in_array('bill_post_code',$fields_required) ? 'required' : '') ?>">Post Code</label>
                        <input type="text" name="bill_post_code" id="bill_post_code" value="<?= $bill_post_code ?>" class="<?= (in_array('bill_post_code',$field_error) ? 'error' : '') ?>" />
                        </div>
                        <div>
                        <label for="bill_country_id" class="<?= (in_array('bill_country_id',$fields_required) ? 'required' : '') ?>">Country</label>
                        <select name="bill_country_id" id="bill_country_id" class="<?= (in_array('bill_country_id',$field_error) ? 'error' : '') ?>">
                            <?
                            $countries = $basket->getCountryZoneList();
                            $selected = isset($_POST['del_country_id']) ? $_POST['del_country_id'] : 222; // default to UK
                            foreach( $countries as $country ){
                            ?>
                            <option value="<?= $country->country_id ?>"<?= ($selected==$country->country_id ? ' selected="selected"' : '') ?>><?= $country->title ?></option>
                            <? } ?>
                        </select>
                        </div>
                    </fieldset>
                <?php } ?>
               
				<?php if ($store->config['collect-cc-data']) { ?> 
                    <fieldset>
                        <h3>Credit card details</h3>
                        <div id="ccNameHolder">
                        <label for="ccName" class="<?= (in_array('ccName',$fields_required) ? 'required' : '') ?>">Name on the card</label>
                        <select name="ccTitle" id="ccTitle">
                        <? foreach( $account->nameTitles as $title ){ ?>
                            <option><?= $title ?></option>
                        <? } ?>
                        </select>
                        <input type="text" name="ccName[]" id="ccName" value="<?= $ccName[0] ?>" maxlength="32" class="<?= (in_array('ccName',$field_error) ? 'error' : '') ?>" />
                        <input type="text" name="ccName[]" id="ccName1" value="<?= $ccName[1] ?>" maxlength="32" class="<?= (in_array('ccName',$field_error) ? 'error' : '') ?>" />
                        </div>
                        <label for="ccType" class="<?= (in_array('ccType',$fields_required) ? 'required' : '') ?>">Card type</label>
                        <?
                        $ccTypes = array('1'=>'Visa','2'=>'Mastercard','9'=>'Solo','10'=>'UK Maestro', '11'=>'Electron', '14'=>'Maestro');
                        //asort($ccTypes);
                        ?>
                        <select name="ccType" id="ccType" class="<?= (in_array('ccType',$field_error) ? 'error' : '') ?>">
                            <option value="">-- please select --</option>
                            <? foreach( $ccTypes as $key => $value ){ ?>
                            <option value="<?= $key ?>"<?= ($ccType==$key ? ' selected="selected"' : '') ?>><?= $value ?></option>
                            <? } ?>
                        </select>
                        <label for="ccNumber" class="<?= (in_array('ccNumber',$fields_required) ? 'required' : '') ?>">Card number</label>
                        <input type="text" name="ccNumber" id="ccNumber" value="<?= $ccNumber ?>" maxlength="19" class="<?= (in_array('ccNumber',$field_error) ? 'error' : '') ?>" />		
                        <div>
                        <label for="ccExDate" class="<?= (in_array('ccExDate',$fields_required) ? 'required' : '') ?>">Expiry Date</label>
                        <select name="ccExDate[]" id="ccExMonth" class="<?= (in_array('ccExDate',$field_error) ? 'error' : '') ?>">
                        <? for($i=1;$i<=12;$i++){ ?>
                            <option value="<?= $i ?>"<?= ($ccExDate[0]==$i ? ' selected="selected"' : '') ?>><?= date('M',mktime(0,0,0,$i,01,2008)) ?></option>
                        <? } ?>
                        </select>
                        <select name="ccExDate[]" id="ccExYear" class="<?= (in_array('ccExDate',$field_error) ? 'error' : '') ?>">
                            <? for($i=date('Y');$i<=date('Y')+10;$i++){ ?>
                            <option value="<?= $i ?>"<?= ($ccExDate[1]==$i ? ' selected="selected"' : '') ?>><?= $i ?></option>
                            <? } ?>
                        </select>
                        </div>
                        <label for="ccCVV" class="<?= (in_array('ccCVV',$fields_required) ? 'required' : '') ?>">CVV number (security code)</label>
                        <input type="text" name="ccCVV" id="ccCVV" maxlength="3" value="<?= $ccCVV ?>" class="<?= (in_array('ccCVV',$field_error) ? 'error' : '') ?>" />
                        
                        <strong>Maestro and Switch only</strong>
                        <div>
                        <label for="ccStDate" class="<?= (in_array('ccStDate',$fields_required) ? 'required' : '') ?>">Start Date</label>
                        <select name="ccStDate[]" id="ccStMonth" class="<?= (in_array('ccStDate',$field_error) ? 'error' : '') ?>">
                        <? for($i=1;$i<=12;$i++){ ?>
                            <option value="<?= $i ?>"<?= ($ccStDate[0]==$i ? ' selected="selected"' : '') ?>><?= date('M',mktime(0,0,0,$i,01,2008)) ?></option>
                        <? } ?>
                        </select>
                        <select name="ccStDate[]" id="ccStYear" class="<?= (in_array('ccStDate',$field_error) ? 'error' : '') ?>">
                            <? for($i=date('Y');$i>=(date('Y')-5);$i--){ ?>
                            <option value="<?= $i ?>"<?= ($ccStDate[1]==$i ? ' selected="selected"' : '') ?>><?= $i ?></option>
                            <? } ?>
                        </select>
                        </div>	
                        <label for="ccIssue" class="<?= (in_array('ccIssue',$fields_required) ? 'required' : '') ?>">Issue Number</label>
                        <input type="text" name="ccIssue" id="ccIssue" maxlength="3" value="<?= $ccIssue ?>" class="<?= (in_array('ccIssue',$field_error) ? 'error' : '') ?>" />
                        </label>
                    </fieldset>
                <?php } ?>
                
				<? if( $basket->totals['donation']>0 || $basket->totals['sponsorships']>0){	?>
                    <fieldset>
                    <div id="giftaid">
                        <img src="/images/Gift_Aid_40mm_black.jpg" width="136" height="62" alt="Gift Aid logo" />
                        <p>If you are a UK taxpayer, <?=$site->name?> can reclaim the tax you have already paid on your donation 
                            at no extra cost to you.<br />Please tick the declaration box below.</p>
                        <input type="checkbox" name="giftaid" id="giftaid"<?= ($giftaid>'' ? ' checked="checked"' : '') ?> />
                        <p id="declaration">I wish all donations I have made for six years prior to this year, 
                            (but no earlier than 06/04/2000) and all donations I make from the date of this declaration 
                            until I notify you otherwise, to be treated as GiftAid donations.</p>
                        <p id="ga_footnote">
                            You must pay an amount of income tax and/or capital gains tax at least equal to the tax that the 
                            charity reclaims on your donations in the appropriate tax year.
                        </p>
                    </div>
        
                    <div id="postal">
                        <p></p>
                    </div>
                    </fieldset>
                <? } ?>
                
				<?php include $_SERVER['DOCUMENT_ROOT']."/store/snippets/order.summary.php"; ?>         
                
                <div id="payment-controls">
                <a id="cancel_button" href="<?= $storeURL ?>/shopping-basket/">Return to shopping basket</a>
                <input type="submit" id="makePayment" name="makePayment" value="Make this payment now" class="orange-button" />
                </div>

            	</form>
				<? 
			}
		}

		// we have received payment and can proceed
		else if( $stage=='complete' ){  
			
            // for purchases 
			if( is_array($basket->basket) && count($basket->basket)>=0 ){ 
            	?>
                <h3>Thank you for your order, <?= $account->properties->firstname ?></h3>
                <p>Thank you for your purchase. We�re sure you�ll be pleased with your order.</p>
                <p>Don�t forget you can contact <?=$site->name?> anytime about your order at <a href="mailto:<?=$site->config['contact_recipient_email']?>"><?=$site->config['contact_recipient_email']?></a>, 
                quoting order number <strong><?= $cartID ?></strong></p>
                <p>We will endeavor to dispatch your order within three working days.</p>
                <p></p>
                <p>Thank you.</p>
                <p></p>
                <p>To place another order <a href="<?=$storeURL?>">visit our shop</a>,
                read our <a href="/delivery-and-returns-policy/">delivery and returns policy</a>
                or <a href="/">return to our homepage</a>.
                </p>
			
				<? 
				if( !$noteSubmitted &&  0 ){ 
					?>
					<form action="/shop/checkout/" method="post" class="checkout" id="additionalInfo">
						<input type="hidden" name="cartID" value="<?= $cartID ?>" />
						<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
						<input type="hidden" name="stage" value="complete" />
						<fieldset>
							<legend>Additional information</legend>
							<p class="instructions">
								Is there anything else you need to tell us about this order?  Any specific delivery instructions?
							</p>
							<label for="memberTel">Telephone</label>
							<input type="text" name="memberTel" id="memberTel" maxlength="30" value="<?= $account->properties->telephone ?>" />
							<label for="orderNote">Order Note</label>
							<textarea name="orderNote" id="orderNote"></textarea>
							<button type="submit" name="additionalInfo" value="1">Submit</button>
						</fieldset>
					</form>
					<? 
				} 
			} 
			?>
			
			<? // for donations
			if( $basket->totals['donation']>0 && 0){ ?>
			<?= ( count($basket->basket)>0 ? '' : '<h3>Thank you for your donation, '. $account->properties->firstname .'</h3>' ) ?>
			<p>Thank you for your kind donation of <strong><?= $basket->currency . $basket->totals['donation'] ?></strong>. 
			Your gift will be put to excellent use; helping those in conflict-affected communities worldwide.</p>
			<? if( !$noteSubmitted ){ ?>
			<form action="/shop/checkout/" method="post" class="checkout" id="additionalInfo">
				<input type="hidden" name="cartID" value="<?= $cartID ?>" />
				<input type="hidden" name="stage" value="complete" />
				<fieldset>
					<legend>Additional information</legend>
					<p class="instructions">
						Do you have a preference for how your donation should be used or have a message for the fundraising team?
					</p>
					<label for="memberTel">Telephone</label>
					<input type="text" name="memberTel" id="memberTel" maxlength="30" value="<?= $account->properties->telephone ?>" />
					<label for="orderNote">Order Note</label>
					<textarea name="orderNote" id="orderNote"></textarea>
					<button type="submit" name="additionalInfo" value="1">Submit</button>
				</fieldset>
			</form>
			<? } ?>
			<p>Did you know there are other ways you can help?</p>
			<p>View our <a href="/supportmag/events/">events programme</a>
			<? if( !is_array($basket->basket) || count($basket->basket)==0 ){ ?>
			 or <a href="/shop/">visit the <?=$site->name?> Shop</a></p> 
			<? } ?>
			<p>Thanks again for your support and interest.</p>
			<? } ?>
			
			<? // for events
			if( $basket->totals['events']>0 ){ 
				echo ( count($basket->basket)>0 ? '' : '<h3>Thank you for joining a '.$site->name.' event, '. $account->properties->firstname .'</h3>' ) 
				?>
                <h4>Congratulations! </h4>
                <p>We have received your registration fee of &pound;<?= $basket->totals['events'] ?> and you have almost finished registering for your place(s). </p>
                <p>In order to complete your registration we need to know more about you, such as your t-shirt size, date of birth 
                and dietary requirements.</p>
                <p>Please complete your details on the event registration form, which will appear when you log-in to your <a href="<?= $ppLink ?>">personal event page</a> for the first time. 
                Please do this as soon as possible 
                to avoid any delay in completing your registration.</p>
                <p>An email to confirm you have paid the registration fee to take part in our event(s) will be sent to the 
                email address you have supplied. </p>
                
                <h4>What happens next�</h4>
                <p>Once you have registered your details with us, we�ll be in touch to send you a confirmation pack 
                which includes everything you need to get started.</p>
                <p>Any questions? Contact us at <a href="mail:events@maginternational.org">events@maginternational.org</a></p>
                <p><strong>Thank you for choosing to take part in our events!</strong></p> 
                
                <p>Go back to <a href="/events/">event page</a>?</p>
    
                <? 
			} 

			$basket->cartID = $cartID = 0; 
			// Start a new cookie
			setcookie('cartID', "0", (time()+3600*24), "/", $_SERVER['HTTP_HOST']);
		} 
		// end 'stage' condition 
		
		
		else if ($mode=='reminder' ){ 
			?>
			<form action="<?=$storeURL?>/checkout/" method="post" id="existing" class="checkout">
				<input type="hidden" name="cartID" value="<?= $cartID ?>" />
				<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
				<input type="hidden" name="mode" value="reminder" />
				<fieldset>
					<legend>Password reminder</legend>
					<p class="instructions">Please enter your registered email address and we'll send your password to your inbox.</p>
					<label for="reminder_email">Email address</label>
					<input type="text" maxlength="150" name="reminder_email" id="reminder_email" value="<?=$_GET['email']?>" />
					<input id="f_submit" type="submit" name="login" value="Send reminder" class="orange-button" />				
				</fieldset>
			</form>				
			<? 
		} 
		
		
		?>
		
        </div>
	    </div>

		<div id="secondarycontent">	
        
			<?php 
            if( $stage=='payment' && ($addr = $basket->getAddress('delivery',$_COOKIE['cartID'])) ){ 
                ?>
                <div id="show-address" class="store_panel">
                    <h4>Delivery Address<?= ($addr->title>'' ? ' ('. $addr->title .')' : '') ?></h4>
                    <p>
                    <?= $addr->house .' '. $addr->street ?><br />
                    <?= ($addr->address_2 ? $addr->address_2.'<br />' : '') ?>
                    <?= ($addr->locality ? $addr->locality.'<br />' : '') ?>
                    <?= ($addr->town_city ? $addr->town_city.'<br />' : '') ?>
                    <?= ($addr->county ? $addr->county.'<br />' : '') ?>
                    <?= ($addr->post_code ? $addr->post_code.'<br />' : '') ?>
                    <?= ($addr->country ? $addr->country : '') ?>
                    </p>
                </div>
                <? 
            } 

			/*
			if( $stage=='payment' ) { 
				?>
				<div id="maestro-holders" class="store_panel">
					<h4>Attention Maestro card holders</h4>
					<p>We are currently experiencing some problems with Maestro/Switch card payments. If you experience these problems whilst trying to donate to <?=$site->name?> or sponsor an event participant, please try using an alternative card if possible or contact the <?=$site->name?> fundraising department on <?=$site->config['contact_recipient_telephone']?> or <a href="mailto:<?=$site->config['contact_reciepient_email']?>"><?=$site->config['contact_reciepient_email']?></a></p>
				</div>
				<? 
			} 
			*/

			include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.security.php');
			?>

		</div>
	</div>
    		

<?php
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>