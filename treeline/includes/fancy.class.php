<?php
class Fancy {

	public $guid;
	public $title;
	public $name;
	public $description, $code, $codetype;
	public $shared;
	public $site_id;	
	
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

	// Code
	public function setCode($code, $type){
		$this->code = $code;
		$this->codetype = $type;
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

	public function create() {
		global $db, $user, $site;

		$this->guid = uniqid();
		$title = $db->escape(htmlentities($this->title,ENT_QUOTES,$site->properties['encoding']));
		$codeRaw = $this->addId('colbox', $this->code);		
		$code = $db->escape(htmlentities($codeRaw,ENT_QUOTES,$site->properties['encoding']));			
		$description = $db->escape(htmlentities($this->description,ENT_QUOTES,$site->properties['encoding']));			
		$msv=$site->id;
		$date_created=$this->date_created?"'".$this->date_created."'":"NOW()";
		$user_created=$this->user_created?$this->user_created:$user->getID();
					
		$query = "INSERT INTO fancy 
			(
			guid, title, description, shared, code, codetype,
			date_created, user_created, msv
			)
			VALUES 
			(
			'$this->guid', '$title', '$description', ".($this->shared+0).", '$code', '".$this->codetype."',
			$date_created, $user_created, $msv
			)
			";
		//print "$query<br>";
		$db->query($query);
		if ($db->last_error) return false;
		return true;
	}
		
	public function save() {
		global $db, $user;
	
		$title = $db->escape(htmlentities($this->title,ENT_QUOTES,$site->properties['encoding']));			
		$description = $db->escape(htmlentities($this->description, ENT_QUOTES, $site->properties['encoding']));
		$codeRaw = $this->addId('colbox', $this->code);
		$code = $db->escape(htmlentities($codeRaw,ENT_QUOTES,$site->properties['encoding']));			
		$name = $db->escape($this->name);
		//$category = $db->escape(htmlentities($this->category,ENT_QUOTES,$site->properties['encoding']));
		$query = "UPDATE fancy SET 
			title='$title', 
			description = '$description', 
			codetype= '".$this->codetype."',
			code = '$code',
			shared = ".($this->shared+0)."
			WHERE guid = '{$this->guid}'";
		//print "$query<br>";
		$db->query($query);
		if ($db->last_error) return false;
		return true;
	}	
	
	public function drawTypes($sel) {
		//print "dT($sel)<br>\n";
		$html = '';
		//$html .= '<p class="instructions" id="instruction-t1">Type 1</p>'."\n";
		if ($sel=="icon") $style="display: block;";
		$html .= '<p class="instructions" id="instruction-icon" style="'.$style.'">
			This code enables 1 or more blocks of text to slide in from the left when scrolled into view. Each block has an icon, a title and some descriptive text.<br />
			When entering your code please separate each block with a level 2 heading. After this heading the first icon will be extracted and all remaining content will be used for the description.
			</p>
			';
		$html .= '
			<label for="f_codetype">Fancy code</label>
			<select name="codetype" onchange="javascript:togglecode(this.value);">
				<option value="">Select code type</option>
				<!-- <option value="t1">Type 1</option> -->
				<!--<option value="icon"'.($sel=="icon"?' selected="selected"':'').'>Icon link block</option>-->
				<option value="colbox"'.($sel=="colbox"?' selected="selected"':'').'>Collapsible box</option>
			</select>
			<br />
			';
			
		return $html;
		
	}


	// 16/12/2008 Phil Redclift
	// Delete fancy from the database.
	public function delete($guid) {
		global $db;
		$query = "DELETE FROM fancy WHERE guid='$guid'";
		if ($db->query($query)) return true;
		//print "Failed delete($query)<br>\n";
		return false;
	}

	
	public function loadByGUID($guid){
		////// fills the instance of this class with the parameters for the given file
		global $db;

		$query=	"SELECT * FROM fancy WHERE guid='$guid' LIMIT 1";
		//print "$query<br>\n";
		$row = $db->get_row($query);
		if ($row) {
			$this->guid = $row->guid;
			$this->title = $row->title;
			$this->code = $row->code;
			$this->codetype = $row->codetype;
			$this->description = $row->description;
			$this->shared = $row->shared;
			$this->site_id = $row->msv;
		}
		else $this->guid='';
	}
	
	

		
	// 13th Jan 2009 - Phil Redclift
	// 19th Oct 2009 - Phil Redclift
	// Select a list of fancy and return a data array.
	// $cat is an optional category variable, if its set to zero or has a space or nothing, it should show all files
	public function getFancyList($cat=false, $keywords=''){
		global $db, $site;

		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		$query = "SELECT m.guid, m.title, date_format(m.date_created,'%D %M %Y') datemade,
			u.name username, u.full_name fullname, u.email
			FROM fancy m 
			LEFT JOIN users u ON m.user_created=u.id 
			WHERE m.msv=".$site->id." ";
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

	// draw a list of fancy with options to manage them
	public function drawList($p=1, $action=false, $cat=false, $keywords=''){
		global $help, $page;
		
		$this->setPerPage(10);
		$this->setPage($p);	
			
		if($results = $this->getFancyList($cat, $keywords) ){
			foreach($results as $result){
				$html .= '<tr>
	<td><strong>'.$result->title.'</strong></td>
	<td><a href="mailto:'. $result->fullname.'&lt;'.$result->email .'&gt;">'. $result->username .'</a></td>
	<td nowrap>'.$page->languageDate($result->datemade).'</td>
	<td nowrap class="action">
		<a class="edit" '.$help->drawInfoPopup("Edit code").' href="/treeline/fancy/?action=edit&amp;guid='.$result->guid.'">edit code</a>
		<a class="delete" '.$help->drawInfoPopup("Delete code").' href="/treeline/fancy/?action=delete&amp;guid='.$result->guid.'">delete code</a>
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
	<th scope="col">Manage code</th>
	</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
			//$html .= $this->drawPagination("/treeline/files/?action=$action&amp;category=$cat", $this->getTotal(), 10, $page);
			$html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/fancy/?action=$action&amp;category=$cat&amp;q=".$keywords);
			return $html;
		}
		else return 'There are no code blocks to display';
	}
	
	// 13th Jan 2009 - Phil Redclift
	// Show the number of files matched by the current search.
	public function drawTotal(){
		global $page;
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1) $msg = 'There is only 1 matching block in the code library';
		else $msg = 'Showing blocks '.($this->from+1).' - '.$to.' '.$page->drawGeneric('of').' '.$this->getTotal();
		return $msg;
	}

	//Calum Jameson
	//Takes a fancy code entry and adds unique id's to div tags.
	//only used  for collapsible boxes.
	public function addId($codetype, $item)
	{
		//Check if the item is a collapsible box
		if ($codetype ='colbox')
		{
			//Find divs with colbox class
			$dom = new DomDocument();
			$dom->loadHTML($item);
			$xpath = new DOMXPath($dom);
			$results = $xpath->query("//*[@class='colbox']");
			
			//Add unique id's to containing divs.
			$count = 0;
			if ($results->length > 0)
			{
				foreach ($results as $result)
				{
					
					$result->setAttribute("id", "colbox".$count);		
					$count++;
				}
			}
			
			//Add unique id's to short divs
			$results = $xpath->query("//*[@class='short']");
			
			$count = 0;
			if ($results->length > 0)
			{
				foreach ($results as $result)
				{
					$result->setAttribute("id", "short".$count);
					$count++;
				}
			}
			
			//Apply same id to button
/* 			$results = $xpath->query("//*[@class='button']");
			//$results = $xpath->query("//a[text()='Show']");
			$count = 0;
			if ($results->length > 0)
			{
				foreach ($results as $result)
				{
					//$result->setAttribute("id", "button".$count);
					//$result->setAttribute("onClick", "changeBox(" .$count. ")");
					$result->nodeValue = 'show';
					$count++;
				}
			} */
			
			$results = $xpath->query("//a[text()='Show']");
			$count = 0;
			if ($results->length > 0)
			{
				foreach ($results as $result)
				{
					$result->setAttribute("id", "button".$count);
					$result->setAttribute("onClick", "changeBox(" .$count. ")");
					$result->setAttribute("style", "cursor: pointer;");
					$count++;
				}
			}
			
			
			return $dom->saveHTML();
		}
		//if not just return the code unmodified
		else
		{
			return $item;
		}
	}

}


?>