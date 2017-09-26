<?php

	/*
	=====================
	Tags Class
	---------------------
	Add/Edit/Manage/View tags aka content keywords
	=====================
	
	written by: Phil Thompson phil.thompson@ichameleon.com
	when: February/March 2007
	
	Amended by : Phil Redclift september 2008
	Microsite and library support was added. Each site has its own tag library
	Language support still needs to be added next time this installation is used for a language site.
	*/

	class Tags{
	
		public $error=array();
		public $msv;
		public $tag_type;
		public $mode, $parent;
		
		// This is loaded when the class is created	
		public function __construct($msv=1, $tag_type=1) {
			$this->setMode('view'); // set the default mode
			$this->msv=$msv;
			$this->tag_type=$tag_type;
		}	
		
		public function setMode($mode, $parent='') {
			//print "T::sM($mode, $parent)<br>\n";
			$this->mode = $mode;
			if ($parent) $this->parent = $parent;
		}
		
		public function getMode() {
			return $this->mode;
		}
		
		// Add a new tag to the library
		public function addTag($tag){
			global $db, $page;
			unset($this->error);

			if ($tag) {
				$tag=preg_replace("/[`�{}><�$\/\\%^]/", "", strtolower(trim($tag)));
				//print "add tag($tag)<br>";
				$tag_exists = $this->getTagID($tag);
				if(!$tag_exists){
					$query = "INSERT INTO tags (tag, msv) VALUES ('".$db->escape($tag)."', ".$this->msv.");";
					if($db->query($query)){
						return true;
					}
				}
				else $this->error[] = $page->drawLabel("tl_tag_err_exists", "Tag already exists")."[$tag]";
			}
			else $this->error[]= $page->drawLabel("tl_tag_err_addnone", "No tag specified");
			return 0;
		}		
		
		
		// Remove a link between a tag and a resource		
		public function removeTagRelationship($guid){
			global $db;
			//print "rTR($guid)<br>\n";
			$query =  "DELETE FROM tag_relationships WHERE guid = '$guid'";
			//print "$query<br>\n";

			if(@$db->query($query)) return true;
			return false;
		}

	 	// add a new tag relationship e.g. add a tag to a page/file/image
		public function addTagRelationship($guid, $tag_id, $type=1){
			global $db;
			//print "aTR($guid, $tag_id, $type)<br>\n";
			// First check if a tag relationship already exists.			
			if(!$this->checkTagRelationship($guid, $tag_id)){
				$query = "INSERT INTO tag_relationships VALUES ('$guid',$type, $tag_id);";
				//print "$query<br>\n";
				if(@$db->query($query)){
					return true;
				}
				else $this->error[] = "Failed to insert tag relationship";
				return 0;
			}
			// Relationship exists already just retrun true
			// even though we didnt do anything.
			return true;
		}
		//check to see if a tag relationship already exist
		public function checkTagRelationship($guid, $tag_id){
			global $db;
			$query = "SELECT * FROM tag_relationships WHERE guid= '$guid' AND tag_id = '$tag_id'";
			$results = $db->get_results($query);
			if($results) return true;
			return false;
		}
		
		
		public function addTagsToContent($guid, $tagslist){
			//take the user entered tags and assign them to the content
			// NB: $tagslist should be a comma separated list
			global $db;
			//print "aTTC($guid, $tagslist)<br>\n";
			
			// convert comma separated taglist into an array
			$taglist_array = explode(",",$tagslist);
			
			foreach($taglist_array as $tags){
				$taglist[] = trim($tags); //remove whitespace
			}
			
			if(!$guid){ // guid won't be present on a newly created page so we have to find it
				$grouping = ''; //set GROUP BY clause (blank unless it's an image)
				switch($this->tag_type){
					case 1://content
					default:
						$content_type = 'pages';
						$query = "SELECT guid FROM $content_type $grouping ORDER BY date_created DESC LIMIT 1";
					break;
					case 2://images
						$content_type = 'get_formatted_image_list';
						$query = "SELECT guid FROM $content_type WHERE parent = 0 ORDER BY datemade DESC LIMIT 1";
					break;
					case 3://files
						$content_type = 'files';
						$query = "SELECT guid FROM $content_type $grouping ORDER BY date_created DESC LIMIT 1";
					break;
				}
				//print "$query<br>\n";
				$results = $db->get_results($query);
				if($results){
					foreach($results as $result){
						$guid = $result->guid; // set the guid to be the guid of the most recently added page
					}
				}
				else{ // or return false and don't any more functions
					return false;
				}
			}
			
			$this->removeTagRelationship($guid); // remove old tag relationships
			
			foreach($taglist as $tag){
				if($tag != ''){// tag is empty or just a comma
					if($tag_id = $this->getTagID($tag)){ // if tags are already in the database
						$this->addTagRelationship($guid, $tag_id, $this->tag_type); // add new tag relationships
					}
					else{
						$this->addTag($tag); // add tag
						$tag_id = $this->getTagID($tag); //get newly added tag ID
						if($tag_id){
							$this->addTagRelationship($guid, $tag_id, $this->tag_type); // add new tag relationships
						}
					}
				}
			}
			
		}
		
		public function drawRelatedContentLinks($guid=''){
			//create a list of links that relate to the current page. Good for SEO, site structure etc
			// user specifies a amximum no. of links and how closely (in %s) the links should be related
			global $db, $pageGUID;
			//print "dRCL($guid)<br>\n";
			$content = '';

			if ($guid) {
				if (($current_options = $this->getRelatedContentDetails($guid))) {
					$accuracy = $current_options->accuracy;
					$maxlinks = $current_options->maxlinks;
					$title = $current_options->title;
				}
			}
			
			//edit mode dont show panel
			if($this->mode == 'edit'){
				//$content .= $this->drawToolbar($guid,$accuracy,$maxlinks);
				$content .= $this->drawEditControls($guid, $accuracy, $maxlinks, $title);
			}
			else if ($current_options && $guid) {
				// get contents tags
				//print "get tags for page($pageGUID)<br>\n";
				$tags = $this->drawTags($pageGUID);
				
				$currentTag = $this->drawTags($pageGUID);
				
				//print '<!-- TAGS: '.$currentTag.'-->';
				if($tags){
					$tags = $this->convertTagsToArray($tags);
					$total_tags = sizeof($tags); // total number of tags the page has - to be used for %s
					$i = 1;
					
					$query_accuracy = 'AND ('; // this value will decide how many results appear/the accuracy of the query
					foreach($tags as $tag){
						$query_accuracy .= "t.tag = '$tag'";
						if($i != $total_tags){
							$query_accuracy .= ' OR ';
						}
						$i++;
					}
					$query_accuracy .= ")";
					
					$query = "(
						SELECT p.guid, p.title,p.name, p.date_published as date, tr.type_id as type 
						FROM content c, pages p, tags t, tag_relationships tr 
						WHERE c.parent = p.guid 
						AND p.guid=tr.guid 
						AND tr.tag_id=t.id 
						$query_accuracy 
						AND p.guid != '$pageGUID' 
						AND p.offline=0
						GROUP BY p.guid
						) 
						UNION
						(
						SELECT f.guid, CONCAT(f.title,' (',f.extension, ' file)') as title, 
						CONCAT(f.name,'.',f.extension) as name, f.date_created as date,
						tr.type_id as type 
						FROM files f, pages p, tags t, tag_relationships tr 
						WHERE f.guid=tr.guid 
						AND tr.tag_id=t.id 
						$query_accuracy 
						GROUP BY f.guid
						
						
						)
						
						ORDER by date DESC
						";
					
					//print "<!--QUERY: $query<br>-->\n";			
					$results = $db->get_results($query);
					if($results){
						$related=0;
						foreach ($results as $result) {
							$tag_accuracy = $this->getTagAccuracy($tags,$result->guid, $result->type);
							//print "got a result accuracy($tag_accuracy >= $accuracy)..<br>";
							if(($tag_accuracy >= $accuracy) && $related<=$maxlinks) { //only shows links to pages whose accuracy meets minimum
								//print "Show a link type(".$result->type.")<br>\n";
								if($result->type == 1){ // page
									$page = new Page();
									$page->loadByGUID($result->guid);
									$content .= "<p><a href=\"".$page->drawLinkByGUID($result->guid)."\">".$result->title."</a></p>\n\t";
									$related++;
								}
								else if($result->type == 3){ // files
									$content .= '<p><a href="'.'/silo/files/'. $result->name.'" target="_blank" title="download this file">'.$result->title."</a></p>\n\t";
									$related++;
								}
							}
						}
					}
					if ($content) $content = '<h3>'.$title.'</h3>'.$content;
					if ($currentTag == 'science policy africa') $content = $content. '<p><a href="'.$page->drawLinkByGUID('5630ea00ceb36').'">Latest publications</a></p>';
				}

			}


			return $content;
		}
		
	
		public function convertTagsToArray($tags){
			// convert tags into an array
			$tags = explode(", ",$tags); // split tags into array at seperator (which is a comma and a space)
			return $tags;
		}
		


		public function drawContentByTag($tag,$type=0){
			//draw a list of items with a set tag
			global $db, $site;
			
			$tag_id = $this->getTagID($tag);
			
			switch($type){
				case 1:
					//content
					$query = "SELECT p.guid, p.title, 'page' AS type, p.name AS link
						FROM pages p LEFT JOIN tag_relationships tr on p.guid=tr.guid
						LEFT JOIN tags t on t.id=tr.tag_id
						WHERE t.id = $tag_id 
						AND p.msv=".$this->msv." AND p.offline=0
						ORDER BY p.sort_order, p.title";
				break;
				//images - not sure if Treeline uses these or not
				case 2:
					$query = "SELECT i.guid, i.title, 'image' as type, '' AS link
					FROM get_formatted_image_list i, tag_relationships tr, tags t 
					WHERE i.guid=tr.guid AND tr.tag_id=t.id AND t.id = $tag_id AND i.parent = 0 AND i.site_id=". $this->msv;
				break;
				//files - not sure if Treeline uses these either.
				case 3:
					$query = "SELECT f.guid, f.title, 'file' as type, '' as link
					FROM files f, tag_relationships tr, tags t 
					WHERE f.guid=tr.guid AND tr.tag_id=t.id AND t.id = $tag_id AND f.site_id=". $this->msv;
				break;
				// files and content
				default:
					$query = "SELECT p.guid, p.title, 'page' as type, p.name AS link
						FROM pages p LEFT JOIN tag_relationships tr on p.guid=tr.guid
						LEFT JOIN tags t on t.id=tr.tag_id
						WHERE t.id = $tag_id 
						AND p.msv=".$this->msv." AND p.offline=0
						UNION
						SELECT f.guid, f.title, 'file' AS type, 
						CONCAT(f.name,'.',f.extension) As link
						FROM files f
						LEFT JOIN tag_relationships tr ON f.guid = tr.guid
						LEFT JOIN tags t ON t.id=tr.tag_id
						WHERE f.site_id = ".$this->msv." 
						AND t.id = $tag_id
						ORDER BY title";
				break;
			}
			
			if($tag_id){
				//print "< -- list tags - $query -- > \n";
				$results = $db->get_results($query);
				$rex = $db->num_rows;
				
				if($results){
					
					$count = 0;
					$content = $html = '';
					
					foreach ($results as $result) {

						$items[$result->type]['count']++;
						if ($result->type=='file') {
							$items[$result->type]['html'] .= '<li class="arrow"><a href="/silo/files/'.$result->link.'" target="_blank">'.$result->title.'</a>';
						}
						else {
							$page = new Page();
							$page->loadByGUID($result->guid);
							$items[$result->type]['html'] .= '<li class="arrow"><a href="'.$page->drawLinkByGUID($result->guid).'">'.$result->title.'</a></li>';
						}
						$total++;

					}

					foreach ($items as $type=>$array) {
						$count = 0; $html = '';
						//print "$type = $array <br>\n";
						if (is_array($array)) {
							foreach ($array as $k=>$v) {
								//print "$k = $v <br>\n";
								if ($k=="count") $count=$v;
								if ($k=="html") $html=$v;
							}
						}
						if ($count && $html) {
							$content .= '<p>Here\'s the '.$count.' '.$type.($count>1?"s":"").' with the tag <strong>'.$tag.'</strong></p>';
							$content .= '<ul id="tags-list">'.$html.'</ul>';
						}
					}
					$content .= '<p><a href="'.$site->link.'tags/">view all tags</a></p>';	
				}
				else $content = "<p>There were no items for that tag</p>";
			}
			else $content = "<p>There were no items for that tag</p>";
			return $content;
			
		}

		// Draw tags
		// Produces a formatted list of tags for a given pages.
		// Formats : list = tag, tag, tag
		//			 linklist = tag, tag, tag where each tag will click through to the page and is wrapped in an li element and in a <ul>.
		// 			 paragraph = tag, tag, tag where each tag will click through to the tags page and is wrapped in a <div><p>
		// 			 csvbyid = id,id,id		
		public function drawTags($guid, $format = 'list'){
			global $db, $site;
			//print "dT($guid, $format) tt(".$this->tag_type.")<br>";
			
			//variables
			$tags = ''; // set up varaiables as empty to begin with (this will be returned)
			$grouping = '';
			//print "switch on (".$this->tag_type.")<br>\n";
			switch($this->tag_type){
					//images
					case 2: 
						$content_type = 'images';
						$query = "SELECT t.id, t.tag 
							FROM tags t, tag_relationships tr, get_formatted_image_list i 
							WHERE t.id=tr.tag_id 
							AND tr.guid=i.parent 
							AND i.parent = '$guid' 
							AND i.original_size = 1 
							AND i.site_id=". $this->msv ." GROUP BY t.tag";
					break;
					
					//files
					case 3:
						$content_type = 'files';
						$query = "SELECT t.id, t.tag FROM tags t, tag_relationships tr, files f  
						WHERE t.id=tr.tag_id AND tr.guid=f.guid AND f.guid = '$guid' AND f.site_id=". $this->msv ." GROUP BY t.tag";
					break;
					
					//galleries
					case 5:
						// Have to be careful with galleries as microsites can
						// share gallery guids.
						$content_type = 'galleries';
						$query = "SELECT t.id, t.tag FROM tags t
							left join tag_relationships tr on t.id=tr.tag_id
							left join galleries g on tr.guid=g.guid
							WHERE g.guid = '$guid' 
							AND tr.guid='$guid'
							AND g.msv=".$this->msv." 
							AND t.msv=".$this->msv."
							GROUP BY t.tag
							";
					break;
					
					case 6: // Media library tags,
						$content_type = 'files';
						$query = "SELECT t.id, t.tag 
							FROM tags t
							INNER JOIN tag_relationships tr ON t.id=tr.tag_id
							INNER JOIN media m ON m.guid = tr.guid
							WHERE m.guid = '$guid' 
							AND m.msv=".$this->msv." GROUP BY t.tag";
						break;
					
					case 1: //content
					case 4: // Panels.
					default:
						$content_type = 'pages';
						$query = "SELECT t.id, t.tag FROM tags t, tag_relationships tr, pages p  
						WHERE t.id=tr.tag_id AND tr.guid=p.guid AND p.msv=". $this->msv ." 
						AND p.offline=0 AND p.guid='". $guid ."' GROUP BY t.tag";
					break;
				}
			//echo $query .'<br />';
			$results = $db->get_results($query);
			//echo '<pre>'. print_r($results,true) .'</pre>';
			if($results){
				$count=0;
				foreach($results as $result){
					$tag_separator=", ";
					if($format == 'list'){ //used when editing content
						$tags .= $result->tag.", ";
					}
					else if($format == 'linklist') {
						$tag_separator=" | ";
						$tags .= '<li><a href="'.$site->link.'tags/'.urlencode($result->tag).'/">'.$result->tag."</a></li>".$tag_separator;
					}
					else if ($format=="bloglist") { 
						$tag_separator = '';
						$tags .= '<li><a href="'.$site->link.'tags/'.urlencode($result->tag).'/">'.$result->tag."</a></li>".$tag_separator;
					}
					else if($format == 'paragraph'){ //used when viewing a page
						$tags .= '<a href="'.$site->link.'tags/'.urlencode($result->tag).'/">'.$result->tag."</a>".$tag_separator;
					}
					else if ($format=="csvbyid") {
						$tags .= $result->id.",";
					}
				}
				
				if($format == 'list'){
					$tags = substr($tags,0, strlen($tags) -2); // remove trailing comma and space
				}
				else if ($format == "pagelist") {
					$tags = '<p class="tagslist">Tags on this page: '.substr($tags,0, strlen($tags) -2).'</p>'; // remove trailing comma and space
				}
				else if ($format == "bloglist") {
					$tags = '
					<div class="info-tags">
						<ul>
							<li><strong>Tags</strong></li>
							'.substr($tags,0,-3).'
						</ul>
					</div>
					';
				}
				else if($format == 'linklist'){
					$tags = '
					<div class="tags">
						<ul>
							<li><i class="ion-ios-pricetag-outline"></i></li>
							'.substr($tags,0,-3).'
							<li><a href="'.$site->link.'tags/">all tags</a></li>
						</ul>
					</div>
					'; // add wrapping list tags
				}
				else if ($format == "paragraph") {
					$tags = '<div id="tagslist"><p class="tagslist">'.substr($tags,0,-2).'</p></div>'; // add wrapping list tags
				}
			}
			
			// Must at least return the , for a csv listing even if there are no tags.
			if ($format == "csvbyid") $tags=",".$tags;
			
			return $tags;
		}
		
		// ------------------------------------------------------------------------------
		// Function to product an actionable list of tag names based on a list of tag ids.
		public function drawAdminTags($list, $action=''){
			global $db;
			//print "dAT($list, $action)<br>\n";
			
			// Remove leading and trailing , if there is one.
			if (substr($list, -1, 1)==",") $list=substr($list, 0, -1);
			if (substr($list, 0, 1)==",") $list=substr($list,1);
			// Its possible there are no tags or stipping the comma killed the list so back out if no data.
			if (!$list) return;
			$query = "SELECT t.id, t.tag 
				FROM tags t 
				WHERE id IN ($list) 
				GROUP BY t.tag 
				ORDER BY t.tag";
			//print "$query<br>\n";
			$results = $db->get_results($query);
			if($results){
				foreach($results as $result) {
					$tags.= $result->tag;
					if ($action=="remove") $tags.=' <input type="submit" class="empty" name="tagaction" value="remove-'.$result->id.'" />';
					$tags.=", ";
				}
				$tags = substr($tags,0, strlen($tags) -2); // remove trailing comma and space
			}
			return $tags;
		}
		// ------------------------------------------------------------------------------

		
		public function drawTagCloud(){
			// draw unordered list of all tags, with different CSS classes dependign on popularity
			global $db, $site;
			
			//$query = "SELECT t.tag, COUNT(tr.tag_id) AS count FROM tags t, tag_relationships tr 
			//			WHERE t.id=tr.tag_id AND tr.type_id = 1 GROUP BY t.tag ORDER BY t.tag";
			
			// NOTE
			// The get formatted image list view can run very slowly when there are many images.
			$query = "SELECT t.tag, COUNT(tr.tag_id) AS count,
				IF(p.title>'', p.title, IF(i.title, i.title, IF(f.title, f.title, NULL) )) as title
				FROM tags t
				LEFT JOIN tag_relationships tr ON t.id=tr.tag_id
				LEFT OUTER JOIN pages p ON p.guid=tr.guid
				LEFT OUTER JOIN files f ON tr.guid=f.guid
				LEFT OUTER JOIN get_formatted_image_list i ON tr.guid=i.guid
				WHERE tr.type_id = 1 AND 
				t.msv=".$this->msv." AND
				(
					(p.msv=".$this->msv." AND p.offline=0)
					OR f.site_id=".$this->msv." 
					OR i.site_id=".$this->msv."
				)
				";
			if ($this->parent) $query .= "AND p.parent='".$this->parent."' ";
			$query .= "GROUP BY t.tag ORDER BY t.tag";
				
			//print "$query<br>";
			$results = $db->get_results($query);
			
			if($results){
					$content = '<ul class="tag-cloud" id="tagcloud">'."\n";
					foreach ($results as $result) {
						$linktitle = $result->count.' item'; // wording for link title (tel user how mnay items share each tag)
						if($result->count > 1){
							$linktitle .= 's'; // pluralise
						}

						if($result->count > 15) {
							$classImpLevel = 16;
						} else {
							$classImpLevel = $result->count;
						}
						$content .= '<li class="importance-level-'.$classImpLevel.'"><a href="'.$site->link.'tags/'.urlencode($result->tag).'/" rel="tag" title="'.$linktitle.'">'.$result->tag."</a></li>\n\t";
					}
					$content .= "</ul>\n\t";	
					return $content;
				}
				else{
					$error = "There were no items for that tag";
					return $error;
				}
			
		}


		public function drawPopularTags(){
			// draw unordered list of all tags, with different CSS classes dependign on popularity
			global $db;
			
			//$query = "SELECT t.tag, COUNT(tr.tag_id) AS count FROM tags t, tag_relationships tr 
			//			WHERE t.id=tr.tag_id AND tr.type_id = 1 GROUP BY t.tag ORDER BY t.tag";
			$query = "SELECT t.tag, COUNT(tr.tag_id) AS count,
						IF(p.title>'', p.title, IF(i.title, i.title, IF(f.title, f.title, NULL) )) as title
						FROM tags t
						LEFT JOIN tag_relationships tr ON t.id=tr.tag_id
						LEFT OUTER JOIN pages p ON p.guid=tr.guid
						LEFT OUTER JOIN files f ON tr.guid=f.guid
						LEFT OUTER JOIN get_formatted_image_list i ON tr.guid=i.guid
						WHERE tr.type_id = 1 AND 
						(
							(p.site_id=".$this->msv." AND p.offline=0)
							OR f.site_id=".$this->msv." 
							OR i.site_id=".$this->msv."
						)
						GROUP BY t.tag ORDER BY count(tr.tag_id) desc, tag";
			$results = $db->get_results($query);
			
			if($results){
					$content = "<p id=\"tagcloud\">\n\t";
					foreach ($results as $result) {
						$linktitle = $result->count.' item'; // wording for link title (tel user how mnay items share each tag)
						if($result->count > 1){
							$linktitle .= 's'; // pluralise
						}
						$content .= '<a href="/tags/?tag='.$result->tag.'" rel="tag" title="'.$linktitle.'">'.$result->tag."</a>, \n\t";
					}
					$content .= "</p>\n\t";	
					return $content;
				}
				else{
					$error = "There were no items for that tag";
					return $error;
				}
			
		}

		
		public function drawToolbar($guid,$accuracy,$maxlinks){
			// draw form items to add/edit intelligent link panels
			
			$accuracy_options = NULL;
			$maxlinks_options = NULL;
			
			for($i = 100;$i>=0; $i-=5){
				if($i != 0){
				$accuracy_options[] = $i;
				}
			}
			
			for($i = 1;$i<=10; $i++){
				$maxlinks_options[] = $i;
			}
			
			$toolbar = "\n".'<fieldset id="related_content_links">'."\n".'<legend>Edit Related content links</legend>'."\n";
			
			// show or hide the intelligent link panels
			if($this->showRelatedContent($guid)){
				$checked = ' checked="checked"';
			}
			$toolbar .= '<input type="checkbox" id="show_related_content" name="show_related_content" value="1" class="checkbox"'.$checked.' /><label for="show_related_content">Show related items?</label><br />'."\n";
			
			$toolbar .= '<div id="related_options">'."\n";
			// Accuracy (%) dropdown menu
			$toolbar .= '<label for="accuracy" class="hide">Accuracy:</label>'."\n".'<select name="accuracy" id="accuracy">'."\n".'
				<option value="xx">% accuracy</option>'."\n";
				foreach($accuracy_options as $accuracy_option){
					$selected = NULL;
					if($accuracy_option == $accuracy){
						$selected = ' selected="selected"';
					}
					$toolbar .= '<option value="'.$accuracy_option.'"'.$selected.'>'.$accuracy_option.'%</option>'."\n";
				}
			$toolbar .='</select>'."\n";
			
			// maximum links drop down menu
			$toolbar .= '<label for="maxlinks" class="hide">Maximum number of links:</label>'."\n".'<select name="maxlinks" id="maxlinks">'."\n".'
				<option value="xx">Total links (max.)</option>'."\n";
				foreach($maxlinks_options as $maxlinks_option){
					$selected = NULL;
					if($maxlinks_option == $maxlinks){
						$selected = ' selected="selected"';
					}
					$toolbar .= '<option value="'.$maxlinks_option.'"'.$selected.'>'.$maxlinks_option.'</option>'."\n";
				}
			$toolbar .='</select>'."\n";
			$toolbar .='</div>'."\n";
			
			$toolbar .='</fieldset>'."\n";
			return $toolbar;
		}


public function drawEditControls($guid,$accuracy,$maxlinks, $title='') {
	global $page;
	$title_html = $delete_html = '';
	
	// Do we need an option to delete the panel?
	if ($accuracy || $maxlinks) {
		$delete_html='
		<label for="f-delete-related">'.$page->drawLabel("tl_paedit_intel_delete", "Delete this panel").'</label>
		<input type="checkbox" id="f-delete-related" class="related-checkbox" name="delete-related" value="1" />
		';
		$delete_html = '';	// Don't allow panel deletion this way for now.
	}
	
	// Or an option to add a title for the panel?
	//if (!$title) $title=$page->drawLabel("tl_paedit_intel_related", "Related links");
	$title_html='
	<label for="f-title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).'</label>
	<input type="text" id="f-title" class="text" name="title" value="'.$title.'" />
	';
			
	
	// Show the accuracy select box
	$tmp='';
	for($i = 100;$i>=10; $i-=10){
		$selected = ($i == $accuracy?' selected="selected"':'');
		$tmp .= '<option value="'.$i.'"'.$selected.'>'.$i.'%</option>'."\n";
	}
	$toolbar.='
	<label for="f_accuracy">'.$page->drawLabel("tl_paedit_intel_accur", "Accuracy").':</label>
	<select name="accuracy" id="accuracy">
		'.$tmp.'
	</select>
	';
	
	// Show the max links select box
	$tmp='';
	for($i = 1;$i<=10; $i++){
		$selected = ($i == $maxlinks?' selected="selected"':'');
		$tmp .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>'."\n";
	}
	$toolbar.='
	<label for="f_maxlinks">'.$page->drawLabel("tl_paedit_intel_numlinks", "Show # links").':</label>
	<select name="maxlinks" id="f_maxlinks">
		'.$tmp.'
	</select>
	';

	// Set up the full toolbar
	$toolbar = $title_html.$toolbar.$delete_html;
	
	// If we are not creating we need another fieldset and a legend
	// Bit crappy but works for now.
	if ($delete_html) $toolbar = '
	<fieldset id="related_content_links">
	<legend>'.$title.'</legend>
		'.$toolbar.'
	</fieldset>
	';

	return $toolbar;
}

		
	public function getRelatedContentDetails($guid){
		//print "gRCD($guid)<Br>\n";
		global $db;
		$query = "SELECT * FROM tags_intelligent_link_panels WHERE guid = '$guid' LIMIT 1";			
		//print "$query<Br>\n";
		return $db->get_row($query);
	}
		
		public function getTagAccuracy($tags, $guid,$type){
			// get tags of each page & put in an array
			$page_tags = $this->drawTags($guid, 'list', $type);
			$page_tags = $this->convertTagsToArray($page_tags);
			$non_tag_matches = array_diff($tags, $page_tags);
			$tag_inaccuracy = round((sizeof($non_tag_matches)/sizeof($tags))*100); // work out % of missing tags
			$tag_accuracy = 100-$tag_inaccuracy; // work out % of matching tags
			
			return $tag_accuracy;
		}
		
		// get the ID of the tag from the tag table
		public function getTagID($tag, $msv=1){
			global $db;
			$query = "SELECT id FROM tags WHERE tag='".$db->escape($tag)."' AND msv=".$this->msv." LIMIT 1;";
			return ($db->get_var($query));
		}

		

		public function showRelatedContent($guid){
			return $this->getRelatedContentDetails($guid);		
		}
		
		public function suggestTags($guid, $type){
			// suggest relevant tags based on content
			global $db;
			
			$conditions = '';
			
			$query = "SELECT tag FROM tags  $conditions LIMIT 5";
			$results = $db->get_results($query);
			
			if($results){
					$content = "<ul id=\"suggesttags\">\n\t";
					foreach ($results as $result) {
						$content .= '<li><a href="?tag='.urlencode($result->tag).'" rel="tag">'.$result->tag."</a></li>\n\t";
					}
					$content .= "</ul>\n\t";	
					return $content;
				}
				else{
					return false;
				}
			
		}
		
		public function updateIntelligentLinkPanelDetails($guid, $accuracy=25, $maxlinks=5, $show_related_content=1, $title = ''){
			//when a page is updated, update the database with intelligent  link panel values
			global $db;
			//print "uILPD($guid, $accuracy, $maxlinks, $show_related_content, $title)<br>\n";
			
			if($show_related_content && $accuracy>0 && $maxlinks>0 ){
				if($this->showRelatedContent($guid)){
					$query = "UPDATE tags_intelligent_link_panels 
						SET maxlinks = $maxlinks, accuracy = $accuracy ";
					if ($title) $query.=", title='$title' ";
					$query.="WHERE guid = '$guid' LIMIT 1";
				}
				else{
					if (!$title) $title="Related links";
					$query = "INSERT INTO tags_intelligent_link_panels 
						VALUES('$guid', $maxlinks, $accuracy, '$title');";
				}
				//print "$query<br>\n";
				// Note - this returns false if nothing was changed!!
				return $db->query($query);
			}
			// Dont show related panel
			else {
				$query = "DELETE FROM tags_intelligent_link_panels WHERE guid = '$guid' LIMIT 1";
				return $db->query($query);
			}		
		}
		
		
		
		
		//display tags for all resources
		public function drawResourceTags($current=false){
			global $db;
			$tags = ''; // set up varaiables as empty to begin with (this will be returned)
			$query = "SELECT t.tag FROM tags t
						LEFT JOIN tag_relationships tr ON t.id=tr.tag_id
						LEFT JOIN files f ON tr.guid=f.guid
						WHERE f.site_id=".$this->msv." AND f.resource=1 
						GROUP BY t.tag
						ORDER BY t.tag ASC";
			//print "$query<br>";
			$results = $db->get_results($query);
			if($results){
				$tags .= "<option value=\"\">-- select a tag --</option>\n\t";
				foreach($results as $result){
					$selected = ($current==$result->tag) ? ' selected="selected"' : '';
					$tags .= '<option value="'. $result->tag .'"'. $selected .'>'. ucwords($result->tag) ."</option>\n\t";
				}
			}
			else $tags='<option value="0">No tags listed</option>';
			
			$tags = "<select name=\"tag\">\n\t$tags</select>\n\t"; // add wrapping list tags
			return $tags;
		}
		


		public function uploadFromCSV($file) {
			global $page;
			unset($this->error);
			$error=array();
			$this->error=print_r($file, true);
			if ($fp=fopen($file['tmp_name'], "r")) {
				while ($data=fread($fp, 10)) {	
					//print "got data($data)<br>";
					$data=str_replace("\n", ",", $leftover.$data);
					$data=str_replace("\r", "", $data);
					//print "explode($data)<br>";
					$newtags=explode(",", $data);
					//print "got newtags(".print_r($newtags, true).")<br>";
					if (count($newtags)>1) {
						for($i=0; $i<(count($newtags)-1); $i++) {
							if (strlen(trim($newtags[$i]))) {
								$tag_count++;
								if (!$this->addTag($newtags[$i])) {
									$err_count++;
									array_push($error, $this->error[0]);
								}
							}
						}
					}
					$leftover=$newtags[(count($newtags)-1)];
					//print "residual data($leftover)<br>";
					unset($newtags);
				}
				fclose($fp);

				//print "still got ($leftover) data($data)<br>";
				if (strlen(trim($leftover))) {
					$tag_count++;
					if (!$this->addTag($leftover)) {
						$err_count++;
						array_push($error, $this->error[0]);
					}
				}
				
				if ($error) $this->error=$error;
				$this->error[]="Added ".($tag_count-$err_count)." of $tag_count tags";
				return true;
			}
			else $this->error=$page->drawLabel("tl_tag_err_loadfile", "Unable to open uploaded file");
			return 0;
		}
		
		
		
	}
?>