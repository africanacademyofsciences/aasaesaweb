<?

	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/subscriber.class.php');
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
	include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include ($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
	
	$action = read($_REQUEST,'action','edit');
	// Anybody can change their settins but nuffn else.
	if ($_SESSION['treeline_user_group']!="Superuser") {
		if ($action != "notify") {
			header("Location: /treeline/");
		}
	}

	$guid = read($_REQUEST,'guid','');

	$message = array();
	$feedback = read($_REQUEST,'feedback','generic');

	$message_in = read($_REQUEST,'message',false);
	if($message_in){ $message[] = $message_in; }
	
	$userid = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET,'userid',false);
	$edit_user = read($_POST,'edit_user',false);
	
	$new_fullname = read($_POST,'new_fullname',false);
	$new_username = read($_POST,'new_username',false);
	$new_email = read($_POST,'new_email',false);
	$new_password = read($_POST,'new_password',false);
	$new_confirm = read($_POST,'new_confirm',false);
	$new_type = read($_POST,'new_type',false);
	$new_status = (read($_POST,'new_status',false) ) ? 1 :0;
	$status = read($_POST,'status',false);

	$job = read($_POST, 'job', '');
	$org = read($_POST, 'org', '');
	$portrait = read($_POST, 'portrait', '');
	
	$newuser = new User();
	
	//for searching users...
	$search_field = read($_POST,'search_field',false);
	$select_field = read($_POST,'select_field',false);

	$nextsteps='';
	$feedback="error";
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		// ------------------------------------------------------------		
		// Create new user
		if ($action == 'create') {

			if (!$new_username) {
				$message[] = $page->drawLabel('tl_acc_err_name', 'Please enter a Username for this user');
			}
			if($newuser->userExists($new_username)){
				$message[] = $page->drawLabel("tl_acc_err_inuse", 'This username is already in use');
			}
			if (!$new_fullname){
				$message[] = $page->drawLabel("tl_acc_err_full", 'Please enter the Full Name for this user');
			}
			if(!$new_email){
				$message[] = $page->drawLabel("tl_acc_err_email", 'Please enter an Email Address for this user');
			}
			if ($new_type == 'xx' || !$new_type) {
				$message[] = $page->drawLabel("tl_acc_err_group", 'Please select a Group for this user');
			}
			if (!$new_password){
				$message[] = $page->drawLabel("tl_acc_err_pass", 'You need to specify a Password for this account');
			}
			if ($new_password!=$new_confirm){
				$message[] = $page->drawLabel("tl_acc_err_match", 'The Password and Password Confirmation fields do not match');
			}
			if(!$message){

				// Create a new user:	
				/// change IF to validate email address...
				if($newuser->setEmail($new_email)){
					$newuser->setName($new_username);
					$newuser->setFullName($new_fullname);
					$newuser->setGroup($new_type);	
					$newuser->setPassword($new_password);
					$newuser->setStatus(0);
					
					/// all OK?  Save the user!
					if($newuser->save()){
						$nextsteps.='<li><a href="/treeline/access/?action=create">'.$page->drawLabel("tl_acc_next_new", "Create another Treeline user").'</a></li>';
						$action='edit';
						
						// Update user job, org and image
						$query = "UPDATE users SET job='".$db->escape($job)."', organisation='".$db->escape($org)."', portrait='$portrait' WHERE id=".($user->id+0);
						$db->query($query);
						print "Update user(".$query.")<br>\n";
						
					}
					else $message[] = $page->drawLabel("tl_acc_err_cprob", 'There was a problem creating this user, please try again');
				}
				else $message[] = $page->drawLabel("tl_acc_err_vemail", 'The email address does not appear to be valid');
			}
		} 
		
		// ------------------------------------------------------------		
		// Modify user details
		else if ($action == 'edit') {

			if($userid && $edit_user){
				$edit_user=true;

				$newuser->loadByID($userid);
	
				if (!$new_username) {
					$message[] = $page->drawLabel("tl_acc_err_name", 'Please enter a Username for this user');
				}
				if($newuser->getName()!=$new_username){
					if($newuser->userExists($new_username)){
						$message[] = $page->drawLabel("tl_acc_err_inuse", 'This username is already in use');
					}
				}
				if (!$new_fullname){
					$message[] = $page->drawLabel("tl_acc_err_full", 'Please enter the Full Name for this user');
				}
				if(!$new_email){
					$message[] = $page->drawLabel("tl_acc_err_email", 'Please enter an Email Address for this user');;
				}
				if ($new_type == 'xx' || !$new_type) {
					$message[] = $page->drawLabel("tl_acc_err_group", 'Please select a Group for this user');
				}
				if (!$new_password){
					$message[] = $page->drawLabel("tl_acc_err_pass", 'You need to specify a Password for this account');
				}
				if ($new_password!=$new_confirm){
					$message[] = $page->drawLabel("tl_acc_err_match", 'The Password and Password Confirmation fields do not match');
				}
				if(!$message){
	
					if($newuser->setEmail($new_email)){
						$newuser->setName($new_username);
						$newuser->setFullName($new_fullname);
						$newuser->setGroup($new_type);	
						$newuser->setPassword($new_password);
						$newuser->setStatus($new_status);
						
						/// all OK?  Save the user!
						$newuser->update($userid);
						$feedback="success";
						$message[] = 'User details have been updated';
						
						// Update user job, org and image
						$query = "UPDATE users SET job='".$db->escape($job)."', organisation='".$db->escape($org)."', portrait='$portrait' WHERE id=".$userid;
						$db->query($query);
						//print "Update user(".$query.")<br>\n";
						
						$userid=0;
					}
					else{
						$newuser->__destruct();
						$message[] = $page->drawLabel("tl_acc_err_vemail", 'The email address does not appear to be valid');
					}
	
				}
			}
		}
		// ------------------------------------------------------------		


		// ------------------------------------------------------------		
		else if ($action == "notify") {
			if ($user->updateNotifications($_POST)) {
				$feedback="success";
				$message[]=$page->drawLabel("tl_acc_not_succ", "Your preferences have been saved");
			}
		}
		// ------------------------------------------------------------		
		
		
		// ------------------------------------------------------------		
		// Delete a user from Treeline
		else if ($action == 'delete') {
			$newuser->loadByID($userid);
			if($status=='deleted'){
				if($newuser->delete($userid)){
					$nextsteps.='<li><a href="/treeline/access/?action=create">'.$page->drawLabel("tl_acc_next_create", "Create a new administrator").'</a></li>';
					$action='edit'; 
					$userid=0;
				}
				else $message[] = $page->drawLabel("tl_acc_del_fail", 'User could not be deleted');
			}
		}				
		// ------------------------------------------------------------		

	}

	else {
	
		// Create a CSV listing of all administrators
		if ($action=="download") {
			$dl_filename=$user->generateCSV($_SESSION['treeline_user_id']);
			if (!$dl_filename) {
				$message[]="Failed to generate CSV listing file";
				$action="";
			}
		}
	}
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
		table.tl_list td span {
			float: none;
		}
	'; // extra on page CSS
	
	if ($action == "notify") $extraCSS = '
	
form fieldset div#notify-options label {	
	width: 400px;
}	
	
form fieldset div#notify-options input.not-opt {	
	width: 50px;

}	

	';

	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title
	
	
	
	$pageTitle = $pageTitleH2 = $page->drawPageTitle("access rights", $action);
	$pageClass = 'access';
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');

?>
<div id="primarycontent">
<div id="primary_inner">
<?php
echo drawFeedback($feedback,$message);
if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");

if ($action == 'create') { 
	$page_html='
	<form id="treeline" action="/treeline/access/'.($DEBUG?'?debug':"").'" method="post">
    	<fieldset>
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="guid" value="'.$guid.'" />
            <p class="instructions">'.$page->drawLabel("tl_acc_crea_msg1", "This section allows super-users to add new administrators of Treeline").'</p>
            <p class="instructions">'.$page->drawGeneric("mandatory field message", 1).'</p>
            <label for="new_fullname" class="required">'.$page->drawLabel("tl_acc_field_name", "Full Name").':</label>
            <input type="text" name="new_fullname" id="new_fullname" value="'.$new_fullname.'" /><br />
            <label for="new_username" class="required">'.$page->drawGeneric("username", 1).':</label>
            <input type="text" name="new_username" id="new_username" value="'.$new_username.'" /><br />
            <label for="new_email" class="required">'.$page->drawGeneric("email", 1).':</label>
            <input type="text" name="new_email" id="new_email" value="'.$new_email.'" /><br />
            <label for="new_password" class="required">'.$page->drawGeneric("password", 1).':</label>
            <input type="password" name="new_password" id="new_password" value="" /><br />
            <label for="new_confirm" class="required">'.$page->drawGeneric("confirm password", 1).':</label>
            <input type="password" name="new_confirm" id="new_confirm" value="" /><br />
            <label for="new_type" class="required">'.$page->drawLabel("tl_acc_field_utype", "Type of User").':</label>
            '.$newuser->drawGroupsList($new_type,'new_type').'<br />
			
            <label for="f_job" class="">Job title:</label>
            <input type="text" name="job" id="f_job" value="'.$job.'" /><br />
			
            <label for="f_org" class="">Organisation:</label>
            <input type="text" name="org" id="f_org" value="'.$org.'" /><br />
			<div style="clear:both;">
				<label for="f_img" class="">Portrait:</label>
				<div style="float: left;">
					<textarea name="portrait" id="f_img">'.$portrait.'</textarea><br />
				</div>
			</div>
			
            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
            </fieldset>
    	</fieldset>
    </form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_acc_crea_title", "Create a new user"), "blue");
} 
// ---------------------------------------------------------
// EDIT USERS.
else if ($action == 'edit') {

	if ($userid) { 
		$newuser->loadByID($userid); 
		$page_html='
		<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="guid" value="'.$guid.'" />
				<p class="instructions">'.$page->drawLabel("tl_acc_edit_msg1", 'This section allows super-users to modify the details of Treeline users').'</p>
				<p class="instructions">'.$page->drawGeneric("mandatory field message", 1).'</p>	
				<label for="new_fullname" class="required">'.$page->drawGeneric("fullname", 1).':</label>
				<input type="text" name="new_fullname" id="new_fullname" value="'.($_POST?$_POST['new_fullname']:$newuser->getFullName()).'" /><br />
				<label for="new_username" class="">'.$page->drawGeneric("username",1).':</label>
				<input type="text" name="new_username" id="new_username" value="'.$newuser->getName().'" readonly="readonly" /><br />
				<label for="new_email" class="required">'.$page->drawGeneric("email", 1).':</label>
				<input type="text" name="new_email" id="new_email" value="'.($_POST?$_POST['new_email']:$newuser->getEmail()).'" /><br />
				<label for="new_password" class="required">'.$page->drawGeneric("password", 1).':</label>
				<input type="password" name="new_password" id="new_password" value="'.($_POST?$_POST['new_password']:$newuser->getPassword()).'" /><br />
				<label for="new_confirm" class="required">'.$page->drawGeneric("confirm password", 1).':</label>
				<input type="password" name="new_confirm" id="new_confirm" value="'.($_POST?$_POST['new_confirm']:$newuser->getPassword()).'" /><br />
				<label for="new_type" class="required">'.$page->drawLabel("tl_acc_field_utype", "Type of user").':</label>
				'.$newuser->drawGroupsList($_POST?$_POST['new_type']:$newuser->getGroup(),'new_type').'<br />
				<input type="checkbox" class="checkbox" name="new_status" id="new_status"'.(($_POST?$_POST['new_status']:($newuser->getStatus()=='blocked'))?' checked="checked"':'').' />
				<label for="new_status" class="checklabel" class="required">'.$page->drawLabel("tl_acc_field_block", "Block user").'</label><br />
				<input type="hidden" name="userid" value="'.$userid.'" />

				<input type="hidden" name="'.$action.'_user" value="true" />

				<label for="f_job" class="">Job title:</label>
				<input type="text" name="job" id="f_job" value="'.($_POST?$job:$newuser->job).'" /><br />
				
				<label for="f_org" class="">Organisation:</label>
				<input type="text" name="org" id="f_org" value="'.($_POST?$org:$newuser->organisation).'" /><br />
				<div style="clear:both;">
					<label for="f_img" class="">Portrait:</label>
					<div style="float: left;">
						<textarea name="portrait" id="f_img">'.($_POST?$portrait:$newuser->portrait).'</textarea><br />
					</div>
				</div>
	
				<fieldset class="buttons">
					<input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
				</fieldset>
			</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_acc_edit_title", 'Edit user').' : '.$newuser->getName(), "blue");
	
	}
	else {
		$page_html='
		<form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
			<fieldset>
				<input type="hidden" name="action" value="'.$action.'" />
				<input type="hidden" name="guid" value="'.$guid.'" />
				<p class="instructions">'.$page->drawLabel("tl_acc_find_msg2", "To find the user you want to manage, you can search by name, username, email address or group").'</p>
				<label for="search_field">'.$page->drawGeneric("search for", 1).':</label>
				<input type="text" name="search_field" id="search_field" value="'.$search_field.'" /><br />
				<label for="select_field">'.$page->drawGeneric("search by", 1).':</label>
				<select name="select_field" id="select_field">
					<option value="u.name"'.($select_field=='u.name'?' selected':"").'>'.$page->drawGeneric("username", 1).'</option>
					<option value="full_name"'.($select_field=='full_name'?' selected':"").'>'.$page->drawGeneric("full name", 1).'</option>
					<option value="email"'.($select_field=='email'?' selected':"").'>'.$page->drawGeneric("email", 1).'</option>
					<option value="g.name"'.($select_field=='g.name'?' selected':"").'>'.$page->drawGeneric("group", 1).'</option>
				</select><br />
				<fieldset class="buttons">
					<input type="submit" class="submit" value="'.$page->drawGeneric("search").'" />
				</fieldset>
			</fieldset>
		</form>
		';	

		echo treelineBox($page_html, $page->drawLabel("tl_acc_find_title", "Manage users"), "blue");
	
		?>
		<h2><?=$page->drawLabel("tl_acc_find_recent", "Recently Added Users")?></h2>
		<?php
		if($select_field && $search_field) echo $newuser->getUsersList($select_field,$search_field);
		else echo $newuser->getUsersList();
	}
}
// ---------------------------------------------------------
// DELETE A USER
else if($userid && $action=='delete' ){ 
	$newuser->loadByID($userid); 
	$page_html='
    <form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
    <fieldset>
        <input type="hidden" name="action" value="'.$action.'" />
        <input type="hidden" name="guid" value="'.$guid.'" />
        <p class="instructions">'.$page->drawLabel("tl_acc_del_msg1", 'Are you sure you want to delete this user').'</p>
        <input type="hidden" name="status" value="deleted" />
        <input type="hidden" name="userid" value="'.$userid.'" />
        <fieldset class="buttons">
            <input type="submit" class="submit" value="'.$page->drawGeneric("delete", 1).'" />
        </fieldset>
   </fieldset>
   </form>
   ';
   echo treelineBox($page_html, $page->drawLabel("tl_acc_del_title", "Delete user").' : '.$newuser->getName(), "blue");
} 
// ---------------------------------------------------------
// MANAGE NOTIFICATIONS
else if ($action == "notify") {
	$page_html = '
	<p class="instructions">'.$page->drawLabel("tl_acc_not_msg1", "By default all notifications are eligible to go to all administrators. At the point of sending an email the system will decide if an email is appropriate to send to you based on its content and your privileges. This means that just because an options is selected you may still never receive this email. You should use this section mainly to opt out of emails that you are receiving that you do not require").'</p>
	<form method="post">
	<fieldset>
		<input type="hidden" name="action" value="'.$action.'" />
		<div id="notify-options">
			'.$user->drawNotifications().'
		</div>
		<div class="buttons">
			<label for="f_submit" style="visibility:hidden;">submit</label>
			<input type="submit" class="submit" value="'.$page->drawGeneric("save", 1).'" />
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_acc_not_title", "Setup notifications required"), "blue");
}
// ---------------------------------------------------------
else if ($action=="download") { 
	$html = '<p><a href="'.$site->link."silo/tmp/".$dl_filename.'" target="_blank">'.$page->drawGeneric("click here", 1).'</a> '.$page->drawLabel("tl_acc_dl_msg1", "to view the administrator listing").'</p>';
	echo treelineBox($html, $page->drawLabel("tl_acc_dl_title", "All Treeline administrators"), "blue");
}

?>
</div>
</div>

<?php
if ($userid>0) {
	?>
	<script type="text/javascript" src="/treeline/includes/ckeditor/ckeditor.js"></script>
	<script type="text/javascript">
	CKEDITOR.replace('f_img', { toolbar : 'contentImageOnly', height: '200px', width: '200px' });
	</script>
	<?php
	}
?>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>