<?
/*
	Thread Class for Treeline Intranet
	------------------------------------
	Author: Dan Donald
	Email: dan.donald@ichameleon.com
	Date Started: 17th July 2007

	Last Updated By:
	Last Updated On:
	
	Description:
		This class controls forum posts and comments.

	Prerequisites:
		Uses a predefined instance of the ezSQL class called $db.
	
	Methods:
		__construct
		__get
		__set
		load( $file_id )
*/

class Thread {

	public $id;
	private $type;
	private $count;
	private $posts = array();
	//private $tags = array();
	//private $tag;

	public $abuse;

	public function __construct( $id = false, $type=false ){
		
		if( isset($id) ){
			$this->id = $id;
		}
		if( isset($type) ){
			$this->type = $type;
		}
		if( $id && $type ){
			$this->load($id,$type);
		}
		//$this->tag = new Tag();
		
		$this->abuse = new Abuse();

	}



//// Get/set methods

	// this can be used to get an attribute, unless a specialised method exists.
	// methods need to be in the format getThisMethodName.
	private function __get($attribute){	
		$method = str_replace(' ','','get'.ucwords( str_replace('_',' ',$attribute) ) );
		
		if( isset($this->$attribute)  ){
			return $this->$attribute;
		} else if( method_exists($this,$method) ){
			return call_user_method($method,$this);
		} else {
			return false;
		}
	}

	private function __set($attribute,$value){
		if( isset($this->$attribute) ){
			$this->$attribute = $value;
			return true;
		}else{
			return false;
		}
	}

	
	
	public function load( $id=false, $parent=false, $orderby='date_created', $order_direction='desc', $from=0, $quantity=10 ){
		global $db, $site;
		//print "load(id($id), parent($parent), type($type), $orderby, $order_direction, $from, $quantitiy)<br>\n";
		$id = ($id>=0) ? $id : $this->id;
		//print "l***($id, $parent, $type, $orderby, $order_direction, $from, $quantity)<br>";
		
		$fields = "p.*, 
			CONCAT(mc.firstname,' ',mc.surname) as user_created_name, 
			CONCAT(mm.firstname,' ',mm.surname) as user_modified_name ";
		if ($parent==0) $fields.=", count(p3.post_id) as posts ";
				
		// If we have a parent then get this post
		if( $parent>0 ){
			$tmp = "SELECT ". $fields .", 
					IF(p2.`member_type`,p2.`member_type`,null) as category_restrict FROM forum_posts p
					LEFT JOIN members mc ON mc.member_id=p.user_created /* uc = user created */
					LEFT JOIN members mm ON mm.member_id=p.user_modified /* um = user modified */
					LEFT OUTER JOIN forum_posts p2 ON p.parent_id=p2.post_id /* get the parent's data */
					LEFT OUTER JOIN forum_posts p3 ON p3.parent_id=p.post_id /* get the child's data */
					LEFT OUTER JOIN forum_posts p4 ON p2.parent_id=p4.post_id /* get the grandparent's data */
					WHERE p.post_id=". $id ." 
					AND (p.member_type=0 OR p.member_type=".($_SESSION['member_type_id']+0).")
					AND p.msv=".$site->id."
					
					UNION 
					";
		}
		//print_r($_SESSION);
				
		$query = "SELECT ". $fields .", IF(p4.`member_type`,p4.`member_type`,p2.`member_type`) as category_restrict
					FROM forum_posts p
					LEFT JOIN members mc ON mc.member_id=p.user_created /* uc = user created */
					LEFT JOIN members mm ON mm.member_id=p.user_modified /* um = user modified */
					LEFT OUTER JOIN forum_posts p2 ON p.parent_id=p2.post_id /* get the parent's data */
					LEFT OUTER JOIN forum_posts p3 ON p3.parent_id=p.post_id /* get the child's data */
					LEFT OUTER JOIN forum_posts p4 ON p2.parent_id=p4.post_id /* get the grandparent's data */
					WHERE p.parent_id=$id 
					AND p.msv=".$site->id."
					AND (p.member_type=0 OR p.member_type=".($_SESSION['member_type']+0).")
					AND p.suspended>=-1
					GROUP BY ". (( !$tmp ) ? 'p.' : '' ) ."post_id
					";
				
		$orderby = (!$tmp) ? 'p.'.$orderby : $orderby;
		$orderby = 'parent_id ASC, '. $orderby;
		$orderby = (!$tmp) ? 'p.'.$orderby : $orderby;
				
		$query = ($tmp) ? $tmp.$query : $query;
		$db->query($query);
		//echo nl2br($query)."<br>\n";
			
		$this->count = $db->num_rows;
			
		$query .= ' ORDER BY '. $orderby .' '. $order_direction .' LIMIT '. $from .','. $quantity;
		//print "$query<br>\n";
			
		//print "$query<br>\n";
		if( $results = $db->get_results($query) ){
			$this->id = $id;
			$this->posts = $results;
			return true;
		}
			
		return false;		
	}
	


	public function add( $properties = false ){
		global $db, $site;

		$fields = $values = '';

		if( $properties && is_array($properties) ){

			//print_r($properties);
			
			// Dont save any empty messages
			if (!$properties['message']) return false;
			if (!$properties['parent_id']>0) return false;
		
			//$fields = implode(', ',array_keys($properties) );
			foreach( $properties as $k=>$v ) {
				$fields .= $k.", ";
				$values .= "'". $db->escape($v) ."', ";
			}
			//print "got f($fields) v($values)<br>\n";
			
			$query = "INSERT INTO forum_posts 
				(user_created, date_created, msv, ".substr($fields, 0, -2).") 
				VALUES 
				(
					".($_SESSION['member_id']+0) .", NOW(), 
					".$site->id.", ".substr($values, 0, -2)."
				)";
			//print "$query<br>\n"; 
			if( $db->query($query)){
				$this->id = $db->insert_id;
				return $this->id;
			}
		}
		return false;
	}


	public function update( $properties = false ){
		global $db;
		
		if( $properties && is_array($properties) ){
			//echo '<pre>'. print_r($properties,true) .'</pre>';
			foreach( $properties as $key => $value ){
				if( in_array($key, array('member_type','parent_id') ) && $value<=0 ){
					$properties[$key] = 0;
				}
				if( $key=='member_type' ){
					$properties['`member_type`'] = $value;
					unset( $properties['member_type'] );
				}
			}
			
			//$fields = implode(', ',array_keys($properties) );
			//$values = '';
			$insert = '';
			foreach( $properties as $key => $item ){
				if( $key=='message' ){
					$item = nl2br($item);
				}
				if( $key!='post_id' ){
					$insert .= ", ". $key ."='". mysql_real_escape_string( $item ) ."'";
				}
			}
			
			$insert = substr($insert,1);

			$query = "UPDATE forum_posts SET ". $insert .", user_modified=". $_SESSION['intranet_user_id'] .",
						date_modified=NOW() WHERE post_id='". $properties['post_id'] ."' ";
			//echo $query.'<br />';

			$this->id = $properties['post_id'];
			$db->query($query);

			if( $db->rows_affected>=0 ){
				return true;
			}else{
				return false;
			}

		}else{
			return false;
		}
	}
	
	
	// use this for 'suspending' posts
	public function changeStatus( $postID=false, $status=false ){
		global $db;
		
		if( $postID && ($status==0 || $status==-1) ){
			$query = "UPDATE forum_posts SET suspended=". $status ." WHERE post_id=". $postID;
			//echo $query.'<br />';
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
	
	
	public function delete( $postID ){
		global $db;
		
		if( $postID>0 ){
			// this deletes the main post - how can we expend that to delete the posts and all of it's siblings?
			$query = "select `p1`.`post_id` AS `p1_id`,`p2`.`post_id` AS `p2_id`,`p3`.`post_id` AS `p3_id`
				from `forum_posts` `p1`
				left outer join `forum_posts` `p2` on `p1`.`post_id` = `p2`.`parent_id`
				left outer join `forum_posts` `p3` on `p2`.`post_id` = `p3`.`parent_id`
				where p1.post_id=". $postID;
			//echo $query .'<br />';
			$breadcrumb = $db->get_results($query,"ARRAY_A");
			
			$tmpArray = array();
			foreach($breadcrumb as $item){
				foreach($item as $value){
					if( $value>'' && !in_array($value,$tmpArray) ){
						$tmpArray[] = $value;
						$tmp .= ', '.$value;
					}
				}
			}
			//$tmp = substr($tmp,2);
			//$tmp = $postID.$tmp;

			$query = "DELETE FROM forum_posts WHERE post_id IN (". $postID . $tmp .")";
			//echo $query .'<br />';
			
			if( $db->query( $query ) ){
				return true;
			}else{
				return false;
			}
			
		}else{
			return false;
		}	
	}
	



	public function getBreadcrumb( $post_id ){
		global $db;
		$breadcrumb = array();
		
		if( $post_id ){
			$query = "select `p1`.`post_id` AS `p1_id`,`p1`.`title` AS `p1_title`,`p2`.`post_id` AS `p2_id`,`p2`.`title` AS `p2_title`,
						`p3`.`post_id` AS `p3_id`,`p3`.`title` AS `p3_title`
						from `forum_posts` `p1`
						left outer join `forum_posts` `p2` on `p1`.`parent_id` = `p2`.`post_id`
						left outer join `forum_posts` `p3` on `p2`.`parent_id` = `p3`.`post_id`
						where p1.post_id=". $post_id;
			if( $results = $db->get_row($query,"ARRAY_A") ){
				if( $results['p1_id']>0 ){
					if( $results['p3_title'] ){
						$breadcrumb[] = array('id'=>$results['p3_id'],'parent'=>0,'title'=>$results['p3_title']);
					}
					if( $results['p2_title'] ){
						$parent = (!$results['p3_title']) ? 0 : $results['p1_id'];
						$breadcrumb[] = array('id'=>$results['p2_id'],'parent'=>$parent,'title'=>$results['p2_title']);
					}
					if( $results['p1_title'] ){
						$breadcrumb[] = array('id'=>$results['p1_id'],'parent'=>$results['p2_id'],'title'=>$results['p1_title']);
					}
					$this->breadcrumb = $breadcrumb;
					
					return $breadcrumb;
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}


	public function topForumPosts( $total=false ){
		global $db, $site;
		if( $total>0 ){
			$html = '';
			$query = "SELECT f.* 
				FROM forum_posts f
				LEFT JOIN members m ON m.member_id=f.user_created
				WHERE f.parent_id!=0
				AND f.suspended>=0
				AND (f.member_type=0 OR f.member_type=".($_SESSION['member_type_id']+0).")
				AND f.msv=".$site->id."
				GROUP BY f.post_id
				ORDER BY f.date_created DESC LIMIT ". $total;
			//echo $query .'<br />';
			
			if( $data = $db->get_results( $query ) ){
				$i =0;
					$html = '<table class="treeline" id="recent-posts" cellpadding="0" cellspacing="0" border="0">
<caption class="title">Recent posts</caption>
<thead>
<tr class="category">
<th scope="col">Post</th>
<th scope="col">Date/Time</th>
</tr>
</thead>
<tbody>
';
					foreach( $data as $item ){
						$class = ($i%2 != 0) ? 'even' : 'odd';
						$html .= '<tr class="category '.$class.'">'."\n";
						$html .= '<td class="title"><a href="'.$site->link.'forum/?post='.$item->post_id.'&amp;parent='.$item->parent_id.'">'.$item->title.'</a></td>'."\n";
						$html .= '<td class="postdate"><span class="date">'. date('d/m/y  \a\t H:i', getDateFromTimestamp( $item->date_created ) ) .'</span></td>'."\n";
						$html .= '</tr>'."\n";
						$i++;
					}
					$html .= '<tr><td colspan="2" class="forum-bottom"></td></tr></tbody>'."\n".'</table>';	
					return $html;		
			}else{
				return '<p>There are no forum posts to display.</p>';
			}
			
		}else{
			return false;
		}
	}




	/*
		public function addTags($taglist=false,$postID=false){
			global $db;
			$id = ($postID) ? $postID : $this->id;
			
			if( is_array($taglist) ){
				$query = "DELETE FROM tag_relationships WHERE type=4 AND ref_id=". $id;
				$db->query($query);
			//echo $query .'<br />';
				foreach( $taglist as $tag ){
					if( $tag['title']>'' ){
						$tag_id = $this->tag->add( $tag['title'] );
						$query = "REPLACE INTO tag_relationships (tag_id, type, ref_id) VALUES (". $tag_id .", 4, ". $id .")";
						//echo $query.'<br />';
						$db->query( $query );
					}
				}
				return true;
			}else{
				return false;
			}
			
		}
	*/

	/*	
	public function getTags($postID=false){
		$id = ($postID) ? $postID : $this->id;
		if( $id ){
			
			if( is_object($this->tag) ){
			}else{
				$this->tag = new Tag();
			}
		
			
			// Get tags for the user...
			$this->tag->tags = array();
			$this->tag->count = 0;
			if( $this->tag->loadByRef( $id, 4 ) ){
				return $this->tag->tags;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}
	*/




	
}


?>