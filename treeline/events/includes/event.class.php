<?php
//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

	/*
		===============
		EVENT CLASS
		---------------
		Purpose: Add/Edit/View Events
		
		Disclaimer: I am not an OO-Rockstar. This class may not be perfectly written.
		
		===============
		
		written by: Phil Thompson
		when: 25/05/2007
		
		edited by: Phil Thompson
		when: 21/07/2007
	
		Table of Contents
		-----------------
		
		add()
		delete()
		edit()
		
		approve()
		
		getAll()
		getById()
		
	
	*/




	class Event{
	
		
		public function add($type =  NULL){
			// add a event to the system
			global $db, $siteID;
			
			
			$error = 0; // set error to 0. Increment everytime theres is an error.
			// clean data
			$title = $db->escape($_POST['title']); // title
			$description = $db->escape($_POST['description']); // description
			$member_id = $db->escape($_POST['member_id']); // member_id
			$venue = $db->escape($_POST['venue']); // venue
			$start_date = $_POST['date']['year'].'-'.$_POST['date']['month'].'-'.$_POST['date']['day'].' '.$_POST['date']['hour'].'-'.$_POST['date']['minute'].'00'; // start_date
			$end_date = $_POST['end_date']['year'].'-'.$_POST['end_date']['month'].'-'.$_POST['end_date']['day'].' '.$_POST['end_date']['hour'].'-'.$_POST['end_date']['minute'].'00';  // end_date
			$status = ($type == 'admin') ? 1 : 0; // if a admin's adding... set statsu to 1 aka live otherwise don't set live.
			
			// check for data
			if($title && $description && $venue && $start_date){ // all data present
				// query
				$query = "INSERT INTO events (title, description, member_id, venue, start_date, end_date, date_added, status, site_id) VALUES ('$title', '$description', '$member_id', '$venue', '$start_date', '$end_date', Now(), '$status',$siteID)";
				
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully added a new event';
					$message .= ($type == 'admin') ? '' : 'Your event will appear once an administrator approves it';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed';
				}
			}
			else{ // vital data missing
				$error++;
				$message[] = 'The following vital information was missing:';
				
				if(!$title){ // no title
					$message[] = 'event name';
				}
				if(!$description){ // no description
					$message[] = 'description';
				}
				
				if(!$venue){ // no venue
					$message[] = 'location';
				}
				
				if(!$start_date){ // no start date
					$message[] = 'start date';
				}
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				//redirect('/event/edit/'.$id.'/?'.createFeedbackURL('error',$message));
				return $message;
			}
			else{ // no error: all went well
				$url = ($type == 'admin') ? '/treeline/events/?' : '/events/?';
				redirect($url.'?'.createFeedbackURL('success',$message));
			}
		
		}
		
		public function delete($id){
			// remove a event from the system
			// IMPORTANT: event aren't actually deleted as this would create redundant data
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			
			
			// query
			$query = "DELETE FROM events WHERE event_id = $id";

			// run query
			if($results = $db->query($query)){ // Success
				$feedbackType = 'success';
				$message = 'You have successfully deleted that event';
			}
			else{ // unsuccessful
				$error++;
				$feedbackType = 'error';
				$message = 'That event hasn\'t been deleted. Please try again later.';
			}
			
			redirect('/treeline/events/?'.createFeedbackURL($feedbackType,$message));
		}
		
		
		public function edit($id, $type = NULL){
			// edit a event already in the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			// clean data
			$title = $db->escape($_POST['title']); // title
			$description = $db->escape($_POST['description']); // description
			$member_id = $db->escape($_POST['member_id']); // member_id
			$venue = $db->escape($_POST['venue']); // venue
			$start_date = $_POST['date']['year'].'-'.$_POST['date']['month'].'-'.$_POST['date']['day'].' '.$_POST['date']['hour'].'-'.$_POST['date']['minute'].'00'; // start_date
			$end_date = $_POST['end_date']['year'].'-'.$_POST['end_date']['month'].'-'.$_POST['end_date']['day'].' '.$_POST['end_date']['hour'].'-'.$_POST['end_date']['minute'].'00';  // end_date

			
			// check for data
			if($title && $description && $venue && $start_date){ // all data present
				// query
				$query = "UPDATE events SET title = '$title', description = '$description', venue = '$venue', start_date = '$start_date', end_date = '$end_date', date_edited = Now() WHERE event_id = $id";
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully updated this event';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed update this event';
				}
			}
			else{ // vital data missing
				$error++;
				$message[] = 'The following vital information was missing:';
				
				if(!$title){ // no title
					$message[] = 'event name';
				}
				if(!$description){ // no description
					$message[] = 'description';
				}
				
				if(!$venue){ // no venue
					$message[] = 'location';
				}
				
				if(!$start_date){ // no start date
					$message[] = 'start date';
				}
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				//redirect('/event/edit/'.$id.'/?'.createFeedbackURL('error',$message));
				return $message;
			}
			else{ // no error: all went well
				$url = ($type == 'admin') ? '/treeline/events/?' : '/events/?id='.$id.'&';
				redirect($url.createFeedbackURL('success',$message));
			}
			
		}
		public function approve($id){
			// set an event live
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			
			
			// query
			$query = "UPDATE events SET status = 1 WHERE event_id = $id";

			// run query
			if($results = $db->query($query)){ // Success
				$feedbackType = 'success';
				$message = 'You have successfully approved that event';
			}
			else{ // unsuccessful
				$error++;
				$feedbackType = 'error';
				$message = 'That event hasn\'t been approved. Please try again later.';
			}
			
			redirect('/treeline/events/?'.createFeedbackURL($feedbackType,$message));
		}
		
		
		public function getAll($orderBy, $status = 'approved', $search, $dateFilter = 'future', $currentPage = 1, $perPage = 20){
			// return all the details of every user
			global $db, $siteID;
			
			
			// QUERY ORDERING
			switch($orderBy){
				default:
					$orderBy = 'start_date DESC'; // order by start date
				break;
				case 'newest';
				case 'latest';
				case 'date';
					$orderBy = 'date_added DESC'; // order by newest users
				break;
				case 'chronologically':
					$orderBy = 'start_date ASC'; // closest first
				break;
				case 'non-chronologically':
					$orderBy = 'start_date DESC'; // last first
				break;
			}
			
			// STATUS
			switch($status){
				default:
				case 'all':
					$statusFilter = '';
				break;
				case 'approved':
					$statusFilter = ' AND status = 1';
				break;
				case 'unapproved':
					$statusFilter = ' AND status = 0';
				break;
			}
			
			// DATE FILTER
			$date = date('Y-m-d \0\0:\0\0:\0\0');
			
			switch($dateFilter){
				default:
				case 'future';
					$dateFilter = " AND start_date >= '$date'";
				break;
				case 'past':
					$dateFilter = " AND start_date <= '$date'";
				break;
				case 'all':
					$dateFilter = '';
				break;
			}
			
			// QUERY FILTERING
			if($search){
				$searchFilter = " AND (title LIKE '%$search%'  OR description LIKE '%$search%' OR venue LIKE '%$search%')";
			}
			
			// QUERY LIMITATIONS
			$limit = getQueryLimits($perPage,$currentPage); // Query limitations (Don't overload the Database)
			
			$query = "SELECT * FROM events WHERE 1 $searchFilter $statusFilter $dateFilter AND site_id=". $siteID ." ORDER BY $orderBy LIMIT $limit";
			//niceError($query); /* debug */

			$results = $db->get_results($query);
			
			return $results;
		}
		
		public function getById($id){
			// return a specific event info from a supplied id
			global $db, $siteID;
			//$siteID = (!isset($siteID)) ? 1 : $siteID;
			
			$query = "SELECT * FROM events WHERE event_id = '$id' AND site_id=". $siteID;
			//niceError($query); /* debug */
			
			$results = $db->get_row($query);
			
			return $results;
		}
		
		public function getTotal($status = 'approved', $search, $dateFilter = 'future'){
			// get all event in the system: used for pagination and display
			global $db, $siteID;
			
			// STATUS
			switch($status){
				default:
				case 'all':
					$statusFilter = '';
				break;
				case 'approved':
					$statusFilter = ' AND status = 1';
				break;
				case 'unapproved':
					$statusFilter = ' AND status = 0';
				break;
			}
			
			// DATE FILTER
			$date = date('Y-m-d \0\0:\0\0:\0\0');
			
			switch($dateFilter){
				default:
				case 'future';
					$dateFilter = " AND start_date >= '$date'";
				break;
				case 'past':
					$dateFilter = " AND start_date <= '$date'";
				break;
				case 'all':
					$dateFilter = '';
				break;
			}
			
			// QUERY FILTERING
			if($search){
				$searchFilter = " AND (title LIKE '%$search%'  OR description LIKE '%$search%' OR venue LIKE '%$search%')";
			}
			
			$query = "SELECT COUNT(event_id) FROM events WHERE 1 $searchFilter $statusFilter $dateFilter AND site_id=". $siteID;
			//niceError($query); /* debug */
			$total = $db->get_var($query);
			
			return $total;
		}
		
		
	}

?>