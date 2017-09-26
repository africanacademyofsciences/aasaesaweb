<?php

class Petition {
	
	public $id, $guid;

	public $title, $email;
	public $end_date, $end_day, $end_month, $end_year;
	public $msg, $threshold;
	
	public $errmsg=array(); 
	
	public function Petition($guid='') {
	
		//print "P($guid)<br>\n";
		if ($guid) $this->loadByGUID($guid);
		
	}


	// Check if a petition record exists and if not create one
	public function create($guid) {
		global $db;
		if (!$guid) return false;

		$query="select count(*) from petition where guid='$guid'";
		if ($db->get_var($query)==0) {
			$query="insert into petition (guid) values ('$guid')";
			return $db->query($query);
		}
		return true;
	}
	

	public function update($guid, $data) {
		global $db;

		//print "u($guid, ".print_r($data, true).")<br>\n";
		if (!$this->create($guid)) return false;
		
		$end_date=$data['exp_year'].'-'.$data['exp_month'].'-'.$data['exp_day'];
		$email = $data['email'];
		$msg = $data['msg'];
		$threshold = $data['stats_threshold'];
		
		$query="update petition SET  
			end_date='$end_date', 
			email = '".$db->escape($email)."', threshold=".($threshold+0).", 
			message='".$db->escape($msg)."' 
			where guid='$guid'";
		//print "update event($query)<br>";
		if (!$db->query($query)) return false;
		return true;
	}



	public function loadByGUID($guid) {
		global $db;
		if (!$guid) return false;
		
		$query="SELECT p.title,
			pt.*,
			date_format(end_date, '%D %b %Y') as nice_end_date,
			date_format(end_date, '%d') as end_day, 
			date_format(end_date, '%m') as end_month, 
			date_format(end_date, '%Y') as end_year
			FROM pages p
			LEFT JOIN petition pt ON p.guid=pt.guid
			WHERE p.guid='".$guid."'
			LIMIT 1";
		//print "$query<br>\n";
		if ($row = $db->get_row($query)) {
			$this->id = $row->id;
			$this->guid = $row->guid;
			$this->title = $row->title;
			$this->email = $row->email;
			$this->end_date = $row->nice_end_date;
			$this->end_day = $row->end_day;
			$this->end_month = $row->end_month;
			$this->end_year = $row->end_year;
			$this->threshold = $row->threshold;
			$this->msg = $row->message;
		}
		return true;
	}
	
	
	public function sign($data) {
		global $db;
		//print "sign(".print_r($data, true).")<br>\n";
		$name = $db->escape($data['name']);
		$email = $db->escape($data['email']);
		$msg = $db->escape($data['message']);
		
		if (!$name) $this->errmsg[]="You must enter your name";
		if (!$email) $this->errmsg[]="You must enter your email address";
		if (!is_email($email)) $this->errmsg[]="Your email address is not valid";
		if ($this->signed($email)) $this->errmsg[]="You have already signed this petition";

		// Add this entry
		if (!count($this->errmsg)) {
			$query = "INSERT INTO petition_signed 
				(petition_id, name, email, message, added)
				VALUES 
				(
				".$this->id.", '".$name."', '".$email."',
				'".$msg."', NOW()
				)";
			//print "$query<br>\n";
			if (!$db->query($query)) $this->errmsg[]="Failed to add signature";
			else {
				// Do we need to wing out a message to anyone to inform them 
				// that the petition has been signed?
				//print "send sig to (".$this->email.")<br>\n";
				if ($this->email) {
					//print "Need to inform(".$this->email.")<br>\n";
					
					$send_data=array("PETITION-TITLE"=>$this->title,
						"SIGNED-NAME"=>$name, 
						"SIGNED-EMAIL"=>$email,
						"SIGNED-MESSAGE"=>$db->escape(str_replace("\r\n", "<br />", $data['message']))
					);
					include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
					include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
					include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
					$newsletter = new Newsletter();
					$newsletter->sendText($this->email, "PETITION-SIGNED", $send_data, false);
				}
				return true;
			}
		}
		return false;
		
	}
	
	// Check if this email has already signed the petition
	public function signed($email) {
		global $db;
		$query = "SELECT COUNT(*) FROM petition_signed 
			WHERE petition_id = ".$this->id." 
			AND email='$email'";
		return $db->get_var($query);
	}
		
	// Produce HTML for petition infomation
	public function drawStats() {
		global $db;
		$html='';
		$query = "SELECT p.title, 
			date_format(p.date_published, '%D %M %Y') as start_date, 
			date_format(pt.end_date, '%D %M %Y') as end_date,
			count(ps.id) as total
			FROM pages p
			LEFT JOIN petition pt ON p.guid=pt.guid
			LEFT OUTER JOIN petition_signed ps ON pt.id=ps.petition_id
			WHERE pt.id = ".$this->id."
			GROUP BY ps.petition_id
			";
		//print "$query<br>\n";
		return $db->get_row($query);
	}

	// Produce HTML for other petitions
	public function drawOther($status='A', $type="list") {
		global $db, $page, $site;
		$html='';
		
		$query = "SELECT p.title, p.guid,
			date_format(p.date_published, '%D %M %Y') as start_date, 
			date_format(pt.end_date, '%D %M %Y') as end_date
			FROM pages p
			INNER JOIN petition pt ON p.guid=pt.guid
			WHERE p.msv = ".$site->id."
			AND p.date_published IS NOT NULL
			AND p.date_published != '0000-00-00 00:00:00'
			";
		if ($this->id && $type=="list") $query .= "AND pt.id <> ".$this->id." ";			
		if ($status=='A') $query.="AND pt.end_date > (NOW() - INTERVAL 1 DAY) ";
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			foreach($results as $result) {
				//print "rguid(".$result->guid.") this(".$this->guid.")<br>\n";
				if ($type=="list") $html.='<li><a href="'.$page->drawLinkByGUID($result->guid).'">'.$result->title.'</a></li>';
				else if ($type=="select") $html.='<option value="'.$result->guid.'"'.($result->guid==$this->guid?' selected="selected"':'').'>'.$result->title.'</option>';
			}
		}
		if ($html) {
			if ($type=="list") $html='<ul>'.$html.'</ul>';
			else if ($type=="select") $html = '<option value="0">Select petition</option>'.$html;
		}
		return $html;
	}
	
	
	public function drawSignatureForm($data=array()) {
		global $labels;
		
		$html = '
		<ul id="petition-top">
			<li class="sign"><h2 class="flirneue">Sign our petition</h2></li>
			<li class="link"><a href="/privacy-policy/">'.$labels['privacy']['txt'].'</a></li>
			<li class="sep">|</li>
			<li class="link"><a href="/help/">'.$labels['help-link']['txt'].'</a></li>
		</ul>
		
		<form id="petitionForm" class="std-form" action="" method="post">
		<input type="hidden" name="treeline" value="sign" />
		<fieldset class="border">
			<fieldset class="field">
				<label for="f_name" class="required">'.$labels['name']['txt'].':</label>
				<input type="text" name="name" id="f_name" class="text'.$required['name'].'" value="'.$data['name'].'" />
			</fieldset>
	
			<fieldset class="field">
				<label for="f_email" class="required">'.$labels['email']['txt'].':</label>
				<input type="text" name="email" id="f_email" class="text'.$required['email'].'" value="'.$data['email'].'" />
			</fieldset>
	
			<fieldset class="field">
				<label for="f_message" class="required">'.$labels['message']['txt'].':<br />
				<span class="lighter">This is our suggested message.<br />You can edit or delete it if you wish.</span>
				</label>
				<textarea class="text'.$required['message'].'" name="message" id="f_message">'.($_POST?$data['message']:$this->msg).'</textarea>
			</fieldset>
	
			<fieldset class="field">
				<label for="f_submit" style="visibility:hidden;">Submit</label>
				<input type="submit" id="f_submit" class="submit" name="submit" value="'.$labels['SUBMIT']['txt'].'" />
			</fieldset>
			
		</fieldset>
		</form>
		';
		return $html;
	
	}

	public function drawForm($data=array(), $template=0){
	
		global $action, $help;
	
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/formDate.class.php');
		$formDate = new formDate();
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/events/getCurrentDate.inc.php');
	
		$end_day=count($data)?$data['exp_day']:$this->end_day;
		$end_month=count($data)?$data['exp_month']:$this->end_month;
		$end_year=count($data)?$data['exp_year']:$this->end_year;

		$email=count($data)?$data['email']:$this->email;
		$msg=count($data)?$data['msg']:$this->msg;
		$threshold=count($data)?$data['stats_threshold']:$this->threshold;

		$currentYear=date("Y", time());

		//print "exp $end_day, $end_month, $end_year<br>\n";
		$pet_html = '
	
	<div>
		<label for="f_email">Email:</label>
		<input type="text" name="email" id="f_email" value="'.$email.'" />
	</div>
	
	<div>
		<label for="end_day" >Expiry date:</label>
		'.$formDate->getDay('exp_day',$end_day).'
		'.$formDate->getMonth('exp_month',$end_month).'
		'.$formDate->getYear('exp_year',$end_year, 2, 'future',NULL, $currentYear).'
	</div>
	
	<div>
		<label for="f_msg">Message:</label>
		<textarea name="msg" id="f_msg" >'.$msg.'</textarea>
	</div>

	<div>
		<label for="f_stats_threshold">Threshold</label>
		<input type="text" name="stats_threshold" id="f_stats_threshold" value="'.$threshold.'" />
	</div>

		';
		return $pet_html;
	}
	
}

?>