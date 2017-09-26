<?

class Poll {

	public $guid;
	public $structure=array();
	
	public function __construct( $guid=false ){

		if( $guid ){
			$this->loadByGUID( $guid );
		}
		
	}
	
	public function __tostring(){
		return '<pre>'. print_r($this->structure,true) .'</pre>'; 
	}
	

	public function loadByGUID($guid=false){
		global $db;
		$tmp = array();
		
		if( $guid ){

			$tmp['guid'] = $guid;
			$tmp['title'] = $db->get_var("SELECT title FROM pages WHERE guid='$guid'");
			
			$query = "SELECT * FROM content WHERE parent='$guid' AND revision_id=0 ORDER BY placeholder";
			//print "$query<br>\n";
			
			if( $results = $db->get_results($query) ){
			
				// get the question and response from the content table
				foreach ($results as $result) {
					if ($result->placeholder=="question") {
						$tmp['question'] = $result->content;
						$tmp['date_set'] = $result->revision_date;
					}
					if ($result->placeholder=="response") $tmp['response'] = $result->content;
				}
				
				// get the answers and store them in an array called answers (funnily enough), use sort_order as the key
				// 'default' is the 'corrrect answer' if there is one... We can use this to style it later...
				$query = "SELECT *, ROUND((votes/(SELECT SUM(votes) FROM poll_answers WHERE guid='$guid'))*100) percentage
							FROM poll_answers WHERE guid='$guid' ORDER BY sort_order ASC";
				//print "$query<br>\n";
				if( $answers = $db->get_results($query) ){

					foreach($answers as $answer){
						$tmp['answers'][$answer->sort_order]['text'] = $answer->answer_text;
						$tmp['answers'][$answer->sort_order]['votes'] = $answer->votes;
						$tmp['answers'][$answer->sort_order]['default'] = $answer->default;
						if( $answer->default==1 ){
							$tmp['default'] = $answer->sort_order; // alias for quick reference...
						}
						$tmp['answers'][$answer->sort_order]['percentage'] = ($answer->percentage>0 ? $answer->percentage :0);
						$tmp['total_votes'] += $answer->votes;
					}
					
				}
				// format the structure with amswers, etc.
				$this->structure = $tmp;
				return true;
			}
		}
		return false;
	}
	
	
	// 12/12/2008 Comment
	// Create a new poll panel record
	public function create($title=false, $question=false, $response=false,$answers=false,$default=false){
		global $db, $site, $user;
		
		//print "create($title, $question, $response, ".print_r($answers,true).", $default)<br>\n";
		if( $title && $question && $response && is_array($answers) ){
			$pageGUID = uniqid();
			$questionGUID = uniqid();
			$responseGUID = uniqid();
			// Treat the name the same way we do in Page.class
			$name = preg_replace("/[^A-Za-z0-9 ]/", "", $title);
			$name = str_replace(" ",'-',$name);
			$title = $db->escape($title);
			$description = 'Poll panel';
			$question = $db->escape($question);
			$response = $db->escape($response);
			
			$query = "INSERT INTO pages 
				(guid, parent, sort_order, name, title, meta_description, hidden, locked, 
				 style, template, date_created, user_created, date_modified, user_modified, 
				 date_published, user_published, msv) 
				VALUES 
				('$pageGUID', '".$site->id."', 0, '{$name}', '{$title}', '$description', 1, 1, 
				 1, '17', NOW(), {$user->getID()}, NOW(), {$user->getID()}, NOW(), {$user->getID()}, 
				 ".$site->id.")";
			//print "$query<br>\n";	
			if( $db->query($query) ){
			
				// INSERT into the content table...
				$contentTypes = array('question','response');
				foreach( $contentTypes as $item ){
					$query = "INSERT INTO content 
						(guid, parent, content, revision_id, revision_date, placeholder)
						VALUES 
						('".${$item.'GUID'}."','$pageGUID','". ${$item} ."',0,NOW(),'". $item ."')";
					//print "$query<br>\n";
					$db->query($query);
				}

				foreach( $answers as $key => $value ){
					$text = $db->escape($value);
					$setDefault = ($default==$key) ? 1 : 0 ;
					$query = "INSERT INTO poll_answers 
						(guid, sort_order, answer_text, votes, `default`) 
						VALUES ('$pageGUID', ". $key .", '". $text ."', 0, ". $setDefault .")";
					//print "$query<br>\n";
					$db->query($query);
				}
				return true;
			}
			//else print "Failed to create page record<br>\n";
			
		}
		//else print "Not enough data passed to func<br>\n";
		return false;
	}
	



	// add a new poll thorugh Treeline...
	public function update($guid=false, $title=false, $question=false, $response=false,$answers=false,$default=false){
		global $db, $siteID, $user;
		
		if( $guid ){

			if( $title>'' ){
				// Treat the name the same way we do in Page.class
				$name = preg_replace("/[^A-Za-z0-9 ]/", "", $title);
				$name = str_replace(" ",'-',$name);
				$title = $db->escape($title);
				$description = 'Poll panel';			
				
				$query = "UPDATE pages SET title='$title', name='$name' WHERE guid='$guid'";
				//print "$query<br>\n";
				$db->query($query);
			}
			
			$question = $db->escape($question);
			$response = $db->escape($response);
			// INSERT into the content table...
			$contentTypes = array('question','response');
			foreach( $contentTypes as $item ){
				if( ${$item} > '' ){
					$query = "UPDATE content SET content='". ${$item} ."'
								WHERE parent='$guid' AND placeholder='". $item ."'";
					//print "$query<br>\n";
					$db->query($query);
				}
			}

			// Save number of votes for each answer
			$query = "SELECT votes FROM poll_answers WHERE guid='$guid'";
			//print "$query<br>\n";
			$votes = $db->get_results($query);
			
			// unless we want to reset results
			if ($_POST['poll_reset']==1) {
				$votes=array(0,0,0,0,0);
			}
			
			// Going to have to delete all the answers and recreate them all :o(#
			$query = "DELETE FROM poll_answers WHERE guid='$guid'";
			//print "$query<br>\n";
			$db->query($query);
			$i=1;
			foreach( $answers as $key => $value ){
				$text = $db->escape($value);
				$setDefault = ($default==$key) ? 1 : 0 ;
				$query = "INSERT INTO poll_answers 
					(guid, sort_order, answer_text, votes, `default`) 
					VALUES ('$guid', $key, '$text', ".($votes[($key-1)]->votes+0).", $setDefault)";
				//print "$query<br>\n";
				$db->query($query);
			}
			
			/*
			The answers section may need more work because what happens if an answer is removed??
			The replace into might get rid of vote counts - but update wouldn't set any news answers added...
			*/
			
			
			return true;
			
			
		}else{
			return false;
		}
		
	}
	
	
	public function delete($guid=false){
		global $db;
		
		if( $guid ){	
			// remove page, content rows and answers...
			$query = "DELETE FROM pages WHERE guid='$guid'";
			$db->query($query);
			
			$query = "DELETE FROM content WHERE parent='$guid'";
			$db->query($query);
			
			$query = "DELETE FROM poll_answers WHERE guid='$guid'";
			$db->query($query);
			
			return true;
			
		}else{
			return false;
		}
	}



	
	public function addVote($guid=false, $answer=false, $votes=false){
		global $db;
		//echo 'guid: '. $guid .', answer: '. $answer .', votes: '. $votes .'<br />';
		if( $guid && $answer && $votes ){
			$query = "UPDATE poll_answers SET votes=$votes WHERE guid='$guid' AND sort_order=$answer";
			//print "$query<br>\n";
			$db->query($query);

			if( $db->affected_rows>=0 ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	
	public function getSitePolls($siteID=1){
		global $db;
		$polls = array();
		// returns a list of guids
		$query = "SELECT guid FROM pages WHERE template=17 AND msv=$siteID ORDER BY date_created DESC, title ASC";
		if( $list = $db->get_results($query) ){
			foreach( $list as $item ){
				$tmp = new Poll($item->guid);
				$polls[] = $tmp->structure;
				//echo 'guid:'. $item->guid.'<br />';
			}
			return $polls;
		}else{
			return false;
		}
		
	}
	
	


}



?>