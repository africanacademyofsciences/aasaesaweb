<?php

class subscriber{

	public $id;
	public $email;
	public $fullname;
	public $first, $firstname;
	public $surname;
	var $info;
	
	var $organisation;
	var $jobtitle;
	var $postcode;
	var $opted_in;

	public $add_paid_date = false;
	public $paid_date = "2015-01-01";

	public $validateEmail = true;
	var $date_changed;
	var $updated=false;

	public $errmsg = array();
	
	function subscriber($id = null){

		$this->id = null;
		$this->email = null;
		$this->firstname = null;
		$this->surname = null;
		$this->fullname = null;
		$this->organisation = null;
		$this->jobtitle = null;
		$this->postcode = null;
		$this->date_changed = null;
		$this->opted_in = null;

		if($id){

			global $db;

			// Pull subscriber data out
			$strSQL = "SELECT member_id, email, firstname, surname  
				FROM members WHERE member_id = '$id'
				";
			//print "$strSQL<br />";
			if ($s = $db->get_row($strSQL)) {

				$this->id = stripslashes($s->member_id); 
				$this->email = stripslashes($s->email);
				$this->firstname = $s->firstname;
				$this->surname=$s->surname;
				$this->fullname = $this->firstname." ".$this->surname;
			}
			///$this->printObject();
		}
	}

	function loadByEmail($email) {
		global $db;
		
		// Pull subscriber data out
		$query = "SELECT member_id, email, firstname, surname  
			FROM members WHERE email='$email' LIMIT 1";
		//print "$query <br>\n";
		if ($row = $db->get_row($query)) {

			$this->id = $row->member_id; 
			$this->email = stripslashes($row->email);
			$this->firstname = $row->firstname;
			$this->surname=$row->surname;
			$this->fullname = $this->firstname." ".$this->surname;
			return true;
		}
		else return false;
	}
	
		
	function set($attribute, $value) {
	
		if ($this->$attribute != $value) {
			$this->$attribute=$value;
			$this->updated=true;
		}
	}

	function setName($name) {
		
		$this->fullname=$name;
		$this->firstname=$this->surname='';
		$a=explode(" ", $name);
		if (count($a)>1) {
			foreach ($a as $tmpname) {
				if (!$this->firstname) $this->firstname=$tmpname;
				else $this->surname.=$tmpname." ";
			}
		}
		else {
			$this->firstname = $name;
		}
		if ($this->surname) $this->surname=substr($this->surname, 0, -1);
		$this->firstname=removeAccents($this->firstname);
		$this->surname=removeAccents($this->surname);
		//print "created ".$this->firstname."+".$this->surname." from($name)<br>";
	}
	
	function isValid($checkid=true){
	
		// Should check email address is valid...
		//print "iV($checkid) validateEmail(".$this->validateEmail.")<br>";
		//print "e(".$this->email.") s(".$this->surname.") f(".$this->firstname.") id(".$this->id.")<br>\n";
		if (strlen($this->firstname." ".$this->surname)==1) { 
			$this->errmsg[] = "You have not entered your name";
		}

		if (!$this->email) { 
			$this->errmsg[] = "No email address entered";
		}
		else if ($this->validateEmail && !$this->validateEmail($this->email)) { 
			$this->errmsg[] = "Your email address is not valid";
		}
		
		//if (!$this->organisation) $this->errmsg[] = "You must enter your organisation";
		//if (!$this->country) $this->errmsg[] = "You must select a country";

		return !count($this->errmsg);
	}

	function validateEmail($email)
	{
		// Allow switchng off real email check for fellow import process.
		$check_real_email = false;
		$debug = false;
		$ev = is_email(trim($email), $check_real_email, $debug);
		//print "vE(".$this->validateEmail.") (".trim($email).") valid($ev)<br>\n";
		//print "<!-- vE(".trim($email).") valid($ev) -->\n";
		return $ev;
	}

	function randomPassword() {
		$alpha = "abcdefghijklmnopqrstuvwxyz012345678901234567890123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		for($i = 0; $i < 6; $i++){
			$password .= $alpha{rand(0, strlen($alpha) - 1)};
		}
		return $password;
	}
	
	function createNew(){

		global $db, $site;		
		
		if (!$this->email) return false;
		if ($this->validateEmail && !$this->validateEmail($this->email)) return false;
		
				
		$query = "INSERT INTO members 
			(
				email, firstname, surname, further_info, password, date_added,
				country, organisation, jobtitle".($this->add_paid_date?', paid_date':'')."
			) 
			VALUES ('". $db->escape($this->email)."', 
			'". $db->escape(htmlentities($this->firstname,ENT_QUOTES,$site->properties['encoding'])). "', 
			'".$db->escape(htmlentities($this->surname,ENT_QUOTES,$site->properties['encoding']))."', 
			'".$db->escape($this->info)."',
			'".$this->randomPassword()."', 
			NOW(),
			".($this->country+0).", 
			'".$this->organisation."', 
			'".$this->jobtitle."'".($this->add_paid_date?" ,'".$this->paid_date."'":'')."
			)";
		//print "$query<br>";
		if($db->query($query)){
			$this->id = $db->insert_id;
			return $this->id;
		}
		return 0;
	}


	function update(){
		global $db;
		$strSQL = "UPDATE members SET email = '" . $db->escape($this->email) . "', 
			firstname = '" . $db->escape($this->firstname) . "',
			surname = '" . $db->escape($this->surname) . "'
			WHERE member_id=".$this->id;
		//print "$strSQL<br>";
		return $db->query($strSQL);

	}
	

	public function printObject() {
	
		print "id = {$this->id}<br>
email = {$this->email}<br>
firstname = {$this->firstname}<br>
surname = {$this->surname}<br>
fullname = {$this->fullname}<br>
organisation = {$this->organisation}<br>
jobtitle = {$this->jobtitle}<br>
postcode = {$this->postcode}<br>
";
 	}

}


?>