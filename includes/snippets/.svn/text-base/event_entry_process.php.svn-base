<?php

//ini_set("display_errors", true);
//error_reporting(E_ALL);

	//print "processing event entry form...<br>";
	// Process !_POST variables from the event form
	// Validate and add to database if ok.
	// set $message[] if not ok.
	
	$evt_config=$db->get_row("select * from event_config where guid='".$event->id."'");

	// If no entry ID has been passed we need to create the entry and entry data records
	if (!$ef_entry_id) {
		// Add an event entry record for this participant
		$query="INSERT INTO event_entry 
			(member_id, event_guid, registered)
			VALUES 
			(".($_SESSION['member_id']+0).", '".$event->id."', 0)
			";
		//print "$query<br>";
		if ($db->query($query)) {
			$ef_entry_id=$db->insert_id;

			// Add an event entry data record for this participant
			// This record will keep all the data entered on their event appliation form?
			$query="INSERT INTO event_entry_data (entry_id) VALUES ($ef_entry_id)";
			$db->query($query);
		}
	}	
	
	$ef_title=$_POST['title'];
	$ef_forenames=$_POST['forenames'];
	$ef_surname=$_POST['surname'];
	$ef_prefname=$_POST['prefname'];
	$ef_day_tel=$_POST['day_tel'];
	$ef_mob_tel=$_POST['mob_tel'];
	$ef_dob=$_POST['dob'];
	$ef_email=$_POST['email'];
	$ef_address=$_POST['address'];
	$ef_nationality=$_POST['nationality'];
	$ef_hearabout=$_POST['hearabout'];
	$ef_sex=$_POST['sex'];
	
	$ef_pass_number=$_POST['pass_number'];
	$ef_pass_country=$_POST['pass_country'];
	$ef_pass_dob=$_POST['pass_dob'];
	$ef_pass_pob=$_POST['pass_pob'];
	$ef_pass_issue=$_POST['pass_issue'];
	$ef_pass_expiry=$_POST['pass_expiry'];

	$ef_food=$_POST['food'];
	$ef_tickets=$_POST['tickets'];
	$ef_height=$_POST['height'];
	$ef_ladies=$_POST['ladies'];
	$ef_topsize=$_POST['topsize'];

	$ef_accom=$_POST['accom'];
	$ef_frnd_name=$_POST['frnd_name'];
	$ef_frnd_email=$_POST['frnd_email'];
	$ef_frnd_add=$_POST['frnd_add'];
	
	$ef_no_news=$_POST['no_news'];
	
	$cb_terms_required=$_POST['cb_terms_required'];
	foreach ($_POST as $k=>$v) {
		if (substr($k, 0, 6)=="cb_tnc") {
			$cb_terms.=$v.",";
		}
	}
	
	if (!$ef_title) $err.="<li>You must enter your title</li>";
	else {	
		$mem_sql.="title='".$db->escape($ef_title)."',";
		$ev_sql.="title='".$db->escape($ef_title)."',";
	}
	
	if (!$ef_forenames) $err.="<li>You must enter your forenames</li>";
	else $ev_sql.="forenames='".$db->escape($ef_forenames)."',";
	
	if (!$ef_surname) $err.="<li>You must enter your surname</li>";
	else $ev_sql.="surname='".$db->escape($ef_surname)."',";
	
	if (!$ef_address && (!$ef_email || !is_email($ef_email))) $err.="<li>You must enter your address or a valid email address</li>";
	else {
		if ($ef_address) $ev_sql.="address='".$db->escape($ef_address)."',";
		if ($ef_email) $ev_sql.="email='".$db->escape($ef_email)."',";
	}

	if (!$ef_day_tel && !$ef_mob_tel) $err.="<li>You must enter your phone number or your mobile number</li>";
	else {
		if ($ef_day_tel) $ev_sql.="day_tel='".$db->escape($ef_day_tel)."',";
		if ($ef_mob_tel) $ev_sql.="mob_tel='".$db->escape($ef_mob_tel)."',";
	}
	
	/*
	if (!$ef_dob) $err.="<li>You must enter your date of birth</li>";
	else $ev_sql.="dob='".$db->escape($ef_dob)."',";

	if (!$ef_nationality) $err.="<li>You must enter your nationality</li>";
	else $ev_sql.="nationality='".$db->escape($ef_nationality)."',";
	
	if (!$ef_sex) $err.="<li>You must enter your gender</li>";
	else $mem_sql.="gender='$ef_sex',";
	*/
	
	/*
	if ($evt_config->chk_passport) {
		if (!$ef_pass_number) $err.="<li>You must enter your passport number</li>";
		else $ev_sql.="pass_number='".$db->escape($ef_pass_number)."',";
	
		if (!$ef_pass_country) $err.="<li>You must enter your passport country of issue</li>";
		else $ev_sql.="pass_country='".$db->escape($ef_pass_country)."',";
	
		if (!$ef_pass_dob) $err.="<li>You must enter your date of birth</li>";
		else $ev_sql.="pass_dob='".$db->escape($ef_pass_dob)."',";
	
		if (!$ef_pass_pob) $err.="<li>You must enter your place of birth</li>";
		else $ev_sql.="pass_pob='".$db->escape($ef_pass_pob)."',";
	
		if (!$ef_pass_issue) $err.="<li>You must enter your passport issue date</li>";
		else $ev_sql.="pass_issue='".$db->escape($ef_pass_issue)."',";
	
		if (!$ef_pass_expiry) $err.="<li>You must enter your passport expiry date</li>";
		else $ev_sql.="pass_expiry='".$db->escape($ef_pass_expiry)."',";
	}
	*/
	
	//print "go terms($cb_terms) needed($cb_terms_required)<br>";
	if ($cb_terms!=$cb_terms_required) $err.="<li>You must agree to all the terms and conditions</li>";

	//if (!$ef_) $err.="<li>You must enter your </li>";
	//else $ev_sql.="='".$db->escape($ef_)."',";

	if ($err) {	
		$message[]='Entry form submission failed for the following reasons:</strong><ul style="clear:both;">'.$err.'</ul>';
	}

	$ev_sql.="food='".$db->escape($ef_food)."',";
	$ev_sql.="tickets='".($db->escape($ef_tickets)+0)."',";
	//$ev_sql.="prefname='".$db->escape($ef_prefname)."',";
	//$ev_sql.="hearabout='".$db->escape($ef_hearabout)."',";
	//$ev_sql.="specreq='".$db->escape($ef_specreq)."',";
	//$ev_sql.="vegetarian='".($db->escape($ef_vegetarian)+0)."',";
	//$ev_sql.="height='".$db->escape($ef_height)."',";
	//$ev_sql.="ladies='".($db->escape($ef_ladies)+0)."',";
	//$ev_sql.="topsize='".$db->escape($ef_topsize)."',";
	//$ev_sql.="accom='".$db->escape($ef_accom)."',";
	//$ev_sql.="frnd_name='".$db->escape($ef_frnd_name)."',";
	//$ev_sql.="frnd_email='".$db->escape($ef_frnd_email)."',";
	//$ev_sql.="frnd_add='".$db->escape($ef_frnd_add)."',";
	$ev_sql.="cb_terms='$cb_terms',";
	//$ev_sql.="=''".$db->escape($ef_)."',";
	
	// Update event entry and event entry data tables.
	$ev_set = substr($ev_sql, 0, -1);
	if ($ev_sql) {
		$ev_sql="update event_entry_data set ".$ev_set." WHERE entry_id=$ef_entry_id";
		//print "$ev_sql<br>\n";
		$db->query($ev_sql);
		if ($db->last_error) {
			$err_message.="err(".$db->last_error.")".$ev_sql."\n";
		}
		// Success data is complete so update the entry record to show this entry can be processed
		else if (!$err) {

			// Do we need to forward this entry to the shopping basket 
			// have they just registered for tickets?
			if ($ef_tickets>0) {
				include_once ($_SERVER['DOCUMENT_ROOT'] .'/treeline/store/includes/basket.class.php');
				$basket = new Basket($_COOKIE['cartID']);
				if( !isset($_COOKIE['cartID']) && !$_COOKIE['cartID']) $basket->setCookie();
				
				if (!$basket->cartID) $message[]="No cart ID was generated for this entry";
				else {
				
					// Empty the basket of any items already associated with this entry ID
					$query = "DELETE FROM store_orders_events WHERE order_id='".$basket->cartID."' AND entry_id=$ef_entry_id";
					//print "$query<br>\n";
					$db->query($query);
	
					//print "basket->add($ef_entry_id, $ef_tickets, event)<br>\n";
					if ($basket->add($ef_entry_id, $ef_tickets, 'event')) {
						redirect("/shop/shopping-basket");
					}
					else $message[]="Failed to add this item to your shopping cart";
				}
			}
			// We are booking but do not require tickets 
			// therefore this event is free to enter and just need to confirm places.
			else {
				$query = "UPDATE event_entry SET status='Pending' WHERE id=".$ef_entry_id;
				$db->query($query);
			}			

		}
	}

	// If they would like follow up emails about events then add them ....
	if (!$ef_no_news) {

		// Check if this site has an events newsletter preference
		$event_preference=$db->get_var("SELECT preference_id FROM newsletter_preferences WHERE preference_title='Events' AND site_id=".$site->id);
		if ($event_preference>0) {

			// If the email address does not exist in the members database we need to add them to 
			// the members table first.
			$query = "SELECT member_id FROM members WHERE email='".$db->escape($ef_email)."'";
			//print "$query<br>\n";
			$member_id=$db->get_var($query);
			if (!$member_id) {
				$query="insert into members (firstname, surname, title, email, date_added)
					VALUES 
					(
					'".$db->escape($ef_forenames)."', '".$db->escape($ef_surname)."',
					'".$db->escape($ef_title)."', '".$db->escape($ef_email)."',
					NOW() )
					";
				//print "$query<br>\n";
				if ($db->query($query)) $member_id=$db->insert_id;	
			}
			
			// Need to subscribe this person to something or other
			// Going to take a wild guess at the main newsletter?
			// Dont need to check if they are there already as the query will gracefully fail if they are
			if ($member_id>0) {
				$query="insert into newsletter_user_preferences (member_id, preference_id) values (".$_SESSION['member_id'].", $event_preference)";
				@$db->query($query);
			}
		}
	}
	
	if ($err_message) {
		$message[]=$err_message."<br>Event register update failed. Please try again later or inform ".$site->name." support team";
		mail("phil.redclift@ichameleon.com", $site->name." event form submit failure", $err_message);
	}
	
//ini_set("display_errors", false);

	
?>
