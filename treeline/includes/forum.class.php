<?php

class Forum {

	public $totalPerPage = 20; // no. of results to be shown on a page of results
	public $totalPerPageAdmin = 10; // no. of results to be shown on treeline admin page.
	
	public $err_msg = array();
		
	public function __contruct() {

		// This is loaded when the class is created...
	}
		
		
	// --------------------------------------------------
	// 31st Jan 2009 - Phil Redclift
	// Create a new category or return set an error
	public function createCategory() {
		global $db, $site, $page;
		
		$cat_title = $db->escape($_POST['cat_title']);
		$cat_desc = $db->escape($_POST['cat_desc']);
		$suspended = $_POST["suspended"]==1?'-1':'0';
		$restrict = $_POST['member_type'];
		$userid = 0;
		
		if ($db->get_var("SELECT post_id FROM forum_posts WHERE title='$cat_title' AND parent_id=0")) {
			$this->err_msg[] = $page->drawLabel("tl_foro_err_catexist", "A category by that name already exists in this forum");
		}
		else if($cat_title && $cat_desc) {
			
			$query = "INSERT INTO forum_posts 
			(title, message, date_created, suspended, user_created, member_type, msv) 
			VALUES (
			'". $cat_title."', '".$cat_desc."', NOW(), 
			".$suspended.", ".$userid .", ".($restrict+0).", ".$site->id.") ";
			//print "$query<br>\n";
			if(!$db->query($query)) {
				$this->err_msg[] = 'Whilst adding the category, an error occured. Please try again. ';
			}
		} 
		else {
			$this->err_msg[] = $page->drawLabel("tl_foro_err_notadd", 'This category has not been added for the following reasons');
			if (!$cat_title) $this->err_msg[] = $page->drawLabel("tl_foro_err_notitle", 'No category title was entered');
			if (!$cat_desc) $this->err_msg[] = $page->drawLabel("tl_foro_err_nodesc", 'No category description was entered');
		}
		
		return !count($this->err_msg);
	}

	// *****************************************************
	public function editCategory($cat_id) {
		global $db, $site, $page;
	
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$cat_title = $db->escape($_POST['cat_title']); // Does this need entities?
			$cat_desc = $db->escape($_POST['cat_desc']);
			$suspended = $_POST["suspended"]==1 ? '-1' : '0';
			$restrict  = $_POST['member_type'];
						
			if ($cat_id && $cat_title){
				$query = "SELECT post_id FROM forum_posts WHERE title='$cat_title' AND parent_id=0 AND msv = ".$site->id." AND post_id<>".$cat_id;
				//print "$query<br>\n";
				if ($db->get_var($query)) {
					$this->err_msg[] = $page->drawLabel("tl_foro_edit_catexist", "A category by that name already exists in this forum");
				}
				else {
					$query = "UPDATE forum_posts 
						SET title='$cat_title', message='$cat_desc', 
						suspended='$suspended', member_type=".($restrict+0)." 
						WHERE post_id=$cat_id
						";
					///print "$query<br>\n";
					$db->query($query);
					if($db->last_error){
						//print "e(".$db->last_error.")<br>\n";
						$this->err_msg[] = $page->drawLabel("tl_foro_edit_err1", 'An error occured while trying to save this category details. Please try again');
					}
				}
			}
			else{
				$this->err_msg[] = $page->drawLabel("tl_foro_err_cattitle", 'You have not updated this category, it needs a title');
			}
		}
		return !count($this->err_msg);
	}


	// 31st Jan 2009 - Phil Redclift
	// Create a new topic or return an error string.
	public function createTopic($cat_id) {
		global $db, $site, $page;
		
		if (!$cat_id) return "No category specified for new topic";
		$topic_title = $db->escape($_POST['topic_title']);
		$topic_desc = $db->escape($_POST['topic_desc']);
		$suspended = $_POST["suspended"]==1?'-1':'0';
		$userid = 0;
		
		if ($db->get_var("SELECT post_id FROM forum_posts WHERE title='$topic_title' AND parent_id=".($cat_id+0))) {
			$this->err_msg[] = $page->drawLabel("tl_foro_addt_texist", "A topic by this name already exists in that category");
		}
		else if($topic_title && $topic_desc) {
			
			$query = "INSERT INTO forum_posts 
			(parent_id, title, message, date_created, suspended, user_created, msv) 
			VALUES (
			".($cat_id+0).", '". $topic_title."', '".$topic_desc."', NOW(), 
			".$suspended.", ".$userid .", ".$site->id.") ";
			if($db->query($query)) return true;
			else $this->err_msg[] = 'Whilst adding the topic, an error occured. Please try again. ';
		} 
		else {
			$this->err_msg[] = $page->drawLabel("tl_foro_top_notadded", 'This topic has not been added for the following reasons');
			if (!$topic_title) $this->err_msg[] = $page->drawLabel("tl_foro_top_notitle", 'No topic title was entered');
			if (!$topic_desc) $this->err_msg[] = $page->drawLabel("tl_foro_top_nodesc", 'No topic description was entered');
		}
		return false;
	}

	public function editTopic($topic_id, $cat_id) {
		global $db;
	
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$topic_title = $db->escape($_POST['topic_title']);
			$topic_msg = $db->escape($_POST['topic_desc']);
			$suspended = $_POST["suspended"]==1 ? '-1' : '0';
						
			if ($topic_id){
				$query = "UPDATE forum_posts SET title='$topic_title', message='$topic_msg', suspended='$suspended' WHERE post_id= '$topic_id'";
				if($db->query($query)) return 1;
				else $message = 'Whilst editing the topic, an error occured or no details were changed. Please try again. ';
			}
			else $message = 'You have not updated this topic, it needs a topic id.';
		}
		return $message;
	}
		
		
	
/*		
		public function drawCategoryList($guid, $currentPage = false){
			global $totalPerPage;
			
			$currentPage = ($currentPage) ? $currentPage : 1;
			// if (!$currentPage) $currentPage= 1;
			$results = $this->getCategoryList($guid, $currentPage);
			$totalResults = $this->getTotalCategories();
			//$pagination = drawPagination( $_SERVER['PHP_SELF'] ,$totalResults, $this->totalPerPage, $currentPage); //set up Pagination
			$pagination = drawPagination($totalResults, $this->totalPerPage, $currentPage);
			if ($results){
				if($guid){ //Show a category
					foreach ($results as $result){
						$html .='<h3><b>'.htmlentities($result->organisation_name)."</b></h3><br />\n";
						$html .= "<dl>\n\t";
						$html .= "<dt>Website:</dt>\n";
						$html .='<dd><a href="http://'.$result->url.'">'.$result->url."</a></dd>";
						$html .= "<dt>Country:</dt>\n";
						$html .='<dd><b>'.$result->country."</b></dd>\n";
						$html .= "<dt>Individual's Name:</dt>\n";
						$html .= "<dd><b>".$result->individual_name."</b></dd>\n";
						$html .= "<dt>Role in organisation:</dt>\n";
						$html .='<dd><b>'.$result->role."</b></dd>\n";
						$html .= "<dt>Contact Email:</dt>\n";
						$html .='<dd><b>'.$result->email."</b></dd>\n";
						$html .= "<dt>Type of organisation:</dt>\n";
						$html .='<dd><b>'.$result->type."</b></dd>\n";
						$html .= "</dl>\n\t";
					}
				}
				else{ //Show List
					$html = "<div align='center'><h3>Signatories to the code</h3></div>\n";
					//$html .= '<ul id="signatoryList">'."\n\t";
					//print_r($results);
					foreach($results as $result){
						if ($result->country){
							$html .= "<h4>".htmlentities($result->organisation_name).", ".$result->country."</h4>";
						}
						else{
							$html .= "<h4>".htmlentities($result->organisation_name)."</h4>";
						}
						if ($result->url){
							$html .= "<p><a href='http://".$result->url."'>".$result->url."</a>";
							$html .= "<br /><br /><a href=/signatories-to-the-code/?id=".$result->id.">More Details</a></p>";
						} else {
							$html .= "<p><br /><a href=/signatories-to-the-code/?id=".$result->id.">More Details</a></p>";
						}
							$html .= "<hr />";
					}
					//$html .= "</ul>\n\t";
					$html .= "<div align='center' id='pagination'>".$pagination."</div>";
				} 
			}
			else{ // no signatories -> Probably something has gone wrong...
					$html  .= "<p>There are no signatories at this point.</p>\n\t";
			}	
			
			return $html;
			
		}
		
*/		
		public function drawCategoryAdminList($guid = '', $currentPage = false){

			global $totalPerPageAdmin;
			
			$currentPage = ($currentPage) ? $currentPage : 1;
			$results = $this->getCategoryAdminList($guid, $currentPage);
			$totalResults = $this->getTotalCategories();
			$pagination = drawPagination($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], $totalResults, $this->totalPerPageAdmin, $currentPage); // set up Pagination
			//$pagination = str_replace('?','?action=edit&amp;section=contacts&amp;',$pagination); // add action URL variable
			
			// create showing N - n of X results text
			$currentStart = (($currentPage-1)*$this->totalPerPageAdmin)+1; //= N
			$currentEnd = $this->totalPerPageAdmin*$currentPage; //== n
			if($currentEnd > $totalResults){ // if  n is larger than the total results, assign it the total results
				$currentEnd = $totalResults;
			}			
			if($results){
				$html = "<table class=\"filelisttable\">\n<td colspan='4'><p align='center'>Showing $currentStart - $currentEnd of $totalResults</p></td>\n<tr class=\"colheader\">\n";
				$html .= '<th scope="col">Title</th>'."\n";
				$html .= '<th scope="col">Added</th>'."\n";
				$html .= '<th scope="col">Edit</th>'."\n";
				$html .= '<th scope="col">Delete</th>'."\n";
				$html .= "</tr>\n</thead>\n<tbody>";
				foreach($results as $result){
					$html .= '<tr class="tablerowsingle">'."\n\t";
					$html .= '<td><b>'.stripslashes($result->title).'</b></td>'."\n";
					$html .= '<td class="date">'.getUFDate($result->datestarted).'</td>'."\n";
					$html .= '<td class="action edit"><a href="?section=cat&amp;action=edit&amp;id='.$result->cat_id.'">edit</a></td>'."\n";		
					if ($result->suspended == 0){
						$html .= '<td class="action delete">delete</td>'."\n";
						} else {
						$html .= '<td class="action delete"><a href="?section=cat&amp;action=delete&amp;id='.$result->cat_id.'">delete</a></td>'."\n";		
						}
					
				$html .= '</tr>'."\n";
				}
				$html .= "</tbody>\n</table>\n";
				$html .= "<p><div align='center' class='filelisttable'>".$pagination."</div></p>\n";
			}
			else{ //No Categories - something has gone wrong.
				$html = '<p>There are no Categories</p>'."\n";
			}
			
			return $html;
		}
		
		public function drawUserAdminList($guid = '', $currentPage = false){
			global $totalPerPageAdmin;
			
			$currentPage = ($currentPage) ? $currentPage : 1;
			$results = $this->getUserAdminList($guid, $currentPage);
			$totalResults = $this->getTotalUsers();
			//$pagination = drawPagination($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], $totalResults, $this->totalPerPageAdmin, $currentPage); // set up Pagination
			$pagination = drawPagination($_SERVER['PHP_SELF']."?section=user", $totalResults, $this->totalPerPageAdmin, $currentPage);
			//$pagination = str_replace('?','?action=edit&amp;section=contacts&amp;',$pagination); // add action URL variable
			
			// create showing N - n of X results text
			$currentStart = (($currentPage-1)*$this->totalPerPageAdmin)+1; //= N
			$currentEnd = $this->totalPerPageAdmin*$currentPage; //== n
			if($currentEnd > $totalResults){ // if  n is larger than the total results, assign it the total results
				$currentEnd = $totalResults;
			}			
			if($results){
				$html = "<table class=\"filelisttable\">\n<td colspan='4'><p align='center'>Showing $currentStart - $currentEnd of $totalResults</p></td>\n<tr class=\"colheader\">\n";
				$html .= '<th scope="col">Name</th>'."\n";
				$html .= '<th scope="col">Joined</th>'."\n";
				$html .= '<th scope="col">Edit</th>'."\n";
				$html .= '<th scope="col">Suspend</th>'."\n";
				$html .= "</tr>\n</thead>\n<tbody>";
				foreach($results as $result){
					$status = ($result->status==0) ? ' suspended' : false;
					$s = ($result->status==0) ? 1 : 0;
					$txt = ($result->status==0) ? 'activate' : 'suspend';
					
					$html .= '<tr class="tablerowsingle">'."\n\t";
					$html .= '<td><b>'.stripslashes($result->screenname).'</b></td>'."\n";
					$html .= '<td class="date">'.getUFDate($result->datejoined).'</td>'."\n";
					$html .= '<td class="action edit"><a href="?section=user&amp;action=edit&amp;id='.$result->user_id.'">edit</a></td>'."\n";		
					$html .= '<td class="action suspend"><a href="?section=user&amp;action=suspend&amp;s='.$s.'&amp;id='.$result->user_id.'">['.$txt.']</a></td>'."\n";		
					
					
				$html .= '</tr>'."\n";
				}
				$html .= "</tbody>\n</table>\n";
				$html .= "<p><div align='center' class='filelisttable'>".$pagination."</div></p>\n";
			}
			else{ //No users - something has gone wrong.
				$html = '<p>There are no users</p>'."\n";
			}
			
			return $html;
		}
		


		public function getPostAdminList($topic_id, $currentPage = '', $post_id = ''){
			global $db, $totalperPage;
			
			// Create limitations for results
			$startLimit = ($currentPage-1)*$this->totalPerPageAdmin; // work out which record to start from in the database	
			$limitQuery = $startLimit.', '.$this->totalPerPageAdmin;
			
			$query = "SELECT 
				concat(m.firstname, ' ', m.surname) AS username, p.*, 
				DATE_FORMAT(p.date_created, '%D %M %Y') AS nice_date
				FROM forum_posts p 
				LEFT OUTER JOIN members m ON p.user_created=m.member_id WHERE 
				suspended > -2 ";
			
			if($topic_id) {
				if($post_id>0) {
					$query.="AND post_id = '".$post_id."'";
				} 
				else {
					$query.= "AND parent_id = '".$topic_id."'";
				}
				$query.=" ORDER BY date_created DESC LIMIT $limitQuery"; 
				//print "$query<br>\n";
				$results = $db->get_results($query);
				return $results;
			} 
			else {
				echo "getPostAdminList::forum.class.php went wrong"; exit;
			}
			return false;
		}

	public function drawPostAdminList($cat_id, $topic_id, $currentPage = false){
		//print "dPAL($cat_id, $topic_id, $currentPage)<br>\n";
		global $totalPerPageAdmin, $help, $page;
		
		$currentPage = ($currentPage) ? $currentPage : 1;
		$results = $this->getPostAdminList($topic_id, $currentPage);
		$totalResults = $this->getTotalPosts($topic_id);
		//$pagination = drawPagination($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], $totalResults, $this->totalPerPageAdmin, $currentPage); // set up Pagination
		//$pagination = str_replace('?','?action=edit&amp;section=contacts&amp;',$pagination); // add action URL variable
		$msg_len=35;
		
		$pag_html=drawPagination($totalResults,10, $currentPage,$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] );
		
		// create showing N - n of X results text
		$currentStart = (($currentPage-1)*$this->totalPerPageAdmin)+1; //= N
		$currentEnd = $this->totalPerPageAdmin*$currentPage; //== n
		if($currentEnd > $totalResults){ // if  n is larger than the total results, assign it the total results
			$currentEnd = $totalResults;
		}			
		
		if($results){
			
			$html = '<table class="tl_list" id="forum-posts">
<caption>'.$page->drawLabel("tl_foro_post_showing", "Showing posts").' '.$currentStart.' - '.$currentEnd.' '.$page->drawGeneric("of").' '.$totalResults.'</caption>
<thead>
<tr>
<th scope="col">'.$page->drawLabel("tl_foro_post_by", "Posted By").'</th>
<th scope="col">'.$page->drawGeneric("message", 1).'</th>
<th scope="col">'.$page->drawGeneric("date", 1).'</th>
<th scope="col">'.$page->drawLabel("tl_foro_post_manage", "Manage posts").'</th>
</tr>
</thead>
<tbody>
';
			foreach($results as $result){
				$suspended=$result->suspended<0?'<span class="suspended">('.$page->drawGeneric("suspended").')</span>':"";
				$html .= '<tr>
<td><b>'.($result->username?stripslashes($result->username):$result->user_id).'</b></td>
<td><b>'.substr(strip_tags($result->message),0,$msg_len).(strlen($result->content)>$msg_len?"...":"").' '.$suspended.'</b></td>
<td class="date">'.$page->languageDate($result->nice_date).'</td>
<td class="action">
<a '.$help->drawInfoPopup($page->drawLabel("tl_foro_help_edit", "Edit this post")).' class="edit" href="?action=edit&amp;topic_id='.$result->parent_id.'&amp;post_id='.$result->post_id.'&amp;cat_id='.$cat_id.'">edit</a>
<a '.$help->drawInfoPopup($page->drawLabel("tl_foro_help_".($result->suspended<0?"un":"")."susp", ($result->suspended<0?"Un":"")."suspend this post")).' class="'.($result->suspended<0?"publish":"suspend").'" href="?topic_id='.$result->parent_id.'&amp;post_id='.$result->post_id.'&amp;cat_id='.$cat_id.'&amp;action='.($result->suspended<0?"un":"").'suspend">'.($result->suspended<0?"un":"").'suspend</a>
<a '.$help->drawInfoPopup($page->drawLabel("tl_foro_help_delete", "Delete this post")).' class="delete" href="?topic_id='.$result->parent_id.'&amp;post_id='.$result->post_id.'&amp;cat_id='.$cat_id.'&amp;action=delete">delete post</a>
</td>
</tr>
';
			}
			$html .= "</tbody>\n</table>\n";
			$html .= "<p><div align='center' class='filelisttable'>".$pag_html."</div></p>\n";
		}
		else{ //No Posts - something has gone wrong.
			$html = '<p>'.$page->drawLabel("tl_foro_post_none", "There are no posts on this thread").'</p>'."\n";
		}
		return $html;
	}
		
		
		
		
		
		public function editPost($topic_id, $cat_id, $post_id) {
			global $db;
		
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$post_title = $db->escape($_POST['post_title']);
				$post_msg = $db->escape($_POST['post_msg']);
				$suspended = $_POST["suspended"]==1 ? '-1' : '0';
							
				if ($post_id){
					$query = "UPDATE forum_posts SET title='$post_title', message='$post_msg', suspended='$suspended' WHERE post_id= '$post_id'";
					//print "$query<br>\n";
					if($db->query($query)) return 1;
					else $message = 'Whilst editing the post, an error occured or no details were changed. Please try again. ';
				}
				else $message = 'You have not updated this post, no post id was found.';
			}
			return $message;
		}
		

		public function getSuspended($post_id) {
			global $db;
			$suspended=$db->get_var("SELECT suspended FROM forum_posts WHERE post_id=".$post_id)?true:false;
			//print "gS($post_id) = $suspended<br>\n";
			return $suspended;
		}
		public function suspendPost($post_id, $suspended=true) {
			global $db;
			//print "sP($post_id, $suspended)<br>\n";
			if ($post_id){
				$query = "UPDATE forum_posts SET suspended='".($suspended?-1:0)."' WHERE post_id= '$post_id'";
				return $db->query($query);
			}
			return false;
		}


		// Dont really delete just set suspended to -2
		// Maybe need to consider what to do if we are deleting categories or threads.
		public function deletePost($post_id) {
			global $db;
			if ($post_id){
				$query = "UPDATE forum_posts SET suspended=-2 WHERE post_id= '$post_id'";
				//print "$query<br>\n";
				return $db->query($query);
			}
			return false;
		}
		
		public function editUser($guid) {
			global $db, $message;
		
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$fname = addslashes($_POST['fname']);
				$lname = addslashes($_POST['lname']);
				$screenname = addslashes($_POST['screenname']);
				$email = addslashes($_POST['email']);
							
				if ($guid){
				
					$query = "UPDATE forum_users SET fname='$fname', lname='$lname', screenname = '$screenname', email='$email' WHERE user_id= '$guid'";
					
					//echo $query	; exit;
					
					if($db->query($query)){
						if ($db->rows_affected == 0){
							$message = 'No changes have been made to this user';
						} else if ($db->rows_affected == 1) {
							$message = 'You have successfully updated this user.';	
						} else {
							$message = 'Whilst editing the user, an error occured. Please try again. ';
						}
					}else {
						$message = 'Whilst editing the user, an error occured. Please try again. ';
					}
					
				}
				else{
					$message = 'You haven\'t updated this user, it needs a user id.';
				}
				$message = str_replace(' ','+',$message);
				redirect('/treeline/forum-admin.html?section=user&message='.$message.'');
			}
		}
		
		
		
		// 31st Jan 2009 - Phil Redclift
		// Return a list of categories
		public function getCategoryAdminList($guid, $currentPage = ''){
			global $db, $site, $totalperPage;
			
			// Create limitations for results
			$startLimit = ($currentPage-1)*$this->totalPerPageAdmin; // work out which record to start from in the database	
			$limitQuery = $startLimit.', '.$this->totalPerPageAdmin;
			
			$query = "SELECT post_id, title, message, date_created, 
				suspended as status, if(suspended=-1,1,0) AS suspended, member_type
				FROM forum_posts WHERE 
				suspended>-2 ";
			if($guid) $query.="AND post_id='". $guid ."' LIMIT 1";
			else $query.= "AND msv=".$site->id." AND parent_id=0 LIMIT $limitQuery";
			//print "$query<br>\n";
			$results = $db->get_results($query);
			return $results;
		}
		
		public function getUserAdminList($guid, $currentPage = ''){
			global $db, $totalperPage;
			
			// Create limitations for results
			$startLimit = ($currentPage-1)*$this->totalPerPageAdmin; // work out which record to start from in the database	
			$limitQuery = $startLimit.', '.$this->totalPerPageAdmin;
			
			if($guid) {
				/*$query = "SELECT * FROM forum_users 
						WHERE user_id='". $guid ."' ORDER BY datejoined ASC LIMIT 1";*/
				$query = "SELECT * FROM members 
						WHERE member_id='". $guid ."' ORDER BY datejoined ASC LIMIT 1";
			} else {
				//$query = "SELECT * FROM forum_users ORDER BY datejoined ASC LIMIT $limitQuery";
				$query = "SELECT * FROM members ORDER BY datejoined ASC LIMIT $limitQuery";
			}
			$results = $db->get_results($query);
			return $results;
		}
		
		
		public function getTopicAdminList($cat_id, $currentPage = '', $topic_id = ''){
			global $db, $totalperPage;
			//print "gTAL($cat_id, $currentPage, $topic_id)<br>\n";
			// Create limitations for results
			$startLimit = ($currentPage-1)*$this->totalPerPageAdmin; // work out which record to start from in the database	
			$limitQuery = $startLimit.', '.$this->totalPerPageAdmin;

			$query = "SELECT *, IF(suspended=-1,1,0) AS suspended FROM forum_posts f WHERE
				suspended>-2 ";
			if($topic_id) {
				 $query.="AND post_id = '".$topic_id."' AND parent_id = '".$cat_id."'";
			} 
			else if($cat_id) {
				$query.= "AND parent_id = $cat_id";
			} 
			else {
				echo "getTopicAdminList::forum.class.php went wrong"; 
				exit;
			}
			//print "$query<br>\n";
			$results = $db->get_results($query);
			return $results;
		}
		
		
	
		
		public function getTotalCategories(){
			global $db;
			
			$totalQuery = "SELECT count(c.cat_id) FROM forum_categories c";
			
			$totalResults = $db->get_var($totalQuery);
			
			return $totalResults;
		}
		
		public function getTotalUsers(){
			global $db;
			
			$totalQuery = "SELECT count(m.member_id) FROM members m";
			
			$totalResults = $db->get_var($totalQuery);
			
			return $totalResults;
		}
		
		public function getTotalPosts($topic_id){
			global $db;
			
			$totalQuery = "SELECT count(p.post_id) 
				FROM forum_posts p 
				WHERE parent_id=".$topic_id." 
				AND suspended > -2";
			
			$totalResults = $db->get_var($totalQuery);
			
			return $totalResults;
		}
		
		
		public function suspendUser($guid) {
			global $db, $message;
		
			//if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$statusflag = read($_REQUEST,'s',false);
				if($statusflag>=0){
					$query = "UPDATE forum_users SET suspended=". $statusflag ." WHERE user_id=".$guid;			
								
					if($db->query($query)){
						$message = 'You have successfully changed the status of this user.';	
					}else {
						$message = 'Whilst changing the status of the user, an error occured. Please try again. ';
					}
				}
				else{
					$message = 'You haven\'t updated this user, no status flag provided.';
				}
				$message = str_replace(' ','+',$message);
				redirect('/treeline/forums/?section=user&message='.$message);
		}
		
	}