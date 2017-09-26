<?

class PersonalStory {

	public $properties = array(); // use to hold all data for a story
	public $stories = array(); // used for a collection of stories (stories[0]->properties for example...
	
	public function __construct( $guid=false ){
		if( $guid ){
			$this->loadByGUID($guid);
		}
		else{ // get random story
			$this->loadByRandom();
		}
	}
	
	// LOAD by guid
	public function loadByGUID( $guid=false ){
		global $db;
		//echo 'guid: '. $guid .'<br />';
		if( $guid ){
			$query = "SELECT ps.*, p.title, p.meta_description as summary
						FROM pages_stories ps
						LEFT JOIN pages p ON ps.guid=p.guid 
						WHERE ps.guid='$guid'";
//echo 'query: '. $query .'<br />';
			if( $results = $db->get_row($query,"ARRAY_A")){
				$this->properties = $results;
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function loadByRandom( ){
		global $db;
		
			$query = "SELECT ps.*, p.title, p.meta_description as summary
						FROM pages_stories ps
						LEFT JOIN pages p ON ps.guid=p.guid 
						ORDER BY RAND()
						LIMIT 1";
//echo 'query: '. $query .'<br />';
			if( $results = $db->get_row($query,"ARRAY_A")){
				$this->properties = $results;
				return true;
			}else{
				return false;
			}
	}
	
	
	
	public function create( $properties=false ){
		global $db;
		
		if( isset($properties) && is_array($properties) ){
			
			$query = "INSERT INTO pages_stories (guid, image1, link_text, related1, related2, name) 
						VALUES ('". $properties['guid'] ."', '". $properties['image1'] ."', 
						'". $db->escape($properties['link_text']) ."', '". $properties['related1'] ."', 
						'". $properties['related2'] ."', '". $properties['name'] ."')";
			//niceError($query);
			if( $db->query($query) ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function update( $properties=false ){
		global $db;
		
		if( isset($properties) && is_array($properties) ){
			
			/*$query = "UPDATE pages_stories SET  
						image1 = '". $properties['image1'] ."', image2 = '". $properties['image2'] ."', 
						link_text = '". $properties['link_text'] ."', related1 = '". $properties['related1'] ."', 
						related2 = '". $properties['related2'] ."', related3 = '". $properties['related3'] ."', name = '". $properties['name'] ."' WHERE guid = '". $properties['guid'] ."'";*/
			
			$query = "UPDATE pages_stories SET  
						image1 = '". $properties['image1'] ."', 
						link_text = '". $db->escape($properties['link_text']) ."', related1 = '". $properties['related1'] ."', 
						related2 = '". $properties['related2'] ."', name = '". $properties['name'] ."' WHERE guid = '". $properties['guid'] ."'";			
						
			niceError($query); 
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
	
	
	
	
	public function getStoriesList(){
		global $db;
		$query = "SELECT p.title, p.guid
					FROM pages_stories ps
					LEFT JOIN pages p ON ps.guid=p.guid 
					ORDER BY ps.name";
		if( $list = $db->get_results($query,"ARRAY_A") ){
			return $list;
		}else{
			return false;
		}
	}
	
	public function getExtendedStory($guid = false){
		global $db;
		
		$query = "SELECT id 
				  FROM content c 
				  WHERE parent = '$guid' 
				  AND placeholder = 'content'";
		$db->query($query);
		
		if ($db->num_rows >= 1){
			return true;
		}else{
			return false;
		}
	}
	
	
	// LOAD by a tag or select of...
	/*
	public function loadByTags( $tags=false ){
		global $db,$siteID;
		
		if( isset($tags) ){
			
			$query = "SELECT ps.*, count(ps.guid) as total 
						FROM pages_stories ps
						LEFT JOIN tag_relationships tr ON tr.guid=ps.guid
						LEFT JOIN tags t ON t.id=tr.tag_id
						LEFT JOIN pages p ON ps.guid=p.guid
						WHERE p.site_id=". $siteID;
			
			if( is_string($tags) ){
				$query .= " AND t.tag='$tags'";
				$limit = 
			}else if( is_array($tags) ){
				foreach($tags as $tag){
					$tmp[] = "'". $tag ."'";
				}
				$tmp = join(', ',$tmp);
				$query .= " AND t.tag IN ($tmp)";
			}
			
			// try to add a random number in to get a different one each time "LIMIT $random,1"
			$query .= " LIMIT 1";
			
			/// now go and get a story...
			
			return true;
			
		}else{
			return false;
		}
		
	}
*/
}


?>