<?php

class preference{

	var $preference_id;
	var $preference_title;
	var $preference_description;
	var $deleted=0;

	function preference($preference_id = 0){
		global $db, $site;
		
		if($preference_id>0){

			// Pull subscriber data out
			$query = "SELECT preference_id, preference_title, preference_description, deleted
				FROM newsletter_preferences 
				WHERE preference_id = $preference_id 
				AND site_id = ".$site->id;
			//print "$query<br>"; 
			if($db->query($query)){

				$s = $db->get_row(null);
				$this->preference_id = stripslashes($s->preference_id); 
				$this->preference_title = stripslashes($s->preference_title);
				$this->preference_description = stripslashes($s->preference_description);
				$this->deleted = $s->deleted;
			}
		}
	}
	

	function set($attribute, $value) {
		if ($this->$attribute != $value) {
			$this->$attribute=$value;
			$this->updated=true;
		}
	}

	function isValid($checkid=true){
		if (!$this->preference_title) return false;
		if ($checkid) if(!$this->preference_id) return false;
		return true;
	}

	function createNew(){
		global $db, $site;
		if ($this->preference_title && $this->preference_description) {
			$query = "INSERT INTO newsletter_preferences (preference_title, preference_description, site_id)
				VALUES ('" . $db->escape($this->preference_title) . "', 
					'" . $db->escape($this->preference_description) ."', 
					".$site->id.")";
			//print "$query<br>\n";
			$this->preference_id = $db->insert_id;
			return $db->query($query);
		}
		return false;
	}


	function update(){
		global $db;
		$query = "UPDATE newsletter_preferences SET 
			preference_title = '" . $db->escape($this->preference_title) . "',
			preference_description = '" . $db->escape($this->preference_description) . "' 
			WHERE preference_id=".$this->preference_id;
		//print "$query<br>\n";
		return $db->query($query);

	}
	
	function delete($act='delete') {
		global $db;
		if ($act == "reallydelete") {
			$query = "DELETE FROM newsletter_preferences WHERE preference_id='".$this->preference_id."'";
			return $db->query($query);
		}
		else return $this->disable();
	}
	
	function disable() {
		global $db;
		$query = "UPDATE newsletter_preferences SET deleted=1 WHERE preference_id=".$_GET['preference_id'];
		return $db->query($query);
	}

	function re_enable() {
		global $db;
		$query = "UPDATE newsletter_preferences SET deleted=0 WHERE preference_id=".$this->preference_id;
		//print "$query<br>\n";
		return $db->query($query);
	}
}