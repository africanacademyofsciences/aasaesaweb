<?php

//ini_set("display_errors", true);
//error_reporting(E_ALL);
	


	$member_registered=false;

	$cb_terms=array();
	$form_entry_id=$_REQUEST['entry_id'];
	
	if ($form_mode=="PREVIEW") {
		$form_entry_id=$entry_id;
		if (!$event) $event = new Event($page->getGUID());
	}

	//print "mode($form_mode) entry_id($form_entry_id)<br>\n";

	$query = "select * from event_config where guid='".$event->id."'";
	$evt_config=$db->get_row($query);
	//print "<!-- get config ($query) -->";
	
	//print "got ef_entry_id($ef_entry_id)<br>\n";
	$form_entry_id = 0;
	if ($form_entry_id>0) {
		$query="select 
		m.title as mem_title, m.firstname as mem_forenames, 
		m.surname as mem_surname, m.gender as mem_gender, 
		ee.registered,
		eed.entry_id,
		eed.title, eed.forenames, eed.surname, eed.address, eed.day_tel, eed.mob_tel, 
		eed.email, eed.food, 
		eed.tickets,
		eed.height, eed.ladies, eed.topsize,
		eed.accom, eed.frnd_name, eed.frnd_email, eed.frnd_add, 
		eed.cb_terms,
		eed.cb_agree, eed.cb_spons, eed.cb_health, eed.cb_fotos, eed.cb_money
		FROM events e
		LEFT JOIN event_entry ee ON e.guid=ee.event_guid
		LEFT JOIN event_entry_data eed on ee.id=eed.entry_id
		LEFT OUTER JOIN members m ON ee.member_id=m.member_id
		WHERE ee.id=".$form_entry_id."
		LIMIT 1";
		//print "$query<br>\n";	
		
		if ($row=$db->get_row($query)) {
	
			$member_registered=true;
			
			$ef_entry_id=$row->entry_id;
			if (!$ef_title) $ef_title=$row->title;
			if (!$ef_title) $ef_title=$row->mem_title;
			if (!$ef_forenames) $ef_forenames=$row->forenames;
			if (!$ef_forenames) $ef_forenames=$row->mem_forenames;
			if (!$ef_surname) $ef_surname=$row->surname;
			if (!$ef_surname) $ef_surname=$row->mem_surname;
			if (!$ef_prefnmae) $ef_prefname=$row->prefname;
			if (!$ef_prefname) $ef_prefname=$ef_forenames." ".$ef_surname;
			if (!$ef_address) $ef_address=$row->address;
			if (!$ef_address) {
				if ($row->sab_house) $ef_address=$row->sab_house;
				if ($row->sab_street) $ef_address.=" ".$row->sab_street;
				if ($row->sab_address_2) $ef_address.="\n".$row->sab_address_2;
				if ($row->sab_locality) $ef_address.="\n".$row->sab_locality;
				if ($row->sab_town_city) $ef_address.="\n".$row->sab_town_city;
				if ($row->sab_county) $ef_address.="\n".$row->sab_county;
				if ($row->sab_post_code) $ef_address.="\n".$row->sab_post_code;
				if ($row->sab_country) $ef_address.="\n".$row->sab_country; // Should get the real contry here....
			}
			if (!$ef_email) $ef_email = $row->email;
			if (!$ef_day_tel) $ef_day_tel=$row->day_tel;
			if (!$ef_mob_tel) $ef_mob_tel=$row->mob_tel;
			if (!$ef_dob) $ef_dob=$row->dob;
			if (!$ef_nationality) $ef_nationality=$row->nationality;
			if (!$ef_sex) $ef_sex=$row->mem_gender;
			
			if (!$pass_number) $ef_pass_number=$row->pass_number;
			if (!$pass_country) $ef_pass_country=$row->pass_country;
			if (!$pass_dob) $ef_pass_dob=$row->pass_dob;
			if (!$pass_pob) $ef_pass_pob=$row->pass_pob;
			if (!$pass_issue) $ef_pass_issue=$row->pass_issue;
			if (!$ef_pass_expiry) $ef_pass_expiry=$row->pass_expiry;
	
			if (!$ef_specreq) $ef_specreq=$row->specreq;
			if (!$ef_food) $ef_food=$row->food;
			if (!$ef_tickets) $ef_tickets=$row->tickets;
			if (!$ef_height) $ef_height=$row->height;
			if (!$ef_ladies) $ef_ladies=$row->ladies;
			if (!$ef_topsize) $ef_topsize=$row->topsize;
			
			if (!$ef_accom) $ef_accom=$row->accom;
			if (!$ef_frnd_name) $ef_frnd_name=$row->frnd_name;
			if (!$ef_frnd_email) $ef_frnd_email=$row->frnd_email;
			if (!$ef_frnd_add) $ef_frnd_add=$row->frnd_add;
			if (!$ef_cb_agree) $ef_cb_agree=$row->cb_agree;
			if (!$ef_cb_spons) $ef_cb_spons=$row->cb_spons;
			if (!$ef_cb_health) $ef_cb_health=$row->cb_health;
			if (!$ef_cb_fotos) $ef_cb_fotos=$row->cb_fotos;
			if (!$ef_cb_money) $ef_cb_money=$row->cb_money;
			
			$cb_terms=explode(",", $row->cb_terms);
			
		}
	}

	if ($results=$db->get_results("select id, description from event_config_tnc where guid='".$event->id."' order by sort_order, id")) {
		$cb_terms_required='';
		foreach($results as $result) {
			$thiscond="ef_cb_".$result->id;
			$cb_terms_required.=$result->id.",";
			if ($_SERVER['REQUEST_METHOD']=="POST") $checked = ($_POST['cb_tnc'.$result->id]>0);
			if (!$$thiscond) $$thiscond=in_array($result->id, $cb_terms)?1:0;
			//print "check condition(ef_cb_".$result->id.")=".$$thiscond."<br>";
			$conditionsList.='<p><input type="checkbox" class="checkbox" name="cb_tnc'.$result->id.'" value="'.$result->id.'" '.($checked?'checked="checked"':(($$thiscond==1)?'checked="checked"':'')).' /> '.str_replace("<p>","",str_replace("</p>","",$result->description)).'</p>';
		}
		$conditionsList.='<input type="hidden" name="cb_terms_required" value="'.$cb_terms_required.'" />';
	}
	

// Check if registration has been completed.
if ($row->registered!=0 && $form_mode!="PREVIEW") { 
	?>
	<p>You have successfully completed the registration process for this event</p>
	<?php 
}
// Show the registration form 
else if ($event->id) { 
	?>
    <form method="POST" id="event_register" class="std-form">
    <fieldset class="event_form">
        <input type="hidden" name="register" />
        <input type="hidden" name="id" value="<?=$form_member_id?>" />
        <legend>Personal details</legend>
        <table border="0" class="event_table" cellpadding="0" cellspacing="0">
        <tr>
        <td><label for="ef_title" class="inline">Title</label> <input id="ef_title" type="text" class="text" name="title" value="<?=$ef_title?>" /></td>
        </tr><tr>
        <td><label for="ef_forenames" class="inline">Forenames</label> <input id="ef_forenames" type="text" class="text" name="forenames" value="<?=$ef_forenames?>" /></td>
        </tr><tr>
        <td><label for="ef_surname" class="inline">Surname</label> <input id="ef_surname" type="text" class="text" name="surname" value="<?=$ef_surname?>" /></td>
        </tr><tr>
        <td><label for="ef_email" class="inline">Email address</label> <input id="ef_email" type="text" class="text" name="email" value="<?=$ef_email?>" /></td>
        <!-- <td><label for="ef_prefname" class="inline">Preferred name</label> <input id="ef_prefname" type="text" class="text" name="prefname" value="<?=$ef_prefname?>" /></td> -->
        </tr><tr>
        <td valign="top"><label for="ef_add1">Address</label><textarea id="ef_add1" class="text" name="address"><?=$ef_address?></textarea></td>
        <!--
        </tr><tr>
        <td valign="top"><label for="ef_hearabout">Where did you hear about <?=$event->title?></label><input id="ef_hearabout" type="text" class="text" name="hearabout" value="<?=$ef_hearabout?>" /></td>
        -->
        </tr><tr>
        <td><label for="ef_tel_day">Phone number</label><input id="ef_tel_day" type="text" class="text" name="day_tel" value="<?=$ef_day_tel?>" /></td>
        </tr><tr>
        <td><label for="ef_tel_mob">Mobile number</label><input id="ef_tel_mob" type="text" class="text" name="mob_tel" value="<?=$ef_mob_tel?>" /></td>
        <!-- <td><label for="ef_tel_eve">Date of birth</label><input id="ef_dob" type="text" class="text" name="dob" value="<?=$ef_dob?>" /></td> -->
        </tr>
        
        <!--
        <tr>
        <td><label for="ef_nationality">Nationality</label><input id="ef_nationality" type="text" class="text" name="nationality" value="<?=$ef_nationality?>" /></td>
        <td valign="top">
        <label for="ef_dummy_01">Sex</label>
        <label for="ef_sex_m" class="sex">Male</label>
        <input id="ef_sex_m" class="radio" type="radio" <?=(($ef_sex=="M")?"checked":"")?> name="sex" value="M"  />
        <label for="ef_sex_f" class="sex">Female</label>
        <input id="ef_sex_f" class="radio" type="radio" <?=(($ef_sex=="F")?"checked":"")?> name="sex" value="F" />
        </td>
        </tr>
        -->
        
        </table>
        
        <?php if ($evt_config->chk_passport) { ?>
            <p style="width:608px;">(<i><strong>Names must be written exactly as they appear on your passport</strong></i>)</p>
        <?php } ?>
    </fieldset>
    
    
    <?php if ($evt_config->chk_passport) { ?>
        <fieldset class="event_form">
            <legend>Passport details</legend>
            <table border="0" class="event_table" cellpadding="0" cellspacing="0">
            <tr>
            <td><label for="ef_pass_number">Number</label> <input id="ef_pass_number" type="text" class="text" name="pass_number" value="<?=$ef_pass_number?>" /></td>
            <td><label for="ef_pass_country">Country of issue</label> <input id="ef_pass_country" type="text" class="text" name="pass_country" value="<?=$ef_pass_country?>" /></td>
            </tr><tr>
            <td><label for="ef_pass_dob">Date of birth</label> <input id="ef_pass_dob" type="text" class="text" name="pass_dob" value="<?=$ef_pass_dob?>" /></td>
            <td><label for="ef_pass_pob">Place of birth</label> <input id="ef_pass_pob" type="text" class="text" name="pass_pob" value="<?=$ef_pass_pob?>" /></td>
            </tr><tr>
            <td><label for="ef_pass_issue">Issue date</label> <input id="ef_pass_issue" type="text" class="text" name="pass_issue" value="<?=$ef_pass_issue?>" /></td>
            <td><label for="ef_pass_expiry">Expiry date</label> <input id="ef_pass_expiry" type="text" class="text" name="pass_expiry" value="<?=$ef_pass_expiry?>" /></td>
            </tr>
            </table>
            <p style="padding-top:10px;"><strong><i><?=$evt_config->passport?></i></strong></p>
        </fieldset>
    <?php } ?>
    
    <?php 
	// ----------------------------------------------------------------------
	// SPECIAL DIETARY REQUIREMENTS
	if ($evt_config->chk_special) { 
		$foods = array("Vegetarian", "Vegan", "Yeast intolerant", "Glucose intolerant", "Wheat intolerant");
		foreach ($foods as $tmp) {
			$food_opts .= '<option value="'.$tmp.'"'.($ef_food==$tmp?' selected="selected"':"").'>'.$tmp.'</option>';
		}
		?>
        <fieldset class="event_form">
            <legend>Special requirements</legend>
          	<label for="ef_foodreq">Do you have any special dietary requirements/food allergies?</label>
            <select name="food" id="ef_foodreq">
                <option value="0">None</option>
                <?=$food_opts?>
            </select>
            <p id="ef-diet-requirements">*Those with special dietary requirements should bring supplementary food.</p>
        </fieldset>
    	<?php
	} 
	// ----------------------------------------------------------------------
	?>
    
    
    <?php if ($evt_config->chk_bikes) { ?>
        <fieldset class="event_form">
            <legend>Bikes</legend>
            <table border="0" class="event_table" cellpadding="0" cellspacing="0">
            <tr>
            <td><label for="ef_height">Height (metres)</label> <input id="ef_height" type="text" class="text" name="height" value="<?=$ef_height?>" /></td>
            <td><label for="ef_ladies">Ladies frame</label> <input type="checkbox" id="ef_ladies" class="checkbox_left" name="ladies" <?=(($ef_ladies==1)?"checked":"")?> value="1" /></td>
            </tr>
            <tr>
            <td>
                <label for="ef_">Cycling top size</label>
                <select name="topsize">
                    <option value="">Select</option>
                    <option value="S"<?=(($ef_topsize=="S")?" selected":"")?>>Small</option>
                    <option value="M"<?=(($ef_topsize=="M")?" selected":"")?>>Medium</option>
                    <option value="L"<?=(($ef_topsize=="L")?" selected":"")?>>Large</option>
                    <option value="XL"<?=(($ef_topsize=="XL")?" selected":"")?>>Extra Large</option>
                </select>
            </td>
            <td></td>
            </tr>
            </table>
            <p style="padding-top:10px;">(<i>Bikes with a ladies frame can be requested but are not guaranteed</i>)</p>
        </fieldset>
    <?php } ?>
    
    <?php if ($evt_config->chk_accommodation) { ?>
        <fieldset class="event_form">
            <legend>Accommodation</legend>
            <p style="padding-top:10px;">If there is anyone you would like to share with please write their full name here (otherwise participants will be allocated rooms on same sex sharing basis - rooms will be twins / triples / quads). <i>We will try to accommodate your request, however it cannot be guaranteed.</i></p>
            <table border="0" class="event_table" cellpadding="0" cellspacing="0">
            <tr>
            <td colspan="2"><textarea id="ef_accom" class="text_accom" name="accom"><?=$ef_accom?></textarea></td>
            </tr>
            </table>
        </fieldset>
    <?php } ?>
    
    
    <?php if ($evt_config->chk_friends) { ?>
    <fieldset class="event_form">
        <legend>Friends</legend>
        <p>Would you like us to send details of the <?=$event->title?> to a friend?</p>
        <table border="0" class="event_table" cellpadding="0" cellspacing="0">
        <tr>
            <td><label for="ef_frnd_name">Name</label> <input id="ef_frnd_name" type="text" class="text" name="frnd_name" value="<?=$ef_frnd_name?>" /></td>
            <td rowspan="2"><label for="ef_frnd_add">Address</label> <textarea id="ef_frnd_add" class="text" name="frnd_add"><?=$ef_frnd_add?></textarea></td>
        </tr>
        <tr>
            <td><label for="ef_frnd_email">Email</label> <input id="ef_frnd_email" type="text" class="text" name="frnd_email" value="<?=$ef_frnd_email?>" /></td>
        </tr>
        </table>
    </fieldset>
    <?php } ?>
    
    <?php
	// ----------------------------------------------------------------------
	// Show tickets
	if ($event->price>0) {
		$ticket_opts = '';
		for($i=1;$i<10;$i++) {
			$ticket_opts.='<option value="'.$i.'"'.($ef_tickets==$i?' selected="selected"':"").'>'.$i.'</option>';
		}
		?>
        <fieldset class="event_form">
            <legend>Tickets</legend>
            <label for="ef_tickets"># Tickets</label> 
            <select id="ef_tickets" class="select" name="tickets">
            	<?=$ticket_opts?>
            </select>
        </fieldset>
        <?php
	}	
	// ----------------------------------------------------------------------
	?>
    
    
    <?php 
	// ----------------------------------------------------------------------
	// Show terms and conditions if there are any or we are previewing the form
	if ($conditionsList || $form_mode!="PREVIEW") { 
		?>
        <fieldset class="event_form">
	        <input type="hidden" name="entry_id" value="<?=$ef_entry_id?>" />
    	    <input type="hidden" name="action" value="process_entry_form" />
        	<?php if ($conditionsList) { ?>
	            <legend>DECLARATION (all options must be checked)</legend>
    	        <?=$conditionsList?>
        	<?php } else  { ?>
            	<legend>Complete registration</legend>
    	    <?php }  ?>
	
			<?php if ($form_mode!="PREVIEW") { ?>
                <input type="submit" class="submit" name="treeline" value="Submit entry" />
            <?php } ?>
        </fieldset>
    	<?php 
	} 
	// ----------------------------------------------------------------------
	?>

    
    <?php if ($form_mode!="PREVIEW") { ?>
    <p><input type="checkbox" class="checkbox" name="no_news" value="1" <?=(($ef_no_news==1)?"checked":"")?> /> We would like to keep you informed of future events and <?=$site->name?> news. If you prefer not to be contacted please tick here.</p>
    <?php } ?>
    
    </form>
    

<?php 
} else { 
	?>
	<p>Please select an event</p>	
	<?php 
} 
?>