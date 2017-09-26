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
		//echo '<p style="color:#fff">';
		//echo '<pre>'.print_r($_COOKIE,true).'</pre>';
		//echo 'cartID: '. $cartID .'<br />';
	
		if( !isset($cartID) || !$cartID ){
			//echo 'NO cartID!<br />';
			$this->cartID = uniqid();
			//echo 'create new one: '. $this->cartID .'<br />';
			$this->create($this->cartID);
		}else{
			$this->cartID = $cartID;
			//echo 'seems like we have a cartID: '. $cartID .'<br />';
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
	
	public function create( $cartID=false ){
		global $db;
		//echo 'CREATE: '. $cartID .'<br />';
		if( isset($cartID) ){
			$query = "INSERT INTO store_orders (order_id,date_cart_started) VALUES ('$cartID',NOW())";
			//echo 'query: '. $query .'<br />';
			if( $db->query($query) ){
				//echo 'CREATED NEW order<br />';
				return true;
			}else{
				return false;
			}			
		}else{
			return false;
		}
	}
	
	public function add( $id=false, $quantity=1, $type=false ){
		global $db;
		
		if( isset($id) ){
			if( !$type ){
				$query = "REPLACE INTO store_orders_details (order_id, item_id, quantity, date_added) 
							VALUES ('{$this->cartID}',$id,$quantity,NOW())";
			}else if( $type=='event' ){
				$query = "REPLACE INTO store_orders_events (order_id, event_id,date_added) 
							VALUES ('{$this->cartID}','$id',NOW())";			
			}else if( $type=='sponsorship' ){
				$eventID = $id[0];
				$memberID = $id[1];
				$query = "REPLACE INTO store_orders_sponsorships (order_id, event_id, member_id, amount, date_added) 
							VALUES ('{$this->cartID}','$eventID',$memberID,$quantity,NOW())";			
			}
			//echo '<p style="color:#fff">query: '. $query .'</p>';
			if( $db->query($query) ){
				$this->loadShoppingBasket();
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}




	public function loadShoppingBasket( $cartID=false ){
		global $db;
		
		$cartID = (isset($cartID) && $cartID>0) ? $cartID : $this->cartID;
		$this->cartID = $cartID;
		if( $cartID ){
			// get the donation info first - we can use this without anything in the basket
			$this->getOrderData();
			$this->getOrderDonation();
			$this->getOrderEvents();
			$this->getOrderSponsorships();
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
			}else{
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

		// Just update order info if we have been sent any
		if (is_array($info)) {
			foreach ($info as $k=>$v) $set.="$k='$v',";
			$query="update store_orders set ".substr($set, 0, -1)." where order_id='".$this->cartID."'";
			//print "$query<br>";
			$db->query($query);
		}
		
		if( (is_array($basket) && count($basket)>=0) || (is_array($donation) && count($donation)>=0)
				 || (is_array($events) && count($events)>=0) || (is_array($sponsorships) && count($sponsorships)>=0) ){
			// handle basket first...
			// the array should have arrays of item_id and quantity for us to work with...
			$update=false;
			if( is_array($basket) && count($basket)>0 ){
				// 1) Delete the existing basket
				$query = "DELETE FROM store_orders_details WHERE order_id='{$this->cartID}'";
	
				$db->query($query);
				// 2) Loop through basket and store it...
				$query = "INSERT INTO store_orders_details (order_id, item_id, quantity, date_added) VALUES ";
				$i=0;
				foreach($basket as $item){
					$query .= ($i>0 ? ',' : '');
					$query .= "('{$this->cartID}', {$item['item_id']}, {$item['quantity']}, NOW())";
					$i++;
				}

				$db->query($query);
				$update=true;
			}else if( is_array($basket) && count($basket)==0 ){
				$query = "DELETE FROM store_orders_details WHERE order_id='{$this->cartID}'";
				//echo $query .'<br />';
				if( $db->query($query) ){
					//echo 'reload basket<br />';
					$update=true;
				}
			}
			
			// 3) process any donation info we have been given...
			if( is_array($donation) && count($donation)>0 && in_array($donation['type'],array('amount_id','value')) ){
				// Delete existing donation info...
				$query = "DELETE FROM store_orders_donations WHERE order_id='{$this->cartID}'";
				$db->query($query);
				// if we have a specific value
				if( $donation['type']=='value' && $donation['value']>0 ){
					$query = "INSERT INTO store_orders_donations (order_id,donation_amount, used_suggested_id, frequency, use_gift_aid)
								VALUES ('{$this->cartID}',". $donation['value'] .", 0, ". $donation['frequency'] .", ". $donation['gift_aid'] .")";
				// if we're using an amount_id - this allows us to track if those specific amounts are being used 
				// instead of it being co-incidence
				}else if( $donation['type']=='amount_id' ){
					// What's the value associate with that amount_id
					$query = "SELECT `value` FROM store_donation_amounts WHERE amount_id=". $donation['value'];
					if( $value = $db->get_var($query) ){
					$query = "INSERT INTO store_orders_donations (order_id,donation_amount, used_suggested_id, frequency, use_gift_aid)
								VALUES ('{$this->cartID}',". $value .", ". $donation['value'] .", ". $donation['frequency'] .", ". $donation['gift_aid'] .")";				
					}
				}
				$db->query($query);
				if ($donation['donation_message'] || $donation['donation_written']) {
					$query="update store_orders_donations set donation_message='".htmlentities($donation['donation_message'],ENT_QUOTES)."', donation_written='".(($donation['donation_written']==1)?1:0)."' where order_id='".$this->cartID."'";
					//print "$query<br>";
					$db->query($query);
				}
				$update=true;
			}else if( is_array($donation) && count($donation)==0 ){
				$query = "DELETE FROM store_orders_donations WHERE order_id='{$this->cartID}'";
				if( $db->query($query) ){
					$update = true;
				}
			}
			
			// 4) Do we have any events?
			if( is_array($events) && count($events)>0 ){
				// Delete existing events...
				foreach($events as $key => $item){
					$query = "DELETE FROM store_orders_events WHERE order_id='{$this->cartID}' AND event_id='{$key}'";
					//echo $query .'<br />';
					$db->query($query);
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
			}else if( is_array($sponsorships) && count($sponsorships)==0 ){
				$query = "DELETE FROM store_orders_sponsorships WHERE order_id='{$this->cartID}'";
				//echo $query .'<br />';
				if( $db->query($query) ){
					//echo 'reload basket<br />';
					$update=true;
				}
			}


			if( $update ){
				$this->loadShoppingBasket();
				return true;
			}else{
				return false;
			}
			
		}else{
		
			if( is_array($basket) && count($basket)==0 ){
				$query = "DELETE FROM store_orders_details WHERE order_id='{$this->cartID}'";
				//echo $query .'<br />';
				if( $db->query($query) ){
					//echo 'reload basket<br />';
					$update=true;
				}
			}

			if( is_array($donation) && count($donation)==0 ){
				$query = "DELETE FROM store_orders_donations WHERE order_id='{$this->cartID}'";
				//echo $query .'<br />';
				if( $db->query($query) ){
					//echo 'reload basket<br />';
					$update=true;
				}
			}		

			if( is_array($events) && count($events)==0 ){
				$query = "DELETE FROM store_orders_events WHERE order_id='{$this->cartID}'";
				//echo $query .'<br />';
				if( $db->query($query) ){
					//echo 'reload basket<br />';
					$update=true;
				}
			}	

			if( is_array($sponsorships) && count($sponsorships)==0 ){
				$query = "DELETE FROM store_orders_sponsorships WHERE order_id='{$this->cartID}'";
				//echo $query .'<br />';
				if( $db->query($query) ){
					//echo 'reload basket<br />';
					$update=true;
				}
			}
		
			if( $update ){
				$this->loadShoppingBasket();
				return true;
			}else{
				return false;
			}
		}
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
				$total += $item->price;
				$this->totals['events'] += $item->price;
			}
		}else{
			unset($this->totals['events']);
		}
		// sponsorships?
		if( is_array($this->sponsorships) ){
			$this->totals['sponsorships'] = 0;
			foreach($this->sponsorships as $item){
				$total += $item->amount;
				$this->totals['sponsorships'] += $item->amount;
			}
		}else{
			unset($this->totals['sponsorships']);
		}
		
		return $total;
	}



	public function getPostageAndPacking(){
		global $db;
		if( isset($this->total) ){
			$weight = 0;
			foreach($this->basket as $item){
				$weight += $item->weight;
			}
			$query = "SELECT (sz.packaging_value + IF($weight<max_weight_used,($weight*sz.postage_per_kilo),(max_weight_used*sz.postage_per_kilo)))
						FROM store_shipping_zones sz
						LEFT JOIN store_countries sc ON sz.zone_id=sc.zone_id
						WHERE sc.country_id={$this->country}";
			if( $value = $db->get_var($query) ){
				$this->pandp = $value;
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
		if( $data = $db->get_results($query) ){
			return $data;
		}else{
			return false;
		}	
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
				
		$query = "SELECT e.guid, e.title, e.fee price, DATE_FORMAT(e.start_date,'%d %M %Y') start_date, 
					DATE_FORMAT(e.end_date,'%d %M %Y') end_date, DATEDIFF(e.end_date,e.start_date) days
					FROM store_orders_events oe
					LEFT JOIN `events` e ON oe.event_id=e.guid
					WHERE oe.order_id='{$this->cartID}'";
		if( $results = $db->get_results($query) ){
			$this->events = $results;
			return true;
		}else{
			$this->events = array();
			return false;
		}
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
		global $db;
		if( isset($productID) ){
			$query = "SELECT IF(sc2.name IS NOT NULL, CONCAT('/',sc2.name,'/',sc.name,'/?product=',sp.name),
						IF(sc.name IS NOT NULL, CONCAT('/',sc.name,'/?product=',sp.name), CONCAT('/?product=',sp.name))) url
						FROM store_categories sc
						LEFT JOIN store_categories_products scp ON sc.cat_id=scp.cat_id
						LEFT OUTER JOIN store_categories sc2 ON sc.parent_id=sc2.cat_id
						LEFT JOIN store_products sp ON scp.product_id=sp.product_id
						WHERE scp.product_id=$productID LIMIT 1";
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



	public function drawCurrencyConversion($price=false,$show='full') {
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
			$cacheFile = $_SERVER['DOCUMENT_ROOT'] .'/includes/cache/currencies.xml';
			//$cacheFile = 'c:\\Webserver\\xampp\\htdocs\\magdev\\includes\\cache\\currencies.xml'; // for use on local Windows set-up
			$cacheDate = mktime(date('h')-1,date('i'),date('s'),date('m'),date('d'),date('y'));
			if( !file_exists($cacheFile) ){
				//file_put_contents($cacheFile,simplexml_load_file('http://currencysource.ez-cdn.com/GBP.xml'));
				$doc = new DOMDocument('1.0');
				$doc->load($src);
				// we want a nice output
				$doc->formatOutput = true;
				if( $doc->save($cacheFile) ){
					//echo 'SAVED XML';
				}else{
					//echo 'Could not save XML on line '. __LINE__ .'<br />';
				}
			}else{
				if( filemtime($cacheFile)<$cacheDate ){
					unlink($cacheFile);
					//file_put_contents($cacheFile,simplexml_load_file('http://currencysource.ez-cdn.com/GBP.xml'));
					$doc = new DOMDocument('1.0');
					$doc->load($src);
					// we want a nice output
					$doc->formatOutput = true;
					if( $doc->save($cacheFile) ){
						//echo 'SAVED XML';
					}else{
						//echo 'Could not save XML on line '. __LINE__;
					}
				}
			}

			if( $feedXml = simplexml_load_file($cacheFile) ){
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
						$tmpC[] = array('title'=>$currencies[$cTxt]['title'],
										'symbol'=>$currencies[$cTxt]['symbol'],
										'value'=>number_format($price * $cValue,2)
										);
					}
				} 
				
				//return $tmp;
				return $tmpC;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}



	
	public function addOrderAddress( $type=false, $orderID=false, $addressID=false, $memberID=false ){
		global $db;
		//echo "SELECT 1 FROM store_address_book WHERE addr_id='$addressID' AND member_id='$memberID'";
		//exit();
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
							LEFT JOIN store_orders so ON so.billing_addr_id=sab.addr_id
							LEFT JOIN store_countries sc ON sab.country_id=sc.country_id
							LEFT JOIN country c ON sc.ISO3=c.code3
							WHERE so.order_id='$orderID'";

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
