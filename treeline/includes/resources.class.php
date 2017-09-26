<?php

class Resource{

	public $term; //// our search term...
	public $totalresults;
	public $perpage;
	public $thispage;
	public $totalpages;
	public $taglist;
	public $resourcetypes = array();
	public $from;
	public $to;
	public $allresults;
	
	private $page;

	public function __construct($term=false, $taglist='', $meta_desc=false){
		global $db, $page, $site;

		//print "R($term, $taglist, $typelist)<br>\n";		
		$this->page = $page; // to prevent any alterations affecting later functions that may rely of this data.
		
		$this->setTerm($term);
		$this->setTagList($taglist);
		$this->setTypes($meta_desc);
		$this->setPage(1);	
		$this->setPerPage(10);
			

	}
	
	public function setPerPage($num){
		$this->perpage = $num;
	}
	public function getPerPage(){
		return $this->perpage;
	}

	public function setTerm($term){
		$this->term = $term;
	}
	public function getTerm(){
		return $this->term;
	}


	public function setTotal($count){
		$this->totalresults = $count;
	}
	public function getTotal(){
		return $this->totalresults;
	}


	// expects a string in the format tag1,tag2,tag3...
	public function setTagList($taglist){
		$tags = explode(",",$taglist);
		foreach($tags as $tag) {
			if ($tag) $this->taglist .= "'".trim($tag)."',";
		}
		$this->taglist = substr($this->taglist, 0, -1);
	}
	public function getTagList(){
		return $this->taglist;
	}


	public function getPage(){
		return $this->thispage;
	}
	public function setPage($page){
		$this->thispage = $page;
	}


	public function setTotalPages($total){
		$this->totalpages = ceil($this->getTotal()/$this->getPerPage());
	}
	public function getTotalPages(){
		return $this->totalpages;
	}


	public function update($guid, $list, $meta_desc) {
		global $db;
		//print "Resource::u($guid, $list, $meta_desc)<br>\n";
		
		// Check current meta description
		$cur_desc = $meta_desc;
		if (preg_match("/^(.*) \((.*)\)$/", $cur_desc, $reg)) {
			$cur_desc = $reg[1];
		}

		// Generate new list		
		foreach($list as $res_type) {
			if ($res_type>0) $tmp.=$res_type.",";
		}
		
		// update pages
		$query = "update pages set meta_description = '$cur_desc (".substr($tmp, 0, -1).")' WHERE guid='$guid'";
		//print "$query<br>\n";
		if ($db->query($query)) return true;

		return false;
	}
	
	
	
	public function doResourceSearch($thispage, $orderBy='date_created', $orderDir='desc'){
		global $db, $site, $publicationcategories;
		
		$this->setPage($thispage);
		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		$file_query = "SELECT 'file' AS res_type,
			f.name AS filename,
			f.title AS title,
			f.guid AS guid,
			f.description AS description, 
			f.extension as type, 
			CONCAT(f.name,'.',f.extension) AS resource, 
			f.size as size,
			f.date_created,
			fc.title AS category
			FROM files f
			INNER JOIN filecategories fc ON fc.id = f.category
			LEFT JOIN tag_relationships tr ON tr.guid=f.guid
			LEFT JOIN tags t on t.id=tr.tag_id
			WHERE f.site_id=". $site->id."
			".($this->getTerm()?"AND (
				fc.content LIKE '%". $this->getTerm() ."%' OR 
				f.description LIKE '%". $this->getTerm() ."%' OR 
				f.title LIKE '%". $this->getTerm() ."%'
				)":"")."
			".($this->getTagList()?"AND t.tag in (".trim($this->getTagList()).")":"")."
			AND f.resource=1
			AND fc.id IN ($publicationcategories) 
			";				
		
		$image_query = "SELECT 'image' AS res_type,
			i.title AS title,
			i.guid AS guid,
			i.description AS description, 
			i.extension as type, 
			i.filename AS resource, 
			i.size as size,
			i.date_created
			FROM get_formatted_image_list i
			LEFT JOIN tag_relationships tr ON tr.guid=i.guid
			LEFT JOIN tags t on t.id=tr.tag_id
			WHERE i.site_id=". $site->id."
			AND i.width=80 
			AND i.original_size=0
			AND tr.type_id=2
			".($this->getTerm()?"AND (
				i.description LIKE '%". $this->getTerm() ."%' OR 
				i.title LIKE '%". $this->getTerm() ."%'
				)":"")."
			".($this->getTagList()?"AND t.tag in (".trim($this->getTagList()).")":"")."
			AND i.resource=1
			";

		$media_query = "SELECT 'media' AS res_type,
			m.title AS title,
			m.guid AS guid,
			m.description AS description, 
			'' AS type, 
			code AS resource, 
			0 as size,
			m.date_created
			FROM media m
			LEFT JOIN tag_relationships tr ON tr.guid=m.guid
			LEFT JOIN tags t on t.id=tr.tag_id
			WHERE m.msv=". $site->id."
			AND tr.type_id=6
			".($this->getTerm()?"AND (
				m.description LIKE '%". $this->getTerm() ."%' OR 
				m.title LIKE '%". $this->getTerm() ."%'
				)":"")."
			".($this->getTagList()?"AND t.tag in (".trim($this->getTagList()).")":"")."
			AND m.resource=1
			";

		$gallery_query = "SELECT 'gallery' AS res_type,
			g.title AS title,
			g.id AS guid,
			g.description AS description, 
			'' AS type, 
			'' AS resource, 
			0 as size,
			g.date_created
			FROM galleries g
			LEFT JOIN tag_relationships tr ON tr.guid=g.id
			LEFT JOIN tags t on t.id=tr.tag_id
			WHERE g.msv=". $site->id."
			AND tr.type_id=5
			".($this->getTerm()?"AND (
				g.description LIKE '%". $this->getTerm() ."%' OR 
				g.title LIKE '%". $this->getTerm() ."%'
				)":"")."
			".($this->getTagList()?"AND t.tag in (".trim($this->getTagList()).")":"")."
			AND g.resource=1
			";


		// Count total possible results.
		$query = '';
		//if (in_array(2, $this->resourcetypes) && $image_query) $query .= $image_query."UNION ";
		if (in_array(3, $this->resourcetypes) && $file_query) $query .= $file_query."UNION ";
		//if (in_array(5, $this->resourcetypes) && $gallery_query) $query .= $gallery_query."UNION ";
		//if (in_array(6, $this->resourcetypes) && $media_query) $query .= $media_query."UNION ";
		if ($query) {
			$query = substr($query, 0, -6);
			//print "$query<br>\n";		
			if ($db->get_results($query)) {
				$this->setTotal($db->num_rows);	
				$this->setTotalPages($this->getTotal());	
			
				$query .= "ORDER BY ".$orderBy." ".$orderDir." ";
				$limits = "LIMIT ". $this->from .",". $this->getPerPage();
				//print "limit ($limits)<br>\n";
				$query .= $limits;
				//echo nl2br($query) .'<br />';
			
				$results = $db->get_results( $query );
				if ($results) {
					$db->flush();
					return $results;
				}
			}
		}
		return false;
	}
	






	public function drawResourceResults($p=1, $orderBy='last_updated', $orderDir='desc'){
		global $labels, $site, $page;

		if($results = $this->doResourceSearch($p, $orderBy, $orderDir) ){
			
			$doctypes=array("flv", "mp3");	// List any doc types you want to open in the same window
			
			foreach($results as $item){

				$link = $block = '';
				$target=' target="_blank"';
				$blockstyle = "display:none;";

				switch ($item->res_type) {
					case "file": 
						$link = '/download/'. $item->guid; 
						if ($item->type=="mp3" || $item->type=="flv") {
							$link=$site->link."/media-player/?guid=".$item->guid;
						}
						if (in_array($item->type, $doctypes)) $target="_self";	// All things should be opened in a new window???
						break;
					
					case "image": 
						$block = '<img src="/silo/images/'.$item->resource.'" />';
						$blockstyle = "display:block;";
						break;
						
					case "media" :
						$block = html_entity_decode($item->resource);
						$blockstyle = "display:block;";
						break;
						
					case "gallery":
						$gallery = new Gallery('', $item->guid);
						$block = $gallery->drawSlideShow();
						if ($block) $blockstyle = "display:block;";
						break;	
				
				}	
				

				$title = str_replace(' & ',' &amp; ',$item->title);
				$title = highlightSearchTerms($title, $this->getTerm(), 'strong', 'keywords');
				//if ($link && $target) $title = '<a href="'.$link.'"'.$target.'>'.$title.'</a>';
				
				$description = strip_tags(nl2br($item->description)); // don't use 'content' - it's not usable

				$html .= '
                <li class="download '.$this->formatCategory($item->category).'">
                    <a href="'.$link.'" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-lightbulb-outline"></i>
                            <h6>'.$title.'</h6>
                        </div>
                        <div class="meta">
                            <p><i class="ion-ios-cloud-download-outline"></i> '.$item->filename.'.'.$item->type.'</p>
                            <p><i class="ion-ios-information-outline"></i> '.formatFilesize($item->size, 0).'</p>
                        </div>
                        <div class="abstract">
                            '.$description.'
                        </div>
                    </a>
                </li>
				';
				/*			
				$html .= '
<li class="'.$item->res_type.'">
	<p class="title">'.$title.'</span>
	'.($description?'<p class="description">'.$description.'</p>':'').'
	<p class="block" style="'.$blockstyle.'">'.$block.'</p>
	<ul class="info">
		<li class="left">Filetype</li>
		<li class="right">.'.$item->type.'</li>
		<li class="left">'.$page->drawLabel('updated','Date created').'</li>
		<li class="right">'.date('jS F Y', getDateFromTimestamp($item->date_created) ) .'</li>
		<li class="left">Size</li>
		<li class="right">'.formatFilesize($item->size, 0).'</li>
	</ul>
</li>
';	
				*/			
			}
			if ($html) {
				$html = '
					<p class="totals">'. $this->drawTotal() .'</p>
					<ul class="filter-list block" id="filter-container">
						'.$html.'
					</ul>
				';
			}
			else $html = '<p>No publications found matching this criteria.</p>';
			
			// Resource pagination
			if (0) {
				$url="?filter=$orderBy&order=$orderDir";
				$html .= drawPagination($this->getTotal(), $this->getPerPage(), $p, $url);	
			}
			unset($results);

		}
		else {
			return false;
		}
		return $html;
	}


	private function formatCategory($c) {
		if ($c == "Science policy Africa") return "spa";
		return strtolower($c);
	}
	

	public function drawTotal(){
		global $labels;
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		//print "total(".$this->getTotal().") from(".$this->from.") to($to)<br>\n";
		if($this->getTotal()==1 && $this->getTerm()) $msg = $labels['SEARCHFOR']['txt'].' <strong>'. $this->getTerm() .'</strong> '.$labels['RETURNED']['txt'].' 1 '.$labels['RESULT']['txt'];
		else $msg = $labels['SHOWING']['txt'].' '. ($this->from+1) .' - '. $to .' '.$labels['OF']['txt'].' '. $this->getTotal();

		return $msg;
	}
	
	public function setTypes($meta) {
		$types = $this->getResourceTypes($meta);
		if (!$types) $types = "2,3,5,6";
		$this->resourcetypes = explode(",", $types);
		//print "types($types)<br>\n";
	}

	public function getResourceTypes($meta_desc) {
		// Grab resource list from page data
		if (preg_match('/ \((.*)\)$/', $meta_desc, $reg)) {
			return $reg[1];
		}
		return '';
	}

	// This is horrible, should really start a resources table.
	public function drawResourceTypes($name='type', $meta_desc=''){
		global $db;
		//print "dRT($name, $meta_data)<br>\n";
		
		// Get passed resource types (I hate this)
		$curtype = $this->getResourceTypes($meta_desc).",";
		// Do we need to use the posted data
		if ($_POST) {
			$curtype='';
			foreach($_POST[$name] as $tmp) $curtype.=$tmp.",";
		}
		
		$query = "SELECT id, title FROM tag_types WHERE resource=1";
		if ($results = $db->get_results($query)) {
			foreach($results as $result){
				//print "strstr(',$curtype', ',".$result->id.",')<br>\n";
				$selected = (strstr(",".$curtype, ",".$result->id.",")) ? ' selected="selected"' : '';
				$html .= '<option value="'.$result->id.'"'.$selected.'>'.$result->title.'</option>'."\n";
			}
		}
		if ($html) $html='<select name="'.$name.'[]" id="res_type" multiple="multiple">
	<option value=""'.(!$curtype?' selected="selected"':"").'>All resources</option>
	'.$html.'
</select>
';
		else $html = '<span>No resource types</span>';
		return $html;
	}


}

?>