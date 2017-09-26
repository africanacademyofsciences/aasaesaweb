<?php

class Campaign {
	
	public $msv;
	public $title, $active, $description, $target;
	public $count;
	
	public $totalresults;
	public $perpage;
	public $page;
	public $totalpages;
	public $from;
	public $to;	
	

	public function Campaign($msv, $cid=0) {
	
		$this->msv = $msv;
		if ($cid) $this->loadByID($cid);
		
	}
	
	
	public function loadByID($cid) {
		global $db;
			
		if ($this->msv>0 && $cid>0) {
			$query = "Select * from store_donation_campaign WHERE msv=".$this->msv." AND id=$cid";
			//print "$query<br>\n";
			if ($row = $db->get_row($query)) {	
				$this->id = $row->id;
				$this->title = $row->title;
				$this->active = $row->active;
				$this->description = $row->description;
				$this->target = $row->target;
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
			$msg = 'There is only 1 matching campaign';
		}
		else{
			$msg = 'Showing campaign '. ($this->from+1) .'-'. $to .' of '. $this->getTotal() .' ';
		}
		
		return $msg;
	}
	
	

	public function getList($cat=false, $keywords='', $orderBy=''){
		global $db, $site;

		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		if (!$orderBy) $orderBy = "c.added DESC, c.title ASC";
		
		$query = "SELECT c.id, c.active, c.title, date_format(c.added,'%D %M %Y') AS added
			FROM store_donation_campaign c
			WHERE c.msv=".$site->id." ";
		if ($keywords) $query.="AND c.title LIKE '%$keywords%' ";
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
			foreach($results as $result){
				$html .= '<tr>
	<td class="'.($result->active?'':'disabled').'"><strong>'.$result->title.'</strong></td>
	<td nowrap>'.$result->added.'</td>
	<td nowrap class="action">
		<a class="edit" '.$help->drawInfoPopup("Edit campaign").' href="/treeline/store/campaign.php?action=edit&amp;cid='.$result->id.'">edit campaign</a>
		<a class="delete" '.$help->drawInfoPopup("Delete campaign").' href="/treeline/store/campaign.php?action=delete&amp;cid='.$result->id.'">delete campaign</a>
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
	<th scope="col">Manage campaign</th>
	</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
			//$html .= $this->drawPagination("/treeline/files/?action=$action&amp;category=$cat", $this->getTotal(), 10, $page);
			$html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/store/campaign.php?action=$action&amp;q=".$keywords);
			return $html;
		}
		else {
			return 'There are no campaigns to display';
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