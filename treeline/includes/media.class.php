<?php
class Media {

	public $guid;
	public $title;
	public $name;
	public $description, $code;
	public $shared;
	public $resource, $respond;
	public $site_id;	
	public $category, $catid;
	public $subcategory, $subcatid;
	
	
	public $totalresults;
	public $perpage;
	public $page;
	public $totalpages;
	public $from;
	public $to;	
	
	public function __construct() {
		// This is loaded when the class is created	

	}
	
	// guid
	public function setGUID($guid) {
		$this->guid = $guid;
	}
	public function getGUID() {
		return $this->guid;
	}
		
	// Title
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getTitle() {
		return $this->title;
	}

	// Name
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function setShared($shared) {
		if ($shared) $this->shared=1;
		else $this->shared=0;
	}		

	public function setResource($resource) {
		if ($resource) $this->resource=1;
		else $this->resource=0;
	}		

	public function setRespond($respond) {
		if ($respond) $this->respond=1;
		else $this->respond=0;
	}		

	// Code
	public function setCode($code){
		$this->code = $code;
	}
	public function getCode($code){
		return $this->code;
	}
	


		
	// Pagination variables.		
	public function setPerPage($num){
		$this->perpage = $num;
	}
	public function getPerPage(){
		return $this->perpage;
	}

	public function setTotal($count){
		$this->totalresults = $count;
	}
	public function getTotal(){
		return $this->totalresults;
	}

	public function setPage($page){
		$this->page = $page;
	}
	public function getPage(){
		return $this->page;
	}

	public function setTotalPages($total){
		$this->totalpages = ceil($this->getTotal()/$this->getPerPage());
	}
	public function getTotalPages(){
		return $this->totalpages;
	}


		

		// 12th Jan 2009 - Phil Redclift
		// 19th Oct 2009 - Updated Phil Redclift 
		// Make this more robust.
		// Not it always succeeds no matter what
		// Set category($cat) 
		// Sets category and add it to media_categories if its not there already
		public function setCategory($category, $newcategory) {
			global $db, $site;
			//print "sC($category, $newcategory)<br>\n";
			$this->catid=$catid=0;
			if ((!$category || $category=="xx") && !$newcategory) return false;
			
			if ($category>0) $catid = $category;
			else if ($newcategory) {
				$newcategory=$db->escape(htmlentities($newcategory,ENT_QUOTES,$site->properties['encoding']));
				$query="SELECT id 
					FROM media_categories 
					WHERE title='".$newcategory."' AND msv=".$site->id;
				//print "$query<br>";
				$catid=$db->get_var($query);
				if (!$catid) {
					$query="insert into media_categories (title, msv)
						values ('".$newcategory."', ".$site->id.")";
					//print "$query<br>";
					if ($db->query($query)) $catid=$db->insert_id;
				}
			}
			$this->catid=$catid;
			return $catid;
		}
		
		public function getCategory() {
			return $this->category;
		}
		public function getCatID() {
			return $this->catid;
		}

		// 12th Jan 2009 - Phil Redclift
		// 19th Oct 2009 - Updated Phil Redclift 
		// Set subcategory($catid, $subcat) 
		// Sets subcategory and add it to media_categories if its not there already under $catid 
		public function setSubcategory($subcategory, $newsubcategory) {
			global $db, $site;
			//print "sS($subcategory, $newsubcategory)<br>\n";
			$this->subcatid = $subcatid = 0;
			
			if ((!$subcategory || $subcategory=="xx") && !$newsubcategory) return false;
			if (!$this->catid) return false;
			
			if ($subcategory>0) $subcatid = $subcategory;
			else if ($newsubcategory) {
				$newsubcategory = $db->escape(htmlentities($newsubcategory,ENT_QUOTES,$site->properties['encoding']));
				$query="SELECT id 
					FROM media_categories 
					WHERE title='".$newsubcategory."' and parent=".$this->catid;
				$subcatid=$db->get_var($query);
				//print "scid($subcatid) $query<br>";
				if (!$subcatid) {
					$query="insert into media_categories (title, parent, msv)
						values ('".$newsubcategory."', ".$this->catid.", ".$site->id.")";
					//print "$query<br>";
					if ($db->query($query)) $subcatid=$db->insert_id;
				}
			}
			$this->subcatid=$subcatid;
			return $subcatid;
		}
		
		public function getSubcategory() {
			return $this->subcategory;
		}
		public function getSubcatID() {
			return $this->subcatid;
		}
		
		

		public function create() {
			global $db, $user, $site;

			$this->guid = uniqid();
			$title = $db->escape(htmlentities($this->title,ENT_QUOTES,$site->properties['encoding']));	
			$code = $db->escape(htmlentities($this->code,ENT_QUOTES,$site->properties['encoding']));			
			$description = $db->escape(htmlentities($this->description,ENT_QUOTES,$site->properties['encoding']));			
			$name = $db->escape($this->name);
			$msv=$this->site_id?$this->site_id:$site->id;
			$date_created=$this->date_created?"'".$this->date_created."'":"NOW()";
			$user_created=$this->user_created?$this->user_created:$user->getID();
						
			$query = "INSERT INTO media 
				(guid, title, description, shared, name,  code,
				category, subcategory, date_created, user_created, 
				msv, resource, responsive)
				VALUES 
				('$this->guid', '$title', '$description', ".($this->shared+0).", '$name', '$code',
				{$this->catid}, {$this->subcatid}, $date_created, $user_created, 
				$msv, ".($this->resource+0).", ".($this->respond+0).")";
			//print "$query<br>";
			$db->query($query);
			if ($db->last_error) return false;
			return true;
		}
		
		public function save() {
			global $db, $user;
		
			$title = $db->escape(htmlentities($this->title,ENT_QUOTES,$site->properties['encoding']));			
			$description = $db->escape(htmlentities($this->description, ENT_QUOTES, $site->properties['encoding']));
			$code = $db->escape(htmlentities($this->code,ENT_QUOTES,$site->properties['encoding']));			
			$name = $db->escape($this->name);
			//$category = $db->escape(htmlentities($this->category,ENT_QUOTES,$site->properties['encoding']));
			$query = "UPDATE media
				SET title='$title', 
				name = '".$this->getName()."', 
				description = '$description', 
				code = '$code',
				shared = ".($this->shared+0).",
				resource = ".($this->resource+0).",
				responsive = ".($this->respond+0).",
				category={$this->catid}, subcategory=".($this->subcatid+0)."
				WHERE guid = '{$this->guid}'";
			//print "$query<br>";
			$db->query($query);
			if ($db->last_error) return false;
			return true;
		}		


		// 16/12/2008 Phil Redclift
		// Delete media from the database.
		public function delete($guid) {
			global $db;
			$query = "DELETE FROM media WHERE guid='$guid'";
			if ($db->query($query)) return true;
			//print "Failed delete($query)<br>\n";
			return false;
		}

	
		public function drawCategories($current=0) {
			global $db, $site;
			$query="SELECT m.id, m.title as category 
				FROM media_categories m
				WHERE msv=".$site->id."
				AND parent=0
				GROUP BY title 
				ORDER BY title";
			//print "$query<br>";
			if ($categories = $db->get_results($query)) {
				$html = '';
				foreach ($categories as $category) {
					$selected = ($category->id == $current)?'selected="selected"':'';
					$html .= '<option value="'.$category->id.'" '.$selected.'>'.$category->category.'</option>'."\n";
				}
			}
			return $html;
		}			



	// 13th Jan 2009 - Phil Redclift
	// Collect a list of subcategories and return as a data array.
	public function getSubcategories($msv=1){
		global $db;
		
		$query = "SELECT m.id, m.parent, m.title, m.msv
					FROM media_categories m
					INNER JOIN media_categories m1 on m.parent=m1.id
					WHERE m.msv=$msv
					ORDER BY m.parent, m.title";
		//print "$query<br>";
		return $db->get_results($query);
	}

	// 13th Jan 2009 - Phil Redclift
	// Collect a list of subcategories and put them into a javascript array.
	// This array is used to dynamically populate the subcategory list without having to refresh the page.
	public function drawSubcategories() {
		global $db, $site;
		$categories = $this->getSubcategories($site->id);
		$html = '';
		if (is_array($categories)) {
			foreach ($categories as $category) {
				//$selected = ($category->id == $current)?' selected="selected"':'';
				//$html .= '<option value="'.$category->id.'"'.$selected.'>'.$category->title.'</option>';
				$html.="subcats.push(new Array(".$category->id.", ".$category->parent.",'".addslashes($category->title)."'));"."\n";
			}
		}
		return $html;
	}	

		
		public function loadByGUID($guid){
			////// fills the instance of this class with the parameters for the given file
			global $db;

			$query=	"SELECT * FROM media WHERE guid='$guid' LIMIT 1";
			//print "$query<br>\n";
			$row = $db->get_row($query);
			if ($row) {
				$this->guid = $row->guid;
				$this->title = $row->title;
				$this->name = $row->name;
				$this->code = $row->code;
				$this->description = $row->description;
				$this->shared = $row->shared;
				$this->resource = $row->resource;
				$this->respond = $row->responsive;
				$this->site_id = $row->msv;
				$this->catid = $row->category;
				$this->subcatid = $row->subcategory;
			}
			else $this->guid='';
		}
		
		

		
	// 13th Jan 2009 - Phil Redclift
	// 19th Oct 2009 - Phil Redclift
	// Select a list of media and return a data array.
	// $cat is an optional category variable, if its set to zero or has a space or nothing, it should show all files
	public function getMediaList($cat=false, $keywords=''){
		global $db, $site;

		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		$query = "SELECT m.guid, m.name, m.title, date_format(m.date_created,'%D %M %Y') datemade,
			u.name username, u.full_name fullname, u.email
			FROM media m 
			LEFT JOIN users u ON m.user_created=u.id 
			WHERE m.msv=".$site->id." ";
		if($cat) $query.=" AND m.category='$cat' ";
		if ($keywords) $query.="AND m.title LIKE '%$keywords%' ";
		//print "$query<br>\n";
		
		$db->query($query);
		$this->setTotal($db->num_rows);	
		$this->setTotalPages($db->num_rows);	
		$db->flush();
		$query.="ORDER BY m.date_created DESC, m.title ASC LIMIT ". $this->from .",". $this->getPerPage();
		//print "$query<br>\n";

		$files = $db->get_results($query);
		if(sizeof($files)>0) return $files;
		return false;
	}

	// draw a list of media with options to manage them
	public function drawList($p=1, $action=false, $cat=false, $keywords=''){
		global $help, $page;
		
		$this->setPerPage(10);
		$this->setPage($p);	
			
		if($results = $this->getMediaList($cat, $keywords) ){
			foreach($results as $result){
				$html .= '<tr>
	<td><strong>'.$result->title.'</strong></td>
	<td><a href="mailto:'. $result->fullname.'&lt;'.$result->email .'&gt;">'. $result->username .'</a></td>
	<td nowrap>'.$page->languageDate($result->datemade).'</td>
	<td nowrap class="action">
		<a class="edit" '.$help->drawInfoPopup($page->drawLabel("tl_media_help_edit", "Edit media")).' href="/treeline/media/?action=edit&amp;guid='.$result->guid.'">edit media</a>
		<a class="delete" '.$help->drawInfoPopup($page->drawLabel("tl_media_help_delete", "Delete media")).' href="/treeline/media/?action=delete&amp;guid='.$result->guid.'">delete media</a>
	</td>
</tr>
';
			}
			$html = '<table class="tl_list">
<caption>'.$this->drawTotal() .'</caption>
<thead>
	<tr>
	<th scope="col">'.$page->drawGeneric("title", 1).'</th>
	<th scope="col">'.$page->drawGeneric("author", 1).'</th>
	<th scope="col">'.$page->drawLabel("tl_img_list_created", "Created on").'</th>
	<th scope="col">'.$page->drawLabel("tl_media_list_manage", "Manage media").'</th>
	</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
			//$html .= $this->drawPagination("/treeline/files/?action=$action&amp;category=$cat", $this->getTotal(), 10, $page);
			$html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/media/?action=$action&amp;category=$cat&amp;q=".$keywords);
			return $html;
		}
		else return $page->drawLabel("tl_media_list_nomedia", 'There is no media to display');
	}
	
	// 13th Jan 2009 - Phil Redclift
	// Show the number of files matched by the current search.
	public function drawTotal(){
		global $page;
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1) $msg = $page->drawLabel("tl_media_total_one", 'There is only 1 matching block in the media library');
		else $msg = $page->drawLabel("tl_media_title_showing", 'Showing blocks').' '.($this->from+1).' - '.$to.' '.$page->drawGeneric('of').' '.$this->getTotal();
		return $msg;
	}



//////////////////////////////


	
	}
?>
