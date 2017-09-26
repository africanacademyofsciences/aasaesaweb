<?php
	$feedback='';
	// Do we need to run any searchings?
	if ($_SERVER['REQUEST_METHOD']=="POST") {

		
		include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/event.class.php");
		include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
		include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
		include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
		include ($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
		$event = new Event();
		$user = new User();
		$user->loadById(1);	// Create events pages under the ID of ichameleon
		
		$ev_name=$_POST['organiser'];
		$ev_email=$_POST['email'];
		$ev_title=$_POST['title'];
		$ev_day=$_POST['start_day'];
		$ev_month=$_POST['start_month'];
		$ev_year=$_POST['start_year'];
		$ev_start=mktime(23,59,00, $ev_month, $ev_day, $ev_year);
		$ev_raise=$_POST['min_sponsorship'];
		
		if ($ev_day>31) $err_msg[]="Invalid date entered";
		if ($ev_start < time()) $err_msg[]="Event start must be in the future, date entered has already passed";
		if (!$ev_title) $err_msg[]="You must enter a title for you new event";
		if (!$ev_name) $err_msg[]="You must enter your name";
		if (!$ev_email) $err_msg[]="You must enter you email address";
		else if (!$event->checkValidEmail($ev_email)) $err_msg[]="Your email address appears to be invalid. Please check your email address is correct";
		else {
			$query = "SELECT member_id FROM members WHERE email = '".$ev_email."'";
			if (!$member_id = $db->get_var($query)) {
				$subscriber=new Subscriber();
				$subscriber->set("email", $ev_email);
				$subscriber->setName($ev_name);
				if (!$member_id=$subscriber->createNew($ev_name, $ev_email)) {
					$err_msg.="You are not subscribed to ".$site->name." yet and we were unable to add you as a subscriber at this time";
				}
			}
		}
		//print "create new event for member($member_id)<br>";
		
		if (!count($err_msg)) {
			$create_err_msg=$event->createPage($_POST);
			if (!$create_err_msg) {
				// Success, entry in pages table was created....
				$_POST['end_year']=$_POST['start_year'];
				$_POST['end_month']=$_POST['start_month'];
				$_POST['end_day']=$_POST['start_day'];
				if ($event->update($event->id, $_POST)) {
					$member_err_msg=$event->addMember($member_id, true);
					if ($member_err_msg>0) {
						$err_msg="Your event has been submitted. You will receive an email shortly telling you what you need to do next";
						$feedback="success";
						// Also need to send an email to admin to inform them that the new event and PP 
						// have been set up and they need to log in and approve the event.
						$host="http://".$_SERVER['HTTP_HOST'];
						if (substr($host,-1,1)!="/") $host.="/";

						$send_data=array("PERSONALPAGELINK"=>$event->pp['guid'],
							"NAME"=>$ev_name,
							"TITLE"=>$ev_title,
							"HOMEPAGELINK"=>'<a href="'.$host.'">click here</a>',
							"EVENTPAGELINK"=>'<a href="'.$host.'events/">click here</a>',
							"SHOPLINK"=>'<a href="'.$host.'shop/">click here</a>'
							);
						//print_r($send_data);
						$notify_email="events@maginternational.org";
						//$notify_email="phil.redclift@ichameleon.com";
						$newsletter = new Newsletter();
						$newsletter->sendText($notify_email, "EVENT_NOTIFY", $send_data);
					}
					else $err_msg[]=$member_err_msg;
				}
				else $err_msg[]="Failed to create or update the event record";
			}
			else $err_msg[]=$create_err_msg;
		}
	}
	
	//$amonths=array("January","Febrary","March","April","May","June","July","August","September","October","November","December");
	$amonths=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	$curyear=date("Y", time());
	foreach($amonths as $month) {
		$mid++;
		$month_opts.='<option value="'.(($mid>9)?$mid:"0".$mid).'"'.(($mid==$ev_month)?" selected":"").'>'.$month.'</option>';
	}
	
//print "got year($curyear)<br>";	
	for($year=$curyear; $year<($curyear+4); $year++) {
//		print "add year($year)<br>";
		$year_opts.='<option value="'.$year.'"'.(($year==$ev_year)?" selected":"").'>'.$year.'</option>';
	}		

	if(!$_POST['e_day'])$_POST['e_day']="01"
?>

<?php
	if ($err_msg) {
		if (!$feedback) $feedback="error";
		echo drawFeedback($feedback, $err_msg);
	}
?>


<?php
	if ($feedback!="success") {
	?>
    <form id="event-search-form" action="<?=$page->drawLinkByGUID($page->getGUID())?>" class="contact" method="post">
    <fieldset class="border">
        <legend>Add fundraising event</legend>
        <div class="ie-fix">
            <label for="f_name">Name:</label>
            <input type="text" name="organiser" id="f_name" class="text" value="<?=$_POST['organiser']?>" /><br />
            <label for="f_email">Email:</label>
            <input type="text" name="email" id="f_email" class="text" value="<?=$_POST['email']?>" /><br />
            <label for="f_event">Event title:</label>
            <input type="text" name="title" id="f_event" class="text" value="<?=$_POST['title']?>" /><br />
            <label for="f_event_date">Event date:</label>
            <input type="text" name="start_day" id="f_event_date" class="date" value="<?=$_POST['start_day']?>" />
            <select class="date" name="start_month" id="f_event_month"><?=$month_opts?></select>
            <select name="start_year" id="f_event_year" class="date"><?=$year_opts?></select><br />
            <label for="f_raise">I plan to raise £:</label>
            <input type="text" name="min_sponsorship" id="f_raise" class="text" value="<?=$_POST['min_sponsorship']?>" /><br />
            <div class="ie-fix2">
                <label for="f_submit" style="visibility:hidden;">Submit</label>
                <input type="submit" class="submit" value="Add event" />
            </div>
        </div>
    </fieldset>
    </form>
	<?php
	}
?>

