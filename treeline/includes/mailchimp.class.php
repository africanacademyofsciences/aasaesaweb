<?php

class mc {

	private $apiKey;
	private $center, $base;
	private $sublistID;
	
	private $sublistname = "Adeso Newsletter Subscriber List";
	
	public $debug = false;
	public $err = array();
	
	public function mc($debug = false) {
		//print "Init MC\n";	
		$this->setDebug($debug);
		$this->apiKey = 'c437a094a4717f3582693dbd3ff2d5ad-us4';
		$this->center = substr($this->apiKey,strpos($this->apiKey,'-')+1);
		$this->base = 'https://'. $this->center.'.api.mailchimp.com/3.0/';
		$this->getLists();
	}
	
	public function setDebug($debug) {
		$this->debug = $debug;
	}
	
	private function getData($url, $type="GET", $json='') {
		$url = $this->base.$url;
		//print "gD($url, $type, $json)\n";
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
			if ($this->debug) print "Lists(".print_r($lists, 1).")<br>\n";
			foreach ($lists as $list) {
				if ($this->debug) print "Got list(".print_r($list, 1).")--------------------<br><br>\n\n";	
				if ($this->debug) print "List name(".$list->name.")<br>\n";
			}
		}
		if ($this->ch) curl_close($this->ch);
	}
	
	public function syncMailchimp($data) {
		
		$listId = $this->sublistID;
		$memberId = md5(strtolower($data['email']));
		$url = 'lists/' . $listId . '/members/' . $memberId;
		$json = json_encode(array(
			'email_address' => $data['email'],
			'status'        => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
			'merge_fields'  => array(
				'FNAME'     => $data['firstname'],
				'LNAME'     => $data['lastname']
			)
		));
	
		$result = $this->getData($url, "PUT", $json);
		//print "\n\nAResult(".print_r($result, 1).") <br>\n\n";
		//print "R(".json_decode((string)$result).")<br>\n";		
		$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($this->ch) curl_close($this->ch);

		if ($httpCode != 200) {
			//print "Got code($httpCode) \n";
			$this->setError($result);
			// Save the error somehow
		}

		return $httpCode;
	}	

	private function setError($s) {
		$p = explode(",", $s);
		//print "Got p(".print_r($p, 1).")\n";
		foreach ($p as $item) {
			//print "Got item: $item\n";
			$item = $this->strip($item, "{", 1);
			$item = $this->strip($item, "}", 2);
			$items = explode(":", $item);
			$this->err[$this->strip($items[0])]=$this->strip($items[1]);
		}
		//print "Got err(".print_r($this->err, 1).")<br>\n";
	}
	
	private function strip($s, $c='"', $p=0) {
		//print "s($s, $c, $p)\n";
		if ($p!=2 && substr($s, 0, 1)==$c) $s = substr($s, 1);
		if ($p!=1 && substr($s, -1, 1)==$c) $s = substr($s, 0, -1);
		return $s;
	}
}



?>