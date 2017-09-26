<?php

class Search{

	public $term; //// our search term...
	public $totalresults;
	public $perpage;
	public $thispage;
	public $totalpages;
	public $table;
	public $from;
	public $to;
	public $type;
	public $date_range;
	public $resources;

		
	private $page;
	private $page_where, $page_from, $file_where;
	
	// 16/12/2008 Phil Redclift
	// Construct the search object.
	// Initially the object runs the query to establish the number of results 
	// and set up object data.
	public function __construct($table=false,$term=false,$type=false,$resources=0,$date_range='') {
	
		global $db, $page, $site;
		//print "__search($table, $term, $type, $resources, $date_range)<br>\n";
		
		$this->page = $page; // to prevent any alterations affecting later functions that may rely of this data.
		$this->type = $type;
		$this->date_range = $date_range;
		
		if(!$term) return;

		$this->setTerm($term);
		$this->setPage(1);	
		$this->setPerPage(5);
		$this->setTable($table);

		// --- Seriously sad :( ---
		if ($table=="blogs") {
			$this->setBlogGUID($type);
			$type ='pages';
			$table = "content";
			$this->type = $type;
			$this->setTable($table);
		}
		
		if($table == "events") {
			$query = "SELECT p.guid FROM pages p 
				LEFT JOIN events e ON p.guid=e.guid
				WHERE p.msv=".$site->id."
				AND '".$this->getTerm()."'>=e.start_date
				AND '".$this->getTerm()."'<=e.end_date ";
		}
		else if ($table) {

			$this->resources = $resources;
			$this->pageGUID = $page->getGUID();
			// get the homepage of the site.
			$this->sitehome = $page->getSiteID();
			
			if ($date_range) {
				if ($date_range=="0.25") $interval="1 WEEK";
				else $interval = $date_range." MONTH";
			}

			$pagelist = $page->getDescendentsByGUID($this->sitehome);
			$sitepages = "'".$this->sitehome."'";
			foreach($pagelist as $pageitem){
				$sitepages .= ", '".$pageitem."'";
			}
			//$sitepages = substr($sitepages,2);
			//echo '<pre>'. print_r($sitepages,true) .'</pre>';
			
			$placeholders = "'content'";
			$query = '';

			
			//print "Type ($type) blogguid(".$this->blogguid.")<br>\n";
			if( !$type || $type=='pages' || $type=="events" ){

				$this->page_where = "c.placeholder IN ('content')
					AND (c.content LIKE '%". $this->getTerm() ."%' 
						OR p.meta_description LIKE '%". $this->getTerm() ."%' 
						OR p.title LIKE '%". $this->getTerm() ."%'
						OR t.tag LIKE '%".$this->getTerm()."%')
					AND p.msv=". $site->id ." AND c.revision_id=0
					AND p.offline=0 ";
				if ($type=="events") $this->page_where.=" AND p.template=19 ";
				if ($interval) $this->page_where.=" AND p.date_modified > NOW() - INTERVAL $interval ";
				if ($this->blogguid) $this->page_where.=" AND p.parent = '".$this->blogguid."' ";
				
				$this->page_from = "content c
							LEFT JOIN pages p ON c.parent=p.guid
							Left join tag_relationships tr on tr.guid=p.guid
							left join tags t on t.id=tr.tag_id ";
					
				$page_query = "SELECT p.guid FROM ".$this->page_from." WHERE ".$this->page_where;
			}
			
			// Add files search 
			if( !$type || $type=='files' || $type=="media"){		

				$this->file_where = "f.site_id=". $site->id;
				if( $term ){
					$this->file_where .= " AND (fc.content LIKE '%". $this->getTerm() ."%' 
						OR f.description LIKE '%". $this->getTerm() ."%' 
						OR f.title LIKE '%". $this->getTerm() ."%'
						OR t.tag LIKE '%".$this->getTerm()."%')
						";
				}
				if( $resources==1 )	$this->file_where .= " AND resource=1 ";
				if ($type=="files") $this->file_where .= " AND media=0 ";
				if ($type=="media") $this->file_where .= " AND media=1 ";
				if ($interval) $this->file_where.=" AND f.date_created > NOW() - INTERVAL $interval ";

				$file_query = "SELECT f.guid 
					FROM files f
					LEFT OUTER JOIN files_content fc ON f.guid=fc.guid
					LEFT JOIN tag_relationships tr on tr.guid = f.guid
					LEFT JOIN tags t ON t.id=tr.tag_id 
					WHERE ".$this->file_where;
			}
			
			// Build real query;
			$query = $this->buildQuery($page_query, $file_query);
		}
		
		if ($query) {
			//print "<!-- $query -->\n";
			$search = $db->get_results($query);  
			
			//echo '<br />total: '. $db->num_rows .'<br />';
			$this->setTotal($db->num_rows);	
			$this->setTotalPages($db->num_rows);	
			
			$db->flush();
		}
	}


	// 16/12/2008 Comment
	// Build the full query from each section.
	public function buildQuery($pq, $fq, $l='') {
		$query=$pq;
		if ($fq) {
			if ($query) $query.="\n UNION \n";
			$query.=$fq;
		}
		if ($this->blogguid) $query .= "GROUP BY p.guid ";
		$query.=' '.$l;
		//print "QQ($query)<br>\n";
		return $query;
	}


	public function getTable(){
		return $this->table;
	}
	public function setTable($table){
		$this->table = $table;
	}

	public function setBlogGUID($guid) {
		$this->blogguid = $guid;
		//print "S::sBG(".$this->blogguid.")<br>\n";
	}
	public function setPerPage($num){
		$this->perpage = $num;
	}
	public function getPerPage(){
		return $this->perpage;
	}

	public function setTerm($term){
		$term = htmlentities($term,ENT_QUOTES,'utf-8');
		$this->term = $term;
	}
	public function getTerm(){
		return $this->term;
	}

	public function setType($type){
		$this->type = $type;
	}
	public function getType(){
		return $this->type;
	}

	public function setTotal($count){
		$this->totalresults = $count;
	}
	public function getTotal(){
		return $this->totalresults;
	}

	public function getPage(){
		return $this->thispage;
	}
	public function setPage($page){
		$this->thispage = $page;
	}

	public function setTotalPages($total){
		$tp = ceil($this->getTotal()/$this->getPerPage());
		//print "sTP($total) pages($tp)<br>\n";
		$this->totalpages = $tp;
	}
	public function getTotalPages(){
		//print "gTP() return(".$this->totalpages.")<br>\n";
		return $this->totalpages;
	}

	// 16/12/2008 Phil Redclift
	// Rebuild the query and run pulling out the required information
	// returns an array of search results.
	public function doSearch($thispage){
		global $db, $site, $siteID;
		$this->setPage($thispage);
		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();
		$type = $this->type;
		
		//print "<!-- dS($thispage) type($type) -->\n";
		
		if($this->table =="events") {
			$query = "SELECT p.guid, p.title, p.meta_description AS description, 
				'event' AS type, '' AS content, CONCAT(p.name, '/') AS link,
				p.date_published AS last_updated
				FROM pages p 
				LEFT JOIN events e ON p.guid=e.guid
				WHERE p.msv=".$site->id."
				AND '".$this->getTerm()."'>=e.start_date
				AND '".$this->getTerm()."'<=e.end_date ";
			$limits = "ORDER BY e.end_date ASC LIMIT ". $this->from .",". $this->getPerPage();
			$query.=$limits;
		}
		else if ($this->table) {
			$page = $this->page;
			$this->pageGUID = $page->getGUID();
			// get the homepage of the site.
			$this->sitehome = $page->getSiteID();
			
			$placeholders = "'content'";

			$pagelist = $page->getDescendentsByGUID($this->sitehome);
			$sitepages = "'".$this->sitehome."'";
			foreach($pagelist as $pageitem){
				$sitepages .= ", '".$pageitem."'";
			}
			
			if( !$type || $type=='pages' || $type=="events"){
				$page_query = "SELECT p.guid, p.title, p.meta_description as description, IF(p.guid>'','page',null) as `type`, 
							c.content, CONCAT(p.name,'/') link, IF(p.guid>0,'','') as info ,
							IF(p.date_modified>p.date_created, p.date_modified, p.date_created) last_updated
							FROM ".$this->page_from."
							WHERE ".$this->page_where;
			}
			
			if( !$type || $type=='files' || $type=="media"){		
						
				$file_query .= "SELECT f.guid, f.title, f.description, IF(f.guid>'','file',null) as `type`,  
							fc.content, CONCAT(f.name,'.',f.extension) link, f.size as info, 
							IF(date_modified>date_created, date_modified, date_created) last_updated 
							FROM files f
							LEFT OUTER JOIN files_content fc ON f.guid=fc.guid
							LEFT JOIN tag_relationships tr ON tr.guid = f.guid
							LEFT JOIN tags t ON t.id = tr.tag_id
							WHERE ".$this->file_where;
			} 
			
			$limits = " ORDER BY last_updated DESC LIMIT ". $this->from .",". $this->getPerPage();
			$query = $this->buildQuery($page_query, $file_query, $limits);
		}
		
		if ($query) {
			//print "<!-- do search($query) -->\n";
			if($results = $db->get_results( $query ) ){ 
				$db->flush();
				//print "got results (".sizeof($results).")<br>\n";
				return $results;
			}
		}
		return false;
	}

	public function drawResults($thispage=1){
		global $site, $page;

		if($results = $this->doSearch($thispage) ){
			
			foreach($results as $item){
			
				$item->title = str_replace(' & ',' &amp; ',$item->title);

				if( $item->type=='page' || $item->type=="event" ) $link = '<a href="'.$page->drawLinkByGUID($item->guid).'?keywords='. urlencode($this->getTerm()) .'">';
				else $link = '<a href="/silo/files/'. $item->link.'" target="_blank">';
				$itemtitle = highlightSearchTerms(html_entity_decode($item->title,ENT_QUOTES,$site->properties['encoding']), $this->getTerm(), 'strong', 'keywords')."</a>";
				$link.=$itemtitle;
				
				$parent_link='';
				if ($item->parent_guid) $parent_link='<a href="'.$page->drawLinkByGUID($item->parent_guid).'">'.$item->parent_title.'</a> | ';

				$description = ($item->description) ? strip_tags(nl2br($item->description)) : strip_tags(limitWords($item->content, 20));
				$html .= '
<li>
	<h2>'.$link.'</h2>
	<p class="links">'.$this->drawBreadcrumb($item->guid).'</p>
	<p>
		<span class="lastUpdated">(last updated: '. date('H:i \o\n jS F Y', getDateFromTimestamp($item->last_updated) ) .') </span>
		<span class="description">'.highlightSearchTerms(html_entity_decode($description,ENT_QUOTES,$site->properties['encoding']), $this->getTerm(), 'strong', 'keywords').'</span>
	</p>
</li>
';
			}
			$html = '<ul id="serps" class="links">'.$html."</ul>".$this->getPagination($thispage);	
			
			unset($results);

		}
		else{
		
			$html = '<p>Your search for \'<strong>'. $this->getTerm() .'</strong>\' returned no results</p>';
			return false;
		}
		return $html;
	}




	public function drawResourceResults($page=1){
		if($results = $this->doSearch($page) ){
			//require('file.class.php');
			
			$html = '<p>'. $this->drawTotal() ."</p>\n";
			$html .= '<ul id="serps" class="links">'."\n";
			foreach($results as $item){
				$item->title = str_replace(' & ',' &amp; ',$item->title);
				$html .= "<li>";
				$link = '/silo/files/'. $item->link ;

				$html .= '<a href="'. $link .'">';

				$html .= highlightSearchTerms($item->title, $this->getTerm(), 'strong', 'keywords');
				$html .= '</a>';
				$html .= ' <span class="lastUpdated">(last updated: '. date('H:i \o\n jS F Y', getDateFromTimestamp($item->last_updated) ) .')</span>';
				
				// page description
				$description = ($item->description) ? strip_tags(nl2br($item->description)) : strip_tags(limitWords($item->content, 20));
				$html .= '<span class="description">'.highlightSearchTerms($description, $this->getTerm(), 'strong', 'keywords').'</span>'."\n";				
				$html .= ' <span class="breadcrumb">'. $item->link .', '. formatFilesize($item->info, 0) .'</span>';
				$html .= "</li>\n";
			}
			$html .= "</ul>\n".$this->getPagination($page);	
			unset($results);

		}else{
			if($this->getTerm() == ''){
				$html = $this->drawSearchForm();
			}else{
				$html = '<p>Your search for \'<strong>'. $this->getTerm() .'</strong>\' returned no results</p>';
			}
			//$html .= file_get_contents($_SERVER['DOCUMENT_ROOT'] .'/includes/templates/searchForm.inc.php');
			return false;
		}
		return $html;
	}



	

	public function drawTotal(){
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1){
			if( $this->getTerm>'' ){
				$msg = 'Search results for [\'<strong>'. $this->getTerm() .'</strong>\'] only returned 1 result';
			}else{
				$msg = 'Your search only returned 1 result';
			}
		}else{
			$msg = "showing results ". ($this->from+1) .' - '. $to .' of '. $this->getTotal();
			if( $this->term>'' ){
				$msg .= ' in your search for [<strong>'. $this->getTerm() .'</strong>]';
			}
		}
		return $msg;
	}
	

		  public function drawBreadcrumb($guid = false){
		   global $db, $siteID, $page;
		   if (!guid) return false;
		   
		   $html = '';
		   $link = array();
		   
		   // current page
		   $link[] = '<a href="'. $page->drawLinkByGUID($guid) .'?keywords='. urlencode($this->getTerm()).'">'.$page->drawTitleByGUID($guid). '</a>';
		   
		   // add all precedings parents to the array
		   while($parent = $page->getParentByGUID($guid)){
			$link[] = '<a href="'. $page->drawLinkByGUID($parent) .'">'. $page->drawTitleByGUID($parent) .'</a>';
			$guid = $parent;
		   }
		   
		   if($link){
			krsort($link);
		   }
		 
		   $last = sizeof($link);
		   $link[$last-1] = '<a href="'. $page->drawLinkByGUID($siteID) .'">'. $page->drawTitleByGUID($siteID) .'</a>';
		   // this is a microsite so remove the first (duplicate) link
		   if($link[$last-1] == $link[$last-2]){
			unset($link[$last-1]);
		   }
		   
		   $html = join(' | ',$link); // add the separator
		   
		   return $html;  
		  }


	public function didYouMean($max=0) {
		global $db;
	
		$q = $this->getTerm();
		
		$i=0;
		if ($q) {
	
			// no shortest distance found, yet
			$shortest = -1;
			$closest = array();
		
			$query = "SELECT id, word, relation
				FROM dictionary d
				WHERE 1
				AND SOUNDEX('$q') = SOUNDEX(d.word)
				LIMIT 1000;
				";
			//print "run ($query) <br>\n";
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
		
					// calculate the distance between the input word,
					// and the current word
					$lev = levenshtein($q, $result->word);
					//print (++$i)." Got result(".print_r($result, true).") lev($lev)<br>\n";
					
					// check for an exact match
					if ($lev == 0) return 0;
	
					// if this distance is less than the next found shortest
					// distance, OR if a next shortest word has not yet been found
					if ($lev <= $shortest || $shortest < 0) {
						// set the closest match, and shortest distance
						$closest[$lev][]  = $result->word;
						$shortest = $lev;
					}
				}
			}
	
			// No max send them all back
			if (!$max) return $closest[$shortest];
			
			// Just strip the first few elements off the return array
			$r = array();
			for($i=0; $i<$max; $i++) {
				if ($closest[$shortest][$i]) $r[]=$closest[$shortest][$i];
			}
			return $r;
		}
		return -1;	
	}

	public function getPagination($page){
		global $site;
		$totalpages = $this->getTotalPages();
		//print "gP($page) total($totalpages)<br>\n";
		$defurl = '?keywords='. $this->getTerm().'&amp;filter='.$this->type.'&amp;daterange='.$this->date_range;
		//if($totalpages>1) return drawFLpagination($totalpages, $this->getPerPage(), $page, $defurl);
		if($totalpages>1) {
			//print "dP($totalpages, ".$this->getPerPage().", $page, $defurl)<br>\n";
			return drawPagination($this->getTotal(), $this->getPerPage(), $page, $defurl);
		}
		return '';
	}
	public function _getPagination($page){
		$totalpages = $this->getTotalPages();
		
		$start_page=0;
		$defurl = '?keywords='. $this->getTerm().'&amp;filter='.$this->type.'&amp;daterange='.$this->date_range;
		
		if($totalpages>1){
			$html = '<ul class="pagination"> ';
			if( ($totalpages >= 5) && ($page >3)){
				$html .= ' <li><a href="'.$site->link.'search/'.$defurl.'&amp;p=1" title="View the first page of results" class="nextprevious bookend">First</a></li>'."\n";
			}
			if( $page > 1 ){
				$previousclass = 'bookend';
			} else{
				$previousclass = 'inactive bookend';
			}
			$html .= ' <li class="'.$previousclass.'"><a href="'.$site->link.'search/'.$defurl.'&amp;p='. ($page-1) .'" title="View the previous page of results" class="nextprevious bookend">Previous</a> </li>'."\n";
			for($i=1;$i<=$totalpages;$i++){
				if($page == $i){
					$class = ' class="selected"';
				}else{
					$class ='';
				}
				$html .= '<li'.$class.'><a href="'.$site->link.'search/'.$defurl.'&amp;p='. $i .'" title="View page '.$i.' of results">'. ($start_page+$i) .'</a></li>'."\n";
			}

			if( ($totalpages > 1) && ($page < $totalpages)){
				$html .= '<li class="bookend"><a href="'.$site->link.'search/'.$defurl.'&amp;p='. ($page+1) .'" title="View the next page of results"  class="nextprevious">Next</a></li>'."\n";
			}

			if( ($totalpages >= 5) && ($page < ($totalpages-1))){
				$html .= '<li class="last"><a href="'.$site->link.'search/'.$defurl.'&amp;p='. $totalpages .'"  title="View the last page of results"class="nextprevious">Last</a></li>'."\n";
			}
			$html .= '</ul>';
			return $html;
		}else{
			return false;
		}
	}

		public function drawLinkByGUID($guid) {
			// draws a link to the page specified by the GUID
			global $db;
			$data = $db->get_row("SELECT parent,name FROM pages WHERE guid = '$guid'");
			$location = array();
			$html = '';
			if ($data->parent == 0) {
				// if this is the homepage
				$html = "/".$data->name;
			}
			else {
				while ($data->parent != 0) {
					$html = '/'.$data->name.$html;
					$data = $db->get_row("SELECT parent,name FROM pages WHERE guid = '{$data->parent}'");
				}
			}
			return $html . '/';
		}

}

?>