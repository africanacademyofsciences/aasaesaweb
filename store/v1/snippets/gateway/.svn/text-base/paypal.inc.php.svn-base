<?php

$i=1;
$no_shipping_required=1;	// No shipping
$paypal_hidden_inputs='';

$pptesting = true;

foreach ($basket->basket as $item) {

	$tmp_price = $item->price;
	$tmp_pnp = $basket->pandp;

	// All products are only 1p to me and some of us don't pay P&P too :o)
	$tmp_price=($_SERVER['REMOTE_ADDR']=="80.177.11.158" && $pptesting)?"0.01":$tmp_price;
	$tmp_pnp=($_SERVER['REMOTE_ADDR']=="80.177.11.158" && $pptesting)?"0":$tmp_pnp;
	
	$paypal_hidden_inputs .= '
		<input type="hidden" name="item_number_'.$i.'" value="'.$item->product_id.'" />
		<input type="hidden" name="item_name_'.$i.'" value="'.$item->title.'" />
		<input type="hidden" name="amount_'.$i.'" value="'.$tmp_price.'" />
		<input type="hidden" name="quantity_'.$i.'" value="'.$item->quantity.'" />
		<input type="hidden" name="shipping_'.$i.'" value="'.($i==1?$tmp_pnp:0).'" />
		';
	$no_shipping_required=2;	// Require a delivery address as we have products
	$i++;
}
$no_shipping_required=1;	// Overrulled, we have already collected it on site

// Add any donations
if ($basket->totals['donation']>0) {
	$paypal_hidden_inputs .= '
		<input type="hidden" name="item_number_'.$i.'" value="0" />
		<input type="hidden" name="item_name_'.$i.'" value="donation" />
		<input type="hidden" name="amount_'.$i.'" value="'.$basket->totals['donation'].'" />
		<input type="hidden" name="shipping_'.$i.'" value="0" />
		<!-- <input type="hidden" name="quantity_'.$i.'" value="1" /> -->
		';
}


// Do we have to maintain a store address book?
// Paypal appear to collect this info also
/*
$paypal_delivery_address='';
$query="select sab.*, sc.title from store_address_book sab
	LEFT JOIN store_orders so on sab.addr_id=so.shipping_addr_id
	LEFT JOIN store_countries sc on sab.country_id=sc.country_id
	where order_id='".$basket->cartID."'";
//print "$query<br>";
if ($row=$db->get_row($query)) {
	if ($row->house) $ad_street.=$row->house.", ";
	if ($row->street) $ad_street.=$row->street.", ";
	if ($row->address_2) $ad_city.=$row->address_2.", ";
	if ($row->locality) $ad_city.=$row->locality.", ";
	if ($row->town) $ad_city.=$row->town.", ";
	$paypal_delivery_address='
		<input type="hidden" name="shipping_street" value="'.substr($ad_street,0,-2).'" />
		<input type="hidden" name="shipping_city" value="'.substr($ad_city,0,-2).'" />
		<input type="hidden" name="shipping_state" value="'.$row->county.'" />
		<input type="hidden" name="shipping_zip" value="'.$row->post_code.'" />
		<input type="hidden" name="shipping_country" value="'.$row->title.'" />
		';
}
*/

$receiver_email="finance@dep.org.uk";
// Show me the money :o)
if ($_SERVER['REMOTE_ADDR']=="80.177.11.158" && $pptesting) $receiver_email="phil@iggyfred.com";

include $_SERVER['DOCUMENT_ROOT']."/store/snippets/order.summary.php"; 

$storeLink = $site->link."/".$storeURL;
while (!$done) {
	$storeLink = str_replace("//", "/", $storeLink);
	$done = !strpos($storeLink, "//");
}
$storeLink = str_replace("http:/", "http://", $storeLink);


//print "Cart ($cartID) <br>\n";
//print "Basket (".var_dump($basket).")<br>\n";

?>
<form class="rc_form" action="https://www.paypal.com/cgi-bin/webscr" method="post" id="f_paypal">
<fieldset id="typePayPal">
<legend>Pay by PayPal</legend>
    <!-- Shopping basket settings -->
    <input type="hidden" name="cmd" value="_cart" />
    <input type="hidden" name="business" value="<?=$receiver_email?>" />
    <input type="hidden" name="currency_code" value="GBP" />
    <input type="hidden" name="notify_url" value="<?=$siteLink?>store/paypal_ipn.php" />
    <input type="hidden" name="return" value="<?=$storeLink?>/checkout/?ref=<?=$basket->cartID?>&stage=complete" />
    <input type="hidden" name="upload" value="1" />
    <input type="hidden" name="custom" value="<?=$basket->cartID?>" />
	<input type="Hidden" name="payer_id" value="<?=$_COOKIE['memberID']?>" />
    <input type="hidden" name="no_shipping" value="<?=$no_shipping_required?>" />
    <?=$paypal_delivery_address?>
    <!-- Shopping basket items -->
    <?=$paypal_hidden_inputs?>
    <!-- Checkout button -->
	<div id="payment-controls">
		<a id="cancel_button" href="<?=$storeURL?>/shopping-basket/">Return to shopping basket</a>
		<input type="submit" id="makePayment" name="makePayment" value="Make this payment now" class="orange-button" />
	</div>
</fieldset>
</form>

<?php
/*
http://dep.ichameleon.com/bookshop/checkout/?
mc_gross=0.01&
protection_eligibility=Eligible&
address_status=confirmed&
item_number1=15&
payer_id=G65ED9CZZM6VW&
tax=0.00&
address_street=34+Merebrook+Road&
payment_date=04%3A50%3A40+Aug+14%2C+2009+PDT&
payment_status=Completed&
charset=windows-1252&
address_zip=SK11+8RH&
mc_shipping=0.00&
mc_handling=0.00&
first_name=Phil&
mc_fee=0.01&
address_country_code=GB&
address_name=Phil+Redclift&
notify_version=2.8&
custom=4a854d7f4453e&
payer_status=verified&
business=phil%40iggyfred.com&
address_country=United+Kingdom&
num_cart_items=1&
mc_handling1=0.00&
address_city=Macclesfield&
payer_email=paypal%40iggyfred.com&
verify_sign=A-JXtEBC7L7-WKvmO90k6iT.Q1g8Aay9mGVi75yaOfHdb-dciIqU0fEa&
mc_shipping1=0.00&
tax1=0.00&
txn_id=44B60677WT879230E&
payment_type=instant&
last_name=Redclift&
receiver_email=phil%40iggyfred.com&
item_name1=Sustainable+Lifestyles%3F&address_state=Cheshire&
payment_fee=&quantity1=1&
receiver_id=9HB2L5MHLPMES&txn_type=cart&
mc_currency=GBP&mc_gross_1=0.01&
residence_country=GB&
transaction_subject=4a854d7f4453e&
payment_gross=&
merchant_return_link=Return+to+S4X&
auth=y3bXsl2qe_She3_OC2FSD2bAHj1YPHwJ1vkSKO47wN8bf5xx3JrpaAB1QEhfgOpA4GJziHqy0vqtK6R5
*/
?>