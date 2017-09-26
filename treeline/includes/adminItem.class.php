<?

	/*
	=================
	Admin Items Class
	-----------------
	Allow clients to view/add items including feedback, bugs and functionality request.
	They can also view and rate Ichameleon's responses.
	=================
	
	
	
	*/

class adminItem{

	private $title;
	public $errmsg=array();
			
	public function __construct($title='') {
		// This is loaded when the class is created	
		if ($title) $this->title = $title;
	}
		
	// Send feedback form data into centrlaised database
	public function createItem($type, $client_id){
		global $db_admin, $page;
		
		$type_id = $this->getItemIdFromTitle($type);
		if (!$this->title) $this->title = $type;
		
		// clean up variables
		$action = $_POST['action'];
		$title = $db_admin->escape($_POST['title']);
		$description = $db_admin->escape($_POST['description']);
		
		// check for variables
		if($action == 'create' && $client_id>0 && $title && $description){ // all required variables are present
			$query = "INSERT INTO items 
				(item_type, client_id, title, description, date_added) 
				VALUES
				($type_id, $client_id, '$title', '$description', Now())
				";
			//niceError($query);
			$db_admin->query($query);
			if (!$db_admin->last_error) return true;
			else {
				$this->errmsg[] = $page->drawLabel("tl_adit_c".substr($this->title, 0, 4)."_errtech", 'Your '.$this->title.' was not sent due to a technical error. Please try again in a few minutes');
			}
		}
		// varibles missing
		else $this->errmsg[] = $page->drawLabel("tl_adit_c".substr($this->title, 0, 4)."_errinfo", 'Your '.$this->title.' was not sent because it was missing some vital information');
		return false;		
	}
		
	public function drawItems($client_id, $type, $orderBy = 'newest', $currentPage = 1, $perPage = 10){
		// put feedback into a nice table for viewing
		//print "dI($client_id, $type, $orderBy, $currentPage, $perPage)<br>\n";
		$results = $this->getItems($client_id, $type, $orderBy, $currentPage, $perPage);
	
		if($results){
			$html = '<table class="treeline">'."\n";
			$html .= '<caption>All previous '.$type.'</caption>'."\n";
			$html .= '<thead>'."\n";
			$html .= '<tr>'."\n";
			$html .= '<th scope="col">Review</th>'."\n";
			$html .= '<th scope="col">Code</th>'."\n";
			$html .= '<th scope="col"><a href="?order=title">Title</a></th>'."\n";
			$html .= '<th scope="col"><a href="?order=newest">Created on</a></th>'."\n";
			$html .= '<th scope="col"><a href="?order=response">Treeline response</a></th>'."\n";
			$html .= '</tr>'."\n";
			$html .= '</thead>'."\n";
			$html .= '<tbody>'."\n";
			foreach($results as $result){

				if(!$result->response_id) $response = '<abbr title="Not applicable">N/A</abbr>';
				else if($result->response_id) $response = getUFDateTime($result->response_date);

				$type_id = $result->response_id>0?"response_id=".$result->response_id:$type."_id=".$result->item_id;
				$html .= '
<tr>
	<td class="action preview"><a href="/treeline/'.$type.'/?'.$type_id.'" title="Preview this '.$type.'">Preview</a></td>
	<td>'.$this->generateItemCode($type, $result->item_id).'</td>
	<td>'.$result->title.'</td>
	<td>'.getUFDateTime($result->date_added).'</td>
	<td>'.$response.'</td>
</tr>
';
			}
			$html .= '</tbody>'."\n";
			$html .= '</table>'."\n";
			$html .= drawPagination($this->getTotalItems($client_id, $type), $perPage, $currentPage)."\n";
		} 
		else{
			$html = '<p>There is no previous feedback</p>'."\n";
		}
		
		return $html;
	}
		
		public function drawItemById($client_id, $type, $item_id){
			// UI/HTML for an individual item
			
			$item = $this->getItemById($client_id, $type, $item_id); // get results;
			
			if($item){ // item exists
				$html = '<h3>'.$item->title.'</h3>'."\n";
				$html .= '<p>'.getUFDateTime($item->date_added).'</p>'."\n";
				$html .= '<p>'.$item->description.'</p>'."\n";
				$html .= '<p><strong>Code:</strong> '.$this->generateItemCode($type, $item_id).'</p>'."\n";
			} else{ // item doesn't exist
				$html = '<p>That '.$type.' doesn\'t seem to exist</p>'."\n";
			}
			
			return $html;
		}
		
		public function drawRatingForm(){
			//
			$html = '<h4>Help us improve by rating this response out of 5</h4>';
			$html .= '<ul id="ratings">';
			for($i = 1; $i < 6; $i++){
				$html .= '<li><a href="'.$_SERVER['REQUEST_URI'].'&amp;rating='.$i.'" title="Rate this response as '.$i.' out of 5">'.$i.'</a></li>';
			}
			$html .= '</ul>';
		
			return $html;
		}
		
		public function drawResponseById($response_id, $type){
			//
			
			$html = '';
			
			$response = $this->getResponseById($response_id);
			if($response){
				$html .= '<h3><abbr title="Regarding">Re:</abbr> '.$response->title.'</h3>'."\n";
				$html .= '<p>'.$response->content.'</p>'."\n";
				$html .= '<p>Response by <a href="mailto:'.$response->admin_email.'?subject=Re: '.$response->title.'" title="email '.$response->admin_name.' about this response">'.$response->admin_name.'</a> on '.getUFDateTime($response->date_added).'</p>'."\n";
				$html .= '<hr />'."\n";
				if(!$response->rating){
					$html .= $this->drawRatingForm($response_id);
				} else{
					$html .= '<h4>Response rating</h4>'."\n";
					$html .= '<p class="rating'.$response->rating.'">'.$response->rating.' out of 5</p>'."\n";
				}
				$html .= '<hr />'."\n";
				// original message
				$html .= '<p>This response relates to the following item:</p>'."\n";
				$html .= '<h4>'.$response->title.'</h4>'."\n";
				$html .= '<p>'.$response->description.'</p>'."\n";
				$html .= '<p>Added on '.getUFDateTime($response->item_date_added).'</p>'."\n";
			} else{
				$html = '<p>That response item doesn\'t seem to exist</p>'."\n";
			}
			
			return $html;
		}
		
		
		public function getItems($client_id, $type, $orderBy, $currentPage, $perPage){
			global $db_admin;
			
			$type_id = $this->getItemIdFromTitle($type);
	
			// QUERY ORDERING
			switch($orderBy){
				case 'newest';
				case 'latest';
				case 'date';
				default:
					$orderBy = 'date_added DESC'; // order by newest items
				break;
				case 'title':
					$orderBy = 'title ASC'; // order by title A-Z
				break;
				case 'response':
					$orderBy = 'response_date DESC'; // order by title A-Z
				break;
			}

			$limit = getQueryLimits($perPage,$currentPage); // Query limitations (Don't overload the Database)

			$query = "SELECT * FROM view_items WHERE client_id = ".($client_id+0)." AND type_id = $type_id ORDER BY $orderBy LIMIT $limit";
			//print "$query<br>\n";
			$results = $db_admin->get_results($query);
			
			return $results;
		}
		
		public function generateItemCode($type,$item_id){
			// return a unique code for reference
			
			
			// Add preceding zeroes
			if($item_id < 10){ // if the id number is less than 10 add two preceding zeros
				$item_id = '00'.$item_id;
				$item_id = $item_id*360;
				$item_id = $item_id-230;
			}
			else if($item_id > 9 && $item_id < 100){ // if the id number is greater than 9 but less than 100 add one preceding zeros
				$item_id = '0'.$item_id;
				$item_id = $item_id*360;
				$item_id = $item_id-230;
			}
			
			return substr(strtoupper($type),0,1).$item_id;
		}
		
		public function getItemById($client_id, $type, $item_id){
			global $db_admin;
			
			$type_id = $this->getItemIdFromTitle($type);
		
			// QUERY USING VIEW
			$query = "SELECT * FROM view_items WHERE client_id = $client_id AND item_id = $item_id AND type_id = $type_id LIMIT 0, 1";

			// run EZ-SQL query
			$results = $db_admin->get_row($query);
			
			return $results;
		}
		
		public function getItemIdFromTitle($title){
			//convert the title into an id for database insertion
			$type_id = 0;

			switch($title){// convert type into a numeric id: Shoudl this be from the database, so it's dynamic?
				default:
				case 'feedback':
				case 'Feedback':
					$type_id = 1;
				break;
				case 'request':
				case 'requests':
					$type_id = 2;
				break;
				case 'bug':
				case 'Bug':
				case 'bugs':
				case 'Bugs':
				case 'bug report':
				case 'Bug report':
				case 'Bug Report':
					$type_id = 3;
				break;
			}
				return $type_id;
		}
		
		
		public function getTotalItems($client_id, $type){
			global $db_admin;
			
			$type_id = $this->getItemIdFromTitle($type);
			
			// query
			$query = "SELECT COUNT(item_id) FROM items WHERE client_id = $client_id AND item_type = $type_id;";

			// run EZ-SQL query
			$results = $db_admin->get_var($query);
			return $results;
		}
		
		public function rateResponse($response_id, $rating, $type){
			// add a rating to a Ichameleon response
			global $db_admin;
			
			$error = 0; // no error so far. Increment whenever one occurs.
			
			// check for existing rating
			if(!$this->getRating($response_id)){ // rating hasn't previously been set
			
				if($response_id && $rating){
					$query = "INSERT INTO response_ratings (response_id, rating, date_added) VALUES ($response_id, $rating, Now())";
					if($results = $db_admin->query($query)){ // success
						$message = 'Your rating of '.$rating.' has been counted';
					} else{ // database failure
						$error++;
						$message = 'Your rating was not counted due to a technical error';
					}
				} else{ // item id or rating is missing
					$error++;
					$message = 'Your rating was not counted due to missing vital information';
				}
			} else{ // a rating has been set
				$error++;
				$message = 'Your rating was not counted because this response has already been rated';
			}
			if($error > 0){ // errors have occurred
				redirect('/treeline/'.$type.'/?response_id='.$response_id.'&'.createFeedbackURL('error',$message));
			} else{ // no errors aka  success
				redirect('/treeline/'.$type.'/?response_id='.$response_id.'&'.createFeedbackURL('success',$message));
			}
		}
		
		public function getRating($response_id){
			// check a reponse has a rating before adding a new one
			global $db_admin;
		
			$query = "SELECT rating FROM response_ratings WHERE response_id = $response_id LIMIT 0, 1";

			if($results = $db_admin->get_var($query)){
				return $results; // has a rating already
			}	 else{
				return false; // doesn't have a rating
			}
			
		}
		
		public function getResponseById($response_id){
			// get a response
			global $db_admin;
		
			$query = "SELECT * FROM view_response_details WHERE response_id = $response_id LIMIT 0, 1";

			if($results = $db_admin->get_row($query)){
				return $results; // response exists
			}	 else{
				return false; // response does't exist
			}
		}
		
		
		public function getClientId($url){
			// get this client's Id
			global $db_admin;
			// This queryY is woefully inaccurate and needs improving
			$query = "SELECT client_id FROM clients WHERE client_url LIKE '%$url%' LIMIT 0, 1";
			if($id = $db_admin->get_var($query)){
				return $id; // id exists
			}
			return false; // id does't exist
		}
		
	}

?>