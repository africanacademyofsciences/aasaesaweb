<?php

/*
	===============
	Members Class
	===============
	
	written by: Phil Thompson 14/07/2007
	Update by : Phil Redclift 12/12/2008
	
	Table of Contents
	-----------------
	
	add()
	delete()
	edit()
	approve()
	addMembersPreferences()
	emailMember()
	
	getAll()
	getById()
	getTotal()
	

*/
class Member{

	
	private $mode="";	// only used if we would like CSV output.
	public $new_member_id;
	public $details;
	public $errmsg = array();
	public $showallmembers = false;
	
	// 12/12/2008 Comment
	// Function to add a new member and configure their newsletter preferences.		
	public function add($type = ''){

		// add a members to the system
		global $db, $site, $page;
		
		$error = 0; // set error to 0. Increment everytime theres is an error.
		
		$firstname = $db->escape($_POST['firstname']); // firstname
		$surname = $db->escape($_POST['surname']); // surname
		$email = $db->escape($_POST['email']); // email
		$password = $db->escape($_POST['password']); // password
		$address1 = $db->escape($_POST['address1']); // address line 1
		$address2 = $db->escape($_POST['address2']); // address line 2
		$address3 = $db->escape($_POST['address3']); // address line 3
		$postal_code = $db->escape($_POST['postal_code']); // postal code
		$telephone = $db->escape($_POST['telephone']); // telephone
		$further_info = $db->escape($_POST['further_info']) ; // further information
		$terms = $db->escape($_POST['terms']); // terms
		$bloggable = $_POST['bloggable'];
		$forumable = $_POST['forumable'];

		// You can only sign up to membership type 1 (default member) from the website.
		$member_type = 1;
		$new_status = 'N';
		
		if ($type=="admin") {
			$member_type = $_POST['member_type'];	// Whatever we put in the TL form
			$new_status = 'A';						// New members from TL are auto active
			$label_prefix = "tl_";
		}
		
		//$new_member_id = $this->checkEmail($email);
		//if ($type=="admin") print "Got new member ID($new_member_id)<br>\n";

		$new_member_id = $this->memberExists($email);
		//if ($type=="admin") print "Got new member ID($new_member_id)<br>\n";

		// If this member does not exist we need to create them.
		if (!$new_member_id) {
			// check all required data is present and valid
			if($firstname && $surname && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && $password && $terms)
			{
			
				//$query = "INSERT INTO members (firstname, surname, email, password, address1, address2, address3, postal_code, telephone, further_info, terms, date_added) VALUES ('$firstname', '$surname', '$email', '$password', '$address1', '$address2', '$address3', '$postal_code', '$telephone', '$further_info', '$terms', Now());";
				$query = "INSERT INTO members 
					(firstname, surname, email, password, telephone, 
					 further_info, terms, date_added) 
					VALUES 
					('$firstname', '$surname', '$email', '$password', '$telephone', 
					 '$further_info', '$terms', Now());";
				// run query
				//print "insert($query)<br>\n";
				if($db->query($query)){ 
					$new_member_id=$db->insert_id;
				}
				// failed to create member record
				else { 
					$this->errmsg[] = 'Due to a technical error, the member could not be created. Please try again later or contact your support team.';
				}
			}
			else{ // vital data missing
				$error++;
				$this->errmsg[] = $page->drawLabel($label_prefix."mem_err_data", 'Membership application failed because the following vital information was missing');
				
				if(!$firstname) $this->errmsg[] = ucfirst($page->drawLabel($label_prefix."generic_firstname", 'Firstname'));
				if(!$surname) $this->errmsg[] = ucfirst($page->drawLabel($label_prefix."generic_surname", 'Surname'));
				if(!$email) $this->errmsg[] = ucfirst($page->drawLabel($label_prefix.'generic_email', "Email address"));
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $this->errmsg[]=$page->drawLabel($label_prefix."err_evalid", "Email address is not valid");
				if(!$password) $this->errmsg[] = ucfirst($page->drawLabel($label_prefix."generic_password", 'Password'));
				if(!$terms) $this->errmsg[] = $page->drawLabel($label_prefix.'generic_terms', 'Must agree to terms');
				//if(!$telephone) $message[] = 'Telephone';
			}
		}
		
		if (!count($this->errmsg)) {
			if ($new_member_id>0) {
				$this->addToSite($new_member_id, $site->id, array("member_type"=>$member_type, "forumable"=>$forumable, "bloggable"=>$bloggable), $type=="admin");
			}
			else $this->errmsg[] = "This member could not be found or created but there was no error??";
		}

		

		// No error, 
		// redirect new website member & give feedback
		if (!count($this->errmsg) && $type!="admin") {
			$url=$site->link.'member-login/';
			redirect($url.createFeedbackURL('success', "Your details have been updated"));
			exit();
		}
		
		return !count($this->errmsg);
		
	}
	
	public function addToSite($member_id, $msv=0, $data=array(), $admin=false) {
		global $db, $site, $admin;
		//print "aTS($member_id, $msv, \n".print_r($data,true)."\n)<Br>\n";
		
		if (!$msv) $msv=$site->id;
		if (!$data['member_type']) $data['member_type']=1;

		$new_status = ($admin)?'A':'N';
		$new_status =  'A'; // Active everybody.
		if ($data['member_type']==6) $new_status = 'N';
				
		// Even if its an old member record we need the new member id
		$this->new_member_id = $member_id;
	 
		// Check if they are already signed up to this site and create the link if not.
		$query = "SELECT id, `status` FROM member_access WHERE member_id=$member_id AND msv=".$msv." LIMIT 1";
		//print "$query<br>\n";
		if ($row = $db->get_row($query)) {
			$set = "blog_allowed=".($data['bloggable']+0).", 
				type_id=".($data['member_type']+0).", 
				forum_allowed=".($data['forumable']+0)." ";
			// If deleted reactivate membership.
			if ($row->status=="X" && $admin) $set.=", `status`='A' ";
			$query="UPDATE member_access 
				SET $set
				WHERE id = ".$row->id. " 
				";
			//print "$query<br>\n";
			//$this->errmsg[]="Update member access is a bit odd although the member may still have been created ok";
			$db->query($query);
			if ($db->last_error) {
				$this->errmsg[] = "Failed to update existing member access record";
				return false;
				//$this->errmsg[] = $query;
			}
		}
		// No current member access record exists so add them
		else {
			$query="INSERT INTO member_access 
				(
					member_id, msv, status, 
					blog_allowed, type_id, forum_allowed, hearabout
				) 
				VALUES 
				(
					$member_id, ".$site->id.", '$new_status', 
					".($data['bloggable']+0).", ".($data['member_type']+0).", ".($data['forumable']+0).",
					'".$db->escape($data['hearabout'])."'
				)";
			//print "$query<br>\n";
			if (!$db->query($query)) {	
				$this->errmsg[] = "Failed to insert new member access record";
				return false;
			}
		}
		
		if ($data['member_type']==2) $this->subscribeFellow($member_id);
		
		return true;
	}
	

	// **********************************************
	// 12/12/2008 Comment
	// Validate and update member data
	public function edit($id, $type= ''){
		global $db, $site, $page;
		
		$error = 0; // set error to 0. Increment everytime there is an error.

		$firstname = $db->escape($_POST['firstname']); // firstname
		$surname = $db->escape($_POST['surname']); // surname
		$email = $db->escape($_POST['email']); // email
		$oldemail = $db->escape($_POST['oldemail']); // email
		$password = $db->escape($_POST['password']); // password
		$telephone = $db->escape($_POST['telephone']); // telephone
		$further_info = $db->escape($_POST['further_info']) ; // further information
		//$address1 = $db->escape($_POST['address1']); // address line 1
		//$address2 = $db->escape($_POST['address2']); // address line 2
		//$postal_code = $db->escape($_POST['postal_code']); // postal code
		$terms = $db->escape($_POST['terms']); // terms
		$preference = $_POST['preference']; 
		$bloggable = $_POST['bloggable'];
		$forumable = $_POST['forumable'];
		
		// You can only sign up to membership type 1 (default member) from the website.
		$member_type = ($type == 'admin') ? $_POST['member_type'] : 1;
		$paid_date = $_POST['paid_date']+0;
		
		if (in_array($member_type, array(5, 6))) {
			// Researchers and Funders don't need a paid date. 
			// Might be only fellows that do but not sure so just put these two for now.
		}
		else {
			$thisyear = date("Y", time());
			if ($paid_date < ($thisyear-1)) {
				$this->errmsg[] = "You must set the paid date to at least ".($thisyear-1);
				$paid_date = '';
			}
			else if ($paid_date>($thisyear+100)) {
				$this->errmsg[] = "You cannot prepay someone for more than 100 years (max:".($thisyear+100).")";
				$paid_date = '';
			}
			// print "Set paid($paid_date)<br>\n";		
		}
		//print "email($email) oldemail($oldemail)<br>\n";
		
		// check for data
		if($firstname && $surname && $terms){

			// set blog allowed first
			$query = "UPDATE member_access 
				SET blog_allowed=".($bloggable+0).", forum_allowed=".($forumable+0).", type_id=".($member_type+0)."
				WHERE member_id=".$id." 
				AND msv=".$site->id." 
				";
			//print "$query<br>\n";
			$db->query($query);
			if ($db->last_error) {
				$this->errmsg[] = $page->drawLabel("tl_mem_err_access", "Failed to update member access details but will still try to update the member record");
				$db->last_error = '';
			}
			
			//address1 = '$address1', address2 = '$address2', address3 = '$address3', postal_code = '$postal_code', 
			$query = "UPDATE members SET 
				firstname = '$firstname', surname = '$surname', 
				telephone = '$telephone', password='$password',
				email = '$email',
				further_info = '$further_info', terms = '$terms', date_edited = Now() 
				";
			if ($paid_date) $query.=", paid_date = '".$paid_date."-01-01' ";
			$query .= "WHERE member_id = $id";
			//print "$query<br>\n";
			$db->query($query); 
			if ($db->last_error) {
				$this->errmsg[] = 'Due to a technical error, it has not been possible to update your details';
			}
			else if ($member_type == 2) {
				$this->subscribeFellow($id);
			}
		}
		
		// vital data missing
		else{ 

			$this->errmsg[] = $page->drawLabel("tl_mem_err_input", 'This member has not been updated due to a problem with the input');
			
			//if(!$email) $this->errmsg[] = $page->drawLabel("tl_mem_err_email", 'Email address not entered');
			//else if($this->checkEmail($email)) $this->errmsg[] =  $page->drawLabel("tl_mem_err_emailuse", 'That email address is already in use');
			if(!$firstname) $this->errmsg[] = $page->drawLabel("tl_mem_err_first", 'You must enter a first name for this firstname');
			if(!$surname) $this->errmsg[] = $page->drawLabel("tl_mem_err_surname", 'You must enter a surname for this member');
			if(!$terms) $this->errmsg[] = $page->drawLabel("tl_mem_err_terms", 'You must click to say you agree to the terms and conditions');
			//if(!$telephone) $message[] = 'Telephone';
		}
		
		return !count($this->errmsg);
	}


	private function subscribeFellow($member_id) {
		$mcdebuglevel = 0;

		if ($mcdebuglevel>0) print "<!-- sF($member_id) -->\n";
		$fellow_list = "AAS Fellows (updated)";
		
		$mc = new mc($mcdebuglevel);
		if (!$mc->subscribeToList($member_id, $fellow_list, true)) {
			foreach ($mc->errmsg as $tmp) $this->errmsg[] = $tmp;
			$this->errmsg[] = "Failed to add fellow to Mailchimp list";
			addHistory($_SERVER['treeline_user_id'], "Subscribe fellow[fail]", "", "Member-".$member_id);
		}
		else addHistory($_SERVER['treeline_user_id'], "Subscribe fellow", "", "Member-".$member_id);
	}
	
	
	// 12/12/2008 Comment
	// Totally remove a member from the database?????
	// We probably should'nt. Just change their status to X
	public function delete($id){
		global $db, $site;
		
		if (!$id>0) return false;
		
		$query = "UPDATE member_access SET status = 'X' WHERE member_id=$id AND msv=".$site->id;
		//print "$query<br>\n";
		$db->query($query);
		if ($db->rows_affected) {
			$feedbackType = 'success';
			$message = 'You have successfully deleted that member';
		}
		else{
			$feedbackType = 'error';
			$message = 'Failed to delete member. Please try again later.';
		}
		redirect('/treeline/members/?'.createFeedbackURL($feedbackType,$message));
	}
		
		
	// check to see if a user is logged in, if they're not send them to a log in page
	public function checkLogin(){
		global $site;
		//print "member(".$_SESSION['member_id'].") logged in to site(".$_SESSION['member_site_id'].") == site(".$site->id.")<br>\n";
		if($_SESSION['member_id']>0 && $_SESSION['member_site_id']==$site->id){
			return true;
		} 
		return false;
	}
		


	public function logIn(){

		global $db, $loginRedirect, $site;
		$error = 0; // set error as none and increment whenever one occurs
	
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$email = $db->escape($_POST['email']);
			$password = $db->escape($_POST['password']);
			// are all variables present
			if($email && $password){
				// check user from database
				$query = "SELECT ma.status, ma.type_id,
					m.password 
					FROM members m
					LEFT JOIN member_access ma ON m.member_id=ma.member_id
					WHERE m.email = '$email' 
					AND ma.msv=".$site->id." AND ma.status='A'
					AND ma.type_id IN (2,4,6)
					LIMIT 0, 1";
				print "<!-- check login($query)-->\n";
				$row = $db->get_row($query);
				if(!$row->password){ // unrecognised user/ misspelt email address
					$message[] = 'Your email address has not been recognised';
				} 
				else if($row->password != $password){ // incorrect password
					$message[] = 'Your password is incorrect';
				} 
				// Login User
				else if ($row->password == $password) { 
					$query = "SELECT * FROM members WHERE email = '$email' AND password = '$password' LIMIT 0, 1";
					$userDetails = $db->get_row($query);
					// set sessions: to be used throughout the site for permissions/form processing etc
					session_start();
					$_SESSION['member_id'] = $userDetails->member_id;
					$_SESSION['member_type'] = $row->type_id;
					$_SESSION['member_site_id']= $site->id;
					$_SESSION['member_name'] = $userDetails->firstname.' '.$userDetails->surname;
					$_SESSION['member_email'] = $userDetails->email;
					$_SESSION['member_organisation'] = $userDetails->organisation;
					
					if ($_POST['fwd']) redirect($_POST['fwd']);
				}
			} 
			// missing data
			else{ 
				if(!$email) $message[] = 'You have not entered an email address';
				if(!$password) $message[] = 'You have not entered a password';
			}
			return $message;						
		} 
	}
		
	public function logOut(){
		// quit destroying the session, it logs me out of Treeline
		foreach ($_SESSION as $k => $v) {
			if (substr($k, 0, 6)=="member") unset($_SESSION[$k]);
		}

	}

		
		public function updatePassword($id,$password){
			// approve a member
			global $db;
			
			$query = "UPDATE members SET password = '".$db->escape($password)."' WHERE member_id = $id LIMIT 1;";
			if($results = $db->query($query)){
				return true;
			}
			else{
				return false;
			}
			
		}
		
		public function approve($id){
			// approve a member
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			// query
			$query = "UPDATE members SET status = 1 WHERE member_id = $id";
			// run query
			if($results = $db->query($query)){ /// success
				$message = 'You have successfully approved this member';
				$this->emailMember($id,'approval');
			}
			else{ // failure
				$error++;
				$message = 'Due to a technical error, you have failed to approve this member.';
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				redirect('/treeline/members/?'.createFeedbackURL('error',$message));
			}
			else{ // no error: all went well
				redirect('/treeline/members/?'.createFeedbackURL('success',$message));
			}
			
		}
		
		
		public function addMembersPreferences($id, $preferences){
			//
			global $db;
			
			// remove current
			$query = "DELETE FROM members_preferences WHERE member_id = $id";
			
			$db->query($query);
			
			// NOW add new preferences
			$queryCount = 0;
			
			foreach($preferences as $preference => $preference_id){
				$query = "INSERT INTO members_preferences (member_id, preference_id) VALUES ($id, $preference_id);";
				if($db->query($query)){
					$queryCount++;
				}
			}
			
			if($queryCount > 0){
				return true;
			}
			else{
				return false;
			}
		}
		
		
		public function emailMember($id, $type){
				global $site;
				$success = false;
				$newsletter = new Newsletter();
				$details = $this->getById($id);
				$data=array("FIRSTNAME" => $details->firstname,
					"SURNAME" => $details->surname,
					"FULLNAME" => $details->firstname." ".$details->surname,
					"USERNAME" => $details->email,
					"EMAIL" => $details->email,
					"PASSWORD" => $details->password,
					"SITENAME" => $site->name
					);
					
				switch($type){
					case 'forgotpassword':
						$success = $newsletter->sendText($details->email, "MEMBER_FORGOT_PASSWORD", $data, false);
						break;
					default:
						mail("phil.redclift@ichameleon.com", $site->name." member email($type)", "This email does not exist???");
						break;
				}
				return $success;
			}
		
		
	// 28th Jan 2009 - Phil Redclift
	// Get member data
	// Option status and search term can be used to filter results
	// Only returns members for the current microsite.
	public function getAll($orderBy, $search = NULL, $status = '', $currentPage = 1, $perPage = 20){

		global $db, $site;
		//print "member.gA($orderBy, $search, $status, $currentPage, $perPage)<br>\n";
				
		// QUERY ORDERING
		switch($orderBy){
			case 'newest';
			case 'latest';
			case 'date';
				$orderBy = 'm.date_added DESC'; // order by newest users
				break;
			case 'oldest';
				$orderBy = 'm.date_added ASC'; // order by oldest users
				break;
			case 'surname_za';
				$orderBy = 'm.surname DESC'; // order by surname Z-A
				break;
			case 'firstname_az';
				$orderBy = 'm.firstname ASC'; // order by firstname A-Z
				break;
			case 'firstname_za';
				$orderBy = 'm.firstname DESC'; // order by firstname Z-A
				break;
			case 'email_az':
				$orderBy = 'm.email ASC';
				break;
			case 'email_za':
				$orderBy = 'm.email DESC';
				break;
			default:
			case 'surname_az';
				$orderBy = 'm.surname ASC'; // order by surname A-Z
				break;
		}
		
		// QUERY FILTERING
		if ($status) $where="ma.status='$status' ";
		
		// USER SEARCH: FILTER
		if ($search) $where = "(m.firstname LIKE '%$search%' OR m.surname LIKE '%$search%' OR m.email LIKE '%$search%') ";
		

		// What to pull from the database?
		$select = "m.*, m.member_id AS member_id, ma.`status`, mt.title AS member_type";
		
		// Query limitations 
		$limit = "LIMIT ".getQueryLimits($perPage,$currentPage); 
		
		// In CSV mode we pull different data and dont limit the query.
		if ($this->mode=="CSV") {
			$select = "m.firstname, m.surname, ms.title, m.date_added, m.email";
			$limit='';	// Get all matching members for CSV
		}
		
		$query = "SELECT $select 
			FROM members m 
			LEFT JOIN member_access ma ON ma.member_id=m.member_id
			LEFT JOIN member_status ms ON ms.status=ma.status
			INNER JOIN member_types mt ON mt.id = ma.type_id
			WHERE ma.`status`!='X' 
			AND ma.msv=".$site->id." ";
		if (!$this->showallmembers) $query .= "AND ma.type_id > 1 ";
		if ($where) $query .= "AND ".$where." ";
		$query .= "
			GROUP BY m.member_id 
			ORDER BY $orderBy $limit";
		//print "$query<br>\n";
		$results = $db->get_results($query);
		
		return $results;
	}
	// 12/12/2008 Comment
	// Get a list of currently requested members for CSV output.
	public function getAllForCSV($orderBy, $search= NULL, $status = '') {
		$this->mode="CSV";
		return $this->getAll($orderBy, $search, $status);	
	}
	
	// *********************************************************
	// 12/12/2008 Comment
	// Load member data by member id.
	public function getById($id,  $status = '') {
		return $this->loadByID($id, $status);
	}


	// return a specific members info from a supplied id
	public function loadByID($id) {
		global $db, $site;
	
		//print "gBI($id)<br>\n";
		$query = "SELECT m.*,
			DATEDIFF(NOW(), m.paid_date) AS last_paid,
			DATE_FORMAT(m.paid_date + INTERVAL 1 YEAR, '%D %b %Y') AS renewal_due,
			DATEDIFF(NOW(), m.paid_date + INTERVAL 1 YEAR) AS renewal_overdue_days,
			DATE_FORMAT(m.paid_date, '%Y') AS paid_year,
			ma.id as access_id, 
			ma.status AS status, 
			ma.blog_allowed as bloggable,
			ma.type_id AS member_type,
			ma.forum_allowed as forumable,
			mp.id AS profile_id,
			mp.profile_text as profile,
			mp.blog_title AS blogtitle,
			mp.blog_comments as blogcomments,
			mp.expertise AS expertise,
			mp.email2 AS email2,
			mt.title AS type_name,
			sc.title AS countrytitle
			FROM members m
			INNER JOIN member_access ma ON m.member_id=ma.member_id
			LEFT JOIN member_profile mp ON mp.access_id = ma.id
			INNER JOIN member_types mt ON mt.id = ma.type_id
			LEFT JOIN store_countries sc ON sc.country_id = m.country
			WHERE m.member_id = '$id' 
			AND ma.msv=".$site->id."
			LIMIT 1";
		//print "<!-- $query -->\n";
		$row = $db->get_row($query);
		$this->details = $row;
		return $row;
	}

	// 12/12/2008 Comment
	// Get the total number of members returned by the current search parameters.
	public function getTotal($search, $status='') 
	{
		global $db, $site;

		// QUERY FILTERING
		if ($status) $where=" ma.status='$status' AND";
		// USER SEARCH: FILTER
		if ($search) $where = " (m.firstname LIKE '%$search%' OR m.surname LIKE '%$search%' OR m.email LIKE '%$search%') AND";
		if ($where) $where=" AND ".substr($where,0,-4);		
		
		$query = "SELECT COUNT(ma.member_id) 
			FROM members m 
			LEFT JOIN member_access ma ON m.member_id=ma.member_id
			LEFT JOIN member_status ms ON ma.status=ms.status
			WHERE ma.`status`!='X' 
			AND ma.msv=".$site->id." ";
		if (!$this->showallmembers) $query .= "AND ma.type_id > 1";
		$query .= $where." ";
		//print "$query<Br>\n";
		$total = $db->get_var($query);
		
		return $total;
	}
		
		
		
		public function getPreferences(){
			//
			global $db;
			
			$query = "SELECT * FROM newsletter_preferences ORDER BY preference_title ASC";
			
			$results = $db->get_results($query);
			
			return $results;
		}
		
		public function getMemberPreferencesById($id){
			//
			global $db;
			
			$query = "SELECT * FROM members_preferences mp LEFT JOIN newsletter_preferences p ON mp.preference_id = p.preference_id WHERE mp.member_id = $id ORDER BY p.preference_title ASC";
			$results = $db->get_results($query);
			
			if($results){
				foreach($results as $result){
					$preferences[] = $result->preference_id;
				}
			}
			
			return $preferences;
		}
		
		public function getMemberImage($member_id) {
			$img = "/silo/upload/members/mem-".$member_id.".jpg";
			//print "check if img($img) exists<br>\n";
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
				return $img;
			}
			return "";
		}
		
	public function getProfileID($access_id) {
		global $db;
		//print "cP($access_id)<br>\n";
		if (!$access_id>0) return false;
		
		$query = "SELECT id FROM member_profile WHERE access_id = $access_id";
		//print "$query<br>\n";
		$profile_id = $db->get_var($query);
		if ($profile_id>0) return $profile_id;
		else {
			$query = "INSERT INTO member_profile (access_id) VALUES ($access_id)";
			//print "$query<br>\n";
			if ($db->query($query)) return $db->insert_id;
		}
		return false;
	}			
		
	// 12/12/2008 Comment
	// Make sure an email address added or editted does not already exist in the database.
	// Return true if the email exists already
	public function checkEmail($email, $oldemail=''){
		global $db, $site;
		
		// If email address has not been changed we dont need to check it.
		if ($oldemail==$email) return 0;
		
		$query = "SELECT m.member_id FROM members m
			INNER JOIN member_access ma ON ma.member_id = m.member_id
			WHERE m.email = '$email'
			AND ma.msv=".$site->id."
			AND ma.`status`= 'A'
			AND ma.type_id IN (2,4,5,6) ";
		//print "<!-- $query -->\n";
		return $db->get_var($query);
	}
	
	public function memberExists($email) {
		global $db;
		$query = "SELECT m.member_id FROM members m
			INNER JOIN member_access ma ON ma.member_id = m.member_id
			WHERE m.email = '$email'
			";
		//print "<!-- $query -->\n";
		return $db->get_var($query);
	}
		
}

?>