<?php

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/subscriber.class.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/member.class.php');
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
	$emailOut = new Newsletter;

	$newsletter = new Newsletter();
	
	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($mode);

	// If we try to get in the store outside a secure connection 
	// Redirect to the secure version
	if ($store->config['use_https'] && $mode!="edit") {
		if( $_SERVER['SERVER_PORT']!=443){ // replace with a regex
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

	$feedback = 'error';

	$field_error = array();
	
	$tags = new Tags();

	if( $basket->getGrandTotal()==0 ){
		$_POST = false;
		$mode = 'emptyBasket';
		$message[] = 'You have nothing in your shopping basket';
	}	
	//print "Got total (".$basket->getGrandTotal().") stage($stage) mode($mode)<br>\n";
	
	if( isset($_POST) && $_POST ) {

		//print "Posted stage($stage) mode($mode)<br>\n";

		// =====================================================================
		// First worry about page processing
		$action = read($_POST,'treeline','');
		$redirect = true;
		if ($_POST['post_action']) $action = $_POST['post_action'];
		
		if ($action == 'Save changes' || $action=="Save") {
		
			//print "post(".print_r($_POST, true).")<br>\n";
			$content->save();
			$page->save(true);
			
			// Content is saved so redirect the user
			$feedback = 'feedback=success&message='.urlencode($page->getLabel("tl_pedit_msg_saved", true));
			
			//$author_redirect = '/treeline/pages/?action=edit&'.$feedback;
			$author_redirect = "/treeline/pages/action=edit";
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
			//$publish_redirect .= '&'.$feedback;

			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");

			// For users with authorisation go to the publish option				
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				//print "would go to $publish_redirect<br>";
				$redirectURL = $publish_redirect; // show them the publish option
			}
			// Just go back to the page edit listing. 
			else $redirectURL = $author_redirect; 
			
			if ($redirect && $action=='Save changes') redirect($redirectURL);
				
		}
		// Discard changes was pressed
		else if ($action == 'Discard changes') {
			// We have to manually release the page here as we are not saving the page.
			$page->releaseLock($_SESSION['treeline_user_id']);			
			if ($redirect) redirect ('/treeline/pages/?action=edit&feedback=notice&message='.urlencode($page->getLabel("tl_pedit_err_nosave", true)));
		}

		$redirect=false;
		// End of page processing
		// =====================================================================

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
				
					//print "store config(".print_r($store->config, 1).")<br>\n";
					if($store->config['collect-cc-data'] ){
						//echo '<pre style="color:#fff">'. print_r($_POST,true) .'</pre>';
	
						$pmsg[]="Processing transaction ".$basket->cartID;
						// Validation		
														
						// CARD
						//print "0-".$ccName[0]." 1-".$ccName[1]."<br>\n";
						if(!$ccName[0] || $ccName[0]<=''){
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
						}
						else {
							$ccStart = ($ccStDate[0]<10 ? '0'.$ccStDate[0] : $ccStDate[0]) .'/'. substr($ccStDate[1],2);
						}
											
						
						// Address
						if( !$bill_house || $bill_house<='' ){
							// house & street
							$message[] = 'You need to enter the <strong>first line</strong> of your billing address</strong>';
							$field_error[] = 'bill_house';			
						}
						if( !$bill_locality || $bill_locality<='' ){
							// town
							$message[] = 'Your need to enter your <strong>town or city</strong>';
							$field_error[] = 'bill_locality';			
						}
						if( !$bill_town_city || $bill_town_city<='' ){
							// state/county
							$message[] = 'Your need to enter your <strong>state</strong>';
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
		
		
						if( !$message || count($message)==0 ){
						
							$pmsg[] = "Process transation - Add addresses<br>\n";
							// do actual payment processing here...
						
							$address = array('title'=>'Billing address');
							foreach($_POST as $key => $value){
								if( preg_match('/bill_[_a-z]/',$key) ){
									$key = substr($key,5);
									$address[$key] = $value;
								}
							}
							$billAddressID = $account->createAddress($_COOKIE['memberID'],$address);
							if ($billAddressID>0) {
								if( !$basket->addOrderAddress( 'billing', $_COOKIE['cartID'], $billAddressID, $_COOKIE['memberID'] ) ){
									$message[] = 'There was a problem with attaching this billing address to your order';							
								}
								else {
									$pmsg[] = "Process transation - Added billing address($billAddressID)<br>\n";
								}
							}
							else {
								$message[] = 'There was a problem with adding your billing address';					
							}	

							// Add delivery address to order if it exists.
							if ($del_house) {
								$address = array('title'=>'Shipping address');
								foreach($_POST as $key => $value){
									if( preg_match('/del_[_a-z]/',$key) ){
										$key = substr($key,4);
										$address[$key] = $value;
									}
								}
								if( $deliveryAddressID = $account->createAddress($_COOKIE['memberID'],$address) ){
									if( !$basket->addOrderAddress( 'delivery', $_COOKIE['cartID'], $deliveryAddressID, $_COOKIE['memberID'] ) ){
										$message[] = 'There was a problem with attaching the delivery address to your order';							
									}				
									else {
										$pmsg[] = "Process transation - Added delivery address($deliveryAddressID)<br>\n";
									}
								}
								else $message[] = 'There was a problem with adding your delivery address';					
							}
							
							// process payment...
							if( !$message || count($message)==0 ){
							
								// Update campaigns and gift aid
								$set = "gift_aid=".($giftaid>''?"1":"0").", ";
								$set .= "campaign=".($campaign+0).", ";
								$query="update store_orders set ".substr($set, 0, -2)." where order_id='".$basket->cartID."'";
								$pmsg[] = "Add gift aid and camps $query";
								$db->query($query);


								// If we have been sent a valid email address then subscribe them
								if ($email && is_email($email)) {
									print "Subscribe($email)<br>\n";
									$newsletter->subscribe();
								}
								
								$grandTotal = ($basket->total>0) ? $basket->getGrandTotal()+$basket->getPostageAndPacking() : $basket->getGrandTotal();
								
								
								// Need to add the actual payment processing code in here
								$feedback = "success";
								$message[] = "All parameters appear to be fine";
								$message[] = "But no transaction processing code is built yet";
								$message[] = 'We need to add code to take the money, send confirmation emails and <a href="'.$site->link.'about-us/content-testing/thank-you/">sent the visitor here</a>';
								if ($transactionProcessedSuccess) {
									$basket->cartToOrder($basket->cartID);
									$store->updateStock($basket);
	
								
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
										}
									}
									
									// then go to the completion stage...
									$stage='complete';
									

									// this is where we should kill the cookie!
									setcookie('cartID',FALSE,time()-42000,'/',$_SERVER['HTTP_HOST']);
									unset($_COOKIE['cartID']);
								
								} 
								else {
									$pmsg[] = "Transaction processing failed(Not coded)";
								}
								// End of success(PAID - OK)
							}
							// end if no message(1)
						}
						else{
							$feedback = 'error';
							$pmsg[] = "There was a problem validating the data input";
						}
						// end if no message(1)
						
						//print "pm(".print_r($pmsg, 1)." m(".print_r($message, 1).")<br>\n";
						if ($pmsg || $message) {
							if (is_array($pmsg)) foreach ($pmsg as $tmp) $sendtmp.=$tmp."\n";
							if (is_array($message)) foreach ($message as $tmp) $sendtmp.=$tmp."\n";
							else $sendtmp.=$message."\n";
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

	// If we have nipped into preview mode we need to set up lots of stuff
	// Just do it in one place as too many templted to keep copying it to.
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");

	// Page specific options
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('1col', 'thanks', '../store/style/store', '../store/style/checkout','../store/style/store_panels'); // all attached stylesheets
	
	$js = array('jquery','../store/behaviour/store','page_functions'); // all atatched JS behaviours
	$extraJS = '

var giftaid = false;	
var campaign = false;
var register = false;
function toggleGiftAid() {
	var f = document.getElementById("giftaid-block");
	var h = document.getElementById("giftaid-link");
	if (giftaid) {
		f.style.display="none";
		h.style.backgroundImage = "url(\'/img/layout/res-open.png\')";
		giftaid=false;
	}
	else {
		f.style.display="block";
		h.style.backgroundImage = "url(\'/img/layout/res-close.png\')";
		giftaid=true;
	}
}
function toggleCampaign() {
	var f = document.getElementById("checkout-campaign-block");
	var h = document.getElementById("checkout-campaign-link");
	if (campaign) {
		f.style.display="none";
		h.style.backgroundImage = "url(\'/img/layout/res-open.png\')";
		campaign=false;
	}
	else {
		f.style.display="block";
		h.style.backgroundImage = "url(\'/img/layout/res-close.png\')";
		campaign=true;
	}
}

function toggleRegister() {
	var f = document.getElementById("checkout-register-block");
	var h = document.getElementById("checkout-register-link");
	if (register) {
		f.style.display="none";
		h.style.backgroundImage = "url(\'/img/layout/res-open.png\')";
		register=false;
	}
	else {
		f.style.display="block";
		h.style.backgroundImage = "url(\'/img/layout/res-close.png\')";
		register=true;
	}
}
	
function toggleDelivery() {
	var frm = document.getElementById("paymentForm");
	var f = document.getElementById("deliveryAddress");
	var h = document.getElementById("deliveryLink");

	if (f.style.display=="none") {
		f.style.display="block";
		frm.showDelivery.value=1;
		h.style.backgroundImage = "url(\'/img/layout/res-close.png\')";
	}
	else {
		f.style.display="none";
		frm.showDelivery.value=0;
		h.style.backgroundImage = "url(\'/img/layout/res-open.png\')";
	}
}
	
	
	'; // etxra page specific  JS behaviours
	

	
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
	
if ($mode=="edit") $extraJSbottom .= '

CKEDITOR.replace(\'ck_treeline_content\', { toolbar : \'contentStandard\' });

';

	
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');

$itemcount = $store->total;
?>	


<div id="fixed-content" style="visibility: hidden;"> 
</div>
    
<div id="contentholder-top"></div>
<div id="contentholder">

	<?php
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
	?>
    <h1 class="pagetitle">Checkout</h1>
    
    <div id="primarycontent">

        <?php
		if (!$stage && !$mode) $stage="payment";

		?>

        <div id="red-box-top"></div>
        <div id="red-box">
    
			<?php
			if ($stage=="payment") {
				?>
                <h2>Your credit or debit card details</h2>
                <!--//<pre><?//= print_r($_POST,true) ?></pre>
                <pre><?//= print_r($_COOKIE,true) ?></pre>//-->
                <?php
                echo highlightSearchTerms(validateContent($content->draw()), $_GET['keywords'], 'span', 'keywords');
            }
			?>
            
            <div id="white-box-top"></div>
            <div id="white-box">
            
				<?php
		        echo drawFeedback($feedback,$message);
				?>
    
                <div id="basket">
                    
                    <? 
					//print "Stage($stage) mode($mode)<br>\n";
                    // ****************************************************
                    // Payment processing 
                    if ($stage=='payment') { 
                    
                        $fields_required = array();
                        array_push($fields_required, 'ccName', 'ccType', 'ccNumber', 'ccExDate', 'ccCVV');
                        
                        // Are we heading off somewhere sunny for payment services?
						//print "Use gateway(".$store->config['payment-gateway'].")<br>\n";
                        if ($store->config['payment-gateway']) { 
                            include $_SERVER['DOCUMENT_ROOT']."/store/snippets/gateway/".$store->config['payment-gateway'].".inc.php";
                        }
                        // Or processing it here?
                        else {
							?>
							<form action="" class="std-form" method="post" id="paymentForm">
			
							<input type="hidden" name="stage" value="payment" />
							<input type="hidden" name="cartID" value="<?= $cartID ?>" />
							<input type="hidden" name="subscribe" value="<?= $_REQUEST['subscribe'] ?>" />
						
							
                            <!-- Credit card/billing top block -->
                            <fieldset class="block">

								<?php 
                                // Collect Credit card details
                                if ($store->config['collect-cc-data']) { 
                                    ?> 
                                    <fieldset id="checkout-ccdata">
                                        <p class="larger">Card details</p>
    
                                        <div class="field">
                                            <label for="ccType" class="<?= (in_array('ccType',$fields_required) ? 'required' : '') ?>">Type of card</label>
                                            <?
                                            $ccTypes = array('1'=>'Visa','2'=>'Mastercard','9'=>'Solo','10'=>'UK Maestro', '11'=>'Electron', '14'=>'Maestro');
                                            //asort($ccTypes);
                                            ?>
                                            <select name="ccType" id="ccType" class="text <?= (in_array('ccType',$field_error) ? 'error' : '') ?>">
                                                <option value="">-- please select --</option>
                                                <? foreach( $ccTypes as $key => $value ){ ?>
                                                <option value="<?= $key ?>"<?= ($ccType==$key ? ' selected="selected"' : '') ?>><?= $value ?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                        
                                        <div id="ccNameHolder" class="field">
                                            <label for="ccName" class="<?= (in_array('ccName',$fields_required) ? 'required' : '') ?>">Name on card</label>
                                            <input type="text" name="ccName[]" id="ccName" value="<?= $ccName[0] ?>" maxlength="32" class="text <?= (in_array('ccName',$field_error) ? 'error' : '') ?>" />
                                        </div>
    
                                        <div class="field">
                                            <label for="ccNumber" class="<?= (in_array('ccNumber',$fields_required) ? 'required' : '') ?>">Card number</label>
                                            <input type="text" class="text" name="ccNumber" id="ccNumber" value="<?= $ccNumber ?>" maxlength="19" class="text <?= (in_array('ccNumber',$field_error) ? 'error' : '') ?>" />		
                                        </div>
                                        
                                        <div class="field">
                                            <label for="ccExDate" class="<?= (in_array('ccExDate',$fields_required) ? 'required' : '') ?>">Expiry Date</label>
                                            <select name="ccExDate[]" id="ccExMonth" class="text <?= (in_array('ccExDate',$field_error) ? 'error' : '') ?>">
                                            <? 
                                            for($i=1;$i<=12;$i++){ 
                                                ?>
                                                <option value="<?= $i ?>"<?= ($ccExDate[0]==$i ? ' selected="selected"' : '') ?>><?= date('M',mktime(0,0,0,$i,01,2008)) ?></option>
                                                <? 
                                            } 
                                            ?>
                                            </select>
                                            <select name="ccExDate[]" id="ccExYear" class="text <?= (in_array('ccExDate',$field_error) ? 'error' : '') ?>">
                                                <? for($i=date('Y');$i<=date('Y')+10;$i++){ ?>
                                                <option value="<?= $i ?>"<?= ($ccExDate[1]==$i ? ' selected="selected"' : '') ?>><?= $i ?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                        
                                        <div class="field" id="checkout-cvv">
                                            <label for="ccCVV" class="<?= (in_array('ccCVV',$fields_required) ? 'required' : '') ?>">CVV number</label>
                                            <input type="text" name="ccCVV" id="ccCVV" maxlength="3" value="<?= $ccCVV ?>" class="text <?= (in_array('ccCVV',$field_error) ? 'error' : '') ?>" />
                                            <p>The last 3 digits on the card signature strip</p>
                                        </div>
                                        
                                        <div id="checkout-maestro-only">
                                            <p><strong>Maestro and Switch only</strong></p>
                                            <div class="field">
                                                <label for="ccStDate" class="<?= (in_array('ccStDate',$fields_required) ? 'required' : '') ?>">Start Date</label>
                                                <select name="ccStDate[]" id="ccStMonth" class="text <?= (in_array('ccStDate',$field_error) ? 'error' : '') ?>">
                                                <? for($i=1;$i<=12;$i++){ ?>
                                                    <option value="<?= $i ?>"<?= ($ccStDate[0]==$i ? ' selected="selected"' : '') ?>><?= date('M',mktime(0,0,0,$i,01,2008)) ?></option>
                                                <? } ?>
                                                </select>
                                                <select name="ccStDate[]" id="ccStYear" class="text<?= (in_array('ccStDate',$field_error) ? 'error' : '') ?>">
                                                    <? for($i=date('Y');$i>=(date('Y')-5);$i--){ ?>
                                                    <option value="<?= $i ?>"<?= ($ccStDate[1]==$i ? ' selected="selected"' : '') ?>><?= $i ?></option>
                                                    <? } ?>
                                                </select>
                                            </div>	
    
                                            <div class="field">
                                                <label for="ccIssue" class="<?= (in_array('ccIssue',$fields_required) ? 'required' : '') ?>">Issue Number</label>
                                                <input type="text" name="ccIssue" id="ccIssue" maxlength="3" value="<?= $ccIssue ?>" class="text <?= (in_array('ccIssue',$field_error) ? 'error' : '') ?>" />
                                            </div>
                                            
                                        </div>
                                        
                                    </fieldset>
                                    <?php 
                                } 

								?>
                                <div id="store-addresses">
                                <?php
								
								// Collect billing address
								if ($store->config['collect-billing-addr']) {
									array_push($fields_required, 'bill_house', 'bill_locality', 'bill_town_city', 'bill_post_code', 'bill_country_id');
									?>
									<fieldset class="address billing">
										<p class="larger">Billing address</p>
										<div class="field">
											<label for="bill_house" class="<?= (in_array('bill_house',$fields_required) ? 'required' : '') ?>">Address</label>
											<input type="text" name="bill_house" id="bill_house" value="<?= $bill_house ?>" class="text <?= (in_array('bill_house',$field_error) ? 'error' : '') ?>" />
										</div>
										
										<div class="field">
											<label for="bill_street" class="<?= (in_array('bill_street',$fields_required) ? 'required' : '') ?>">Address 2</label>
											<input type="text" name="bill_street" id="bill_street" value="<?= $bill_street ?>" class="text <?= (in_array('bill_street',$field_error) ? 'error' : '') ?>" />
										</div>
										
										<div class="field">
											<label for="bill_locality" class="<?= (in_array('bill_locality',$fields_required) ? 'required' : '') ?>">City</label>
											<input type="text" name="bill_locality" id="bill_locality" value="<?= $bill_locality ?>" class="text <?= (in_array('bill_locality',$field_error) ? 'error' : '') ?>" />
										</div>
										
										<div class="field">
											<label for="bill_town_city" class="<?= (in_array('bill_town_city',$fields_required) ? 'required' : '') ?>">State</label>
											<input type="text" name="bill_town_city" id="bill_town_city" value="<?= $bill_town_city ?>" class="text <?= (in_array('bill_town_city',$field_error) ? 'error' : '') ?>" />
										</div>
										
										<!--
										<div class="field">
										<label for="bill_county" class="<?= (in_array('bill_county',$fields_required) ? 'required' : '') ?>">County / Region</label>
										<input type="text" name="bill_county" id="bill_county" value="<?= $bill_county ?>" class="text <?= (in_array('bill_county',$field_error) ? 'error' : '') ?>" />
										</div>
										-->
										<div class="field">
											<label for="bill_post_code" class="<?= (in_array('bill_post_code',$fields_required) ? 'required' : '') ?>">Zip code</label>
											<input type="text" name="bill_post_code" id="bill_post_code" value="<?= $bill_post_code ?>" class="text <?= (in_array('bill_post_code',$field_error) ? 'error' : '') ?>" />
										</div>
										
										<div class="field">
											<label for="bill_country_id" class="<?= (in_array('bill_country_id',$fields_required) ? 'required' : '') ?>">Country</label>
											<select name="bill_country_id" id="bill_country_id" class="text <?= (in_array('bill_country_id',$field_error) ? 'error' : '') ?>">
											<?
											$countries = $basket->getCountryZoneList();
											$selected = isset($_POST['bill_country_id']) ? $_POST['bill_country_id'] : 222; // default to UK
											foreach( $countries as $country ){
											?>
											<option value="<?= $country->country_id ?>"<?= ($selected==$country->country_id ? ' selected="selected"' : '') ?>><?= $country->title ?></option>
											<? } ?>
											</select>
										</div>
									</fieldset>
									<?php 
								} 
								
								// Collect delivery address
								if ($store->config['collect-deliver-addr']) {
									array_push($fields_required, 'del_house', 'del_locality', 'del_town_city', 'del_post_code', 'del_country_id');
									?>
									<fieldset class="address delivery">
                                    	<input type="hidden" name="showDelivery" value="<?=($showDelivery?1:0)?>" />
										<p class="larger" style="padding: 20px 0 0;"><a id="deliveryLink" class="delivery-link<?=($showDelivery?"-open":"")?>" href="javascript:toggleDelivery();">Delivery address</a></p>
                                        <p>If different to billing address</p>
                                        <div id="deliveryAddress" style="display:<?=($showDelivery?"block":"none")?>">
                                            <div class="field">
                                                <label for="del_house" class="<?= (in_array('del_house',$fields_required) ? 'required' : '') ?>">Address</label>
                                                <input type="text" name="del_house" id="del_house" value="<?= $del_house ?>" class="text <?= (in_array('del_house',$field_error) ? 'error' : '') ?>" />
                                            </div>
                                            
                                            <div class="field">
                                                <label for="del_street" class="<?= (in_array('del_street',$fields_required) ? 'required' : '') ?>">Address 2</label>
                                                <input type="text" name="del_street" id="del_street" value="<?= $del_street ?>" class="text <?= (in_array('del_street',$field_error) ? 'error' : '') ?>" />
                                            </div>
                                            
                                            <div class="field">
                                                <label for="del_locality" class="<?= (in_array('del_locality',$fields_required) ? 'required' : '') ?>">City</label>
                                                <input type="text" name="del_locality" id="del_locality" value="<?= $del_locality ?>" class="text <?= (in_array('del_locality',$field_error) ? 'error' : '') ?>" />
                                            </div>
                                            
                                            <div class="field">
                                                <label for="del_town_city" class="<?= (in_array('del_town_city',$fields_required) ? 'required' : '') ?>">State</label>
                                                <input type="text" name="del_town_city" id="del_town_city" value="<?= $del?>" class="text <?= (in_array('del_town_city',$field_error) ? 'error' : '') ?>" />
                                            </div>
                                            
                                            <!--
                                            <div class="field">
                                            <label for="bill_county" class="<?= (in_array('bill_county',$fields_required) ? 'required' : '') ?>">County / Region</label>
                                            <input type="text" name="bill_county" id="bill_county" value="<?= $bill_county ?>" class="text <?= (in_array('bill_county',$field_error) ? 'error' : '') ?>" />
                                            </div>
                                            -->
                                            <div class="field">
                                                <label for="del_post_code" class="<?= (in_array('del_post_code',$fields_required) ? 'required' : '') ?>">Zip code</label>
                                                <input type="text" name="del_post_code" id="del_post_code" value="<?= $del_post_code ?>" class="text <?= (in_array('del_post_code',$field_error) ? 'error' : '') ?>" />
                                            </div>
                                            
                                            <div class="field">
                                                <label for="del_country_id" class="<?= (in_array('del_country_id',$fields_required) ? 'required' : '') ?>">Country</label>
                                                <select name="del_country_id" id="del_country_id" class="text <?= (in_array('del_country_id',$field_error) ? 'error' : '') ?>">
                                                <?
                                                $countries = $basket->getCountryZoneList();
                                                $selected = isset($_POST['del_country_id']) ? $_POST['del_country_id'] : 222; // default to UK
                                                foreach( $countries as $country ){
                                                ?>
                                                <option value="<?= $country->country_id ?>"<?= ($selected==$country->country_id ? ' selected="selected"' : '') ?>><?= $country->title ?></option>
                                                <? } ?>
                                                </select>
                                            </div>
                                   		</div>
									</fieldset>
									<?php 
								} 
								?>
                                </div>

                            </fieldset>
                            <!-- End of credit card/billing top block -->
                            
                            
                            <!-- Optional extras -->
                            <div id="optional-extras">
                            	<p class="larger">Optional extras</p>
                                
                                <!-- Start of gift aid block -->
                                <fieldset class="block">
                                
                                    <? 
                                    if( $basket->totals['donation']>0 || $basket->totals['sponsorships']>0){	
                                        ?>
                                        <div id="giftaid">
                                        	<p class="larger giftaid-link"><a class="giftaid-link" id="giftaid-link" href="javascript:toggleGiftAid();">UK taxpayer? Give 20% extra for free with GiftAid</a></p>
                                            <div id="giftaid-block">
                                                <p class="larger giftaid-logo">If you are a UK taxpayer, <?=$site->name?> can reclaim the tax you have already paid on your donation at no extra cost to you.</p>
                                                <p>Please tick the declaration box below.</p>
                                                <div id="declaration">
                                                	<input type="checkbox" name="giftaid" id="giftaid"<?= ($giftaid>'' ? ' checked="checked"' : '') ?> /> 
                                                    <p>I wish all donations I have made for six years prior to this year, (but no earlier than 06/04/2000) and all donations I make from the date of this declaration until I notify you otherwise, to be treated as GiftAid donations.</p>
                                                </div>
                                                <p>You must pay an amount of income tax and/or capital gains tax at least equal to the tax that the charity reclaims on your donations in the appropriate tax year.</p>
                                            </div>
                                        </div>
                                        <? 
                                    } 
                                    ?>
                                </fieldset>

								<?php
								//print "Camps(".$campaign.")<br>\n";
								$campaign_list = '';
								$query = "SELECT * FROM store_donation_campaign WHERE msv=".($site->id+0)." AND active=1 ORDER BY added DESC";
								if ($results = $db->get_results($query)) {
									foreach ($results as $result) {
										$campaign_list.='
<li>
<input type="radio" id="'.$result->id.'" class="radio" name="campaign" value="'.$result->id.'" '.($result->id==$campaign?'checked="checked"':"").' />
<label for="f_camp_'.$result->id.'">'.$result->title.'</label>
</li>
';
									}
								}
								if ($campaign_list) {
									?>
									<fieldset class="block">
										<div id="checkout-campaign">
											<p class="larger giftaid-link"><a class="giftaid-link" id="checkout-campaign-link" href="javascript:toggleCampaign();">Give to a specific campaign</a></p>
											<div id="checkout-campaign-block">
												<p class="larger">AMREF is currently funding the following campaigns. If you wish your donation to support one of these campaigns, please tick a box.
												<p>All other donations will be used by AMREF to support our programmes wherever the need is greatest.</p>
												<ul id="campaign-list">
													<?=$campaign_list?>
												</ul>
                                                <div class="clearfix"></div>
											</div>
										</div>
									</fieldset>
									<?php
								}
								?>
                                
								<fieldset class="block">
                                    <div id="checkout-register">
                                        <p class="larger giftaid-link"><a class="giftaid-link" id="checkout-register-link" href="javascript:toggleRegister();">Find out how we've spent your donation by registering for email updates</a></p>
                                        <div id="checkout-register-block">
											<p class="larger">Keep track of how our work is progressing by registering for our regular email updates.</p>
                                            <div class="field">
                                                <label for="f_email">Your email address:</label>
                                                <input class="text" type="text" id="f_email" value="<?=$_POST['email']?>" name="email" />
                                                <?php
												echo $newsletter->drawPreferences($site->id);
                                                ?>
                                            </div>
                                            <p>You can unsubscribe or change your preferences at any time. <a href="<?=$site->link?>privacy-policy/" target="_blank">Privacy policy</a>.</p>
                                        </div>
                                    </div>
                                </fieldset>

                            </div>
                            <!-- // Optional extras -->
                            
                            
                            <!-- Pay now block -->
                            <fielset class="block">
								<?php 
                                if (
									is_array($basket->basket) && count($basket->basket) ||
									is_array($basket->events) && count($basket->events)									
									) {
                                    include $_SERVER['DOCUMENT_ROOT']."/store/snippets/order.summary.php"; 
                                    ?>
                                    <div id="payment-controls">
                                        <div class="left">
                                        <p class="donation-totals">Complete this purchase</p>
                                        <p><a id="cancel_button" href="<?=$storeURL?>/shopping-basket/">Go back and edit your shopping before you proceed</a></p>
                                        </div>
                                        <div class="right">
	                                        <input type="submit" id="makePayment" name="makePayment" value="Click to make&#10;this donation" class="submit" />
                                        </div>
                                    </div>
                                    <?php
                                }
                                else if($basket->totals['donation']>0) {
                                    ?>
                                    <div id="payment-controls">
                                        <div class="left">
                                        <p class="donation-totals">Make a donation of $<?=$basket->totals['donation']?><?=$basket->donation['frequency']?" every month":""?></p>
                                        <p><a id="cancel_button" href="<?=$site->link?>ways-to-give/make-a-donation/">Change this donation before you proceed</a></p>
                                        </div>
                                        <div class="right">
	                                        <input type="submit" id="makePayment" name="makePayment" value="Click to make&#10;this donation" class="submit" />
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>         

							</fieldset>
                            							
							<div class="clearfix"></div>
							</form>
							<?php
						}
						// End internal processing
                    }

					else if ($mode=="edit") {
						?>
						<p>Payment forms are disabled in edit mode</p>
						<?php
					}
            
                    // we have received payment and can proceed
                    else if( $stage=='complete' ){  
                        
                        // for purchases 
                        if( is_array($basket->basket) && count($basket->basket)>=0 ){ 
                            ?>
                            <h3>Thank you for your order, <?= $account->properties->firstname ?></h3>
                            <p>Thank you for your purchase. We’re sure you’ll be pleased with your order.</p>
                            <p>Don’t forget you can contact <?=$site->name?> anytime about your order at <a href="mailto:<?=$site->config['contact_recipient_email']?>"><?=$site->config['contact_recipient_email']?></a>, 
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
                            
                            <h4>What happens next…</h4>
                            <p>Once you have registered your details with us, we’ll be in touch to send you a confirmation pack 
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

                    // ****************************************************
                    // check totals and proceed to payment
                    else if($stage=='confirm' ){  
            
                        /* originally, events could be sold as items so a flag called 'physical' was used for products.
                           if we reinstate this, then we'd need to check if the basket had any items that needs shipping
                           in the condition below */
                        //echo is_array($basket->basket).'<br />';
                        //echo 'basket: <pre>'. print_r($basket->basket,true) .'</pre>';
						/*
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
						*/
                    }
                    
					// ****************************************************
                    // Sign-in or register
                    else if (!$stage && !$mode && 0) {	 
                        ?>
                    	<!-- 
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
                    	-->
                        <? 
                    }
                    ?>
                    
                </div>
                <!-- // End of basket div -->
    
            </div>
            <!-- // End of white-box div -->
            <div id="white-box-bottom"></div>
                
        </div>
        <!-- // End of red-box div -->
        <div id="red-box-bottom"></div>
    
	</div>
    <!-- // End of primary content div -->
    
</div>
<div id="contentholder-bottom"></div>


<div id="secondarycontent">	

    <?php 
	/*
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
	*/
	
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

    //include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.security.php');
    ?>

</div>
    		

<?php
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>