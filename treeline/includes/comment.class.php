<?
class Comment {

	public $id;
	public $name;
	public $comment;
	public $count;
		
	public $msg=array();
	
	// This is loaded when the class is created	
	public function __construct($guid) {
		global $db;
		$this->guid=$guid;
		$this->setCount();
	}
		
	public function add($name, $email, $comment, $country) {
		global $db;
		if (!$comment) $this->msg[]="No comment was entered";
		if (!$name) $this->msg[]="No name was entere";
		if (!$this->guid) $this->msg[]="No page id associated with this comment";
		if (!$message) {
			$query="INSERT INTO pages_comments (guid, name, email, `comment`, country, date_created) 
				VALUES('".$this->guid."', '".$db->escape($name)."', '".$db->escape($email)."', 
				'".$db->escape(nl2br(strip_tags($comment)))."', ".($country+0).", NOW())";
			//print "$query<br>\n";
			if ($db->query($query)) {
				$this->id=$db->insert_id;
				return true;
			}
			else $this->msg[]="Failed to add this comment";
		}
		return false;
	}
	
	// Display comments for this page
	public function draw($comment_id=0, $status='A') {
		global $db, $page;
		//print "C::d($comment_id, $status)<br>\n";
		if (!$this->guid) return;
		// If we have passed a specific comment ID than show it 
		// even if its not active.
		if ($comment_id>0) $status='';	

		$query="SELECT pc.*, 
			DATE_FORMAT(date_created, '%D %M %Y') AS date,
			DATE_FORMAT(date_created, '%d') AS day,
			DATE_FORMAT(date_created, '%b %Y') AS myear,
			sc.title AS country
			FROM pages_comments pc 
			LEFT JOIN store_countries sc ON sc.country_id = pc.country
			WHERE guid='".$this->guid."' ";
		if ($comment_id) $query.="AND id=$comment_id ";
		if ($status) $query.="AND status='$status' ";
		$query.="ORDER BY date_created DESC";
		//print "$query<br>\n";
		if ($results=$db->get_results($query)) {
			$count = 0;
			foreach($results as $result) {
				$html.='
				<div class="media">
					<div class="media-left">'.$result->day.'<span>'.$result->myear.'</span></div>
					<div class="media-body">
					'.$result->comment.'
					<h4 class="media-heading">'.$result->name.', '.$result->country.'</h4>
					</div>
				</div>
				';
				$count++;
			}
			$html='
			<div class="media-object-default">
				'.$html.'
			</div>
			';
		}
		$this->count = $count;
		return $html;
	}
	public function setCount($guid='') {
		global $db;
		$query = "select count(*) from pages_comments where guid='".($guid?$guid:$this->guid)."' AND status='A'";
		//print "get comments $query<br>\n";
		$this->count=$db->get_var($query);
	}
	
	public function getCount() {
		return $this->count;
	}
}


?>