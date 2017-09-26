<?php	
	class Placeholder {
	
		// A couple of conventions:
		
		// load() loads an object
		// save() saves an object
		// get() gets a value
		// put() updates a value ?
		// draw() outputs data as HTML, or a string usable in an HTML page
	
		public $guid; // Should this be private? Should ALL these be private because we access them via functions?
		public $content;
		public $name;
		public $title;
		public $revision_id;
		public $revision_date;
		public $blog_date;
		public $age;
		public $mode;
		public $parent;
				
		public function __construct() {
			// This is loaded when the class is created	
			$this->setMode('view'); // set the default mode
		}	
		
		public function setMode($mode) {
			$this->mode = $mode;
		}
		
		public function setContent($content) {
			$this->content = $content;
		}
		
		public function getMode() {
			return $this->mode;
		}
		
		public function getGUID(){
			return $this->guid;
		}
		
	}
	
	class HTMLPlaceholder extends Placeholder {

		public $height;
		public $width;

		public function __construct() {
			// This is loaded when the class is created
			// Set some default dimensions for a WYSIWYG area:
			$this->height = '500px';
			$this->width = '100%';			
		}	

		function setHeight($height) {
			$this->height = $height;
		}
		
		function setWidth($width) {
			$this->width = $width;
		}		

		public function load($parent,$name,$restore_id = 0, $revision_id=0) {
				
			global $db, $page, $mode, $siteData;
			//print "load in mode($mode) this mode(".$this->mode.")<br>\n";
			// If we're editing or previewing the page, we want to look at the very latest version, so:
			$query = "SELECT c.*,
				date_format(c.revision_date, '%D %M %Y') as blog_date, datediff(now(), c.revision_date) as age 
				FROM content c WHERE parent = '$parent' AND placeholder = '$name'";
			if ($mode == 'edit' || $mode == 'preview' || 
				$mode == 'save' || 
				$this->mode=="inline-preview" || $this->mode=="inline-edit") 
			{
				$query .= ' ORDER BY revision_id DESC LIMIT 1';	
			}
			else if($mode == 'restore' ){ // restore mode shows content based on id (from query string)
				$query .= ' AND id = '.$restore_id;	
			}
			else {
				$query .= ' AND revision_id = '.($revision_id+0);	
			}
			//print "Load(".$mode.") placeholder ($query)<br>";
			
			$data = $db->get_row($query);	
			if ($db->num_rows == 0) {
				//print "failed to load($query)<br>";
				if ($mode == 'edit' || $this->getMode()=="inline-edit") {
					// If the placeholder doesn't appear in the content table, and we're trying to editing it, insert it into the database
					// This situation arises when we're editing a newly created page for the first time
					$guid = uniqid();
					$query = "INSERT INTO content (guid, parent, content, revision_id, revision_date, placeholder)
										VALUES ('$guid','$parent', '', 1, NOW(), '$name')";
					//print "insert ($query)<br>";
					$db->query($query);
					$this->loadByGUID($guid);				
				}
			}
			else {
				$this->guid = $data->guid;
				$this->parent = $data->parent;
				//$this->content = html_entity_decode($data->content);
				//print "got conent(".$data->content.")<br>\n";
				$this->content = html_entity_decode($data->content, ENT_QUOTES, $siteData->encoding);
				//print "decoded(".$this->content.")<br>\n";
				$this->name = $data->placeholder.($data->placeholder=="panelcontent"?"-".$this->parent:"");
				$this->revision_id = $data->revision_id;
				$this->revision_date = $data->revision_date;
				$this->blog_date = $data->blog_date;
				$this->age = $data->age;
				$this->title = $data->title;
				
				$request = read($_REQUEST,'treeline_'.$name,false);
				//echo "REQUEST['treeline_$name']: " . $request;
				if ($request !== false) {
					// If we've passed the content in via a $_REQUEST, that overrules the database
					// This is used when we're updating the panels on the page
					// Although it may also be used with $_SESSION when previewing the page
					$this->content = $request;
				}
			}
		}
		
		public function loadByGUID($guid) {
			global $db, $page;
			$query = "SELECT c.*, 
				date_format(c.revision_date, '%D %M %Y') as blog_date, datediff(now(), c.revision_date) as age 
				FROM content c WHERE guid = '$guid'";
			// If we're editing or previewing the page, we want to look at the very latest version, so:
			if ($page->getMode() == 'edit' || $page->getMode() == 'preview' || 
				$page->getMode() == 'save' ||
				$this->getMode()=="inline-edit" || $this->getMode=="inline-preview") {
				$query .= ' ORDER BY revision_id DESC LIMIT 1';	
			}
			else {
				$query .= ' AND revision_id = 0';	
			}
			//print "load content($query)<br>\n";			
			$data = $db->get_row($query);				
			if ($db->num_rows > 0) {
				$this->guid = $data->guid;
				$this->parent = $data->parent;
				$this->content = html_entity_decode($data->content);
				$this->name = $data->placeholder.($data->placeholder=="panelcontent"?"-".$this->parent:"");
				$this->revision_id = $data->revision_id;
				$this->revision_date = $data->revision_date;
				$this->blog_date = $data->blog_date;
				$this->age = $data->age;
				$this->title = $data->title;
				//print "set name(".$this->name.")<br>\n";
			}
		}		
		
		public function draw($class="") {
			// This draws a placeholder that will contain a chunk of HTML content
			// OR draws the TinyMCE editor if we're in EDIT mode
			
			//print "show content(".$this->getMode().") (".$this->content.")<br>\n";
			$editor_id = "treeline_".$this->name;
			if ($class=="MCElandingPanel") $this->setHeight("250px");
			//if ($class=="mcePanelEditor") $editor_id .= $this->guid;
			
			$html = '';
			
			if (!$class) $class="treeline_".$this->name;
			//print "<!-- d::(".$this->name.") m(".$this->getMode().") -->\n";
			if ($this->getMode() == 'edit' || $this->getMode()=="inline-edit" || $this->mode=="inline") {
				$html .= '<textarea name="treeline_'.$this->name.'" id="'.$editor_id.'" rows="5" cols="5" class="'.$class.'" style="width: '.$this->width.'; height: '.$this->height.'">';
				$html .= $this->content;
				$html .= '</textarea>';
				// describe yellow background feature to user
				/*$html .= '<p><strong>Why is it yellow?</strong></p>
				<p>Incorrectly formatted content (or content to which a format has not yet been applied) appears on a yellow background. Before saving your changes, ensure all content is on a white background.</p>
				<p>It is normal for spaces between paragraphs to appear yellow.</p>';*/
			}
			else {
				$html .= $this->content;
			}
			//print "<!-- d($html) -->\n";
			return $html;
		}
		
		public function save($revID=1) {
			// This should read in the $_POSTed treeline_[name] variable and save it in the content table
			global $db, $site, $siteData;
			$html = read($_POST,"treeline_".$this->name,'');
			//print "got treeline_".$this->name." html($html)<br>\n";
			// First strip out al the absolute links to this site
			//$html = str_replace($site->link, "/", $html);

			$encoding = $_SESSION['treeline_user_encoding']?$_SESSION['treeline_user_encoding']:$siteData->encoding;
			
		 	$html = href_replace($html);	
			$html = htmlentities($html,ENT_QUOTES,$encoding);
			//print "entities ($html)<br>\n";
			$html = $db->escape($html);
			//print "escaped ($html)<br>\n";
			$title = $db->escape( htmlentities($this->title,ENT_QUOTES,$encoding) );
			//print "save html($html)<br>"; exit();
			//print "Got revision(".$this->revision_id.") rev to save($revID)<br>\n";
			if ($this->revision_id == $revID) {
				// If we're editing an edit
				$query = "UPDATE content SET content = '$html', title='$title', revision_date = NOW() WHERE guid = '{$this->guid}' AND revision_id = $revID";
			}
			else {
				$name = $this->name;
				$guidlen = strlen($this->guid);
				//$guid_ending = substr($this->name, -$guidlen);
				//print "guid end($guid_ending==".$this->guid.")<br>\n";
				if (substr($this->name, -$guidlen)==$this->parent) $name = substr($this->name, 0, -($guidlen+1));
				// If this is the first time this content has been edited since it was last approved
				$query = "INSERT INTO content
					(guid,parent, content, revision_id, revision_date, placeholder, title)
					VALUES (
						'{$this->guid}',
						'{$this->parent}',
						'$html', 
						$revID, 
						NOW(), 
						'$name', 
						'$title'
					)
					";
			}
			//print "$query<br>\n";
			$db->query($query);
		}			
		
		public function delete($revID='') {
			global $db;
			$query="delete from content where guid='{$this->guid}'";
			if ($revID) $query.=" and revision_id=$revID";
			//print "QRY - $query<br>";
			return $db->query($query);
		}
		
		
		// Bit of a fix.
		// Need to strip out text and background image and change content
		public function formatAsBranding() {
			global $site, $browser;
			//print "fAB(".$this->content.")<br>\n";
			$browser_version = browser_detection("number");
			$browser_name = browser_detection("browser");
			
			// Find the first image and pull it out as a background. 
			if (preg_match_all("/<img src=\"(.*?)\"/", $this->content, $reg, PREG_SET_ORDER)) {
				//print "match count(".count($reg).") ".print_r($reg, true)."<br>\n";
				$tmp=rand(0, (count($reg)-1));
				$css.= '
					div#header_img {
						background-image: url(\''.$reg[$tmp][1].'\');
					}	
				';
			}
			
			// This is a bit of a nightmare but we need to apply the alpha filter to ie6,
			// even though the image can change so have to do this here.
			if ($site->logo>0 && $site->logo_filename) {
				$logo_file=$_SERVER['DOCUMENT_ROOT']."/img/logos/".$site->logo_filename;
				if (file_exists($logo_file)) {
					$logo_size = getimagesize($logo_file);
					if ($browser_name=="ie" && ($browser_version=="6" || $browser_version=="6.0" || $browser_version=="5.5")) {
						$css.='
							div#header_img div#branding {
								background-image: none;
								height:'.$logo_size[1].'px;
								width:'.$logo_size[0].'px;
								filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'/img/logos/'.$site->logo_filename.'\',sizingMethod=\'crop\'); 
								position: relative;
							}
						';
					}
					else {
						$css.='
						div#header_img div#branding {
							'.($logo_size[0]>0?"width: ".$logo_size[0]."px;":"").'
							'.($logo_size[1]>0?"height: ".$logo_size[1]."px;":"").'
							background-image: url(\'/img/logos/'.$site->logo_filename.'\');
						}
						';
					}
										
					// We should really now check if we are using internet explorer v6 or below
					// and add PNG stuff for that too!!!
				}
				//else print "logo file($logo_file) does not exist<br>\n";
			}
				
			// Stick the branding and tagline on top
			$this->content='';
			if ($site->logo>0) $this->content = '<div id="branding">'.$site->properties['tagline'].'</div>';
			$this->content.= '<h1><a href="'.$site->link.'">'.$site->properties['tagline'].'</a></h1>';
				
			// If we cant find an image then just leave the content alone
			return $css;
		}

	}		

	class PanelsPlaceholder extends Placeholder {
		
		public $panels = array();

		public function __toString(){
			return Placeholder::getMode() .' - '. $this->panels[1];
		}

		public function drawSelectablePanels($include=array(), $exclude=array(), $order_by="p.title") {
			
			global $db, $site;
			
			$style = $exguids = '';
			
			if (count($include)) {
				foreach($include as $tmp) $tmp_style.=$tmp.",";
				$style="AND style IN(".substr($tmp_style,0,-1).") ";
			}
			if (count($exclude)) {
				foreach($exclude as $tmp) $tmp_style.=$tmp.",";
				$style.="AND style NOT IN(".substr($tmp_style,0,-1).") ";
			}

			// Don't offer to add any panels already on the page
			// IDs clash and screw things up.
			if (count($this->panels)) {
				foreach ($this->panels as $panel) $exguids.="'".$panel."',";
				$exguids = "AND p.guid NOT IN(".substr($exguids, 0, -1).") ";
			}

			// Have to fudge a little for left/right panels
			$query = "SELECT p.*,
				pt.template_title AS paneltype
				FROM pages p
				LEFT JOIN content c on p.guid=c.parent
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE (c.revision_id=0 OR c.revision_id IS NULL)
				AND p.parent='".$site->id."'
				AND pt.template_type=2 
				AND pt.template_title <> 'Custom panel'
				".$style.$exguids."
				GROUP BY p.guid
				ORDER BY ".$order_by;
			//print "$query<br>";
			
			// Now, we need to select all "panels" -- but we don't actually track content-type
			// What this needs to say is, select all children of the "panels" page in this page's CMS
			// Hardcode this for now:
			//$panels = $db->get_results("SELECT * FROM pages WHERE parent = '44c246a6751e2' AND name!='footer'");
			//$query = "select p.* from pages p left join content c on p.guid=c.parent where c.revision_id=0 and p.parent=$siteID AND (c.placeholder = 'panelcontent' OR c.placeholder = 'question') group by p.guid"
			$panels = $db->get_results($query,"ARRAY_A");

			if ($db->num_rows > 0) {
				//echo '<pre>'. print_r($panels,true) .'</pre>';
				$tmp = array();
				foreach ($panels as $panel) {
					$tmp[$panel['paneltype']][] = $panel;
				}
				foreach ($tmp as $key => $item) {
					$opthtml .= '<optgroup label="'. $key .'">'."\n";
					foreach( $item as $panel ){
						$opthtml .= '<option value="'.$panel['guid'].'">'.$panel['title'].'</option>'."\n";
					}
					$opthtml .= '</optgroup>'."\n";
				}
			}
			return $opthtml;
		}	
	
	
		public function load($parent,$name,$revID=1) {
			global $db, $page, $mode, $site;

			// This function loads in a series of comma-delimited GUIDs that refer to the page table
			
			$this->name = $name;
			$this->parent = $parent;			
			$request = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET,'treeline_'.$name,false);
			//print "load request($request)<br>\n";
			//print_r($_REQUEST);
			
			// If we're editing or previewing the page, we want to look at the very latest list of panels, so:
			$query = "SELECT guid, content, revision_id FROM content WHERE parent = '$parent' AND placeholder = '$name'";
			
			if ($mode=='edit' || $mode=='preview' || $mode=='save') {
				$query .= ' ORDER BY revision_id DESC LIMIT 1';	
			}
			else if($page->getMode() == 'restore'){
				$query .= ' AND id = '.$_GET['version'];	
			}
			else {
				$query .= ' AND revision_id = 0';	
			}			
//			print "$query<br>";			

			if ($data = $db->get_row($query)) {
				$this->guid = $data->guid;
				$this->panels = explode(",",$data->content);
				$this->revision_id = $data->revision_id;
			}
			else {
				if ($page->getMode() == 'edit') {
					// If the placeholder doesn't appear in the content table, and we're trying to editing it, insert it into the database
					// This situation arises when we're editing a newly created page for the first time
					$guid = uniqid();
					$query = "INSERT INTO content (guid, parent, content, revision_id, revision_date, placeholder)
										VALUES ('$guid','$parent', '', $revID, NOW(), '$name')";
					$db->query($query);
					$this->guid = $guid;
					$this->panels = array();
					$this->revision_id = $revID;			
				}
			}			
			if ($request !== false) {
				// If we've passed the panels in via a $_POST, that overrules the database
				// This is used when we're updating the panels on the page
				//echo "FOUND UPDATED PANELS: " . $request;
				$tmp = explode(",",$request);
				$this->panels=array();
				foreach ($tmp as $panel_id) {
					if ($panel_id=="new") {
						// CREATE A NEW CUSTOM PANEL
						$new_panel = new Page;
						$new_panel->setParent($site->id); 	// Set it to be a child of this page -- that's how we know it's a panel belonging to this site
						$new_panel->setTitle("new-".time());
						$name = $new_panel->generateName();
						$new_panel->setStyle(9);
						$new_panel->setHidden('0');
						$new_panel->setSortOrder();					
						$new_panel->setTemplate(23);
						$new_panel->setMetaDescription('A new custom panel');
						$new_panel->create(2);
						$panel_id=$new_panel->getGUID();
					}
					if ($panel_id) {
						//print "Add panel ID($panel_id)<br>\n";
						$this->panels[]=$panel_id;
					}
				}
				//print "got panels(".print_r($this->panels, true).")<br>\n";
			}
		}
	
		public function draw($include=array(), $exclude=array()) {
			
			// This function loops through all the GUIDs we've loaded
			// and draws out each "page" [panel] according to its template
			// In edit mode, we need to add a drop-down list above each one to allow the panel to be changed
			// and an additional drop-down list to allow a new panel to be added
			// How are we going to do this without repeatedly $_POSTing the page? Can we use Ajax here? I suspect that's over-complicating it...
			// Ah, but we can surely just "get" the page with a new list of comma-delimited panels, can't we?
			global $db, $site, $mode, $labels, $siteLink, $previewMsgShown;
			
			$html = '';

			$order_by="p.title";

			// Generate the panel options list
			$style="";
			
			//print "load panel in mode(".$this->mode.")<BR>\n";
			if ($this->mode == 'edit' || $this->mode == 'inline') {
				// If we're editing the containing page, we need to add a hidden value that tracks what panels we've added in this section:
				$html .= '
				<input type="hidden" name="treeline_'.$this->name.'" id="treeline_'.$this->name.'" value="'.implode(',',$this->panels).'" />
				<input type="hidden" name="delete_panel" value="" />
				';
				//$html.=Page::drawPanelTinyMCE();

				// We also need to add the "Add a panel here" option, if we're editing the page:
				$opthtml = $this->drawSelectablePanels($include, $exclude, $order_by);
				$opthtml = '
					<option value="xx" style="color: #f00">Add a panel</option>
					<option value="new" style="color: #f00">Create a new panel</option>
					'.$opthtml;
				//else $opthtml = '<option value="" style="color: #f00">--no panels--</option>';
				$html .= '
				<fieldset>
					<label for="treeline_'.$this->name.'_add" class="hide">Add a panel</label>
					<select name="treeline_'.$this->name.'_add" style="width:'.$panel_width.'px;" id="treeline_'.$this->name.'_add" onchange="addPanel(\''.$this->name.'\',this)">
						'.$opthtml.'
					</select>
				</fieldset>
				';
			}
		
			//$global_edhtml = '';	// As each panel draws it adds something here.
			foreach ($this->panels as $panel) {
				// This needs to read the panel from the PAGE table
				// and then dynamically include the panel within the page

				ob_start();

				$page = new Page();
				if ($page->loadByGUID($panel)) {
			
					// This checks that the page exists before we load it
					if ($mode == 'edit' || $this->mode == "inline") {
						$page->setMode('inline'); // If we're editing the containing page, we need to put the panel in 'inline' mode:
						// We now need to add the dropdown list to each panel:

						/*
						$query = "select p.title, p.guid 
							from pages p 
							left join content c on p.guid=c.parent 
							left join pages_templates pt ON p.template=pt.template_id
							where c.revision_id=0 and p.parent=".$site->id."
							AND pt.template_type=2
							".(($style!="")?$style:"")."
							GROUP BY p.guid
							ORDER BY p.title
							"; 
						print "$query<br />\n";
						$panels = $db->get_results($query);
						*/
						/*
						if(strtolower(trim($page->getTemplate())) != 'panel.stream.php') {
							$list = "\n";
							$list .= '<fieldset>'."\n";
							$list .= '<label for="treeline_'.$this->name.'_'.$panel.'" class="hide">Panels</label>'."\n";
							$list .= '<select style="width:'.$panel_width.'px;" name="treeline_'.$this->name.'_'.$panel.'" id="treeline_'.$this->name.'_'.$panel.'" class="panelUpdate" onchange="updatePanels(\''.$this->name.'\',this)">'."\n";
							*/
							/*
							foreach ($panels as $panel) {
								$selected = ($page->getGUID() == $panel->guid)?'selected="selected"':'';
								$list .= '<option value="'.$panel->guid.'" '.$selected.'>'.$panel->title.'</option>'."\n";
							}
							*/
							
							// alexph: Restrict list of options to 'Delete Panel' if panel is of
							// a particular template
							// in this case the 'workstreams' panel
							/*
							$list .= str_replace('value="'.$page->getGUID().'"', 'value="'.$page->getGUID().'" selected="selected"', $opthtml);
							$list .= '<option value="DELETE" style="color: #f00">Delete this panel</option>'."\n";
							$list .= '</select>'."\n";
							$list .= '</fieldset>'."\n";
							
							echo $list;
						}	*/
						
					}
					else {
						$page->setMode('view');
					}
					$template = $page->getTemplate();
					if ($page->template_type=="panel") $template="panel.php";
					//include($_SERVER['DOCUMENT_ROOT'].'/'.$page->getTemplate());
					include($_SERVER['DOCUMENT_ROOT'].'/'.$template);

					$html .= ob_get_contents();
				}
				ob_end_clean();
				
			}
			//$html.=$global_edhtml;

			return $html;
		}


		public function save($revID=1) {
//			echo read($_POST,'treeline_'.$this->name,'');
			// This should read in the $_POSTed treeline_[name] variable and save it in the content table
			global $db, $site, $siteData;
			
			//print "POST(".print_r($_POST, true).")<br>\n";
			foreach ($this->panels as $panel) {
				$page = new Page();
				if ($page->loadByGUID($panel)) {

					// 2 - Save the content of each panel			
					if (isset($_POST['treeline_panelcontent-'.$panel])) {
						//print "SAVE PANEL ($panel)<br>\n";
						if ($page->template_id == 6 || $page->template_id==23) {
							$content = new HTMLPlaceholder();
							$content->load($panel, 'panelcontent');
							$content->setMode($mode);
							$content->save();
	
							// Do we need to update the page title/style?
							if ($_POST['title-'.$panel] && $_POST['title-'.$panel]!=$_POST['xtitle-'.$panel]) {
								$page->setTitle($_POST['title-'.$panel]);
							}
							if ($_POST['style-'.$panel]>0) $page->style_id=$_POST['style-'.$panel];
							$page->save();
						}
					}
				}
			}

			// 3 - All going well, save the panel order
			$html = read($_POST,"treeline_".$this->name,'');
			$html  = htmlentities($html,ENT_QUOTES,$siteData->encoding);
			if ($this->revision_id == $revID) {
				// If we're editing an edit
				$query = "UPDATE content SET content = '$html', revision_date = NOW() WHERE guid = '{$this->guid}' AND revision_id = $revID";
			}
			else {
				// If this is the first time this content has been edited since it was last approved
				$query = "INSERT INTO content
					(guid,parent, content, revision_id, revision_date, placeholder)
					VALUES 
					(
						'{$this->guid}',
						'{$this->parent}',
						'$html', 
						$revID, 
						NOW(), 
						'{$this->name}'
					)
					";
			}
			$db->query($query);			
			unset($page);
			
		}

		
		// Publish all panels on a page.
		// Library panels are publish if they have changed which will affect all pages that contain this panel.
		public function publish() {
			global $treeline;
			
			//print "POST(".print_r($_POST, true).")<br>\n";
			//print "Publish panels<br>\n";
			foreach ($this->panels as $panel) {
				//print "Do I need to publish($panel)<br>\n";
				if($treeline->isContentPublishable($panel, 'panelcontent')) { 
					$page = new Page();
					if ($page->loadByGUID($panel)) {
						//print "Would publish page($panel)<br>\n";
						if (!$page->publish()) {
							//print "Failed to publish this page???<br>\n";
						}
					}
				}
				//else print "This panel is not publishable<br>\n";
			}
		}
		
	}		
?>
