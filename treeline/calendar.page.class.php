<?php	

	class Calendar {

		public $config;
		public $events = false;
		public $year, $month;
		
		public $months = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		public $mdays = array(0, 31, 0, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		public $wday = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		
		private $totalresults;
	
		public function __construct($is_events, $year, $month) {
			// This is loaded when the class is created	
			$this->events = $is_events;
			$this->year = $year;
			$this->month = $month;

			if ($this->events) print "n::__c($is_events, $year, $month)<br>\n";
			return;
		}
		
		public function getNews($guid,$per_page,$current_page=''){
			global $db, $site;
			print "<!-- site(".$site->id.") gN($guid, $per_page, $current_page) -->\n";
			//print "date(".$this->month.", ".$this->year.")<br>\n";
			
			if($current_page == 0 || $current_page == 1 || !$current_page) $limit = '0,'.$per_page;
			else $limit = ($current_page-1)*$per_page.','.$per_page;
			
			$order_date = "p.date_created DESC";
			
			$query = "FROM pages p
				INNER JOIN content c ON p.guid = c.parent
				INNER JOIN users u ON u.id = p.user_created
				LEFT JOIN events e ON e.guid = p.guid
				WHERE p.offline = 0
				AND p.hidden = 0
				AND c.placeholder = 'content' AND c.revision_id = 0 
				AND msv=".($site->id+0)." 
				";
			if ($this->events) $query.="
				AND p.template = 19
				AND e.start_date < '".$this->year."-".$this->month."-01' + INTERVAL 1 MONTH 
				AND e.end_date > '".$this->year."-".$this->month."-01' 
				";
			else if ($site->id==4 && $per_page==3) ;	// Canada shows all latest content in this block
			else $query .= "AND p.parent = '$guid' ";

			//print "<!-- Get total news ($query) -->\n";
			
			//if ($this->events) print "SELECT COUNT(*) $query<br>\n";
			//print "SELECT COUNT(*) ".$query."<br>\n";
			
			if ($total = $db->get_var("SELECT COUNT(*) ".$query)) {
				$this->totalresults = $total;
				if ($this->totalresults > 0) {
					//print "Got ".$this->totalresults." results<br>\n";
					$select = "SELECT p.guid, p.title, c.content, p.meta_description, 
						p.date_created, 
						IF (p.date_effective<>'0000-00-00 00:00:00' AND p.date_effective IS NOT NULL,p.date_effective,p.date_created) AS order_date,
						DATE_FORMAT(IF(p.date_effective<>'0000-00-00 00:00:00' AND p.date_effective IS NOT NULL,p.date_effective,p.date_created), '%d %M %Y') AS nice_order_date,
						date_format(p.date_created, '%D') as `day`, 
						date_format(p.date_created, '%d') as nday, 
						date_format(p.date_created, '%w') as wday,
						date_format(p.date_created, '%b') as month, 
						date_format(p.date_created, '%m') as nmon, 
						date_format(p.date_created, '%Y') as year,
						p.background_img,
						c.revision_date,
						u.full_name ";
					if ($this->events) $select.=",
						IF(date_format(e.start_date, '%m%Y')='".str_pad($this->month, 2, "0", STR_PAD_LEFT).$this->year."',date_format(e.start_date, '%d'),0) AS esday,
						IF(date_format(e.end_date, '%m%Y')='".str_pad($this->month, 2, "0", STR_PAD_LEFT).$this->year."',date_format(e.end_date, '%d'),0) AS eeday 
						";
					$query = $select." ".$query." ORDER BY sticky_date ASC, order_date DESC LIMIT $limit ";
					if ($site->id==4) print "<!-- Get news ($query) -->\n";
					//if ($this->events) print "$query<br> \n";
					
					$results = $db->get_results($query);
					return $results;
				}
			}
			return '';
		}
		
		public function getDefImg($size) {
			return "/img/layout/def-".$size.".png";
		}

		
		public function drawNews($parent, $per_page, $current_page = 1){
			global $site, $db, $page;
			//print "dn($parent, $per_page, $current_page)<br>";
			//$page = new Page();

			if(!$per_page || $per_page == 0){
				$per_page = 5;
			}
			
			if(!$current_page || $current_page == 0){
				$current_page = 1;
			}
			//print "getNews($parent, $per_page, $current_page)<br>";
			$items = $this->getNews($parent, $per_page, $current_page);
			if($items){
				$html = ''; $i=0;
				$sizes = array("589x0","295x0","295x0");
				
				$dim = $this->getDaysInMonth($this->month, $this->year);
				$last_month = $this->month - 1;
				$last_year = $this->year;
				if ($last_month<1) {
					$last_month = 12;
					$last_year = $this->year - 1;
				}
				$dilm = $this->getdaysInMonth($last_month, $last_year);
				
				foreach($items as $item){

					if (!$this->events) {
						$sitePageBg = array(4);
						
						$newslink = $page->drawLinkByGUID($item->guid);
						
						$style = '';
						
						$img = $this->firstImg($item->content, $sizes[$i]?$sizes[$i]:"100x100");
						if (!$img) if (in_array($site->id, $sitePageBg)) $img = $this->firstImg($item->background_img, $sizes[$i]?$sizes[$i]:"100x100");
						if (!$img) $img = $this->getDefImg($sizes[$i]?$sizes[$i]:"100x100");

						//print "<!-- $i - size[".$sizes[$i]."] img($img) -->\n";
						if ($img && $i>2) $style = "background-image: url('$img')";
						
						$html .= '<div class="item item-'.($i).'" style="'.$style.'">'."\n\t";
						if ($i>0 && $i<3) $html .= '<img src="'.$img.'" class="newsimg" />';
						$html .= '
	<div class="detail-block">
		<h3><a href="'.$newslink.'">'.$item->title.'</a></h3>
		'.(!$i?'<img src="'.$img.'" class="newsimg" />':'').'
		<p class="authordate">
			<!-- <span class="date">'.$page->drawLabel('PUBLISHED', "Published").': '.$item->nday.' '.$page->drawLabel(strtoupper($item->month), ucfirst($item->month)).' '.$item->year.'</span> -->
			<span class="date">'.$page->drawLabel('PUBLISHED', "Published").': '.$item->nice_order_date.'</span>
			<span class="author">'.$item->full_name.'</span>
		</p>
	</div>
	<p class="summary">'.createSummary($item->content, $item->meta_description).' <a href="'.$newslink.'">Read more</a></p>
</div>
';
					}
					else {
						$newslink = $page->drawMSVLinkByGUID($item->guid);
						$this->ev[$i]['link'] = $newslink;
						$this->ev[$i]['start'] = $item->esday+0;
						$this->ev[$i]['end'] = $item->eeday+0;
						$this->ev[$i]['guid'] = $item->guid;
						$this->ev[$i]['title'] = $item->title;
						if (!$this->ev[$i]['img']) {
							$this->ev[$i]['img'] = $this->firstImg($item->content, "141x50");
							if ($this->ev[$i]['img']) {
								if (file_exists($_SERVER['DOCUMENT_ROOT'].$this->ev[$i]['img'])) {
									$this->ev[$i]['showimg']=true;
								}
							}
						}
					}
					$i++;
				}
				// Show pagination for news pages.
				if (!$this->events) {
					$html .= drawPagination($this->totalresults, $per_page, $current_page, "");
				}
				else {
					//print "Got events (".print_r($this->ev, 1).")<br>\n";
					
					$query = "SELECT date_format('".$this->year."-".str_pad($this->month, 2, "0", STR_PAD_LEFT)."-01', '%w') FROM sites LIMIT 1";
					$first = $db->get_var($query)-0;
					//print "Got dim($dim) dilm($dilm) first=$first from q($query)<br>\n";
					
					$earlys = $first - 1;
					if ($earlys < 0) $earlys = 6;
					$chtml = '';
					$wday = 1;
					for ($i=$earlys; $i>0; $i--) {
						if ($wday==6) $chtml.='<div class="wend">';
						$chtml .= '<div class="day '.$this->wday[$wday].' early"><p class="enday">'.($dilm-($i-1)).'</p></div>';
						if ($wday==7) $chtml.='</div>';
						$wday++;
					}
					for ($i=0; $i<$dim; $i++) {
						$actual_day = $i+1;
						if ($first==6) $chtml .= '<div class="wend">';
						
						
						$dayhtml = $daycount = $imgshown = $daymsg = $firstlink = '';
						foreach ($this->ev as $ind => $ev) {
							//print "Its the $actual_day day of the current month<br>\n";
							//print "I have an event starting the ".$ev['start']." and ending the ".$ev['end']."<br>\n";
							$eventlink = $ev['link'];
							//print "got link($eventlink) from (".$ev['link'].")<br>\n";
							$eventlink .= "?d=".$actual_day."&amp;m=".$this->month."&amp;y=".$this->year;
							//print "got link($eventlink)<br>\n";
							if (!$firstlink) $firstlink = $eventlink;
							
							if ($ev[start]<=$actual_day && ($ev['end']>=$actual_day || !$ev['end']) ) {
								//$dayhtml .= '<p class="event">event ('.$ev['guid'].') on this day</p>';
								$daycount++;

								if (!$imgshown) {
									//$daymsg .= 'ins f('.$first.')';
									if ($ev['showimg'] && $first < 6) {
										$dayhtml.='<p class="image"><a href="'.$eventlink.'"><img src="'.$ev['img'].'" /></a></p>';
										$this->ev[$ind]['showimg']=false;
										$imgshown=true;
									}
								}
								
								$dayhtml .= '<p class="title"><a href="'.$eventlink.'">'.$ev['title'].'</a></p>';
							}
							
						}

						$chtml .= '
<div class="day '.$this->wday[$first].'">
	<p class="nday">
		<span class="day">'.$actual_day.'</span>
		<span class="count"><a href="'.$firstlink.'">'.($daycount?($daycount.' event'.($daycount>1?"s":"")." today"):"").'</a></span>
	</p>
	'.($daymsg?'<p>'.$daymsg.'</p>':'').'
	'.$dayhtml.'
</div>
';
						if ($first==7 || !$first) {
							$chtml .= '</div>';
							$first = 0;
						}
						/*
						if (!$first) {
							$chtml .= '</tr><tr>';
						}
						*/
						$first++;
					}
					//$first--;
					//print "Ended the month on day($first)<br>\n";
					if ($first>1) {
						for ($i=$first; $i<=7; $i++) {
							if ($i==6) $chtml.='<div class="wend">';
							$chtml .= '<div class="day '.$this->wday[$i].' late"><p class="lnday">'.(($i-$first)+1).'</p></div>';
							if ($i==7) $chtml.='</div>';
						}
					}
					//$chtml .= '</tr></table>';
					echo '<div class="calendar">'.$chtml.'</div>';
				}
			}
			else {
				if ($this->events) $html = '<p>There are no events planned during this month</p>';
				else $html = '<p>There is no news currently</p>';
			}
			
			return $html;
		}

		public function listEventMonths() {
			global $db;
			$curMonthStart = date("Y-m-01", time());
			$query = "SELECT 
				date_format(e.start_date, '%Y') as sy,
				date_format(e.start_date, '%m') as sm,
				date_format(e.end_date, '%Y') as ey,
				date_format(e.end_date, '%m') as em
				FROM pages p
				INNER JOIN events e ON e.guid = p.guid
				WHERE p.offline = 0
				AND p.date_published <> '0000-00-00 00:00:00'
				AND e.end_date > '$curMonthStart'
				ORDER BY p.date_created ASC
				";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
					for($y = $result->sy; $y<=$result->ey; $y++) {
						//print "add year($y)<br>\n";
						if ($y == $result->sy) {
							$start = $result->sm+0;
							$end = 12;
						}
						else if ($y == $result->ey) {
							$start = 1;
							$end = $result->em+0;
						}
						else {
							$start = 1;
							$end = 12;
						}
						//print "From $start to $end<br>\n";
						for ($i=$start;$i<=$end;$i++) {
							$m = str_pad($i, 2, "0", STR_PAD_LEFT);
							$months[$y][$m] = $this->months[($m+0)]." ".$y;
						}
					}
				}
				//print "Got months(".print_r($months, 1).")<br>\n";
				ksort($months);
				//print "Got sorted months(".print_r($months, 1).")<br>\n";
				for($i=0; $i<count($months); $i++) {
					ksort($months[$i]);
				}
				//print "Got totally sorted months(".print_r($months, 1).")<br>\n";
				
				foreach ($months as $y=>$a) {
					foreach ($a as $month=>$ym) {
						$m = str_pad($month, 2, "0", STR_PAD_LEFT);
						//print "Got ym(".($y.$m).") this(".($this->year.str_pad($this->month, 2, "0", STR_PAD_LEFT)).")<br>\n";
						$html .= '<option value = "'.($y.$m).'"'.(($y.$m)==($this->year.str_pad($this->month, 2, "0", STR_PAD_LEFT))?' selected="selected"':"").'>'.$ym.'</option>';
					}
				}
			}
			return $html;
		}
	
		public function getDaysInMonth($m, $y) {	
			if ($y%4==0) {
				//print "Its a leap year, there are 29 days in feb<br>\n";
				$this->mdays[2]=29;
			}
			else {
				//print "$y is not a leap year, there are 28 days in feb<br>\n";
				$this->mdays[2]=28;
			}
			$r = $this->mdays[$m];
			//print "gDIM($m, $y) = $r<br>\n";
			return $r;
		}
				
		public function drawLatestPanel($count = 2) {
			global $db, $siteData, $page, $labels;
			$html = $ticker = '';
			// Need to find the first news section for this microsite
			$query="SELECT guid AS news_parent FROM pages 
				WHERE msv=".$siteData->msv." AND template=4 
				ORDER BY date_created LIMIT 1
				";
			//print "$query<br>\n";
			$items = $this->getNews($db->get_var($query), $count, 0);
			if($items){
				foreach($items as $item){
					$newimg = '';
					$img = pullImage($item->content);
					if (preg_match("/_(\d*)x(\d*)\./", $img, $reg)) $newimg = str_replace($reg[1]."x".$reg[2], "120x90", $img);
					//print "<!-- Got img($img) Check if (".($_SERVER['DOCUMENT_ROOT'].$newimg).") exists -->\n";
					if ($img && file_exists($_SERVER['DOCUMENT_ROOT'].$newimg)) ;
					else {
						//print "<!-- look for image in (".$item->background_img.") -->\n";
						$newimg = $this->firstImg($item->background_img, "120x90");
					}
					if (!file_exists($_SERVER['DOCUMENT_ROOT'].$newimg)) $newimg='';
					$itemHREF = $page->drawLinkByGUID($item->guid);
					$html.='
<li class="news">
	<div class="item">
		<div class="image" style="display:'.($newimg?"block":"none").';">
			<a href="'.$itemHREF.'"><img src="'.$newimg.'" /></a>
		</div>
		<div class="summary" style="width:'.($newimg?"429":"589").'px;">
			<h3 class="title"><a href="'.$itemHREF.'">'.$item->title.'</a></h3>
			<p class="summary">'.createSummary($item->content, '', 20).'</p>
			<!-- <p class="date">'.ucfirst($item->month).' '.$item->nday.' '.$item->year.'</p> -->
			<p class="date">'.$item->nice_order_date.'</p>
		</div>
	</div>
</li>
';
				}
			}
			return '<ul id="latest-news-summary">'.$html.'</ul>';
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

		public function firstImg($content, $new_size="100x100") {
			global $db;
			$newsimg = pullImage($content);
			
			//print "fI($new_size) Found image($newsimg)<br>\n";
			// Try to get the correctly sized image if it exists
			if (preg_match("/(.*)\/(.*?)\_(\d*)x(\d*)\.(.*)$/", $newsimg, $reg)) {
				if (preg_match("/(\d*)x(\d*)$/", $new_size, $reg2)) {
					//print "Look for img at ".$reg2[1]."x".$reg2[2]."<br>\n";
					if ($reg2[1]>0 && $reg2[2]>0) {
						// Full image size spec sent
						$newsimg = str_replace($reg[3].'x'.$reg[4], $new_size, $newsimg);
					}
					else if ($reg2[1]>0 && $reg2[2]==0) {
						// Ok we need a specified width image
						$query = "SELECT filename FROM images i 
							INNER JOIN images_sizes isz ON isz.guid = i.guid
							WHERE i.name = '".$reg[2]."'
							AND isz.width = ".$reg2[1];
						//print "$query<br>\n";
						if ($newsimg = $db->get_var($query)) {
							$newsimg = $reg[1]."/".$newsimg;
						}
						//else print "Failed to find image in DB<br>\n";
					}
				}
			}
			
			if (!file_exists($_SERVER['DOCUMENT_ROOT']."/".$newsimg)) {
				//print "file($newsimg) does not exist<br>\n";
				$newsimg="/img/layout/news-default.gif";
				$newsimg = '';
			}
			//print "send back($newsimg)<br>\n";
			return $newsimg;
		}

		
	}
	
?>