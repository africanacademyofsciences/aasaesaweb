<?
/*
=====================
Event Class
---------------------
=====================
*/
class Event {

	public $id;
	
	public $start_day, $start_month, $start_year;
	public $end_day, $end_month, $end_year;
	public $start_time, $end_time;
	
	public $prev_guid, $next_guid;
	
	//public $cutoff_day, $cutoff_month, $cutoff_year;
	public $title;
	
	public $location;
	public $capacity;				// Basically the same as the number of tickets.
	public $offline_alloc;			// This is the number of tickets allocated offline
	public $invite;					// Should people be allowed to request a ticket via the website
	public $price;					// Ticket cost
	
	public $error=array();
	
	private $published=false;
	
	public function __construct($event_guid='') {
		// This is loaded when the class is created	
		if ($event_guid!='') {
			$this->load($event_guid);
		}
	}
		
	public function load($event_guid) {
		global $db;

		if (!$event_guid) return 0;
		$query="select e.*,  
		date_format(start_date, '%D %b %Y') as nice_start_date,
		date_format(end_date, '%D %b %Y') as nice_end_date,
		date_format(start_date, '%d') as start_day, 
		date_format(start_date, '%m') as start_month, 
		date_format(start_date, '%Y') as start_year,
		date_format(end_date, '%d') as end_day, 
		date_format(end_date, '%m') as end_month, 
		date_format(end_date, '%Y') as end_year,
		substr(start_time,1,2) as start_hour,
		substr(start_time,4) as start_minute,
		substr(end_time,1,2) as end_hour,
		substr(end_time,4) as end_minute,
		e.capacity-e.offline AS total_tickets,
		e.price AS price,
		p.title, p.hidden, p.locked
		from events e
		left join pages p on p.guid=e.guid
		WHERE e.guid='$event_guid'";
		//print "$query<br>\n";
		//print "<!-- load event($event_guid) $query --> \n";
		
		if ($row=$db->get_row($query)) {
			//$this->member_id=$row->member_id;
			$this->id = $row->guid;
			$this->title = $row->title;
			
			$this->prev_guid = $row->prev_guid;
			$this->next_guid = $row->next_guid;
			
			$this->start_date = $row->nice_start_date;
			$this->end_date = $row->nice_end_date;
			$this->start_day=$row->start_day;
			$this->start_month=$row->start_month;
			$this->start_year=$row->start_year;
			$this->end_day=$row->end_day;
			$this->end_month=$row->end_month;
			$this->end_year=$row->end_year;
			
			$this->start_time = $row->start_time;
			$this->end_time = $row->end_time;
			$this->start_hour = $row->start_hour;
			$this->start_minute = $row->start_minute;
			$this->end_hour = $row->end_hour;
			$this->end_minute = $row->end_minute;
			
			$this->location=$row->location;
			$this->capacity=$row->capacity;
			$this->offline=$row->offline;
			$this->total_tickets=$row->total_tickets;
			$this->price = $row->price;
			$query = "SELECT count(*) FROM event_entry WHERE event_guid='".$this->id."' AND registered>=0";
			//print "$query<br>\n";
			$this->booked = $db->get_var($query);
			$this->invite=$row->invite;
			return 1;
		}
		// Its not a problem if this fails, sometimes we just load a guid on spec
		// in case its an event and we need the object later.
		else {
			
			//print "Failed to load event<br>\n";
		}
		return 0;
	}

	// Check if an event record exists and if not create one
	public function create($guid) {
		global $db;
		if (!$guid) return false;

		// Create the directory for tickets if it dont exist already
		//print "check if ($dir) exists<br>\n";
		$dir=$_SERVER['DOCUMENT_ROOT']."/silo/pdf/events/".$guid;
		if (!file_exists($dir))
		{
			//print "create ticket dir($dir)<br>\n";
			@mkdir($dir);
			@chmod($dir, 0777);
		}
		
		$query="select count(*) from events where guid='$guid'";
		if ($db->get_var($query)==0) {
			$query="insert into events (guid) values ('$guid')";
			return $db->query($query);
		}
		return true;
	}
	
	// FUNCTION createPage.
	// Creates a new record in the pages table.
	// Required by the website when someone needs to generate their own event
	// All events are created by Treeline only.
	public function createPage($data) {
		global $db;
		//print "createPage(";
		//print_r($data);
		//print ")<br>";
		$newPage=new Page();
		$newPage->setParent($db->get_var("select guid from pages where name='events'"));
		$newPage->setTitle($db->escape(htmlentities($data['title'])));
		$newPage->setTemplate(19);
		$newPage->setLocked(1);
		$newPage->setStyle(1);
		$newPage->setPageType(1);
		$newPage->setMetaDescription('');
		$name = $newPage->generateName();
		if (!$name) return 'An event by this title already exists. Please use a different title for your event';
		else {
			$newPage->setHidden(1);
			$newPage->setSortOrder();					
			if($newPage->create()) $this->id=$newPage->getGUID();
			else return "Failed to create the event page";
		}
		return 0;	// Success
	}

	// 21st Jan 2009 - Phil Redclift
	// Draw any event related stuff that needs to appear on the website
	public function drawEventInfo() {
		global $page;
		
		$show_places = false;	// Set this to switch on/off showing remaining places.
		
		//print "Dates : ".$this->start_date." to ".$this->end_date."<br>\n";
		$html = '<div id="event-info">';
		if ($this->location) $html.='<p>Event location: <strong>'.$this->location.'</strong></p>';
		if ($this->prev_guid || $this->next_guid) {
			$html.='<p>Series: ';
			if ($this->prev_guid) $html.='<a href="'.$page->drawLinkByGUID($this->prev_guid).'">Previous event</a> -> ';
			$html.=$this->title;
			if ($this->next_guid) $html.=' -> <a href="'.$page->drawLinkByGUID($this->next_guid).'">Next event</a>';
		}
		
		if ($this->start_date && $this->end_date && $this->start_date!=$this->end_date) $html.='<p>Runs: From '.$this->start_date.' to '.$this->end_date.'</p>';
		else if ($this->start_date && $this->end_date && $this->start_date==$this->end_date) $html.='<p>Date: '.$this->start_date.'</p>';
		
		if ($this->start_time && $this->end_time && $this->start_time!="00:00" && $this->end_time!="00:00") $html.='<p>Event time: '.$this->start_time.' to '.$this->end_time.'</p>';
		else if ($this->start_time && $this->start_time!="00:00") $html.='<p>Start time: '.$this->start_time.'</p>';
		
		// Do we need to think about tickets.
		if (!$this->invite) {
		
			$html .= '<p class="evt-price">Ticket price: &pound;'.number_format($this->price, 2).'</p>';
			
			// 1 calculate how many tickets spare
			$spare_tickets = $this->total_tickets - $this->booked;
			$pc_spare=0;
			if ($this->total_tickets) $pc_spare = floor(($spare_tickets/$this->total_tickets)*100);
			//print "total tickets ".$this->total_tickets." booked ".$this->booked." spare ".$spare_tickets." pc spare ".$pc_spare."%<br>\n";
			if (!$pc_spare) {
				if ($show_places) {
					$ticket_html='There are no places left on this event';
				}
			}
			else {
				$ticket_html='<a href="'.$page->drawLinkByGUID($this->id).'?register=1&amp;id='.$_SESSION['member_id'].'">Attend this event</a>';
				if ($pc_spare<10 && $show_places) $ticket_html.=' (There are limited places left for this event)';
			}
			if ($ticket_html) $html.='<p class="evt-tickets">Tickets: '.$ticket_html.'</p>';
		}
		$html.='</div>';
		return $html;
	}
	
	
	public function login($user, $pass, $pp_guid) {
		global $db;
		//print "login($user, $pass, $pp_guid)<br>";
		$member_id=0;
		if ($user && $pass && $pp_guid) {
			$_SESSION['user_logged_in']=0;
			$query="SELECT m.member_id, ee.registered FROM members m
				LEFT OUTER JOIN event_entry ee ON m.member_id=ee.member_id
				WHERE m.email = '$user' AND m.password='$pass' and ee.pp_guid='$pp_guid'";
			//print "<!-- $query --> \n";
			if ($row=$db->get_row($query)) {
				$member_id = $row->member_id;
				$this->pp['registration_complete']=($row->registered==1);
			}
		}
		//print "logged in member($member_id), this page belongs to(".$this->pp['member_id'].")<br>";
		return ($member_id+0);
	}
	
	public function sendPassword($user) {
		global $db, $site;
		$query="select email, password from members where email = '$user'";
		//print "$query<br>";
		if ($row=$db->get_row($query)) {
			$headers="";
			$subject="Access to your ".$site->name." personal page";
			$body="Thank you for using the ".$site->name." personal page password retrieval system
			
Your password is ".$row->password."

".$site->name." Events team
";
			mail($row->email, $subject, $body, $headers);
		}
		return;
	}

	// 6th February 2009 - Phil Redclift
	// Create a PDF ticket for this event and save it to disk.
	// Then either send the ticket to the entrant or log a task for someone else to print out and mail the ticket.
	public function sendTicket($eid) {
		global $db, $site;
		//print "sT($eid)<br>\n";
		
		$ticket_filename = "/events/".$this->id."/ticket-".$eid.".pdf";
		
		// This ticket should not already exist but if so create a new one.
		$ticket_path = $_SERVER['DOCUMENT_ROOT']."/silo/pdf".$ticket_filename;
		if (file_exists($ticket_path)) {
			unlink($ticket_path);
		}
		
		//print "create ticket<br>\n";
		$ticket_html = '<html>
<head>
</head>
<body>
<h1 style="font-size:350%;">Ticket</h1>

<h1>'.$this->title.'</h1>

<p>We look forward to seeing you at the event</p>
<p>'.$site->title.' events team</p>
</body>
</html>
';
		//print "generatePDF($ticket_html, $ticket_filename)<br>\n";
		if (generatePDF($ticket_html, $ticket_filename)===true) {
		
			// if entrant has an email addy then send the ticket to them
			$row = $db->get_row("SELECT email, address FROM event_entry_data where entry_id=$eid");

			$send_data=array("SITE_NAME"=>$site->title,
				"EVENT_NAME"=>$this->title,
				"EVENT_TITLE"=>$this->title,
				"EVENT_TICKET"=>'<a href="'.($_SERVER['HTTP_HOST']."/silo/pdf/".$ticket_filename).'">download ticket</a>',
				"MEMBER_ADDRESS"=>nl2br($row->address)
				);

			if ($row->email) {
				$newsletter = new Newsletter();
				$newsletter->sendText($row->email, "EVENT-ATTEND", $send_data);
			}
			else {
				// Need to add a notification tasky thing and email all the superusers.....
				$tasks=new Tasks($site->id);
				if ($tasks->add(0, 'Event ticket', $this->id, "ENTRY ID:".$eid, 3, 0)) {
					$tasks->notify("EVENT-TICKET-PRINT", $send_data, 'Author');
				}
			}
			return true;
			
		}
		return false;
	}
	
	
	public function update($guid, $data) {
		global $db;

		if (!$this->create($guid)) return false;
		
		$prev_guid = $data['prev_series'];
		//print "got prev guid($prev_guid)<br>\n";
		$start_date=$data['start_year'].'-'.$data['start_month'].'-'.$data['start_day'];
		$end_date=$data['end_year'].'-'.$data['end_month'].'-'.$data['end_day'];

		$start_time=$data['start_hour'].':'.$data['start_minute'];
		$end_time=$data['end_hour'].':'.$data['end_minute'];

		//$cutoff_date="'".$data['cutoff_year'].'-'.$data['cutoff_month'].'-'.$data['cutoff_day']."'";
		//if ($cutoff_date=="'--'") $cutoff_date="'".$end_date."' + INTERVAL 8 WEEK";
		
		// Make sure the rest of the data is valid
		$price=number_format($data['price']>0?$data['price']:0, 2);
		//$min_sponsorship=($data['min_sponsorship']>0)?$data['min_sponsorship']:0;
		//$show_sponsorship=($data['show_sponsorship']>0)?$data['show_sponsorship']:0;
		$location = $data['location'];
		$capacity = ($data['capacity']>0)?$data['capacity']:0;
		$offline = ($data['offline_alloc']>0)?$data['offline_alloc']:0;
		$invite=($data['invite']==1)?1:0;
		
		//$email=$this->checkValidEmail($data['email'])?$data['email']:'';
		//$this->title=$db->escape($data['title']);
		
		$query="update events set ".($prev_guid?"prev_guid = '$prev_guid'":"prev_guid = NULL").", 
			start_date='$start_date', end_date='$end_date', 
			start_time='$start_time', end_time='$end_time',
			location = '".$db->escape($location)."', capacity=".($capacity+0).", 
			invite=".($invite+0).", offline=".($offline+0).", price = ".$price."
			where guid='$guid'";
		//print "update event($query)<br>";
		if ($db->query($query)) {

			// If we have a previous event we need to update this event also
			// in case its next_guid has changed,
			if ($prev_guid) {
				$query = "UPDATE events SET next_guid='$guid' 
					WHERE guid='$prev_guid'";
				$db->query($query);
			}
			// Otherwise this event is NOT part of a series so we need to 
			// ensure no other events have this registered as their next event 
			else {
				$query = "UPDATE events SET next_guid = NULL
					WHERE next_guid='$guid'";
				$db->query($query);
			}
			return true;
		}		
		return false;
	}
	
	// VALIDATE EMAIL ADDRESS	
	function checkValidEmail($email){

		//copyright - http://www.ilovejackdaniels.com/php/email-address-validation
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!ereg("^(([A-Za-z0-9!$%&'*+/=?^_`{|}~-][A-Za-z0-9!$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
				return false;
			}
		}
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					return false;
				}
			}
		}
		return true;
	}
	
	public function drawSelectList($name, $all=false, $selecttext='') {
		global $db, $site;
		//print "dSL($name) guid(".$this->id.")<br>\n";
		$query="SELECT p.guid, p.title 
			FROM events e
			INNER JOIN pages p ON e.guid=p.guid
			WHERE p.locked=0 
			AND p.template=19 
			AND msv=".$site->id." ";
		if (!$all) $query.="AND e.end_date > NOW() ";
		//print "$query<br>";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				$html.='<option value="'.$result->guid.'"'.(($result->guid==$this->id)?" selected":"").'>'.$result->title.'</option>';
			}
			if ($html) $html='<select name="'.$name.'"><option value="">'.($selecttext?$selecttext:"Select event").'</option>'.$html.'</select>';
		}
		return $html;
	}

	// Function to allocate a place on an event to a member.
	// Returns
	// 0 on failure and sets the event->error array
	// a postive integer representing the entry ID on success
	public function addMember($member_id, $own_event=false, $grp_title='') {
		
		global $db;

		unset($this->error);
		
		// Ensure we have a valid event to add them to
		if (!$this->id) {
			$this->error[]="No event loaded, cannot add member";
			return 0;
		}
		// Check we have valid member id to add to the event.	
		if (!($member_id>0)) {
			$this->error[]="Invalid member ID passed, cannot add member($member_id)";
			return 0;
		}
		// Check this member is not already participating in this event
		$entry_id=$db->get_var("SELECT id FROM event_entry ee WHERE member_id=$member_id AND event_guid='".$this->id."'");
		if ($entry_id>0) {
			$this->error[]="This member is already subscribed to the event";
			
			// Normally we would leave here as we dont want the same member
			// Joining the same event twice.
			if (0) {
				return 0;
			}
		}
		
		$newPage = new Page;
		$newsletter = new Newsletter;
		
		// Collect member data 
		// We may not need to set the title as we are not going to show any page related to this entry
		$mem_row=$db->get_row("select * from members where member_id=$member_id");


		if ($mem_row->firstname) $title=$mem_row->firstname;
		if ($mem_row->surname) {
			if ($title) $title.=" ";
			$title.=$mem_row->surname;
		}
		
		// Add an event entry record for this participant
		$query="INSERT INTO event_entry 
			(member_id, event_guid, registered)
			VALUES 
			($member_id, '".$this->id."', 0)
			";
		//print "$query<br>";
		if ($db->query($query)) {
			$entry_id=$db->insert_id;

			// Add an event entry data record for this participant
			// This record will keep all the data entered on their event appliation form?
			$query="INSERT INTO event_entry_data (entry_id) VALUES ($entry_id)";
			$db->query($query);
			
			// Create a ticket for this member to this event.
			$filename=$this->id."-".$member_id."-".date("dmYhis",time()).".pdf";
			$ticketHTML = '<html>
<head>
<style type="text/css">
p.address {
	font-family:arial;
	text-align:center;
	font-weight: bold;
	font-size: 25px;
}
</style>
</head>
<body>
<div style="border: 1px solid #000;">
<p class="address">'.$title.'</p>
<p class="address">Address line 1</p>
<p class="address">Address line 2</p>
<p class="address">County</p>
<p class="address">Postcode</p>
</div>
<p>Its not at all clear to me why this is not writing all of my stuff out to the file. looks like it needs some serious debugging :o(</p>
</body>
</html>
';
			if (!generatePDF($ticketHTML, $filename)) {
				$this->error[]="Failed to create a ticket for this entry";
				return 0;
			}
			
			// Send a message to the participant
			if ($mem_row->email) {
				$this->pp['guid']=$newPage->getGUID();
				$pp_link='<a href="'.$newPage->drawLinkByGUID($newPage->getGUID()).'">personal page</a>';

				$reg_link='<a href="'.$newPage->drawLinkByGUID($this->id).'?register&amp;id='.$member_id.'">registration form</a>';
	
				$host="http://".$_SERVER['HTTP_HOST'];
				if (substr($host,-1,1)!="/") $host.="/";

				$send_data=array("TITLE"=>$this->title, 
					"REGISTRATIONLINK"=>$reg_link,
					"PERSONALPAGELINK"=>$pp_link,
					"FIRST"=>$mem_row->firstname,
					"SURNAME"=>$mem_row->surname,
					"REGISTRATIONFEE"=>number_format($this->fee,2),
					"HOMEPAGELINK"=>'<a href="'.$host.'">click here</a>',
					"EVENTPAGELINK"=>'<a href="'.$host.'events/">click here</a>',
					"SHOPLINK"=>'<a href="'.$host.'shop/">click here</a>'
					);
				//print_r($send_data);
				$newsletter->sendText($mem_row->email, "PARTICIPATE".($own_event?"_FUNDRAISING":""), $send_data);
			}
			else {
				$this->error[]="Event was set up but no email address found to send confirmation email?";
				return 0;
			}
			
			return $entry_id;
		}
		
	}
	
	
	// HTML FOR FORM	
	function drawForm($data=array(), $template=0){
		global $action, $help;

		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/formDate.class.php');
		$formDate = new formDate();
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/events/getCurrentDate.inc.php');

		$start_day=count($data)?$data['start_day']:$this->start_day;
		$start_month=count($data)?$data['start_month']:$this->start_month;
		$start_year=count($data)?$data['start_year']:$this->start_year;
		$end_day=count($data)?$data['end_day']:$this->end_day;
		$end_month=count($data)?$data['end_month']:$this->end_month;
		$end_year=count($data)?$data['end_year']:$this->end_year;
		
		$start_hour = count($data)?$data['start_hour']:$this->start_hour;
		$start_minute = count($data)?$data['start_minute']:$this->start_minute;
		$end_hour = count($data)?$data['end_hour']:$this->end_hour;
		$end_minute = count($data)?$data['end_minute']:$this->end_minute;
	
		$event_location=count($data)?$data['location']:$this->location;
		$event_capacity=count($data)?$data['capacity']:$this->capacity;
		$event_offline=count($data)?$data['offline_alloc']:$this->offline;
		$event_price=count($data)?$data['price']:$this->price;
	//$event_organiser=count($data)?$data['organiser']:$this->organiser;
	//$event_email=count($data)?$data['email']:$this->email;
	//$min_sponsorship=count($data)?$data['min_sponsorship']:$this->min_sponsorship;
	//$show_sponsorship=count($data)?$data['show_sponsorship']:$this->show_sponsorship;
	//$pp_title=count($data)?$data['pp_title']:$this->pp_title;
	//$title=count($data)?$data['evt_title']:$this->title;

		$invite = count($data)?$data['invite']:$this->invite;
	
//}
//print "start($start_day - $start_month - $start_year)<br>";
//p/rint "start($end_day - $end_month - $end_year)<br>";
//print "start($cutoff_day - $cutoff_month - $cutoff_year)<br>";

		$currentYear=date("Y", time());

		$ef_html = '
	
<div>
	<label for="form_series_prev">Previous event:</label>
	<select name="prev_series" id="form_series_prev">
		<option value="">Not part of a series</option>
		'.$this->drawSeriesList().'
	</select>
</div>
	
<div>
	<label for="start_day">Event date:</label>
	'.$formDate->getDay('start_day',$start_day).'
	'.$formDate->getMonth('start_month',$start_month).'
	'.$formDate->getYear('start_year',$start_year, 2, 'future',NULL, $currentYear).'
</div>
	
	<div>
		<label for="end_day" >Ending date:</label>
		'.$formDate->getDay('end_day',$end_day).'
		'.$formDate->getMonth('end_month',$end_month).'
		'.$formDate->getYear('end_year',$end_year, 2, 'future',NULL, $currentYear).'
	</div>
	
	<div>
		<label for="start_hour">Time</label>
		'.$formDate->getHour('start_hour', $start_hour).'
		'.$formDate->getMinute('start_minute', $start_minute, 5).'
		<span style="float:left;margin:5px;margin-left:0;padding-top:3px;"><strong>until</strong></span>
		'.$formDate->getHour('end_hour', $end_hour).'
		'.$formDate->getMinute('end_minute', $end_minute, 5).'
	</div>
	
	<div>
		<label for="form_location">Location</label>
		<input type="text" name="location" id="form_location" value="'.$event_location.'" />
	</div>
	
	<div>
    	<label for="f_price">Tickets (�):</label>
        <input type="text" name="price" id="f_price" value="'.$event_price.'" />
	</div>

	<div>
		<label for="form_capacity">Capacity</label>
		<input type="text" name="capacity" id="form_capacity" value="'.$event_capacity.'" />
	</div>
	<div>
		<label for="form_offline">Offline tickets'.$help->drawSmallPopupByID(90).'</label>
		<input type="text" name="offline_alloc" id="form_offline" value="'.$event_offline.'" />
	</div>
	<div>
		<label>Invitation only</label>
		<input id="event_noinvite" type="radio" name="invite" value="0" '.($invite!=1?'checked="checked"':"").' style="width:40px;" />
		<label style="clear:none;width:40px;margin:4px 0;" for="event_noinvite">No</label>
		<input id="event_yesinvite" type="radio" name="invite" value="1" '.($invite==1?'checked="checked"':"").' style="width:40px;" />
		<label style="clear:none;width:40px;;margin:4px 0;" for="event_yesinvite">Yes</label>
	</div>

';
	return $ef_html;
	
        /*
		<!--
    	<label for="form_organiser">Organiser:</label>
        <input type="text" name="organiser" id="form_organiser" value="<?=$event_organiser?>" />
    	<label for="fom_email">Organiser email:</label>
        <input type="text" name="email" id="form_email" value="<?=$event_email?>" />

        <div class="hasHelp">
	    	<label for="form_min_sponsorship">Min Sponsorship (�):</label>
    	    <input type="text" name="min_sponsorship" id="form_min_sponsorship" value="<?=$min_sponsorship?>" />
            <span class="help">The minimum amount a participant commits to raise when they sign up for this event</span>
		</div>

        <div class="hasHelp">
	    	<label for="form_show_sponsorship">Display threshold (�):</label>
    	    <input type="text" name="show_sponsorship" id="form_show_sponsorship" value="<?=$show_sponsorship?>" />
            <span class="help">Minimum amount of sponsorship required to display total on participant pages</span>
		</div>

        <div class="hasHelp">
	    	<label for="form_pp_title">Title text:</label>
    	    <input type="text" name="pp_title" id="form_pp_title" value="<?=$pp_title?>" />
            <span class="help">This text will appear on personal pages next to participants names</span>
		</div>
        -->
		*/
		        
	}
	
	// If the master event has never been published then we should not be able 
	// to view this page.....
	function is_published() {
		global $db;
		return $db->get_var("select date_published from pages where guid='".$this->id."'");
	}
	
	// 22nd Jan 2009 - Phil Redclift
	// This function draws a list of events that could preceed this event in a series.
	// I am not sure what the criteria for this is yet so I will allow all 
	// events from the same site that have not finished or finish within the past 3 months
	// to be included for now.
	function drawSeriesList() {
		global $db, $site;
		$query="SELECT p.guid, p.title 
			FROM pages p
			LEFT JOIN events e ON p.guid=e.guid
			WHERE p.template=19 
			AND e.end_date > NOW() - INTERVAL 3 MONTH
			AND p.guid <> '".$this->id."' AND msv=".$site->id;
		//print "$query<br>\n";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				$html.='<option value="'.$result->guid.'"'.($result->guid==$this->prev_guid?' selected="selected"':"").'>'.$result->title.'</option>';
			}
		}
		return $html;
	}
}

?>