<?php
	
$title = ($_POST['title']) ? $_POST['title'] : ''; // firstname
$description = ($_POST['description']) ? $_POST['description'] : ''; // surname
$successmsg = ($_POST['success-msg']) ? $_POST['success-msg'] : ''; // surname

if($action == 'edit' || $action=="duplicate"){
	if ($action!="duplicate") $title = ($_POST['title']) ? $_POST['title'] : $form->title; 
	$description = ($_POST['description']) ? $_POST['description'] : $form->description; 
	$successmsg = ($_POST['success-msg']) ? $_POST['success-msg'] : $form->successmsg;
	$user_email = ($_POST['user_email']) ? $_POST['user_email'] : $form->user_email;
	$cust_email = ($_POST['cust_email']) ? $_POST['cust_email'] : $form->cust_email;
	//print "Got email($cust_email)<br>\n";
}

// If this happens to be an event form we need to save the event guid
$event_guid = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST, 'event-guid', '');	
	
if ($action == 'create') $page_title='Add a new form';
else if ($action =="edit") $page_title ='Edit '.$form->title;
else if ($action =="duplicate") $page_title ='Duplicate '.$form->title;

$userlist = '';
$query = "SELECT u.id, u.full_name, u.email FROM users u 
	LEFT JOIN groups g ON g.id=u.`group`
	WHERE u.id>1 
	AND g.domain = ".$site->id."
	ORDER BY full_name
	";
//print "$query<br>\n";
if ($results = $db->get_results($query)) {
	foreach ($results as $result) {	
		$userlist.='<option value="'.$result->id.'"'.($result->id==$user_email?' selected="selected"':'').'>'.$result->full_name.'('.$result->email.')</option>';
	}
}

$emaillist = '';
$query = "SELECT subject AS title, text3 
	FROM newsletter WHERE 
	`status` = 'S' AND
	text3 like 'FORM-CUSTOM-%'
	ORDER BY subject
	";
//print "$query<br>\n";
if ($results = $db->get_results($query)) {
	foreach ($results as $result) {	
		$thisnews = substr($result->text3, 12);
		$emaillist.='<option value="'.$thisnews.'"'.($thisnews==$cust_email?' selected="selected"':'').'>'.$result->title.'</option>';
	}
}
//print "Got list($emaillist)<br>\n";



$page_html = '

<form id="'.$action.'form" action="" method="post">
<fieldset>
	<input type="hidden" name="fid" value="'.($action=="create"?0:$form->id).'" />
	<input type="hidden" name="action" value="'.$action.'" />
	<input type="hidden" name="event-guid" value="'.$event_guid.'" />
	<p class="instructions">'.($action == 'create'?'Enter new':'Edit the').' form data below</p>
	'.($action=="edit"?'
		<p>You cannot change the form title as this would invalidate any pages already containing this form.</p>
		<p>To add this form to a content page just include <strong>@@FORM_'.$form->name.'@@</strong> at the position you would like the form to appear.</p>
		':'').'
	<fieldset>
		<legend>Form data</legend>
		<label for="f_title" class="required">'.($action=="duplicate"?"New t":"T").'itle:</label>
		<input type="text" value="'.$title.'" id="f_title" name="title" '.($action=="edit"?'readonly="readonly"':'').' /><br />
		<label for="f_description">Description:</label>
		<textarea id="f_description" name="description" />'.$description.'</textarea><br />
		<label for="f_success">Success message:</label>
		<textarea id="f_success" name="success-msg" />'.$successmsg.'</textarea><br />
	';

if ($userlist) {
	$page_html.='
		<label for="f_email" class="">Email to admin:</label>
		<select name="user_email" id="f_email">
			<option value="">Don\'t email data</option>
			'.$userlist.'
		</select>
	';
}

$page_html.='
		<label for="f_cemail" class="">Email to visitor:</label>
		<select name="cust_email" id="f_demail">
			<option value="">Don\'t email visitor</option>
			'.$emaillist.'
		</select>
	</fieldset>
	<fieldset>
		<fieldset class="buttons">
			<input type="submit" class="submit" value="'.($action=="create"?'Create':'Post changes').'" />
		</fieldset>
	</fieldset>
</fieldset>
</form>
';

echo treelineBox($page_html, $page_title, "blue");