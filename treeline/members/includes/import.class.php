<?php


class Import {
	public $fields = array();
	public $fp;

	public $member_inserts, $member_updates;
	public $gnored, $errors=0;
	
	public $errmsg = array();
	
	public function Import() {
		$this->member_inserts=$this->member->updates=0;
		$this->address_inserts=$this->address_updates=0;
		$this->ignored=$this->errors = 0;
		
		$this->newsletter = new Newsletter();
	}
	
	public function writeData($line_no, $update=false) {
		global $db, $site;
		
		$sql=array();
		
		unset ($this->errmsg);
		$this->errmsg=array();
		
		$member_id = $addr_id = 0;
		
		$postcode = $table = '';
		
		$email = $name = '';
		$member_id = 0;
		
		foreach ($this->fields as $tmp) {
			//$tmp->dump();
			if ($tmp->table) {
				
				$this->newsletter->resetErr();
				
				if (!$table) $table = $tmp->table;
				
				//print "proc field(".$tmp->fieldname.")<br>\n";
				//if ($tmp->fieldname=="social_pension") print "soc pen value(".$tmp->value.")<br>\n";
				$sep = "'";
				$value = $db->escape($tmp->value);
				if ($tmp->numeric) {
					$sep='';
					if (substr($value,-1, 1) =="%") $value=substr($value,0, -1);
					$value+=0;
				}

				$ignore_this_field = ($tmp->comment == "ignore");
				
				if ($table=="members") {	
					if ($tmp->fieldname=="email") $_POST['email'] = $email = trim($value);
					else if ($tmp->fieldname=="name") {
						$_POST['name'] = $name = $value;
						$_POST['fellow']=1;
						$this->newsletter->subscriber->setName($name);
						$sql['members']['update'] .= "firstname='".$this->newsletter->subscriber->surname."', ";	
						$sql['members']['update'] .= "surname='".$this->newsletter->subscriber->firstname."', ";	
						$this->newsletter->subscriber->add_paid_date = true;
					}
					else if ($tmp->fieldname=="email_2") $sql['member_profile']['update'] .= "email2='$value', ";
					else if ($tmp->fieldname=="year_elected") $sql['member_profile']['update'] .= "year_elected=".($value+0).", ";
					else if ($tmp->fieldname=="expertise") $sql['member_profile']['update'] .= "expertise='$value', ";
					else if ($tmp->fieldname=="further_info") {
						if (preg_match("/deceased/", strtolower($tmp->value))) {
							print "Deceased member[$name] - Not imported<br>\n";
							//$this->errmsg[] = "Deceased member[$name] - Not imported";
							$this->ignored++;
							return false;
						}
					}
					else if ($tmp->fieldname=="organisation" || $tmp->fieldname=="address") {
						//print "Set value(".htmlentities($value, ENT_QUOTES, $site->properties['encoding'])." from ($value)<br>\n";
						$value = htmlentities($value, ENT_QUOTES, $site->properties['encoding']);
					}
					else if ($tmp->fieldname=="country") {
						$ignore_this_field=true;
						$query = "SELECT country_id FROM store_countries WHERE title='".$db->escape($tmp->value)."'";
						//print "$query<br>\n";
						$country_id = $db->get_var($query);
						if ($country_id>0) $sql['members']['update'] .= "country=$country_id, ";	
						else $sql['members']['update'] .= "countryname='".$tmp->value."', ";	
					}
					//else if ($tmp->fieldname=="") $ = $value;
				}
				
				if (!$ignore_this_field && $tmp->fieldname!="NONE") {
					// Add to query builder
					$sql[$tmp->table]['update'].=$tmp->fieldname."=".$sep.$value.$sep.", ";
					$sql[$tmp->table]['fields'].=$tmp->fieldname.", ";
					$sql[$tmp->table]['values'].=$sep.$value.$sep.", ";
				}				
			}
		}

		//print "Got sql(".print_r($sql, true).")<br>\n";
		
		if ($table=="members") {

			if ($email) {
				if ($email == "n/a") {
					$_POST['email'] = $email = str_replace(".", "", str_replace(" ", "-", $name))."@aasciences";
					$this->newsletter->subscriber->validateEmail = false;
				}
				$query = "SELECT member_id FROM members WHERE email='$email'";
				//print "$query<br>\n";
				$member_id = $db->get_var($query);
				if ($member_id>0) {
					// Check member is a fellow
					$member_type = $db->get_var("SELECT type_id FROM member_access WHERE member_id = ".$member_id." AND msv=".$site->id);
					if ($member_type != 2) {
						print "Existing member type($member_type)<br>\n";
						$db->query("UPDATE member_access SET type_id = 2 WHERE member_id = $member_id AND msv=".$site->id);
					}
				}
				else {
					//print "Subscribe fellow[$email] [$name]<br>\n";
					$this->member_inserts++;
					if (!$this->newsletter->subscribe()) {
						print "Newsletter subscribe fail<br>\n";
						foreach ($this->newsletter->errmsg as $tmp) {
							$this->errmsg[] = $tmp;
						}
					}
					else $member_id = $this->newsletter->member_id;
				}
			}
	

			if ($member_id) {
				$query = "SELECT ma.id AS access_id, mp.id as profile_id 
					FROM member_access ma 
					LEFT OUTER JOIN member_profile mp ON mp.access_id = ma.id
					WHERE ma.member_id = $member_id
					AND ma.msv = ".($site->id+0)."
					LIMIT 1";
				//print "$query<br>\n";
				if ($row = $db->get_row($query)) {
					$access_id = $row->access_id;
					$profile_id = $row->profile_id;
					if (!$profile_id) {
						$query = "INSERT INTO member_profile (access_id) values ($access_id)";
						//print "$query<br>\n";
						if ($db->query($query)) $profile_id = $db->insert_id;
					}
				}
				//print "Got member m($member_id) a($access_id) p($profile_id)<br>\n";
				
				if ($member_id && $access_id && $profile_id) {
					$update_query = "update members set ".substr($sql[$table]['update'],0,-2)." WHERE member_id = ".$member_id;
					$this->member_updates++;
					print "Update[$line_no] - $update_query<br>\n";
					
					$profile_query = "UPDATE member_profile SET ".substr($sql['member_profile']['update'], 0, -2)." WHERE id=".$profile_id;
					//print "Update profile($profile_query)<br>\n";
				
					$live_run = false;
					$live_run = true;
					if ($live_run) {	
						$db->query($update_query);
						if (!$db->last_error) {
							$db->query($profile_query);
							if (!$db->last_error) ;
							else {
								$this->errmsg[]="Failed to add or update profile data on line $line_no($profile_query) e(".$db->last_error.")";			
								$this->errors++;
							}
						}
						else {
							$this->errmsg[]="Failed to add or update member data on line $line_no($update_query) e(".$db->last_error.")";			
							$this->errors++;
						}
					}
					else $this->errmsg[] = "Dummy run no data modified";
				}
				else $this->errmsg[] = "Failed to locate valid member records m($member_id) a($access_id) p($profile_id)";
			}
			else $this->errmsg[] = "Failed to find or create member record";
		}
		else $this->errmsg[]="Failed to work out which table I need to update";
	}
	
	public function resetErr() {
		global $db;
		unset($db->last_error);
		$this->errmsg = array();
		$this->newsletter->errmsg = array();
		$this->newsletter->subscriber->errmsg = array();
	}

	
	public function openFile($file) {
		$mime = array(
			"text/csv", 
			"text/plain", 
			"application/vnd.ms-excel", 
			"application/octet-stream", 
			"application/octet-string", 
			"application/csv"
			);
		if (in_array($file['type'], $mime)) {
			if ($this->fp = fopen($file['tmp_name'], "rt")) {
				return true;
			}
			else $this->errmsg[] = "Failed to open uploaded file";			
		}
		else $this->errmsg[] = "Mime type ".$file['type']." does not appear to be a CSV file";
		return false;
	}
	
	public function closeFile() {
		fclose($this->fp);
	}
	
}

class Field {
	
	public $name, $value;
	public $table;
	public $fieldname, $comment, $numeric;
	public $errmsg = array();
	
	public function Field($table, $name='') {
		if ($table && $name) $this->loadField($table, $name);
	}
	
	public function loadField($table, $name) {
		global $db, $site;
		//print "lf($name)<br>\n";
		$this->name = trim($name);
		$query = "SELECT * FROM data_import WHERE name='".trim($name)."' AND `table`='$table' LIMIT 1";
		//print "$query<br>\n";
		if ($row = $db->get_row($query)) {
			$this->table = $row->table;
			$this->fieldname = $row->field;
			$this->comment = $row->comment;
			$this->numeric = $row->numeric;
		}
		else {
			$this->table = "none";
			$this->fieldname = "blank";
			$this->comment = '';
			$this->numeric = 0;
			$this->errmsg[] = "Field ($name) found but not processed";
		}
	}
	
	public function dump() {
		print "
-----------------------------------<br />
Name : '".$this->name."'<br>
Value : ".$this->value."<br>
Table : (".$this->table.")<br>
Field : ".$this->fieldname."<br>
Comment: ".$this->comment."<br>
Numeric : ".$this->numeric."<br>
-----------------------------------<br />
		";
	}
}


?>