<?php
	class User {
	
		// A couple of conventions:
		
		// load() loads an object
		// save() saves an object
		// get() gets a value
		// put() updates a value ?
		// draw() outputs data as HTML, or a string usable in an HTML page
	
//		private $guid;
	
		public $id;
		public $name;
		public $full_name;
		public $group;
		public $email;
		public $password;
		public $status;

		public $job, $organisation, $portrait;
		
		public $errmsg = array();
		
		public function __construct() {
			// This is loaded when the class is created	
		
		}
		
		public function __destruct(){
			return true;
		}
		
		public function getID() {
			return $this->id;
		}
		public function setID($id){
			$this->id = $id;
		}
		
		public function getName() {
			return $this->name;
		}
		public function setName($name){
			$this->name = $name;
		}	
		public function getFullName() {
			return $this->full_name;
		}
		public function setFullName($full_name){
			$this->full_name = $full_name;
		}
		public function getGroup() {
			return $this->group;
		}
		public function setGroup($group){
			$this->group = $group;
		}
		public function drawGroup() {
			global $db;
			$data = $db->get_var("SELECT name FROM `groups` WHERE id = '{$this->group}'");
			return $data;
		}
		
		
		public function getEmail() {
			return $this->email;
		}
		public function setEmail($thisemail){
			
		// from php.net
		   $x = '\d\w!\#\$%&\'*+\-/=?\^_`{|}~';    //just for clarity
		
		   if( count($email = explode('@', $thisemail, 3)) == 2
			   && strlen($email[0]) < 65
			   && strlen($email[1]) < 256
			   && preg_match("#^[$x]+(\.?([$x]+\.)*[$x]+)?$#", $email[0])
			   && preg_match('#^(([a-z0-9]+-*)?[a-z0-9]+\.)+[a-z]{2,6}.?$#', $email[1]) ){
			
				$this->email = $thisemail;
				return true;
			}else{
				return false;
			}
		}
		public function getPassword() {
			return $this->password;
		}
		public function setPassword($password){
			$this->password = $password;
		}
		
		public function getStatus() {
			return $this->status;
		}
		public function setStatus($status){
			$this->status = $status;
		}
		
		public function loadByID($id) {
			global $db;
			$data = $db->get_row("SELECT * FROM users WHERE id = $id");	
			if ($db->num_rows > 0) {
				$this->id = $data->id;
				$this->name = $data->name;
				$this->full_name = $data->full_name;
				$this->group = $data->group;
				$this->email = $data->email;
				$this->job = $data->job;
				$this->organisation = $data->organisation;
				$this->portrait = $data->portrait;
				$this->password = $data->password;
				if ($data->blocked == 1) {
					$this->status = 'blocked';
				}
				else {
					$this->status = 'logged in';
				}
			}
			else {
				$this->status = 'logged out';
			}
			//print "Loaded(".print_r($this, 1).")<br>\n";
		}
		
		public function getDetailsFromEmail($email) {
			global $db;
			return $db->get_results("SELECT u.id, u.name, u.full_name, u.email, u.password, 
				s.title, s.primary_msv as msv
				FROM users u
				LEFT JOIN groups g ON u.`group`=g.id
				LEFT JOIN sites s ON g.domain=s.primary_msv
				WHERE email = '$email'");
		}
		
		
		public function update($id){
			global $db;
			//// user a user record...
			if($id){
				$name = $this->getName();
				$full_name = $this->getFullName();
				$email = $this->getEmail();
				$password = $this->getPassword();
				$group = $this->getGroup();
				$status = $this->getStatus();

				$query = "UPDATE users SET 
					name='$name', full_name='$full_name', email='$email', 
					`group`='$group', `password`='$password', 
					blocked='$status' 
					WHERE id='$id'";
				//print "$query<br>\n";
				$db->query($query);
				if($db->rows_affected==1){
					return true;
				}
			}else{
				return false;
			}
		}


		public function save(){
			global $db;
			//// save a new user record...
			$name = $this->getName();
			$full_name = $this->getFullName();
			$email = $this->getEmail();
			$password = $this->getPassword();
			$group = $this->getGroup();
			$status = $this->getStatus();
			
			$query = "INSERT INTO users (name,full_name,email,`password`,`group`,blocked,date_added) VALUES
						('". $name ."','". $full_name ."','". $email ."','". $password ."',". $group .",". $status .",NOW())";
			if($db->query($query)){
				$this->id=$db->insert_id;
				return true;
			}else{
				return false;
			}
		}		
		
		public function delete($id){
			//// delete a user's record...
			global $db;
			
			if($id){
				if($db->query("DELETE FROM users WHERE id='".$id ."'")){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		
		public function userExists($name){
			global $db;
			
			if($db->query("SELECT id FROM users WHERE name='". $name ."'") ){
				return true;
			}else{
				return false;
			}
		}
		
		//// return an array of groups to save repetition...
		public function getGroupList($orderby='id',$orderdir='ASC'){
			global $db, $site;
			
			$query = "SELECT id,name FROM groups WHERE domain=".$site->msv." ORDER BY $orderby $orderdir";
			if($groups = $db->get_results($query)) {
				return $groups;
			}else{
				return false;
			}
		}
		
		public function drawGroupsList($selected=false,$selectname='type'){
			global $site, $page;
			$groups = $this->getGroupList();
			$html ='';
			
			if($groups){
				$html .= '<select name="'. $selectname .'" id="'. $selectname .'">'. "\n\t";
				$html .= '<option value="">'.$page->drawLabel("tl_acc_user_group", "Select a group").'</option>'."\n\t";
				foreach($groups as $group){
					$selectedtxt = ($selected==$group->id)? ' selected':false;
					$html .= '<option value="'. $group->id .'"'. $selectedtxt .'>'.$page->drawGeneric($group->name, 1).'</option>'."\n\t";
				}
				$html .= '</select>'."\n\n";
				
				return $html;
			}else{
				return false;
			}
		}
		
		
		
		public function getUsersList($field=false,$item=false){
			global $db, $site, $page, $help;
			$condition='';
			$html = '';
			
			//// this checks for 
			$condition="WHERE u.id!=1 ";
			if($field && $item)	$condition .= "AND $field LIKE '%".$item."%' ";
			
			$query = "SELECT u.id as id, u.name as name ,u.full_name as full_name,u.email as email,u.`group` as `group`, 
						g.name as group_name,u.blocked as `status`, u.date_added as date_added 
						FROM users u 
						LEFT JOIN groups g ON u.`group`=g.id ".$condition." AND g.domain=".$site->id." ORDER BY date_added DESC";	

			//print "$query<br>\n";
			$users = $db->get_results($query);
			if($db->num_rows>=1){
				$html .= '<table class="tl_list">
<caption>&nbsp;</caption>
<thead><tr>
	<th scope="col">'.$page->drawGeneric("Full name", 1).'</th>
	<th scope="col">'.$page->drawGeneric("username", 1).'</th>
	<th scope="col">'.$page->drawGeneric("type", 1).'</th>
	<th scope="col">'.$page->drawLabel("tl_usr_list_date", "Date added").'</th>
	<th scope="col">'.$page->drawLabel("tl_usr_list_manage", "Manage user").'</th>
</tr></thead>
<tbody>
';
				foreach($users as $user){

					$editlink= '<a '.$help->drawInfoPopup($page->drawLabel("tl_usr_list_edit", "Edit user")).' class="edit" href="/treeline/access/?action=edit&userid='.$user->id.'">Edit user</a>';
					$dellink= '<a '.$help->drawInfoPopup($page->drawLabel("tl_usr_list_delete", "Delete user")).' class="delete" href="/treeline/access/?action=delete&userid='.$user->id.'">Delete user</a>';
				
					$html .= '<tr><td><strong>'. $user->full_name.'</strong>
'.($user->status==1?' <span style="color:#a33">[blocked]</span>':"").'
</td><td><a href="mailto:'.$user->email .'" title="email '.$user->full_name.'">'. $user->name .'</a></td><td>'. $user->group_name.'</td>
<td>'. date('jS F Y',strtotime($user->date_added)) .'</td>
<td class="action">
'.$editlink.'
'.$dellink.'
</td>
</tr>
';
				}
				
				$html .= '</tbody></table>'."\n\n";
			}
			else $html .= '<div class="feedback error"><h3>'.$page->drawGeneric("warning", 1).'</h3><p>'.$page->drawLabel("tl_usr_err_nomem", "There are no users to display").'</p></div>';
			return $html;
		}
		
		public function sendPasswordReminder($email){
			//
			global $db;
			
			$details = $this->getDetailsFromEmail($email);
			$emailContent = " Hello ".$details->name."\n\n You recently requested your login details for your Treeline Content Management System so here they are: \n\n username: ".$details->name."\n password: ".$details->password."\n\n Thanks,\n The Treeline Team\n Ichameleon";
			
			
			$to  = $details->full_name .'<'.$email.'>';
			$subject = 'Treeline password reminder';
			$headers = "From: Treeline <treeline@ichameleon.com> \r\n";
			$headers .= 'X-Mailer: PHP/' . phpversion();
			if(@mail($to, $subject, $emailContent, $headers)){
				$message = 'Your password has been emailed to you. Please check your inbox';
				$feedback = 'success';
				redirect('/treeline/login/?'.createFeedbackURL($feedback, $message));
			} else{
				return false;
			}
			
		}
		
		// 27th Jan 2009 - Phil Redclift
		// Create a CSV listing of all administrators on the site
		public function generateCSV($userid=0) {

			global $db;		
			$query = "SELECT u.id, u.name username, u.password, full_name, u.email, u.blocked, u.date_added,
				g.name as `group`, s.name as sitename
				FROM users u
				LEFT JOIN groups g ON u.`group`=g.id
				LEFT JOIN sites s ON g.domain=s.primary_msv
				WHERE u.id > 1
				ORDER BY g.domain, u.full_name";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
					$html.='"'.$result->sitename.'","'.$result->full_name.'","'.$result->group.'","'.$result->email.'","'.$result->username.'","'.$result->password.'","'.($result->blocked?"blocked":"live").'","'.$result->date_added.'"'."\n";
				}
				if ($html) {
					$html='"microsite","full name","group","email","username","password","status","date_created"'."\n".$html;
					$filename="ulist-".date("YmdHis",time()).".csv";
					$filepath = $_SERVER['DOCUMENT_ROOT']."/silo/tmp/".$filename;
					//print "try to write to $filepath<br>\n";
					if (file_put_contents($filepath, $html)) {
						return $filename;
					}
				}
			}
			return false;
		}
		
		public function updateNotifications($data) {
			global $db, $site;
			if (!$this->id) return '';
			$query = "DELETE FROM user_notify WHERE user_id=".$this->id;
			$db->query($query);

			$allids = explode(",", $data['allids']);
			foreach ($allids as $id) {
				if ($id>0) {
					//print "check nid_".$id." v(".$data['nid_'.$id].")<br>\n";
					if (!$data['nid_'.$id]) {
						//print "opt out of $id<br>\n";
						$query = "INSERT INTO user_notify (newsletter_id, user_id, notify) 
							VALUES 
							($id, ".$this->id.", 0)
							";
						if (!$db->query($query)) {
							$this->errmsg[]="Failed to opt out of notification ID[$id]";
						}
					}	
				}
			}
			return !count($this->errmsg);
		}

		public function drawNotifications() {
			global $db, $site;
			if (!$this->id) return '';
			$html = '';
			$query = "SELECT n.id, ".($site->id>1?"IF(n2.msv,n2.subject,n.subject) AS ":"")."subject, 
				IF(un.notify IS NULL, 1, un.notify) AS notify
				FROM newsletter n
				LEFT JOIN user_notify un ON un.newsletter_id = n.id AND un.user_id=".$this->id."
				".($site->id>1?"LEFT JOIN newsletter n2 ON n.text3 = n2.text3 AND n2.msv=".$site->id:"")."
				WHERE n.`status`='S'
				AND n.msv = 1
				AND (user_id = ".$this->id." OR user_id IS NULL)
				ORDER BY subject
				";
			//print "$query";
			if ($results = $db->get_results($query)) {
				foreach($results as $result) {
					$all.=$result->id.",";
					$html.= '<div>
	<label for="f_nid_'.$result->id.'">'.$result->subject.'</label>
	<input type="checkbox" class="not-opt" id="f_nid_'.$result->id.'" name="nid_'.$result->id.'" value="1" '.($result->notify?' checked="checked"':'').' />
</div>
';
				}
				$html.='<input type="hidden" name="allids" value="'.$all.'" />';
				return $html;
			}
		}
	}					
?>