<?php

class mc {

	private $apiKey;
	private $center, $base;
	private $sublistID;
	
	private $sublistname = "Adeso Newsletter Subscriber List";
	private $lists = array();
		
	public $debug = false;
	public $err = array();
	public $ea = array();
	public $errmsg = array();
	
	public function mc($debug = false) {
		//print "Init MC\n";	
		$this->setDebug($debug);
		$this->apiKey = '5d7137ade8d1d9978cedad3563515e38-us9';
		$this->center = substr($this->apiKey,strpos($this->apiKey,'-')+1);
		$this->base = 'https://'. $this->center.'.api.mailchimp.com/3.0/';
		$this->getLists();
	}
	
	public function setDebug($debug) {
		$this->debug = $debug;
	}
	
	private function getData($url, $type="GET", $json='') {
		$url = $this->base.$url;
		if ($this->debug>0) print "<!-- gD($url, $type, $json) -->\n";
		$this->ch = curl_init($url);
		curl_setopt($this->ch, CURLOPT_USERPWD, 'user:' . $this->apiKey);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($json) curl_setopt($this->ch, CURLOPT_POSTFIELDS, $json);                                                                                                                 
		$result = curl_exec($this->ch);
		return $result;
	}
	
	private function getLists() {
		$json = json_decode($this->getData("lists"));
		if (is_object($json)) {
			$lists=$json->lists;
			if ($this->debug>3) print "<!-- Lists(".print_r($lists, 1).") -->\n";
			foreach ($lists as $list) {
				if ($this->debug>2) print "<!-- Got list(".print_r($list, 1).")------------------ -->\n\n";	
				if ($this->debug>1) print "<!-- List[".$list->id."] name(".$list->name.") -->\n";
				$this->lists[$list->id]=$list->name;
			}
		}
		if ($this->ch) curl_close($this->ch);
		if ($this->debug>1) print "<!-- Loaded lists(".print_r($this->lists, 1).") -->\n";
	}
	
	
	public function subscribeToList($member_id, $list_title, $subscribe) {
		global $db;
		
		$found_list = false;
		$success=false;
		
		$action = ($subscribe?"":"un")."subscribed";
		if ($this->debug>0)  print "<!-- ".ucfirst($action)." member:$member_id to list $list_title -->\n";
		
		$query = "SELECT firstname, surname, email FROM members WHERE member_id = ".$member_id;
		if ($row=$db->get_row($query)) {
			
			foreach ($this->lists as $mc_list_id => $mc_list_title) {
				// print "<!-- Check if $mc_list_title == $list_title -->\n";
				if ($mc_list_title == $list_title) {
					$found_list = true;
					if ($this->debug>1) print "<!-- List: exists on MC: name(".print_r($row, 1).") -->\n";
					if ($row->email) $data['email'] = $row->email;
					if ($row->firstname) $data['firstname' ] = $row->firstname;
					if ($row->surname) $data['lastname' ] = $row->surname;
	
					if ($this->debug>1) print "<!-- sync($mc_list_id, $action, ".print_r($data, 1).") -->\n";
					$r = $this->sync($mc_list_id, $action, $data);
	
					if ($r!=200) {
						if ($this->debug>0) {
							print "<!-- Mailchimp[$r] sign up fail: ".$this->err['detail']." -->\n";
							print "<!-- ".$this->err['errmsg']." -->\n";
						}
						$this->errmsg[] = $list_title."[$r]: ".$this->err['detail'];
						$this->errmsg[] = $this->err['errmsg'];
					}
					else $success = true;
					break;
				}
			}
		}
		
		
		if (!$success && !$found_list) {
			$this->errmsg[] = "List: $list_title not found on Mailchimp";
			if ($this->debug>0) print "<!-- List: $list_title not found on Mailchip -->\n";
		}
		
		return $success;
	}
	


	private function sync($list_id, $action, $data) {

		$httpcode = 800; 		// Made up HTTP code meaning not attempted;
		
		if ($this->debug>2) print "<!-- sMC($list_id, $action, ".print_r($data, 1).") -->\n";
		
		// STATUS = "subscribed","unsubscribed","cleaned","pending"
		$memberId = md5(strtolower($data['email']));
		$url = 'lists/' . $list_id . '/members/' . $memberId;
		
		$json = json_encode(array(
			'email_address' => $data['email'],
			'status'        => $action, 
			'merge_fields'  => array(
				'FNAME'     => $data['firstname'],
				'LNAME'     => $data['lastname']
			)
		));
		if ($this->debug>1) print "<!-- json data(".print_r($json, 1).") -->\n";
		
		$result = $this->getData($url, "PUT", $json);
		if ($this->debug>2) print "<!-- \n\nResult(".print_r(json_decode($result, true), 1).") -->\n\n";
		//print "R(".json_decode((string)$result).")<br>\n";		
		$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($this->ch) curl_close($this->ch);

		if ($httpCode != 200) {
			if ($this->debug>0) print "<!-- Failed, got code($httpCode) -->\n";
			$this->setError($result);
			// Save the error somehow
		}

		return $httpCode;
	}	

	// $s is JSON 
	// FIND A BETTER WAY TO DECODE IT INTO AN ARRAY !!!!!
	private function setError($s) {

		// Try json_decode
		$this->ea = json_decode($s, true);
		$this->err['type'] = $this->ea['type'];
		$this->err['title'] = $this->ea['title'];
		$this->err['status'] = $this->ea['status'];
		$this->err['detail'] = $this->ea['detail'];
		if (is_array($this->ea['errors'])) {
			foreach ($this->ea['errors'] as $err) {
				$this->err['errmsg'] .= $err['field'].":".$err['message'].", ";
			}
		}
		if ($this->debug>0) print "<!-- Got ea(".print_r($this->ea, 1).") -->\n";
		if ($this->debug>2) print "<!-- Got err(".print_r($this->err, 1).") -->\n";
	}
	
	private function strip($s, $c='"', $p=0) {
		//print "s($s, $c, $p)\n";
		if ($p!=2 && substr($s, 0, 1)==$c) $s = substr($s, 1);
		if ($p!=1 && substr($s, -1, 1)==$c) $s = substr($s, 0, -1);
		return $s;
	}
}



?>