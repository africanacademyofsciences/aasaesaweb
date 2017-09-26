<?php	
	class Menu {
		
		public $html;
		public $items=array();
		

		// 16/12/2008 Comment
		// Primary menu is the main menu for the site. 
		// It displays all sections configured in Treeline
		// It usually appears across the top of all pages
		public function drawPrimary($guid) {
			global $db, $mode, $page, $site, $location;
			$i = 0;
			$html = '';
			$use_homepage=true;

			// Get information about all children of the homepage
			$query = "SELECT p.guid, p.name, p.title
				FROM pages p
				LEFT JOIN sites_versions msv ON p.msv=msv.msv
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE p.hidden=0 and p.offline=0 AND pt.template_type=3
				AND p.date_published IS NOT NULL AND p.parent=".$site->id."
				AND p.msv=".$site->id." AND p.guid!=p.msv
				GROUP BY p.guid ORDER BY sort_order ASC";
			//print "<!-- $query -->\n";
			$items = $db->get_results($query);
			
			if( is_array($items) && count($items)>0 ){
			
				$selected = false;
				
				// Load details of the current page [should we use a global here?]
				$tmp_page = new Page();
				$parentGUID = $tmp_page->getParent();
				
				foreach ($items as $item) {
					$i++;
					
					// Position of this element in the list
					$position='';
					if(sizeof($items) == $i) $position = 'last';
					else if($i==1 && !$use_homepage) $position = 'first';

					// Class for selected
					$class="";
					//print "<!-- item->guid(".$item->guid.") guid($guid) parent($parentGUID) name(".$item->name.") loc[0](".$location[0].") --> \n";
					if ($item->guid == $guid || $item->guid == $parentGUID || $item->name == $location[0]) {
						$class="selected";
						$selected=true;
					}
					if($class) $class = ' class="'.$class.' '.$position.'"';
					else if($position) $class = ' class="'.$position.'"';


					$html .= '<li id="'.$item->name.'-link"'.$class.'>';
					if ($mode == 'edit' || $mode == 'preview') {
						$html .= '<a href="#" title="This links is not clickable in '.$mode.' mode">'.ucfirst($item->title).'</a>';
					}
					else {
						//$html .= '<a href="'.$page->drawLinkByGUID($item->guid).'" title="view the '.strtolower($page->drawTitleByGUID($item->guid)).' section">'.$page->drawTitleByGUID($item->guid).'</a>';
						$html .= '<a href="'.$tmp_page->drawLinkByGUID($item->guid).'" title="view the '.strtolower($item->title).' section">';
						$html .= ucfirst($item->title) .'</a>';
					}
					$html .= '</li>'."\n\t";

				}
				// Throw the home page onto the start of the list
				$html .= '</ul>'."\n";
			}else{
				$html .= '<li></li></ul>'."\n\n";
			}
			
			// Add the link the the homepage
			if ($use_homepage) {
				if ($mode=="edit" || $mode=="preview") $homepage_link = '<li class="'.($guid==$site->id?"selected":"").' first"><a href="#" title="This link is not clickable in '.$mode.' mode">Home</a></li>';
				else $homepage_link = '<li class="'.($guid==$site->id?"selected":"").' first"><a href="'.$page->drawLinkByGUID($site->id).'">Home</a></li>';
			}
						
			$html ='<ul id="menu">'.($use_homepage?$homepage_link:'').$html;
			return $html;
			
		}
		
		
		// 16/12/2008 Comment
		// The secondary menu is a sub menu within each section 
		// It is usually drawn vertically on the left side of the page
		public function drawSecondaryByParent($guid, $this_guid = false, $orderBy='p.sort_order ASC', $level=1) {
			
			//print "dSBP($guid, $this_guid, $orderBy, $level)<br>";
			global $db, $mode, $page, $site;

			// Use page's parent and draw all the children
			$html = "\n";
			
			$query = "SELECT 
				unix_timestamp(p.date_published), p.guid, p.name, p.title, p.private, p.template,
				DATEDIFF(e.end_date, date_format(NOW(), '%Y-%m-%d')) AS days_ahead
				FROM pages p
				LEFT OUTER JOIN events e ON p.guid=e.guid 
				WHERE p.parent = '$guid' AND p.msv=".$site->id."
				AND p.hidden = 0 AND p.offline=0
				AND p.date_published IS NOT NULL AND unix_timestamp(p.date_published)>0
				AND p.template NOT IN (6, 23, 24)
				ORDER BY $orderBy ";
			
			// Check if this is a menu for a news section?
			// Show most recent 10 stories if this is a news page
			if ($db->get_var("select template from pages where guid='$guid'")==4) {
				$query.="LIMIT 10";
			}
			//print "$query<br>";
			if ($items = $db->get_results($query)) {	

				foreach ($items as $item) {
					
					$thisguid = $item->guid;
					$class = '';
					
					if ($item->guid == $this_guid) {
						// if this page in the menu is the page we're looking at
						$class = "subon";
					}
					if ($item->private) $class.=$class?" private":"private";
					
					for ($i=0;$i<(($level-1)*2);$i++){
						$indent.="&nbsp;";
					}
					$indent='';
					
					// Dont allow active menu links in edit/preview modes
					if ($mode == 'edit' || $mode == 'preview') {				
						//$html .= '<a href="#"'.$class.' style="padding-left:'. $indent .'px">'.$item->title.'</a>';
						$html .= '<li class="'.$class.'"><a href="#" title="These links aren\'t clickable in edit/preview mode" class="level-'.$level.'">'.$indent.$item->title.'</a>';
						if ($thisguid == $guid || in_array($guid, $page->getDescendentsByGUID($thisguid))) {
							$html .= $this->drawSecondaryByParent($thisguid, $this_guid, $orderBy, $level+1);
						}
						$html .= '</li>'."\n";
					}
					// Show the menu item.
					else {
					
						//if ($item->template==19) {
						//	print "<!-- showing events page for (".$item->days_ahead.") days ahead -->\n";
						//}
						
						if ($item->days_ahead<0 && $level==1) ;
						else {
							$html .= '<li class="'.$class.'"><a href="'.$page->drawLinkByGUID($thisguid).'" class="level-'.$level.'">'.$indent.$item->title.'</a>';
							$html .= '<ul>';
							if (($thisguid == $this_guid || in_array($this_guid, $page->getDescendentsByGUID($thisguid))) || $site->id == 18) {
								if ($thisguid != '57ff539fd1e94')
								{
									$html .= $this->drawSecondaryByParent($thisguid, $this_guid, $orderBy, $level+1);
								}
							}
							$html .='</ul>';
							$html .= '</li>'."\n";
						}
					}
				}
			}
			return $html;	
		}
		
		

		public function drawAll($guid) {
			global $db, $mode, $page, $site, $location;
			$i = 0;
			$html = '';
			$use_homepage=true;
			$use_homepage=false;

			// Get information about all children of the homepage
			$query = "SELECT p.guid, p.name, p.title
				FROM pages p
				LEFT JOIN sites_versions msv ON p.msv=msv.msv
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE p.hidden=0 and p.offline=0 AND pt.template_type=3
				AND p.date_published IS NOT NULL AND p.parent=".$site->id."
				AND p.msv=".$site->id." AND p.guid!=p.msv
				GROUP BY p.guid ORDER BY sort_order ASC";
			//print "<!-- $query -->\n";
			$items = $db->get_results($query);
			
			if(is_array($items) && count($items)>0 ){
			
				$selected = false;
				
				// Load details of the current page [should we use a global here?]
				$tmp_page = new Page();
				$parentGUID = $tmp_page->getParent();
				
				foreach ($items as $item) {
					$i++;
					
					// Position of this element in the list
					$selected = false;
					$position='';
					
					if(sizeof($items) == $i) $position = 'last';
					else if($i==1 && !$use_homepage) $position = 'first';

					// Class for selected
					$levelclass="level-0 ";
					
					$class ="color-".$item->color." ".$position;

					//print "<!-- item->guid(".$item->guid.") guid($guid) parent($parentGUID) name(".$item->name.") loc[0](".$location[0].") --> \n";
					if ($item->guid == $guid || $item->guid == $parentGUID || $item->name == $location[0]) {
						$selected=true;
						$this->color = $item->color;
					}

					$html .= '	<li id="'.$item->name.'-link" class="dropdown '.($levelclass.$class).'">';
					if ($mode == 'edit' || $mode == 'preview') {
						$html .= '<a href="#" title="This links is not clickable in '.$mode.' mode">'.ucfirst($item->title).'</a>';
					}
					else {
						//$html .= '<a href="'.$page->drawLinkByGUID($item->guid).'" title="view the '.strtolower($page->drawTitleByGUID($item->guid)).' section">'.$page->drawTitleByGUID($item->guid).'</a>';
						//$html .= '<a class="'.($levelclass.($selected?"selected":"")).'" href="'.$tmp_page->drawLinkByGUID($item->guid).'">';
						$html .= '<a href="#" class="dropdown-toggle '.($levelclass.($selected?"selected":"")).'" data-toggle="dropdown">';
						$html .= ucfirst($item->title).'</a>';
					}

					// Add all secondary items now...
					if ($item->template!=4) $submenu = $this->drawAllSecondaryByParent($item->guid);					
					if ($submenu) $html .= $submenu;
					$html .= '	</li>'."\n";

				}
				// Throw the home page onto the start of the list
			}
			
			// Add the link the the homepage
			if ($use_homepage) {
				if ($mode=="edit" || $mode=="preview") $homepage_link = '	<li class="'.($guid==$site->id?"selected":"").' first"><a href="#" title="This link is not clickable in '.$mode.' mode">'.$page->drawLabel("menu-home", "Home").'</a></li>';
				else $homepage_link = '	<li class="'.($guid==$site->id?"selected":"").' first"><a href="'.$page->drawLinkByGUID($site->id).'">'.$page->drawLabel("menu-home", "Home").'</a></li>';
			}
						
			$html ='
<ul id="menu" class="nav">
'.($use_homepage?$homepage_link:'').'
'.$html.'</ul>
';

			// We should cache this menu
			return $html;
			
		}
		


		public function drawMega($guid) {
			
			global $db, $mode, $page, $site, $location;
			$i = 0;
			$html = '';
			//$use_homepage = true;
			
			// Get information about all children of the homepage
			$query = "SELECT p.guid, p.name, p.title
				FROM pages p
				LEFT JOIN sites_versions msv ON p.msv=msv.msv
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE p.hidden=0 and p.offline=0 AND pt.template_type=3
				AND p.date_published IS NOT NULL AND p.parent=".$site->id."
				AND p.msv=".$site->id." AND p.guid!=p.msv
				GROUP BY p.guid ORDER BY sort_order ASC";
			//print "<!-- $query -->\n";
			$items = $db->get_results($query);
			
			if( is_array($items) && count($items)>0 ){
			
				$selected = false;
				
				// Load details of the current page [should we use a global here?]
				$tmp_page = new Page();
				$parentGUID = $tmp_page->getParent();
				
				if ($use_homepage) {
					$html = '<li><a href="'.$site->link.'">Home</li>'."\n";
					$this->items[0]['title']="home";
					$this->items[0]['html'] = '';
				}

				foreach ($items as $item) {
					$i++;
					$selected = false;
					// Position of this element in the list
					$class = '';
					$position='';
					if(sizeof($items) == $i) $class = 'last ';
					else if($i==1 && !$use_homepage) $class = 'first ';

					// Class for selected
					$class="";
					//print "<!-- item->guid(".$item->guid.") guid($guid) parent($parentGUID) name(".$item->name.") loc[0](".$location[0].") --> \n";
					if ($item->guid == $guid || $item->guid == $parentGUID || $item->name == $location[0]) {
						$class.="selected ";
						$selected=true;
					}
					$class = ' class="'.$class.'"';

					$html .= '
<li id="'.$item->name.'-link"'.$class.'>
	<a href="'.$tmp_page->drawLinkByGUID($item->guid).'" title="view the '.strtolower($item->title).' section">
	'.ucfirst($item->title) .'</a>
</li>
';
					if ($selected === true)
					{
						$this->items[$i]['selected'] = 'selected';
					}
					else
					{
						$this->items[$i]['selected'] = '';
					}
					$this->items[$i]['title'] = ucfirst($item->title);
					//print "<!-- get mega for ".$item->title." -->\n";
					$this->items[$i]['html'] = $this->drawMegaSecondaryByParent($i, $guid , $item->guid);
					if ($item->guid == '57d7c86cc2ce8')
					{
						$this->items[$i]['html'] .= '
						</div>
						<div>
						<li>
							<ul>
								<li class="level-1 dropdown-header"><a href="'.$site->link.'explore--resources/explore--resources">Explore / Resources</a></li>
							</ul>
						</li>
						</div>
						<div>
						<li>
							<ul>
								<li class="level-1 dropdown-header "><a href="'.$site->link.'events/current-events/">Events</a></li>
							</ul>
						</li>
						</div>
						';
					}
					//else if ($item->guid != '57d7c86cc2ce8')
					//{
					//	$this->items[$i]['html'] .= '
					//	</div>
					//	<div>
					//	<li>
					//		<ul>
					//			<li class="level-1 dropdown-header"><a href="'.$site->link.'annual-update-2017">Annual update 2017</a></li>
					//		</ul>
					//	</li>
					//	</div>
					//	';
					//}
					
					print '<!--Name: '.$item->title.' Guid:-->';

				}
				
				
			}

			$this->html = $html;
			//print "Got menu items(".print_r($this->items, 1).")<br>\n";
			return;
			
		}


		public function drawMegaSecondaryByParent($section, $guid, $parent, $level=1) {
			
			//print "dMSBP($section, $guid, $parent, $level)<br>";
			global $db, $mode, $page, $site;

			// Read more for blogs/news/publications/events pages
			$moreguid = array('55b2329b38d67', '55c33a5b0c6e1', '55c48af15dd20', '55c48ac2bcf10', "58c01938a0f25");
			
			$html = '';
			$orderBy = "sort_order ASC ";
			// Use page's parent and draw all the children
			
			$pagemax = 8;	// might find we need to change this for different page types.
			
			$query = "SELECT 
				unix_timestamp(p.date_published), p.guid, p.name, p.title, p.private, p.template,
				DATEDIFF(e.end_date, date_format(NOW(), '%Y-%m-%d')) AS days_ahead
				FROM pages p
				LEFT OUTER JOIN events e ON p.guid=e.guid 
				WHERE p.parent = '$parent' AND p.msv=".$site->id."
				AND p.hidden = 0 AND p.offline=0
				AND p.date_published IS NOT NULL AND unix_timestamp(p.date_published)>0
				AND p.template NOT IN (6, 23, 24)
				ORDER BY $orderBy
				LIMIT ".(($level==1 && $site->id != 18)?4:$pagemax);
			//print "<!-- $query -->\n";
			//print "$query\n";

			if ($items = $db->get_results($query)) {	

				$i = 0;
				foreach ($items as $item) {
					
					$class = 'level-'.$level.' ';
					if ($level==1) $class.="dropdown-header ";					
					if ($item->private) $class.=$class?" private":"private";

					if ($level>1 ) {
						$html .= '<li class="'.$class.'">';
						$link = $page->drawLinkByGUID($item->guid);
						$html .= '<a href="'.$link.'">'.ucfirst($item->title).'</a>';
						$html .= '</li>'."\n";
					}
					else {
						//print '<!-- show l('.$level.') submenu('.$item->title.') items... -->';
						$readmore = '';
						if (in_array($item->guid, $moreguid)) {
							//print '<!-- show l('.$level.') submenu('.$item->title.') items... -->';
							if (strtolower($item->title)=="events") {	
								$evtcalguid = "560d53797159e";
								$readmore .= '<li><a href="'.$page->drawLinkByGUID($evtcalguid).'"><strong>Event calendar</strong></a></li>'."\n";
							}
							$readmore .= '<li><a href="'.$page->drawLinkByGUID($item->guid).'"><strong>More '.strtolower($item->title).'</strong></a></li>'."\n";
						}
						
						if (($i == 0 ||$i % 4 == 0) && $site->id == 18)
						{
							if ($i == 0)
							{
								$html .= '<div class="">';
							}
							else
							{
								$html .= '</div>';
								$html .= '<div class="">';
							}
						}
						//Level 1 uses links on AESA microsite
						if ($site->id == 18)
						{
							//'.$this->drawMegaSecondaryByParent($section, $guid, $item->guid, $level+1).'
							$html .='
							<li class="">
								<ul>
									<li class="'.$class.'"><a href="'.$page->drawLinkByGUID($item->guid).'">'.ucfirst($item->title).'</a></li>
									
									'.$readmore.'
								</ul>
							</li>
							';
							
							if ($item->guid == '580bb4b88a90d')
							{
								$html .='
								<li class="">
									<ul>
										<li class="'.$class.'"><a href="'.$site->link.'annual-update-2017">Annual update 2017</a></li>
										
										'.$readmore.'
									</ul>
								</li>
								';
							}
						}
						//Default AAS Behavior
						else
						{
							$html .='
								<li class="col-sm-3">
									<ul>
										<li class="'.$class.'">'.ucfirst($item->title).'</li>
										'.$this->drawMegaSecondaryByParent($section, $guid, $item->guid, $level+1).'
										'.$readmore.'
									</ul>
								</li>
								';
						}
					}
					
					
				$i++;	
				}
			}
			else {
				// Is it a publications menu?
				if ($parent == '55c48ac2bcf10') $html .= $this->drawPublicationsMenu($pagemax);
				else {
					//$html .= '<li>There are no items('.$parent.')</li>'."\n";
				}
			}			
			
			return $html;	
		}

		// Find links to the latest x publications
		private function drawPublicationsMenu($max) {
			global $db, $site, $publicationcategories;
			
			$query = "SELECT f.title AS title, f.guid AS guid
				FROM files f INNER JOIN filecategories fc ON fc.id = f.category
				WHERE f.site_id=". $site->id." AND f.resource=1 AND fc.id IN ($publicationcategories) 
				LIMIT $max";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				foreach($results as $result) {
					$html .= '<li><a href="/download/'.$result->guid.'/">'.$result->title.'</a></li>'."\n";
				}
			}
			return $html;
		}
		
		
		public function drawAllSecondaryByParent($guid, $this_guid = false, $orderBy='p.sort_order ASC', $level=1) {
			
			//print "dASBP($guid, $this_guid, $orderBy, $level)<br>";
			global $db, $mode, $page, $site;

			// Use page's parent and draw all the children
			$query = "SELECT 
				unix_timestamp(p.date_published), p.guid, p.name, p.title, p.private, p.template,
				DATEDIFF(e.end_date, date_format(NOW(), '%Y-%m-%d')) AS days_ahead
				FROM pages p
				LEFT OUTER JOIN events e ON p.guid=e.guid 
				WHERE p.parent = '$guid' AND p.msv=".$site->id."
				AND p.hidden = 0 AND p.offline=0
				AND p.date_published IS NOT NULL AND unix_timestamp(p.date_published)>0
				AND p.template NOT IN (6, 23, 24)
				ORDER BY $orderBy ";
			
			// Check if this is a menu for a news section?
			// Show most recent 10 stories if this is a news page
			if ($db->get_var("select template from pages where guid='$guid'")==4) {
				$query.="LIMIT 10";
			}
			//print "<!-- $query -->\n";
			if ($items = $db->get_results($query)) {	

				foreach ($items as $item) {
					
					$thisguid = $item->guid;
					$class = '';
					
					if ($item->guid == $this_guid) {
						// if this page in the menu is the page we're looking at
						$class = "subon";
					}
					if ($item->private) $class.=$class?" private":"private";
					
					for ($i=0;$i<(($level-1)*2);$i++){
						$indent.="&nbsp;";
					}
					$indent='';
					
					$tabs = '';
					for($j=0;$j<$level+1;$j++) $tabs.="\t";

					// Dont allow active menu links in edit/preview modes
					if ($mode == 'edit' || $mode == 'preview') {				
						//$html .= '<a href="#"'.$class.' style="padding-left:'. $indent .'px">'.$item->title.'</a>';
						$html .= $tabs.'<li class="level-'.$level.' '.$class.'"><a href="#" title="These links aren\'t clickable in edit/preview mode" class="level-'.$level.'">'.$indent.$item->title.'</a>';
						if ($thisguid == $guid || in_array($guid, $page->getDescendentsByGUID($thisguid))) {
							if ($item->template==4) ;
							else {
								$html .= $this->drawAllSecondaryByParent($thisguid, $this_guid, $orderBy, $level+1);
							}
						}
						$html .= $tabs.'</li>'."\n";
					}
					// Show the menu item.
					else {
						//print "<!-- Show title(".$item->title.") level($level) temp(".$item->template.")-->\n";					
						if ($item->days_ahead<0 && $level==1) {
							//print "<!-- no days ahead ignore submenu -->\n";
						}
						else {
							$html .= $tabs.'<li class="level-'.$level.' '.$class.'"><a href="'.$page->drawLinkByGUID($thisguid).'" class="level-'.$level.'">'.$indent.$item->title.'</a>';
							//if ($thisguid == $this_guid || in_array($this_guid, $page->getDescendentsByGUID($thisguid))) {
							if ($level<3) {
								if ($item->template==4);	// Skip news and events submenus
								else {
									//print "<!-- show submenu items... -->\n";
									$html .= $this->drawAllSecondaryByParent($thisguid, $this_guid, $orderBy, $level+1);
								}
							}
							//}
							$html .= $tabs.'</li>'."\n";
						}
					}
				}
			}
			if ($html) {
				$html = '
'.$tabs.'<ul class="dropdown-menu submenu">
'.$html.''.$tabs.'
</ul>
';
			}
			return $html;	
		}
		
		public function drawFooterMenu($guid) {
			// draw links to gloabl items e.g. contact, accessibility, privacy policy
			global $db, $MODE;
			$i = 0;
				
			$query = "SELECT p.guid, p.name, p.title 
						FROM pages p 
						WHERE p.parent = ". $guid ." AND p.hidden = 1 AND p.type_id!=4
						AND p.offline=0
						AND (p.locked = 0 OR p.name='sitemap' ) AND p.date_published IS NOT NULL 
						ORDER BY p.sort_order ASC";	
			// print "$query<br>\n";
			$items = $db->get_results($query);

			if( is_array($items) && count($items)>0 ) {
				//echo 'ok on line: '. __LINE__ .'<br />';
				$page = new Page();
				//$page->loadByGUID($guid);	
				
				$parentGUID = $page->getParent();
				
				foreach ($items as $item) {
					//echo 'ok on line: '. __LINE__ .'<br />';
					$i++;
					// add first/last class for CSS
					if(sizeof($items) == $i){
						$position = 'last';
					} 
					else if($i == 1){
						$position = 'first';
					}
					else{
						$position = '';
					}
					//echo 'ok on line: '. __LINE__ .'<br />';
					// Class for selected
					$class = ($item->guid == $guid || $item->guid == $parentGUID) ? 'selected' : '';
					if($class){
							$class = ' class="'.$class.' '.$position.'"';
					}
					else if($position){
						$class = ' class="'.$position.'"';
					}
					else{
						$class = '';
					}
					//echo 'ok on line: '. __LINE__ .'<br />';
					$html .= '<li id="'.$item->name.'-link"'.$class.'>';
					
					if ($MODE == 'edit' || $MODE == 'preview') {
						$html .= '<a href="#" title="These links aren\'t clickable in edit/preview mode">'.$item->title.'</a>';
					}
					else {
						//echo 'GUID: '. $item->guid .'<br />';
						//$html .= '<a href="'.$page->drawLinkByGUID($item->guid).'">'.$page->drawTitleByGUID($item->guid).'</a>';
						$html .= '<a href="'.$page->drawLinkByGUID($item->guid).'">'.$item->title.'</a>';
					}
					$html .="</li>\n\t";
					//echo 'ok on line: '. __LINE__ .'<br />';
				}
			}
			//$html .= "</ul>\n";
			
			return $html;
		}


		// This function returns a list of all pages
		public function drawSiteMapByParent($parent = 0, $siteID=1, $level=0) {
			global $db;


			// Disable main site news stories
			if ($siteID==1 && $parent=="47f10c47d90f7") return;
			
			// Load details of the current page [should we use a global here?]
			$thispage = new Page();
			$query = "SELECT p.parent, p.guid, p.name, p.title, p.locked, p.hidden,
				p.template, p.date_published
				FROM pages p
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE parent = '$parent' AND msv='$siteID'
				AND p.offline=0
				AND p.hidden = 0
                                AND p.template != 75
				AND pt.template_type!=2
				ORDER BY p.sort_order, p.name ASC";
			//print "$query<br>";
			$db->query($query);

			if ($pages = $db->get_results($query)) {

				foreach ($pages as $page) {
					if(($page->locked == 1 && $page->hidden == 1) || $page->hidden==1){ /* hide certain pages */
						$html .= '';
						$i = 0;
					} 
					else {
						$link = $thispage->drawLinkByGUID($page->guid);
						if( $page->guid==$siteID ){
							$html .= '<li><a href="'.$link.'">Home</a>';
						}
						else {
							if($page->type!='microsite'){ // quick fix to hide microsites from site map for Website Launch
								if ($page->template==2 && $page->date_published=="0000-00-00 00:00:00") ;	// Don't show unpublished content pages
								else $html .= '<li><a href="'.$link.'">'. ucfirst($page->title) .'</a>';
							}
						}
						if( isset($page->type) && $page->type=='microsite' ){
							//$html .= ' (microsite)';
						}
						$html .= $this->drawSiteMapByParent($page->guid,$siteID, $level+1); /* Get children if they exist */
						$html .= '</li>'."\n\t";
					}

				}

			}
			
			if (!$html && $level==0) $html='<li>No pages exist for this site</li>';
			$html = '<ul>'.$html.'</ul>';
			
			
			return str_replace("<ul></ul>", "", $html);

		}
		
		public function drawXMLSiteMapByParent($parent=1,$loop=0, $news=false) {
			// This function returns a list of all pages within the site in an XML format for Google sitemaps
			global $db, $site;
			//print "dXMLSMBP($parent, $loop, $news)<Br>\n";
			// Load details of the current page [should we use a global here?]
			$thispage = new Page();
			//$thispage->loadByGUID(1);	

			$html = ''; // setup varaible (avoid array errors)
			if (!$loop) {
				if (!$news) $html = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
';
				else {
					$html = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
';
				}
			}
			$newsdelay = 48;	// Only last 48 hours wanted by google news
			
			// Detect news index page
			if ($news) {
				$query = "SELECT guid FROM pages WHERE template = 4 AND msv=".$site->id;
				$newsguid = $db->get_var($query);
			}

			//$condition = ($loop==0) ? "(parent = '$parent' OR guid='$parent')" : "parent = '$parent'";
			$query = "SELECT parent, guid, name, title, locked, hidden, date_published, date_created 
				FROM pages 
				WHERE msv=$parent 
				AND title!='Treeline' 
				AND template IN (1,2)
				AND offline=0
				AND hidden=0
				".($news?"AND parent='$newsguid' AND date_published>NOW()-INTERVAL $newsdelay HOUR ":"")." 
				ORDER BY sort_order, name ASC
				";
			//print "$query<br>\n";
			if ($pages = $db->get_results($query)) {
			
				foreach ($pages as $page) {
					//print "got guid(".$page->guid.")<br>\n";
					//if ($page->guid=='1') print "Home link(".$thispage->drawLinkByGUID($page->guid).")<br>\n";
					$link = $thispage->drawLinkByGUID($page->guid);
					$priority = $c = 0;
					if (preg_match_all("|/|", $link, $reg, PREG_SET_ORDER)) {
						$c = count($reg)-3;
						$priority = 1-(2*($c/10));
					}
					if ($priority < 0.1) $priority = 0.1;
					//print "pri($priority) levl($c) link($link)<br>\n";
					
					if($page->date_published == '0000-00-00 00:00:00'){
						$lastMod = date('Y-m-d',strtotime($page->date_created));
					} 
					else{
						$lastMod = date('Y-m-d',strtotime($page->date_published));
					}
					
					if (!$news) {
						$html .= '<url>
	<loc>'.$link.'</loc>
	<lastmod>'.$lastMod.'</lastmod>
	<priority>'.$priority.'</priority>
</url>
';
					}
					else {
						$html.='<url>
	<loc>'.$link.'</loc>
	<news:news>
		<news:publication>
			<news:name>HelpAge Newsroom</news:name>
			<news:language>'.($site->properties['language']).'</news:language>
		</news:publication>
		<news:publication_date>'.$lastMod.'</news:publication_date>
		<news:title>'.$page->title.'</news:title>
	</news:news>
</url>
';
					}
					//$html .= $this->drawXMLSiteMapByParent($page->guid, $loop+1, $news);
				}
			}
			if ($loop==0) $html.='</urlset>'."\n";
			return $html;
		}

		
		public function _drawXMLSiteMapByParent($parent=1,$loop=0) {
			// This function returns a list of all pages within the site in an XML format for Google sitemaps
			global $db;
			
			// Load details of the current page [should we use a global here?]
			$thispage = new Page();
			//$thispage->loadByGUID(1);	

			$html = ''; // setup varaible (avoid array errors)
			//$condition = ($loop==0) ? "(parent = '$parent' OR guid='$parent')" : "parent = '$parent'";
			$query = "SELECT parent, guid, name, title, locked, hidden, date_published, date_created 
				FROM pages 
				WHERE msv=$parent AND title!='Treeline' AND template=2 AND offline=0
				ORDER BY sort_order, name ASC";
			
			if ($pages = $db->get_results($query)) {
				foreach ($pages as $page) {
						$link = $thispage->drawLinkByGUID($page->guid);
						
						if($page->date_published == '0000-00-00 00:00:00'){
							$lastMod = date('Y-m-d',strtotime($page->date_created));
						} 
						else{
							$lastMod = date('Y-m-d',strtotime($page->date_published));
						}
						
						$html .= '<url>'."\n\t\t";
						$html .= '<loc><![CDATA[http://'.$_SERVER['HTTP_HOST'].urlencode($link).']]></loc>'."\n\t\t";
						$html .= '<lastmod>'.$lastMod.'</lastmod>'."\n\t\t";
						$html .= '<changefreq>monthly</changefreq>'."\n\t\t";
						$html .= '<priority>1</priority>'."\n\t";
						$html .= '</url>'."\n\t";
						$html .= $this->drawXMLSiteMapByParent($page->guid,$loop+1);
				}
			}

			return $html;
		}



		// need to remove and recreate the sitemap index.
		// FORMAT::
		/*
		<?xml version="1.0" encoding="UTF-8"?>
		<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			<sitemap>
				<loc>http://www.helpage.org/sitemap.xml</loc>
				<lastmod>2004-10-01T18:23:17+00:00</lastmod>
			</sitemap>
			<sitemap>
				<loc>http://www.helpage.org/videositemap.xml</loc>
				<lastmod>2005-01-01</lastmod>
			</sitemap>
		</sitemapindex>
		*/
		public function drawSiteMapIndex() {
			//print "sSMI()<br>\n";
			// Find all sitemaps in the cache
			global $site;
			$format = '%Y-%m-%dT%H:%M:%S+00:00';
			$cacheRoot = $_SERVER['DOCUMENT_ROOT']."/cache/";
			if ($dir = opendir($_SERVER['DOCUMENT_ROOT']."/cache/")) {
				while ($file = readdir($dir)) {
					//print "file($file) time(".strftime($format, filectime($cacheRoot.$file)).")<br>\n";
					switch($file) {
						case "sitemap1.xml":
							$contents .= "	<sitemap>\n";
							$contents .= "		<loc>".$site->root."sitemap.xml</loc>\n";
							$contents .= "		<lastmod>".strftime($format, filectime($cacheRoot.$file))."</lastmod>\n";
							$contents .= "	</sitemap>\n";
							break;
						/*
						case "sitemap1news.xml":
							$contents .= "	<sitemap>\n";
							$contents .= "		<loc>http://www.helpage.org/sitenews.xml</loc>\n";
							$contents .= "		<lastmod>".strftime($format, filectime($cacheRoot.$file))."</lastmod>\n";
							$contents .= "	</sitemap>\n";
							break;
						*/
						default : break;
					}
				}
				closedir($dir);
			}		
			//print "c($contents)<br>\n";
			$contents = '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
'.$contents.'</sitemapindex>
';
			$indexfile = $_SERVER['DOCUMENT_ROOT']."/sitemapindex.xml";
			unlink($indexfile);
			//print "Put($indexfile, $contents)<br>\n";
			file_put_contents($indexfile, $contents);
		}
	
		
	}
?>