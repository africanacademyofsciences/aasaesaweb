<?php

class Blog {

	public $msv;
	public $member_id;
	
	public $term; //// our search term...
	public $totalresults;
	public $perpage;
	public $thispage;
	public $totalpages;
	public $tag;
	public $from;
	public $to;
	public $type;
	public $resources;
	public $allresults;
	public $filetype;
	
	public $edit_id;
	public $edit_title;
	public $edit_text;
	
	private $page;

	public $abuse;
	
	public function __construct($member_id=0, $term=false){
	
		global $db, $page, $site;
		$this->page = $page; // to prevent any alterations affecting later functions that may rely of this data.
		$this->type = $type;
		
		$this->member_id=$member_id;
		$this->msv = $site->id;
		
		$this->setTerm($term);
		$this->setPage(1);	
		$this->setPerPage(5);
		
		$this->abuse = new Abuse();
		
		$query .= "SELECT b.id, b.title FROM blogs b 
			LEFT JOIN members m ON b.member_id=m.member_id 
			LEFT JOIN member_access ma ON m.member_id=ma.member_id AND b.msv=ma.msv
			WHERE b.msv=".$this->msv." AND b.revision_id<=0 AND ma.blog_allowed=1 
			AND b.suspended=0 ";

		//print "total - $query<br>\n";
		$this->allresults = $db->get_results( $query );
		
		if( $this->getTerm() ){
			if ($this->getTerm()=="member") {
				$query.=" AND m.member_id=".$_GET['id']." ";
			}
			else $query.= " AND (concat(m.firstname, ' ',m.surname) LIKE '%". $this->getTerm() ."%' 
							OR b.title LIKE '%". $this->getTerm() ."%') ";
		}
		//echo '<br />'. nl2br($query) .'<br />';
		
		$search = $db->get_results( $query );  //// removed -- 'AND p.hidden=0'
		//print_r($search);
		//echo '<br />total: '. $db->num_rows .'<br />';
		$this->setTotal($db->num_rows);	
		$this->setTotalPages($db->num_rows);	
		$db->flush();

	
		// If we have a member id then see if they have a currently open blog
		if ($_SESSION['member_id']>0) {
			$query="SELECT ma.blog_allowed as bloggable,
				b.id, b.title, b.text, b.revision_id
				FROM member_access ma
				LEFT OUTER JOIN blogs b ON (b.msv=ma.msv AND b.member_id=ma.member_id)
				WHERE ma.msv=".$this->msv." 
				AND ma.member_id=".$this->member_id." 
				GROUP BY b.id
				ORDER BY b.revision_id DESC, b.date_added 
				LIMIT 1";
			//print "$query<br>\n";
			if ($row = $db->get_row($query)) {
				if ($row->revision_id==1) {
					$this->edit_id=$row->id;
					$this->edit_title = $row->title;
					$this->edit_text = $row->text;
				}
				$this->member_bloggable = $row->bloggable;
				//print "set title to (".$this->edit_title.")<br>\n";
			}
		}
	}

	public function getFileType(){
		return $this->filetype;
	}

	public function setFileType($filetype){
		$this->filetype = $filetype;
	}

	public function getTag(){
		return $this->tag;
	}

	public function setTag($tag){
		$this->tag = $tag;
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

	// 2nd Feb 2009 - Phil Redclift
	// Create a new blog record
	public function create() {
		global $db, $site;
		if (!$_SESSION['member_id']) return 0;
		$query="INSERT INTO blogs (msv, member_id, date_added, title)
			VALUES(".$this->msv.", ".$_SESSION['member_id'].", NOW(), 'New blog title')";
		//print "$query<br>\n";
		if ($db->query($query)) return $db->insert_id;
	}
	
	// 2nd Feb 2009 - Phil REdclift
	// Insert a new blog record if we dont have one on the go then update it.
	public function update() {
		global $db;
		
		$title=$db->escape(cleanField($_POST['blogtitle'], 1, '<p><strong><em><h3><a><span><img>'));
		$text = $db->escape(cleanField($_POST['blogtext'], 1, '<p><strong><em><h3><a><span><img>'));

		// First check they have entered valid data for this blog
		if (!$title) $message[]="You must enter a blog title";
		if (!$text) $message[]="You must enter some blog text";
		$query = "SELECT id FROM blogs WHERE member_id=".$this->member_id." AND msv=".$this->msv." AND revision_id<1 AND title='".$title."'";
		//print "$query<br>\n";
		if ($db->get_var($query)) $message[]="You already have a blog by this title";

		if (!$this->edit_id) $this->edit_id = $this->create();
		if (!$message) {
			$query="UPDATE blogs SET title='".$title."', text='".$text."' WHERE id=".$this->edit_id;
			//print "$query<br>\n";
			$db->query($query);
			if (!$db->last_error) return 0;
			else $message[]="Failed to update blog record";
		}
		return $message;
	}
	
	// 3rd Feb 2009 - Phil Redclft
	// Update
	public function publish() {
		global $db, $site;
		// Check the current dude/site have a publishable blog
		if ($db->get_var("SELECT id FROM blogs WHERE msv=".$this->msv." AND member_id=".$this->member_id." AND revision_id=1")) {
			$db->query("UPDATE blogs SET revision_id=revision_id-1 WHERE msv=".$this->msv." AND member_id=".$this->member_id);
			$db->query("UPDATE blogs SET date_added=NOW() WHERE msv=".$this->msv." AND member_id=".$this->member_id." AND revision_id=0");
			$this->edit_id=0;
			$this->edit_title='';
			$this->edit_text='';
			return true;
		}
		return false;
	}

	// 12th Feb 20009 - Phil Redclift
	// Delete a blog entry permenantly
	public function delete($blog_id) {
		global $db;
		if ($blog_id>0) {
			$query="UPDATE blogs SET suspended=-2 WHERE id=".$blog_id;
			return $db->query($query);
		}
		return false;
	}
	
	public function suspend($blog_id, $suspend=true) {
		global $db;
		if ($blog_id>0) {
			$query = "UPDATE blogs SET suspended=".($suspend?-1:0)." WHERE id=".$blog_id;
			$db->query($query);
			return true;
		}
		return false;
	}

	
	// 12th Feb 2009 - Phil Redclift
	// Collect relevant blogs from the database
	public function getBlogsList($page=1, $cat='', $search='') {
		global $db, $site;
		//print "gBL($page, $cat, $search)<br>\n";
		
		if ($cat=="abuse") $where.="AND abuse=1 ";
		else if ($cat=="author" && $search) $where.="AND concat(m.firstname, ' ', m.surname) LIKE '%$search%' ";
		else if ($cat=="title" && $search) $where.="AND b.title LIKE '%$search%' ";
		
		$order = "ORDER BY b.date_added DESC ";
		
		$query = "SELECT b.id as blog_id, b.title, b.abuse,
			b.member_id, ma.blog_allowed,
			concat(m.firstname, ' ', m.surname) as author, 
			concat(m2.firstname, ' ', m2.surname) as reporter,
			b.suspended,
			IF (b.revision_id=1,'pending',IF(b.suspended=-1,'suspended','live')) AS `status`
			FROM blogs b
			LEFT JOIN members m ON b.member_id=m.member_id
			LEFT OUTER JOIN members m2 on b.abuse=m.member_id
			LEFT OUTER JOIN member_access ma ON m.member_id=ma.member_id AND b.msv=ma.msv
			WHERE b.msv=".$site->msv." 
			AND b.suspended>-2 ";
		//print "$query<br>\n";
		$query.= $where;
		//print "actually get blogs - $query<br>\n";
		$db->query($query);
		$this->setTotal($db->num_rows);	
		$this->setTotalPages($db->num_rows);	
		$db->flush();

		$this->setPage($page);
		//print "PerPage(".$this->getPerPage().") page(".$this->getPage().")<br>\n";
		
		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();
		$limits = "LIMIT ".getQueryLimits($this->getPerPage(), $this->thispage);

		$query.= $order.$limits;
		//print "$query<br>\n";
		
		return $db->get_results($query);
	
	}
	// 12th Feb 2009 - Phil Redclift
	// Draw a list of blogs with optional search 
	public function drawBlogsList($page, $cat, $search) { 
		global $help;
		$html = '';
		if ($results = $this->getBlogsList($page, $cat, $search)) {
		
			foreach ($results as $result) {
	
				if ($result->suspended) $suspendlink = '<a '.$help->drawInfoPopup("Un-suspend this blog").' class="publish" href="/treeline/blogs/?bid='.$result->blog_id.'&amp;action=unsuspend">Un-suspend</a>';
				else $suspendlink = '<a '.$help->drawInfoPopup("Suspend this blog").' class="suspend" href="/treeline/blogs/?bid='.$result->blog_id.'&amp;action=suspend">Suspend</a>';
				$deletelink = '<a '.$help->drawInfoPopup("Delete this blog").' class="delete" href="/treeline/blogs/?bid='.$result->blog_id.'&amp;action=delete">Delete</a>';

				$member_name = "not found";
				$member_link = '';
				$member_info = 'There is no member '.($result->member_id+0);
				if ($result->author>'') {
					$member_name=$result->author;
					$member_link='<a href="/treeline/members/?id='.$result->member_id.'&amp;action=edit">'.$member_name.'</a>';
					$member_info="Click to edit member info";
					if (!$result->blog_allowed) $member_info.='<br>This member is <b>not allowed</b> to blog<br>This post will not appear on the website.';
				}
				
				$html.='<tr>
	<td>'.$result->title.'</td>
	<td nowrap '.$help->drawInfoPopup($member_info).'>'.($member_link?$member_link:$member_name).'</td>
	<td>'.$result->status.'</td>
	<td nowrap class="action">
	'.$suspendlink.'
	'.$deletelink.'
	</td>
<tr>
';

			}
			if ($html) {
				$html = '<table class="tl_list">
<caption>'.getShowingXofX($this->getPerPage(), $this->thispage, sizeof($results), $this->getTotal()).'</caption>
<tr>
	<th scope="col">Blog title</th>
	<th scope="col" nowrap>Author</th>
	<th scope="col" nowrap>Status</th>
	<td scope="col">Manage blog</th>
</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
'.drawPagination($this->getTotal(), $this->getPerPage(), $this->thispage, "?keywords=".$search."&amp;category=".$cat).'
';
			}
			return $html;
			
		}
		return;
			
	}
	
	public function doBlogSearch($thispage,$orderBy='date_added',$orderDir='desc'){
		global $db, $site;
		$this->setPage($thispage);
		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();
						
		$query .= "SELECT b.id, b.title, b.date_added,
			date_format(b.date_added, '%j %m %Y') as added,
			concat(m.firstname, ' ', m.surname) AS name 
			FROM blogs b 
			LEFT JOIN members m ON b.member_id=m.member_id 
			LEFT JOIN member_access ma ON m.member_id=ma.member_id AND b.msv=ma.msv
			WHERE b.msv=".$this->msv." AND ma.blog_allowed=1 AND b.revision_id<=0
			AND b.suspended=0 ";
		//print "\n $query<br>\n";
		$this->allresults = $db->get_results( $query );
		
		if( $this->getTerm() ){
			if ($this->getTerm()=="member") {
				$query.=" AND m.member_id=".$_GET['id']." ";
			}
			else $query.= " AND (concat(m.firstname, ' ',m.surname) LIKE '%". $this->getTerm() ."%' 
							OR b.title LIKE '%". $this->getTerm() ."%') ";
		}
		$query .= " ORDER BY b.". $orderBy ." ". $orderDir;
		$query .= " LIMIT ". $this->from .",". $this->getPerPage();
		
		//print "$query<br>\n";
		//echo '<br /><br />'. nl2br($query) .'<br />';
	
		if($results = $db->get_results( $query ) ){ 
			$db->flush();
			return $results;
		}else{
			return false;
		}

	}
	



	public function drawBlogResults($p=1, $orderBy='last_updated', $orderDir='desc'){
		global $labels, $page;
		//print "dBR($p, $orderBy, $orderDir)<br>\n";
		$htm.='';
		if($results = $this->doBlogSearch($p, $orderBy, $orderDir) ){
		
			foreach($results as $result) {
				//print_r($result);
				$html.='<li>
<span class="title">
	<a href="'.$page->drawLinkByGUID($page->getGUID()).'?bid='.$result->id.'&amp;keywords='.$this->getTerm().'">'.highlightSearchTerms($result->title, $this->getTerm(), 'strong', 'keywords').'</a>
</span>
<ul class="fileinfo">
	<li class="left">Posted</li>
	<li class="right">'.highlightSearchTerms($result->name, $this->getTerm(), 'strong', 'keywords').'</li>
	<li class="left">Date</li>
	<li class="right">'.date('jS F Y', getDateFromTimestamp($result->date_added) ) .'</li>
</ul>
</li>';
			}
			if ($html) {
				$html = '<ul id="serp" class="links">'.$html.'</ul>';
				$html .= $this->getPagination($p, $page->drawLinkByGUID($page->getGUID())."?filter=".$orderBy."&amp;order=".$orderDir);	
			}
			unset($results);
			return $html;
		}
		return false;
	}



	public function drawTotal(){
		global $labels;
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		//print "total(".$this->getTotal().") from(".$this->from.") to($to)<br>\n";
		if($this->getTotal()==1 && $this->getTerm()) $msg = $labels['SEARCHFOR']['txt'].' <strong>'. $this->getTerm() .'</strong> '.$labels['RETURNED']['txt'].' 1 '.$labels['RESULT']['txt'];
		else $msg = $labels['SHOWING']['txt'].' '. ($this->from+1) .' - '. $to .' '.$labels['OF']['txt'].' '. $this->getTotal();

		return $msg;
	}
	

	public function loadBlog($id) {
		global $db, $site;
		$query = "SELECT b.member_id, b.title, b.`text`, 
			IF (b.revision_id=1,'pending',IF(b.suspended=-1,'suspended','live')) AS `status`,
			concat(m.firstname, ' ', m.surname) as fullname
			FROM blogs b
			LEFT JOIN members m ON b.member_id=m.member_id
			WHERE b.id=".$id."
			AND suspended>-2";
		//print "$query<br>\n";
		$row = $db->get_row($query);
		if ($row) {
			$this->blog['id']=$id;
			$this->blog['title']=$row->title;
			$this->blog['content']=$row->text;
			$this->blog['status']=$row->status;
			$this->blog['blogger_id']=$row->member_id;
			$this->blog['blogger_name']=$row->fullname;
			//print_r($this->blog);
			return true;
		}
		return false;
		
	}

	public function drawListByBlogger($blogger_id) {
		global $db, $site;
		$query = "SELECT id, title,
			IF (revision_id=1,'pending',IF(suspended=-1,'suspended','live')) AS `status`
			FROM blogs 
			WHERE member_id=$blogger_id AND msv=".$site->id."
			AND suspended>-2";
		//print "$query<br>\n";
		return $this->drawList($db->get_results($query));
	}
	
	public function drawListByID($blog_id, $exclude=false) {
		global $db, $site;
		//print "dLBID($blog_id, $exclude)<br>\n";
		$query = "SELECT b1.id, b1.title,
			IF(b1.revision_id=1,'pending','live') AS `status` 
			FROM blogs b1
			LEFT JOIN blogs b2 ON b1.member_id=b2.member_id AND b1.msv=b2.msv
			WHERE b1.msv=".$site->id." AND b1.suspended=0	
			AND b2.id=$blog_id ";
		if ($exclude) $query.="AND b1.id <> $blog_id";
		//print "$query<Br>\n";
		return $this->drawList($db->get_results($query));
	}
	
	public function drawList($data, $format="li") {
		$html='';
		if ($format=="li") { $open="<li>"; $close="</li>"; }
		if(is_array($data)) {
			foreach ($data as $result) {
				$status=$result->status!="live"?' (<span class="'.$result->status.'">'.$result->status.'</span>)':'';
				$html.=$open.'<a href="?bid='.$result->id.'&keywords='.$this->getTerm().'">'.$result->title.$status.'</a>'.$close."\n";
			}
		}
		return $html;		
	}
	
	public function getPagination($page,$url=''){
		global $site;
		
		$url.='&amp;keywords='.$this->getTerm();
		if ($_GET['id']) $url.="&amp;id=".$_GET['id'];
		
		$totalpages = $this->getTotalPages();
		if($totalpages>1){
			$html = '<ul class="pagination"> ';
			if( ($totalpages >= 5) && ($page >3)){
				$html .= ' <li><a href="'.$url.'&amp;p=1" title="View the first page of results" class="nextprevious bookend">First</a></li>'."\n";
			}
			if( $page > 1 ){
				$previousclass = 'bookend';
			} else{
				$previousclass = 'inactive bookend';
			}
			$html .= ' <li class="'.$previousclass.'"><a href="'.$url.'&amp;p='. ($page-1) .'" title="View the previous page of results" class="nextprevious bookend">Previous</a> </li>'."\n";
			for($i=1;$i<=$totalpages;$i++){
				if($page == $i){
					$class = ' class="selected"';
				}else{
					$class ='';
				}
				$html .= '<li'.$class.'><a href="'.$url.'&amp;p='. $i .'" title="View page '.$i.' of results">'. $i .'</a></li>'."\n";
			}

			if( ($totalpages > 1) && ($page < $totalpages)){
				$html .= '<li class="bookend"><a href="'.$url.'&amp;p='. ($page+1) .'" title="View the next page of results"  class="nextprevious">Next</a></li>'."\n";
			}

			if( ($totalpages >= 5) && ($page < ($totalpages-1))){
				$html .= '<li class="bookend"><a href="'.$url.'&amp;p='. $totalpages .'"  title="View the last page of results"class="nextprevious">Last</a></li>'."\n";
			}
			$html .= '</ul>';
			return $html;
		}else{
			return false;
		}
	}



}

?>