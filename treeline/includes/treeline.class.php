<?php

	/*
	
	  Treeline Class
	  
	  last edited: 19/09/2007 
	  last edited by: by Phil T phil.thompson@ichameleon.com
	  changes made: add getAllRecentItems() and drawAllRecentItems()
	  
	  
	  Table of contents
	  
	  # Config =  Site configuration
	  	get
		set
		save
		draw
	  # construct
	  # checkLogin
	  # endSession
	  # draw Menu (treeline navigation)
	  # Menu Manager
	    draw
		draw (rows)
	  # Sections
	    get
		draw
		delete
		has section got children?
		check name
		generate name
	  # Pages/Panels	
	    draw by parent
		draw editable
		draw publishable
		is the page publishable?
	
	*/
	

	// 

	$treeline = new Treeline();
	$user = new User();
	
	$treeline->checkLogin(); // is the suer logged in? (They need to be, to see/use Treeline's functioanlity)
	
	class Treeline {

		public $config = array();

		// Config settings...
		public function getConfig($name){
			return $this->config[$name];
		}
		public function setConfig($name,$value){
			$this->config[$name] = $value;
		}
		public function getAllConfig(){
			return $this->config;
		}
		public function saveConfig(){
			global $db,$site;
			
			if(sizeof($this->config>0)){
				$query = "UPDATE sites_versions SET ";
				foreach($this->config as $key => $value){
					$query .= $key ."='". $db->escape($value) ."',";
					
				}
				$query = substr($query,0,strlen($query)-1);
				$query .= " WHERE msv=".$site->id;
				//print "$query<br>\n";
				$db->query($query);
				
				if( $db->affected_rows>=0 ) {
				
					// We may need to copy another palate file if they have changed palate
					//print "new = ".$_POST['config_palate']." old=".$_POST['original_palate']."<br>\n";
					if ($_POST['config_palate']!=$_POST['original_palate']) {
						//print "save palate file<br>\n";
						$palate_file=$_SERVER['DOCUMENT_ROOT']."/style/scheme/palate".(($_POST['config_palate']<10?"0":"").($_POST['config_palate']+0)).".css";
						if (file_exists($palate_file)) {
							$scheme_file=$_SERVER['DOCUMENT_ROOT']."/style/microsite/scheme".($site->id+0).".css";
							//print "copy($palate_file, $scheme_file)<br>\n";
							copy($palate_file, $scheme_file);	
						}
					}
				
					return true;
				}
			}
			return false;
		}
		
		public function drawEditableConfig(){
			global $db, $user, $page, $site;
			$html='';
			
			$fields = array();
			if ($user->drawGroup()=="Superuser" && $site->id==$_SESSION['treeline_user_default_site_id']) $fields[]='title';
			$fields[]='tagline';
			$fields[]='description';
			$fields[]='keywords';
			$fields[]='preview_username';
			$fields[]='preview_password';
			$fields[]='contact_name';
			$fields[]='contact_email';
			$fields[]='contact_phone';
			$fields[]='palate';
			$fields[]='font';
			$fields[]="fellow_membership";
			$fields[] = "fellow_renewal";
			
			if( !is_a($page,'Page') ){
				require($_SERVER['DOCUMENT_ROOT'] .'/page.class.php');
				$page = new Page();
			}

			$query = "SELECT ". join(', ',$fields) ." FROM sites_versions sv 
				".($user->drawGroup()=="Superuser"?"LEFT JOIN sites s ON sv.msv=s.primary_msv ":"")."
				WHERE sv.msv=".$site->id;
			//print "group(".$user->drawGroup().") $query<br>\n";
			
			if($result = $db->get_row($query,"ARRAY_A") ){
				foreach($fields as $field ){
					
					$label=$field;
					
					// Cant change the name or layout of the main site.
					if($site->id==1) {
					
					}
					
					if($field=='description'){
						$value = ($_POST?$_POST['config_'.$field]:$result[$field]);
						$html .= '
						<div>						
							<label for="config_'. $field .'">'. $page->drawLabel("tl_conf_".$label, ucwords(str_replace('_',' ',$label))).':</label>
							<textarea name="config_'. $field .'" id="config_'. $field .'">'.$value.'</textarea>
						</div>
						';
					} 
					else if($field=='keywords') {
						$html .= '
						<div>
							<label for="config_'. $field .'">'. $page->drawLabel("tl_conf_".$label, ucwords(str_replace('_',' ',$label))).':</label>
							<textarea name="config_'. $field .'" id="config_'. $field .'">'.($_POST?$_POST['config_'.$field]:$result[$field]).'</textarea>
						</div>
						';
					}
					else if ($field=="palate" || $field=="font") {
						if ($site->config['setup_palates']) {
							$thisvalue=($_SERVER['REQUEST_METHOD']=="POST"?$_POST['config_'.$field]:$result[$field]);
							$preview_link=($field=="palate"?' (<a href="javascript:palate_preview();">example</a>)':"");
							if ($field=="palate") $html.='<input type="hidden" name="original_palate" value="'.$result[$field].'" />';
							$html.='<label for="config_'.$field.'">'.ucwords(str_replace("_"," ", $label)).$preview_link.':</label>';
							$html.='<select name="config_'.$field.'" id="config_'.$field.'">';
							$query = "SELECT value, title, data FROM sites_options WHERE name='$field' ORDER BY value";
							//print "$query<br>\n";
							if ($options=$db->get_results($query)) {
								foreach ($options as $option) {
									$thisstyle='';
									if ($field=="palate" && $option->data) $thisstyle='text-indent:-1000px; color: #'.$option->data.'; background-color: #'.$option->data.";";
									$html.='<option value="'.$option->value.'"'.($option->value==$thisvalue?' selected="selected"':"").' style="'.$thisstyle.'">'.($field=="palate"?"Palate ".$option->value." (#".$option->data.")":$option->title).'</option>';
								}
							}
							$html.='</select>';
						}
						else {
							$html.='<input name="config_'.$field.'" id="config_'.$field.'" type="hidden" value="0" />';
						}
					}
					else {
						$value = ($_POST?$_POST['config_'.$field]:$result[$field]);
						$html .= '
						<div>						
							<label for="f_'. $field .'">'.$page->drawLabel("tl_conf_".$label, ucwords(str_replace('_',' ',$label))).':</label>
							<input type="text" name="config_'.$field.'" id="f_'.$field.'" value="'.$value.'" />
						</div>
						';
					}
				}
			
				return $html;
			}
			return false;
		}		
		
		
		
		
		
	////////////	
	
		/*
			Construct
		*/
		
		public function __construct(){
			global $db;
			/*$tmparray = array();
			
			if($results = $db->get_results("SELECT name,value FROM config","ARRAY_A")){
				foreach($results as $result){
					$this->config[$result['name']] = $result['value'];
				}
				return true;
			}else{
				return false;
			}
			*/
		}

		/*
			check Login
		*/
	
		public function checkLogin() {
			// this function checks if the user's logged in, and redirects them to login.php if they're not
			global $user;
//			session_start();
			$userID = read($_SESSION,'userid',0);
			$user->loadById($userID);
			if ($user->getStatus() != 'logged in') {

				// ---------------------------------------------------
				// Change all the below in any T3 that need updating...
				$message = "?feedback=error&message=".urlencode('You must be logged in to view Treeline');
				$location = "Location: /treeline/login/";

				$script=strtolower($_SERVER['DOCUMENT_ROOT']);
				if (substr($_SERVER['DOCUMENT_ROOT'], -1, 1)!="/") $script.="/";
				$script.="treeline/index.php";
				if (strtolower($_SERVER['SCRIPT_FILENAME'])!=$script) {
					$location.=$message;
				}

				header ($location."\n\n");
				exit();	// This mother is pretty efin important
				// ---------------------------------------------------
//				$message = urlencode('You must be logged in to view Treeline');
//				header ("Location: /treeline/login/?feedback=error&message=".$message);
//				echo "not logged in [$userID] [{$user->getStatus()}]";
			}
			
		}

		/*
			end Session
		*/

		public function endSession(){
			if(session_destroy()){
				unset($_SESSION);
				return true;
			}else{
				return false;
			}
		}
		
		/*
			Menu: navigation
		*/
	

		/*
			Menu manager
		*/
		// 15/12/2008 Comment 
		// This function draws the menu-manager for the section specified by $parent
		// The page specified by $guid will be highlighted
		// The menu manager outputs a state list to /behaviour/ajax/save_menu.php for later processing
		public function drawMenuManagerByParent($parent, $guid, $page_restrict) {
			global $db, $page;
			$html = '<div id="th">
	<p>
	<span class="title">'.$page->drawLabel("tl_menum_pagename", "Page name").'</span>
	<span class="preview">'.ucfirst($page->drawLabel("tl_generic_preview", "Preview")).'</span>
	<span class="status">'.ucfirst($page->drawLabel("tl_generic_status", "Status")).'</span>
	</p>
</div>
<div class="menu-wrap-wrap">
<div class="menu-wrap">
	<ul id="mm" class="page-list">
	'.$this->drawMenuManagerRowsByParent($parent,$guid,$page_restrict).'
	</ul>
</div>
</div>
';
			return $html;
		}

		// 15/12/2008 Comment
		// Collect all rows at this level in the menu specified by parent
		// Draw each row and recursively call to see if submenus exist.
		public function drawMenuManagerRowsByParent($parent, $guid, $page_restrict=false, $depth = 0, $loop = 1) {
			global $db, $site, $page, $help, $mm_index;
			//print "dMMRBP($parent, $guid, $page_restrict, $depth, $loop)<br>\n";
			$html = '';
			$query = "SELECT p.guid, p.title, p.parent, p.sort_order, offline,
				IF (MAX(c.revision_id)>0,1,0) AS publishable,
				u1.full_name as modified_by, u2.full_name as published_by,
				IF (u3.id,1,0) as locked
				FROM pages p
				LEFT JOIN content c ON p.guid=c.parent
				LEFT JOIN users u1 on p.user_modified=u1.id
				LEFT JOIN users u2 on p.user_published=u2.id
				LEFT JOIN users u3 on p.guid=u3.lock_guid
				WHERE p.parent = '$parent' AND msv=".$site->id." AND hidden = 0 
				GROUP by p.guid
				ORDER BY sort_order";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				if ($depth>0) $html.='<ul class="page-list">';
				foreach ($results as $result) {
					$html.='<li id="mm_'.$result->guid.'" parent="'.$result->parent.'" order="'.$result->sort_order.'" class="'.(($result->guid == $guid)?' created':'').' page-item1 clear-element">'."\n";
					$bgcol=($mm_index++%2==0)?"menu-bg1":"menu-bg2";
					$class=($result->guid==$guid || !$page_restrict)?"sort-handle":"";
					$status=($result->guid==$guid?"New":$page->drawPageStatus($result->modified_by, $result->published_by, $result->publishable, $result->offline, $result->locked));
					//$titletext= $result->title.($result->guid==$guid?'<span class="newpage"></span>':"");
					$titletext= $result->title;
					$previewlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_menum_preview_page", "Preview this page")).' class="preview" href="'.$page->drawLinkByGUID($result->guid).'?mode=preview" class="" target="_blank">&nbsp;</a>';
					
					$html.='<div class="'.$class.' '.$bgcol.'">
<table>
<tbody>
<tr>
<td class="action preview">'.$previewlink.'</td>
<td class="status">'.$page->drawLabel("tl_p_status_".$status, $status).'</td>
<td class="title">'.$titletext.'</td>
</tr>
</tbody>
</table>
</div>
';

					// Now Loop through and kep looping to show children.
					$page->loadByGUID($parent);
					if($page->getTemplate() != 'index.php'){ // Except if we're on the home page then don't loop.
						$html .= $this->drawMenuManagerRowsByParent($result->guid, $guid, $page_restrict, $depth+1, $loop++);	
					}

					$html.='</li>'."\n";
					
				}
				if ($depth>0) $html.='</ul>'."\n";
			}
			else if ($depth==0) $html = '<li class="no-pages">There are no pages in this section yet</li>';
			return $html;
		}
		
		// 15/12/2008 Comment
		// Process a saved menu.
		function saveMenu($parent, $children) {
			//print "sM($parent, ".print_r($children, true).")<br>\n";
			global $db, $site;
			$i = 1;
			foreach ($children as $k => $v) {
				$guid = substr($children[$k]['id'], 3);
				if ($guid) {
					$query = "UPDATE pages SET parent = '$parent', sort_order=$k WHERE guid = '$guid' AND msv=".$site->id;
					$db->query($query);
					//print "$query<br>\n";
				}
				// If this menu item has submenu items recursively call to process each submenu
				if (isset  ( $children[$k]['children'][0]  )) {
					$this->saveMenu($guid, $children[$k]['children']);
				}
				$i++;
			}
		}			


		/*
			Sections
		*/
		public function getSections($siteID=1){
			global $db;
			
			$query = "SELECT p.guid, p.title, p.name, p.sort_order, p.template 
						FROM pages p 
						LEFT JOIN pages_templates t ON t.template_id = p.template 
						WHERE p.parent = $siteID AND p.hidden = 0 AND msv=$siteID 
						AND (t.template_php = 'folder.php' OR t.template_php = 'news.index.php') 
						ORDER BY p.sort_order";
			//niceError($query);
			//print "$query<br>\n";
			if($sections = $db->get_results($query)){
				return $sections;
			}else{
				return false;
			}
		}


		public function getSectionByGUID($guid){
			global $db;
			
			$query="SELECT title FROM pages WHERE guid = '$guid' LIMIT 1";
			if($sections = $db->get_var($query)){
				return $sections;
			}else{
				return false;
			}
		}
		
		
		public function drawSectionTemplates($current=11, $append='', $name='section_templates'){
			global $db, $page;
			$html = '';
			
			// allow folders, 11,75
			// NEWS SECTIONS - Template 4 is no long a section, you must create news pages as pages.
			$query = "SELECT template_id id, template_title title FROM pages_templates WHERE template_id IN (11,75) ORDER BY template_title";
			//print "$query<br>";
			if( $list = $db->get_results($query) ){
				$html .= '<select name="'. $name . $append .'">'."\n";
				foreach($list as $item){
					$selected = ( $current==$item->id ) ? ' selected="selected"' : false;
					$html .= "\t".'<option value="'. $item->id .'"'. $selected .'>'.$page->drawLabel("tl_sectname_".str_replace(" ", "-", substr($item->title, 0, 10)), $item->title).'</option>'."\n";
				}
				$html .= '</select>'."\n";
			}
			return $html;
		}
		

	public function drawEditableSections(){
		global $db, $page, $site;
		$html = '';
		$i=1;
		
		if($sections = $this->getSections($site->id)){
			foreach($sections as $section){
				$html .= '
					<label for="section_'.$section->guid .'">'.ucfirst($page->drawLabel("tl_generic_section", "Section")).' '.$i.': </label>
					<input type="text" name="title_'.$section->guid .'" class="section_title" id="section_'.$section->guid .'" value="'. $section->title .'" />
					<label class="sort_order" for="order_'.$section->guid .'">'.ucfirst($page->drawLabel("tl_generic_sort_order", "Sort order")).':</label>
					<input type="text" name="order_'.$section->guid .'" id="order_'.$section->guid .'" value="'. $section->sort_order .'" class="sort_order"  />
					<label for="section_templates_'. $section->guid .'" class="template">'.ucfirst($page->drawLabel("tl_generic_type", "Type")).':</label>
					'.$this->drawSectionTemplates($section->template, '_'.$section->guid).'<br />
					';
				$i++;
			}
			
			return $html;
		}
		return false;
	}


	public function drawDeleteableSections($siteID=1, $exclude=array()){
		global $db, $page;
		$html = '<ul>'."\n";
		if($sections = $this->getSections($siteID)){
			foreach($sections as $section){
				if (!in_array($section->title, $exclude)) {
					if($count = $this->sectionHasChildren($section->guid)){
						$html .= '<li><span style="color:#666">'. $section->title .' ('.$page->drawLabel("tl_sect_err_kids", "has children").')</span></li>'."\n\t";
					}
					else $html .= '<li><a href="'. $_SERVER['PHP_SELF'] .'?action=delete&amp;guid='.$section->guid .'">'. $section->title .'</a></li>'."\n\t";
				}
			}
			$html .= '</ul>'."\n";
			return $html;
		}
		return false;
	}
		
		
		public function deleteSection($guid){
			global $db;
			//print "delete section($guid)<br>";
			if($db->query("DELETE FROM pages WHERE guid = '$guid'") ){
				return true;
			}else{
				return false;
			}
		}
		
		
		public function sectionHasChildren($guid){
			global $db;
			
			if($count = $db->get_var("SELECT COUNT(guid) FROM pages WHERE parent = '$guid'")){
				return $count;
			}else{
				return false;
			}
		}
		
		
		public function chkName($parent,$title){
			global $db;
			$title = $db->escape($title);
			
			if($guid = $db->get_var("SELECT guid FROM pages WHERE title = '". $title ."' AND parent = '". $parent ."' LIMIT 1") ){
				return $guid;
			}else{
				return false;
			}
		}
		
		
		//// lifted from Page class...
		public function generateName($parentvalue=false,$titlevalue=false) {
			// Generates a "friendly" page name from $title
			// checking that there are no existing pages with the same name and parent
			global $db, $site;
			$parent = ($parentvalue) ? $parentvalue : $this->parent;
			$title = ($titlevalue) ? $db->escape($titlevalue) : $db->escape($this->title);		
			
			$query="SELECT * FROM pages WHERE title = '".$title."' AND parent = '". $parent ."'";
			$msg.="check sec ($query) \n";
			$db->query($query);
			if ($db->num_rows > 0) {
				$ret = false;
			}
			else {
				// Strip everything but letters, numbers and spaces from the title
				$tmp = $title;
				if ($_SESSION['treeline_user_language']=="ar") $tmp = UTF_to_Unicode($tmp);
				else if ($_SESSION['treeline_user_language']=="jp") $tmp = UTF_to_Unicode($tmp);
				//else $tmp=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $tmp);
				$name = preg_replace("/[^A-Za-z0-9 ]/", "", $tmp);
				// Replace spaces with dashes
				$name = str_replace(" ",'-',$name);
				$name = strtolower($name);
				$msg.="got ($title -> $tmp -> $name) \n"; 
				$ret = $name;
			}
			//if ($msg) mail("phil.redclift@ichameleon.com", $site->name." - section generate name", $msg);
			return $ret;
		}
		
		
	// save section title...
	public function saveSection($guid=false,$name=false,$title=false,$type=11,$sort_order=false){
		//print "sS($guid, $name, $title, $type, $sort_order)<br>\n";
		global $db,$user,$siteID;
		
		$name = $db->escape($name);
		$title = $db->escape($title);
		
		
		if($guid){
			$query = "";
			if($title) $query .= "title='". $title ."', ";
			if( $type ) $query .= "template=". $type .", ";
			if( $sort_order ) $query .= "sort_order ='".$sort_order."', ";
			$query = "UPDATE pages SET ".substr($query, 0, -2)." WHERE guid='". $guid ."'";
		}
		else{
			$guid = uniqid();
			$sort_order = $db->get_var("SELECT p.sort_order FROM pages p  LEFT JOIN pages_templates t ON t.template_id = p.template WHERE p.parent=$siteID AND (t.template_php = 'folder.php' OR t.template_php = 'news.index.php') ORDER BY p.sort_order DESC LIMIT 1");
			$query = "INSERT INTO pages 
				(guid, parent, sort_order, name, title, hidden, locked, template, date_created, date_modified, date_published, date_effective, date_expires, user_created, user_modified, user_published,msv)
				VALUES 
				(
					'$guid', '$siteID', ". ($sort_order+1) .", 
					'{$name}', '{$title}', '0', '1', ". $type .", 
					NOW(), 
					'0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 
					{$user->getID()}, {$user->getID()}, 
					{$user->getID()}, $siteID
				)
				";
		}
		//print "update section($query)<br>\n";
		$db->query($query);
		//echo $query .'<br />';
		if (!$db->last_error) return true;
		//if( $db->rows_affected>0 ){ // this allows for us to return true if the update doesn't change anything, otherwise it'll come up with an error!
		//	return true;
		//}
		return false;
	}
		
		
		public function saveSectionOrder($guid,$sort_order){
			//// save the sort order of the sections
			global $db,$user;
			
			if($guid){
				$query = "UPDATE pages SET sort_order ='".$sort_order."' WHERE guid = '". $guid ."'";
			}
						
			if($db->query($query) ){
				return true;
			}else{
				return false;
			}
		}
		
		
		/*
			Pages/Panels
		*/

		
		// Another convention: drawSelect draws a select box
	
		public function drawSelectPagesByPrimary($primary) {
			// This function returns a select list of the site's primary categories, selecting the "current" one
			global $db;
			//return $guid .",".$primary;
			$html = ''; 
			$query = "SELECT p.guid, p.title FROM pages p 
						WHERE p.parent = '1' AND p.hidden = 0 
						ORDER BY p.sort_order";
		
			if( $pages = $db->get_results($query) ){
				foreach ($pages as $page) {
					$selected = ($page->guid == $primary) ? ' selected="selected"' : '';
					$html .= '<option value="'.$page->guid.'""'.$selected.'>'.$page->title.'</option>';
				}
				return $html;
			}else{
				return false;
			}
		}

		
		public function drawSelectPagesByParent($parent = 1, $current, $siteID=1, $include_templates=false, $exclude_pages=array()) {
			// This function returns a select list of pages by parent, selecting the "current" one
			global $db, $page;

			$html = "\n";
			//$query = "(SELECT guid, title FROM pages WHERE guid ='1') UNION (SELECT guid, title FROM pages WHERE parent = '$parent' AND hidden = 0 ORDER BY sort_order)";
			//$query = "SELECT guid, title FROM pages WHERE (guid='$parent' OR (parent = '$parent' AND hidden = 0)) AND site_id=$siteID ORDER BY sort_order";
			
			$query = "SELECT p.guid, p.title, pt.template_title FROM pages p 
					LEFT JOIN pages_templates pt ON p.template=pt.template_id
					WHERE (p.guid='$parent' OR (p.parent = '$parent' /*AND p.hidden = 0*/)) 
					AND p.msv=$siteID ";
			if (count($exclude_pages)) {
				foreach($exclude_pages as $exclude_page) {
					$query.=" AND p.name != '$exclude_page' ";
				}
			}

			if( is_array($include_templates) && count($include_templates)>0 ){
				$query .= "AND pt.template_id IN (". join(', ', $include_templates) .") ";
			}
			else if (isset($include_templates) && !is_array($include_templates) && $include_templates>'') {
				$query .= "AND pt.template_id = ". $include_templates ." ";
			}
			else{
				$query .= "AND pt.template_type = 3 ";
			}
			$query .= "ORDER BY p.sort_order";
			//echo '<pre>'. print_r($include_templates,true) .'</pre><br />';
			//print "$query<br>\n";
			//niceError($query);
			if( $pages = $db->get_results($query) ){
				foreach ($pages as $thispage) {
					$class = strtolower($thispage->template_title);
					$class = preg_replace("/[^A-Za-z0-9 ]/", '', $class);
					$class = str_replace(' ','',$class);
					$selected = ($thispage->guid == $current || $thispage->guid == $parent)?' selected="selected"':'';
					$html .= '<option value="'.$thispage->guid.'" class="'. $class .'"'.$selected.'>'.$thispage->title.' ('.$page->drawLabel("tl_p_".str_replace(" ", "-", $thispage->template_title), $thispage->template_title).')</option>'."\n";
				}
				return $html;
			}
			else{
				return false;
			}
		}
		
		// 16/12/2008 Comment
		// This function returns a list of all pages that the current user has the right to edit
		public function drawEditablePagesByParent($parent) {
			global $db, $user, $site, $page;

			$html = "\n";
			
			$query = "SELECT p.guid, p.title, p.locked, p.hidden, p.offline FROM pages p 
						LEFT JOIN pages_templates pt ON p.template=pt.template_id
						WHERE (p.parent = '$parent' AND p.msv=".$site->id.") 
						AND (pt.template_type=1 OR pt.template_type=3) ORDER BY p.sort_order";			
			//print "$query<br>";
			
			if ($pages = $db->get_results($query)) {
				//$html .= '<ul>'."\n";
				foreach ($pages as $thispage) {

					if( $thispage->hidden==1 && $thispage->locked==1){
						$html .= '';
					}
					else if ($thispage->locked == 1) {
						$html .= '<li><span style="color: #999">'.$indent.$thispage->title.'</span>'."\n";
					}
					else {
						$html .= '<li><a href="/treeline/pages/?action=list&amp;guid='.$thispage->guid.'">'.$thispage->title.'</a>'."\n";
						if($thispage->hidden==1){
							$html .= ' <span style="color: #669">['.$page->drawLabel("tl_generic_hidden", "hidden").']</span>'."\n";
						}
						if($thispage->offline==1){
							$html .= ' <span style="color: #669">['.$page->drawLabel("tl_generic_offline", "offline").']</span>'."\n";
						}
						
					}
					$html .= $this->drawEditablePagesByParent($thispage->guid);
					$html .= '</li>'."\n";
				}
				$html  = '<ul>'.$html.'</ul>'."\n";
			}
			
			// remove excess HTL tags
			$html = str_replace("</li>\n</li>","</li>\n",$html);
			$html = str_replace("</li>\n\n</li>","</li>\n",$html);
			$html = str_replace("<ul>\n</li>","<ul>\n",$html);
			
			return $html;
		}
		
		public function drawEditablePanelsByParent($parent='44c246a6751e2', $siteID=1, $indent = '') {

			// This function returns a list of all panels that the current user has the right to edit
			// Note that it should *exclude* panels that have already been edited by someone else
			// [unless the user is a superuser?]. THIS STILL NEEDS DOING.
			// NOTE that this function closely duplicates drawEditablePagesByParent
			// but the links in this function point to panels.html, not pages.html
			// and there's no need for recursion and therefore no need to indent the list
			global $db, $user, $page;
			
			$html = "\n";

			$query = "SELECT p.guid, p.title, p.locked, p.name, t.template_php as template, t.template_title 
				FROM pages p 
				LEFT JOIN pages_templates t ON t.template_id = p.template 
				WHERE t.template_type=2 AND p.msv=$siteID 
				ORDER BY p.sort_order";
			//print "$query<br>\n";
			
			if ($panels = $db->get_results($query)) {
				$html .= '<ul>'."\n";
				foreach ($panels as $panel) {
					if( $panel->hidden==1 && $panel->locked==1 && $panel->template!='panel.poll.php' ){
						$html .= '';
					}
					else if ($panel->locked == 1 && $panel->template!='panel.poll.php') {
						$html .= '<li><span style="color: #999">'.$indent.$panel->title.'</span></li>'."\n";
					} 
					else if ($panel->template_title == "Custom panel") {
						$html.='';	// Dont show custom panels
					}
					else if($panel->template == 'panelrss.php'){
						$html .= '<li>';
						$html .= '<a href="/treeline/panels/?action=list&amp;guid='.$panel->guid.'">'.$panel->title.'</a>';
						$html .= ' <span style="color:#3a3">['.$page->drawLabel("tl_paedit_panel_rss", "RSS Panel").']</span>';
						$html .= '</li>'."\n";
					}
					else if($panel->template == 'panel.poll.php'){
						$html .= '<li>';
						$html .= '<a href="/treeline/panels/?action=list&amp;guid='.$panel->guid.'">'.$panel->title.'</a>';
						$html .= ' <span style="color:#3a3">['.$page->drawLabel("tl_paedit_panel_poll", "Poll Panel").']</span>';
						$html .= '</li>'."\n";
					}
					else {
						$html .= '<li><a href="/treeline/panels/?action=list&amp;guid='.$panel->guid.'">'.$panel->title.'</a></li>'."\n";
					}
				}
				$html .= '</ul>'."\n";
			}
			return $html;
		}		
			
		
		public function drawPublishablePagesByParent($parent) {
			// This function returns a list of all pages that are ready to be published
			global $db, $user;

			$html = "\n";
			$query = "SELECT p.guid, p.title, p.locked, p.hidden, p.date_created, p.date_modified, p.date_published
					FROM pages p
					LEFT JOIN pages_templates pt ON p.template=pt.template_id
					WHERE p.parent = '$parent' AND pt.template_type=1 
					ORDER BY p.sort_order ASC, p.title ASC";
			
			if( $pages = $db->get_results($query) ) {
				$html .= '<ul>'."\n";
				foreach ($pages as $page) {
					if($page->hidden==1 && $page->locked==1){
						$html .= ''; // don't show hidden and locked pages
					}
					else if ($page->locked == 1) {
						$html .= '<li>'.$indent.$page->title.'</li>'; // show locked pages (sections) as text and not a link
					}
					else if ($page->date_modified > $page->date_published || !$page->date_published) {
						// If we've edited the page since it was last published, OR if we've never published the page [ie, it's a new page]
						$newPage = new Page();
						$newPage->loadByGUID($page->guid);
						if($this->isContentPublishable($page->guid)){ // only show publish/preview link if page is publishable
							$html .= '<li><a href="/treeline/pages/?action=publish&amp;guid='.$newPage->getGUID().'">'.$newPage->getTitle().'</a>';
							if($page->hidden==1){
								$html .= ' <span style="color: #669">[hidden]</span>';
							}
							$html .= ' [<a href="'.$newPage->drawLink().'?mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width=920" class="thickbox" title="Preview this page">Preview</a>]';
						}
					}
					
					$html .= $this->drawPublishablePagesByParent($page->guid);
					$html .= '</li>'."\n";
				}
				$html .= '</ul>'."\n";
			}
			// remove excess HTML tags/empty lists/list items
			$html = str_replace("</li><ul>","<ul>",$html);
			$html = str_replace("</li></li>","</li>",$html);
			$html = str_replace("</li>\n</li>","</li>\n",$html);
			$html = str_replace("</li>\n\n</li>","</li>\n",$html);
			$html = str_replace("</li>\n\n\n</li>","</li>\n",$html);
			$html = str_replace("</li><ul>\n<li>","<ul>\n<li>",$html);
			$html = str_replace("<ul>\n</li>","<ul>\n",$html);
			$html = str_replace("<ul>\n\n</ul>\n","",$html);
			return $html;
		}	

/*		
		public function drawPublishablePanelsByParent($parent='44c246a6751e2', $indent = '') {
			// This function returns a list of all panels that are ready to be published
			global $db, $user, $site;
			$query = "SELECT * FROM get_page_content_properties p
				WHERE p.msv = ".$site->id."
				AND p.name != 'footer' AND p.placeholder='panelcontent'
				AND p.revision_id=1
				ORDER BY p.date_modified";
			print "$query<br>";
			$html = "\n";
			
			if ($panels = $db->get_results($query)) {			
			
				$html .= '<table class="treeline">'."\n";	
				$html .= '<caption>All publishable panels</caption>'."\n";
				$html .= '<thead>'."\n";
				$html .= '<tr>'."\n";
				$html .= '<th scope="col">Preview</th>'."\n";
				$html .= '<th scope="col">Title</th>'."\n";
				$html .= '<th scope="col">Modified on</th>'."\n";
				$html .= '<th scope="col">Modified by</th>'."\n";
				$html .= '<th scope="col">Publish</th>'."\n";
				$html .= '</thead>'."\n";
				$html .= '<tbody>'."\n";	
																 
				foreach ($panels as $panel) {
					unset($rss);
					$newPanel = new Page();
					$newPanel->loadByGUID($panel->guid);

					if($panel->template == 'panelrss.php'){
						$tag = ' <span style="color:#3a3">[RSS Panel]</span>';
					}else if($panel->template == 'panel.poll.php'){
						$tag = ' <span style="color:#3a3">[Poll Panel]</span>';
					}
					$html .= '<tr>'."\n";	
					$html .= '<td class="action preview"><a href="'.$newPanel->drawLink().'?mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width='.$site->getConfig("site_page_width").'" class="thickbox"" title="preview">Preview</a></td>'."\n";	
					$html .= '<td>'.$newPanel->getTitle().$tag.'</td>'."\n";
					$html .= '<!-- <td>'.getUFDatetime($panel->date_modified).'</td> -->'."\n";
					$html .= '<td>'.$panel->date_modified.'</td>'."\n";
					$html .= '<td>'.$panel->modified_by.'</td>'."\n";
					$html .= '<td class="action preview"><a href="/treeline/panels/?action=publish&amp;guid='.$newPanel->getGUID().'" title="publish">Publish</a></td>'."\n";						
					$html .= '</tr>'."\n";	
				}
				$html .= '</tbody>'."\n";	
				$html .= '</table>'."\n";
			}
			return $html;
		}		
		
		public function _drawPublishablePanelsByParent($parent) {
			// This function returns a list of all panels that are ready to be published
			global $db, $user;
			$html = "\n";
			$query = "SELECT p.guid, p.name, p.title, t.template_php as template, p.locked, p.date_created, date_modified, date_published FROM pages p LEFT JOIN pages_templates t ON t.template_id = p.template WHERE p.parent = '$parent' AND p.name!='footer' AND p.hidden = 0 ORDER BY p.sort_order";
			if ($panels = $db->get_results($query)) {
			$html .= '<ul>'."\n";															 
				foreach ($panels as $panel) {
					if ($panel->locked == 1) {
						$html .= '<li>'.$indent.$panel->title.'</li>';
					}
					else if ($panel->date_modified > $panel->date_published || !$panel->date_published) {
						// If we've edited the page since it was last published, OR if we've never published the page [ie, it's a new page]
						$newPanel = new Page();
						$newPanel->loadByGUID($panel->guid);
						if($this->isContentPublishable($panel->guid, 'panelcontent')){ // only show publish/preview link if page is publishable
							$html .= '<li><a href="/panels/?action=publish&amp;guid='.$newPanel->getGUID().'">'.$newPanel->getTitle().'</a>';
							if($panel->template == 'panelrss.php'){
								$html .= ' <span style="color:#3a3">[RSS Panel]</span>';
							}
							$html .= ' [<a href="'.$newPanel->drawLink().'?mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=300&amp;width=250" class="thickbox" target="_blank">Preview</a>]';
							$html .= '</li>'."\n";
						}
					}
				}
				$html .= '</ul>'."\n";
			}
			return $html;
		}	
*/		
		public function isContentPublishable($id, $placeholder = 'content'){
			//only show pages/panels that are publishable i.e. have  a revision_id of 1
			//print "icp($id, $placeholder)<br>\n";
			global $db;
			$publishable=false;
			
			$query = "SELECT revision_id FROM content WHERE parent = '$id' AND placeholder = '$placeholder' ORDER BY revision_id DESC LIMIT 1"; //get results but only show the highest revision_id i.e. 1 or 0.
			$publishable = $db->get_var($query); // run query
			//print "pub($publishable) - $query<br>";

			// Check to see if this is a homepage with any publishable content
			if (!$publishable && $placeholder=="content" && $db->get_var("select parent from pages where guid='$id'")==0) {
				if ($results=$db->get_results("select distinct placeholder from content where parent='$id'")) {
					foreach ($results as $result) {
						$query="SELECT revision_id FROM content WHERE parent = '$id' AND placeholder = '".$result->placeholder."' ORDER BY revision_id DESC LIMIT 1";
						//print "$query<br>";
						if ($db->get_var($query)==1) {
							$publishable=1;
							break;
						}					
					}
				}
			}
			
			// Need to check if this is a landing page and check all its children
			if (!$publishable && $placeholder=="content") {
				$query="select template from pages where guid='$id'";
				//print "$query<br>\n";
				if ($db->get_var($query)==67) {
					$query="SELECT max(revision_id) FROM content c WHERE 	
						c.parent = '$id' 
						GROUP BY c.parent 
						ORDER BY c.revision_id DESC 
						LIMIT 1";
					//print "$query<br>\n";
					$publishable = $db->get_var($query)>0;
				}
			}
			
			return $publishable;
		}
				
		public function drawDeleteablePagesByParent($siteID=1, $parent=0) {
			// This function returns a list of all pages that can be deleted by the user
			// WE NEED TO INSERT PERMISSION CHECKING HERE
			global $db, $user, $site;
			$html = "\n";
			/*
			$query = "SELECT p.guid, p.title, p.locked, p.hidden 
						FROM pages p
						LEFT JOIN pages_templates pt ON p.template=pt.template_id
						WHERE (p.parent = '$parent' AND p.site_id=$siteID AND p.title!='Treeline' AND p.name!='footer') 
						AND p.hidden = 0 AND pt.template_type=1 ORDER BY p.sort_order";
			*/
			
			$query = "SELECT p.guid, p.parent, p.title, p.locked, p.hidden FROM pages p 
						LEFT JOIN pages_templates pt ON p.template=pt.template_id
						WHERE (p.parent = '$parent' AND p.msv=$siteID) 
						AND (pt.template_type=1 OR pt.template_type=3) 
						ORDER BY p.sort_order";	
			//print "$query<br>";
			if ($pages = $db->get_results($query)) {	
			$html .= '<ul>'."\n";	
				// Hidden isn't the best field to use here. I'm using it to "hide" the Treeline pages, but it's not designed to do that
				// It's designed to hide pages from the main menu
				// I think we're going to have to start logging types in the Page table																 
				foreach ($pages as $page) { 
					if( $page->hidden==1 && $page->locked==1){
						$html .= '';
					}else if ($page->locked == 1 || $page->parent==$siteID) {
						$html .= '<li class="locked">'.$page->title."\n";
					}
					else {
						// We should have a "can delete" clause here
						$newPage = new Page();
						$newPage->loadByGUID($page->guid);
						$html .= '<li><a href="/treeline/pages/?action=delete&amp;guid='.$newPage->getGUID().'">'.$newPage->getTitle().'</a> [<a href="'.$newPage->drawLink().'?mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width='.$site->getConfig("site_page_width").'" class="thickbox" target="_blank">Preview</a>] '."\n";
					}
					
					$html .= $this->drawDeleteablePagesByParent($siteID, $page->guid);
					$html .= '</li>'."\n";
				}
				$html .= '</ul>'."\n";
			}
			return $html;
		}			
		
		public function drawDeleteablePanels($siteID) {
			// This function returns a list of all panels that the current user has the right to delete
			// [unless the user is a superuser?]. THIS STILL NEEDS DOING.
			// NOTE that this function closely duplicates drawEditablePagesByParent
			// but the links in this function point to panels.html, not pages.html
			// and there's no need for recursion and therefore no need to indent the list
			global $db, $user;
			$html = "\n";

			$query = "SELECT p.guid, p.title, p.hidden, p.locked, t.template_php as template 
						FROM pages p 
						LEFT JOIN pages_templates t ON t.template_id = p.template 
						WHERE p.parent = '$siteID' AND p.msv=$siteID 
						AND t.template_type=2 ORDER BY p.sort_order";
			//niceError($query);
			
			//echo $query.'<br />';
			
			if ($panels = $db->get_results($query)) {
				$html .= '<ul>'."\n";
				foreach ($panels as $panel) {
					if( $panel->hidden==1 && $panel->locked==1){
						$html .= '';
					}else if ($panel->locked == 1) {
						$html .= '<li><span style="color: #999">'.$indent.$panel->title.'</span></li>';
					}
					else {
						$html .= '<li><a href="/treeline/panels/?action=delete&amp;guid='.$panel->guid.'">'.$panel->title.'</a>'."\n";
						if($panel->hidden==1){
							$html .= ' <span style="color: #669">[hidden]</span>'."\n";
						}
						if($panel->template == 'panelrss.php'){
							$html .= ' <span style="color:#3a3">[RSS Panel]</span>';
						}
						if($panel->template == 'panel.poll.php'){
							$html .= ' <span style="color:#3a3">[Poll Panel]</span>';
						}
						$html .= '</li>';
					}
				}
				$html .= '</ul>'."\n";
			}
			return $html;
		}				
		
		public function drawDeleteablePanelsByParent($parent,$siteID) {
			// This function returns a list of all panels that the current user has the right to edit
			// Note that it should *exclude* panels that have already been edited by someone else
			// [unless the user is a superuser?]. THIS STILL NEEDS DOING.
			// NOTE that this function closely duplicates drawEditablePagesByParent
			// but the links in this function point to panels.html, not pages.html
			// and there's no need for recursion and therefore no need to indent the list
			global $db, $user;
			$html = "\n";

			$query = "SELECT p.guid, p.title, p.hidden, p.locked, t.template_php as template 
						FROM pages p 
						LEFT JOIN pages_templates t ON t.template_id = p.template 
						WHERE p.parent = '$siteID' AND p.msv=$siteID 
						AND t.template_type=2 
						ORDER BY p.sort_order";
			niceError($query);
			
			if ($panels = $db->get_results($query)) {
				$html .= '<ul>'."\n";
				foreach ($panels as $panel) {
					if( $panel->hidden==1 && $panel->locked==1){
						$html .= '';
					}else if ($panel->locked == 1) {
						$html .= '<li style="color: #999">'.$panel->title;
					}
					else {
						if($panel->template == 'panelimages.php'){
							$endtag = '<span style="color: #696">[banner panel]</span></li>';
						}else{
							$endtag = '</li>';
						}
						$html .= '<li><a href="/treeline/panels/?action=delete&amp;guid='.$panel->guid.'">'.$panel->title.'</a>'."\n";
						if($panel->hidden==1){
							$html .= ' <span style="color: #669">[hidden]</span>'."\n";
						}
						if($panel->template == 'panelrss.php'){
							$html .= ' <span style="color:#3a3">[RSS Panel]</span>';
						}
						$html .= '</li>';
					}
				}
				$html .= '</ul>'."\n";
			}
			return $html;
		}	
		
		// 6th Jan 2009 - Phil Redclift
		// Collect a list of recently modified resources....
		public function getAllRecentItems($timeframe, $type, $sortBy = 'newest', $msv = 1, $limit=5){
			global $db;
			//print "gARI($timeframe, $type, $sortBy, $msv, $limit)<br>\n";
			
			// SQL ORDERING
			switch($sortBy){
				default:
				case 'newest':
					$orderBy = 'item_date DESC';
				break;
				case 'oldest':
					$orderBy = 'item_date ASC';
				break;
				case 'az':
					$orderBy = 'title ASC';
				break;
				case 'za':
					$orderBy = 'title DESC';
				break;
			}
			
			if($type == 'panels'){
				$placeholder = " AND placeholder IN ('panelcontent', 'question', 'response') AND placeholder != 'panels'";
			}
			else if($type == 'pages'){
				$placeholder = " AND placeholder IN ('content')";
			}
			
			$query = "SELECT * 
				FROM get_recent_activity 
				WHERE msv=$msv 
				AND template<>23
				".$placeholder." 
				ORDER BY ".$orderBy." 
				LIMIT ".$limit;
			//print "$query<br>\n";
			$results = $db->get_results($query);
			
			return $results;
			
		}
		
		// 6th Jan - Phil Redclift
		// Draw a list of recent activity to be shown on the homepage.
		// Timeframe limits the results to items modified within this many days.
		// Type may be page/panel
		public function drawAllRecentItems($msv=1, $timeframe = 14, $type ='all', $sortBy='newest'){
		
			// turn results from getAllRecentItems() into HTML
			global $user, $help, $page;
			
			$results = $this->getAllRecentItems($timeframe, $type, $sortBy, $msv, 6);
			$html='';
			
			// order by/sort SQL table ehade links
			$titleSort = ($sortBy == 'az') ? 'za' : 'az';
			$dateSort = ($sortBy == 'newest') ? 'oldest' : 'newest';
			
			if($results){
				foreach($results as $result){
					$page = new Page();
					
					// Find the guilty party details and changed date
					if ($result->item_date==$result->date_created) {
						$edit_date=$result->nice_created;
						$edit_user=$result->created_name;
						$date_hint=($result->nice_created?$page->drawLabel("tl_recent_pagedate", "New page created")." ".$result->nice_created:"");
						$user_hint=($result->created_name?$page->drawLabel("tl_recent_pageby", "New page created by")." ".$result->created_name:"");
					}
					else if ($result->item_date==$result->date_modified) {
						$edit_date=$result->nice_modified;
						$edit_user=$result->modified_name;
						$date_hint=($result->nice_created?$page->drawLabel("tl_recent_createdate", "Page created")." ".$result->nice_created."<br>":"").$page->drawLabel("tl_recent_editdate", 'Lasted edited').' '.$result->nice_modified;
						$user_hint=($result->created_name?$page->drawLabel("tl_recent_createby", "Created by")." ".$result->created_name."<br>":"").$page->drawLabel("tl_recent_editby", 'Lasted edited by').' '.$result->modified_name;
					}
					else if ($result->item_date==$result->date_published) {
						$edit_date=$result->nice_published;
						$edit_user=$result->published_name;
						$date_hint=($result->nice_created?$page->drawLabel("tl_recent_createdate", "Page created")." ".$result->nice_created."<br>":"").($result->nice_modified?$page->drawLabel("tl_recent_editdate", 'Lasted edited').' '.$result->nice_modified."<br>":"").$page->drawLabel("tl_recent_publishdate", 'Published').' '.$result->nice_published;
						$user_hint=($result->created_name?$page->drawLabel("tl_recent_createby", "Created by")." ".$result->created_name."<br>":"").($result->modified_name?$page->drawLabel("tl_recent_editby", 'Lasted edited by').' '.$result->modified_name."<br>":"").$page->drawLabel("tl_recent_publishby", 'Published by').' '.$result->published_name;
					}
					
					$status=$page->drawPageStatus($result->modified_name, $result->published_name, $result->publishable, $result->offline, $result->locked);
					
					// Set the page hint text.
					$title_hint='';
					if ($result->template_type!=2) $title_hint=$page->drawLinkByGUID($result->guid);

					//$panel_type = ($result->placeholder == 'question') ? ' <span style="color:#3a3">[Poll]</span>' : '';
					
					$html .= '<tr>
	<td '.$help->drawInfoPopup($title_hint).' class="title">'.substr($result->title, 0, 30).'</td>
	<td nowrap>'.$page->drawLabel("tl_p_".str_replace(" ", "-", $result->template_title), $result->template_title).'</td>
	<td nowrap '.$help->drawInfoPopup($date_hint).'>'.$page->languageDate($edit_date).'</td>
	<td '.$help->drawInfoPopup($user_hint).'>'.$edit_user.'</td>
	<td>'.$page->drawLabel("tl_p_status_".$status, $status).'</td>
	<td class="action">'.$page->drawEditCheckboxes($result->guid, $result->template_type==2?"panel":"page", $result->template_title, $result->template, $result->publishable, $result->locked, $result->offline).'</td>
</tr>
';
				}
				
				$html='<table class="tl_list">
<caption>'.$page->drawLabel("tl_recent_tab_title", "Recent updates").'</caption>
<thead>
	<tr>
	<th scope="col">'.$page->drawLabel("tl_recent_th_title", "Title").'</th>
	<th scope="col">'.$page->drawLabel("tl_recent_th_type", "Type").'</th>
	<th scope="col">'.$page->drawLabel("tl_recent_th_updated", "Updated").'</th>
	<th scope="col">'.$page->drawLabel("tl_recent_th_lastuse", "Last used by").'</th>
	<th scope="col">'.$page->drawLabel("tl_recent_th_status", "Status").'</th>
	<th scope="col" colspan="5">'.$page->drawLabel("tl_recent_th_action", "Manage this page").'</th>
	</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
			}
			return $html;
		}
		
		public function drawAllItemsTypesDropDown($type){
			// This function draws a drop down menu alloing users to select a a content type
			
			$options = array('pages','panels'/*,'images','files'*/); // this array stored the drop down menu's value.
			
			$html = '<label for="type" class="hide">Choose content:</label>'."\n";
			$html .= '<select id="type" name="type">'."\n";
			foreach($options as $option){
				unset($selected); // reset the variable below so all items are marked as selected
				if($type == $option){ //if this option is the current selected one then make it selected in the drop down menu
					$selected = ' selected="selected"';
				}
				$html .= '<option value="'.$option.'"'.$selected.'>'.$option.'</option>'."\n";
			}
			$html .= '</select>'."\n";
			
			return $html;
		}
		
		public function drawAllItemsActionsDropDown($action){
			// This function draws a drop down menu alloing users to select a action e.g. edit or publish
			
			$options = array('edited','published','created'); // this array stored the drop down menu's value.
			
			
			
			$html = '<label for="action" class="hide">Choose action:</label>'."\n";
			$html .= '<select id="action" name="action">'."\n";
			foreach($options as $option){
				unset($selected); // reset the variable below so all items are marked as selected
				if($action == $option){ //if this option is the current selected one then make it selected in the drop down menu
					$selected = ' selected="selected"';
				}
				$html .= '<option value="'.$option.'"'.$selected.'>'.$option.'</option>'."\n";
			}
			$html .= '</select>'."\n";
			
			return $html;
		}
		
		public function drawAllRecentItemsDropDown($timeframe){
			// This function draws a drop down menu alloing users to select a timeframe
			
			$timeframe_options = array(
			1=>'Yesterday', 2=>'in the last 2 days', 7=>'this week',14=>'this fortnight', 30=>'this month', 90=>'in the last 3 months', 180=>'in the last 6 months',365=>'this year'
			); // this array stored the drop down menu's value.
			
			
			
			$html = '<label for="days" class="hide">Choose timeframe:</label>'."\n";
			$html .= '<select id="days" name="days">'."\n";
			foreach($timeframe_options as $timeframe_option => $text_value){
				unset($selected); // reset the variable below so all items are marked as selected
				if($timeframe == $timeframe_option){ //if this option is the current selected one then make it selected in the drop down menu
					$selected = ' selected="selected"';
				}
				$html .= '<option value="'.$timeframe_option.'"'.$selected.'>'.$text_value.'</option>'."\n";
			}
			$html .= '</select>'."\n";
			
			return $html;
		}
		
		
	}
	
?>