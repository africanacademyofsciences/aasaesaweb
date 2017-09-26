<?php

class Donation {
	
	public $msv;
	public $title, $content, $active, $hidetitle;
	public $count;
	
	public $totalresults;
	public $perpage;
	public $page;
	public $totalpages;
	public $from;
	public $to;	
	

	public function Donation($msv, $did=0) {
	
		$this->msv = $msv;
		if ($did) $this->loadByID($did);
		
	}
	
	
	public function loadByID($did) {
		global $db;
			
		if ($this->msv>0 && $did>0) {
			$query = "Select * from donation WHERE msv=".$this->msv." AND id=$did";
			//print "$query<br>\n";
			
			if ($row = $db->get_row($query)) {	
				$this->id = $row->id;
				$this->title = $row->title;
				$this->amount = $row->amount;
				$this->active = $row->active;
				$this->description = $row->description;
			}
		}
	}

	public function setTotal($count){
		$this->totalresults = $count;
	}
	public function getTotal(){
		return $this->totalresults;
	}
	public function setTotalPages($total){
		$this->totalpages = ceil($this->getTotal()/$this->getPerPage());
	}
	public function getTotalPages(){
		return $this->totalpages;
	}

	public function setPage($page){
		$this->page = $page;
	}
	public function getPage(){
		return $this->page;
	}

	public function setPerPage($num){
		$this->perpage = $num;
	}
	public function getPerPage(){
		return $this->perpage;
	}
	
	// 13th Jan 2009 - Phil Redclift
	// Show the number of files matched by the current search.
	public function drawTotal(){
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1){
			$msg = 'There is only 1 matching donation';
		}
		else{
			$msg = 'Showing donations '. ($this->from+1) .'-'. $to .' of '. $this->getTotal() .' ';
		}
		
		return $msg;
	}
	
	

	public function getList($cat=false, $keywords='', $orderBy=''){
		global $db, $site;

		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		if (!$orderBy) $orderBy = "d.added DESC, d.title ASC";
		
		$query = "SELECT d.id, d.active, d.title, date_format(d.added,'%D %M %Y') AS added
			FROM donation d
			WHERE d.msv=".$site->id." ";
		if ($keywords) $query.="AND d.title LIKE '%$keywords%' ";
		//print "$query<br>\n";
		
		$db->query($query);
		$this->setTotal($db->num_rows);	
		$this->setTotalPages($db->num_rows);	
		$db->flush();
		$query.="ORDER BY $orderBy LIMIT ". $this->from .",". $this->getPerPage();
		//print "$query<br>\n";

		$files = $db->get_results($query);
		if(sizeof($files)>0) return $files;
		return false;
	}

	// ADMIN function
	// draw a list of charts with options to manage them
	public function drawList($p=1, $action=false, $cat=false, $keywords=''){
		global $db, $help, $page;
		$html = '';
		$this->setPerPage(10);
		$this->setPage($p);	
			
		if($results = $this->getList($cat, $keywords) ){
			$previewGUID = $db->get_var("SELECT guid FROM pages WHERE name = 'make-a-donation' LIMIT 1");
			foreach($results as $result){
				$html .= '<tr>
	<td class="'.($result->active?'':'disabled').'"><strong>'.$result->title.'</strong></td>
	<td nowrap>'.$result->added.'</td>
	<td nowrap class="action">
		<a class="preview" '.$help->drawInfoPopup("View donation").' target="_blank" href="'.$page->drawLinkByGUID($previewGUID).'?mode=preview&d='.$result->id.'">preview donation</a>
		<a class="edit" '.$help->drawInfoPopup("Edit donation").' href="/treeline/store/donation.php?action=edit&amp;did='.$result->id.'">edit donation</a>
		<a class="delete" '.$help->drawInfoPopup("Delete donation").' href="/treeline/store/donation.php?action=delete&amp;did='.$result->id.'">delete donation</a>
	</td>
</tr>
';
			}
			$html = '<table class="tl_list">
<caption>'.$this->drawTotal() .'</caption>
<thead>
	<tr>
	<th scope="col">Title</th>
	<th scope="col">Created On</th>
	<th scope="col">Manage donation</th>
	</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
			//$html .= $this->drawPagination("/treeline/files/?action=$action&amp;category=$cat", $this->getTotal(), 10, $page);
			$html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/store/donation.php?action=$action&amp;q=".$keywords);
			return $html;
		}
		else {
			return 'There are no donations to display';
		}
	}


	public function formatLink($link) {
		if (strtolower(substr($link, 0, 3))=="www") $link = "http://".$link;
		$link = strtolower($link);
		return $link;
	}
					
	
	public function draw() {
	}
	
}


?>