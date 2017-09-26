<?php
	class File {
	
		public $guid;
		public $title;
		public $name;
		public $size;
		public $type;
		public $extension;
		public $description;
		public $shared;
		public $site_id;	
		public $content;
		public $resource;
		
		public $img_guid;
		public $img_src;

		public $category;	
		public $catid;
		public $subcategory;
		public $subcatid;
		
		
		public $totalresults;
		public $perpage;
		public $page;
		public $totalpages;
		public $from;
		public $to;	
		
		// Only used by migration process
		public $date_created;
		public $user_created;
		
		public function __construct() {
			// This is loaded when the class is created	

		}
		


	public function setGUID($guid) {
		$this->guid = $guid;
	}
	public function getGUID() {
		return $this->guid;
	}
		
		public function setTitle($title) {
			$this->title = $title;
		}
		
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	
	public function setSize($size) {
		if ($size > 80000000) {
			return false;
		}
		else {
			$this->size = $size;
			return true;
		}
	}
	
	public function setContent( $content ){
		$this->content = $content;
	}
		
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

	public function setPage($page){
		$this->page = $page;
	}
	public function getPage(){
		return $this->page;
	}

	public function setTotalPages($total){
		$this->totalpages = ceil($this->getTotal()/$this->getPerPage());
	}
	public function getTotalPages(){
		return $this->totalpages;
	}

/****
      .jpe  -  image/jpeg
     .jpeg  -  image/jpeg
      .jpg  -  image/jpeg
	  
      .mid  -  audio/midi
     .midi  -  audio/midi

      .mp2  -  audio/mpeg
      .mp3  -  audio/mpeg
      .mpe  -  video/mpeg
     .mpeg  -  video/mpeg
      .mpg  -  video/mpeg
     .mpga  -  audio/mpeg


      .pot  -  application/mspowerpoint
      .pps  -  application/mspowerpoint
      .ppt  -  application/mspowerpoint
      .ppz  -  application/mspowerpoint
       .ps  -  application/postscript ( as well as eps)

***/
	
		public function setType($type, $tmp_extension='') {
			// Exact mime-types to be agreed

			//print "setType($type, $tmp_extension)<br>";
			$extension = '';
			
			/* There is an issue over mime types having many different extensions:
			      .pot  -  application/mspowerpoint
				  .pps  -  application/mspowerpoint
				  .ppt  -  application/mspowerpoint
				  .ppz  -  application/mspowerpoint
				What may be the best way to address this?
			*/
			
			switch($type){


				// Wierd virus type files trying to be uploaded? ----
				case 'application/octet-stream':
					if ($tmp_extension=="wmv") $extension="wmv";
					if ($tmp_extension=="doc") $extension="doc";
					break;

				case 'application/x-download':
				case 'application/x-octet-stream':
				case 'application/binary':
					if ($tmp_extension=="pdf") $extension="pdf";
					break;


				////////////// Documents / Text Based ///////////////
				//// PDF
				case 'application/pdf':
				case 'application/x-pdf':
				case "application/acrobat":
					$extension = 'pdf';
					break;
					
					
				//// Word docs
				case 'application/msword':
				case 'application/vnd.ms-word':
					$extension = 'doc';
					break;
				//// Excel Files
				case 'application/excel':
				case 'application/vnd.ms-excel':
				case 'application/x-excel':
				case 'application/x-msexcel':
					$extension = 'xls';
					break;
				////
				case 'application/octet-stream':
					$tmp = strtolower($tmp_extension);
					if ($tmp=="dmg" || 
						$tmp=="flv" ||
						$tmp=="msi") $extension=$tmp_extension;
					break;

				case 'application/vnd.ms-powerpoint':
				case 'application/mspowerpoint':
				case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
					$extension = 'ppt';
					break;
				//// Style sheets - not sure why we need this in upload, unless it's just for sharing?
				case 'text/css':
					$extension = 'css';
					break;
				//// Postscript EPS
				case 'application/postscript':
					$extension = 'eps';
					break;
				case 'application/x-shockwave-flash2-preview':
					$extension = "swf";
					break;
				//// RFT
				case 'text/rtf':
					$extension = 'rtf';
					break;
				//// Rich Text
				case 'text/richtext':
					$extension = 'rtx';
					break;
				//// Tab Separated Values
				case 'text/tab-separated-values':
					$extension = 'tsv';
					break;
				//// Comma separated values
				case 'text/comma-separated-values':
					$extension = 'csv';
					break;
				//// Plain Text
				case 'text/plain':
					$extension = 'txt';
					break;
				//// XML
				case 'text/xml':
					$extension = 'xml';
					break;
					
				//////////////// Image files //////////////
				//// GIF Images
				case 'image/gif':
					$extension = 'gif';
					break;
				//// JPEGs
				case 'image/jpeg':
				case 'image/pjpeg':
					$extension = 'jpg';
					break;
				case 'image/cmu-raster':
					$extension = 'ras';
					break;
				case 'image/x-rgb':
					$extension = 'rgb';
					break;
				//// Bitmap
				case 'image/x-portable-bitmap':
					$extension = 'pbm';
					break;
				case 'image/x-portable-graymap':
					$extension = 'pgm';
					break;
				case 'image/x-portable-anymap':
					$extension = 'pnm';
					break;
					
				///////////////// Video //////////////////
				//// AVI Video
				case 'video/x-msvideo':
					$extension = 'avi';
					break;
				//// MPEG Video
				case 'video/mpeg':
				case 'video/mp4':
					if ($tmp_extension=="mp4") $extension="mp4";
					else $extension = 'mpg';
					break;
				//// Quicktime Video
				case 'video/quicktime':
					$extension = 'mov';
					break;
				//// Movie files
				case 'video/x-sgi-movie':
					$extension = 'movie';
					break;
				//// Flash Video
				case 'video/x-flv':
					$extension = 'flv';
					break;
				case 'video/x-ms-wmv': 
					$extension = "wmv";
					break;
				
				
				/////////////////// Audio ///////////////
				//// Real Media (this does not include video or audio specific files!)
				case 'application/vnd.rn-realmedia':
				case 'audio/x-pn-realaudio':
					$extension = 'rm';
					break;
				//// Real Audio
				case 'audio/x-realaudio':
					$extension = 'ra';
					break;
				//// Real Audio file -> used as a pointer to .ra files
				case 'audio/x-pn-realaudio':
					$extension = 'ram';
					break;
				//// MP3
				case 'audio/mpeg':
				case 'audio/x-mpeg':
				case 'audio/mp3':
				case 'audio/x-mp3':
				case 'audio/mpeg3':
				case 'audio/mpg':
					$extension = 'mp3';
					break;
				//// WAVE files
				case 'audio/x-wav':
					$extension = 'wav';
					break;
				//// Basic Audio
				case 'audio/basic':
					$extension = 'au';
					break;
				
				////////////////// Other media //////////////
				case 'application/x-shockwave-flash':
					$extension = 'swf';
					break;
				
				////////////////// Other ////////////////////
				//// ZIP Files
				case 'application/zip':
				case 'application/x-zip-compressed':
					$extension = 'zip';
					break;
			}
			
			if ($extension) {
				$this->extension = $extension;
				$this->type = $type;				
				return true;
			}
			else {
				return false;
			}			

		}
		
		public function setExtension($extension) {
			$this->extension = $extension;
		}
		
		public function setDescription($description){
			$this->description = $description;
		}

		public function setShared($shared) {
			if ($shared) $this->shared=1;
			else $this->shared=0;
		}		
		
		public function setResource($resource){
			$this->resource = $resource;
		}
		

		// 12th Jan 2009 - Phil Redclift
		// Set category($cat) 
		// Sets category and add it to filecategories if its not there already
		public function setCategory($category) {
			global $db, $site;
			$this->catid=0;
			if (!$category) return false;
			$this->category=$db->escape(htmlentities($category,ENT_QUOTES,$site->properties['encoding']));
			$query="SELECT id 
				FROM filecategories 
				WHERE title='".$this->category."' AND  site_id=".$site->id;
			//print "$query<br>";
			$catid=$db->get_var($query);
			if (!$catid) {
				$query="insert into filecategories (title, parent, site_id)
					values ('".$this->category."', 0, ".$site->id.")";
				//print "$query<br>";
				if ($db->query($query)) $catid=$db->insert_id;
				else return false;
			}
			$this->catid=$catid;
			return $catid;
		}
		public function getCategory() {
			return $this->category;
		}

		// 12th Jan 2009 - Phil Redclift
		// Set subcategory($catid, $subcat) 
		// Sets subcategory and add it to filecategories if its not there already under $catid 
		public function setSubcategory($subcategory) {
			global $db, $site;
			$this->subcatid=0;
			if (!$subcategory) return false;
			if (!$this->catid) return false;
			$this->subcategory = $db->escape(htmlentities($subcategory,ENT_QUOTES,$site->properties['encoding']));
			$query="SELECT id 
				FROM filecategories 
				WHERE title='".$this->subcategory."' and parent=".$this->catid;
			$subcatid=$db->get_var($query);
			//print "scid($subcatid) $query<br>";
			if (!$subcatid) {
				$query="insert into filecategories (title, parent, site_id)
					values ('".$this->subcategory."', ".$this->catid.", ".$site->id.")";
				//print "$query<br>";
				if ($db->query($query)) $subcatid=$db->insert_id;
				else return false;
			}
			$this->subcatid=$subcatid;
			return $subcatid;
		}
		public function getSubcategory() {
			return $this->subcategory;
		}
		
		

		public function create() {
			global $db, $user, $site;

			$this->guid = uniqid();
			$title = $db->escape(htmlentities($this->title,ENT_QUOTES,$site->properties['encoding']));	
			$description = $db->escape(htmlentities($this->description,ENT_QUOTES,$site->properties['encoding']));			
			$name = $db->escape($this->name);
			$site_id=$this->site_id?$this->site_id:$site->id;
			$date_created=$this->date_created?"'".$this->date_created."'":"NOW()";
			$user_created=$this->user_created?$this->user_created:$user->getID();
						
			$query = "INSERT INTO files 
				(guid, title, description, shared, name, size, type, 
				extension, category, subcategory, date_created, user_created, 
				site_id, resource, img_guid)
				VALUES 
				('$this->guid', '$title', '$description', ".($this->shared+0).", '$name', {$this->size}, '{$this->type}', 
				'{$this->extension}', {$this->catid}, {$this->subcatid}, $date_created, $user_created, 
				$site_id, {$this->resource}, '".$this->img_guid."')";
			//print "$query<br>";
			if( $db->query($query) ){
				if( isset($this->content) && $this->content>'' ){
					$query = "INSERT INTO files_content (guid, content) VALUES ('{$this->guid}','". $db->escape($this->content) ."')";
					$db->query($query);
				}
				return true;
			}else{
				//print "$query<br>";
				return false;
			}
			
		}
		
		public function save() {
			global $db, $user;
		
			$title = $db->escape(htmlentities($this->title,ENT_QUOTES,$site->properties['encoding']));			
			$description = $db->escape(htmlentities($this->description, ENT_QUOTES, $site->properties['encoding']));
			$name = $db->escape($this->name);
			//$category = $db->escape(htmlentities($this->category,ENT_QUOTES,$site->properties['encoding']));
			$query = "UPDATE files
				SET title='$title', size='{$this->size}', type='{$this->type}', 
				description = '$description', 
				shared = ".($this->shared+0).",
				category={$this->catid}, subcategory=".($this->subcatid+0).", 
				date_modified = NOW(), user_modified = {$user->getID()}, 
				resource={$this->resource}, img_guid='".$this->img_guid."'
				WHERE guid = '{$this->guid}'";
			//print "$query<br>";
			if($db->query($query)){
				return true;
			}else{
				return false;
			}
		}		


		// 16/12/2008 Phil Redclift
		// Delete a file from disk and the database.
		public function delete($guid) {
			global $db;
			
			// Load the file data
			$this->loadFileByGUID($guid);
			if(!$this->getGUID()){
				return false;
			}
			
			// If the file exists on disk then remove it.
			$filename =  $_SERVER['DOCUMENT_ROOT'] .'/silo/files/'.$this->name.'.'.$this->extension;
			//print "got filename($filename)<br>\n";
			if (file_exists($filename)) {
				if (!@unlink($filename)) {
					return false;
				}
			}
			
			// Remove the file from the database.
			// If we are still here either the file did not actually exist on disk
			// or we have successfully deleted it
			$query = "DELETE FROM files WHERE guid='{$this->guid}'";

			return $db->query($query);
		}

		// 16/12/2008 Phil Redclift
		// Function updated above.
		/*
		public function _delete($guid) {
			global $db;
			
			$this->loadFileByGUID($guid);
			if(!$this->getGUID()){
				return false;
			}
			/// physically remove the file first...
			/// then remove its entry from the database...
			$filename =  $_SERVER['DOCUMENT_ROOT'] .'/silo/files/'.$this->name.'.'.$this->extension;
			
			if(@unlink($filename)){
				$query = "DELETE FROM files WHERE guid='{$this->guid}'";
				if($db->query($query)){
					return true;
				}
			}else{
				return false;
			}
		}
		*/
		
		public function write($src) {
			// Move the uploaded file into /silo/files, with its own unique name
			if (move_uploaded_file($src, $_SERVER['DOCUMENT_ROOT'] . '/silo/files/'.$this->name.'.'.$this->extension)) {
				return false;
			}
			else {
				return 'File cannot be saved onto the server';
			}
		}
		
	
		
		
		public function getUploadError($code) {
		
			switch ($code) {
				case UPLOAD_ERR_OK:
					$return = false;
					break;
				case UPLOAD_ERR_INI_SIZE:
					$return = "The uploaded file exceeds the upload_max_filesize directive (".ini_get("upload_max_filesize").") in php.ini.";
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$return = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
					break;
				case UPLOAD_ERR_PARTIAL:
					$return = "The uploaded file was only partially uploaded.";
					break;
				case UPLOAD_ERR_NO_FILE:
					$return = "No file was uploaded.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$return = "Missing a temporary folder.";
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$return = "Failed to write file to disk.";
					break;
				default:
				 $return = "Unknown File Error.";
			}
			return $return;
									
		}
		
		public function generateName() {
			// Generates a "friendly" filename from $title
			// checking that there are no existing files with the same name/title
			global $db, $site;
			$title = $db->escape($this->title);			
			$query="SELECT * FROM files WHERE title = '$title' AND site_id=".$site->id;
			$db->query($query);
			if ($db->num_rows > 0) {
				return false;
			}
			else {
				// Strip everything but letters, numbers and spaces from the title
				$name = preg_replace("/[^A-Za-z0-9 ]/", "", $this->title);
				// Replace spaces with dashes
				$name = str_replace(" ",'-',$name);
				$this->name = strtolower($name);
				return true;
			}
		}		
		
		public function drawCategories($current=0) {
			global $db, $site;
			$query="SELECT fc.id, fc.title as category 
				FROM filecategories fc
				WHERE site_id=".$site->id."
				AND parent=0
				GROUP BY title 
				ORDER BY title";
			//print "$query<br>";
			if ($categories = $db->get_results($query)) {
				$html = '';
				foreach ($categories as $category) {
					$selected = ($category->id == $current)?'selected="selected"':'';
					$html .= '<option value="'.$category->id.'" '.$selected.'>'.$category->category.'</option>'."\n";
				}
			}
			return $html;
		}			



	// 13th Jan 2009 - Phil Redclift
	// Collect a list of subcategories and return as a data array.
	public function getFileSubcategories($msv=1){
		global $db;
		
		$query = "SELECT fc.id, fc.parent, fc.title,fc.site_id
					FROM filecategories fc
					INNER JOIN filecategories fc1 on fc.parent=fc1.id
					WHERE fc.site_id=$msv
					ORDER BY fc.parent, fc.title";
		//print "$query<br>";
		return $db->get_results($query);
	}

	// 13th Jan 2009 - Phil Redclift
	// Collect a list of subcategories and put them into a javascript array.
	// This array is used to dynamically populate the subcategory list without having to refresh the page.
	public function drawSubcategories() {
		global $db, $site;
		$categories = $this->getFileSubcategories($site->id);
		$html = '';
		if (is_array($categories)) {
			foreach ($categories as $category) {
				//$selected = ($category->id == $current)?' selected="selected"':'';
				//$html .= '<option value="'.$category->id.'"'.$selected.'>'.$category->title.'</option>';
				$html.="subcats.push(new Array(".$category->id.", ".$category->parent.",'".addslashes($category->title)."'));"."\n";
			}
		}
		return $html;
	}	

		
		public function loadFileByGUID($guid){
			////// fills the instance of this class with the parameters for the given file
			global $db;

			$query=	"SELECT * FROM files WHERE guid='$guid' LIMIT 1";
			//print "$query<br>\n";
			$fileinfo = $db->get_row($query);
			if ($fileinfo) {
				//print_r($fileinfo);
				$this->guid = $fileinfo->guid;
				$this->title = $fileinfo->title;
				$this->name = $fileinfo->name;
				$this->description = $fileinfo->description;
				$this->shared = $fileinfo->shared;
				$this->size = $fileinfo->size;
				$this->type = $fileinfo->type;
				$this->site_id = $fileinfo->site_id;
				$this->extension = $fileinfo->extension;						
				$this->category = $fileinfo->category;
				$this->subcategory = $fileinfo->subcategory;
				$this->resource = $fileinfo->resource;
				$this->img_guid= $fileinfo->img_guid;
				if ($this->img_guid) {
					$query="select concat('/silo/images/', filename) from images i
						left join images_sizes isz on i.guid=isz.guid
						where i.guid='".$this->img_guid."'
						order by isz.width asc
						limit 1";
					$img_src=$db->get_var($query);
					if (file_exists($_SERVER['DOCUMENT_ROOT'].$img_src)) {
						$this->img_src=$img_src;
					}
				}
			}
			else $this->guid='';
		}
		
		
		
		public function generateKeywords($file) {
			// This checks if the file is a Word document or PDF
			// then runs one of two shell scripts to extract text from them
			// It then reads in the response line by line
			// removes punctuation
			// identifies unique tokens
			// and returns them all in an array
			$keywords = array();
			$format = substr($file, strrpos($file,'.')+1);

			if ($format == 'doc' || $format == 'pdf') {
				if ($format == 'doc') {
					$command = 'strings '. $file;
				}
				else if ($format = 'pdf') {
					$command = 'pdftotext '. $file .' -'; 
				}

				exec($command, $lines);
				$stream = '';
				foreach ($lines as $line) {
					$stream .= $line;
				}
				$punctuation = array('.',',','-','@','\'','"','/',':',';','?','(',')','&','\\','[',']','>','<','=','+','{','}','#','|','!','_');
				$stream = str_replace($punctuation,' ',$stream);
				$sticks = preg_split('/\s+/', $stream);
				$uniquewords = array_unique($sticks);
				foreach ($uniquewords as $u) {
						
							if( $u=='MERGEFORMAT' && $format=='doc' ){
								break;
							}else{
								foreach($punctuation as $p){
									$u = str_replace($p,'',$u);
								}
								if( $u >' ' ){
									$keywords[] = strtolower( htmlentities( trim($u) ) );
								}
							}
						
				}
				return join(' ',$keywords);
			}else{
				return false;
			}
			
		}
		
		
	// 13th Jan 2009 - Phil Redclift
	// Select a list of files and return a data array.
	// $cat is an optional category variable, if its set to zero or has a space or nothing, it should show all files
	public function getFileList($cat=false, $keywords=''){
		global $db, $site;

		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		$query = "SELECT f.guid,f.name, f.title, date_format(f.date_created,'%D %M %Y') datemade,
			u.name username,u.full_name fullname,u.email
			FROM files f 
			LEFT JOIN users u ON f.user_created=u.id 
			WHERE f.site_id=".$site->id." ";
		if($cat) $query.=" AND f.category='$cat' ";
		if ($keywords) $query.="AND f.title LIKE '%$keywords%' ";
		//print "$query<br>\n";
		
		$db->query($query);
		$this->setTotal($db->num_rows);	
		$this->setTotalPages($db->num_rows);	
		$db->flush();
		$query.="ORDER BY f.date_created DESC,f.title ASC LIMIT ". $this->from .",". $this->getPerPage();
		//print "$query<br>\n";

		$files = $db->get_results($query);
		if(sizeof($files)>0) return $files;
		return false;
	}

	// draw a list of files with options to manage them
	public function drawFileList($p=1, $action=false, $cat=false, $keywords=''){
		global $help, $page;
		
		$this->setPerPage(10);
		$this->setPage($p);	
			
		if($results = $this->getFileList($cat, $keywords) ){
			foreach($results as $file){
				$html .= '<tr>
	<td><strong>'.$file->title.'</strong></td>
	<td><a href="mailto:'. $file->fullname.'&lt;'.$file->email .'&gt;">'. $file->username .'</a></td>
	<td nowrap>'.$page->languageDate($file->datemade).'</td>
	<td nowrap class="action">
		<a class="edit" '.$help->drawInfoPopup($page->drawLabel("tl_help_file_edit", "Edit this file")).' href="/treeline/files/?action=edit&amp;guid='.$file->guid.'">edit this file</a>
		<a class="delete" '.$help->drawInfoPopup($page->drawLabel("tl_help_file_delete", "Delete this file")).' href="/treeline/files/?action=delete&amp;guid='.$file->guid.'">delete this file</a>
	</td>
</tr>
';
			}
			$html = '<table class="tl_list">
<caption>'.$this->drawTotal() .'</caption>
<thead>
	<tr>
	<th scope="col">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).'</th>
	<th scope="col">'.ucfirst($page->drawLabel("tl_generic_author", "Author")).'</th>
	<th scope="col">'.$page->drawLabel("tl_img_list_created", "Created on").'</th>
	<th scope="col">'.$page->drawLabel("tl_file_list_manage", "Manage this file").'</th>
	</tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
			//$html .= $this->drawPagination("/treeline/files/?action=$action&amp;category=$cat", $this->getTotal(), 10, $page);
			$html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/files/?action=$action&amp;category=$cat&amp;q=".$keywords);
			return $html;
		}
		else return $page->drawLabel("tl_file_list_none", 'There are no files to display');
	}
	
	// 13th Jan 2009 - Phil Redclift
	// Show the number of files matched by the current search.
	public function drawTotal(){
		global $page;
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1) $msg = $page->drawLabel("tl_file_total_one", 'There is only 1 matching file in the library');
		else $msg = $page->drawLabel("tl_file_title_showing", 'Showing files').' '.($this->from+1).'-'.$to.' '.$page->drawLabel("tl_generic_of", "of").' '. $this->getTotal();
		return $msg;
	}

	public function _getPagination($page,$action,$cat){
		$totalpages = $this->getTotalPages();
		if($totalpages>1){
			$html = '<ul class="pagination">';
			
			if( $page > 1 ){
				$html .= '<li><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .'&amp;category='. $cat .'&amp;p='. ($page-1) .'">&laquo; previous</a></li>'."\n";
			}
			for($i=1;$i<=$totalpages;$i++){
				if($page == $i){
					$html .= '<li>'.$i.'</li>';
				}else{
					$html .= '<li><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .'&amp;category='. $cat .'&amp;p='. $i .'">'. $i .'</a></li>'."\n";
				}
			}
			if( ($totalpages > 1) && ($page < $totalpages)){
				$html .= '<li><a href="'. $_SERVER['PHP_SELF'] .'?action='. $action .'&amp;category='. $cat .'&amp;p='. ($page+1) .'">next </a></li>'."\n";
			}
			$html .= '</ul>';
			
			return $html;
		}else{
			return false;
		}
	}
	public function drawPagination($currentURL,$totalResults,$perPage=10,$currentPage=1) {
		// $page indicates which page we're on
		//global $search;
		//print "db($currentURL, $totalResults, $perPage, $currentPage)<br>";
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
			$html .= '<li class="bookend"><a href="'.$currentURL.'">&laquo; First</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		}
		else{
			$html .= '<li class="bookend inactive">&laquo; First</li>'."\n";
		}
		if ($currentPage > 1) {
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. ($currentPage-1).'">&lt; Previous</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		}
		else{
			$html .= '<li class="bookend inactive">&lt; Previous</li>'."\n";
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
		//echo $pagestart.' > '.$pageend.' - ['.$currentPage.'] of ['. $totalpages .']<br />';
		for ($i=$pagestart; $i<=$pageend; $i++) {
			//// We don't want to show all pages, just a few either side of the page we're on.
			//// If we keep the page we're on centrally (position 5) then when we get to position 6
			//// we'll need to cycle the whole lot down...
			$class = ($i==$pageend) ? 'bookend' : '';
			if ($i != $currentPage) {
				$html .= '<li class="page '. $class .'"><a href="'. $currentURL.'p='. $i.'">'.$i.'</a></li>'."\n"; 
			} else {
				$html .= '<li class="page selected '. $class .'"><strong>'.$i.'</strong></li>'."\n";
			}

		}
		if ($currentPage < $totalpages) {
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. ($currentPage+1).'">Next &gt;</a></li>'."\n";
		}else{
			$html .= '<li class="bookend inactive">Next &gt;</li>';
		}	
		if($currentPage < ($totalpages-1)){
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. $totalpages.'">Last &raquo;</a></li>'."\n";
		}else{
			$html .= '<li class="bookend inactive">Last &raquo;</li>'."\n";
		}
		
		$html .= '</ul>'."\n";
			
		return $html;
	}
	

//////////////////////////////


	
	}
?>
