<?php

	/*
	
	  Newsletter Class
	  
	  last edited: 03/08/2007 
	  last edited by: by Phil T phil.thompson@ichameleon.com
	  changes made: Modified drawPreferences HTML
	  
	  
	  Table of contents
	  
	  includes: html2text class
	  # newsletter config
	  # isValid
	  # reuse
	  # createNew
	  # subscribe
	  # unsubscribe
	  # update
	  # validate
	  # validateSubject
	  # validateHTMLText
	  # Email design
	  	- getCSS
		- getHTMLHeader
		- GEtHTMLHEader
		- convertImages
		- convertLinks
		- getBodyText
		-setUnsubscribe
		- getBodyTExt
		- getHTMLEmail
		- getPlainEmail
	  # Preferences
	  
	
	*/


require_once('html2text.class.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ezSQL.class.php');


class newsletter{

	var $id;
	var $subject;
	
	var $html_text;
	var $html_text2;
	var $html_text3;
	var $plain_text;
	var $plain_text2;
	var $plain_text3;
	var $status;

	var $digestHTML;
	
	var $send_date;
	var $done;
	var $errorMsg;
	var $sitename;
	var $sitelang;
	var $siteencoding; 
	var $siteltr;
	
	public $valid = true;
	public $outbox_id;
	public $subscriber;
	
	public $event_id;
	public $member_id, $member_type_id, $member_status;
	
	private $msg = array();
	public $errmsg = array();
	public $labels=array();
	
	private $testing = '';
	private $mode; 
	
	public function newsletter($id = null, $html_text= null){

		$this->id = null;
		$this->subject = null;
		$this->html_text = $this->html_text2 = $this->html_text3 = null;
		$this->plain_text = null;
		$this->plain_text2 = null;
		$this->plain_text3 = null;
		$this->send_date = null;
		$this->done = null;
		$this->msv = null;
		$this->sitename = null;
		$this->sitelang = null;
		$this->status='N';
		$this->outbox_id = 0;
		
		if ($id) $this->loadByID($id);

		$this->subscriber = new Subscriber();
		
	}
	
	public function loadByID($id) {
	
		if($id){

			global $db;

			// Pull newsletter data out
			$query = "SELECT n.subject, n.text, n.text2, n.text3, n.send_date, n.done, n.msv, n.status, 
				sv.language, sv.msv, 
				s.name as title, 
				l.encoding, l.text_dir
				FROM newsletter n
				LEFT JOIN sites_versions sv on n.msv = sv.msv
				LEFT JOIN sites s on sv.microsite=s.microsite
				LEFT JOIN languages l ON sv.language=l.abbr
				WHERE n.id = " . $id;
			//print "$query<br>";
			if($db->query($query)){

				$n = $db->get_row(null);

				$this->id = $id;
				$this->subject = stripslashes($n->subject);

				$this->html_text = stripslashes($n->text);
				$this->html_text2 = stripslashes($n->text2);
				$this->html_text3 = stripslashes($n->text3);
				$h2t =& new html2text($this->html_text);
				$this->plain_text = $h2t->get_text();
				$h2t =& new html2text($this->html_text2);
				$this->plain_text2 = $h2t->get_text();
				$h2t =& new html2text($this->html_text3);
				$this->plain_text3 = $h2t->get_text();
				$this->status=$n->status;

				$this->send_date = $n->send_date;
				$this->done = $n->done;
				$this->msv = $n->msv;

				$this->sitename = $n->title;
				$this->sitelang = $n->language;
				$this->siteencoding = $n->encoding;
				$this->siteltr = $n->text_dir;
				
			}
		}
		
		/*
		if ($html_text) { //This means it's a digest.
			
			global $site;
				
			$this->html_text = stripslashes($html_text);
			$this->msv = $site->id;
			$h2t =& new html2text($this->html_text);
			$this->plain_text = $h2t->get_text();
			
		}
		*/
	}

	public function setMode($mode) {
		$this->mode=$mode;
	}

	public function isValid(){
		if($this->id) return true;
		return(false);
	}


	public function reuse($id){

		global $db;

		// Pull newsletter data out

		$query = "SELECT subject, text, text2, text3, send_date, done, msv
			FROM newsletter
			WHERE id = " . $id;

		if($db->query($query)){

			$n = $db->get_row(null);

			$this->id = null; // Creating a new newsletter based on an old one
			$this->subject = stripslashes($n->subject);

				$this->html_text = stripslashes($n->text);
				$this->html_text2 = stripslashes($n->text2);
				$this->html_text3 = stripslashes($n->text3);
				$h2t =& new html2text($this->html_text);
				$this->plain_text = $h2t->get_text();
				$h2t =& new html2text($this->html_text2);
				$this->plain_text2 = $h2t->get_text();
				$h2t =& new html2text($this->html_text3);
				$this->plain_text3 = $h2t->get_text();
			/*
			$this->html_text = stripslashes($n->text);
			$h2t =& new html2text($this->html_text);
			$this->plain_text = $h2t->get_text();
			*/
			$this->send_date = $n->send_date;
			$this->done = $n->done;
			$this->msv = $n->msv;
		}
	}


	public function createNew($status='N'){

		global $_SESSION, $db, $site;

		$preference = $_POST['preference'];
		$query = "INSERT INTO newsletter 
			(subject, text, text2, text3, msv, added_date, `status`) 
			VALUES ('" . $db->escape($this->subject) . "', 
				'" . $db->escape($this->html_text) . "', 
				'" . $db->escape($this->html_text2) . "', 
				'" . $db->escape($this->html_text3) . "', 
				".$site->id.", NOW(),
				'$status')";
		//echo $query; exit;
		$db->query($query);

		$this->id = $db->insert_id;
		
		while (list ($key,$val) = @each ($preference)) { 
			$query = "INSERT INTO newsletter_send_preferences (newsletter_id, preference_id) VALUES (".$this->id.",".$val.")";	
			
			if ($db->query($query)){ // update their status to opted in
				$message .= "<br />Newsletter preference group added";
			}
			else {
				$error++;
				$message .= "<br />Didn't add newsletter preference group";
			}
		}

		// Add event subscriber preferences.
		$event_id = $_POST['guid'];
		if ($event_id) {
			$query = "INSERT INTO newsletter_send_preferences (newsletter_id, event_id) VALUES (".$this->id.", '".$event_id."')";
			$db->query($query);
		}
		
		return $this->id;

	}

	public function subscribe($subscribe=true) {
		//print "<!-- sub($subscribe) p(".print_r($_POST, 1).") -->\n";
		global $db, $site, $page, $site;
		
		$testing = false;
		if ($this->testing=="sub") $testing = true;
		if ($testing) print "<!-- N::s($subscribe) testing(".$this->testing.") -->\n";
		$error = 0; // error counter. increment for every error
		
		$sub = array();
		$subscriber = $this->subscriber;
		
		$email = $db->escape($_POST['email']);
		$name = $db->escape($_POST['name']);
		
		//if (!$email) $this->errmsg[]=$page->drawLabel('NOEMAIL','You did not provide an email address');
		//if (!is_email($email)) $this->errmsg[]="You have entered an invalid email address";
		
		$allpref=substr($_POST['allpref'],0,-1);
		$preference = $_POST['preference'];
		
		$subscriber->setName($name);
		$subscriber->set("email", $email);
		//$subscriber->set("info", $_POST['work']);		
		$subscriber->set("organisation", $db->escape($_POST['work']));
		$subscriber->set("jobtitle", $db->escape($_POST['job']));
		$subscriber->set("country", $_POST['country']);
		$subscriber->isvalid();
		foreach ($subscriber->errmsg as $tmp) $this->errmsg[] = $tmp;
				
		if ($testing) print "<!-- Sub data ok? e(".print_r($this->errmsg, 1).") -->\n";
		if (count($this->errmsg)) return 0;
		
		// Should really check if this email address is already
		$query = "SELECT member_id FROM members WHERE email = '".$email."'";
		//$message="<br>$query";
		$member_id = $db->get_var($query);
		
		// Existing members can't use the form as it sends admin confirmations now.
		// Now they can again as we no longer need to use it for fellows (How annoying is this?)
		// if ($member_id >0 ) $this->errmsg[] = 'You are already registered on this site. Please <a href="'.$site->link.'member-login/">log in</a> to update your details.';
		
		if (!$member_id) $member_id=$subscriber->createNew();
		
		if ($member_id>0) {
		
			// This member has a member record but are they a member of this site?
			$query = "SELECT member_id, type_id, `status` FROM member_access where member_id=".$member_id." and msv=".$site->id;
			//print "<!-- $query -->\n";
			$row = $db->get_row($query);
			if (!$row->member_id) {

				if ($site->id == 19) $sub['member_type'] = $_POST['member_type'];
				// Add member to this site.
				$member = new Member($member_id);
				$member->addToSite($member_id, $site->id, $sub);
			}
			else { 
				$this->member_type_id = $row->type_id;
				$this->member_status = $row->status;
				$this->errmsg[] ="This email address is already subscribed to our mailing lists, your email preferences have been updated but your personal data has not been modified";
			}
			$this->member_id = $member_id;

			// Are there any preference groups
			//print "allpref($allpref) pst(".print_r($_POST, true).")<br>\n";
			if ($allpref) {

				// Remove all currently set up preferences
				$query = "DELETE from newsletter_user_preferences WHERE member_id = ".$member_id." AND preference_id in ($allpref)";
				$db->query($query);
				//print "$query<br>\n";
				
				//Subscriber gets all newsletters
				if ($_POST['all'] == 1) { 
					unset($preference);
					$query = "SELECT preference_id FROM newsletter_preferences n WHERE deleted != 1 AND site_id = ".$site->id;
					//print "$query<br>";
					if ($results = $db->get_results($query)) {
						foreach($results as $result) {
							$preference[]=$result->preference_id;
						}
					}
				}
		
				//Now add preferences to subscriber
				//print_r($preference);
				if (is_array($preference)) {
					foreach($preference as $pref_id) {
						if ($pref_id>0) {
							$query = "INSERT INTO newsletter_user_preferences (member_id, preference_id) VALUES (".$member_id.",".$pref_id.")";
							$db->query($query);
							$addmc[] = $pref_id;
						}
					}
				}
				else if ($preference>0) {
					$query = "INSERT INTO newsletter_user_preferences (member_id, preference_id) VALUES (".$member_id.",".$preference.")";
					$db->query($query);
					$addmc[] = $pref_id;
				}
				else {
					// No preferences selected. 
					// This is fine as people can just signup 
					//$this->errmsg[] = "This site has no newsletters to subscribe to";
				}
				
				// Subscribe to mailchimp
				$useMC = true; 	// Open to all
				if ($_SERVER['REMOTE_ADDR']=="80.0.182.170") $useMC = true;
				if ($testing) print "<!-- IP(".$_SERVER['REMOTE_ADDR'].") mc($useMC) -->\n";
				if ($useMC) $this->setMC($member_id, $allpref, $addmc);
			}
		}
		else $this->errmsg[] = "Failed to add or find member id<br />"; 
		
		return !count($this->errmsg);
	}
	

	public function unsubscribe($email, $member_id=0) {
		global $db, $site, $page;
		
		//print "us($email, $member_id)<br>";
		if (!$member_id>0) {
			$query="select member_id, concat(firstname, ' ', surname) as name from members where email='$email'";
			//print "$query<br>";
			if ($row=$db->get_row($query)) {
				$member_id=$row->member_id;
				$_POST['name']=$row->name;
			}
		}
		
		if ($member_id>0) {		
			$query="select * from newsletter_user_preferences nup
				left join newsletter_preferences np on nup.preference_id=np.preference_id 
				where member_id=".$member_id."
				and np.site_id=".$site->id;
			//print "$query<br>";
			if ($results=$db->get_results($query)) {
				foreach($results as $result) {
					$query="delete from newsletter_user_preferences where member_id=".$member_id." and preference_id=".$result->preference_id;
					//print "$query<br>";
					$db->query($query);
				}
			}
			return true;
		}
		
		$this->errmsg[] = $page->drawLabel('NOTREG', 'Email address was not registered');
		return false;
	}
	
	public function setMC($member_id, $all, $addtomc) {
		global $db;

		//print "<!-- N::sMC() testing(".$this->testing.") -->\n";
		$mcdebuglevel = 1;
		$mcdebug = $this->testing=="sub"?$mcdebuglevel:0;
		if ($mcdebug) print "<!-- sMC($member_id, $all, ".print_r($addtomc, 1).") -->\n";
		
		$success = $error = 0;

		if (!$member_id) {
			$this->errmsg[] = "No member ID to send to MC";
			$error++;
		}
		else {
			$mc = new mc($mcdebug);
	
			$prefs = explode(",", $all);
			foreach ($prefs as $pref_id) {
				if ($pref_id>0) {
					$query = "SELECT * FROM newsletter_preferences WHERE preference_id = ".$pref_id;
					$pd = $db->get_row($query);
					$pref_title = $pd->preference_title;
	
					$subscribe = 0;
					if (in_array($pref_id, $addtomc)) $subscribe = 1;
					
					if ($mcdebug>1) print "<!-- Mem:$member_id [$pref_id:$subscribe] MC($pref_title) -->\n";
					if ($mc->subscribeToList($member_id, $pref_title, $subscribe)) {
						$success++;
					}
					else {
						if (count($mc->errmsg)) $this->errmsg[] = array_pop($mc->errmsg);
						if ($mcdebug>0) print "<!-- Failed -->\n";
						$error++;
					}
				}
				else if($mcdebug>0) print "<!-- Preference[$pref_id] ignored -->\n";
			}
		}
	
		if ($mcdebug>1) print "<!-- N::setMC success[$success] error[$error] -->\n";
		return $error;
	}
	
	
	public function update(){

		global $db;
		
		$query = 
			"UPDATE newsletter 
			SET subject = '" . $db->escape($this->subject) . "', 
			text = '" . $db->escape($this->html_text) . "',
			text2 = '" . $db->escape($this->html_text2) . "',
			text3 = '" . $db->escape($this->html_text3) . "'
			WHERE id = " . $this->id;
		$db->query($query);

		// Remove all preferences first.
		$query = "DELETE FROM newsletter_send_preferences WHERE newsletter_id = ".$this->id;
		$db->query($query);

		// Add news preferences
		$preference = $_POST['preference'];
		if ($preference){
			
			while (list ($key,$val) = @each ($preference)) { 
				$query = "INSERT INTO newsletter_send_preferences (newsletter_id, preference_id) VALUES (".$this->id.",".$val.")";
				if ($db->query($query)){ // update their status to opted in
					$message .= "<br />Added newsletter preference.";
				}
				else {
					$message .= "<br />Error adding preference.";
				}										
			}
		}
		
		// Add event subscriber preferences.
		$event_id = $_POST['guid'];
		if ($event_id) {
			$query = "INSERT INTO newsletter_send_preferences (newsletter_id, event_id) VALUES (".$this->id.", '".$event_id."')";
			$db->query($query);
		}
		
	}

	public function validate()
	{
		$this->validateSubject();
		$this->validateHTMLText();
		return $this->valid;
	}
	public function validateSubject()
	{
		global $page;
		if (strlen($this->subject) < 4) {
			$this->errmsg[]=$page->drawLabel("tl_nl_valid_subject", "Newsletter subject is too short");
			$this->valid = false;
		}
	}
	public function validateHTMLText()
	{	
		global $page;
		if (strlen($this->html_text)<4) {
			$this->errmsg[] = $page->drawLabel("tl_nl_valid_text", "Newsletter content text is too short");
			$this->valid = false;
		}
	}
	
	
	public function validateEmail($email)
	{
		$regExpEmail = "/^([^\@ \.]+\.)*[^\@ \.]+\@([^\@ \.]+\.)+[^\@ \.]+$/";
		if (preg_match($regExpEmail, $email) < 1) return false;
		return true;
	}
	

	
	
	
public function getHTMLheader() {
	global $site;
	//print_r($site);

	$langDayFormat="d";
	if ($this->sitelang=="en") $langDayFormat="jS";

	//$this->msg[]="Get cur date in correct lang...\n";
	$timeNow=time();
	$dateNow=date($langDayFormat, $timeNow)." ";
	//$this->msg[]="Now ($dateNow)\n";
	$monthNow=strtoupper(date("M", $timeNow));
	//$this->msg[]="Month now for label lookup - ($monthNow)\n";
	$dateNow.=$this->labels[$monthNow]['txt']." ";
	//$this->msg[]="Now ($dateNow)\n";
	$dateNow.=date("Y", $timeNow);
	//$this->msg[]="Now ($dateNow)\n ____________________ \n";
	
	
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$this->sitelang.'" lang="'.$this->sitelang.'" dir="'.$this->siteltr.'">
<head>
<meta http-equiv="Content-Type" content="text/plain; charset='.$this->siteencoding.'" />
<title>'.$site->name.' newsletter</title>
</head>
<style type="text/css">
body {
	font-family: arial,helvetica,clean,sans-serif;
	font-size: 77%;
	color: #666;
}
p {
	padding:0 0 10px 0;
	margin:0;
}
a {
	text-decoration: none;
	font-weight: normal;
	color:#3366CC;
}
h1, h2, h3, h4 {
	font-family: Tahoma,Arial,Verdana;
	margin:0;
	padding:0;
	font-weight:normal;
}
h3 {
	font-size:290%;
}
h4 {
	font-size:160%;
}
ul {
	margin:0px 0 20px; 0;
	padding:0;
}
li {
	margin-left: 20px;
}

</style>
<body style="color:#666;background-color:#CCC;margin:0;">
';


	// Generate an info bar containing the current date 
	// If its not an internal email show a link to open it in a browser
	if ($this->status=="S") $this->mode = "VGA";	// Simulate online mode (hide the infobar)
	$infoBarRow = '
<div style="height:31px;margin:left:76px;">
	<p style = "margin-left:150px;margin-top:4px;color:#B7D0E8;">
	'.(($this->mode=="VGA")?"&nbsp;":'If you cannot view this email, please visit <a href="'.$site->root.'newsletter?id='.$this->id.'" style="color:#fff;">'.$site->root.'newsletter?id='.$this->id.'</a>').'
	</p>
</div>
<img src="'.$site->root.'img/email/tracker-image.png" />
';
if ($this->mode!="VGA") $html.=$infoBarRow;

//print_r($site->properties);
// Microsite specific logo here (only for site emails switched off for newsletters)
if ($this->status=="S") {
	$tmp_logo=new Image(); 
	$tmp_logo_guid=$site->properties['email_logo'];
	if ($tmp_logo_guid>'') {
		$tmp_logo->loadImageByGUID($tmp_logo_guid);
		if (is_array($tmp_logo->subimages)) {
			for($i=0; $i<count($tmp_logo->subimages); $i++) {
				if (($tmp_logo->subimages[$i]['width']+0)==550) {
					$tmp_logo_height=$tmp_logo->subimages[$i]['height'];
					$tmp_logo_filename='<img src="'.$site->root.'silo/images/'.$tmp_logo->subimages[$i]['filename'].'" alt="Website logo" />';
					$html.='<div style="margin:0 auto;width:550px;background-color:#AFAFAF;height:'.$tmp_logo_height.'px;">'.$tmp_logo_filename.'</div>';
				}
			}
			//print "got file($tmp_logo_filename) height($tmp_logo_height)<br>\n";
		}	
	}
}

$html.='<p style="margin:0 auto;width:530px;height:64px;background-color:#AFAFAF;font-size:230%;padding:20px 0 0 20px;vertical-align:center;color:#fff;">'.($site->properties['tagline']?$site->properties['tagline']:$site->title).'</p>
<div style="margin:0 auto;width:510px;padding:20px; background-color: #fff;">
'.$this->getBodyText().'
</div>
';

	// Add html for section 2
	if ($this->html_text2) {
		$html.='<div id="section2" class="section2" style="margin:0 auto;width:510px;background-color:#f3f4f1;background-image:url(\'http://'.$site->url.'/img/email/div2top1x9.gif\');background-position:top;background-repeat:repeat-x; padding: 20px;" >
'.$this->getBodyText(2).'
</div>';
	}

	// Add html for section 3
	if ($this->html_text3) {
		$html.='<div id="section3" class="section3" style="margin:0 auto;width:510px;background-color:#e5e6df;background-image:url(\'http://'.$site->url.'/img/email/div3top1x9.gif\');background-position:top;background-repeat:repeat-x;padding:20px;" >
'.$this->getBodyText(3).'


</div>';
	}

	//$html.='<div style="margin:0 auto;width:550px;background-color:#607072;height:10px;"></div>';

	return $html;
}

public function getHTMLfooter() {
	global $site;
	$host = $site->root;

	if ($this->status=='N') $view_in_browser = $this->labels['EMAIL_SEE']['txt'].' <a href="'.$site->root.$site->name."/".$site->lang.'/newsletter/?id='.$this->id.'">'.$this->labels['CLICK_HERE']['txt'].'</a>';

	$html = '<div style="margin:0 auto;width:510px;padding:10px 20px;background-color:#AFAFAF;font-size:90%;color:#fff;">
<p>'.str_replace("@@SITENAME@@", $site->title, $this->labels['EMAIL_UNSUB1']['txt']).' '.$this->labels['EMAIL_UNSUB2']['txt'].' <a href="http://unsubs:email=xxx">'.$this->labels['EMAIL_UNSUBLINK']['txt'].'</a></p>
<p>'.$this->labels['EMAIL_NOSPAM']['txt'].' <a href="'.$site->root.$site->name."/".$site->lang.'/privacy-policy">'.$this->labels['privacy']['txt'].'.</a></p>
<p>'.$this->labels['FOOTER_MSG1']['txt'].$view_in_browser.'</p>

</div>';

	$html.= '
<tr>
	<td>
	<p>Site by <a style="color:#fff;" href="http://www.treelinesoftware.com?ref='.$site->name.'">Treeline Software</a></p>
	<p>Powered by <a style="color:#fff;" href="http://demo.treelinecms.com">Treeline</a></p>
	</td>	
</tr>
</tabel>
';

	return $html;
}	










public function convertImages($content){
	// add full web address to all images so they appear in email clients
	$content = str_replace('src="/silo','src="http://'.SERVER_NAME.'/silo',$content);
	return $content;
}

public function convertLinks($content){
	// add full web address to all site links to they work in email clients
	$content = str_replace('href="/','href="http://'.SERVER_NAME.'/',$content);
	return $content;
}

public function setTesting($t) {
	$this->testing = $t;
}

public function setSubject() {
	global $site;
	$msg = "set Subject(".$this->subject.")";
	if (!$this->subject) $msg.="No subject found \n";
	/*
	$data = array("SITENAME"=>$site->title);
	if (preg_match("/@@(.*)@@/", $this->subject, $reg)) {
		$msg.="replace (".$reg[1].") with (".$data[$reg[1]].") \n";
		$this->subject = str_replace("@@".$reg[1]."@@", $data[$reg[1]], $this->subject);
	}
	$msg.="sub = (".$this->subject.") return true \n";
	//mail("phil.redclift@ichameleon.com", "wtf do I need to do this", $msg);
	*/
	return true;
}

// Set all subscriber specific data here.
public function setData($data, $s, $dbg=false) {
	if ($dbg) $this->msg[]="got keys (".print_r($data, true).")";
	//print "got s($s)<br>\n";
	$s = preg_replace(array_keys($data), array_values($data), $s);
	if ($dbg) $this->msg[]="created s($s)";
	return $s;
}

public function getBodyText($section=1){
	// convert body text/email content into usual content
	switch($section) {
		case 2 : $body = $this->html_text2; break;
		case 3 : $body = $this->html_text3; break;
		case 1 : 
		default : $body = $this->html_text; break;
	}
	$body = $this->convertLinks($body); 
	$body = $this->convertImages($body);
	//$body = nl2br($body);
	return $body;
}


public function getHTMLEmail($footer=true){
	// Return the HTML for this email. Header+Body+Footer
	$content = $this->getHTMLheader();
	if ($footer) $content.=$this->getHTMLfooter();
	return($content);
}


	public function getPlainEmail($footer=true){
	// Return the Plain text for this email
		global $site;
		$host = SERVER_NAME."/".$this->sitename."/".$this->sitelang;
		$content = '

'.$this->plain_text.'
'.$this->plain_text2.'
'.$this->plain_text3.'
';
		if ($footer) {
			$content .= str_replace("@@SITENAME@@", $site->title, $this->labels['EMAIL_UNSUB1']['txt']).' '.$this->labels['EMAIL_UNSUB2']['txt'].' '.$site->link.'enewsletters/?action=unsubscribe
'.$this->labels['EMAIL_NOSPAM']['txt'].' '.$site->link.'privacy-policy
'.$this->labels['FOOTER_MSG1']['txt'];
			/*
			$content.='
This email is only sent to subscribers of '.$site->name.' email news. If you believe you have received
this email in error, please visit http://'.$host.'/enewsletters/?action=unsubscribe
to change your registration details.

We never spam. Read our privacy policy at http://'.$host.'/privacy-policy/

';
			*/
		}
		return $content;
	}
	
// Check if any pages need to be included and get an HTML page sumamry.
public function compileDigestPageHTML($digest) {
	global $db;
	$this->digestHTML = '';
	
	if ($digest>0) {
		$query = "SELECT p.title, c.content FROM newsletter_digest_pages ndp
			LEFT JOIN pages p ON p.guid = ndp.guid
			LEFT JOIN content c ON p.guid = c.parent
			WHERE ndp.digest_id = $digest 
			AND c.placeholder = 'content'
			AND p.date_published <> '0000-00-00 00:00:00'
			AND p.date_published IS NOT NULL
			AND c.revision_id = 0
			";
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$this->digestHTML .='<h3>'.$result->title.'</h3>';
				$this->digestHTML .= limitWords($result->content, 200);
			}
		}
		if ($this->digestHTML) $this->digestHTML = "<h2>Latest content from the website</h2>".$this->digestHTML;
	}
	return true;
}

	

	public function drawPreferences($msv){
		global $db, $labels, $page;
		
		$query = "SELECT * FROM newsletter_preferences n
			WHERE deleted != 1 AND site_id = $msv
			ORDER BY preference_title ASC";
		//echo $query;
		$results = $db->get_results($query);

		$bloggable = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, 'blog', 0);

		if($results){
			$html = '<fieldset class="preferences">'."\n";
			//$html .= '<legend>'.$labels['prefer']['txt'].'</legend>'."\n";
			$pref_count = 0;
			foreach($results as $result){
				$checked='';
				if (is_array($_POST['preference'])) {
					$checked=in_array($result->preference_id, $_POST['preference'])?"checked ":"";
				}
				$allpref.=$result->preference_id.",";
				// In case we only have a single preference we need to hide the field
				$singlehtml .= '<input type="hidden" name="preference" value="'.$result->preference_id.'" />'."\n";
				// Add this preference to the list
				$html.='<input class="checkbox" style="float:left;clear:left;" type="checkbox" '.$checked.'id="'.str_replace(' ','_',$result->preference_title).'" name="preference[]" value="'.$result->preference_id.'" />'."\n";
				$html.='<label class="checklabel" for="'.str_replace(' ','_',$result->preference_title).'">'.$result->preference_title.'</label><br />'."\n";
				$pref_count++;
			}
			$checked=($_POST['all']==1)?"checked ":"";
			$checked='';
			// Only show the all box if we are on the website
			if ($page->getTemplate()=="newsletters.php") {
				if ($pref_count>1 || $bloggable) $html.='
<fieldset class="form-group form-group-sm">				
<input class="checkbox" style="float:left;clear:left;" '.$checked.' type="checkbox" id="All" name="all" value="1" />
<label for="All" class="checklabel">'.$page->drawLabel("recall", "Receive all").'</label><br />
</fieldset>
';
				else $html = '<fieldset>'.$singlehtml;
			}
			$html .= '<input type="hidden" name="allpref" value="'.$allpref.'" />'."\n";
			$html.='</fieldset>'."\n";
		}
		return $html;
	}


	public function updatePreferences($member_id) {
		global $db, $site;
		$total_set=0;
		if ($member_id>0 && $site->id>0) {

			if ($this->testing) print "<!-- uP = post(".print_r($_POST, 1).") -->\n";
			
			// Remove all preferences for this user from this site
			$query = "DELETE FROM newsletter_user_preferences 
				WHERE member_id=".$member_id."
				AND preference_id IN (SELECT preference_id FROM newsletter_preferences WHERE site_id=".$site->id.")";
			//print "$query<br>\n";
			$db->query($query);

			// Create an array of preferences required
			if ($_POST['all']) {
				$apref=explode(",", $_POST['allpref']);
			}
			else if (is_array($_POST['preference'])) {
				foreach ($_POST['preference'] as $pref) {
					$apref[]=$pref;
				}
			}
			
			if (is_array($apref)) {
				unset($_POST['preference']);
				foreach ($apref as $pref) {
					//print "Add preference $pref<br>\n";
					$_POST['preference'][]=$pref;
					if ($pref) {
						//print "add preference ($pref) to member($member_id)<br>\n";
						$query = "INSERT INTO newsletter_user_preferences (member_id, preference_id) 
							VALUES($member_id, $pref)";
						//print "$query<br>\n";
						if ($db->query($query)) {
							$total_set++;
						}
					}
				}
			}
			
			if ($this->testing) print "<!-- uP($member_id) all(".print_r($apref, 1).") -->\n";
			$this->setMC($member_id, $_POST['allpref'], $apref);
			return $total_set;
		}
	
		// These values should not really be important as this will only happen 
		// if there is a bug.
		if (!$member_id) return -1;
		if (!$site->id) return -2;
		return -3;
	}

	public function drawMailPreferences($msv){
		global $db, $labels;
		
				$query = "SELECT * FROM store_mail_groups smg 
					WHERE site_id = $msv 
					ORDER BY name ASC";
				//echo $query;
				$results = $db->get_results($query);
	
				if($results){
					echo '<fieldset class="border">'."\n";
					echo '<legend>'.$labels['mailprefer']['txt'].'</legend>'."\n";
					foreach($results as $result){
						//print "check if id(".$result->id.") is in ";
						//print_r($_POST['mail_group']);
						//print "<br>";
						$checked='';
						if (is_array($_POST['mail_group'])) {
							$checked=in_array($result->id, $_POST['mail_group'])?"checked ":"";
						}
						$mailallpref.=$result->id.",";
						echo '<input class="checkbox" style="float:left;clear:left;" type="checkbox" id="'.str_replace(' ','_',$result->name).'" '.$checked.'name="mail_group[]" value="'.$result->id.'" />'."\n";
						echo '<label class="checklabel" for="'.str_replace(' ','_',$result->name).'">'.$result->name.'</label><br />'."\n";
					}
					$checked=($_POST['mailall']==1)?"checked ":"";
					echo '<input class="checkbox" style="float:left;clear:left;" type="checkbox" id="All" '.$checked.'name="mailall" value="1" />'."\n";
					echo '<label for="All" class="checklabel">'.$labels['recall']['txt'].'</label><br />'."\n";
					echo '<input type="hidden" name="mailallpref" value="'.$mailallpref.'" />';	// Dont like it but need to remove preference somehow
					echo '</fieldset>'."\n";
				}
	}
	
	// Function draws all preference as html for mail merging....
	public function listPreferences($email='', $member_id='') {
		//print "lp($email, $member_id)<br>";
		global $db;
		$field="0";
		$email_html = $post_html = '';
		
		if ($email>'') {
			$field="m.email='$email' ";
		}
		else {
			if (!$member_id) $member_id=$_SESSION['member_id'];
			$field="m.member_id=".$member_id." ";
		}
		// Email preferences
		$query = "SELECT n.preference_title as name, s.title, n.site_id
			FROM newsletter_preferences n
			LEFT JOIN newsletter_user_preferences nup ON nup.preference_id=n.preference_id
			LEFT JOIN members m on nup.member_id=m.member_id
			LEFT JOIN sites_versions sv on n.site_id=sv.msv
			LEFT JOIN sites s on sv.microsite=s.microsite
			WHERE n.deleted != 1 AND $field
			ORDER BY n.site_id, n.preference_title ASC";
		//print "$query<br>";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				if ($cursite!=$result->title) {
					$cursite=$result->title;
					if ($email_html) $email_html.="</ul>";
					if ($result->site_id>1) $email_html.="<p><strong>".$result->title."</strong></p>";
					$email_html.="<ul>";
				}
				$email_html.="<li>".$result->name."</li>";
			}
		}
		if ($email_html) $email_html="<strong>You have selected to receive the following email communications from ".$site->name."</strong>".$email_html."</ul>";
		else $email_html="You have elected to receive no information by email";

		/*
		// Mail preferences
		$query = "SELECT smg.name, sab.post_code, s.title FROM store_mail_groups smg 
			LEFT JOIN store_mail_members smm on smg.id=smm.mail_group
			LEFT JOIN members m on smm.member_id = m.member_id
			LEFT JOIN sites_versions sv on smg.site_id=sv.msv
			LEFT JOIN sites s on sv.microsite=s.microsite
			LEFT JOIN store_address_book sab ON smm.addr_id=sab.addr_id
			WHERE $field
			ORDER BY site_id, smm.addr_id, smg.name";
		//print "$query<br>";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				if ($cursite!=$result->title) {
					$cursite=$result->title;
					if ($post_html) $post_html.="</ul>";
					$post_html.="<p><strong>".$result->title."</strong></p><ul>";
				}
				$post_html.="<li>".$result->name.($result->post_code?" to postcode ".$result->post_code:"")."</li>";
			}
		}
		
		if ($post_html) $post_html="<h4>You have selected to receive postal updates regarding</h4><ul>".$post_html."</ul>";
		else $post_html="<p>You have elected to receive no information by post</p>";
		*/
		return $email_html.$post_html;

	}
	
	
	public function drawCountrySelect($current) {
		global $db;
		$html='';
		$query="select country_id, title from store_countries order by title";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				$html.='<option value="'.$result->country_id.'"'.(($result->country_id==$current)?"selected":"").'>'.$result->title.'</option>';
			}
		}
		return $html;
	}
	
	public function drawAdminPreferences($id, $msv){
		global $db;

		//print "dAP($id, $msv)<br>\n";				
				
		$temp = ($id > 0? ", IF(np.newsletter_id=".$id.",1,0) as flag" : " ");
		
		if (count($exclude)) $exwhere = "AND n.preference_id NOT IN (".implode(",",$exclude).") ";
		//print "got exwhere($exwhere) ex(".print_r($exclude, true).")<br>\n";

		if ($id){
			$query = "SELECT n.preference_id, n.preference_title ".$temp."
				FROM newsletter_preferences n 
				LEFT JOIN newsletter_send_preferences np ON n.preference_id=np.preference_id 
				WHERE n.preference_id NOT IN 
					(
					SELECT n.preference_id FROM newsletter_preferences n 
					LEFT JOIN newsletter_send_preferences np ON n.preference_id=np.preference_id 
					WHERE np.newsletter_id=".$id."
					) 
				AND n.deleted!=1 
				$exwhere
				AND n.site_id = $msv 

				UNION 

				SELECT n.preference_id, n.preference_title ".$temp." 
				FROM newsletter_preferences n 
				LEFT JOIN newsletter_send_preferences np ON n.preference_id=np.preference_id 
				WHERE np.newsletter_id=".$id." 
				AND n.deleted!=1
				$exwhere

				ORDER BY preference_id ";
					
		} 
		else {
			$query = "SELECT * FROM newsletter_preferences n 
				WHERE deleted != 1 
				AND site_id = $msv
				$exwhere
				ORDER BY preference_title ASC ";
		}
	
		//print "$query<br>\n";
		$results = $db->get_results($query);
		
		if($results){
			foreach($results as $result){
				if ($result->flag == 1){
					$html.='<input type="checkbox" class="checkbox" id="'.$result->preference_title.'" name="preference[]" value="'.$result->preference_id.'" checked="checked" />';
				} else {
					$html.='<input type="checkbox" class="checkbox" id="'.$result->preference_title.'" name="preference[]" value="'.$result->preference_id.'" />';
				}
				$html.='<label for="'.$result->preference_title.'" class="checklabel">'.$result->preference_title.':</label><br />'."\n";
			}
			return $html;
		}
	}
	
	
	
	// Man this dude just keeps growing,
	public function sendText($email, $title, $data, $footer=true, $testing=false) {

		if ($testing) print "<!-- sT($email, $title, data, $footer) -->\n";
		//include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

		global $db, $site;
		$result=false;
		unset($this->msg);
		$this->msg[] = "sT($email, $title, data, $footer)";
		
		// Add any relevant default data to the array
		if (!$data['SITENAME']) $data['SITENAME'] = $site->title;
		
		$from=$subject=$strHTMLEmail=$strHTMLPlain='';
		if (!$this->validateEmail($email)) {
			$this->msg[]="send text function sent an invalid email addy($email, $title, data not included...)";
		}
		
		// Select the send text for this microsite 
		// 		or select send text for the main site.
		$query="SELECT id
			FROM newsletter n 
			WHERE text3='$title' 
			AND (msv = ".($site->id+0)." OR msv=1)
			AND status='S'
			ORDER BY msv DESC
			LIMIT 1";
		$this->msg[]="$query \n";
		//print "$query<br>";
		if (!$row=$db->get_row($query)) {
			//print "failed send mail";
			$this->msg[]="failed to locate follow up email to send to $email";
		}
		else {

			// Check if we have a Treeline user and if they actuall want this message?
			if ($data['treeline_user_id']>0) {
				$query = "SELECT IF(notify=0,1,0) AS notify 
					FROM user_notify 
					WHERE newsletter_id = ".$row->id."
					AND user_id = ".$data['treeline_user_id']."
					";
				//print "$query<br>\n";
				// Knock it on the ed now.
				if (@$db->get_var($query)==1) return true;
			}

			// Email checks out, send the test mail
			//print "sending sendText to ($email)<br>";
			$this->loadByID($row->id);
			if ($this->validate()) {
	
				$page=new Page();
				$this->labels=$page->getTranslations($site->id, $site->language);
	
				// Need to hide html_text2 and 3 when collecting body html
				// Save em all just in case we need to reenstate em once they all changed
				/*
				$tmp1=$this->html_text; 
				$tmp2=$this->html_text2; $this->html_text2='';
				$tmp3=$this->html_text3; $this->html_text3='';
				$tmpp1=$this->plain_text; 
				$tmpp2=$this->plain_text2; $this->plain_text2='';
				$tmpp3=$this->plain_text3; $this->plain_text3='';
				*/
				// Dont appear to need em again so just kill em for now
				$this->html_text2=''; $this->html_text3='';
				$this->plain_text2=''; $this->plain_text3='';
				
				$relation=array();
				foreach ($data as $k=>$v) {
					$relation['/@@'.$k.'@@/']=$v; 
				}
				//print "got keys (".print_r($relation).")<br>\n";
				//print "got text(".$this->html_text.")<br>";
				$this->html_text = preg_replace(array_keys($relation), array_values($relation), $this->html_text);
				$this->plain_text = preg_replace(array_keys($relation), array_values($relation), $this->plain_text);
				$this->subject = preg_replace(array_keys($relation), array_values($relation), $this->subject);
				//print "got text2(".$this->html_text.")<br>";
	
				$strHTMLEmail = $this->getHTMLEmail($footer);
				$strHTMLPlain = $this->getPlainEmail($footer);

				// Add an unsub link replacer for the footer if we have one
				if ($footer) {
					$tmp = array('/href="http:\/\/unsubs:email=xxx"/'=>'href="'.$site->root.'/enewsletters/?action=unsubscribe&email='.$email.'"');
					$this->msg[] = "Add unsub email to footer";
					$strHTMLEmail = $this->setData($tmp, $strHTMLEmail);
				}
			
				// Need to fix placeholders in content next
				//print "send from($from) subject($subject)<br>";
				$tmp_contact_email = $site->contact['email']?$site->contact['email']:$site->getConfig("contact_recipient_email");
				$from = '"'.$site->title.'"<'.$tmp_contact_email.'>';
				$this->msg[] = "Set from($from) sc(".$site->contact['email'].") sgc(".$site->getConfig("contact_recipient_email").")";
				
				$mail = new htmlMimeMail();
				$mail->setFrom($from);
				//$mail->setBcc("phil.redclift@ichameleon.com");
				$mail->setReturnPath($from);
				$mail->setSubject($this->subject);
				$mail->setHtml($strHTMLEmail, $strHTMLPlain, null);
				$mail->is_built = false;
				$result = $mail->send(array($email));
				
				if ($testing) print "<!-- sent message to ($email) -->\n";
				// Stop sending copy emails when successful....
				$this->msg[]="Send text to ".$email;
				if ($testing) print "<!-- msg: ".print_r($this->msg, 1)."-->\n";
				unset($this->msg);
			}
			else $this->msg[]="email content is not valid";
		}
				
		// Probably should have a loggin facility to we can trace all sendText calls....
		// we cant really do anything on failure as this process could be run in lots of places....
		if ($this->msg) $this->mailMsg("phil@treelinesoftware.com", $site->name." send text summary");
		return $result;	
	}
	
	private function mailMsg($to, $subject) {
		global $site;
		$headers = "From: ".$site->title." <".$site->name."@ichameleon.com>\n";
		$headers.= "\n";
		
		//print "to($to) send ".print_r($this->msg, true)."<br>\n";
		if (is_array($this->msg)) {
			foreach ($this->msg as $m) {
				$msg.=$m."\n";
			}
		}
		else $msg.=$this->msg."\n";
		if ($msg) {
			//print "Sending now.<br>\n";
			mail($to, $subject, getcwd()."\n\n".$msg, $headers);
		}
	}
}


?>