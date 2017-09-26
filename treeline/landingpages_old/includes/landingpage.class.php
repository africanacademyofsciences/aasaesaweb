<?php

	/*
		===============
		LANDING PAGE CLASS
		---------------
		Purpose: Allow management of section landing pages.
		
		Disclaimer: I am not an OO-Rockstar. This class may not be perfectly written.
		
		===============
		
		written by: Phil Thompson
		when: 25/05/2007
		
		edited by: Dan Donald
		when: 11/10/2007
	
		Table of Contents
		-----------------
		
		
		variables
		
		construct
		
		get
		set
		
		add()
			addPage()
		delete()
			deletePage()
		edit()
			editPage()
		
		getAll()
		getById()
			getPageById()
			getPagesByParent()
		getTotal()
		
	
	*/

	class LandingPage{
	
		
		private $currentPage;
		private $perPage;
		private $orderBy;
		private $id;
		private $page_id;
		private $search;
		private $properties = array();
		private $pages;
		private $total;
		
		public $siteHomeGUID = 1; // needs to change for each microsite
	
		public function __construct($currentPage, $perPage, $orderBy, $id, $page_id, $search){
		
			if($id != ''){
				$this->id = $id; 
				$this->orderBy = $orderBy>'' ? $orderBy : 'x_sort_order';
				$this->getById($id);
				$this->getPagesByParent($id);
				
			}
			else if($page_id != ''){
				$this->page_id = $page_id; 
				$this->getPageById($page_id);
			}
			else if(!$action){
				$this->currentPage = $currentPage;
				$this->perPage = $perPage;
				$this->orderBy = $orderBy;
				$this->search = $search;
				$this->getAll($orderBy, $search, $currentPage, $perPage);
				$this->getTotal($search);
			}
		
		}
		
		private function __get($attribute){	
			$method = str_replace(' ','','get'.ucwords( str_replace('_',' ',$attribute) ) );
			
			if(isset($this->$attribute)){
				return $this->$attribute;
			} 
			else if(method_exists($this,$method)){
				return call_user_method($method,$this);
			} 
			else {
				return false;
			}
		}
	
		private function __set($attribute,$value){
			
			if(isset($this->$attribute)){
				$this->$attribute = $value;
				return true;
			}
			else{
				return false;
			}	
					
		}
	
		
		public function add(){
			// add a landing page to the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime theres is an error.
			// clean data
			$guid = $db->escape($_POST['guid']);
			$content = $db->escape($_POST['content']);
			$donate = ($_POST['donate']) ? 1 : 0 ;
			
			// check for data
			if($guid){ // all data present
				// query
				$query = "INSERT INTO landingpages (guid, style, date_added, donate) VALUES ('$guid', 1, Now(), $donate);";
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully added a new landing page';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed';
				}
			}
			else{ // vital data missing
				$error++;
				$message[] = 'This has not been added because the following information was missing:';
				
				// No guid
				if(!$guid){
					$message[] = 'GUID was missing';
				}

				
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				//redirect('/xxx/create/');
				return $message;
			}
			else{ // no error: all went well
				redirect('?id='.$guid.'&action=edit&'.createFeedbackURL('success',$message));
			}
		
		}
		
		public function addPage(){
			// add a landing page to the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime theres is an error.
			// clean data
			$guid = $db->escape($_POST['guid']);
			$page_guid = $db->escape($_POST['page_guid']);
			$content = $db->escape($_POST['content']);
			
			// check for data
			if($guid && $page_guid){ // all data present
				// query
				$query = "INSERT INTO landingpages_pages (guid, page_guid, sort_order, style, date_added, content) VALUES ('$guid', '$page_guid', 9, 1, Now(), '$content');";
				//niceError($query);
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully added a new landing page section';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed';
				}
			}
			else{ // vital data missing
				$error++;
				$message[] = 'This has not been added because the following information was missing:';
				
				// No guid
				if(!$guid){
					$message[] = 'GUID was missing';
				}
				
				// No guid
				if(!$page_guid){
					$message[] = 'Page GUID was missing';
				}
				
				// no style
				if(!$style){
					$message[] = 'A style wasn\'t selected';
				}

				
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				//redirect('/xxx/create/');
				return $message;
			}
			else{ // no error: all went well
				redirect('?page_id='.$page_guid.'&action=edit&'.createFeedbackURL('success',$message));
			}
		
		}
		
		public function delete($id){
			// remove a landing page from the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			
			
			// query
			$query = "DELETE FROM landingpages WHERE guid = '$id'";

			// run query
			if($results = $db->query($query)){ // Success
				$feedbackType = 'success';
				$message = 'You have successfully deleted that landing page';
				
				// NOW delete the children
				$query = "DELETE FROM landingpages_pages WHERE guid = '$id'";
				$db->query($query);
			}
			else{ // unsuccessful
				$error++;
				$feedbackType = 'error';
				$message = 'This landing page hasn\'t been deleted. Please try again later.';
			}
			
			redirect('?'.createFeedbackURL($feedbackType,$message));
		}
		
		public function deletePage($id){
			// remove a landing page from the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			
			
			// query
			$query = "DELETE FROM landingpages_pages WHERE page_guid = '$id'";
			
			// run query
			if($results = $db->query($query)){ // Success
				$feedbackType = 'success';
				$message = 'You have successfully deleted that landing page section';
			}
			else{ // unsuccessful
				$error++;
				$feedbackType = 'error';
				$message = 'This landing page section hasn\'t been deleted. Please try again later.';
			}
			
			redirect('?'.createFeedbackURL($feedbackType,$message));
		}
		
		
		public function edit($id){
			// edit a landing page already in the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			// clean data
			$style = $db->escape($_POST['style']);
			$content = $db->escape($_POST['content']);
			$banner_image = $db->escape($_POST['banner_image']);
			$donate = $db->escape($_POST['donate']);
			
			// check for data
			if($style){ // all data present
				// query
				$query = "UPDATE landingpages SET style = '$style', date_edited = Now(), content='$content', banner_image='$banner_image', donate='$donate' WHERE guid = '$id';";
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully updated this landing page';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed update this landing page';
				}
			}
			else{ // vital data missing
				$error++;
				$message[] = 'This landing page hasn\'t been updated because the following information was missing:';
				
				// No guid
				if(!$id){
					$message[] = 'ID was missing';
				}
				
				// no style
				if(!$style){
					$message[] = 'A style was selected';
				}
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				return $message;
			}
			else{ // no error: all went well
				redirect('?id='.$id.'&'.createFeedbackURL('success',$message));
			}
			
		}
		
		public function editPage($id){
			// edit a landing page already in the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			// clean data
			$style = $db->escape($_POST['style']);
			$content = $db->escape($_POST['content']);
			$sort_order = $db->escape($_POST['sort_order']);
			
			// check for data
			if($style && $sort_order){ // all data present
				// query
				$query = "UPDATE landingpages_pages SET style = '$style', date_edited = Now(), content = '$content', sort_order = '$sort_order' WHERE page_guid = '$id';";
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully updated this panel';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed update this panel';
				}
			}
			else{ // vital data missing
				$error++;
				$message[] = 'This panel hasn\'t been updated because the following information was missing:';
				
				// no style
				if(!$style){
					$message[] = 'A style wasn\'t selected';
				}
				
				// no sort order
				if(!$sort_order){
					$message[] = 'A sort order wasn\'t selected';
				}
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				return $message;
			}
			else{ // no error: all went well
				redirect('?page_id='.$id.'&'.createFeedbackURL('success',$message));
			}
			
		}
		
		public function updateSortOrders(){
			global $db;
			
			foreach($_POST['sort_order'] as $post => $value){
				$post = str_replace("\'",'',$post);
				$query = "UPDATE landingpages_pages SET sort_order='$value' WHERE page_guid = $post";		
				$db->query($query);
			}
			unset($_POST);
			redirect($_SERVER['REQUEST_URI']);
		}
		
		
		public function getAll($orderBy, $search, $currentPage, $perPage){
			// return all the details of every user
			global $db, $siteHomeGUID;
			
			// QUERY ORDERING
			switch($orderBy){
				case 'newest';
				case 'latest';
				case 'date';
				default:
					$orderBy = 'l.date_added DESC'; // order by newest
				break;
			}
			
			// SEARCH FILTER
			$searchFilter = ($search) ? " AND p.title LIKE '%$search%' OR l.content LIKE '%$search%'" : '';
			
			// QUERY LIMITATIONS
			$limit = getQueryLimits($perPage,$currentPage); // Query limitations (Don't overload the Database)
			
			//$query = "SELECT l.guid, l.style, l.date_added, l.date_edited, l.revision_id, p.title as title FROM landingpages l LEFT JOIN pages p ON p.guid = l.guid WHERE 1 $searchFilter ORDER BY $orderBy LIMIT $limit";

			$query = "SELECT p.title, p.guid, l.guid as landingpage, p2.guid as children FROM pages p LEFT JOIN landingpages l ON p.guid = l.guid LEFT JOIN pages p2 ON p2.parent = p.guid WHERE p.parent = '{$this->siteHomeGUID}' AND p.template = 11  $searchFilter GROUP BY p.guid ORDER BY $orderBy LIMIT $limit";
			//niceError($query);
			if( $this->properties = $db->get_results( $query, "ARRAY_A" ) ){
				return true;
			}
			else{
				return false;
			}	
		}
		
		public function getById($id = false){
			// return a specific landing page info from a supplied id
			global $db;
			
			if($id){
			
				$query = "SELECT l.guid, l.content, l.style, l.date_added, l.date_edited, l.revision_id, l.banner_image, l.donate, p.title as title, p.name as name FROM landingpages l LEFT JOIN pages p ON p.guid = l.guid WHERE p.guid = '$id'";
				//niceError($query);
				$results = $db->get_row($query);
				if( $this->properties = $db->get_row( $query, "ARRAY_A" ) ){
					$this->id = $id;
					return true;
				}
				else{
					return false;
				}
			}
			else{
				return false;
			}

		}
		
		public function getPageById($id = false){
			// return a specific landing page info from a supplied id
			global $db;
			
			if($id){
			
				$query = "SELECT l.guid, l.page_guid, l.style, l.content, l.sort_order, p.title as title FROM landingpages_pages l LEFT JOIN pages p ON p.guid = l.page_guid WHERE l.page_guid = '$id'";
				//niceError($query);
				$results = $db->get_row($query);
				if( $this->properties = $db->get_row( $query, "ARRAY_A" ) ){
					$this->id = $id;
					return true;
				}
				else{
					return false;
				}
			}
			else{
				return false;
			}

		}
		
		public function getPagesByParent($id = false){
			
			
			global $db;
			
			if($id){
			
				/*$query = "SELECT l.page_guid, l.sort_order, l.img_guid, l.content, l.style, 
				ifnull(l.sort_order,200) as x_sort_order, p.title as title, p.guid as original_guid , 
				p.meta_description as meta_description 
				FROM pages p 
				LEFT JOIN landingpages_pages l ON l.page_guid = p.guid 
				WHERE p.parent = '$id' 
				ORDER BY ". $this->orderBy ." ASC";
				*/

				$query = "SELECT p.guid as page_guid, l.sort_order, p.sort_order p_sort_order, l.img_guid, l.content, l.style, 
							ifnull(l.sort_order,200) as x_sort_order, p.title as title, p.guid as original_guid , 
							p.meta_description as meta_description 
							FROM pages p 
							LEFT JOIN landingpages_pages l ON l.page_guid = p.guid 
							WHERE p.parent = '$id' AND p.hidden != 1 
							ORDER BY x_sort_order ASC";
				//niceError($query);
				$results = $db->get_results($query);
				
				if( $this->pages = $db->get_results( $query, "ARRAY_A" ) ){
					return true;
				}
				else{
					return false;
				}
			}
			else{
				return false;
			}
		}
		
		public function getTotal($search = NULL){
			// get all landing page in the system: used for pagination and display
			global $db, $siteHomeGUID;
			
			// SEARCH FILTER
			$searchFilter = ($search) ? " AND p.title LIKE '%$search%' OR l.content LIKE '%$search%'" : '';
			
			$query = "SELECT l.guid FROM pages p LEFT JOIN landingpages l ON p.guid = l.guid LEFT JOIN pages p2 ON p2.parent = p.guid WHERE p.parent = '{$this->siteHomeGUID}' AND p.template = 11  $searchFilter GROUP BY l.guid";

			if($this->total = sizeof($db->get_results($query))){
				return true;
			}
			else{
				return false;
			}
			
		}
		
	
	}

?>