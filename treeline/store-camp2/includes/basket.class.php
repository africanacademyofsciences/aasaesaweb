<?
/************************************************************
Store object for Treeline

Author: Dan Donald
Started: 26-03-08
Description:
	This class effectively gets the core data for the store
	such as product listings and category tree.
	The admin side will use the CRUD methods to maintain the 
	database.

************************************************************/

class Basket {

	public $cartID;
	public $basket;
	public $total;
	public $totals; // store subtotals per section
	public $currency='&pound;';
	public $zone=1;
	public $country=222;
	public $pandp;
	public $donation = array('value'=>'','amount_id'=>'','frequency'=>0);
	public $events = array();
	public $sponsorships = array();
	public $addressTypes = array('billing','delivery');
	
	public $where_seen;
	public $no_swap;

	public function __construct($cartID=false, $zone=false){
		global $db;
		//echo '<p style="color:#fff">';
		//echo '<pre>'.print_r($_COOKIE,true).'</pre>';
		//echo 'cartID: '. $cartID .'<br />';
	
		if( !isset($cartID) || !$cartID ){
			//echo 'NO cartID!<br />';
			$this->cartID = uniqid();
			$this->create($this->cartID);
		}
		else{
			$this->cartID = $cartID;
		}
		
		if ($_SESSION['shipping_zone']) $this->country=$_SESSION['shipping_zone'];
		//echo 'cartID: '. $this->cartID .'<br />';
		//echo '</p>';

		$this->loadShoppingBasket($this->cartID);
		if( isset($zone) ){
			$this->zone = $zone;
		}
	}

//// Get/set methods

	// this can be used to get an attribute, unless a specialised method exists.
	// methods need to be in the format getThisMethodName.
	private function __get($attribute){	
		$method = str_replace(' ','','get'.ucwords( str_replace('_',' ',$attribute) ) );
		
		if( isset($this->$attribute)  ){
			return $this->$attribute;
		} else if( method_exists($this,$method) ){
			return call_user_method($method,$this);
		} else {
			return false;
		}
	}

	private function __set($attribute,$value){
		if( isset($this->$attribute) ){
			$this->$attribute = $value;
			return true;
		}else{
			return false;
		}
	}
	
	public function setCookie() {
		$expires = (time()+3600*24);
		$path = '/';
		if($this->cartID) {
			$local = !strchr($_SERVER['HTTP_HOST'], ".");
			setcookie('cartID', $this->cartID, $expires, $path, $local?'':$_SERVER['HTTP_HOST']);
			return true;	
		}
		return false;
	}
		
	public function create( $cartID=false ){
		global $db, $site;
		//echo 'CREATE: '. $cartID .'<br />';
		if( isset($cartID) ){
			$query = "INSERT INTO store_orders (order_id,date_cart_started,msv) VALUES ('$cartID',NOW(),".($site->id+0).")";
			if( $db->query($query) ){
				return true;
			}
		}
		return false;
	}
	
	public function add( $id=false, $quantity=1, $type=false ){
		global $db;
		
		if(isset($id) ) {
		
			if(!$type){
				if ($quantity>0) {
					// Add a product, find and store the item price
					$price = $db->get_var("SELECT price FROM store_inventory WHERE item_id = ".$id);
					$query = "REPLACE INTO store_orders_details 
					(
						order_id, item_id, quantity, date_added, price
					) 
					VALUES (
						'".$this->cartID."', $id, $quantity, NOW(), ".($price+0)."
					)";
					/* OLD WAY
					$query = "REPLACE INTO store_orders_details 
					(order_id, item_id, quantity, date_added) 
					VALUES ('{$this->cartID}',$id,$quantity,NOW())";
					*/
				}
				else {
					$query = "DELETE FROM store_orders_details WHERE order_id='".$this->cartID."' AND item_id=$id";
				}
			}
			else if( $type=='event' ){
				$query = "REPLACE INTO store_orders_events (order_id, entry_id, quantity, date_added) 
							VALUES ('{$this->cartID}', '$id', $quantity, NOW())";			
			}
			else if( $type=='sponsorship' ){
				$eventID = $id[0];
				$memberID = $id[1];
				$query = "REPLACE INTO store_orders_sponsorships (order_id, event_id, member_id, amount, date_added) 
							VALUES ('{$this->cartID}','$eventID',$memberID,$quantity,NOW())";			
			}
			//echo '<p style="color:#fff">query: '. $query .'</p>';
			
			if( $db->query($query) ){
				$this->loadShoppingBasket();
				return true;
			}
		}
		return false;
	}




	public function loadShoppingBasket( $cartID=false ){
		global $db, $site;
		
		$cartID = (isset($cartID) && $cartID>0) ? $cartID : $this->cartID;
		$this->cartID = $cartID;
		if( $cartID ){
			// get the donation info first - we can use this without anything in the basket
			$this->getOrderData();
			$this->getOrderDonation();
			
			if ($site->getConfig("store_events")) {
				$this->getOrderEvents();
				if ($site->getConfig("store_sponsorship")) {
					$this->getOrderSponsorships();
				}
			}
			
			// Now get the products!
			$query = "SELECT * FROM shopping_cart WHERE order_id='$cartID' ORDER BY date_added DESC";
			//echo 'load basket: '. $query .'<br />';
			if( $data = $db->get_results($query) ){
				$this->basket = $data;
				$this->getBasketTotal();
				if( count($data)==1 && $data[0]->physical==0 ){
					$this->pandp = 0;
				}else{
					$this->getPostageAndPacking();
				}
			}
			else{
				$this->basket = false;
				$this->pandp = 0;
				$this->total = 0;
			}
			$this->getGrandTotal();
			return true;
			//}else{
			//	return false;
			//}
		}else{
			return false;
		}
	}	
	

	
	
	public function update($basket=false, $donation=false, $events=false, $sponsorships=false, $info=false){
		global $db;

		$debug_update = false;
		//$debug_update = true;

		//print "u(basket, donation, events, spons, ".print_r($info, true).")<br>\n";
		
		// Just update order info if we have been sent any
		if (is_array($info)) {
			foreach ($info as $k=>$v) $set.="$k='$v',";
			if ($set) {
				$query="update store_orders set ".substr($set, 0, -1)." where order_id='".$this->cartID."'";
				//print "$query<br>\n";
				$db->query($query);
			}
		}
		
		$update=false;
		
		// 1) handle basket first...
		if( is_array($basket) && count($basket)>0 ){
			// 1.1) Delete the existing basket
			$query = "DELETE FROM store_orders_details WHERE order_id='{$this->cartID}'";
			$db->query($query);
			
			// 1.2) Loop through basket and store it...
			$query = "INSERT INTO store_orders_details 
				(
					order_id, item_id, quantity, date_added, price
				) 
				VALUES 
				";
			foreach($basket as $item){
				$price = $db->get_var("SELECT price FROM store_inventory WHERE item_id = ".$item['item_id']);
				$query .= "(
					'".$this->cartID."', 
					".$item['item_id'].", 
					".$item['quantity'].", 
					NOW(),
					".($price+0)."
					),";
			}
			$query = substr($query,0,-1);
			//print "Update orders($query)<br>\n";
			$db->query($query);
			$update=true;
		}
		// If there was something and its been removed still need to update
		else if( is_array($basket) && count($basket)==0 ){
			$query = "DELETE FROM store_orders_details WHERE order_id='{$this->cartID}'";
			if( $db->query($query) ) $update=true;
		}
		
		// 2) process any donation info we have been given...
		if ($debug_update) print "Start donation update<br>\n";
		if( is_array($donation) && count($donation)>0 && in_array($donation['type'],array('amount_id','value')) ){
			// Delete existing donation info...
			$query = "DELETE FROM store_orders_donations WHERE order_id='{$this->cartID}'";
			if ($debug_update) print "$query<br>\n";
			$db->query($query);
			
			// if we have a specific value
			if( $donation['type']=='value' && $donation['value']>0 ){
				$query = "INSERT INTO store_orders_donations 
					(order_id,donation_amount, used_suggested_id, frequency, use_gift_aid)
					VALUES 
					(
						'{$this->cartID}',
						".$donation['value'].", 
						0, 
						".$donation['frequency'].", 
						".$donation['gift_aid']."
					)
					";
			}
			// if we're using an amount_id - this allows us to track if those specific amounts are being used 
			// instead of it being co-incidence
			else if( $donation['type']=='amount_id' ){
				// What's the value associate with that amount_id
				$query = "SELECT amount FROM donation WHERE id=". $donation['value'];
				if( $value = $db->get_var($query) ){
				$query = "INSERT INTO store_orders_donations 
					(order_id,donation_amount, used_suggested_id, frequency, use_gift_aid)
					VALUES 
					('{$this->cartID}',". $value .", ". $donation['value'] .", ". $donation['frequency'] .", ". $donation['gift_aid'] .")
					";
				}
			}
			$db->query($query);
			if ($debug_update) print "q($query)<br>\n";
			if ($donation['donation_message'] || $donation['donation_written']) {
				$query="update store_orders_donations set donation_message='".htmlentities($donation['donation_message'],ENT_QUOTES)."', donation_written='".(($donation['donation_written']==1)?1:0)."' where order_id='".$this->cartID."'";
				//print "$query<br>";
				$db->query($query);
			}
			$update=true;
		}
		else if( is_array($donation) && count($donation)==0 ){
			$query = "DELETE FROM store_orders_donations WHERE order_id='{$this->cartID}'";
			if( $db->query($query) ){
				$update = true;
			}
		}
		
		
		// 4) Do we have any events?
		//print "update events(".print_r($events, true).")<br>\n";
		if( is_array($events) && count($events)>0 ){
			// Delete existing events...
			$query = "DELETE FROM store_orders_events WHERE order_id='{$this->cartID}'";
			$db->query($query);
			//print "Empty events($query)<br>\n";
			// Re add any events			
			foreach($events as $event){
				if ($event['quantity']>0) {
					$query = "INSERT INTO store_orders_events (order_id, date_added, entry_id, quantity)
						VALUES
						('".$this->cartID."', NOW(), ".$event['item_id'].", ".$event['quantity'].")
						";
					//print "$query<br>\n";
					$db->query($query);
				}
			}
			$update=true;
		}

		// 5) process any sponsorships
		if( is_array($sponsorships) && count($sponsorships)>0){
			// Delete existing donation info...
			$query = "DELETE FROM store_orders_sponsorships WHERE order_id='{$this->cartID}'";
			//echo $query .'<br />';
			$db->query($query);
			$query = "INSERT INTO store_orders_sponsorships (order_id, event_id, member_id, amount, date_added) VALUES ";
			$i=0;
			foreach($sponsorships as $key => $item){
				$query .= ($i>0 ? ',' : '');
				$tmp = explode('::',$key);
				//echo '<pre>'. print_r($tmp,true) .'</pre>';
				$query .= "('{$this->cartID}', '".$tmp[0]."', ".$tmp[1].", {$item}, NOW())";
				$i++;
			}
			//echo $query .'<br />';
			$db->query($query);
			//exit();
			$update=true;
		}
		else if( is_array($sponsorships) && count($sponsorships)==0 ){
			$query = "DELETE FROM store_orders_sponsorships WHERE order_id='{$this->cartID}'";
			//echo $query .'<br />';
			if( $db->query($query) ){
				//echo 'reload basket<br />';
				$update=true;
			}
		}

		// Reload as we always need to get new p&p costs
		$this->loadShoppingBasket();
		
		$query = "UPDATE store_orders SET postandpacking=".$this->pandp." WHERE order_id='".$this->cartID."'";
		$db->query($query);
		//print "$query<br>\n";
		
		if( $update ) return true;

		return false;

	}
	
	

	public function getBasketTotal(){
		if( is_array($this->basket) ){
			$total = 0;
			foreach($this->basket as $item){
				$total += $item->subtotal;
			}
			$this->total = $total;
			return $this->total;
		}else{
			$this->total = 0;
			return $this->total;
		}
	}


	public function getGrandTotal(){
		global $db;
		$this->cartID = (isset($cartID) && $cartID>0) ? $cartID : $this->cartID;
		// total so far
		$total = (isset($this->total) ? $this->total : $this->getBasketTotal());
		if( $total>0 ){
			$this->totals['basket'] = $total;
		}else{
			unset($this->totals['basket']);
		}
		// is there a donation amount?
		if( count($this->donation)>1 && isset($this->donation['value']) ){ 
			$total += $this->donation['value'];
			$this->totals['donation'] = $this->donation['value'];
		}else{
			unset($this->totals['donation']);
		}
		
		// get any event places...
		if( is_array($this->events) ){
			$this->totals['events'] = 0;
			foreach($this->events as $item){
				$total += ($item->price * $item->quantity);
				$this->totals['events'] += ($item->price*$item->quantity);
			}
		}
		else unset($this->totals['events']);

		// sponsorships?
		if( is_array($this->sponsorships) ){
			$this->totals['sponsorships'] = 0;
			foreach($this->sponsorships as $item){
				$total += $item->amount;
				$this->totals['sponsorships'] += $item->amount;
			}
		}
		else unset($this->totals['sponsorships']);

		
		return $total;
	}



	public function getPostageAndPacking(){
		global $db;
		if( isset($this->total) ){
			$weight = 0;
			foreach($this->basket as $item){
				$weight += $item->weight;
			}
			/*
			$query = "SELECT (sz.packaging_value + IF($weight<max_weight_used,($weight*sz.postage_per_kilo),(max_weight_used*sz.postage_per_kilo)))
						FROM store_shipping_zones sz
						LEFT JOIN store_countries sc ON sz.zone_id=sc.zone_id
						WHERE sc.country_id={$this->country}";
			*/
			if (!$weight) return 0;

			$query = "SELECT sz.packaging_value, sw.price
						FROM store_shipping_zones sz
						LEFT JOIN store_shipping_weight sw ON sz.zone_id=sw.zone_id
						LEFT JOIN store_countries sc ON sz.zone_id=sc.zone_id
						WHERE sc.country_id=".$this->country."
						AND sw.over_kg<$weight
						ORDER BY sw.over_kg DESC
						LIMIT 1
						";
			//print "$query<br>\n";

			if( $row = $db->get_row($query) ){
				$this->pandp = $row->packaging_value+$row->price;
				//print "got pandp($value) zone(".$_SESSION['shipping_zone'].") q($query) <br>";
				return $value;
			}else{
				return false;
			}	
		}else{
			return 0;
		}
	}
	
	
	public function getCountryZoneList(){
		global $db;
		
		//$query = "SELECT country_id, title, zone_id FROM store_countries";
		$query = "SELECT country_id, title, zone_id FROM store_countries WHERE country_id IN (222,240,241,242,243,223)
					UNION
					SELECT country_id, title, zone_id FROM store_countries";
		if( $data = $db->get_results($query) ){
			return $data;
		}else{
			return false;
		}
	}


	public function getDonationAmounts($amountID=false){
		global $db;
		$query = "SELECT * FROM store_donation_amounts WHERE `status`=1";
		if( isset($amountID) && $amountID>0 ){
			$query .= " AND amount_id=$amountID";
		}
		$query .= " ORDER BY value ASC";
		//print "$query<br>\n";
		if( $data = $db->get_results($query) ){
			return $data;
		}
		return false;
	}

	public function getOrderData( $cartID=false ){
		global $db;
		$this->cartID = (isset($cartID) && $cartID>0) ? $cartID : $this->cartID;
				
		$query = "SELECT * FROM store_orders WHERE order_id='{$this->cartID}' LIMIT 1";
		//print "$query<br>";
		if( $row = $db->get_row($query) ){
			$this->no_swap = $row->no_swap;
			$this->where_seen = $row->where_seen;
			return true;
		}
	}
	
	public function getOrderDonation( $cartID=false ){
		global $db;
		$this->cartID = (isset($cartID) && $cartID>0) ? $cartID : $this->cartID;
				
		$query = "SELECT * FROM store_orders_donations WHERE order_id='{$this->cartID}' LIMIT 1";
		if( $row = $db->get_row($query) ){
			$this->donation['value'] = $row->donation_amount;
			$this->donation['amount_id'] = $row->used_suggested_id;
			$this->donation['frequency'] = $row->frequency;
			$this->donation['gift_aid'] = $row->use_gift_aid;
			$this->donation['donation_message']=$row->donation_message;
			$this->donation['donation_written']=$row->donation_written;
			return true;
		}else{
			$this->donation = array();
			return false;
		}
	}

	public function getOrderEvents( $cartID=false ){
		global $db;
		$this->cartID = (isset($cartID) && $cartID>0) ? $cartID : $this->cartID;
				
		$query = "SELECT e.guid, p.title, e.price, 
			DATE_FORMAT(e.start_date,'%d %M %Y') start_date, 
			DATE_FORMAT(e.end_date,'%d %M %Y') end_date, 
			DATEDIFF(e.end_date,e.start_date) days,
			oe.entry_id, oe.quantity
			FROM store_orders_events oe
			INNER JOIN event_entry ee ON ee.id = oe.entry_id
			LEFT JOIN events e ON e.guid=ee.event_guid
			LEFT JOIN pages p ON p.guid = e.guid
			WHERE oe.order_id='{$this->cartID}'";
		//print "$query<br>\n";
		if( $results = $db->get_results($query) ){
			$this->events = $results;
			return true;
		}
		else $this->events = array();
		return false;
	}


	public function getOrderSponsorships( $cartID=false ){
		global $db;
		$this->cartID = (isset($cartID) && $cartID>0) ? $cartID : $this->cartID;
				
		//$query = "SELECT * FROM store_orders_sponsorships WHERE order_id='{$this->cartID}'";
		/*
		$query = "SELECT os.*, ee.pp_guid, e.title event_title, CONCAT(m.firstname,' ',m.surname) member_name
					FROM store_orders_sponsorships os
					LEFT JOIN event_entry ee ON os.event_id=ee.event_guid
					LEFT JOIN `events` e ON ee.event_guid
					LEFT JOIN members m ON os.member_id=m.member_id
					WHERE os.order_id='{$this->cartID}'
					GROUP BY os.event_id,os.member_id";
					*/
		$query = "SELECT os.*, ee.pp_guid, e.title event_title, CONCAT(m.firstname,' ',m.surname) member_name, ee.grp_title
					FROM store_orders_sponsorships os
					LEFT JOIN `events` e ON e.guid=os.event_id
					LEFT JOIN members m ON os.member_id=m.member_id
					LEFT JOIN event_entry ee ON ee.event_guid=e.guid
					WHERE os.order_id='{$this->cartID}' AND ee.member_id=os.member_id
					GROUP BY os.date_added ASC";
		if( $results = $db->get_results($query) ){
			$this->sponsorships = $results;
			return true;
		}else{
			$this->sponsorships = array();
			return false;
		}
	}




	public function getProductURL( $productID=false ){
		global $db, $site;
		if( isset($productID) ){
			$query = "SELECT IF(sc2.name IS NOT NULL, CONCAT('/',sc2.name,'/',sc.name,'/?product=',sp.name),
						IF(sc.name IS NOT NULL, CONCAT('/',sc.name,'/?product=',sp.name), CONCAT('/?product=',sp.name))) url
						FROM store_categories sc
						LEFT JOIN store_categories_products scp ON sc.cat_id=scp.cat_id
						LEFT OUTER JOIN store_categories sc2 ON sc.parent_id=sc2.cat_id
						LEFT JOIN store_products sp ON scp.product_id=sp.product_id
						WHERE scp.product_id=$productID 
						AND sp.msv = ".($site->id+0)."
						LIMIT 1";
			if( $data = $db->get_var($query) ){
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	
	public function eventExists($eventID=false, $memberID=false){
		global $db;
		if( isset($eventID) && $eventID ){
			/*$query = "SELECT cutoff_date, end_date, (SELECT firstname FROM members WHERE member_id='$memberID') member_exists 
						FROM `events` WHERE guid='$eventID' LIMIT 1";*/
			$query = "SELECT e.cutoff_date, e.end_date, IF(ee.grp_title>'',ee.grp_title,m.firstname) member_exists
						FROM `events` e
						LEFT JOIN event_entry ee ON e.guid=ee.event_guid
						LEFT JOIN members m ON ee.member_id=m.member_id
						WHERE ee.event_guid='$eventID' AND ee.member_id='$memberID'
						LIMIT 1";
						//echo $query .'<br />';
			if( $data = $db->get_row($query) ){
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	// This function is a bit of a pain in the ass.
	// Really need a reliable currency XML source and a system that does not 
	// freeze when the URL is not available.
	public function drawCurrencyConversion($price=false,$show='full') {
		
		$feed = "http://themoneyconverter.com/rss-feed/GBP/rss.xml";
		$cacheFile = $_SERVER['DOCUMENT_ROOT'] .'/cache/tmc-currencies.xml';
		$cacheBackup = $_SERVER['DOCUMENT_ROOT'] .'/cache/tmc-currencies.bak.xml';
		$cacheDate = mktime(date('h')-0,date('i'),date('s'),date('m'),date('d'),date('y'));
		
		// If the file is out of date then back it up.
		// We backup in case we fail to get a new version
		if(file_exists($cacheFile) ){
			//print "File(".filemtime($cacheFile).") < $cacheDate <br>\n";
			if(filemtime($cacheFile)<$cacheDate ){
				//print "Backup the cached version<br>\n";
				if (file_exists($cacheBackup)) unlink($cacheBackup);
				rename($cacheFile, $cacheBackup);
			}			
		}
		//else "<!-- print cache does not exist -->\n";
		
		// Try to create a new currenty cache file
		if (!file_exists($cacheFile)) {
			//print "<!-- Currency cache does not exist($cacheFile) -->\n";		
			//print "<!-- load($feed) -->\n";
			if( $feedXml = simplexml_load_file($feed) ){
				//print "<!-- FEED(".print_r($feedXml, true).") -->\n";
				$currencies = array("EUR", "USD", "JPY");
				foreach ($feedXml->channel->item as $curr){
					if (in_array(substr($curr->title, 0, 3), $currencies)) {
						if (preg_match("/^1 (.*) = (.*?) (.*)$/", $curr->description, $reg)) {
							//print "<!-- got reg(".print_r($reg, true).") -->\n";
							$html .= '<p style="text-align:right;margin:0;">&pound;'.$price.' = <strong>'.number_format($price*$reg[2], 2, ".", "").'</strong> '.$reg[3].'</p>';
						}
						//else print "<!-- ".$curr->title." no match in (".$curr->description.") -->\n";
					}
				}
				if ($html) {
					//print "<!-- create file(
					file_put_contents($cacheFile, $html);
				}
			}
			else $html = file_get_contents($cacheBackup);
		}
		else $html = file_get_contents($cacheFile);
		return $html;
	}
	
	// Currency source dried up :o(
	public function _drawCurrencyConversion($price=false,$show='full') {
	
		if( isset($price) && $price ){
		
			$currencies = array('EUR'=>array('title'=>'Euro','symbol'=>'&euro;'),
								'USD'=>array('title'=>'US Dollar','symbol'=>'$'),
								/*'CAD'=>array('title'=>'Canadian Dollar','symbol'=>'$'),*/
								'JPY'=>array('title'=>'Japanese Yen','symbol'=>'&yen;'),
								/*'AUD'=>array('title'=>'Australian Dollar','symbol'=>'$')*/
							);
			$tmp = array();
			//$src = 'http://currencysource.ez-cdn.com/GBP.xml';
			$src = 'http://www.currencysource.com/rss/GBP.xml';
			$cacheFile = $_SERVER['DOCUMENT_ROOT'] .'/cache/currencies.xml';
			$cacheBackup = $_SERVER['DOCUMENT_ROOT'] .'/cache/currencies.bak.xml';
			//$cacheFile = 'c:\\Webserver\\xampp\\htdocs\\magdev\\includes\\cache\\currencies.xml'; // for use on local Windows set-up
			$cacheDate = mktime(date('h')-0,date('i'),date('s'),date('m'),date('d'),date('y'));
			
			// If the file is out of date then back it up.
			// We backup in case we fail to get a new version
			if(file_exists($cacheFile) ){
				//print "File(".filemtime($cacheFile).") < $cacheDate <br>\n";
				if(filemtime($cacheFile)<$cacheDate ){
					//print "Backup the cached version<br>\n";
					if (file_exists($cacheBackup)) unlink($cacheBackup);
					rename($cacheFile, $cacheBackup);
				}			
			}
			
			// Try to create a new currenty cache file
			if (!file_exists($cacheFile)) {
			
				$doc = new DOMDocument('1.0');
				/*
				print "Try to load($src)<br>\n"; exit();
				if (@$doc->load($src)) {
					// we want a nice output
					$doc->formatOutput = true;
					if( $doc->save($cacheFile) ){
						;//echo 'SAVED XML';
					}
					//else echo 'Could not save XML on line '. __LINE__ .'<br />';
				}
				//else echo "<!-- Failed to load XML[".$src."] -->\n";
				*/
			}
			

			// Just read the current data
			if(file_exists($cacheFile)) $currencyFile = $cacheFile;
			else if (file_exists($cacheBackup)) $currencyFile = $cacheBackup;
			
			if ($currencyFile) { 
				$currencyFileDate = date("D j M y H:i", filemtime($currencyFile));
				if( $feedXml = simplexml_load_file($currencyFile) ){
					$i=1;
					$tmpC = array();
					foreach ($feedXml->channel->item as $article){
						$cTxt = trim(substr($article->title,7,4));
						if( array_key_exists($cTxt,$currencies) ){
							$cValue = substr($article->title,13);
							$cValue = str_replace(')','',$cValue);
							$tmpPubDate = $article->pubDate;
							$cPubDate = new DateTime($tmpPubDate);
							if( $show=='full' ){
								$tmp[] = $currencies[$cTxt]['title'] .' = '. $currencies[$cTxt]['symbol'] . number_format($price * $cValue,2);
							}else if( $show=='min' ){
								//$tmp[] = '<span title="'. $currencies[$cTxt]['title'] .'">'. $currencies[$cTxt]['symbol'] . number_format($price * $cValue,2) .' <span class="curAbbr">('. $cTxt .')</span></span>';
								$tmp[] = '<span title="'. $currencies[$cTxt]['title'] .'">'. $currencies[$cTxt]['symbol'] . number_format($price * $cValue,2) .'</span>';
							}
							$tmpC[] = array(
								'title'=>$currencies[$cTxt]['title'],
								'symbol'=>$currencies[$cTxt]['symbol'],
								'value'=>number_format($price * $cValue,2),
								'date'=>$currencyFileDate
								);
						}
					}
					return $tmpC;
				}
				else return false; 
			}
			else return false;
		}
		else return false;
		return false;
	}



	
	public function addOrderAddress( $type=false, $orderID=false, $addressID=false, $memberID=false ){
		global $db;
		//print "aOA($type, $orderID, $addressID, $memberID)<br>\n";

		if( $db->query("SELECT 1 FROM store_address_book WHERE addr_id='$addressID' AND member_id='$memberID'") ){
			if( in_array($type,$this->addressTypes) ){
				switch($type){
					case 'delivery':
						$col = 'shipping';
						break;
					case 'billing':
						$col = $type;
						break;
				}
				$query = "UPDATE store_orders SET {$col}_addr_id='$addressID', member_id='$memberID' WHERE order_id='$orderID'";
				//echo $query .'<br />';
				//exit();
				$db->query($query);
				if( $db->rows_affected>=0 ){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function getAddress( $type=false, $orderID=false ){
		global $db;
		//print "gA($type, $orderID)<br>\n";
		if( $orderID && $type){
			if( in_array($type,$this->addressTypes) ){
				switch($type){
					case 'delivery':
						$col = 'shipping';
						break;
					case 'billing':
						$col = $type;
						break;
				}
				$query = "SELECT sab.*, sc.title country, sc.zone_id, c.num country_iso
							FROM store_address_book sab
							LEFT JOIN store_orders so ON so.{$col}_addr_id=sab.addr_id
							LEFT JOIN store_countries sc ON sab.country_id=sc.country_id
							LEFT JOIN country c ON sc.ISO3=c.code3
							WHERE so.order_id='$orderID'";
				//print "q($query)<br>\n";
				if( $data = $db->get_row($query) ){
					return $data;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	

	public function addOrderNote( $orderID=false, $note=false ){
		global $db;
		
		if( $orderID && $note>'' ){
			$query = "UPDATE store_orders SET order_note='$note' WHERE order_id='$orderID'";
			$db->query($query);
			if( $db->afected_rows>=0 ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	public function cartToOrder( $orderID=false ){
		global $db;
		
		if( $orderID ){
			$query = "UPDATE store_orders SET date_order_started=NOW(), `status`=1 WHERE order_id='$orderID'";
			$db->query($query);
			if( $db->affected_rows>=1 ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}





}


?>