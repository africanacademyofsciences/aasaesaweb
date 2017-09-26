<?php

ini_set("display_errors", 1);

include_once($_SERVER['DOCUMENT_ROOT']."/store/pesapal/functions.php");


$testing = false;
$testip = "80.0.182.170";
//$testip = '';	// Cancel test mode

// Should we go into test mode
$ip = $_SERVER['REMOTE_ADDR'];
if ($ip == $testip) {
	$testing = true;
	$usedemo = true;	// Put through smaller transactions
	$usedemo = false;	// Use the demo site rather than live payments
	//$usedemo = true;
	?>
    <h3>TEST: PESAPAL RUNNING IN TEST MODE :TEST</h3>
    <?php
	if ($usedemo) {
		?>
        <p>All payments handled by demo.pesapal.com</p>
        <?php
	}
	else {
		?>
        <p>Payment through live server so you will be billed</p>
        <?php
	}
}
else {
	// Live payment system
	//print "<p>IP $ip not configured for testing</p>\n";
}


//print "<!-- Member join/renew (".print_r($member, 1).") -->\n";


if ($action=="join") {
	//print "<!-- config() ".print_r($site->sitelist, 1).") -->\n";
	$amount = $site->sitelist[0]->fellow_membership;
	if ($testing) $amount = "0.30";
	$description = "Annual payment for fellow membership";
	?>
	<h2>Membership payment</h2>
	<p>The fee for <?=pp_format_years($years)?> membership is <?=($site->getConfig("pesapal_currency").$amount)?></p>
	<p>Please use the form below to make your payment via our secure Pesapal payment system</p>
	<?php
}
else if ($action=="renew") {

	$amount = 0;
	$aytotal = $mytotal = $tytotal = 0;
	
	$this_year= date("Y", time());
	for($i = $member->details->paid_year; $i<=$this_year; $i++) {
		if ($i==$member->details->paid_year); // already paid
		else {
			print "<!-- charge for year ".($i)."? -->\n";
			if ($i==$this_year) $tytotal = $site->sitelist[0]->fellow_renewal;
			else {
				$years_missed++;
				$mytotal += $site->sitelist[0]->fellow_renewal;
			}
		}
	}
	$amount = $tytotal + $mytotal;

	$years = read($_GET, "y", 0)+0;
	if ($years > 0) {
		$aytotal = $years * $site->sitelist[0]->fellow_renewal;
		$amount += $aytotal;
	}
	
	print "<!-- member(".print_r($member, 1)." to renew for this year ($tytotal) + $years_missed missed years ($mytotal) + $years additional years ($aytotal) total $ ($amount) -->\n";
	
	if ($testing) $amount = "0.20";
	$description = "Annual fellow membership renewal";
	?>
	<h2>Renew membership</h2>
    <?=formatrenewal($amount, $tytotal, $mytotal, $aytotal)?>
	<p>Please use the form below to make your payment via our secure Pesapal payment system</p>
    
    <form method="get" id="ppextra" >
    	<input type="hidden" name="action" value="renew" />
        <label for = "f_years">I would like to pre pay an additional  
        <select name="y" onchange="document.forms['ppextra'].submit();">
        	<option value="0">0</option>
        	<option value="1" <?=($years==1?'selected="selected"':"")?>>1</option>
        	<option value="2" <?=($years==2?'selected="selected"':"")?>>2</option>
        	<option value="3" <?=($years==3?'selected="selected"':"")?>>3</option>
        	<option value="4" <?=($years==4?'selected="selected"':"")?>>4</option>
        	<option value="5" <?=($years==5?'selected="selected"':"")?>>5</option>
        	<option value="6" <?=($years==6?'selected="selected"':"")?>>6</option>
        	<option value="7" <?=($years==7?'selected="selected"':"")?>>7</option>
        	<option value="8" <?=($years==8?'selected="selected"':"")?>>8</option>
        	<option value="9" <?=($years==9?'selected="selected"':"")?>>9</option>
        </select>
        years </label>
    </form>
    
	<?php
}


if ($amount>0) {
$test_key = 'wwdeds7jPSiFy+aFiMilsaWzDGlei6wV';
$test_secret = 'OVLCQOt9e3Y3StIA9jSOtrBDr7w=';
$test_link = 'http://demo.pesapal.com/api/PostPesapalDirectOrderV4';

$live_key = 'gJ3ugERFXuavRDBKgzdKbLkyb+8aeXU0';
$live_secret = 'KftEDX1yit9UBv7C8FethUTDu7k=';
$live_link = "https://www.pesapal.com/API/PostPesapalDirectOrderV4";

//$callback = "http://aasciences.ac.ke/thank-you/";
$callback = "http://aasciences.ac.ke/aas/en/membership-payment/";

//$ipn = "aasciences.ac.ke/store/pesapal/Pesapal-ipn-listener-php";

include_once($_SERVER['DOCUMENT_ROOT']."/store/pesapal/OAuth.php");

/*
1. token and params – null
2. consumer_key – merchant key issued by PesaPal to the merchant
3. consumer_secret – merchant secret issued by PesaPal to the merchant
4. signature_method ( leave as default ) – new OAuthSignatureMethod_HMAC_SHA1();
5. iframelink – the link that is passed to the iframe pointing to the PesaPal server
*/
$token = $params = NULL;

//Register a merchant account on
//demo.pesapal.com and use the merchant key for testing.
//When you are ready to go live make sure you change the key to the live account 
//registered on www.pesapal.com!
$consumer_key = $live_key;
if ($testing && $usedemo) $consumer_key = $test_key;

// Use the secret from your
// test account on demo.pesapal.com. When you are ready to go live make sure you 
//change the secret to the live account registered on www.pesapal.com!
$consumer_secret = $live_secret; 
if ($testing && $usedemo) $consumer_secret = $test_secret; 

$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

$iframelink = $live_link;
if ($testing && $usedemo) $iframelink = $test_link;
//if ($testing) print "Connect key($consumer_key) sec($consumer_secret) link($iframelink)<br>\n";


if ($testing) {
	//print "Member(".print_r($member, 1).")<br><br>\n";
	//print "Site(".print_r($site->config, 1).")<br><br>\n";
}

$member_id = $member->details->member_id;

//ONE of email or phonenumber is required by merchant
//Assign form details passed to pesapal‐iframe.php from shopping‐cart‐form.php to the specified variables.
$currency = "USD";
$amount = number_format($amount, 2);//format amount to 2 decimal places
$desc = "PPT:FELLOW:".$amount.":".$description;
$type = "MERCHANT";
$reference = uniqid();
$reference_id = $reference."M".$member_id; 
$first_name = $member->details->firstname;
$last_name = $member->details->surname;
$email = $member->details->email;
$phonenumber = '';
//if ($testing) print "Amount($amount)<Br> desc($desc)<br> type($type)<br> ref($reference)<br> first($first_name) last($last_name)<br> email($email)<br> phone($phone)<br><br>\n";
pplog($desc);

$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchemainstance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" Amount=\"".$amount."\" Description=\"".$desc."\" Type=\"".$type."\" Reference=\"".$reference_id."\" FirstName=\"".$first_name."\" LastName=\"".$last_name."\" Email=\"".$email."\" Currency=\"".$currency."\" PhoneNumber=\"".$phonenumber."\" xmlns=\"http://www.pesapal.com\" />";
$post_xml = htmlentities($post_xml);

//if ($testing) print "Send XML($post_xml)<br>\n";

// Construct the OAuth Request url
// Using the Oauth class included construct the oauth request url using the parameters declared above (the format is standard so no editing is required).
$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
//if ($testing) print "Consumer($consumer)<br>\n";

//post transaction to pesapal
$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
$iframe_src->set_parameter("oauth_callback", $callback);
$iframe_src->set_parameter("pesapal_request_data", $post_xml);
$iframe_src->sign_request($signature_method, $consumer, $token); 

// 7. Display the iframe
// Pass $iframe_src as the iframe’s src.
?>
<iframe src="<?=$iframe_src?>" width="100%" height="620px" scrolling="auto" frameBorder="0">
<p>Unable to load the payment page</p> </iframe>
<?php
}
else {
	?>
    <p>There is nothing to pay for</p>
    <?php
}
?>


