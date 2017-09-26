<?php	

	class News {
		public $config;
		private $totalresults;
	
		private $prev, $next;
		private $type, $guid, $date;
		
		public $items = array();
		private $defimg = "http://aasciences.ac.ke/includes/html/images/news1.jpg";			
		
		private $timespan;
		
		public function __construct($type="news", $guid="", $date='') {
			// This is loaded when the class is created	
			//print "<!-- N::__c($type, $guid, $date) -->\n";
			$this->type = $type;
			$this->guid = $guid;
			$this->setDate($date);
			
			//print "News(".$this->type.", ".$this->guid.")<br>\n";
			return;
		}
		
		public function getNews($guid,$per_page,$current_page=''){
			global $db;
			
			//print "<!-- gN($guid, $per_page, $current_page) type(".$this->type.") -->\n";
			if ($per_page>0) {
				if($current_page == 0 || $current_page == 1 || !$current_page){
					$limit = '0,'.$per_page;
				} 
				else {
					$limit = ($current_page-1)*$per_page.','.$per_page;
				}
			}
			//print "Add limit($limit)<br>\n";
			
			$from = "FROM pages p
				INNER JOIN users u ON u.id = p.user_created
				INNER JOIN content c ON p.guid = c.parent ";
			if ($this->type == "events") {
				$from .= "INNER JOIN events e on e.guid = p.guid ";
			}
			$from .= "WHERE p.parent = '$guid' 
				AND p.offline = 0
				AND c.placeholder = 'content' 
				AND c.revision_id = 0 
				";
			if ($this->timespan) $from .= "AND p.date_published > NOW() - INTERVAL ".$this->timespan." ";
			if ($this->date) {
				if ($this->type=="events") $from .= "AND e.start_date< '".$this->date." 23:59:59' AND e.end_date > '".$this->date." 00:00:00' ";
				else $from .= "AND DATE_FORMAT(p.date_published, '%Y-%m-%d') = '".$this->date."' ";
			}
			
			//print "SELECT COUNT(*) ".$from."<br>\n";
			if ($total = $db->get_var("SELECT COUNT(*) ".$from)) {
				$this->totalresults = $total;
				

				if ($this->type=="blogs" && $this->guid) {
					$limit = '';
				}
			
				$query = "SELECT p.guid, p.title, c.content, p.meta_description, u.full_name AS author, ";
				if ($this->type=="events") {
					$query .= "
					e.location,
					e.start_date,
					date_format(e.start_date, '%D') as day, 
					date_format(e.start_date, '%d') as nday, 
					date_format(e.start_date, '%b') as month, 
					date_format(e.start_date, '%m') as nmon, 
					date_format(e.start_date, '%Y') as year,
					";
					$order = "ORDER BY e.start_date DESC ";
				}
				else {
					$query .= "
					'' AS location, 
					p.date_created, 
					date_format(p.date_created, '%D') as day, 
					date_format(p.date_created, '%d') as nday, 
					date_format(p.date_created, '%b') as month, 
					date_format(p.date_created, '%m') as nmon, 
					date_format(p.date_created, '%Y') as year,
					";
					$order = "ORDER BY p.date_created DESC ";
				}
				$query .= "
					c.revision_date, 
					(
						SELECT GROUP_CONCAT(t.tag)
						FROM tags t 
						INNER JOIN tag_relationships tr ON tr.tag_id = t.id 
						WHERE tr.guid = p.guid 
						AND tr.type_id = 1
						GROUP BY tr.guid
					) AS tagslist,
					(
						SELECT content FROM content c1
						WHERE c1.placeholder='content1'
						AND c1.revision_id = 0
						AND c1.parent = p.guid
					) AS imgcontent
					$from 
					$order
					";
				if ($limit) $query .= "LIMIT ".$limit;
				//print "<!-- $query --> \n";
				$results = $db->get_results($query);
				return $results;
			}
			return '';
		}
		
		public function createSummary($content,$meta_desc=''){
			// create a sumamry beased on a subsection of the content or the meta tag
			global $db;
			
			if($meta_desc){
				$html = $meta_desc;
			}
			else{
				$html = $this->limitWords($content, 50);
			}
			
			return $html;
			
		}
		
		public function setDate($d) {
			if (is_sql_date($d)) $this->date = $d;
		}
		
		public function drawCalendar($parent, $timespan) {
			global $page;
			//print "N::dC($parent, $timespan)<br>\n";
			$this->timespan = $timespan;
			$items = $this->getNews($parent, 0, 0);
			//print "I(".print_r($items, 1).")<br>\n";
			$cnums = array("One", "Two", "Three", "Four", "Five" , "Six");
			$months = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
			$i = 0;
			foreach($items as $item) {
				if ($item->nmon != $thismonth) {
					$thismonth = $item->nmon+0;	
					//print "Add month($thismonth)<br>\n";
					$html .= '</div></div></div>'."\n";
					$html .= '<div class="panel panel-info">
						<div class="panel-heading" role="tab" id="heading'.$cnums[$i].'">
							<h4 class="panel-title"><a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$cnums[$i].'" aria-expanded="true" aria-controls="collapse'.$cnums[$i].'">'.$months[$thismonth].'</a></h4>
						</div>
						<div id="collapse'.$cnums[$i].'" class="panel-collapse collapse '.($i?"":"in").'" role="tabpanel" aria-labelledby="heading'.$cnums[$i].'">
							<div class="panel-body">
							';
				}
				$html.= '<p><a href="'.$page->drawLinkByGUID($item->guid).'">'.$item->title.'</a></p>'."\n";
				$i++;
			}
			$html = substr($html, 18).'</div></div></div>';
			//print "r($html)<br>\n";
			return $html;
		}
		
		public function drawNews($parent, $per_page, $current_page = 1){
			// draw out news summaries, blog style with headings,dates & links
			//print "dn($parent, $per_page, $current_page)<br>";
			global $db, $site, $page, $comment;
			
			if(!$per_page || $per_page == 0){
				$per_page = 5;
			}
			
			if(!$current_page || $current_page == 0){
				$current_page = 1;
			}
			//print "<!-- getNews($parent, $per_page, $current_page) -->\n";
			$items = $this->getNews($parent, $per_page, $current_page);
			
			if($items){
				$html = '';
				$i = 0;
				foreach($items as $item){
					//page (for links)
					$size="small";
					if (!$i) $size = "big";
					
					$img = $this->firstImg($item->imgcontent, $size=="big"?"1600x800":"800x400", false);
					//print "<!-- got img1($img) -->\n";
					if (!$img || $img==$this->defimg) {
						$img = $this->firstImg($item->content, $size=="big"?"1600x800":"800x400", false);
						//print "<!-- got img2($img) -->\n";
					}
					//print "<!-- got img($img) -->\n\n";
					
					$style = ($img?"background: url('/$img') no-repeat; padding-left: 110px;":"");
					
					$page2 = new Page();

					$link = $page2->drawLinkByGUID($item->guid);
					$summary = $this->createSummary($item->content, $item->meta_description);
					
					$this->items['title'] = $item->title;
					$this->items['link'] = $link;
					$this->items['summary'] = $summary;
					
					
					$html .= '<div class="item" style="'.$style.'">'."\n\t";
					$html .= '<h3><a href="'.$link.'">'.$item->title."</a></h3>\n\t";
					$html .= "</p>\n\t";
					$html .= '<div class="summary">'."\n\t";
					$html .= '<p>'.$this->createSummary($item->content, $item->meta_description)."</p>\n\t";
					//$html .= '<p class="readmore"><a href="'.$page->drawLinkByGUID($item->guid).'" title="Continue reading '.$item->title.'">'.$labels['more_link']['txt'].'</a></p>'."\n\t";
					//$html .= "<hr />\n\t";
					$html .= "</div>\n\t";
					$html .= '<p class="date">'.$page->drawLabel('POSTED', "Posted").': '.$item->day.' '.$page->drawLabel(strtoupper($item->month), ucfirst($item->month)).' '.$item->year;
					$html .= "</div>\n\t";
					
					$commentHTML = '';
					if ($this->isBlogs()) {
						$comment->setCount($item->guid);
						if ($comment->count) {
							$commentHTML = '
							<div class="info">
								<p>'.($comment->count+0).'<span> Comment'.($comment->count==1?"":"s").'</span></p>
							</div>
							';
						}
						$tagsHTML = '';
						if ($item->tagslist) {
							$tags = explode(",", $item->tagslist);
							foreach ($tags as $tag) {
								if ($tag) $tagsHTML .= '
									<li><a href="'.$site->link.'tags/'.$tag.'/">'.$tag.'</a></li>
								';
							}
							if ($tagsHTML) $tagsHTML = '
								<div class="info-tags">
									<ul>
										<li><strong>Tags</strong></li>
										'.$tagsHTML.'
									</ul>
								</div>
							';
						}
						$newHTML .= '
						<div class="blog-container">
							<div class="summary">
								<div class="info">
									<p>'.$item->nday.'<span> '.$item->month.' '.$item->year.'</span></p>
								</div>
								'.$commentHTML.'
								'.$tagsHTML.'
							</div>
								
							<a class="content" href="'.$page2->drawLinkByGUID($item->guid).'">
								<div class="image" style="background-image:url(\''.$img. '\');"></div>
								<h3>'.$item->title.'</h3>
								<p>'.$summary.'</p>
								<div class="author-info">
									<div class="author-image valign" style="background:none;display:none;"></div>
									<p class="valign">by <strong>'.$item->author.'</strong><br><span style="display:none;">Job title, Organisation</span></p>
								</div>
							</a>
						</div>
						';
					}
					else {
						$rmlink = "";
						if ($site->id==19) $rmlink = ' <span class="link">read more</span>';
						$newHTML .= '					
						<a class="news-summary '.$size.'-news" href="'.$page2->drawLinkByGUID($item->guid).'">
							<div class="news-image" style="background-image:url(\''.$img.'\');">
							<div class="media-left">'.$item->nday.'<span>'.$item->month.' '.$item->year.'</span></div>
							</div>                    
							<h4>'.$item->title.'</h4>
							<p>'.($summary.$rmlink).'</p>
						</a>
						';
					}
					$i++;
				}
				//$html .= drawPagination($parent,$per_page,$current_page);
				//print "dP(".$this->totalresults.", $per_page, ".$current_page.")<br>\n";
				$pag = drawPagination($this->totalresults, $per_page, $current_page, "");
				$html .= $pag;
				$newHTML .= $pag;
			}
			else{
				$html = '<p>There is no news currently</p>';
				$newHTML = '<p>There is no news currently</p>';
			}

			return $newHTML;
		}
		
		public function drawLatestPanel($count=4) {
			global $db, $siteData, $page, $labels;
			//print "<!-- dlp($count) type(".$this->type.")-->\n";
			$html = $ticker = '';
			// Need to find the first news section for this microsite
			$query = "SELECT p.guid AS news_parent FROM pages p WHERE p.msv=".$siteData->msv." AND p.template=4 ";
			//if ($this->type=="blogs" || $this->type=="events" || $this->type=="publications") 
			$query .= "AND p.name='".$this->type."' ";
			$query .= "ORDER BY date_created LIMIT 1";
			$newsparent = $db->get_var($query);
			//print "<!-- Get news page($query)  parent($newsparent)-->\n";
			$items = $this->getNews($newsparent, $count, 0);
			if($items){
				$i = 0;
				foreach($items as $item){
					
					$link = $page->drawLinkByGUID($item->guid);
					$summary = $this->createSummary($item->content, $item->meta_description);
					
					$this->items[$i]['title'] = $item->title;
					$this->items[$i]['author'] = $item->author;
					$this->items[$i]['link'] = $link;
					$this->items[$i]['date'] = $item->nday." ".$item->nmon." ".$item->year;
					$this->items[$i]['location'] = $item->location;
					$this->items[$i]['summary'] = trim($summary);
					$i++;
					
					$html.='<li class="news"><a href="'.$link.'"><span class="date">'.$item->nday.'/'.$item->nmon.'/'.$item->year.'</span> '.addslashes($item->title)."</a></li>";
				}
			}
			return $html;
		}
		
		public function isBlogs() {
			return $this->type=="blogs";
		}
		
		public function drawLatestTicker() {
			global $db, $siteData, $page, $labels;
			$html = $ticker = '';
			// Need to find the first news section for this microsite
			$query="SELECT guid AS news_parent FROM pages WHERE msv=".$siteData->msv." AND template=4 ORDER BY date_created LIMIT 1";
			$items = $this->getNews($db->get_var($query), 5, 0);
			if($items){
		  	 	$ticker_script = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/behaviour/pause_marquee.js'); 
				foreach($items as $item){
					$ticker.='<a href="'.$page->drawLinkByGUID($item->guid).'" style="padding-right:50px;">'.addslashes($item->title)."</a>";
				}
				$html = '<table cellpadding="0" cellspacing="0"><tr><td>';
//				$html .= '<marquee width="820">'.$html.'</marquee>';
				$html .= str_replace("@@TICKER@@", $ticker, $ticker_script);
				$html .= '</td></tr></table>';
			}
			return $html;
		}
		
		// Collect bit for the content for the main news story
		public function drawMainNews($content, $show) {
			switch($show) {
				case "TITLE" : 
					if (preg_match("/<h3>(.*)<\/h3>/", $content, $reg)) {
						return '<h1>'.$reg[1].'</h1>';
					}
					return "";
					break;
				// Just strip the title from the start of the body
				case "BODY" : 
					$copy = preg_replace("/<h3>(.*?)<\/h3>/", '', $content, 1);
					if (!$copy) $copy="";
					return $copy;
					break;
				default :
					break;
			}
			
		}
		
		public function getPrev() {
			return $this->prev;
		}
		public function getNext() {
			return $this->next;
		}
		
		public function drawMainStory($n) {
			global $db;
			// Collect the story and return stufff.
			$query="select * from pages p left join content c on p.guid=c.parent where p.guid = (select value from config where name='home_story_".$n."') and c.revision_id=0 and c.placeholder='content'";
			if ($story=$db->get_row($query)) {
				$html='<h'.$n.'>'.$story->title.'</h'.$n.'><p>'.$this->limitWords($story->content, 1000)."</p>";			
				return $html;
			}
			else return $query;
			
		}

		private function firstImg($content, $new_size="100x100", $debug=false) {
			$pulledimg = pullImage($content);
			
			if ($debug) print "<!-- fI(content, $new_size, $debug) def(".$this->defimg.") -->\n";
			// Try to get the correctly sized image if it exists
			if (preg_match("/(\d*)x(\d*)\.(.*)$/", $pulledimg, $reg)) {
				$newsimg = str_replace($reg[1].'x'.$reg[2], $new_size, $pulledimg);
				if ($debug) print "<!-- Got($newsimg) from($pulledimg) -->\n";
				if (!file_exists($_SERVER['DOCUMENT_ROOT']."/".$newsimg)) {
					if (file_exists($_SERVER['DOCUMENT_ROOT']."/".$pulledimg)) $newsimg = $pulledimg;
					else {
						$newsimg=$this->defimg;
						//$newsimg = '';
					}
				}
				
			}
			else {
				if ($debug) print "<!-- no img use def -->\n";
				$newsimg = $this->defimg;
			}
			if ($debug) print "<!-- Fi->R($newsimg) -->\n";
			return $newsimg;
		}

		
		public function limitWords($content,$cutoff){
			///strip tags...
			$content = strip_tags(nl2br(html_entity_decode($content))); //this prevents line breaks, images, etc from being counted...
			$wordcount = str_word_count($content);
			$wordindex = str_word_count($content, 1,'.,-\'"\\/?&!�$%^*()_-+=#~{[]}:;|1234567890');
			$wordlimit = ($wordcount<$cutoff) ? $wordcount : $cutoff-1;
			
			if($wordcount > $wordlimit){
				$wordindex = array_slice($wordindex,0,$wordlimit);
				$content = implode(' ',$wordindex).'...';
			}
			return $content;	
		}
	}
	
?>