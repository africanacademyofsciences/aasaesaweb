<?php

/*
	MEMBERSHIP FORM ADMIN VIEW
	
	Add/Edit member details
	
	
	Note: 
	This form could be Ajaxified in the future hence it's presence as an includes file located in a folder called ajax.
	This should allow for an AJAX technique not similar in technique to using an iframe.
*/
	
	$firstname = ($_POST['firstname']) ? $_POST['firstname'] : ''; // firstname
	$surname = ($_POST['surname']) ? $_POST['surname'] : ''; // surname
	$email = ($_POST['email']) ? $_POST['email'] : ''; // email
	$password = ($_POST['password']) ? $_POST['password'] : ''; // password
	$address1 = ($_POST['address1']) ? $_POST['address1'] : ''; // address line 1
	$address2 = ($_POST['address2']) ? $_POST['address2'] : ''; // address line 2
	$address3 = ($_POST['address3']) ? $_POST['address3'] : ''; // address line 3
	$postal_code = ($_POST['postal_code']) ? $_POST['postal_code'] : ''; // postal code
	$telephone = ($_POST['telephone']) ? $_POST['telephone'] : ''; // telephone
	$further_info = ($_POST['further_info']) ? $_POST['further_info'] : ''; // further information
	$terms = ($_POST['terms']) ? $_POST['terms'] : ''; //terms
	$preference = ($_POST['preference']) ? $_POST['preference'] : ''; // preferences

	
	
	if($action == 'edit'){
		// If we're editing the data should be presupplied.
		
		$firstname = ($_POST['firstname']) ? $_POST['firstname'] : $result->firstname; // firstname
		$surname = ($_POST['surname']) ? $_POST['surname'] : $result->surname; // surname
		$email = ($_POST['email']) ? $_POST['email'] : $result->email; // email
		$password = ($_POST['password']) ? $_POST['password'] : $result->password; // password
		$address1 = ($_POST['address1']) ? $_POST['address1'] : $result->address1; // address1
		$address2 = ($_POST['address2']) ? $_POST['address2'] : $result->address2; // address2
		$address3 = ($_POST['address3']) ? $_POST['address3'] : $result->address3; // address3
		$postal_code = ($_POST['postal_code']) ? $_POST['postal_code'] : $result->postal_code; // postal_code
		$telephone = ($_POST['telephone']) ? $_POST['telephone'] : $result->telephone; // telephone
		$further_info = ($_POST['further_info']) ? $_POST['further_info'] : $result->further_info; // further_information
		$terms = ($_POST['terms']) ? $_POST['terms'] : $result->terms; //terms
		$preference = ($_POST) ? $_POST['preference'] : $member->getMemberPreferencesById($memberId); // preferences

	}
	
	
	// POST FORM DETAILS
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		unset($message);
		unset($feedback);
	
		// Add new member
		if($action == 'create' || $action == 'add'){
			$message = $member->add();
		}
		// Edit member
		else if($action == 'edit'){
			$message = $member->edit($memberId);
		}
		
	}
	

?>
<?=drawFeedback('error',$message)?>
<form id="<?php echo $action; ?>form" action="" method="post">
    <fieldset>
        <legend><?php echo ($action == 'join' || $action == 'create' || $action == 'add') ? 'Apply for membership' : 'Edit your details'; ?></legend>
        <p class="instructions"><?php echo ($action == 'join' || $action == 'create' || $action == 'add') ? 'Apply for membership' : 'Edit your details'; ?> using form below</p>
        <fieldset>
            <legend>Personal details</legend>
            <label for="firstname" class="required">First name:</label>
            <input type="text" value="<?php echo $firstname; ?>" id="firstname" name="firstname" /><br />
            <label for="surname" class="required">Surname:</label>
            <input type="text" value="<?php echo $surname; ?>" id="surname" name="surname" /><br />
        </fieldset>
        <fieldset>
        	<legend>Login details</legend>
            <label for="email" class="required">Email:</label>
        	<input type="text" value="<?php echo $email; ?>" id="email" name="email" /><br />
            <?php if($action != 'edit') { ?>
            <label for="password" class="required">Password:</label>
            <input type="text" value="<?php echo $password; ?>" id="password" name="password" /><br />
            <?php } ?>
        </fieldset>
        <fieldset>
        	<legend>Contact details</legend>
            <p id="contactDetailsHelp" class="instructions"></p>
            <div id="extraContactDetails">
            	<p class="instructions">Add contact details that differ to the member's organisation's.</p>
                <label for="address1" class="required">Address line 1</label>
                <input type="text" value="<?php echo $address1; ?>" id="address1" name="address1" /><br />
                <label for="address2" class="required">Address line 2:</label>
                <input type="text" value="<?php echo $address2; ?>" id="address2" name="address2" /><br />
                <label for="address3">Address line 3:</label>
                <input type="text" value="<?php echo $address3; ?>" id="address3" name="address3" /><br />
                <label for="telephone" class="required">Postal code:</label>
                <input type="text" value="<?php echo $postal_code; ?>" id="postal_code" name="postal_code" /><br />
            </div>
            <label for="telephone" class="required">Telephone:</label>
                <input type="text" value="<?php echo $telephone; ?>" id="telephone" name="telephone" /><br />
        </fieldset>
        <fieldset>
        	<legend>Preferences</legend>
            <?php
				$preferences = $member->getPreferences();
				echo drawPreferencesCheckboxes($preferences, $preference);
			?>
        </fieldset>
        <label for="further_info" class="textarea">Further information:</label>
        <textarea id="further_info" name="further_info" rows="5" cols="10"><?php echo $further_info; ?></textarea><br />
         <input type="checkbox" class="checkbox" name="terms" id="terms" value="1" <?php echo ($terms) ? ' checked="checked"' : '' ; ?> />
            <label for="terms" class="checklabel">Do you accept our terms?</label>
        
        <input type="hidden" name="action" value="<?php echo $action; ?>" />
        <button type="submit" class="submit">Submit <?php echo ($action == 'join') ? 'Application' : 'Changes'; ?></button>
    </fieldset>
</form>
