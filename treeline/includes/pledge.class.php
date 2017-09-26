<?php

class Pledge {
	
	public $guid;
	public $reference;
	public $researcher_id;
	public $target, $total, $count;
	public $pledges = array();
	public $currency = "$";
	
	public function Pledge($guid='') {
		
		//print "P::P($guid)<br>\n";
		if ($guid) $this->loadByGUID($guid);
		
	}
	
	public function loadByGUID($guid='') {
		global $db;
		$this->guid = $guid;
		
		if ($guid) {
			$this->guid = $guid;
			$query  = "SELECT p.member_id, p.target, 
				DATE_FORMAT(pl.added, '%D %M %Y %H:%i') as date,
				pl.* , 
				CONCAT(m.firstname, ' ', m.surname) AS name, m.organisation
				FROM pages p 
				LEFT JOIN pledge pl ON pl.guid = p.guid
				LEFT JOIN members m ON m.member_id = pl.funder_id
				WHERE p.guid = '$guid'
				ORDER BY pl.added ASC
				";
			//print "<!-- $query -->\n";
			//print "$query <br>\n";
			if ($results=$db->get_results($query)) {
				$i = 0;
				foreach ($results as $result) {
					//print "<!-- r(".print_r($result, 1).") -->\n";
					//print "r(".print_r($result, 1).") <br />\n";
					if (!$this->researcher_id) $this->researcher_id = $result->member_id;
					if (!$this->target) $this->target = $result->target;			
					$this->pledges[$i]['funder'] = $result->funder_id;
					$this->pledges[$i]['date'] = $result->date;
					$this->pledges[$i]['org'] = $result->organisation;
					$this->pledges[$i]['amount'] = $result->amount;
					$this->total += $result->amount;
					if ($result->amount>0) $this->count++;
					$i++;
				}
			}
			$this->reference = $this->getRef($guid);
			//print "Loaded pledge(".print_r($this, 1).")<br>\n";
		}
	}
	
	public function getRef($guid) {
		for($i=0; $i<strlen($guid); $i++) {
			$c = substr($guid, $i, 1);
			if (is_numeric($c)) $r.=$c;
		}
		while (strlen($r)<5) $r = '9'.$r;
		$r = $this->researcher_id."-".substr($r, 0, 5);
		//print "<!-- Got ref($r) from guid($guid) -->\n";
		return $r;
	}
	
	public function add($funder_id, $researcher_id, $amount=0) {
		global $db, $site, $page;	

		$testing = true;
		$testing = false;
		if ($testing) print "<!-- P::a($funder_id, $researcher_id, $amount) -->\n";
		
		// 1 - Add the pledge
		if ($funder_id>0 && $researcher_id>0 && ($amount>0 || $_POST['type_id']>1)) {
			$query = "INSERT INTO pledge 
				(
					guid, type_id, added, 
					funder_id, researcher_id, amount
				)
				VALUES 
				(
					'".$page->getGUID()."', ".($_POST['type_id']+0).", NOW(), 
					$funder_id, $researcher_id, ".number_format($amount, 2, ".", "")."
				)
				";
			if ($testing) print "<!-- $query -->\n";

			if ($db->query($query)) {

				$newsletter = new Newsletter();
				
				$to = $db->get_var("SELECT email FROM members WHERE member_id = ".$researcher_id);
				$pledge_type = $db->get_var("SELECT title FROM pledge_type WHERE id=".($_POST['type_id']+0));
				
				$sendParams = array(
					"AMOUNT"=>$amount, 
					"TYPE"=>$pledge_type,
					"SENDER"=>'',
					"PROJECT-TITLE"=>$page->title
					);
				if ($testing) print "<!-- Data(".print_r($sendParams, 1).") -->\n";
				$newsletter->sendText($to, "PLEDGE-RECEIVED", $sendParams, true, $testing);
		
				$sendParams["SENDER"]="Received from : ".$db->get_var("SELECT CONCAT(firstname, ' ', surname) AS name FROM members WHERE member_id = ".$funder_id);
				//print_r($sendParams);
				$newsletter->sendText($site->contact['email'], "PLEDGE-RECEIVED", $sendParams, true, $testing);


				return true;
	
			}
			else if ($testing) print "<!-- Failed to add pledge, query error -->\n";
		}
		else if ($testing) print "<!-- Failed to add pledge, insufficient data -->\n";
		
		return false;
	}

	


}

?>