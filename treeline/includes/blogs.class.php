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
	
	private $page, $author;

	public $abuse, $errmsg;
	
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
		$this->errmsg = array();
		
		/*
		$query .= "SELECT b.id, b.title FROM blogs b 
			LEFT JOIN members m ON b.member_id=m.member_id 
			LEFT JOIN member_access ma ON m.member_id=ma.member_id AND b.msv=ma.msv
			WHERE b.msv=".$this->msv." 
			AND ma.blog_allowed=1
			AND b.suspended=0 
			";

		if ($this->getMemberID()) $query.="AND m.member_id=".$this->getMemberID();
		
		//print "total - $query GROUP BY m.member_id<br>\n";
		$this->allresults = $db->get_results( $query );
		
		if( $this->getTerm() ){
			$query.= "AND (
				concat(
				m.firstname, ' ',m.surname) LIKE '%". $this->getTerm() ."%' 
				OR b.title LIKE '%". $this->getTerm() ."%'
				) ";
		}
		$query.=" GROUP BY m.member_id ";
		//echo '<br />'. nl2br($query) .'<br />';
		//print "$query<br>\n";
		
		$search = $db->get_results( $query );  //// removed -- 'AND p.hidden=0'
		//print_r($search);
		//echo '<br />total: '. $db->num_rows .'<br />';
		$this->setTotal($db->num_rows);	
		$this->setTotalPages($db->num_rows);	
		$db->flush();
		*/
	
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

	public function setMemberID($member_id) {
		$this->member_id = $member_id;
	}
	public function getMemberID() {
		return $this->member_id;
	}

	public function setAuthor($author) {
		$this->author = $author;
	}
	public function getAuthor() {
		return $this->author;
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
		$text = $db->escape(cleanField($_POST['blogtext'], 1, '<p><strong><em><h3><a><span><img><ul><ol><li>'));

		// First check they have entered valid data for this blog
		if (!$title) $message[]="You must enter a blog title";
		if (!$text) $message[]="You must enter some blog text";
		$query = "SELECT id FROM blogs WHERE member_id=".$this->member_id." AND msv=".$this->msv." AND revision_id<1 AND title='".$title."'";
		//print "$query<br>\n";
		if ($db->get_var($query)) $message[]="You already have a blog by this title";

		if (!$this->edit_id) $this->edit_id = $this->create();
		if (!$message) {
			$query="UPDATE blogs SET title='".$title."', name='"._generateName($title)."', text='".$text."' WHERE id=".$this->edit_id;
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
		global $page, $help;
		$html = '';
		if ($results = $this->getBlogsList($page, $cat, $search)) {
		
			foreach ($results as $result) {
	
				if ($result->suspended) $suspendlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_blog_help_unsus", "Un-suspend this blog")).' class="publish" href="/treeline/blogs/?bid='.$result->blog_id.'&amp;action=unsuspend">Un-suspend</a>';
				else $suspendlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_blog_help_suspend", "Suspend this blog")).' class="suspend" href="/treeline/blogs/?bid='.$result->blog_id.'&amp;action=suspend">Suspend</a>';
				$deletelink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_blog_help_delete", "Delete this blog")).' class="delete" href="/treeline/blogs/?bid='.$result->blog_id.'&amp;action=delete">Delete</a>';

				$member_name = $page->drawLabel("tl_blog_err_nomemfound", "not found");
				$member_link = '';
				$member_info = $page->drawLabel("tl_blog_err_nomem", 'There is no member').' '.($result->member_id+0);
				if ($result->author>'') {
					$member_name=$result->author;
					$member_link='<a href="/treeline/members/?id='.$result->member_id.'&amp;action=edit">'.$member_name.'</a>';
					$member_info=$page->drawLabel("tl_blog_action_editmem", "Click to edit member info");
					if (!$result->blog_allowed) $member_info.='<br>'.$page->drawLabel("tl_blog_err_invmem", "This member is not a blogger, this post will be hidden");
				}
				//print "got blog status(".$result->status.") db(".substr(strtolower(str_replace(" ", "-", $result->status)), 0, 10).")<br>\n";
				$html.='<tr>
	<td><a '.$help->drawInfoPopup($page->drawLabel("tl_blog_help_edit", "Edit this post")).' href="/treeline/blogs/?id='.$result->blog_id.'&amp;action=edit">'.$result->title.'</a></td>
	<td nowrap '.$help->drawInfoPopup($member_info).'>'.($member_link?$member_link:$member_name).'</td>
	<td>'.$page->drawGeneric(substr(strtolower(str_replace(" ", "-", $result->status)), 0, 10)).'</td>
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
	<th scope="col">'.$page->drawGeneric("title", 1).'</th>
	<th scope="col" nowrap>'.$page->drawGeneric("author", 1).'</th>
	<th scope="col" nowrap>'.$page->drawGeneric("status",1).'</th>
	<td scope="col">'.$page->drawLabel("tl_blog_action_manage", "Manage blog").'</th>
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
	
	public function doBlogSearch($thispage, $orderBy='date_added', $orderDir='desc', $author=false){
		global $db, $site;
		$this->setPage($thispage);
		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();
						
		$select = "SELECT b.id, b.title, b.date_added,
			date_format(b.date_added, '%j %m %Y') as added,
			date_format(b.date_added, '%d') as day,
			date_format(b.date_added, '%b') as month,
			b.text,
			concat(m.firstname, ' ', m.surname) AS name,
			concat(m.firstname, '-', m.surname) AS blogger_name,
			m.member_id, ma.id as access_id,
			mp.blog_title, mp.blog_name,
			(SELECT count(*) FROM blogs_comments bc WHERE bc.blog_id=b.id AND bc.`status`=0) AS comments
			FROM blogs b 
			LEFT JOIN members m ON b.member_id=m.member_id 
			LEFT JOIN member_access ma ON m.member_id=ma.member_id AND b.msv=ma.msv
			LEFT JOIN member_profile mp ON mp.access_id = ma.id
			WHERE b.msv=".$this->msv." 
			AND ma.blog_allowed=1 
			AND b.suspended=0 
			AND revision_id<1
			";
			
		//if($this->getMemberID()>0) $query.=" AND m.member_id=".$this->getMemberID()." ";
		if ($this->getTerm()) {
			$query = "$select
				AND (
					b.title LIKE '%". $this->getTerm() ."%'
					OR b.text LIKE '%". $this->getTerm() ."%'
				) 
				";
			$query .= "UNION 
				$select
				AND (
					concat(m.firstname, ' ',m.surname) LIKE '%".$this->getTerm()."%' 
					OR mp.blog_title LIKE '%".$this->getTerm()."%'
				)
				";
				if ($author) $query .= "GROUP BY m.member_id ";
		}
		else if ($author) $query = $select." GROUP BY m.member_id ";
		else $query = $select;

		//print "\n Turtle q($query)<br>\n";
		if ($db->get_results($query)) {
			
			$this->setTotal($db->num_rows);
	
			$query .= " ORDER BY $orderBy $orderDir ";
			$query .= " LIMIT ". $this->from .",". $this->getPerPage();
			
			//print "\n RUN \n $query<br>\n";
			//print "<!-- RUN $query -->\n";
			
			//echo '<br /><br />'. nl2br($query) .'<br />';
	
			if($results = $db->get_results( $query ) ){ 
				$db->flush();
				return $results;
			}
		}
		return false;
	}
	



	public function drawBlogResults($p=1, $orderBy='last_updated', $orderDir='desc'){
		global $site, $page, $db;
		//print "dBR($p, $orderBy, $orderDir, page(".$this->getPage()."))<br>\n";
		$htm.='';

		if($results = $this->doBlogSearch($p, $orderBy, $orderDir) ){
		
			foreach($results as $result) {
				//print "Result(".print_r($result, 1).")<br>\n";
				
				$blogimg = pullImage($result->text, true, false);
				//print "Got image($blogimg)<br>\n";
				if (!$blogimg) {
					$blogimg = "/silo/upload/members/mem-".$result->access_id.".jpg";
					if (!file_exists($_SERVER['DOCUMENT_ROOT'].$blogimg)) {
						//print "file(".$_SERVER['DOCUMENT_ROOT'].$blogimg.") does not exist<br>\n";
						$blogimg = "/img/layout/blog-default.png";
					}
				}
				
				// Not a blues
				$sz=array();
				if (file_exists($_SERVER['DOCUMENT_ROOT'].$blogimg)) {
					$sz = getimagesize($_SERVER['DOCUMENT_ROOT'].$blogimg);
				}
				//print "get image($blogimg) size(".print_r($sz, true).")...<br>\n";


				$blogLink = $this->drawLink($result->blog_title, $result->name, $result->title);

				// Just browsing, show latest posts by this blogger
				if (!$this->getTerm()) {
					$html .= '					
                    <div class="blog-post-summary">
                        <div class="post-image" style="background-image:url(\''.$blogimg.'\');"></div>
						<div class="date-box">'.$result->day.'
                            <div class="date-box-month">'.$result->month.'</div>
                        </div>                    
	                    <div class="post-content">
    	                    <h3><a href="'.$blogLink.'">'.$result->title.'</a></h3>
        	                <p>'.$this->createSummary($result->text).'</p>
                            <div class="post-meta">
                                <span class="extras"><span class="glyphicon el-icon-user rgap"></span><a href="'.$this->drawAuthorLink($result->blog_title, $result->name).'">'.$result->name.'</a> </span>
                                <span class="extras"><span class="glyphicon el-icon-comment rgap"></span>'.$result->comments.' Comment'.($result->comments==1?'':'s').'</span>
            	                <a href="'.$blogLink.'" class="btn btn-black read-more-blogs">Read more</a>
                            </div>
                        </div>
                    </div>
					';
					/*				
					$result->blog_name?$result->blog_name:$result->blogger_name
					$result->name
					$this->drawLink($result->blog_title, $result->name, $blogresult->title)
					$blogresult->title
					*/
				}
				// This is a search, just show matching blogs.
				else {
					//print "Search add record<br>\n";
					print "highlight(".$result->title.") term(".$this->getTerm().") = ".(highlightSearchTerms($result->title, $this->getTerm(), 'strong', 'keywords')).")<br>\n";
					$html.='
	<li>
		'.highlightSearchTerms('<a href="'.$this->drawAuthorLink($result->blog_title, $result->name).'">'.($result->blog_title?$result->blog_title:$result->name).'</a>', $this->getTerm(), 'strong', 'keywords').' - 
		'.highlightSearchTerms('<a href="'.$this->drawLink($result->blog_title, $result->name, $result->title).'?keywords='.$this->getTerm().'">'.$result->title.'</a>', $this->getTerm(), 'strong', 'keywords').'
	</li>
					';
				}
			}

			if ($html) {
				$html = '<ul id="serp" class="links">'.$html.'</ul>';
				$html .= drawPagination($this->getTotal(), $this->getPerPage(), $p, "?filter=".$orderBy."&amp;order=".$orderDir);	
			}
			unset($results);
			return $html;
		}
		return false;
	}


	public function drawBlogResultsByAuthor($p=1, $orderBy='last_updated', $orderDir='desc'){
		global $site, $page, $db;
		//print "dBR($p, $orderBy, $orderDir, page(".$this->getPage()."))<br>\n";
		$htm.='';

		if($results = $this->doBlogSearch($p, $orderBy, $orderDir, true) ){
		
			foreach($results as $result) {
				//print_r($result);
				$blogimg = "/silo/upload/members/mem-".$result->access_id.".jpg";
				$sz=array();
				if (!file_exists($_SERVER['DOCUMENT_ROOT'].$blogimg)) {
					print "file(".$_SERVER['DOCUMENT_ROOT'].$blogimg.") does not exist<br>\n";
					$blogimg = "/img/layout/blog-default.png";
				}
				if (file_exists($_SERVER['DOCUMENT_ROOT'].$blogimg)) {
					$sz = getimagesize($_SERVER['DOCUMENT_ROOT'].$blogimg);
					//print "get image size(".print_r($sz, true).")...<br>\n";
				}
				// Just browsing, show latest posts by this blogger
				if (!$this->getTerm()) {
					$html .= '
	<li class = "blogger">
		<div class="blogtop"></div>
		<div class="blogmid">
					';
					if ($sz[0]>0) {
						if ($sz[0]>200) $sz[0]=200;
						$html.='
			<div class="mypic">
				<img src="'.$blogimg.'" style="width:'.$sz[0].'px;" alt="'.$result->name.'" />
			</div>
						';
					}
				
					$html.='
			<ul class="myblogs">
				<li class="myname"><a href="'.$site->link.'blogs/'.($result->blog_name?$result->blog_name:$result->blogger_name).'/">'.$result->name.'</a></li>
					';
					// Get most recent 3 posts for this blogger
					$query = "SELECT b.id, b.title 
						FROM blogs b 
						WHERE member_id = ".$result->member_id." 
						AND b.revision_id < 1
						AND b.suspended >=0
						AND b.msv = ".$this->msv."
						ORDER BY b.revision_id DESC
						LIMIT 3 
						";
	
					//print "$query<br>\n";
					if ($blogresults = $db->get_results($query)) {
						foreach ($blogresults as $blogresult) {
							$html.='			<li><a href="'.$this->drawLink($result->blog_title, $result->name, $blogresult->title).'?keywords='.$this->getTerm().'">'.highlightSearchTerms($blogresult->title, $this->getTerm(), 'strong', 'keywords').'</a></li>';
						}
					}
					$html.='
			</ul>
		</div>
		<div class="blogbot"></div>
	</li>
					';
				}
				// This is a search, just show matching blogs.
				else {
					//print "Search add record<br>\n";
					print "highlight(".$result->title.") term(".$this->getTerm().") = ".(highlightSearchTerms($result->title, $this->getTerm(), 'strong', 'keywords')).")<br>\n";
					$html.='
	<li>
		'.highlightSearchTerms('<a href="'.$this->drawAuthorLink($result->blog_title, $result->name).'">'.($result->blog_title?$result->blog_title:$result->name).'</a>', $this->getTerm(), 'strong', 'keywords').' - 
		'.highlightSearchTerms('<a href="'.$this->drawLink($result->blog_title, $result->name, $result->title).'?keywords='.$this->getTerm().'">'.$result->title.'</a>', $this->getTerm(), 'strong', 'keywords').'
	</li>
					';
				}
			}

			if ($html) {
				$html = '<ul id="serp" class="links">'.$html.'</ul>';
				$html .= drawPagination($this->getTotal(), $this->getPerPage(), $p, "?filter=".$orderBy."&amp;order=".$orderDir);	
			}
			unset($results);
			return $html;
		}
		return false;
	}


	public function createSummary($content){
		//print "Limit($content)<br>\n";
		$html = $this->limitWords($content, 50);
		return $html;
		
	}
	public function limitWords($content,$cutoff){
		///strip tags...
		$content = strip_tags(nl2br(html_entity_decode($content))); //this prevents line breaks, images, etc from being counted...
		$wordcount = str_word_count($content);
		$wordindex = str_word_count($content, 1,'.,-\'"\\/?&!£$%^*()_-+=#~{[]}:;|1234567890');
		$wordlimit = ($wordcount<$cutoff) ? $wordcount : $cutoff-1;
		
		if($wordcount > $wordlimit){
			$wordindex = array_slice($wordindex,0,$wordlimit);
			$content = implode(' ',$wordindex).'...';
		}
		return $content;	
	}

	public function drawMemberList($p=1, $orderBy='last_updated', $orderDir='desc'){
		global $site, $page, $db;
		//print "dBR($p, $orderBy, $orderDir)<br>\n";
		$html.='';
		
		$this->setPage($p);
		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();
						
		$query .= "SELECT b.id, b.title, b.date_added,
			date_format(b.date_added, '%j %m %Y') as added,
			concat(m.firstname, ' ', m.surname) AS name,
			concat(m.firstname, '-', m.surname) AS blogger_name,
			m.member_id, ma.id as access_id,
			mp.blog_name, mp.blog_title
			FROM blogs b 
			LEFT JOIN members m ON b.member_id=m.member_id 
			LEFT JOIN member_access ma ON ma.member_id=m.member_id AND ma.msv=b.msv
			LEFT JOIN member_profile mp ON mp.access_id = ma.id 
			WHERE b.msv=".$this->msv." 
			AND ma.blog_allowed=1 
			AND b.suspended=0 
			AND m.member_id = ".$this->getMemberID()."
			AND revision_id<1
			";
		//print "\nTurtle q($query)<br>\n";
		if ($db->query($query)) {

			$this->setTotal($db->num_rows);
			$query .= " ORDER BY b.". $orderBy ." ". $orderDir;
			$query .= " LIMIT ". $this->from .",". $this->getPerPage();
			//print "\n RUN \n $query<br>\n";
			
			//echo '<br /><br />'. nl2br($query) .'<br />';
	
			if($results = $db->get_results($query)){ 
			
				foreach($results as $result) {
					//print_r($result);
					if (!$blogimg) {
						$blogimg = "/silo/upload/members/mem-".$result->access_id.".jpg";
						$sz=array();
						if (!file_exists($_SERVER['DOCUMENT_ROOT'].$blogimg)) {
							print "file(".$_SERVER['DOCUMENT_ROOT'].$blogimg.") does not exist<br>\n";
							$blogimg = "/img/layout/blog-default.png";
						}
						if (file_exists($_SERVER['DOCUMENT_ROOT'].$blogimg)) {
							$sz = getimagesize($_SERVER['DOCUMENT_ROOT'].$blogimg);
							//print "get image size(".print_r($sz, true).")...<br>\n";
						}
						$html .= '
						<li>
		<div class="blogtop"></div>
		<div class="blogmid">
						';
						if ($sz[0]>0) {
							if ($sz[0]>200) $sz[0]=200;
							$html.='
			<div class="mypic">
				<img src="'.$blogimg.'" style="width:'.$sz[0].'px;" alt="'.$result->name.'" />
			</div>
							';
						}
						$html .= '<ul class="myblogs">'."\n";
						if ($result->blog_title) {
							$html .= '<li class="myname">'.$result->blog_title.'</li>'."\n";
						}
						//$html .= '<li class="myname"><a href="'.$site->link.'blogs/'.($result->blog_name?$result->blog_name:$result->blogger_name).'/">'.$result->name.'</a></li>'."\n";
					}
					$html.='			<li><a href="'.$this->drawLink($result->blog_title, $result->name, $result->title).'?keywords='.$this->getTerm().'">'.$result->title.'</a></li>';
				}
				if ($html) {
					$html.='
			</ul>
		</div>
		<div class="blogbot"></div>
		</li>
		';
					$html = '<ul id="serp" style="list-style:none;" class="links">'.$html.'</ul>';
					$html .= drawPagination($this->getTotal(), $this->getPerPage(), $p, "?filter=".$orderBy."&amp;order=".$orderDir);	
				}
			}
			unset($results);
			return $html;
		}
		return false;
	}



	public function drawTotal(){
		global $page;
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		//print "total(".$this->getTotal().") from(".$this->from.") to($to)<br>\n";
		if (!$this->getTotal()) $msg = $page->drawLabel("blog_nonefound", "No blogs found");
		else if($this->getTotal()==1 && $this->getTerm()) $msg = $page->drawLabel("blog_searchfor", "Your search for").' <strong>'. $this->getTerm() .'</strong> '.$page->drawLabel("returned", "returned").' 1 '.$page->drawLabel("result", "result");
		else $msg = $page->drawLabel("showing", "Showing").' '. ($this->from+1) .' - '. ($to+0) .' '.$page->drawLabel("of", "of").' '. ($this->getTotal()+0);
		return $msg;
	}
	
	
	public function drawAuthorLink($blog, $blogger) {
		global $site;
		$blog1 = _generateName($blog?$blog:$blogger);
		if ($blog1) return $site->link."blogs/".$blog1."/";
		return '';
	}
	public function drawLink($blog, $blogger, $title, $id=0) {
		global $site;
		$blog1 = _generateName($blog?$blog:$blogger);
		if ($blog1 && $title) return $site->link."blogs/".$blog1.'/'._generateName($title)."/";
		else if ($id) return $site->link."blogs/$id/";
		else return $site->link."blogs/";
	}
	
	public function loadBlog($id) {
		global $db, $site;
		$query = "SELECT b.member_id, b.title, b.`text`, 
			IF (b.revision_id=1,'pending',IF(b.suspended=-1,'suspended','live')) AS `status`,
			b.date_added,
			DATE_FORMAT(b.date_added, '%d') AS day,
			DATE_FORMAT(b.date_added, '%b') AS month,
			(SELECT count(*) FROM blogs_comments bc WHERE bc.blog_id=b.id AND bc.`status`=0) as comments,
			concat(m.firstname, ' ', m.surname) as fullname, m.email,
			ma.id as access_id, 
			mp.profile_text, mp.blog_comments, mp.blog_title
			FROM blogs b
			LEFT JOIN members m ON b.member_id=m.member_id
			LEFT JOIN member_access ma on ma.member_id = m.member_id AND ma.msv=".$site->id."
			LEFT JOIN member_profile mp ON mp.access_id = ma.id
			WHERE b.id=".$id."
			AND ma.`status` <> 'X'
			AND suspended>-2";
		//print "$query<br>\n";
		$row = $db->get_row($query);
		if ($row) {
			$this->blog['id']=$id;
			$this->blog['title']=$row->title;
			$this->blog['date']=$row->date_added;
			$this->blog['content']=$row->text;
			$this->blog['status']=$row->status;
			$this->blog['blog_title'] = $row->blog_title;
			$this->blog['blog_day']= $row->day;
			$this->blog['blog_month']=$row->month;
			$this->blog['blog_comments'] = $row->comments;
			$this->blog['blogger_id']=$row->member_id;
			$this->blog['blogger_access_id']=$row->access_id;
			$this->blog['blogger_email'] = $row->email;
			$this->blog['blogger_name']=$row->fullname;
			$this->blog['blogger_profile'] = $row->profile_text;
			$this->blog['allow_comments'] = $row->blog_comments;
			$this->blog['banner_html'] = $row->blog_banner;
			
			$blogimg = "/silo/upload/members/mem-".$row->access_id.".jpg";
			//print "Look for blogger($blogimg)<br>\n";
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$blogimg)) {
				$this->blog['blogger_image'] = $blogimg;
			}
			
			$this->blog['next'] = $this->getNextBlog(-1);
			$this->blog['prev'] = $this->getNextBlog(1);
			
			//print "Loaded blog(".print_r($this->blog, true).")<br>\n";
			return true;
		}
		return false;
		
	}

	public function getNextBlog($dir=1) {
		global $db, $site;	
		$query = "SELECT b.title,
			CONCAT(m.firstname, ' ', m.surname) AS name, mp.blog_title AS blog
			FROM blogs b
			LEFT JOIN members m ON b.member_id=m.member_id
			LEFT JOIN member_access ma on ma.member_id = m.member_id AND ma.msv=".($site->id+0)."
			LEFT JOIN member_profile mp ON mp.access_id = ma.id
			WHERE ma.`status` <> 'X'
			AND b.msv = ".$site->id."
			AND b.suspended>-1
			AND b.revision_id<1
			";
		if ($this->searchAuthorID>0) $select .= "AND m.member_id = ".$this->searchAuthorID." ";			

		if ($dir>0) $query .= "AND b.id > ".$this->blog['id']." ORDER BY b.id ASC ";
		else if ($dir<0) $query .= "AND b.id < ".$this->blog['id']." ORDER BY b.id DESC ";
		
		
		$query .= "LIMIT 1 ";
		//print "$query<br>\n";
		if ($row = $db->get_row($query)) {
			//print "got next bllg(".print_r($row, 1).")<br>\n";
			$next = $this->drawLink($row->blog, $row->name, $row->title);
			//print "next ($next)<br>\n";
			return $next;
		}
		return '';
	}

	public function drawListByBlogger($blogger_id) {
		global $db, $site;
		$query = "SELECT id, title,
			IF (revision_id=1,'pending',IF(suspended=-1,'suspended','live')) AS `status`
			FROM blogs 
			WHERE member_id=$blogger_id AND msv=".$site->id."
			AND suspended>-2
			ORDER BY date_added DESC
			";
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
		return $this->drawList($db->get_results($query), true);
	}
	
	public function drawList($data, $onlylive=false, $format="li") {
		//print "dL(data, live-$onlylive, $format)<br>\n";
		$html='';
		if ($format=="li") { $open="<li>"; $close="</li>"; }
		if(is_array($data)) {
			foreach ($data as $result) {	
				//print "This item is live(".$result->status.")<br>\n";
				if (!$onlylive) $status=$result->status!="live"?' (<span class="'.$result->status.'">'.$result->status.'</span>)':'';
				if (!$onlylive || $onlylive && $result->status=="live") $html.=$open.'<a href="?bid='.$result->id.'&keywords='.$this->getTerm().'">'.$result->title.$status.'</a>'.$close."\n";
			}
		}
		return $html;		
	}
	
	/*
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
	*/
	
	public function addComment() {
		global $db, $captcha;
		if (!$_POST['fullname']) $this->errmsg[] = "You must enter your name";
		if (!$_POST['email']) $this->errmsg[] = "You must enter your email address";
		if ($_POST['email'] && !is_email($_POST['email'])) $this->errmsg[] ="You did not enter a valid email address";
		if (!$_POST['comment']) $this->errmsg[]="You must enter the comment text";
		if (is_object($captcha) && !$captcha->valid) array_splice($this->errmsg, count($this->errmsg), 0, $captcha->errmsg);
		
		if (!count($this->errmsg)) {
			$query = "INSERT INTO 
				blogs_comments (blog_id, comment, member_id, email, name)
				VALUES
				(
				  ".($_POST['bid']+0).", '".$db->escape(censor($_POST['comment']))."', 
				  ".($_SESSION['member_id']+0).", '".$db->escape($_POST['email'])."',
				  '".($db->escape($_POST['fullname']))."'
				)
				";
			//print "$query<br>\n";
			$db->query($query);
			if ($db->last_error) {
				$this->errmsg[]="Failed to add your comment to this blog";
				//$this->errmsg[]=$db->last_error;
			}
			else {
				include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
				include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
				include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
				$data = array("BLOG-TITLE"=>$this->blog['title']);
				$newsletter = new Newsletter();
				//print "send to(".$this->blog['blogger_email'].")<br>\n";
				$newsletter->sendText($this->blog['blogger_email'], "BLOG-COMMENT", $data);
				return true;
			}
		}
		return false;
	}	

	public function vetComments($member_id) {
		global $db, $site;
		//print "vC($member_id)<br>\n";
		$query = "SELECT bc.id as comment_id, bc.comment as comment, bc.name as commentor,
			b.title,
			if (bc.`status`=1,'New',if(bc.`status`=0,'Live','Deleted')) as `status` 
			FROM blogs_comments bc
			LEFT JOIN blogs b
			ON b.id=bc.blog_id
			WHERE b.member_id = $member_id
			AND b.msv=".$site->id."
			ORDER by b.revision_id DESC
			";
		//print "$query<br>\n";
		if ($results = $db->get_results ($query)) {
			?>
            <form method="post">
            <fieldset>
            	<input type="hidden" name="action" value="save-comments" />
                <table border="0" cellpadding="2" cellspacing="0">
                <thead>
                <tr>
                    <th style="text-align:left;">Commentor name</th>
                    <th style="text-align:left;">Comment</th>
                    <th style="text-align:center;">New</th>
                    <th style="text-align:center;">Live</th>
                    <th style="text-align:center;">Deleted</th>
                </tr>
                </thead>
                <tbody>
				<?php
                foreach($results as $result) {
                    //print_r($result);
					if ($curblog!=$result->title) {
						print '<tr style="margin-bottom:10px;"><td colspan="5"><h3>'.$result->title.'</h3></td></tr>';
						$curblog=$result->title;
					}
                    ?>
                        <tr style="margin-bottom:4px;">
                            <td><?=$result->commentor?></td>
                            <td><?=$result->comment?></td>
                            <td style="text-align:center;"><input type="radio" name="stat<?=$result->comment_id?>" <?=($result->status=='New'?'checked="checked"':'')?> disabled="disabled" value="1" /></td>
                            <td style="text-align:center;"><input type="radio" name="stat<?=$result->comment_id?>" <?=($result->status=='Live'?'checked="checked"':'')?> value="0" /></td>
                            <td style="text-align:center;"><input type="radio" name="stat<?=$result->comment_id?>" <?=($result->status=='Deleted'?'checked="checked"':'')?> value="-1" /></td>
                       </tr>
                    <?php
                }
                ?>
                </tbody>
                </table>
            </fieldset>
            <fieldset style="padding-top:15px;">	
            	<label for="f_submit" style="visibility:hidden;">Submit</label>
            	<input id="f_submit" type="submit" value="Update comments" />
            </fieldset>
            </form>
            <?php
		}
	}	

	public function drawComments() {
		global $db;
		if ($this->blog['allow_comments']) {
			$query = "SELECT name, comment, date_format(added, '%D %b %Y') as added_date
				FROM blogs_comments 
				WHERE blog_id=".($this->blog['id']+0)." 
				AND `status`=0 ORDER BY added";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				?>
                <a name="comments"></a>
				<?php
				foreach($results as $result) {
					?>
                    <div class="blog_comment">
                    	<p>Comment by: <strong><?=$result->name?> : <?=$result->added_date?></strong></p>
                    	<p><?=$result->comment?></p>
                    </div>
                    <?php
				}
			}
		}
	}

}

?>