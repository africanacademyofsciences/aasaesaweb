<?php

	/*
		===============
		Plugin Class Template
		---------------
		Purpose: Provide a basic template for objects. For the most part
		copy and pastignthis into a new file and search replacing XXX with
		your class name should speed up object writing no end.
		
		Disclaimer: I am not an OO-Rockstar. This class may not be perfectly written.
		
		===============
		
		written by: Phil Thompson
		when: 25/05/2007
		
		edited by: Phil Thompson
		when: 14/07/2007
	
		Table of Contents
		-----------------
		
		add()
		delete()
		edit()
		
		getAll()
		getById()
		
	
	*/




	class Plugin{
	
		
		public function add(){
			// add a XXX to the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime theres is an error.
			// clean data
			$value = $db->escape($_POST['value']);
			
			// check for data
			if($value){ // all data present
				// query
				$query = "INSERT INTO XXX () VALUES ();";
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully added';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed';
				}
			}
			else{ // vital data missing
				$error++;
				$message = 'Vital information was missing';
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				redirect('/XXX/create/');
			}
			else{ // no error: all went well
				redirect('/XXX/'.mysql_insert_id().'/?'.createFeedbackURL('success',$message));
			}
		
		}
		
		public function delete($id){
			// remove a XXX from the system
			// IMPORTANT: XXX aren't actually deleted as this would create redundant data
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			
			
			// query
			$query = "DELETE FROM XXX WHERE id = $id";

			// run query
			if($results = $db->query($query)){ // Success
				$feedbackType = 'success';
				$message = 'You have successfully deleted';
			}
			else{ // unsuccessful
				$error++;
				$feedbackType = 'error';
				$message = 'The XXX hasn\'t been deleted. Please try again later.';
			}
			
			redirect('/XXX/?'.createFeedbackURL($feedbackType,$message));
		}
		
		
		public function edit($id){
			// edit a XXX already in the system
			global $db;
			
			$error = 0; // set error to 0. Increment everytime there is an error.
			// clean data
			$value = $db->escape($_POST['value']);
			
			// check for data
			if($value){ // all data present
				// query
				$query = "UPDATE XXX SET value = '$value' WHERE id = $id;";
				// run query
				if($results = $db->query($query)){ /// success
					$message = 'You have successfully updated';
				}
				else{ // failure
					$error++;
					$message = 'Due to a technical error, you have failed update';
				}
			}
			else{ // vital data missing
				$error++;
				$message = 'This XXX hasn\'t been updated because vital information was missing';
			}
			
			// redirect user & give feedback
			if($error > 0){ // there's been an error
				redirect('/XXX/edit/'.$id.'/?'.createFeedbackURL('error',$message));
			}
			else{ // no error: all went well
				redirect('/XXX/'.$id.'/?'.createFeedbackURL('success',$message));
			}
			
		}
		
		
		public function getAll($orderBy, $currentPage = 1, $perPage = 20){
			// return all the details of every user
			global $db;
			
			
			// QUERY ORDERING
			switch($orderBy){
				case 'newest';
				case 'latest';
				case 'date';
				default:
					$orderBy = 'date_added DESC'; // order by newest users
				break;
			}
			
			// QUERY LIMITATIONS
			$limit = getQueryLimits($perPage,$currentPage); // Query limitations (Don't overload the Database)
			
			$query = "SELECT * FROM XXX WHERE status = 0 ORDER BY $orderBy LIMIT $limit";

			$results = $db->get_results($query);
			
			return $results;
		}
		
		public function getById($id){
			// return a specific XXX info from a supplied id
			global $db;
			
			$query = "SELECT * FROM XXX WHERE plugin_id = '$id'";
			
			$results = $db->get_row($query);
			
			return $results;
		}
		
		public function getTotal(){
			// get all XXX in the system: used for pagination and display
			global $db;
			
			$query = "SELECT COUNT(id) FROM plugin_id";

			$total = $db->get_var($query);
			
			return $total;
		}
		
		public function drawAll($orderBy, $currentPage = 1, $perPage = 20){
			//
			$results = $this->getAll($orderBy, $currentPage, $perPage);
			$total = $this->getTotal();
			if($results){// has results
				$html = ''."\n";
				foreach($results as $result){ // loop through data
					$html .= $result->id."\n";
				}
				$html .= ''."\n";
				$html .= drawPagination('/XXX/', $total, $perPage, $currentPage)."\n";
			}
			else{ // no results/data
				$html = '<p>no results</p>'."\n";
			}
			
		}
		
		public function drawAllAdminView($orderBy, $currentPage = 1, $perPage = 20){
			//
			$results = $this->getAll($orderBy, $currentPage, $perPage);
			$total = $this->getTotal();
			if($results){// has results
				$html = '<table>'."\n";
				$html .= '<caption></caption>'."\n";
				$html .= '<thead>'."\n";
				$html .= '<tr>'."\n";
				$html .= '<th scope="col"></th>'."\n";
				$html .= '</tr>'."\n";
				$html .= '</thead>'."\n";
				$html .= '<tbody>'."\n";
				foreach($results as $result){ // loop through data
					$html .= '<tr>'."\n";
					$html .= '<td>'.$result->id.'</td>'."\n";
					$html .= '</tr>'."\n";
				}
				$html .= '</tbody>'."\n";
				$html .= '</table>'."\n";
				$html .= drawPagination('/XXX/', $total, $perPage, $currentPage)."\n";
			}
			else{ // no results/data
				$html = '<p>no results</p>'."\n";
			}
			
		}
		
		public function drawById($id){
			// draw HTML for specific XXX info from a supplied id
			// get results
			$results = $this->getById($id);
			
			if($results){ // XXX exists
				$html = '<h3>XXX</h3>'."\n";
				
			}
			else{// no XXX exists
				$html = '<p>That XXX doesn\'t appear to exist</p>'."\n";
			}
			
			return $html;
		}
		
		public function drawByIdAdminView($id){
			// draw HTML for specific XXX info from a supplied id
			// get results
			$results = $this->getById($id);
			
			if($results){ // XXX exists
				$html = '<h3>XXX</h3>'."\n";
				
			}
			else{// no XXX exists
				$html = '<p>That XXX doesn\'t appear to exist</p>'."\n";
			}
			
			return $html;
		}


	
	}

?>