<?php

	class Page {
	
		// A couple of conventions:
		
		// load() loads an object
		// save() saves an object
		// get() gets a value
		// put() updates a value ?
		// draw() outputs data as HTML, or a string usable in an HTML page
	
		public $guid;
		private $sort_order;				
		
		// should ALL these be private, because we use functions to access them?
		public $name;
		public $title;
		public $meta_description;
		//public $meta_keywords;	
		public $tagslist;
		public $date_created;
		public $user_created, $user_created_id;
		public $date_modified;	
		public $date_published, $nice_date, $blog_date;
		
		public $shorturl;
		public $hidden, $robots;	
		public $offline;
		public $private;
		public $comment;
		public $siteID;
		public $lang;
		public $newsDisplay;
		public $encoding;
		public $textdir;
		public $locked;
		public $type;
		public $style, $style_id;
		public $target, $member_id;
		
		public $permissions;
		public $mode;

		public $totalresults;
		public $perpage;
		public $page;
		public $totalpages;
		public $from;
		public $to;	
		
		
		public function __construct() {
			// This is loaded when the class is created	
			//$this->meta_description = "<enter description>";
			//$this->meta_keywords = "<enter keywords>";	
		}
				
		public function loadByGUID($guid) {
			// This function loads all data for the page with the specified GUID
			global $db, $tags;
			$query = "SELECT p.*, 
				DATE_FORMAT(p.date_published, '%d <span>%M %Y</span>') as nice_date,
				DATE_FORMAT(p.date_created, '%d <span>%M %Y</span>') as blog_date,
				t.template_php, t.template_type, s.style_css, msv.language, l.encoding, l.text_dir, 
				u.full_name
				FROM pages p 
				LEFT JOIN pages_templates t ON t.template_id = p.template 
				LEFT JOIN pages_style s ON s.style_id = p.style 
				LEFT JOIN sites_versions msv ON msv.msv=p.msv
				LEFT JOIN languages l ON msv.language=l.abbr
				LEFT JOIN users u on u.id = p.user_created
				WHERE p.guid = '$guid'";
			//print "<!-- $query -->\n";
			if ($data = $db->get_row($query)) {
			//echo '<pre>'. print_r($data,true) .'</pre>';
				$this->guid = $data->guid;
				$this->name = $data->name;
				$this->title = html_entity_decode($data->title);
				//$msg.="loaded page title (".$this->title.") data(".$data->title.") \n";
				$this->sort_order = $data->sort_order;
				$this->meta_description = html_entity_decode($data->meta_description);
				//$this->meta_keywords = $data->meta_keywords;
				$this->parent = $data->parent;
				$this->template = $data->template_php;
				$this->template_id = $data->template;
				$this->template_type = $data->template_type==2?"panel":"page";
				$this->style = $data->style_css;
				$this->style_id = $data->style;
				$this->date_created = $data->date_created;
				$this->user_created = $data->full_name;
				$this->user_created_id = $data->user_created;
				$this->date_modified = $data->date_modified;
				$this->date_published = $data->date_published;
				$this->nice_date = $data->nice_date;
				$this->blog_date = $data->blog_date;
				$this->setShortURL($this->getShortURL($guid));
				$this->hidden = $data->hidden;
				$this->robots = $data->robots;
				$this->offline = $data->offline;
				$this->private = $data->private;
				$this->comment = $data->comment;
				$this->locked = $data->locked;
				$this->siteID = $data->msv;
				$this->lang = $data->lang;
				$this->encoding = $data->encoding;
				$this->textdir = $data->text_dir;
				$this->type = $data->type;
				$this->target = $data->target;
				$this->member_id = $data->member_id;
				$this->donate_button = $data->donate_button;
				//print "<!-- loadByGUID($guid) $msg --> \n";
				return true;
			}
			return false;
		}
		
		public function create($type = false) {
			global $db, $user, $site;
			//print "c($type)<br>\n";
			if (!$this->guid) {
				$guid = uniqid();
				$this->guid = $guid;
			}
			$pageSiteID=$this->siteID?$this->siteID:$site->id;
			
			$title = html_entity_decode($this->title, ENT_QUOTES,$site->properties['encoding']) ;
			$title = $db->escape( $title );
			
			$name = $db->escape($this->name);
			
			$meta_description = html_entity_decode($this->meta_description,ENT_QUOTES,$site->properties['encoding']);
			$meta_description = $db->escape( htmlentities($meta_description,ENT_QUOTES,$site->properties['encoding']) );
			
			$shorturl = $db->escape($this->shorturl);  /// shouldn't be nesessary but just to be sure...
			if ($type==2) {
				$template_type="panel";
			}
			else {
				if (!$type) $type=1;
				$template_type="page";
			}
			$locked=$this->locked?$this->locked:0;
			
			$user_id = 0;
			if (is_object($user)) $user_id = $user->getID();
			
			$query = "INSERT INTO pages 
				(
				guid, parent, sort_order, name, title, 
				meta_description, hidden, robots,
				offline, private, locked, 
				template, style, 
				date_created, user_created, 
				".($this->template_id==16?"date_published, ":"")."
				msv, type_id, comment
				) 
				VALUES 
				('{$this->guid}', '{$this->parent}', {$this->sort_order}, '{$name}', 
				'{$title}', '{$meta_description}', ".($this->hidden+0).", ".($this->robots+0).", 
				".($this->offline+0).", ".($this->private+0).", ".($locked+0).", 
				'".($this->template_id+0)."', ".($this->style+0).
				", NOW(), ".$user_id.", 
				".($this->template_id==16?"NOW(), ":"")."
				$pageSiteID, {$type}, ".($this->comment+0)." )";
			//niceError( $query );
			//logit($query);
			//exit();
			// We need to add "expiry" dates here
			if( $db->query($query) ){
				//print "Created page ok<br>\n";
				// We have created a new page
				addHistory($user->id, "", $this->guid, ucfirst($template_type)." created", $template_type."s");
				//print "added history<br>\n";
				if($shorturl){
					$longurl = $this->drawLink();
					$query = "INSERT INTO shorturls (guid,shorturl,longurl, msv) VALUES ('$guid','$shorturl','$longurl', $pageSiteID)";
					$db->query($query);
				}
				clearCache();	
				return true;
			}
			return false;
		}

		public function save($log_action=false) {
			global $db, $siteData, $siteID;
			// Why don't we use a global $user here, as with create();
			$userID = read($_SESSION,'userid',0);
			$user = new User();
			$user->loadById($userID);			
		
			$title = html_entity_decode($this->title, ENT_QUOTES, $siteData->encoding) ;
			//print "h_e_d(".$this->title.", ENT_QUOTES, ".$siteData->encoding.")<br>\n";
			//$title = $db->escape( htmlentities($title,ENT_QUOTES,$siteData->encoding) );			
			$title = $db->escape( $title );
			$name = $db->escape($this->name);
			$meta_description = html_entity_decode($this->meta_description,ENT_QUOTES,$siteData->encoding);
			$desc = $db->escape( htmlentities($meta_description,ENT_QUOTES,$siteData->encoding) );
			$keywords = $db->escape( htmlentities($this->meta_keywords,ENT_QUOTES,$siteData->encoding) );
			$shorturl = $db->escape($this->shorturl);  /// shouldn't be nesessary but just to be sure...

			$style = ($_POST['style']) ? $_POST['style'] : $this->style_id;
			if ($_POST['style-'.$this->getGUID()]) $style=$_POST['style-'.$this->getGUID()];
			
			//print "got style($style) this (".$this->style_id.")<br>\n";
			$template = $db->escape($this->template_id);
			$query = "UPDATE pages SET 
				title='$title', 
				name='$name',
				meta_description='$desc', 
				hidden=".($this->hidden+0).", 
				robots=".($this->robots+0).", 
				offline=".($this->offline+0).", private=".($this->private+0).",
				`comment`=".($this->comment+0).", 
				parent='{$this->parent}', 
				template = '$template', 
				style = '".($style+0)."', 
				date_modified = NOW(), 
				".($template==16?"date_published=NOW(),":"")."
				user_modified = ".$user->getID()." 
				WHERE guid = '{$this->guid}'
				";
			//print "save page($query)<br>\n";
			$db->query($query);

			if($shorturl){
				$longurl = $this->drawLink();
				if($this->getShortURL($this->guid) ){
					$query = "UPDATE shorturls SET shorturl='$shorturl',longurl='$longurl' WHERE guid='{$this->guid}'";
				}else{
					$query = "INSERT INTO shorturls (guid,shorturl,longurl, msv) VALUES ('{$this->guid}','$shorturl','$longurl', $siteID)";
				}
				//print "$query<br>";
				$db->query($query);
			}	

			// Add a task to the history table
			// Basically this sits in the history table until someone publishes or rejects any edits.
			if ($log_action) {
				if(addHistory($_SESSION['treeline_user_id'], 'Publish', $this->guid, ucfirst($this->template_type)." saved", $this->template_type."s")) {
					// Its not critical if page saves fail to register in the history table.
					$tasks=new Tasks($site->id);
					if ($this->template_type=="page") $pageLink='(<a href="'.$this->drawLinkByGUID($this->getGUID()).'">'.$this->title.'</a>)';
					else $pageLink="";
					$sendParams = array("PAGETYPE"=>$this->template_type,
						"PAGELINK"=>$pageLink
						);
					$tasks->notify("publish", $sendParams);
				}
			}
			
			//Content edited so clear sitemap cache.
			clearCache();
		}


		public function publish($forcePublish=false) {
			global $db, $user;
			$success=0;
			//print "p($forcePublish) guid(".$this->guid.")<br>\n";
			
			// Make sure there is some publishable content
			$query = "SELECT count(*) FROM content WHERE revision_id=1 AND parent = '".$this->guid."'";
			if ($db->get_var($query)>0) {
				$query="UPDATE content SET revision_id=revision_id-1 WHERE parent='".$this->guid."'";
				$published_content = $db->query($query);
				//print "PUB - $query<br>\n";
			}
			//else print "No chages to publish<br>\n";
			
			if (is_object($user)) $publish_user = $user->getID();
			else $publish_user = 0;

			if ($published_content || $this->template_id==68 || $forcePublish) {
				
				$query = "UPDATE pages SET date_published = NOW(), user_published = $publish_user WHERE guid = '".$this->guid."'";
				//print "$query<br>\n";
				$success=$db->query($query);

				// We have published the page attributes
				addHistory($user->id, "", $this->guid, ucfirst($this->template_type)." published", $this->template_type."s");

				// Update this page in the history table also
				$query = "UPDATE history 
					SET completed_action='PUBLISHED', completed_date=now(), completed_by=".$publish_user." 
					WHERE action='publish' 
					AND (info = 'Page saved' OR info = 'Panel saved') 
					AND completed_action is null
					AND guid='".$this->guid."'";
				//print "$query<br>\n";
				$db->query($query);
			}
			//Page published so clear sitemap cache.
			clearCache();
			return $success;
		}
		
		// 8th Jan 2009 - Phil Redclift
		// Reject current edits to a page.
		public function rejectedits() {
			global $db, $user;
			$query = "delete from content where parent = '".$this->guid."' and revision_id=1";
			//print "$query<br>\n";
			if ($db->query($query)) {

				// We have rejected edits to the page
				addHistory($user->id, "", $this->guid, "Changes rejected", $this->template_type."s");

				// Update this page in the history table also
				$query = "UPDATE history 
					SET completed_action='CHANGES REJECTED', completed_date=now(), completed_by=".$user->id." 
					WHERE action='publish' 
					AND completed_action is null
					AND (info='Page saved' OR info='Panel saved')
					AND guid='".$this->guid."'";
				if (!$db->query($query)) {
					//print "failed($query)<br>\n";
				}
				return true;
			
			}
			return false;
		}
		

		public function delete() {
			global $db, $user;
			// Check permissions here too? Or should the user just not be given the option to delete without permission?
			// I suspect we should check here too, just in case of querystring manipulation etd

			// Delete all associated content
			$db->query ("DELETE FROM content WHERE parent = '{$this->guid}'");
			// Delete the actual page record
			$db->query ("DELETE FROM pages WHERE guid = '{$this->guid}'");
			// Delete the shorturl
			$db->query("DELETE FROM shorturls WHERE guid='{$this->guid}'");
			
			// We have published the page attributes
			addHistory($user->id, "", $this->guid, ucfirst($this->template_type)." deleted", $this->template_type."s");
			
			//Page deleted so clear sitemap cache.
			clearCache();
		}	
		
	public function deletePanel($panels, $panel_list, $panel_guid) {
		//print "dP($panel_list, $panel_guid)<br>\n";
		$new_panel_list='';

		if ($tmp = explode(",", $panel_list)) {
			foreach($tmp as $tmp_id) {
				if ($tmp_id!=$panel_guid) {
					$new_panel_list .= $tmp_id.",";
				}
			}
			if ($new_panel_list) $new_panel_list = substr($new_panel_list, 0, -1);
			//print "OLD(".$_POST['treeline_panels'].")<br>NEW($new_panel_list)<br>\n";

			if ($new_panel_list != $panel_list) {
				$_POST['treeline_panels']=$new_panel_list;
				$panels->load($this->getGUID(), 'panels');
				
				// If this is a custom panel we are removing then 
				// we need to remove the content also.
				$testpage = new Page();
				$testpage->loadByGUID($panel_guid);
				if ($testpage->getGUID()==$panel_guid) {
					//print "Loaded delete page template(".$testpage->template_id.")<br>\n";
					if ($testpage->template_id == 23) {
						$testpage->delete();
						//print "Deleted custom panel<br>\n";
					}
				}
				return true;
			}
		}
		return false;
	}
				

		// PMR 17th Feb 2010
		// Copy content from one site to another.
		public function copycontent($from_msv, $name, $placeholder='content', $revision_id=0) {
			global $db, $site;
			//print "p::cc($from_msv, $name, $placeholder, $revision_id)<br>\n";
			
			if (!$this->guid) {
				print "No page loaded cannot copy in content from another page<br>\n";
				return false;
			}
			if (!$name || !$from_msv>0) return false;
			
			// 1 Check no content record exists for this placeholder at all. Copied content is always published
			$query = "SELECT p.guid, 
				(SELECT c.id FROM content c WHERE c.parent='".$this->guid."' AND c.placeholder='$placeholder' ORDER BY revision_id DESC LIMIT 1) AS id
				FROM pages p
				WHERE p.guid='".$this->guid."'
				";
			//print "$query<br>\n";
			if ($row = $db->get_row($query)) {
				if (!$row->id) {
					
					// 2 - Collect source content data
					$query = "SELECT c.* FROM pages p
						INNER JOIN content c ON p.guid=c.parent
						WHERE p.name='$name'
						AND p.msv=$from_msv
						AND c.placeholder='$placeholder'
						AND c.revision_id=$revision_id
						LIMIT 1
						";
					//print "$query<br>\n";
					if ($row = $db->get_row($query)) {
						
						// 3 - Insert new content
						$query = "INSERT INTO content
							(guid, parent, content, revision_id, revision_date, placeholder)
							VALUES 
							('".uniqid()."','".$this->guid."', '$row->content', 1, NOW(), '".$placeholder."')
							";
						//print "$query<br>\n";
						if ($db->query($query)) {
							// 4 - Publish new content
							$this->publish();
							return true;
						}
						else print "Failed to publish new page<br>\n";
					}
					else print "Failed to collect source content<br>\n";
				}
				else print "Destination guid already has some content<br>\n";
			}
			else print "Destination page does not exist<br>\n";
			return false;
		}
		
		
		public function setParent($parent) {
			$this->parent = $parent;
		}
		public function setSortOrder($sortOrder = false) {
			// This sets the sort_order for a page, and assings it the minimum value if it's not specified
			global $db;
			if ($sortOrder) {
				$this->sort_order = $sortOrder;
			}
			else {
				$data = $db->get_var("SELECT sort_order FROM pages WHERE parent = '{$this->parent}' ORDER BY sort_order DESC LIMIT 1");
				$this->sort_order = $data + 1;
			}
		}
		
		
		public function getShortURL($guid=false){
			global $db;
			
			if($guid){
				if($url = $db->get_var("SELECT shorturl FROM shorturls WHERE guid='$guid'") ){
					return $url;
				}else{
					return false;
				}
			}else{
				return $this->shorturl;
			}
		}
		
		public function setShortURL($url){
			$this->shorturl = $url;
		}
		
		public function deleteShortURL() {
			global $db;
			if ($this->getGUID() && $this->getShortURL()) {
				$query="delete from shorturls WHERE guid='".$this->getGUID()."' and shorturl='".$this->shorturl."'";
				//mail("phil.redclift@ichameleon.com", "AMG qery", $query);
				$db->query($query);
			}
		}
		
		// This function only checks the page name is not the same
		// as the short URL.
		public function validShortURL($url) {
			//print "name(".$this->name.") == $url<br>\n";
			if ($this->name == $url) return false;
			return true;
		}
		
		public function checkShortURL($url, $returnguid=false) {
			global $db;
			
			// 1 - Check we do not already have this shortURL in the table.
			if($db->get_var("SELECT longurl FROM shorturls WHERE shorturl='$url' LIMIT 1") ){
				//print "cSURL($url) matched a  page<br>\n";
				if ($returnguid) return $db->get_var("SELECT guid FROM shorturls WHERE shorturl='$url' LIMIT 1");
				return true;
			}
			// 2 - Check if there is a microsite with this name already
			if ($db->get_var("SELECT microsite FROM sites WHERE name='$url' LIMIT 1")) {
				//print "cSURL($url) matched a site<br>\n";
				if ($returnguid) return $db->get_var("SELECT msv FROM shorturls WHERE shorturl='$url' LIMIT 1");
				return true;
			}
			return false;
		}
		
		//// This should go through all of the short/long URLs and add guids automatically...
		public function updateAllShortURLs(){
			global $db;
			
			// We need to get the longURL and drawLink() to match...
			if($urls = $db->get_results("SELECT * FROM shorturls WHERE guid<=''") ){
				foreach($urls as $url){
					// So now we have all of the unassigned urls...
					// ??????
				}
			}
		}
		
		
		public function setName($name) {
			$this->name = $name;
		}

		public function setTitle($title) {
			$this->title = $title;
		}

		public function setHidden($hidden) {
			$this->hidden = $hidden;
		}		

		public function setRobots($robots) {
			$this->robots = $robots;
		}		
		public function getRobots() {
			return $this->robots;
		}		

		public function setOffline($offline) {
			$this->offline = $offline;
		}		

		public function setPrivate($private) {
			$this->private = $private+0;
		}		
	
		public function setComment($comment) {
			$this->comment = $comment;
		}		
		public function getComment() {
			return $this->comment;
		}		

		public function setTemplate($template) {
			$this->template = $template;
			$this->template_id = $template;
		}	

		// 16/12/2008 Comment
		// Style of a page e.g. 1 column, 2 column etc
		public function getStyle() {
			return $this->style;
		}
		public function setStyle($style) {
			$this->style = $style;
		}		

		public function setMetaDescription($description) {
			$this->meta_description = html_entity_decode($description);
		}		
		public function setMetaKeywords($keywords) {
		 	$this->meta_keywords = $keywords;
		}								

		public function getMetaDescription() {
			return $this->meta_description;
		}		
		public function getMetaKeywords() {
			return $this->meta_keywords;
		}								
		
		public function getTemplate() {
			// This returns the template of a page e.g. page.php or sitemap.php
			return $this->template;
		}
		
		public function getPageTemplatesList(){
			global $db;
			$query = "SELECT * FROM pages_templates WHERE user_selectable = 1 AND template_id > 1 AND template_type=1";
			//print "$query<br>\n";
			$results = $db->get_results($query);
			return $results;
		}

		
		// 7/1/9 - Phil Redclift
		// Draw list of radio buttons with template images and allow event menu display with javascript
		public function drawRadioTemplatesList($exclude=array(), $selected_id=2){
			global $site;
			//print "dRTL(".print_r($exclude, true).", $selected_id)<br>\n";
			$html = '';
			if($results = $this->getPageTemplatesList()){
				if (!$site->getConfig("setup_members_area")) array_push($exclude, "Members only");
				if (!$site->getConfig("setup_events")) array_push($exclude, "Events page");
				foreach($results as $result){
					if (!in_array($result->template_title, $exclude)) {
						$selected=($result->template_id == $selected_id?' checked="checked"':"");
						$html .= '<div class="dtemplate" id="dtemplate'.$result->template_id.'">
<input type="radio" class="rtemplate" onchange="javascript:toggleEvent('.$result->template_id.');" name="template" id="rtemplate'.$result->template_id.'" value="'.$result->template_id.'" '.$selected.' />
<label class="rtemplate" for="rtemplate'.$result->template_id.'">'.$this->drawLabel("tl_p_".str_replace(" ", "-", $result->template_title), $result->template_title).'</label>
</div>
';
					}
				}
				
				return $html;
			}else{
				return false;
			}
		}

		public function drawTemplatesList($selected = NULL, $exclude=array()){
			//
			$html = '';
			if($results = $this->getPageTemplatesList()){
				foreach($results as $result){
					//print "check page (".$result->template_title.")<br>";
					//print_r($exclude);
					if (!in_array($result->template_title, $exclude)) {
						unset($preSelected);
						if($result->template_id == $selected){
							$preSelected = ' selected="selected"';
						}
			
						$html .= '<option value="'.$result->template_id.'"'.$preSelected.'>'.$result->template_title.'</option>'."\n";
					}
				}
				
				return $html;
			}else{
				return false;
			}
		}

		
		// Return a list of page styles valid for a given template
		public function getPageStyleList($template = null){
			global $db;
			if (!is_null($template)) {
				$template = " AND template = $template";
			}
			$query = "SELECT * FROM pages_style WHERE user_selectable=1".$template;
			//print "$query<br>\n";
			$results = $db->get_results($query);
			return $results;
		}

	// Draw a list of allowed styles for this page
	public function drawStyleList($selected = NULL, $template = null){

		$results = $this->getPageStyleList($template);
		
		$html = "\n";
		if($results){
			
			foreach($results as $result){
				unset($preSelected);
				unset($bgStyle);
				if($result->style_id == $selected){
					$preSelected = ' checked="checked"';
				}
				
				$bgStyle = '';
				$styleFile='/treeline/img/pages/'.$result->style_css.'.gif';
				if (file_exists($_SERVER['DOCUMENT_ROOT'].$styleFile)) {
					$bgStyle= ' style="background: url(\''.$styleFile.'\') 0 0 no-repeat; min-width: 102px;"';
				}
				else print "file $styleFile does not exist";
				
				$html .= '<div class="styleOption"'.$bgStyle.'><input type="radio" name="style" id="style_'.$result->style_id.'" value="'.$result->style_id.'"'.$preSelected.' class="checkbox style_radio" />
				<label for="style_'.$result->style_id.'">'.$this->drawLabel("tl_style_type_".str_replace(" ", "-", $result->style_title), $result->style_title).'</label></div>'."\n";
			}
		}
		
		return $html;
	}
		
		
	// Collect a list of additional CSS files required by this page type.
	public function drawStyleHeadLinks($selected = NULL){
		global $page;
		$results = $page->getPageStyleList();
		
		$html = '';
		
		if($results){
			
			foreach($results as $result){
				unset($preSelected);
				if($result->style_css != $selected && $result->style_id != $selected){
					$preSelected = 'alternate ';
				}
				$html .= '<link href="/style/'.$result->style_css.'.css" rel="'.$preSelected.'stylesheet" type="text/css" title="'.$result->style_title.'" media="screen, projection" id="CSS'.$result->style_id.'" />'."\n";
			}
		}
		
		return $html;
	}
		
		
	// Draw a combo box showing allowed styles for the current page
	public function drawStyleSwitcherMenu($selected = NULL, $template = null, $disabled=false){
		//global $page;
		//print "dSSM($selected, $template, $disabled)<br>\n";
		if ($disabled) return '';
		//$results = $page->getPageStyleList($template);
		$results = $this->getPageStyleList($template);
	
		$html = "\n";
		$xname = '';
		if ($template == 6) $xname = "-".$this->getGUID();
		if($results){
			$html .= '<fieldset id="styleSwitcher">'."\n";
			$html .= '<select name="style'.$xname.'" id="style">'."\n";
			foreach($results as $result){
				unset($preSelected);
				if($result->style_id == $selected){
					$preSelected = ' selected="selected"';
				}
				$label_id = "tl_toolbs_".str_replace(" ", "-", $result->style_title);
				$style_title = $this->getLabel($label_id, true);
				if (!$style_title) $style_title = $this->drawLabel($label_id, $result->style_title);
				$html .= '<option value="'.$result->style_id.'"'.$preSelected.'>'.$style_title.'</option>'."\n";
			}
			$html .= '</select>'."\n";
			$html .= '</fieldset>'."\n";
		}
		return $html;
	}


	public function drawToolbar($pagestyle='', $disableStyles, $toolmode="page") {
		//print "drawToolBar($pagestyle, $disableStyles, $toolmode)<br>\n";
		$template=2;
		$helptitle="Page edit toolbar";
		
		
		if ($toolmode=="panel") {
			$template=6;
			$helptitle='Panel edit toolbar';
		}
		else if ($toolmode=="landing") {
			$template=67;
		}
		
		$html = '
<div id="toolbar">
	<div class="logo"><a href="/treeline/"><img src="/treeline/img/logo.gif" alt="Treeline" /></a></div>
	<a href="javascript:openhelp(\''.Help::helpLinkByTitle($helptitle).'\');" style="float:right;margin:20px;">'.$this->getLabel("tl_help_get_help", true).'</a>
	<div id="toolbar_container">
		<div class="options" style="'.($disableStyles?"visibility:hidden":"").';width:200px;">
			<p>'.ucfirst($this->getLabel("tl_toolb_change_style", true)).'</p>
			'.($disableStyles?"":$this->drawStyleSwitcherMenu($pagestyle, $template)).'
		</div>
		<div class="options" id="toolbar-quick-links" style="">
			<p>'.ucfirst($this->getLabel("tl_toolb_quick", true)).'</p>
			<a class="tl_link" href="javascript:alert(\'Inline asset create\n\nThis function is not yet supported.\');">'.$this->getLabel("tl_toolb_create_ass", true).'</a>
			'.($toolmode=="page"?'<a class="tl_link" href="javascript:panelManager(\''.$this->getGUID().'\');">'.$this->getLabel("tl_toolb_man_panels", true).'</a>':"").'
			<a class="tl_link" href="javascript:editorNotes(\''.$this->getGUID().'\');">'.$this->getLabel("tl_toolb_editor_note", true).'</a>
		</div>
		<div class="options" style="float:right;">
			<p>'.$this->getLabel("tl_toolb_manage_title", true).'</p>
			<input type="button" class="button cancel" name="treeline" value="'.$this->getLabel("tl_toolb_but_discard", true).'" id="btn_discard" onclick="javascript:setTarget(0);setAction(\'Discard changes\')" />
			<input type="button" class="button cancel" name="treeline" value="'.ucfirst($this->getLabel("tl_generic_preview", true)).'" id="btn_preview" onclick="javascript:setTarget(1);setAction(\'Preview\')" />
			<input type="button" class="button" name="treeline" value="'.ucfirst($this->getLabel("tl_generic_save_changes", true)).'" id="btn_save" onclick="javascript:setTarget(0);setAction(\'Save changes\')" />
		</div>
	</div>
	<!-- <div class="clear">&nbsp;</div> -->
</div>
<script type="text/javascript">
	function setTarget(blank) {
		var f=document.forms[0];
		if (blank) f.target="_blank";
		else f.target="_self";
	}
	function setAction(a) {
		var f = document.getElementById(\'treeline_edit\');
		//alert("set post("+f+") act("+a+")");
		if (f) {
			f.post_action.value=a;
			//alert("submitting");
			f.submit();

			document.forms[0].target="_self";
			f.post_action.value="";
			return true;		
		}
		return false;
	}
	
</script>

';
//		print "done toolbar<BR>\n";
		return $html;
	}
	public function drawPanelToolbar() {
	
		// This should definitely be part of the treeline class
		$html = '';
		$html .= '<div id="toolbar">'."\n";
		$html .= '	<div id="toolbar_container">'."\n";
		$html .= '	<div class="logo"><a href="/treeline/"><img src="/treeline/img/logo.gif" alt="Treeline" /></a></div>'."\n";
		$html .= '	<div class="options">'."\n";
		$html .= '		<input type="submit" class="button" name="treeline" id="treeline" value="Save changes" />'."\n";
		$html .= '		<input type="submit" class="button cancel" name="treeline" id="treeline" value="Discard changes" />'."\n";			
		$html .= '	</div>'."\n";
		$html .= '	</div>'."\n";
		$html .= '	<div class="clear"></div>';
		$html .= '</div>';
		return $html;
	}		
		
		
		public function getGUID() {
			// This returns the GUID of a page
			return $this->guid;
		}
		public function setGUID($guid){
			$this->guid = $guid;
		}
		public function setLocked($locked) {
			$this->locked = $locked;
		}
		public function getLocked($locked) {
			return $this->locked;
		}		
		public function getDateModified() {
			// This returns the date_modified of a page
			return $this->date_modified;
		}
		public function setDateModified($date){
			$this->date_modified = $date;
		}		
		public function getDatePublished() {
			// This returns the date_published of a page
			return $this->date_published;
		}
		public function setDatePublished($date){
			$this->date_published = $date;
		}
		
		public function getName() {
			// This returns the name of a page
			return $this->name;
		}	
		public function getParentName() {
			// This returns the name of the parent of a  page
			$parent = new Page();
			$parent->loadByGUID($this->parent);
			return $parent->name;
		}			

		public function getTitle() {
			// This returns the title of a page
			return $this->title;
		}
		public function drawTitle() {
			// This returns the title of a page
			return $this->title;
		}	
		public function drawPageTitle($pagename, $action) {
			// Page title	
			$title =  ucfirst($this->drawLabel("tl_generic_".$pagename, ucfirst($pagename)));
			$title .= ($action)?' : '.ucfirst($this->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action)))):'';
			return $title;
		}
		public function drawGeneric($title, $ucfirst=0) {
			//print "dG($title, $ucfirst)<br>\n";
			$label = $this->drawLabel("tl_generic_".$title, $ucfirst?ucfirst($title):$title);
			//print "got label($label)<br>\n";
			if ($ucfirst) $label[0] = mb_strtoupper($label[0]); 
			//print "return ($label)<br>\n";
			return $label;
		}					
		
		//// pagination get/sets
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
		public function getPage(){
			return $this->page;
		}
		public function setPage($page){
			$this->page = $page;
		}
		public function setTotalPages($total){
			$this->totalpages = ceil($this->getTotal()/$this->getPerPage());
		}
		public function getTotalPages(){
			return $this->totalpages;
		}
		
		public function setSiteID($id){
			$this->siteID=$id;
		}
		public function getSiteID(){
			return $this->siteID;
		}

		public function getLanguage(){
			return $this->lang;
		}


		public function getPageType(){
			return $this->type;
		}
		
		public function setPageType($type){
			$this->type = $type;
		}

		// 16/12/2008 Phil Redclift
		// Obtain a lock on a set page
		public function getLock($user_id, $guid) {
			global $db;
			if ($user_id>0 && $guid>'') {
				
				// Check this page is not already locked by anyone else
				$query = "SELECT u.full_name FROM users u
					WHERE u.lock_guid='$guid' AND u.lock_time > NOW() - INTERVAL 1 HOUR
					AND u.id != $user_id";
				//print "$query<br>\n";
				if (($lock_user=$db->get_var($query))>'') {
					return $lock_user;
				}
				
				// Attempt to get a lock
				$query = "UPDATE users SET lock_guid='$guid', lock_time=NOW() where id=$user_id";
				//print "$query<br>\n";
				if ($db->query($query)) return 0;
				else return "LF - error code: 332";
			}
			return "LF - error code: 942";
		}
		// 16/12/2008 Phil Redclift
		// Release a users lock on a page by deleting the lock guid 
		public function releaseLock($user_id) {
			global $db;
			$query = "UPDATE users SET lock_guid='' WHERE id=$user_id";
			//print "$query<br>\n"; 
			if ($db->query($query)) {
				$_SESSION['treeline_user_lock_guid']='';
				return true;
			}
			return false;
		}
		// 22nd Jan 2009 - Phil Redclift
		// Check if a particular guid has been locked for edit (by a set user)
		public function LockedForEdit($guid, $user_id=0) {
			global $db;
			$query="SELECT id FROM users WHERE lock_guid='$guid' ";
			if ($user_id>0) $query.="AND id=".$user_id;
			return $db->get_var($query);
		}
		
		// returns an array of all of the frequently used terms used on the site in the language used by this page
		public function getPageLabels( $lang='en' ){
			global $db,$site;
	
			$query = "SELECT l.shortname,lt.longname 
						FROM labels_translations lt 
						LEFT JOIN labels l ON lt.label_id=l.id 
						WHERE lt.lang='". $lang ."'"; 
	
			if( $results = $db->get_results($query) ){
				$tmp = array();
				foreach($results as $item){
					//$tmp[$item->shortname] = htmlentities($item->longname, ENT_QUOTES, $this->encoding);
					$tmp[$item->shortname] = $item->longname;
				}
				return $tmp;
			}else{
				return false;
			}
		}



		public function drawTitleByGUID($guid) {
			// This returns the title of the page specified by GUID
			global $db;
			$data = $db->get_var("SELECT title FROM pages WHERE guid = '$guid'");
			return $data;
		}				
		
		public function getPermissions($group) {
			global $db, $siteID;
			
			$query="SELECT level FROM permissions WHERE guid = '{$this->guid}' AND `group` = $group";
//			print "$query<br>";
			$data = $db->get_row($query);

			if ($db->num_rows > 1) {
				die("Fatal error at line " . __LINE__ . " of file " . __FILE__);
			}
	
			if ($db->num_rows == 0) {
				// If we can't find a level for this guid, we work up the tree to find the permissions level of the parent
				// We should always find a level somewhere, as permissions should be set on root for all groups
				
				// Establish the parent of the current page
				$parent = $this->getParent();
//				print "start at parent($parent)<br>";
				if (!$parent) $lastparent=$siteID;

				while (!isset($data->level) && $parent>0) {
					// Find the permissions of the current parent
					$query="SELECT level FROM permissions WHERE guid = '$parent' AND `group`= $group";
//					print "$query<br>";
					$data = $db->get_row($query);
					// If we don't find any, we're still in the loop, so set the new parent to be the parent of the page we've just checked
					$lastparent=$parent;
					$parent = $this->getParentByGUID($parent);
				}
				
				// If !parent && $lastparent
				// Language microsites inherit the master sites permissions...
				if (!$parent && $lastparent) {
				
					$query="SELECT level FROM permissions p 
						LEFT JOIN sites m on p.guid=m.primary_msv
						LEFT JOIN sites_versions msv on msv.microsite=m.microsite
						WHERE msv.msv = '$lastparent' AND p.`group` = $group";
//					print "$query<br>";
					$data = $db->get_row($query);
				}
			}
			return $data->level;
		}
		
		public function checkPermissions($action) {
			// This function checks what we're trying to do with the page -- view, edit etc -- and checks we're allowed to do it
			// It then sets 'mode' accordingly.

			$userID = read($_SESSION,'userid',0);
			$user = new User();
			$user->loadById($userID);

			if ($user->getStatus() != 'logged in') {
//				echo "NOT LOGGED IN: " . $user->getStatus() . $user->getID() ."<br />";
				$this->mode = 'view';
			}	
			else {
				//echo "LOGGED IN<br />";

				$level = $this->getPermissions($user->getGroup());
				
				//echo "LEVEL: " . $level ."<br />";
				
				define('READ', 1);
				define('WRITE', 2);
				define('DELETE', 4);
				define('PUBLISH', 8);
			
				$canread = ($level & READ) == READ ? true : false;
				$canwrite = ($level & WRITE) == WRITE ? true : false;
				$canpublish = ($level & PUBLISH) == PUBLISH ? true : false;		
			
				if ($action == 'view' || $action == 'preview') {
					if ($canread) {
						$this->mode = $action;
					}
					else {
						trigger_error("Permission denied: no read access on page {$this->getGUID()} for user {$user->getID()}");
					}
				}
				else if ($action == 'create' || $action == 'edit') {
					if ($canwrite) {
						$this->mode = $action;
					}
					else {
						trigger_error ("Permission denied: no write access on page {$this->getGUID()} for user {$user->getID()}");
					}
				}
				else if ($action == 'save') {
					if ($canwrite) {
						$this->mode = 'save';
						// Write to the database here
						// no -- hang on -- surely we write to the database within page.php itself?
						// How would this class know what to do with the content being posted?
					}
					else {
						trigger_error ("Permission denied: no save [write] access on page {$this->getGUID()} for user {$user->getID()}");
					}
				}
				else if ($action == 'publish') {
					// Do we publish from the page directly? Or not?
					// See above -- I think we do, but I think we have to do so from within page.php and not this class
					if ($canpublish) {
						$this->mode = 'publish';
						// update the database here
					}
					else {
						trigger_error ("Permission denied: no publish access on page {$this->getGUID()} for user {$user->getID()}");
					}
				}	
				else if ($action == 'restore') {
					$this->mode = 'restore';
				}
				else {
					$this->mode = 'view';
				}
				
			}
		}
		
		public function setMode($mode) {
			$this->mode = $mode;
		}
		
		public function getMode() {
			return $this->mode;
		}		
		
		public function getParent() {
			// This returns the parent of a page as a GUID
			return $this->parent;
		}
		
		public function drawParent() {
			// This returns the parent of a page as a NAME
			global $db;
			$data = $db->get_var("SELECT title FROM pages WHERE guid = (SELECT parent FROM pages where guid = '{$this->guid}')"); 
			return $data;
		}					
			
		public function getParentByGUID($guid) {
			// This returns the parent of the page specified by the GUID
			global $db;
			$query = "SELECT parent FROM pages WHERE guid = '$guid'";
			$data = $db->get_var($query);
			return $data;
		}

		public function getChildByGUID($guid) {
			// This returns the parent of the page specified by the GUID
			global $db;
			$data = $db->get_var("SELECT guid FROM pages WHERE parent = '$guid'");
			return $data;
		}
		
		public function getChildrenByParent($parent, $offline=false) {
			// This returns the grandparent of the page specified by the GUID
			global $db;
			$query = "SELECT guid, title FROM pages WHERE parent = '$parent' ";
			if (!$offline) $query.="AND offline=0";
			//print "$query<br>\n";
			return $db->get_results($query);
		}		

		public function getGrandparent() {
			// This returns the grandparent of a page as a GUID
			global $db;
			$data = $db->get_var("SELECT parent FROM pages WHERE guid = (SELECT parent FROM pages where guid = '{$this->guid}')"); 
			return $data;
		}
		
		public function drawGrandparent() {
			// This returns the grandparent of a page as a NAME
			global $db;
			$data = $db->get_var("SELECT title FROM pages WHERE guid = {$this->getGrandparent()}'"); 
			return $data;
		}					
			
		public function drawGrandparentByGUID($guid) {
			// This returns the grandparent of the page specified by the GUID
			global $db;
			$data = $db->get_var("SELECT parent FROM pages WHERE guid = '{$this->getParentByGUID($guid)}'");
			return $data;
		}		
		
		public function getPrimary($guid = false) {
			// This returns the PRIMARY CATEGORY of a page as a GUID
		   	// ie, the 'section' this page belongs to, the first page under root -
		   	global $db, $site;
			$site_id=$site->id?$site->id:$_SESSION['treeline_user_site_id'];
		   	$guid  = ($guid) ? $guid : $this->guid;
		   
		   	$return='';
		  	//print "getting primary to $site_id<br>"; exit();
		 	if($data = $db->get_row("SELECT parent FROM pages WHERE guid = '{$guid}'")){
				while ($data->parent != $site_id && $data->parent) {
				 	$return = $data->parent;
				 	$data = $db->get_row("SELECT parent FROM pages WHERE guid = '{$data->parent}'");
				}
				return $return;
		   	}
		} 
 
 
 
		
		
		public function getDescendentsByGUID($guid) {
			// This returns the guids of ALL the descendents of the page specified by the GUID, in an array
			global $db; //, $page;
			$array = array();
			if ($rows = $db->get_results("SELECT guid FROM pages WHERE parent = '$guid'")) {
				foreach ($rows as $row) {
					array_push($array,$row->guid);
					$array = array_merge($array,$this->getDescendentsByGUID($row->guid));
				}
			}
			return $array;
		}
		
		public function getSectionByPageGUID($guid){
			// This enables us to determine which section a page belongs to no matter what the depth...
			global $db;
			
			// so we need to recursively get the parent of the page and find the parent of that page
			// this could be any depth, so we stop before we get to '1' - the homepage...
			if($guid){
				$complete=false;
				while(!$complete){
					//$guid = $this->getParentByGUID($guid);
					$query = "SELECT parent,msv FROM pages WHERE guid='". $guid ."'";
					$data = $db->get_row( $query );
					//echo '<br /><br />results: '. print_r($data,true) .' <br />query: '. $query ."<br /><br />";
					if($data->parent===$data->msv){
						$complete=true;
					}else{
						$guid = $data->parent;
					}
					
				}
				//return $this->getChildByGUID($guid);
				return $guid;
			}else{
				return false;
			}
		}
		


		public function getSiteGUID() {
			// This function returns the GUID of the site to which the page belongs, whether that's a microsite or root
			global $db;
			if ($site = $db->get_var("SELECT guid FROM sites WHERE guid = '{$this->guid}'")) {
				return $site;
			}
			else {
				if ($this->parent == 0 || $this->parent == 1) {
					return 1;
				}
				else {
					$parent = new Page;
					$parent->loadByGUID($this->parent);
					$this->siteID = $parent->getSiteGUID();
					return $this->siteID;
				}
			}
		}


		public function getSiteRootGUID($thispage = '',$type=false) {
			// type was adde to throw a switch if it's a news page to return the news section guid for a site/mircosite...
			if(!$thispage){ $thispage= $this->guid;}
			// This function returns the root GUID of the site to which the page belongs, whether that's a microsite or root
			// The two functions below seem like they could be amalgamated and a switch could be thrown in to return guid, name or title...
			global $db;
			//$newsquery = "SELECT guid,name FROM pages WHERE name='news' AND parent=''";

			if ($row = $db->get_row("SELECT pages.guid,pages.name FROM sites, pages WHERE sites.guid = pages.guid && pages.guid = '".$this->guid."'")) {
				// Is this the microsite 'homepage'?
				if($type=='news'){
					$newsguid =  $db->get_row("SELECT guid,name FROM pages WHERE name='news' AND parent='".$row->guid."'");
					return $newsguid->guid;
				}else{
					return $row->guid;
				}
			}
			else {
				if ($this->parent == 0 || $this->parent == 1) {
					if($type=='news'){
						$newsguid =  $db->get_row("SELECT guid,name FROM pages WHERE name='news-and-views' AND parent='1'");
						// through hardcoding, it's not that flexible but using LIKE seemed to open to failure
						return $newsguid->guid;
					}else{
						return 1;
					}
				}
				else {
					$parent = new Page;
					$parent->loadByGUID($this->parent);
					return $parent->getSiteRootGUID($thispage,$type);
				}
			}
		}


		
		public function getSiteRoot($thispage = '') {
			if(!$thispage){ $thispage= $this->guid;}
			// This function returns the root folder of the site to which the page belongs, whether that's a microsite or root
			global $db;
			if ($row = $db->get_row("SELECT pages.name, pages.guid FROM sites, pages WHERE sites.guid = pages.guid && pages.guid = '".$thispage."'")) {
				// Is this the microsite 'homepage'?
				return '/'.$row->name.'/';
			}
			else {
				if ($this->parent == 0 || $this->parent == 1) {
					return '/';
				}
				else {
					$parent = new Page;
					$parent->loadByGUID($this->parent);
					return $parent->getSiteRoot();
				}
			}
		}
		
		public function getSiteName() {
			// This function returns the name of the site to which the page belongs, whether that's a microsite or root
			// This is very similar to the above function, and the two could probably be merged in a much better way
			global $db;
			if ($row = $db->get_row("SELECT pages.name, pages.guid FROM sites, pages WHERE pages.lang='". $_COOKIE['lang'] ."' AND sites.guid = pages.guid && pages.guid = '{$this->guid}'")) {
				// Is this the microsite 'homepage'?
				return $row->name;
			}
			else {
				if ($this->parent == 0 || $this->parent == 1) {
					return 'Root';
				}
				else {
					$parent = new Page;
					$parent->loadByGUID($this->parent);
					return $parent->getSiteName();
				}
			}
		}






		
					
		
		public function drawContent($placeholder) {
			// This returns the content of a page's placeholder
			// NOTE that this only returns the current content for now -- we'll need to change revision_id to '1' if we're editing a page
			global $db;
			$data = $db->get_var("SELECT content FROM content WHERE parent = '".$this->guid."' AND revision_id = 0 AND placeholder = '$placeholder'"); 

			return $data;
		}				
		
		public function getLink() {
			// gets the URL of the current page
			// This is currently identical to drawLink()
			// but that's designed to return HTML, and may in the future contain formatting etc
			global $db;
			$data = $db->get_row("SELECT parent,name FROM pages WHERE guid = '{$this->guid}'");
			$location = array();
			$html = '';
			while ($data->parent != 0) {
				$html = '/'.$data->name.$html;
				$data = $db->get_row("SELECT parent,name FROM pages WHERE guid = '{$data->parent}'");
			}
			return $html . '/'; // .html removed and replaced by a /
		}

		public function drawLink() {
			// draws a link to the current page
			return $this->drawLinkByGUID($this->guid);
			
			global $db;
			$data = $db->get_row("SELECT parent,name FROM pages WHERE guid = '{$this->guid}'");
			$location = array();
			$html = '';
			if ($data->parent == 0) {
				//$html .= '/'.$data->name.$html;
				$html = '';
			}
			else {
				while ($data->parent != 0) {
					$html = '/'.$data->name.$html;
					$data = $db->get_row("SELECT parent,name FROM pages WHERE guid = '{$data->parent}'");
				}
			}
			return $html . '/'; // .html removed and replaced by a /
		}

		public function drawLinkByGUID($guid) {
			// draws a link to the page specified by the GUID
			global $db, $site, $siteLink, $http;
			$query = "SELECT p.parent, p.name FROM pages p WHERE guid = '$guid'";
			$data = $db->get_row($query);
			//echo "SELECT parent,name FROM pages WHERE guid = '$guid'<br />";
			$location = array();
			$html = '';
			
			// Its a bit messy but make sure we don't have any pages that are their own parent.
			if ($data->parent == $guid) {
				$query = "UPDATE pages SET parent='".($site->id+0)."' WHERE guid='$guid'";
				//print "$query<br>\n";
				$db->query($query);
			}
			
			if ($data->parent == 0 && $guid != 1) {
				// if this is the homepage
				$html = $data->name."/";
			}
			else {
				while ($data->parent != 0 && $data->parent!=$guid) {
					$html = $data->name."/".$html;
					$data = $db->get_row("SELECT parent,name FROM pages WHERE guid = '{$data->parent}'");
					//echo "SELECT parent,name FROM pages WHERE guid = '{$data->parent}'<br />";
				}
			}
			//print_r($data);
			//echo "<!-- html - $html -->\n";
			//echo "<!-- site link ($siteLink) -->\n";
			if ($siteLink) $microsite=$siteLink;
			//else $microsite="http://".$_SERVER['SERVER_NAME']."/".$_SESSION['treeline_user_site_name']."/".$_SESSION['treeline_user_language']."/";
            else $microsite=$http."://".$_SERVER['SERVER_NAME']."/".$_SESSION['treeline_user_site_name']."/".$_SESSION['treeline_user_language']."/";
			if (substr($html, -1, 1)!="/") $html.="/";
			$new_link=$microsite.$html;
			//print "<!-- got new link($new_link) last2(".substr($new_link, -2, 2).") -->\n";
			// Sometimes end up with an extra / on the end of the home page.
			if (substr($new_link, -2, 2)=="//") $new_link=substr($new_link, 0, -1);
			return $new_link;
		}
		
		public function generateName() {
			// Generates a "friendly" page name from $title
			// checking that there are no existing pages with the same name and parent
			// If we're generating a name for a page that already exists [eg, if we're editing a page]
			// we need to make sure that we don't return false by mistake --
			// ie, that we don't decide that the new name already exists because THIS page has that name
			global $db, $site;
			$msg="generateName(".$this->title.") enc(".$site->properties['encoding'].") \n";
			$title = $db->escape($this->title);			
			$msg.="entities (".htmlentities($title,ENT_QUOTES,$site->properties['encoding']).")\n";
			$query="SELECT * FROM pages WHERE title = '$title' AND parent = '{$this->parent}'";
			$msg.="genName($query) \n";
			$data = $db->get_row($query);
			$msg.="Found guid(".$data->guid.":".$this->guid.") \n";
			if ($db->num_rows > 0 && $data->guid != $this->guid) {
				$msg.="returning false.. (".$db->num_rows.")\n";
				$ret=false;
			}
			else {
				// Strip everything but letters, numbers and spaces from the title
				$tmp = $title;
				if ($_SESSION['treeline_user_language']=="ar") $tmp = UTF_to_Unicode($tmp);
				else if ($_SESSION['treeline_user_language']=="ja") $tmp = UTF_to_Unicode($tmp);
				//else $tmp=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $tmp);
				//$name = preg_replace("/[^A-Za-z0-9 ]/", "", $tmp);
				// Replace spaces with dashes
				//$name = str_replace(" ",'-',$name);
				$name = _generateName($tmp);
				$msg.="created name(".$name.") from title($title) \n";
				if ($name && str_replace("-", "", $name)!="") {
					$this->name = strtolower($name);
					$msg.="set name to(".$this->name.") \n";
					$ret=true;
				}
				else {
					$msg.="returning false no name generated \n";
					$ret=false;
				}
			}
			//print $msg;
			if (0 && $msg) mail("phil.redclift@ichameleon.com", $site->name." arabic generate name", $msg);
			return $ret;
		}	
		
		public function drawHeader() {
			// This just returns the standard page-header
			// Is there scope for using Smarty or similar here?
			// Note that we don't display the <form> for the search box if we're editing a page, because we end up with nested <form>s
			$html = '';
			return $html;
		}
		
		public function drawFooter() {
			// This just returns the standard page-footer
			// Is there scope for using Smarty or similar here?
			$html = '';
			return $html;
		}
		
		public function drawMeta($name) {
			// This just meta-data for the page
			// Is there scope for using Smarty or similar here?
			if ($name == 'description') {
				return $this->meta_description;
			}
			else if ($name == 'keywords') {
				return $this->meta_keywords;
			}
			else {
				return '<!-- Unknown "meta" data -->';		
			}
		}		


		/*
		   drawBreadcrumb
		   // create a breadcrumb string of current page and it's parents  
		*/
		public function drawBreadcrumb($guid = false){
		   global $db, $siteID, $page;
		   $guid = ($guid) ? $guid : $this->guid;
		   $html = '';
		   $link = array();
		   
		   	// current page
		   	$link[] = $this->drawTitleByGUID($guid);
		   
		   	// add all precedings parents to the array
			while($parent = $this->getParentByGUID($guid)){
				$pagetitle=$this->drawTitleByGUID($parent);
				$pagetitle=(strtolower($pagetitle)=="home")?$page->drawLabel("home", "Home"):$pagetitle;
				$link[] = '<a href="'. $this->drawLinkByGUID($parent) .'">'.ucfirst($pagetitle).'</a>';
				$guid = $parent;
		   	}
		   
		   	if($link){
				krsort($link);
		   	}
		 
		   	// Add a home link
			$last = sizeof($link);
			$pagetitle=$this->drawTitleByGUID($siteID);
			$pagetitle=(strtolower($pagetitle)=="home")?$page->drawLabel("home", "Home"):$pagetitle;
		   	$link[$last-1] = '<a href="'. $this->drawLinkByGUID($siteID) .'">'.$pagetitle.'</a>';
		   	// this is a microsite so remove the first (duplicate) link
		   	if($link[$last-1] == $link[$last-2]){
				unset($link[$last-1]);
		   	}

		   	$html = join(' &gt; ',$link); // add the separator
		   
		   	return $html;  
		}

		public function drawFullTitle(){
			global $db;
			$guid = $this->guid;
			$html = '';
			$link = array();
			
			while($parent = $this->getParentByGUID($guid)){
				$link[] = $this->drawTitleByGUID($parent).' - ';
				///if($this->getParentByGUID($parent)){
					$guid = $parent;
				//}
			}
			
			if($link){
				krsort($link);
			}
				$link[] = $this->drawTitleByGUID($this->guid);
			
				foreach($link as $line){
					$html .= $line;
				}
			return $html;		
		}



		// Slightly message 3 stage process
		// First checks if this microsite has button translations
		// Next checks if the default microsite has button translations
		// Finally pull the standard english translations if the above 2 do not yield results
		public function _getTranslations($msv, $primary_msv, $charset) {
			global $db, $testing;
			$labels=array();
			$testing = false;
			// Collect tranlsations
			$query="select shortname, lt.longname as translation from labels l left join labels_translations lt on l.id=lt.label_id where lt.msv=$msv";
			if ($testing) print "$query<br>";
			$results=$db->get_results($query);
			if (!$results) {
				if ($msv!=$primary_msv) {
					$query="select shortname, lt.longname as translation from labels l left join labels_translations lt on l.id=lt.label_id where lt.msv=$primary_msv";
					if ($testing) print "$query<br>";
					$results=$db->get_results($query);
				}
				if (!$results) {
					$query="select shortname, longname as translation from labels l";
					if ($testing) print "$query<br>";
					$results=$db->get_results($query);
				}
			}
			foreach($results as $result) {
				$translation=$result->translation?$result->translation:$result->english;
				$labels[$result->shortname]=htmlspecialchars($translation, ENT_QUOTES, $charset);
			}
			if ($testing) print_r($labels);
			return $labels;
		}
		
		
		// 18/12/2008 Phil Redclift
		// Collect all translations required by this site.
		//
		// First collect the default english translations
		// Next overwrite these with default translations for the current language (if not english)
		// Next overwrite these with actual translations for the current microsite (if not 1)
		public function getTranslations($msv, $default_lang='', $system=-1) {
			global $db;
			$testing=false;
			//$testing=true;
			$labels=array();
			if ($testing) print "getTrans($msv, $default_lang, $system)<br>";

			// 1 - Collect standard translations in english
			$query="select id, shortname, longname, longname as eng from labels WHERE ".(($system>-1)?"system=$system":"system<2")." order by longname";
			if ($testing) print "$query<br>\n";
			$labels=$this->getLabels($db->get_results($query), $labels);
			
			// 2 - See if we have default site translations for any labels
			//if ($default_lang!="en") {
				$query="select shortname, ld.longname from labels l left join labels_default ld on l.id=ld.label_id where ld.lang='$default_lang' AND ".(($system>-1)?"system=$system":"system<2")." order by l.longname";
				if ($testing) print "$query<br>\n";
				$labels=$this->getLabels($db->get_results($query), $labels, 'default');
			//}
			
			// 3 - Get microsite version specific labels.	
			// Dont do this if we are running for msv=1 as msv 1 has no translations and is allowed to modify defaults only.
			//if ($msv!=1 || $system==2) {
				$query="select shortname, lt.longname from labels l left join labels_translations lt on l.id=lt.label_id where lt.msv=$msv AND ".(($system>-1)?"system=$system":"system<2")." order by l.longname";
				if ($testing) print "$query<br>\n";
				$labels=$this->getLabels($db->get_results($query), $labels, 'site');
			//}

			if ($testing) print_r($labels);
			return $labels;
		}
		
		public function getLabels($results, $labels, $level="label") {
			global $siteData;
			if (!is_array($results)) return $labels;
			//$encoding=$siteData->encoding;
			//print "got encoding(".$siteData->encoding.")<br>";
			foreach($results as $result) {
				//$labels[$result->shortname]=html_entity_decode($result->longname, ENT_QUOTES, $encoding);
				$label_id = strtolower($result->shortname);
				if ($result->eng) {
					$labels[$label_id]['eng']=$result->eng; 
					$labels[$label_id]['id']=$result->id;
				}
				$labels[$label_id][$level]=html_entity_decode($result->longname, ENT_QUOTES);
				//print "label[".$result->shortname."] = ".$labels[$result->shortname]." long(".$result->longname.") enc($encoding)<br>"."\n";
			}
			return $labels;
		}
		
		// Get a single label translation from the database
		public function getLabel($label, $default_only=false) {
			global $db, $site;
			if ($_SESSION['treeline_language']) $lang = $_SESSION['treeline_language'];
			else if ($_SESSION['treeline_user_language']) $lang = $_SESSION['treeline_user_language'];
			
			if ($lang && $label) {
				if ($row = $db->get_row("SELECT id, longname FROM labels WHERE shortname='$label'")) {
					$label_id = $row->id;
					$english = $row->longname;
					$query = "SELECT longname FROM labels_default WHERE label_id = $label_id AND lang = '$lang'";
					//print "$query<br>\n";
					$default = $db->get_var($query);
					if (!$default_only) {
						$query = "SELECT longname FROM labels_translations WHERE label_id = $label_id AND msv=".$site->id;
						//print "$query<br>\n";
						$sitelabel = $db->get_var($query);
					}
					$r = $sitelabel?$sitelabel:($default?$default:($english?$english:$label));
					//print "return $r<br>\n";
					return $r;
				}
			}
			return 0;
		}
		
		public function drawLabel($label, $default='') {
			global $labels, $db;
			//print "dL($label, $default)<br>\n";
			$label_id = strtolower($label);
			if (strlen($label_id)>30) $label_id = substr($label_id, 0, 30);

			//if ($default=="pending") print_r($labels);
			if ($labels[$label_id]['site']) $r = $labels[$label_id]['site'];
			else if ($labels[$label_id]['default']) $r = $labels[$label_id]['default'];
			else if ($labels[$label_id]['label']) $r = $labels[$label_id]['label'];
			
			//print "dL($label, $default = $r)<br>\n";
			if ($r) return $r;
			//print "Failed to find label($label)<br>\n";
			$msg = "Failed to translate the label($label) Just going to send back($default)<br>\n";
			// Check label does not exist and create it if not
			if ($default) {
				if (!$db->get_var("SELECT id FROM labels WHERE shortname = '$label_id'")) {
					if (substr($label,0,3)=="tl_") {
						$query = "INSERT INTO labels 
							(shortname, longname, comment, system)
							VALUES 
							('$label_id', '".$db->escape($default)."', 'Added automatically', 2)
							";
					}
					else {
						$query = "INSERT INTO labels 
							(shortname, longname, comment, system)
							VALUES 
							('$label_id', '".$db->escape($default)."', 'Added by website', 0)
							";
					}
					$msg.="$query\n";
					if (!$db->query($query)) $msg.="Failed to add new default translation\n";
					//mail("phil.redclift@ichameleon.com", "TL failed to get a label", getcwd()."\n\n".$msg);
				}
				return $default;
			}
			return false;
		}
		
		
		public function getLanguageList($orderby=false){
			global $db;
			
			$orderby = ($orderby) ? ' ORDER BY '. $orderby : 'ORDER BY sort_order';
			$query="SELECT * FROM languages WHERE user_selectable=1 ".$orderby;			
			if( $list = $db->get_results($query) ){
				return $list;
			}else{
				return false;
			}
		
		}
		
		public function getLanguageTitle($abbr,$version=''){
			global $db;
			if ($abbr) $where="language='$abbr'";
			if ($version) $where="msv=$version";
			$query="SELECT title FROM sites_versions sv LEFT JOIN languages l ON sv.language=l.abbr WHERE sv.".$where;
			return $db->get_var($query);
		}

		public function getCurrentLanguageVersions() {		
			global $db, $site;
			$query="select language from sites_versions where microsite=".$site->properties['microsite'];
			$results=$db->get_results($query);
			foreach($results as $result) $currentLanguageVersions[]=$result->language;
			return $currentLanguageVersions;
		}
		
		//// draw a select box filled with available languages...
		// - we may want to limit to the languages available to a specific site...
		//public function drawSelectLanguages($name, $language, $exclude=array('en')){
		public function drawSelectLanguages($name, $language, $exclude=array(), $add_select=true){
			global $siteID,$site;
			// using $admin=true states that we want to use it differently in Treeline

			if( $list = $this->getLanguageList() ){

					
				foreach( $list as $item ){
					$class = ( $language==$item->abbr ) ? ' selected="selected"' : '';
					if( !in_array($item->abbr,$exclude) ){
						$html .= "\t".'<option value="'. $item->abbr .'"'. $class .'>'. $item->title . ( ($item->title!='English') ? ' ('. $item->title_local .')' :'') .'</option>'."\n";
					}
				}

				if ($add_select) {
					if ($html) {
						$html = '<select name="'. $name .'" id="'. $name .'">'."\n\t".'<option value="">'.$this->drawLabel("tl_lang_select", "Select a language").'</option>'."\n".$html;
						$html .= '</select><br />'."\n";			
					}
					else $html = '<p style="float:left;">'.$this->drawLabel("tl_lang_err_none", "No selectable languages found")."</p>";
				}
				
				
				return $html;
					
			}else{
				return false;
			}
			
		}
		
		public function drawAvailableLanguages($name, $exclude=array()) {
			global $siteID, $site, $db;
			$query="select msv, language, title, title_local from sites_versions sv
				left join languages l on sv.language = l.abbr 
				where microsite=".$site->properties['microsite'];
			if (count($exclude)) $query.=" and msv not in(".join(",",$exclude).")";
			//print "$query<br>";
			if ($list = $db->get_results($query)) {
				foreach( $list as $item ){
					$html .= '	<option value="'.$item->msv.'">'.$item->title .( ($item->title!='English') ? ' ('. $item->title_local .')' :'') .'</option>'."\n";
				}
				if ($html) {
					$html = '<select name="'. $name .'" id="'. $name .'">'."\n\t".'<option value="">'.$this->drawLabel("tl_lang_select", "Select a language").'</option>'."\n".$html;
					$html .= '</select><br />'."\n";			
				}
			}
			return $html;
		}


		public function drawSelectPageTypes( $current='', $name='page_type' ){
			global $db;
			$query = "SELECT * FROM pages_types ORDER BY title DESC";
			$html = '';
			
			if( $types = $db->get_results($query) ){
				$html .= '<select name="'. $name .'">'."\n";
				$html .= '<option value="">Standard Page</option>'."\n";	
				foreach( $types as $item ){
					$html .= '<option value="'. $item->id .'">'. ucwords($item->title) .'</option>'."\n";
				}
				$html .= '</select>'."\n";
				
				return $html;
			}else{
				return false;
			}
		}


		public function initCKE() {
			return '
<script type="text/javascript" src="/treeline/includes/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
function showOkButton(show) {
	var display = "none";
	if (show==1) display = "";
	/*
	if (document.getElementById("cke_66_uiElement")) {
		document.getElementById("cke_66_uiElement").style.display=display;
	}
	*/
	var f = document.getElementsByClassName("cke_dialog_ui_button cke_dialog_ui_button_ok");
	for(var i = 0; i < f.length; i++){
		var s = f[i].getElementsByTagName("span");
		if (s.length==1) {
			//alert ("hide("+display+") the button");
			s[0].style.display=display;
		}
		//alert ("Got "+s.length+" spans");
		//for (var j = 0; j<s.length; j++) {
		//}
	}
	//alert("f("+f+") Show("+show+") display("+display+")");
}
</script>
';			
		}



	
		// I'm still not sure these methods belong in this class
		
		public function drawTinyMCE($files=array()) {
			// This draws a TinyMCE area
            $filepath="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js";
            //$filepath="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce_src.js";
            if (file_exists($_SERVER['DOCUMENT_ROOT'].$filepath) ) {
				//$html.='<!-- Add tiny mce js ('.$_SERVER['DOCUMENT_ROOT'].$filepath.') -->';
				/*
				$html.='<script type="text/javascript">';
				$html.=file_get_contents($_SERVER['DOCUMENT_ROOT'].$filepath);
				$html.="</script>";
				*/
				$html = '<script type="text/javascript" src="'.$filepath.'"></script>'."\n";
                if (is_array($files)) {
                    foreach($files as $mceFile) {
                        $html .= '<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_'.$mceFile.'.js"></script>';
                    }
                }
            }
            else $html="<!-- cant find requested tiny_mce file -->";
			return $html;
		}	
			
		
		public function drawPanelTinyMCE() {
			// This draws a TinyMCE area
			// This needs cutting down to edit a panel -- basic formatting etc
			$html = '<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>'."\n";
			$html .= '<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_panel.js"></script>';
			return $html;
		}		
		public function drawWorkPanelTinyMCE() {
			// This draws a TinyMCE area for workstream panels
			$html = '<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>'."\n";
			$html .= '<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_workpanel.js"></script>';
			return $html;
		}		
		
		public function drawSpecialTinyMCE(){
			// This draws a TinyMCE area
			// This needs cutting down every now and again
			$html = '<script type="text/javascript" src="/treeline/includes/tiny_mce/tiny_mce.js"></script>'."\n";
			$html .= '<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_special.js"></script>';
			return $html;
		}		
		



	
	// 12/12/2008 Comment
	// Collect and return an array of publishable pages, panels or galleries
	public function getPagePublishableList($page = 1, $format = 0, $msv=1){
		
		global $db;
		//print "gppL($page, $format, $msv)<br />";
		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();
		
		switch($format){
			case 'panel':
			case 'panels':
			case 1:
				$template = 2;
				break;
			/*
			case 'pages':
			case 'page':
			case 0:
			case 'gallery':
			case 'galleries':
			case 2:
			*/
			default:
				$template = 1;
				break;
		}
		$query = "SELECT * FROM get_page_content_properties 
			WHERE revision_id = 1 AND template_type = '$template' AND msv=$msv AND parent>0 
			GROUP BY guid";
		$db->query($query);
		$this->setTotal($db->num_rows);
		
		$query .= " ORDER BY date_modified DESC, date_created DESC LIMIT ". $this->from .",". $this->getPerPage();
 		//print "$query <br>\n";

		$files = $db->get_results($query);

		$this->setTotal=sizeof($files);
        
		if(sizeof($files)>0) return $files;
		else return false;
		
	}
	
	// 12/12/2008 Phil Redclift
	// Draw a list of publishable pages or panels with available options and pagination
	public function drawPagePublishableList($page = 1, $format = 0){

    	global $site;
		//print "dPPL($page, $format)<br>\n";
		$this->setPerPage(10);
		$this->setPage($page);

		if($results = $this->getPagePublishableList($page, $format, $site->id) ){
			switch($format){
				case '0':
				case 'pages':
					$format = 'pages';
					break;
				case '1':
				case 'panels':
					$format = 'panels';
					break;
				default:
					$format = '';
				break;
			}
			
			$html = '<table class="treeline">
<caption>Publishable '.$format.'</caption>
	<thead>
	<tr>
		<th scope="col">Preview</th>
		<th scope="col">Title</th>
		<th scope="col">Author</th>
		<th scope="col">Created On</th>
		<th scope="col">Publish</th>
		<th scope="col">Reject</th>
	</tr>
	</thead>
<tbody>
';
			foreach($results as $thispage){
				$html .= "<tr>\n";
				$html .= '<td class="action preview"><a href="'. $this->drawLinkByGUID($thispage->guid) .'?mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width='.$site->getConfig("site_page_width").'" class="thickbox" title="Preview this">Preview this</a></td>'."\n";
				$html .="<td><strong>".$thispage->title .'</strong>';
				if($thispage->template == 'panelrss.php'){
					$html .= ' <em style="color:#3a3">[RSS Panel]</em>';
				}
				$html .= '</td>'."\n";
				$html .= '<td><a href="mailto:'.$thispage->created_by_email .'" title="Email '.$thispage->created_by.'">'. $thispage->created_by_username .'</a></td>'."\n";
				$html .= '<td>'.$thispage->date_created."</td>\n";
				
				if ($thispage->template == 'gallery.php') {
					$format = 'galleries';
				}
				
				$html .= '<td class="action publish"><a href="/treeline/'.$format.'/?action=publish&amp;guid='.$thispage->guid.'" title="Publish this item">Publish this item</a></td>'."\n";
				$html .= '<td class="action delete"><a href="/treeline/'.$format.'/?action=reject&amp;guid='.$thispage->guid.'" title="Reject this edit">Reject this edit</a></td>'."\n".'</tr>'."\n";
			}
			$html .= "</tbody>\n</table>\n";
			//$html .= $this->getPagination($page, $action, $cat, $term);
			//$html .= $this->drawPagination('/treeline/'.$format.'/?action=publish', $this->getTotal(), 10, $page);
			$html .= drawPagination($this->getTotal(), 10, $page, '/treeline/'.$format.'/?action=publish');
			
			return $html;
			
		}
		else return "<p>There are no $format to display</p>";
	}

	
	// 12/12/2008 - Phil Redclift
	// Select a list of pages and return as an array.
	// May be used to pull page lists or panel lists.
	// $cat is an optional category variable, if its set to zero or has a space or nothing, it should show all files
	public function getPageList($cat=false, $term=false, $type=false, $format=0, $msv){
		global $db, $site;

		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		$select = "SELECT 
			p.guid, p.title, template_title, template, locked, offline, publishable,
			date_created, created_by, created_by_email, o_date_modified, created_by_username, 
			date_modified, modified_by, date_published, published_by, 
			IF(o_date_created>o_date_modified,date_created,IF(o_date_modified>o_date_published,date_modified,date_published)) AS date_updated,
			IF (o_date_created>o_date_modified,created_by,IF(o_date_modified>o_date_published,modified_by,published_by)) AS updated_by,
			IF (o_date_created>o_date_modified,'created',IF(o_date_modified>o_date_published,'modified','published')) AS updated_action
			FROM get_page_properties p ";

		$type=($type)?"AND parent='".$type."' ":'';
		
		$exceptions = "AND template_type=1 ";	// Default to pages.
		if ($format==1) $exceptions = "AND template_type=2 AND template!=23 ";	// Panels

		// Search for pages/panels by title
		if($cat=='title' && $term){ 
			$total_query = "SELECT guid FROM get_page_properties p
				WHERE msv=$msv ".$type.$exceptions." 
				AND p.guid != '".($site->id+0)."'
				AND title LIKE '%". $term ."%'";
			$query=$select."
				WHERE msv=$msv ".$type.$exceptions." 
				AND p.guid != '".($site->id+0)."'
				AND title LIKE '%". $term ."%' 
				ORDER BY title ASC 
				LIMIT ". $this->from .",". $this->getPerPage();
		}
		// Search for pages/panels based on content
		else if($cat=='content' && $term){ 
			$total_query = "SELECT guid FROM get_page_content_properties p
				WHERE msv=$msv ".$type.$exceptions." 
				AND p.guid != '".($site->id+0)."'
				AND revision_id = 0 AND content LIKE '%". $term ."%'";
			$query = $select. "
				LEFT JOIN content c ON p.guid=c.parent
				WHERE msv=$msv ".$type.$exceptions." 
				AND p.guid != '".($site->id+0)."'
				AND revision_id = 0 
				AND content LIKE '%". $term ."%' 
				ORDER by p.title ASC
				LIMIT ". $this->from .",". $this->getPerPage();
		}
		else if($cat=='tags' && $term){ // TAGS
			$total_query = "SELECT p.guid FROM get_page_content_properties p, 
				tag_relationships tr, tags t 
				WHERE p.msv=$msv ".$type.$exceptions." 
				AND p.guid != '".($site->id+0)."'
				AND p.guid = tr.guid AND tr.tag_id = t.id AND p.revision_id = 0 
				AND t.tag LIKE '%". $term ."%' 
				GROUP BY p.guid";
			$query = $select .", tag_relationships tr, tags t  
				WHERE p.msv=$msv ".$type.$exceptions." 
				AND p.guid != '".($site->id+0)."'
				AND p.guid = tr.guid AND tr.tag_id = t.id 
				AND t.tag LIKE '%". $term ."%' 
				GROUP BY p.guid 
				ORDER BY p.title
				LIMIT ". $this->from .",". $this->getPerPage();
		}
		else{
			$total_query = "SELECT guid FROM get_page_properties p
				WHERE msv=$msv ".$type.$exceptions ." 
				AND p.guid != '".($site->id+0)."'
				";
			$query = $select."
				WHERE msv=$msv ".$type.$exceptions ." 
				AND p.guid != '".($site->id+0)."'
				ORDER BY o_date_modified DESC,title ASC 
				LIMIT ". $this->from .",". $this->getPerPage();

		}
		
		// Get total results and set number of pages etc...
		//print "$total_query<br>\n";
		$db->query($total_query);
		$this->setTotal($db->num_rows);	
		$this->setTotalPages($db->num_rows);	
		$db->flush();
		
		////niceError($query);
		//print "$query<br>\n";
		$pages = $db->get_results($query);
		if(sizeof($pages)>0) return $pages;
		else return false;
	}

	// 13th Jan 2009 - Phil Redclift
	// page is for pagination
	// action is so we can reuse for edt & delete
	// cat is field to search within,
	// term is the search keywords 
	// type is actually a reference to the parent which ditates what type of page we're after...
	public function drawPageList($page=1, $action=false, $cat=false, $term=false, $type=false, $format=0, $guid=''){
    	global $site, $help, $db;
		//print "dPL(page-$page, action-$action, cat-$cat, term-$term, type-$type, format-$format, guid-$guid)<br>\n";

		$action = $_REQUEST['action'];
		$this->setPerPage(10);
		$this->setPage($page);
			
        $orig_format=$format;
        switch($format){
            case '0':
                $format='pages';
                break;
            case '1':
                $format='panels';
                break;
            default:
                $format='';
            break;
        }

		if ($guid) {
			$query = "SELECT 
			guid, title, template_title, template, locked, offline, publishable,
			date_created, created_by, created_by_email, created_by_username, 
			date_modified, modified_by, date_published, published_by, 
			IF(o_date_created>o_date_modified,date_created,IF(o_date_modified>o_date_published,date_modified,date_published)) AS date_updated,
			IF (o_date_created>o_date_modified,created_by,IF(o_date_modified>o_date_published,modified_by,published_by)) AS updated_by,
			IF (o_date_created>o_date_modified,'created',IF(o_date_modified>o_date_published,'modified','published')) AS updated_action
			FROM get_page_properties WHERE guid='$guid' LIMIT 1";
			//print "$query<br>\n";
			$this->setTotal(1);	
			$this->setTotalPages(1);	
			$results = $db->get_results($query);
		}
		else $results = $this->getPageList($cat, $term, $type, $orig_format, $site->id);
		
		if ($results){

			$html = '<table class="tl_list">
<caption>'. $this->drawTotal($format) .'</caption>
<thead>
<tr>
	<th scope="col">'.$this->drawLabel("tl_recent_th_title", "Title").'</th>
	<th scope="col">'.$this->drawLabel("tl_recent_th_type", "Type").'</th>
	<th scope="col">'.$this->drawLabel("tl_recent_th_updated", "Last updated").'</th>
	<th scope="col">'.$this->drawLabel("tl_recent_th_lastuse", "Last used by").'</th>
	<th scope="col">'.$this->drawLabel("tl_recent_th_status", "Status").'</th>
	<th scope="col">'.$this->drawLabel("tl_recent_th_action", "Manage this page").'</th>
</tr>
</thead>
<tbody>
';
			foreach($results as $thispage){

				/*
				switch($thispage->template){
					case 7:
						$html .= ' <span style="color:#3a3">[RSS Panel]</span>';
						$thisaction = ($action=='edit') ? 'editrss' : $action;
						break;
					case 17:
						$html .= ' <span style="color:#3a3">[Poll Panel]</span>';
						$thisaction = ($action=='edit') ? 'editpoll' : $action;
						break;
					case 18:
						$html .= ' <span style="color:#3a3">[Gallery]</span>';
						$thisaction = ($action=='edit') ? 'edit' : $action;
						break;			
					default:
						$thisaction=$action;
						break;
				}
				*/

				// Create extra info for popup. 
				// NOTE : Dont use <br /> in popup html
				$xtrainfo=$xtradate='';
				switch ($thispage->updated_action) {
					case "published" : 
						$xtrainfo=$thispage->modified_by?"<br>".$this->drawLabel("tl_recent_editby", 'Lasted edited by')." ".$thispage->modified_by:"";
						$xtradate=$thispage->date_modified?"<br>".$this->drawLabel("tl_recent_editdate", "Last edited on")." ".$this->languageDate($thispage->date_modified):"";
					case "modified" :
						$xtrainfo=$thispage->created_by?$this->drawLabel("tl_recent_createby", "Created by")." ".$thispage->created_by.$xtrainfo:"";
						$xtradate=$thispage->date_created?$this->drawLabel("tl_recent_createdate", "Page created"). " ".$this->languageDate($thispage->date_created).$xtradate:"";
						break;
					case "created": 
					default:
						break;
				}

				// Work out this resources status...
				if (!$thispage->modified_by) $status="New";
				else if ($thispage->publishable) $status="Unpublished";
				else if ($thispage->published_by) $status = "Live";
				if ($thispage->offline ) $status = "Offline";
				else if ($thispage->locked) $status = "Being edited";

				// Have to truncate long titles as mess up my layout.
				$pagetitle=(strlen($thispage->title)>25)?substr($thispage->title,0,22)."...":$thispage->title;

				// Normally just show the template title
				$templatetitle = $thispage->template_title;
				$templatetitle = $this->drawLabel("tl_p_".str_replace(" ", "-", $templatetitle), $templatetitle);
				// Its a bit kak but for events allow event management from clicking the template
				if ($thispage->template==19) {
					$templatetitle = '<a '.$help->drawInfoPopup($this->drawLabel("tl_event_popup_man", "Click to manage this event")).' href="/treeline/events/?guid='.$thispage->guid.'">'.$templatetitle.'</a>';
				}
				
				$html .= '<tr>
<td '.$help->drawInfoPopup($this->drawLinkByGUID($thispage->guid)).'><strong>'.$pagetitle .'</strong>
<td>'.$templatetitle.'</td>
<td '.$help->drawInfoPopup($xtradate).' nowrap>'.$this->languageDate($thispage->date_updated).'</td>
<td '.$help->drawInfoPopup($xtrainfo).'>'.$thispage->updated_by.'</a></td>
<td>'.$this->drawLabel("tl_p_status_".$status, $status).'</td>
<td class="action">
'.$this->drawEditCheckboxes($thispage->guid, substr($format,0,-1), $thispage->template_title, $thispage->template, $thispage->publishable, $thispage->locked, $thispage->offline).'
</td>
';
				/*
				if($action == 'edit' && !in_array($thispage->template, array(7,17)) ){ // RRS Panels' content isn't editable so don't show that link
					$html .= '<td class="action edit"><a href="'.$this->drawLinkByGUID($thispage->guid).'?mode=edit&amp;referer=/treeline/'.$format.'/&amp;guid='.$thispage->guid.'" title="Edit '.$format.' content">Edit '.$format.' content</a></td>'."\n";
				} else if( in_array($thispage->template, array(7,17)) && $action != 'delete'){
					$html .= '<td><abbr title="Not applicable">N/A</abbr></td>'."\n";
				}
				$html .= '<td class="action '.$action.'"><a href="/treeline/'. $format .'/?action='. $thisaction .'&amp;guid='.$thispage->guid.'" title="'. $action .' this '.$format.'">'. $action .' this '.$format.'</a></td>'."\n".'</tr>'."\n";
				*/
			}
			$html .= "</tbody>\n</table>\n";
			//$html .= $this->getPagination($page,$action,$cat,$term);
			//$html .= $this->drawPagination('/treeline/'.$format.'/?action='.$action, $this->getTotal(), 10, $page, $cat, $term);
			//$html .= drawPagination($this->getTotal(), 10, $page, '/treeline/'.$format.'/?action='.$action.'&category='.$cat.'&keywords='.$term);
			$html .= drawPagination($this->getTotal(), 10, $page, '/treeline/'.$format.'/?action=edit&category='.$cat.'&keywords='.$term);
			
			return $html;
		}
		else {
			if($term){
				return '<p>There are no '. $format .' whose <strong>'.$cat.'</strong> matches \'<strong>'.$term.'</strong>\'</p>';
			}else{
				return '<p>There are no '. $format .' to display</p>';
			}
		}
	}
	
	
	public function languageDate($d) {
		global $site;
		if ($_SESSION['treeline_language']!="en") {
			//print "Date a language<br>\n";
			if (preg_match("/(\d*)(\w\w) (.*) (\d*)/", $d, $reg)) {
				$day = $reg[1];
				$dayth = $reg[2];
				$month = $reg[3];
				$year = $reg[4];
				return $day." ".$this->drawGeneric($month, 1)." ".$year;
				//print "Got date(".print_r($reg, true).")<br>\n";
			}
		}
		return $d;
		
	}
	
	// 12th Jan 2009 - Phil Redclift
	// Create a list of clickable boxes applicable to pages or panels 
	// based on the page/panel type and user privileges
	public function drawEditCheckboxes($guid, $type, $template_name, $template_id, $publishable=1, $locked=0, $offline=0) {
		global $db, $help, $site;
		//print "dECb(g($guid), t($type), tt($template_name), tid($template_id), pub($publishable), lok($locked), off($offline))<br>\n";

		// Set default options...
		$no_link='<span class="no-action"></span>';
		$publishlink=$rejectlink=$deletelink=$previewlink=$editlink=$attributelink=$no_link;
		
		$attributelink= '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_".$type."_att", "Edit ".$type." attributes")).' class="edit_att" href="/treeline/'.strtolower($type).'s/?action=edit&guid='.$guid.'">Edit attributes</a>';
		
		
		// Set up publish link and delete link if we are allowed
		if($_SESSION['treeline_user_group']!="Author" && !$locked){
			if($publishable>0){
				$publishlink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_".$type."_pub", "Publish this ".$type)).' class="publish" href="/treeline/'.strtolower($type).'s/?action=publish&amp;guid='.$guid.'">Publish this '.$type.'</a>';
				$rejectlink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_".$type."rej", "Reject edits to this ".$type)).' class="reject" href="/treeline/'.strtolower($type).'s/?action=reject&amp;guid='.$guid.'">Reject edits to this '.$type.'</a>';
			}
		}
		//print "got pl($publishlink)<br>\n";
		
		// If this resource is offline we cant really do anything but publish it
		if (!$offline) {
			
			// We can preview any online page.
			// We dont like previewing offline pages as even hitting offline pages shows a page missing 404
			// and sparks off an email to admin
			// Panels are shown in an thickbox frame.
			$ex_link=$ex_class="";
			$ex_target="_blank";
			if ($type=="panel") {
				//$ex_link = '&amp;KeepThis=true&amp;TB_iframe=true&amp;height=500&amp;width='.$site->getConfig("site_page_width");
				//$ex_class=" thickbox";
				//$ex_target="_blank";
			}			
			$previewlink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_".$type."preview", "Preview this ".$type)).' class="preview'.$ex_class.'" href="'.$this->drawLinkByGUID($guid).'?mode=preview'.$ex_link.'" target="'.$ex_target.'">Preview</a>';
			
			
			// If this page is not being edited then set up the edit link
			if ($locked) {
				$lock_user = $db->get_row("SELECT id, full_name FROM users where LOCK_GUID = '$guid'");
			}
			if (!$locked || $lock_user->id==$_SESSION['treeline_user_id']) {
				$editlink = $this->drawLinkByGUID($guid).'?mode=edit&amp;referer=/treeline/'.$type."s/";
				// Set up publish link and delete link if we are allowed
				if($_SESSION['treeline_user_group']!="Author"){
					$deletelink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_".$type."_del", "Delete this ".$type)).' class="delete" href="/treeline/'.strtolower($type).'s/?action=delete&guid='.$guid.'">Delete</a>';
				}
			}
			// Show the lock icon and say who has the page locked.
			else $editlink = "locked";
			
		}
		else {
			// Added this to allow pages to be viewed while developing new templates. It can be removed if a problem.
			$previewlink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_".$type."preview", "Preview this ".$type)).' class="preview'.$ex_class.'" href="'.$this->drawLinkByGUID($guid).'?mode=preview'.$ex_link.'" target="_blank">Preview</a>';
		}

		// Check for exceptions.
		switch ($template_id) {
			case 1 :	// Home page
				$deletelink = $attributelink = $no_link;
				break;
			case 3 : 	// Contact us page
			case 5 : 	// News letters pages
			case 33: 	// News letters pages
				$deletelink = $attributelink = $no_link;
				break;
			case 4: 	// News index
				$publishlink = $rejectlink = $no_link;
				break; 			
			case 7 : 	// News panel
				$editlink = $no_link;
				//$attributelink= '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_edit_rss", "Edit RSS panel attributes")).' class="edit_att" href="/treeline/panels/?action=edit&map;guid='.$guid.'">Edit attributes</a>';
				break;
			case 12:	// Members login
				$editlink = $publishlink = $rejectlink = $deletelink = $no_link;
				break; 			
			case 15:	// Intelligent panel
				$editlink = $no_link;
				//$attributelink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_edit_intelligent", "Edit panel attributes")).' class="edit_att" href="/treeline/panels/?action=edit&amp;guid='.$guid.'">Edit attributes</a>';
				break;
			case 17:	// Poll panel
				$editlink = $no_link;
				//$attributelink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_edit_poll", "Edit poll panel attributes")).' class="edit_att" href="/treeline/panels/?action=edit&amp;guid='.$guid.'">Edit attributes</a>';
				break;
			case 16: 	// Resources page
				$editlink = $publishlink = $rejectlink = $no_link;
				break;
			case 21: 	// Forum
				$editlink = $publishlink = $rejectlink = $deletelink = $no_link;
				break;
			case 22: 	// Petition page
				break;
			case 24: 	// Functional panel
				$editlink = $deletelink = $no_link;
				break;
			case 28: 	// Media player
				$editlink = $publishlink = $rejectlink = $deletelink = $no_link;
				break;
			case 999: 	// Blogs (was 29)
				$editlink = $publishlink = $rejectlink = $deletelink = $no_link;
				break;
			case 68: 	// Gallery
				$publishlink = $rejectlink = $no_link;
				$editlink = "/treeline/galleries/";
				break;
			break;
		}
		// Create proper edit lnke
		if ($editlink && $editlink!=$no_link) {
			if ($editlink=="locked") $editlink = '<span class="locked" '.$help->drawInfoPopup($this->drawLabel("tl_dEC_lock_edit1", 'This page is currently locked for').'<br>'.$this->drawLabel("tl_dEC_lock_edit2", "edit by").' <strong>'.$lock_user->full_name.'</strong>').'></span>';
		 	else $editlink='<a '.$help->drawInfoPopup($this->drawLabel("tl_dEC_".$type."edit", "Edit this ".$type)).' class="edit" href="'.$editlink.'">Edit</a>';
		}
	
		$html = $previewlink.'
'.$editlink.'
'.$attributelink.'
'.$publishlink.'
'.$rejectlink.'
'.$deletelink.'
';		return $html;
		
	}


	// 16th Jan 2009 - Phil Redclift
	// Create a list of clickable boxes applicable to validating comments 
	public function drawCommentsCheckboxes($guid, $comment_id) {
		global $db, $help, $site;
		//print "dCC($guid, $comment_id)<br>\n";

		// Set default options...
		$no_link='<span class="no-action"></span>';
		$publishlink=$previewlink=$rejectlink=$no_link;
		
		$previewlink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_task_comm_preview", "Preview this page")).' class="preview" href="'.$this->drawLinkByGUID($guid).'?mode=preview&amp;showcomments=1&amp;commentid='.$comment_id.'" target="_blank">Preview</a>';
		$publishlink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_task_comm_publish", "Publish this comment")).' class="publish" href="/treeline/comments/?action=approve&amp;id='.$comment_id.'">Publish</a>';
		$rejectlink = '<a '.$help->drawInfoPopup($this->drawLabel("tl_task_comm_reject", "Reject this comment")).' class="reject" href="/treeline/comments/?action=reject&amp;id='.$comment_id.'">Reject</a>';
		$html = $previewlink.$publishlink.$rejectlink;
		return $html;
	}
	
	
	public function drawPageStatus($modified_by, $published_by, $publishable, $offline, $in_edit) {
		// Work out this resources status...
		//print "dPS($modified_by, $published_by, $publishable, $offline, $in_edit)<br>\n";
		if (!$modified_by) $status="New";
		else if ($publishable ) {
			if (!$published_by) $status="Unpublished";
			else $status="Pending";
		}
		else if ($published_by) $status = "Live";
		if ($offline ) $status = "Offline";
		else if ($in_edit) $status = "Being edited";
		return $status;
	}
	
	
	public function toggleNewsDisplay($guid, $show) {
		global $db;
		$success=0;
		$query="select count(*) from pages_news_display where guid='$guid'";
		$count=$db->get_var($query);
	//	print "toggle($guid, $show) currently($count)<br>";
	
		if ($show && !$count) {
			$query="insert into pages_news_display (`guid`, `show`) values ('$guid', $show)";
			$success=$db->query($query);
	//		print "suc($success)sql($query)<br>";
		}
		if (!$show && $count) {
			$query="delete from pages_news_display where guid='$guid'";
			return $db->query($query);
		}
		return 0;
	}
	
	
	public function drawTotal($format='pages'){
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1){
			$msg = $this->drawLabel("tl_pedit_total1", 'There is only').' 1 '. $this->drawLabel("tl_generic_".$format, substr($format,0,strlen($format)-1)) .' '.$this->drawLabel("tl_pedit_total2", "in this site");
		}else{
			$msg = $this->drawLabel("tl_generic_showing", 'Showing').' '. $this->drawLabel("tl_generic_".$format, $format).' '.($this->from+1).'-'.$to.' '.$this->drawLabel("tl_generic_of", "of").' '.$this->getTotal().' ';
		}
		
		return $msg;
	}

	/*
	public function drawPagination($currentURL,$totalResults,$perPage=10,$currentPage=1,$cat='',$term='') {

		// $page indicates which page we're on
        //print "dp($currentURL, $totalResults, $perPage, $currentPage, $cat, $term)<br />";

		$exLink.=($cat) ? '&amp;category='.$cat : '';
		$exLink.= ($term) ? '&amp;keywords='.$term : '';
        $currentURL.=$exLink;
        
		$totalpages = ceil($totalResults / $perPage);
		if(!$currentPage || $currentPage==0){
			$currentPage = 1;
		}

		for( $i=0; $i<strlen($currentURL); $i++ ){
			$tmp[] = $currentURL[$i];
		}
	
		if( (!in_array('?',$tmp) && !in_array('&',$tmp)) ){
			$currentURL .= '?';
		}else{
			$currentURL .= '&amp;';
		}
		
		$html = '<ul class="pagination">'."\n";
		
		if ($totalpages == 1) {
			return $html;
		}
		if($currentPage > 2){
			$html .= '<li class="bookend"><a href="'.$currentURL.'">First</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		}
		else{
			$html .= '<li class="bookend inactive">First</li>'."\n";
		}
		if ($currentPage > 1) {
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. ($currentPage-1).'">Previous</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		}
		else{
			$html .= '<li class="bookend inactive">Previous</li>'."\n";
		}
		
		if($totalpages<=10){
			$pagestart=1;
			$pageend = $totalpages;
		}
		else if($currentPage<($totalpages-5)){
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		else if( ($currentPage>($totalpages-5)) && ($currentPage<=$totalpages) ){
			$pagestart = $currentPage-(9-($totalpages-$currentPage));
			$pageend = ($currentPage+($totalpages-$currentPage))+1;
		}
		else{
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		
		// for debugging...
		//echo $pagestart.' > '.$pageend.' - ['.$page.'] of ['. $totalpages .']<br />';
		for ($i=$pagestart; $i<=$pageend; $i++) {
			//// We don't want to show all pages, just a few either side of the page we're on.
			//// If we keep the page we're on centrally (position 5) then when we get to position 6
			//// we'll need to cycle the whole lot down...
			$class=($i==$pageend?' bookend':'');
			if ($i != $currentPage) {
				$html .= '<li class="page'.$class.'"><a href="'. $currentURL.'p='. $i.'">'.$i.'</a></li>'."\n"; 
			} else {
				$html .= '<li class="page'.$class.' selected"><strong>'.$i.'</strong></li>'."\n";
			}

		}
		if ($currentPage < $totalpages) {
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. ($currentPage+1).'">Next</a></li>'."\n";
		}else{
			$html .= '<li class="bookend inactive">Next</li>';
		}	
		if($currentPage < ($totalpages-1)){
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. $totalpages.'">Last</a></li>'."\n";
		}else{
			$html .= '<li class="bookend inactive">Last</li>'."\n";
		}
		
		$html .= '</ul>'."\n";
			
		return $html;
	}
	*/
	
	/*
	// 12/12/2008 Comment
	// Page pagination function
	// Should produce a list of selectable page numbers for scrolling through page or panel lists
	// Adds options to edit/delete/publish
	public function getPagination($page,$action,$cat=false,$term=false){
	
		print "gP($page, $action, $cat, $term)<br>\n";
		$totalpages = $this->getTotalPages();
		
		$catLink = ($cat) ? '&amp;category='.$cat : '';
		$termLink = ($term) ? '&amp;keywords='.$term : '';

		if($totalpages>1){
			$html = '<ul class="pagination">'."\n";
			
			// first page link
			if( ($totalpages >= 5) && ($page >3)){
				$html .= '<li class="bookend"><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .$catLink .$termLink .'&amp;p=1">First</a></li>'."\n";
			}
			//previous page link
			if( $page > 1 ){
				$html .= '<li class="bookend"><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .$catLink .$termLink .'&amp;p='. ($page-1) .'">Previous</a></li>'."\n";
			}
			// page numbers
			for($i=1;$i<=$totalpages;$i++){
				if($page == $i){
					$classSelect = ' class="selected"'; // this is the active page so give it the active class
				}else{
					$classSelect = '';
				}
					$html .= '<li'.$classSelect.'><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .$catLink .$termLink .'&amp;p='. $i .'">'. $i .'</a></li>'."\n";
			}
			// next page link
			if( ($totalpages > 1) && ($page < $totalpages)){
				$html .= '<li class="bookend"><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .$catLink .$termLink .'&amp;p='. ($page+1) .'">Next</a></li>'."\n";
			}
			// last page link
			if( ($totalpages >= 5) && ($page < ($totalpages-1))){
				$html .= '<li class="bookend"><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .$catLink .$termLink .'&amp;p='. $totalpages .'">Last</a></li>'."\n";
			}
			$html .= '</ul>'."\n";
			
			return $html;
		}else{
			return false;
		}
	}
	*/
	////////////////////////////////////////////////////////


	public function getGUIDfromURL($url){
		global $db, $site;
		//Code taken from rewrite.php.
		$location = explode('/',$url);
		$first = array_shift($location); // We can disregard the first item, as this is the element before the first /
		$last  = end($location);
	
	
	
		if (!$last) {
			// if the URL ends in '/', eg /organisation/, we want the page 'organisation'
			array_pop($location);
			$last = end($location);
		}
		
		$name = $db->escape(str_replace('.html','',$last));

		// What's the full URL of the page we're looking for?
		$target = '/' . implode("/",$location);
		
		$include = '';
		$page = new Page();
		$match = '';
		
		$query = "SELECT p.guid FROM pages p ";
		$query .= " WHERE p.name = '$name' AND p.msv=". $site->id;

		$pages = $db->get_results($query);
		
		if ($db->num_rows == 1) {
			// if there's only one page in the database with the name we want, select it
			$match = $pages[0]->guid;	
		}
		else if ($db->num_rows > 1) {
			// if there's more than one page in the database with the name we want,
			// compare the link of each page with the target link we're after
			/// if it's a match, we're looking at the right page:
			foreach ($pages as $p) {
				$tmp = $page->drawLinkByGUID($p->guid);
				//echo 'target: '. $target .' == '. $tmp .'<br />';
				if ($tmp == $target.'/') {
					$match = $p->guid;
					
				}
				unset($tmp);
//				else {
//					echo "No match: {$page->getLinkByGUID($p->guid)} != {$target}<br />";
//				}
			}
		}
		
		if ($match>0) {

			return $match;
		} else {
			return false;
		}
		
		
	}	
	/********************************************/





	}
?>
