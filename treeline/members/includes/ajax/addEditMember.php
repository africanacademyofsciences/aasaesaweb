<?php
/*
	MEMBERSHIP FORM ADMIN VIEW
	Add/Edit member details
	
	Note: 
	This form could be Ajaxified in the future hence it's presence as an includes file located in a folder called ajax.
	This should allow for an AJAX technique not similar in technique to using an iframe.
*/
	
$firstname = ($_POST['firstname']) ? $_POST['firstname'] : ''; 
$surname = ($_POST['surname']) ? $_POST['surname'] : ''; 
$email = ($_POST['email']) ? $_POST['email'] : ''; 
$oldemail='';

$password = ($_POST['password']) ? $_POST['password'] : ''; 
$address1 = ($_POST['address1']) ? $_POST['address1'] : ''; 
$address2 = ($_POST['address2']) ? $_POST['address2'] : ''; 
$address3 = ($_POST['address3']) ? $_POST['address3'] : ''; 
$postal_code = ($_POST['postal_code']) ? $_POST['postal_code'] : ''; 
$telephone = ($_POST['telephone']) ? $_POST['telephone'] : ''; 
$further_info = ($_POST['further_info']) ? $_POST['further_info'] : ''; // further information
$terms = ($_POST['terms']) ? $_POST['terms'] : ''; //terms
$preference = ($_POST['preference']) ? $_POST['preference'] : ''; // preferences

$member_type = ($_POST?$_POST['member_type']:1);
$paid_date = ($_POST?$_POST['paid_date']:'');

$bloggable = ($_POST?$_POST['bloggable']:'');
$forumable = ($_POST?$_POST['forumable']:'');


if($action == 'edit'){

	// If we're editing the data should be presupplied.
	$firstname = $_POST?$_POST['firstname']:$result->firstname; 
	$surname = ($_POST['surname']) ? $_POST['surname'] : $result->surname; // surname
	$email = ($_POST['email']) ? $_POST['email'] : $result->email; // email
	$oldemail = $result->email;
	$password = ($_POST['password']) ? $_POST['password'] : $result->password; // password
	$address1 = ($_POST['address1']) ? $_POST['address1'] : $result->address1; // address1
	$address2 = ($_POST['address2']) ? $_POST['address2'] : $result->address2; // address2
	$address3 = ($_POST['address3']) ? $_POST['address3'] : $result->address3; // address3
	$postal_code = ($_POST['postal_code']) ? $_POST['postal_code'] : $result->postal_code; // postal_code
	$telephone = ($_POST['telephone']) ? $_POST['telephone'] : $result->telephone; // telephone
	$further_info = ($_POST['further_info']) ? $_POST['further_info'] : $result->further_info; // further_information
	$terms = ($_POST['terms']) ? $_POST['terms'] : $result->terms; //terms
	//$preference = ($_POST) ? $_POST['preference'] : $member->getMemberPreferencesById($memberId); // preferences

	$member_type = ($_POST?$_POST['member_type']:$result->member_type);
	$paid_date = ($_POST?$_POST['paid_date']:$result->paid_year);
	$bloggable = ($_POST?$_POST['bloggable']:$result->bloggable);
	$forumable = ($_POST?$_POST['bloggable']:$result->forumable);
}

	
// Get allowed member types.
// If there is only 1 type then add as a hidden field
$query = "SELECT id, title FROM member_types ORDER BY sort_order, title";
if ($results = $db->get_results($query)) {
	foreach($results as $result) {
		$member_type_html.='<option value="'.$result->id.'"'.($result->id==$member_type?' selected="selected"':"").'>'.$page->drawLabel("tl_mt_".strtolower(str_replace(" ","_",$result->title)), $result->title).'</option>'."\n";
	}
}
if (!$member_type_html) {
	$member_type_html = '<input type="hidden" name="member_type" value="1" />';
	$member_type_html .= '<input type="hidden" name="paid_date" value="'.$paid_date.'" />';
}
else {
	$member_type_html = '
		<label for="f_member_type" class="required">Membership type:</label>
		<select name="member_type" id="f_member_type">
			'.$member_type_html.'
		</select>
	';	
	if ($member_type==2) {
		$thisyear = date("Y", time());
		if (!$paid_date) $paid_date = $_GET['pd'];
		$member_type_html .= '
			<label for="f_paid_date" class="">Paid 1/1:</label>
			<div style="float: left;">
			<input type="text" name="paid_date" id="f_paid_date" value="'.$paid_date.'" />
			<p class="info">Renewal due: 1/1/'.($paid_date>=$thisyear?$paid_date+1:$thisyear).'</p>
			</div>
		';	
	}
	else $member_type_html .= '<input type="hidden" name="paid_date" value="'.$paid_date.'" />';
}

$page_title = ($action == 'join' || $action == 'create' || $action == 'add') ? $page->drawLabel("tl_mem_add_title", 'Add a new member'):$page->drawGeneric('edit', 1).' '.$firstname.' '.$surname;

$page_html = '

<form id="'.$action.'form" action="" method="post">
<fieldset>
	<p class="instructions">'.(($action == 'join' || $action == 'create' || $action == 'add') ? $page->drawLabel("tl_mem_add_title", 'Add a new member') : $page->drawLabel("tl_mem_edit_title", 'Edit the member').' <strong>'.$firstname.' '.$surname.'</strong>').' '.$page->drawLabel("tl_mem_add_msg1", "using form below").'</p>
	<fieldset>
		<legend>'.$page->drawLabel("tl_mem_add_details", "Personal details").'</legend>
		<label for="firstname" class="required">'.$page->drawGeneric("firstname", 1).':</label>
		<input type="text" value="'.$firstname.'" id="firstname" name="firstname" /><br />

		<label for="surname" class="required">'.$page->drawGeneric("surname", 1).':</label>
		<input type="text" value="'.$surname.'" id="surname" name="surname" /><br />
	';
$page_html.=$member_type_html;

// Should we show the blogs checkbox
if ($site->getConfig('setup_blogs')) {
	$page_html.='
		<div class="field">
			<label for="f_bloggable">'.$page->drawLabel("tl_mem_add_blogok", "Allowed to blog").':</label>
			<input type="checkbox" id="f_bloggable" name="bloggable" value="1" style="width: auto;" '.($bloggable?' checked="checked"':"").' /><br />
		</div>
	';
}
// Should we show the forum checkbox
if ($site->getConfig('setup_forum')) {
	$page_html.='	
		<div class="field">
			<label for="f_forumable">'.$page->drawLabel("tl_mem_add_forumok", "Post to forums").':</label>
			<input type="checkbox" id="f_forumable" name="forumable" value="1" style="width: auto;" '.($forumable?' checked="checked"':"").' /><br />
		</div>
	';
}
	//Removed: '.($action=="edit"?"disabled":"").'
$page_html.='	
	</fieldset>

	<fieldset>
		<legend>'.$page->drawLabel("tl_mem_add_login", "Login details").'</legend>
		<label for="email" class="required">'.$page->drawGeneric("email", 1).':</label>
		<input type="text" value="'.$email.'" id="email" name="email"  /><br />
		<input type="hidden" name="oldemail" value="'.$oldemail.'" />

		<label for="password" class="required">'.$page->drawGeneric("password", 1).':</label>
		<input type="text" value="'.$password.'" id="password" name="password" /><br />
	</fieldset>
    ';
        
	$page_html.='
	<fieldset>
		<legend>'.$page->drawLabel("tl_mem_add_contact", "Contact details").'</legend>
		<label for="telephone">'.$page->drawGeneric("telephone", 1).':</label>
		<input type="text" value="'.$telephone.'" id="telephone" name="telephone" /><br />
		<label for="further_info" class="textarea">'.$page->drawLabel("tl_mem_add_info", "Further information").':</label>
		<textarea id="further_info" name="further_info" rows="5" cols="10">'.$further_info.'</textarea><br />
	</fieldset>
	';
		
	$page_html.='
	<input type="hidden" name="terms" id="terms" value="1" />        
	<input type="hidden" name="action" value="'.$action.'" />
	<fieldset>
	<fieldset class="buttons">
		<input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
	</fieldset>
	</fieldset>
</fieldset>
</form>
	';

	echo treelineBox($page_html, $page_title, "blue");