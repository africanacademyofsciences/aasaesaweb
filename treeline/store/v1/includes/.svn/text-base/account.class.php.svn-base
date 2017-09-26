<?
/************************************************************
Account object for Treeline

Author: Dan Donald
Started: 26-03-08
Description:
	Validate and load up user data

************************************************************/

class Account {

	public $memberID;
	public $properties;
	public $addresses;
	public $nameTitles = array('Mr','Mrs','Miss','Ms','Dr','Rev');
	

	public function __construct($memberID=false){
		if( isset($memberID) && $memberID>0 ){
			$this->load($memberID);
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
	
	

	public function validate( $email=false, $password=false ){
		global $db;
		if( (isset($email) && $email) && (isset($password) && $password) ){
			//echo 'VALIDATE!<br />';
			$this->checkValidEmail($email);
			$email = $db->escape($email);
			$password = $db->escape($password);
			$query = "SELECT member_id FROM members WHERE email='$email' AND password='$password' LIMIT 1";
			//echo $query .'<br />';
			if( $memberID = $db->get_var($query) ){
				$this->memberID = $memberID;
				$this->load();
				return true;
			}
		}else{
			return false;
		}
	}

	// VALIDATE EMAIL ADDRESS	
	private function checkValidEmail($email){
		//echo 'email OK on line: '. __LINE__ .'<br />';
		//copyright - http://www.ilovejackdaniels.com/php/email-address-validation
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		//echo 'email OK on line: '. __LINE__ .'<br />';
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!ereg("^(([A-Za-z0-9!$%&'*+/=?^_`{|}~-][A-Za-z0-9!$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
				return false;
			}
		}
		//echo 'email OK on line: '. __LINE__ .'<br />';
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				return false; // Not enough parts to domain
			}
			//echo 'email OK on line: '. __LINE__ .'<br />';
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					return false;
				}
			}
			//echo 'email OK on line: '. __LINE__ .'<br />';
		}
		return true;
	}



	public function load( $memberID=false ){
		global $db;
		
		$memberID = (isset($memberID) && $memberID>0) ? $memberID : $this->memberID;
		$this->memberID = $memberID;
		if( $memberID ){
			$query = "SELECT * FROM members WHERE member_id={$memberID} LIMIT 1";
			
			if( $data = $db->get_row($query) ){
				$this->properties = $data;
				//echo '<pre>'. print_r($data,true) .'</pre>';
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	
	
	
	public function create( $properties=false ){
		global $db;
		
		if( $properties && is_object($properties) ){
			$this->properties = $properties;
			$query = "INSERT INTO members (title, firstname, surname, status, date_added, email, password, terms, contact) 
						VALUES ('".$properties->cust_title."', '". $db->escape($properties->firstname) ."', '". $db->escape($properties->surname) ."',
						1, NOW(), '". $db->escape($properties->email) ."', '". $db->escape($properties->password) ."',1,1)";
			//echo $query .'<br />';
			if( $db->query($query) ){
				$this->properties->member_id = $db->insert_id;
				return $db->insert_id;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	public function memberExists($email=false, $load=false){
		global $db;
		if( $email ){
			if( $memberID = $db->get_var("SELECT member_id FROM members WHERE email='". $db->escape($email) ."'") ){
				if( $load ){
					$this->load($memberID);
				}
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	public function getDeliveryAddresses( $memberID=false ){
		global $db;
		
		$memberID = (isset($memberID) && $memberID>0) ? $memberID : $this->memberID;
		$this->memberID = $memberID;
		if( $memberID ){
			$query = "SELECT * FROM store_address_book WHERE member_id=$memberID";
			if( $data = $db->get_results($query) ){
				$this->addresses = $data;
				return $data;
			}else{
				return false;
			}			
		}else{
			return false;
		}		
	}
	
	
	public function createAddress( $memberID=false, $address=false ){
		global $db;
		
		if( $memberID && is_array($address) ){
			if( $addrID = $this->addressExists($memberID, $address['house'], $address['post_code']) ){
				return $addrID;
			}else{
				$query = "REPLACE INTO store_address_book (member_id, ";
				$query .= join(', ',array_keys($address));
				$query .= ") VALUES (". $memberID .', ';
				$total = count($address);
				$i=1;
				foreach($address as $item){
					$item = (is_string($item) ? '"'. $db->escape($item) .'"' : $item);
					$query .= trim($item) . ($i==$total ? '' : ', ' );
					$i++;
				}
				$query .= ")";
				//echo $query .'<br />';
				
				if( $db->query($query) ){
					return $db->insert_id;
				}else{
					return false;
				}				
			}
							
		}else{
			return false;
		}
	}


	public function addressExists( $memberID=false, $house=false, $postcode=false ){
		global $db;
		if( $memberID && $house && $postcode ){
			$query = "SELECT addr_id FROM store_address_book WHERE house='$house' AND post_code='$postcode' AND member_id=$memberID LIMIT 1";
			//echo $query .'<br />';
			if( $id = $db->get_var($query) ){
				return $id;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}





}


?>