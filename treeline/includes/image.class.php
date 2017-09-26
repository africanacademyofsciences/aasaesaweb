<?php

class Image {
	
	public $guid;
	public $master_guid;
	public $title; // This is the "Friendly" name of the image
	public $name; // This is the "URL" name of the image, based on the title
	public $filename; // This is the name of the image as it's stored on disk
	public $o_filename;
	public $description;
	public $credit;
	public $shared;		
	public $size; // Note -- this isn't used, but may be necessary later?
	public $height;
	public $width;
	public $type;	
	public $extension;
	public $extensions; /// array of allowed types
	public $imgsrc; // for use in outputting an image to HTML
	public $subimages;  // to store info about smaller sizes of the image...
	
	public $totalresults;
	public $perpage;
	public $page;
	public $totalpages;
	public $from;
	public $to;
	
	public $category;
	public $subcategory;	
	public $resource;
	
	public $upload_max_sizes = 10;
	public $upload_max_width = 2000;
	public $upload_max_height = 1600;
	
	// These are the allowed sizes for images in this instance of treeline
	public $sizes;
	// This determines whether or not uploaded images can be "stretched" to beyond their original size
	public $stretch;
			
	private $object; // This stores the PHP image object
		
	// This is loaded when the class is created	
	public function __construct() {
		
		/*
		$this->sizes = array
		(
			'446', '230', '204', '190','60', '250x80', '190x127', '190x92', '60x40'
		);
		*/
		$this->extensions = array('gif','jpg','png');
		$this->stretch = false;
		$this->subimages = array();
	}
	
	public function getGUID() {
		return $this->guid;
	}
	public function setGUID($guid) {
		$this->guid = $guid;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getTitle(){
		return $this->title;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function setCredit($credit) {
		$this->credit = $credit;
	}				

	public function setSharing($shared) {
		if ($shared) $this->shared=1;
		else $this->shared=0;
	}				
	
	public function setSize($size) {
		$this->size = $size;
	}

	public function setHeight($height) {
		$this->height = $height;
	}
	
	public function setWidth($width) {
		$this->width = $width;
	}		
			
	public function setResource($resource) {
		$this->resource=$resource;
	}
	public function getResource() {
		return $this->resource;
	}
			
	public function setExtension($extension) {
		$this->extension = $extension;
	}
	public function getExtension() {
		return $this->extension;
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


	// 11/12/2008 Comment		
	// Set an image category
	// If it does not exist create it
	public function setCategory($category=false) {
		global $db, $site;
		//$category = $db->escape($category);	
		if ($category>0) {
			$this->category=$category;
			return true;
		}
		
		$query = "SELECT id FROM imagecategories WHERE title = '$category' and parent=0 and site_id=".$site->id." LIMIT 1";
		$this->category = $db->get_var($query);
		if ($this->category>0)	return true;

		// if we don't yet have a category but we have a new one supplied, add it...
		if( $category>'' ){
			$tmpCat = htmlentities($db->escape($category));
			$query = "INSERT INTO imagecategories (title, site_id) VALUES ('$tmpCat', ".$site->id.")";
			if( $db->query($query) ){
				$this->category = $db->insert_id;
				return true;
			}
		}
		return false;
	}
	public function getCategory() {
		return $this->category;
	}


	// 11/12/2008 Comment
	// Set sub category if it does not exist then add it to the table
	// Images do not have to have a subcategory so if this fails we dont have to abort the operation
	public function setSubcategory($category=false, $subcategory=false, $newsubcategory=false) {
		global $db, $site;
		
		//print "setSubcategory($category, $subcategory, $newsubcategory)<br>";
		if ($subcategory>0) {
			$this->subcategory=$subcategory;
			return true;
		}
		if ($subcategory='xx' && !$newsubcategory) {
			$this->subcategory=0;
			return true;
		}

		//$category = $db->escape($category);	
		$query = "SELECT id FROM imagecategories WHERE title = '$newsubcategory' and parent=$category and site_id=".$site->id." LIMIT 1";
		$this->subcategory=$db->get_var($query);
		if ($this->subcategory>0) return true;
		
		// if we don't yet have a category but we have a new one supplied, add it...
		if( $newsubcategory>'' && $category>''){
			$tmpCat = htmlentities($db->escape($newsubcategory));
			$query = "INSERT INTO imagecategories (title, parent, site_id) VALUES ('$tmpCat', $category, ".$site->id.")";
			if( $db->query($query) ){
				$this->subcategory = $db->insert_id;
				return true;
			}
		}	
		return false;
	}
	public function getSubcategory() {
		return $this->subcategory;
	}
		

	// 11/12/2008 Comment
	// Create a new image record in the image table.
	// Add the first image size record (full size image)
	public function create($guid = '0') {

		global $db, $user, $site;

		$this->guid = uniqid();
		$title = $db->escape($this->title);			
		$description = $db->escape($this->description);
		$credit = $db->escape($this->credit);			
		$category = $db->escape($this->category);
		$original_size = 0; // is this the original uploaded file
		$userID = ($user) ? $user->getID() : 0 ;

		// Note that we use "filename" instead of "name" when updating the DB -- this is determined when saving the file to disk
		
		if($guid == 0){
			// INSERT INTO images table: main image data
			$query = "INSERT INTO images (guid, site_id, title, name, description, credit, shared, type, extension, category, subcategory, date_created, user_created)
						VALUES ('$this->guid', ".$site->id.",'$title', '{$this->name}','$description','$credit', ".($this->shared+0).", 
						'{$this->type}', '{$this->extension}', '$category', '".$this->subcategory."', NOW(), $userID)";
			$db->query($query);
			
			$guid = $this->guid;
			$original_size = 1;
		
		}
		
		$query = "INSERT INTO images_sizes 
			(guid, filename, height, width, filesize, original_size)
			VALUES
			('$guid', '{$this->filename}',{$this->height}, {$this->width}, 0, $original_size)";
		//echo $query;
		
		$db->query($query);
	}
		
		/*
		public function _create($parent='0') {
			global $db, $user;

			$this->guid = uniqid();
			$title = $db->escape($this->title);			
			$description = $db->escape($this->description);
			$credit = $db->escape($this->credit);			
			$category = $db->escape($this->category);

			// Note that we use "filename" instead of "name" when updating the DB -- this is determined when saving the file to disk
			$query = "INSERT INTO images (guid, title, name, filename, description, credit, height, width, type, extension, category, date_created, user_created,parent)
							VALUES ('$this->guid', '$title', '{$this->name}','{$this->filename}','$description','$credit', {$this->height}, {$this->width}, 
							'{$this->type}', '{$this->extension}', '$category', NOW(), {$user->getID()},'$parent')";

			$db->query($query);
			//$db->debug();
			
		}
		*/

	// 11/12/2008 Comment
	// Update the main image data		
	// we currently do not allow changing the image name.
	public function save($guid) {
		global $db, $user;
	
		$title = $db->escape($this->title);			
		$description = $db->escape($this->description);
		$credit = $db->escape($this->credit);
		$category = $db->escape($this->category);

		$query = "UPDATE images
			SET title='$title', description='$description', credit='$credit',
			shared='".($this->shared+0)."', category='{$category}', subcategory=".($this->subcategory+0).", 
			date_modified = NOW(), user_modified = ".$user->getID().", resource=".($this->resource+0)."
			WHERE guid = '$guid'";
		//print "$query<br>";
		$db->query($query);
	}
		/*
		public function _save() {
			global $db, $user;
		
			// Warning: can you change the name of an image? I think this will cause problems. TBC.
			$title = $db->escape($this->title);			
			//$name = $db->escape($this->filename);
			$description = $db->escape($this->description);
			$credit = $db->escape($this->credit);
			$category = $db->escape($this->category);

			$query = "UPDATE images
						SET title='$title', description='$description', credit='$credit', size='{$this->size}',
						category='{$category}', date_modified = NOW(), user_modified = {$user->getID()}
						WHERE (guid = '{$this->guid}') OR (parent = '{$this->guid}') ";
			$db->query($query);
			$db->debug();
		}
		*/
		
	// 11/12/2008 Comment
	// Remove image physically from disk and from the database...
	public function delete($guid){
		global $db;
		
		// need to load image details first...
		$this->loadImageByGUID($guid);
		
		$src = $_SERVER['DOCUMENT_ROOT'] .'/silo/images/';
		
		// Get a list of all image sizes(children)
		$subimages = $this->getImageChildren($guid);

		// delete physical files...
		if(unlink($src . $this->filename)){
			// remove child images...
			if($subimages){
				foreach($subimages as $img){
					@unlink($src . $img->filename);
				}
			}
		}	
		
		// get rid of the image from the database...
		$db->query("DELETE FROM images WHERE guid = '$guid'"); // main table (akak parent)
		$db->query("DELETE FROM images_sizes WHERE guid = '$guid'"); // sizes table (aka children)
		return true;
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
				
		public function generateName($force=false) {
			// Generates a "friendly" filename from $title
			// checking that there are no existing images with the same name/title
			global $db, $site;
			$title = $tmp_title = $db->escape($this->title);			
			$i=0;
			//$query="SELECT * FROM images WHERE title = '$title' and site_id=".$site->id." and category=".($this->category+0)." and subcategory=".($this->subcategory+0);
			// Update, don't allow duplicate names even in different catergories/sites.
			$query="SELECT * FROM images WHERE title = '$title'";
			//print "$query<br>";
			$db->query($query);
			if ($db->num_rows>0 && !$force) {
				return false;
			}
			
			while ($db->num_rows > 0 && $i<999) {
				$title=$tmp_title." ".++$i;
				//$query="SELECT guid FROM images WHERE title = '$title' and site_id=".$site->id." and category=".($this->category+0)." and subcategory=".($this->subcategory+0);
				$query="SELECT guid FROM images WHERE title = '$title'";
				//print "$query<br>";
				$db->query($query);
			}
			
			if ($i<999) {
				// Strip everything but letters, numbers and spaces from the title
				$name = preg_replace("/[^A-Za-z0-9 ]/", "", $title);
				// Replace spaces with dashes
				$name = str_replace(" ",'-',$name);
				$this->name = strtolower($name);
				//print "set name to ($name)<br>";
				return true;
			}
		}
		
		
		public function checkName($name){
			global $db;
			
			$query = "SELECT guid FROM images WHERE name = '$name' AND guid!='{$this->guid}'";
			$db->query($query);
			
			if($db->num_rows > 0){
				return true;
			}else{
				return false;
			}
		}	
		
		/* NEW STUFF: */
		
		public function loadFromPost($image) {

			$src = $image['tmp_name'];
	
			$this->setMemoryForImage($src);
			//if($this->setMemoryForImage($src)){
				list($this->width, $this->height, $type, $attr) = getimagesize($src);
				
				if( ($this->width > $this->upload_max_width) || ($this->height > $this->upload_max_height) ){
					return false;
				}else{
					if ($this->setType($type)) {
						// If we can correctly set the image-type
						if ($this->extension == 'gif') {
							$this->object = imagecreatefromgif($src); 
						}
						else if ($this->extension == 'jpg') {
							$this->object = imagecreatefromjpeg($src);
						}
						else if ($this->extension == 'png') {
							$this->object = imagecreatefrompng($src);
						}
						return true;
					} else {
						// Otherwise, this is an unrecognised image-type [ie, not GIF/JPEG/PNG]
						return false;
					}	
				}
			//}else{
			//	return false;
			//}
		}
		
		public function setType($type) {
			//echo "TYPE at " . __LINE__ . " is $type<br />";
			// 	1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM
			//$extensions = array('gif','jpg','png');
			if ($type > count($this->extensions)) {
				// image type not supported
				return false;
			}
			else {
				
				$this->type = image_type_to_mime_type($type);
				$this->extension = $this->extensions[$type-1]; // We don't refer directly to the mime-type here because a jpeg can be image/jpeg or image/pjpeg, for example
				return true;
			}
			
		}		
		
		public function loadFromFile($src) {
			//$src = $_SERVER['DOCUMENT_ROOT'] ."$src";
			if (!file_exists($src)) {
				return false;
			}
			
			//$this->setMemoryForImage($src);
			ini_set('memory_limit', '50M');

			list($this->width, $this->height, $this->type, $attr) = getimagesize($src);
			
			$this->extension = pathinfo($src);
			$this->extension = strtolower($this->extension['extension']);

			// $this->checkFormat(); no such function
			if ($this->extension == 'gif') {
				$this->object = imagecreatefromgif($src);
			}
			else if ($this->extension == 'jpg') {
				$this->object = imagecreatefromjpeg($src);
			}
			else if ($this->extension == 'png') {
				$this->object = imagecreatefrompng($src);
			}	
			return (boolean)$this->object;
		}	
		
		
		public function loadImageByGUID($guid){
			global $db;
			
			$query = "SELECT  * FROM get_formatted_image_list WHERE parent = '$guid' AND original_size = 1";
			
			if($imgdata = $db->get_row($query)){
			
				$this->guid=$imgdata->guid;
				$this->title=$imgdata->title;
				$this->name=$imgdata->name;
				$this->filename=$imgdata->filename;
				$this->o_filename=$imgdata->original_filename;
				$this->description=$imgdata->description;
				$this->credit=$imgdata->credit;
				$this->shared = $imgdata->shared;
				$this->size=$imgdata->size;
				$this->height=$imgdata->height;
				$this->width=$imgdata->width;
				$this->type=$imgdata->type;
				$this->extension=$imgdata->extension;
				$this->category=$imgdata->category;
				$this->subcategory=$imgdata->subcategory;
				$this->resource = $imgdata->resource;
				$this->imgsrc = $imgdata->name .'_'. $this->width .'x'. $this->height .'.'. $imgdata->extension;
				
				$query = "SELECT guid, filename, width, height FROM images_sizes WHERE guid = '{$this->guid}'";
				$data = $db->get_results($query);
				
				if($db->num_rows > 0){
					foreach($data as $subimg){
						$this->subimages[] = array('guid' => $subimg->guid,
							'filename' => $subimg->filename,
							'width' => $subimg->width,
							'height' => $subimg->height);
					}
				}
				
				return true;
			}
			return false;
		}
			
		
		public function resize ($max='',$minOrMax='max') {
			
			if ($max){
				// Have we specified an exact size?
				if (strpos($max,'x') > 0) {
					$s = explode('x',$max);
					$new_width = $s[0];
					$new_height = $s[1];
					
					// Get the width from the ratio of height to $this->height?
					if (!$new_width) $new_width=floor($this->width*($new_height/$this->height));
					if (!$new_height) $new_height=floor($this->height*($new_width/$this->width));
				}
				else {
					//// is it landscape or portrait?
					$formfactor = ($this->width > $this->height)?0:1;			
				
					//// what is the difference in scale??
					if ($formfactor==0) {
						if( $minOrMax=='min' ){
							$scale = ($max/$this->height);
							$new_width = ($scale * $this->width);
							$new_height = ($scale * $this->height);				
						}else{
							$scale = ($max/$this->width);
							$new_width = $max;
							$new_height = ($this->height * $scale);
						}
					} else {
						if( $minOrMax=='min' ){
							$scale = ($max/$this->width);
							$new_width = ($scale * $this->width);
							$new_height = ($scale * $this->height);	
						}else{
							$scale = ($max/$this->height);
							$new_height = $max;
							$new_width = ($this->width * $scale);						
						}		
					}
				}			
			}
			else {
				$new_width = $this->width;
				$new_height = $this->height;
			}
			
			if ($this->extension == 'gif' || $this->extension == 'png') {
				$new_image = imagecreate($new_width, $new_height);
			}
			else if ($this->extension == 'jpg') {
				$new_image = imagecreatetruecolor($new_width, $new_height);
			}		
			//echo "resized to $new_width, $new_height<br />";
			// set transparency [based on http://uk.php.net/manual/en/function.imagecolortransparent.php]

//			$transparency = imagecolortransparent($this->object);
//			imagepalettecopy($new_image,$this->object);
//			imagefill($new_image,0,0,$transparency);
//			imagecolortransparent($new_image, $transparency);
		
			// copy the uploaded file into the image object, resizing at the same time
			imagecopyresampled($new_image, $this->object, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
			
			// replace the current image object with the new one:
			$this->object = $new_image;
			$this->width = imagesx($this->object);
			$this->height = imagesy($this->object);
	
		}
		
		function crop_and_resize($size)
		{
			$size = explode('xc', $size);
			$cw = $size[0];
			$ch = $size[1];
							
			if ($this->extension == 'gif' || $this->extension == 'png') {
				$new_image = imagecreate($cw, $ch);
			}
			elseif ($this->extension == 'jpg') {
				$new_image = imagecreatetruecolor($cw, $ch);
			}
			$sw = $this->width;
			$sh = $this->height;
			 
			$wscale = $cw / $sw;
			$hscale = $ch / $sh;

			if ($wscale > $hscale) {
			        $dw = $cw;
			        $dh = $sh * $wscale;
			        $dx = 0;
			        $dy = ($ch - $dh)/2;
			} else {
			        $dh = $ch;
			        $dw = $sw * $hscale;
			        $dx = ($cw - $dw) / 2;
			        $dy = 0;
			}
			$done = imagecopyresampled($new_image, $this->object, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);
			$this->object = $new_image;
			$this->width = imagesx($this->object);
			$this->height = imagesy($this->object);
			return $done;
		}
		
		public function drawConfigSizes() {
			global $site;
			$html='';
			if (is_array($site->config['size'])) {
				foreach ($site->config['size'] as $i) {
					$html.='<input type="hidden" name="imgsz-'.$i['index'].'" value="'.$i['size'].'" />';
					$html.='<label>'.$i['size'].'</label>';
					$html.='<input type="checkbox" class="right" name="imgdel-'.$i['index'].'" value="1" onclick="warndel(this.checked);" />';
					$html.='<input type="text" name="imgdsc-'.$i['index'].'" value="'.$i['desc'].'" /><br />';
				}
			}
			return $html;
		}
		
		// Check if an resize string is valid, must be number x number
		public function sizeFormatOk($width, $height, $desc, $crop) {
			if ($width==0 && $height==0) return 0;
			if ($width>$this->upload_max_width) return "Resize width must be less than ".$this->upload_max_width;
			if ($height>$this->upload_max_height) return "Resize height must be less than ".$this->upload_max_height;
			if ($crop && ($width==0 || $height==0)) return "You must set the width and height if you want to use the crop option";
			return 1;
		}
			
		public function saveAs($src) {
			// save the file out in the correct format
			if ($this->extension == 'gif') {
				return imagegif($this->object,$_SERVER['DOCUMENT_ROOT'].$src.'.gif');
			}
			else if ($this->extension == 'jpg') {
				return imagejpeg($this->object,$_SERVER['DOCUMENT_ROOT'].$src.'.jpg',100);
			}
			else if ($this->extension == 'png') {
				return imagepng($this->object,$_SERVER['DOCUMENT_ROOT'].$src.'.png');
			}	
			else {
				return false;
			}		
		}
		
		
		
		public function write($filename = null) {
			global $msg;
			// save the file into /silo/images, in the correct format and with its own unique name
			$this->filename = $this->name.'_'.$this->width.'x'.$this->height.'.'.$this->extension;

			if ($filename) $file = $filename;
			else $file = $_SERVER['DOCUMENT_ROOT'].'/silo/images/'.$this->filename;
		
			$msg.="write($file) ext(".$this->extension.")<br>\n";
			
			if ($this->extension == 'gif') {
				return imagegif($this->object,$file);
			}
			else if ($this->extension == 'jpg') {
				return imagejpeg($this->object,$file,70);
			}
			else if ($this->extension == 'png') {
				return imagepng($this->object,$file);
			}
			$msg.="Failed write<br>\n";
			return false;
		}
		
		
		
		
		public function _write() {
			// Move the uploaded image into /silo/images, with its own unique name
			if (move_uploaded_file($src, $_SERVER['DOCUMENT_ROOT'] . '/silo/images/'.$this->name.'_'.$this->width.'x'.$this->height.'.'.$this->extension)) {
				return false;
			}
			else {
				return 'Image cannot be saved onto the server';
			}
		}				
		
		public function close() {
			imagedestroy($this->object);
		}
		
		private function setMemoryForImage($filename) {
			// Taken from http://uk.php.net/manual/en/function.imagecreatefromjpeg.php
			$imageInfo = getimagesize($filename);
			$MB = 1048576;  // number of bytes in 1M
			$K64 = 65536;    // number of bytes in 64K
			$TWEAKFACTOR = 2;  // Or whatever works for you
			$memoryNeeded = round(	($imageInfo[0] * $imageInfo[1]
																* $imageInfo['bits']
																* $imageInfo['channels'] / 8
																+ $K64
															)	* $TWEAKFACTOR
														);
			//ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
			//Default memory limit is 8MB so well stick with that.
			//To find out what yours is, view your php.ini file.
			$memoryLimitMB = 32;
			$memoryLimit = $memoryLimitMB * $MB;
			if (function_exists('memory_get_usage') &&
				memory_get_usage() + $memoryNeeded > $memoryLimit) {
				$newLimit = $memoryLimitMB + ceil((memory_get_usage()
																						+ $memoryNeeded
																						- $memoryLimit
																						) / $MB
																					);
				ini_set('memory_limit', $newLimit . 'M');
				//echo $newLimit;
				return true;
			}
			else {
				return false;
			}
		}
	
		// 11/12/2008 Comment
		// Collect a list of image categories for the current site
		// Form the category list as drop down options selecting the current category.
		public function drawCategories($current) {
			global $db, $site;
			//print "dC($current)<br>\n";
			$categories = $this->getImageCategories($site->id);
			$html = '';
			if (is_array($categories)) {
				foreach ($categories as $category) {
					$selected = ($category->id == $current)?' selected="selected"':'';
					$html .= '<option value="'.$category->id.'"'.$selected.'>'.$category->title.'</option>';
				}
			}
			//print "ret($html)<br>\n";
			return $html;
		}	

		public function drawSubcategories() {
			global $db,$site;
			$categories = $this->getImageSubcategories($site->id);
			$html = '';
			if ($categories) {
				foreach ($categories as $category) {
					//$selected = ($category->id == $current)?' selected="selected"':'';
					//$html .= '<option value="'.$category->id.'"'.$selected.'>'.$category->title.'</option>';
					$html.="subcats.push(new Array(".$category->id.", ".$category->parent.",'".addslashes($category->title)."'));"."\n";
				}
			}
			return $html;
		}	
		
		// 2008-11-12 Comment 
		// Select the list of categories for the currently selected microsite.
		// Return an array of category data
		public function getImageCategories($msv=1){
			global $db;
			$query = "SELECT ic.id, ic.title,ic.site_id
				FROM imagecategories ic
				WHERE ic.site_id=$msv
				AND ic.parent=0
				GROUP BY ic.title
				ORDER BY ic.title";
			//print "$query<br>";
			$imgcat = $db->get_results($query);
			return $imgcat;
		}

		public function getImageSubcategories($msv=1){
			global $db;
			
			$query = "SELECT ic.id, ic.parent, ic.title,ic.site_id
						FROM imagecategories ic
						WHERE ic.site_id=$msv
						AND ic.parent > 0
						ORDER BY ic.parent, ic.title";
			//print "$query<br>";
			$imgcat = $db->get_results($query);
			return $imgcat;
		}
		
		
		
		
		// 11/12/2008 Comment
		// Collects a list of images from the database and returns an array of image data
		// Between the current start and end parameters.
		// If a category/subcategory are passed only this subset of images are returned
		public function getImageList($catid=false, $subcatid=false){
		
			//print "gIL($catid, $subcatid)<br>";
			global $db, $site;

			$this->from = $this->getPerPage()*($this->getPage()-1);
			$this->to = $this->getPerPage()*$this->getPage();
			//$this->to = $this->getPerPage();
			
			$where = " i.original_size = 1";
			if ($site->id>0) $where.=" AND site_id=".$site->id;
			if ($catid>0) $where.=" AND i.category=$catid";
			if ($subcatid>0) $where.=" AND i.subcategory=$subcatid";

			$query = "SELECT * FROM get_formatted_image_list i WHERE".$where;
			//print "$query<br>\n";
			$results = $db->get_results($query);
			
			$total = sizeof($results);
			$this->setTotal($total);	
			$this->setTotalPages($total);
			$db->flush();
			
			$query = "SELECT i.*, 
				(select filename from images_sizes where guid=i.guid and width <> 100 order by width asc limit 1) AS thumb
				FROM get_formatted_image_list i
				WHERE".$where."
				ORDER BY date_created DESC, title ASC
				LIMIT ". $this->from .",". $this->getPerPage();
			//print "$query<br>\n";				
			$files = $db->get_results($query);
				
			if(sizeof($files)>0) return $files;
			return false;
		}


	// 11/12/2008 Comment
	// Return html for a list of image with options to preview or perform a given action
	public function drawImageList($p=1, $cat=false, $subcat=false){

		global $site, $help, $page;		
		//print "dIL($page, $cat, $subcat)<br>";
		
		$action = $_REQUEST['action'];
		$this->setPerPage(10);
		$this->setPage($p);	

		if($results = $this->getImageList($cat, $subcat) ){
			$html = '<table class="tl_list">
<caption>'.$this->drawTotal() .'</caption>
<thead>
<tr>
	<th scope="col">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).'</th>
	<th scope="col">'.ucfirst($page->drawLabel("tl_generic_author", "Author")).'</th>
	<th scope="col">'.$page->drawLabel("tl_img_list_created", "Created On").'</th>
	<th scope="col">'.$page->drawLabel("tl_img_listmanage", "Manage image").'</th>
</tr>
</thead>
<tbody>
';
			foreach($results as $file){
				$filepath = '/silo/images/'.$file->filename;
				if (!preg_match("/(.*)\.(.*?)/", $filepath)) $filepath.=".".$file->extension;
				$html .= '<tr>
<td '.$help->drawInfoPopup("<img src=/silo/images/".$file->thumb." />", "html").'><strong>'.$file->title.'</strong></td>
<td><a href="mailto:'.$file->email .'" title="email '. $file->fullname.'">'. $file->username .'</a></td>
<td>'.$page->languageDate($file->datemade).'</td>
<td class="action">
<a '.$help->drawInfoPopup($page->drawLabel("tl_img_act_preview", "Preview this image")).' class="preview thickbox" href="'.$filepath.'" >Preview image</a>
<a '.$help->drawInfoPopup($page->drawLabel("tl_img_act_edit", "Edit image details")).' class="edit" href="/treeline/images/?action=edit&amp;guid='.$file->parent.'">edit this image</a>
<a '.$help->drawInfoPopup($page->drawLabel("tl_img_act_delete", "Delete the image")).' class="delete" href="/treeline/images/?action=delete&amp;guid='.$file->parent.'">delete this image</a>
</td>
</tr>
';
			}
			$html .= '</tbody>'."\n";
			$html .= '</table>'."\n";
			
			//$html .= $this->drawPagination("/treeline/images/?action=$action&amp;cat=$cat&amp;subcat=$subcat", $this->getTotal(), 10, $page);
			$html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/images/?action=edit&amp;cat=$cat&amp;subcat=$subcat");
			
			return $html;
		}
		else return '<p>'.$page->drawLabel("tl_img_list_none", "There are no images to display").'</p>'."\n";
	}
			
	// 11/12/2008 Comment
	// Return an array of image size data relating to a library image
	public function getImageChildren($guid){
		global $db;
		return $db->get_results("SELECT id, filename, width, height FROM images_sizes WHERE guid='$guid'");
	}
		
		
		//// this should be sed if you have an image object loaded...
		public function getThumbnail($guid){
			$this->loadImageByGUID($guid);
			
			foreach($this->subimages as $key => $value){					
				foreach($value as $guid => $filename){
					if( ($this->width > 100) || ($this->height > 100) ){
						if($guid=='filename'){
							$thumb['filename'] = $filename;
						}else if($guid=='width'){
							$thumb['width'] = $filename;
						}else if($guid=='height'){
							$thumb['height'] = $filename;
						}
					}else{
						$thumb['filename'] = $this->filename;
						$thumb['width'] = $this->width;
						$thumb['height'] = $this->height;
					}
				}
			}
			
			return $thumb;
		}


///// pagination methods /////


	public function drawTotal(){
		global $page;
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1) $msg = $page->drawLabel("tl_img_total_one", 'There is only 1 image in the library');
		else $msg = $page->drawLabel("tl_img_title_showing", 'Showing images').' '.($this->from+1) .'-'. $to .' '.$page->drawLabel("tl_generic_of", "of").' '.$this->getTotal();
		return $msg;
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



	public function uploadImage($upload, $title, $credit, $description, $shared, $category, $subcategory, $newcategory, $newsubcategory, $resource, $force_name=false) {
		global $db, $site;
		//print "uI('FILE', $title, $credit, 'description', $shared, $category, $subcategory, $newcategory, $newsubcategory, $force_name)<br>";
		
		if (!$upload['name']) return 'No file was uploaded';

		// Check we have valid data before we bother doing anything complicated
		if ($category == 'xx' && $newcategory == '') {
			return 'Please select a category for this image, or add a new category';
		}
		if (!$upload) {
			return 'There was a problem uploading your image. It may be too large.';
		}
		if ($error = $this->getUploadError($upload['error'])) {
			return $error; 
		}

		// Set category and subcategory
		if ($category == 'xx') {
			if (!$this->setCategory($newcategory)) {
				return 'A category with that name already exists';
			}
		}
		else {
			$this->setCategory($category);
		}
		$this->setSubcategory($this->getCategory(), $subcategory, $newsubcategory);
		//print "set cat(".$this->category.") subcat(".$this->subcategory.")<br>";
		
		// Check we do not have an image by the same name?
		$this->setTitle($title);
		if (!$this->generateName($force_name)) {
			return 'An image with that name already exists in the library';
		}

		$this->setDescription($db->escape($description));
		$this->setCredit($db->escape($credit));								
		$this->setSharing($shared);
		
		// We now set the image dimensions, size and type
		if (!$this->loadFromPost($upload)) {
			return "Your image is either not a GIF, JPEG or PNG, or it's dimensions are too large. The maximum dimensions should be ".$this->upload_max_width."px x ".$this->upload_max_height."px";
		}

		//// save the original image
		$this->resize('');
		// Save the image to disc
		if (!$writeOK = $this->write()) {
			return "Failed to write image to disk";
		}
		else {
			$orig_file = $_SERVER['DOCUMENT_ROOT'].'/silo/images/'.$this->filename;
			//print "Overwrite the file(".$orig_file."(".filesize($orig_file).") with ".$upload['tmp_name']."(".filesize($upload['tmp_name']).")<br>\n";
			if (!copy($upload['tmp_name'], $orig_file)) {
				//print "Failed to move file<br>\n";
			}			
			//print "Overwritten the file(".$orig_file."(".filesize($orig_file).") with ".$upload['tmp_name']."(".filesize($upload['tmp_name']).")<br>\n";
		}
		
		$this->create();
		$this->master_guid = $this->getGUID();
		
		// Create resized images
		//print "resize to (";
		//print_r($this->sizes);
		//print "<br>";
		
		foreach ($site->config['size'] as $i) {												
			
			// Resize and crop centrally from the original
			//print "first x pos in (".$i['size'].") (".strpos($i['size'], 'xc').")<br>";
			if (strpos($i['size'],'xc') != "") {
				//print "crop to $size<br>";
				$cropped = clone $this;
				//$cropped->loadFromPost($upload);
				$cropped->crop_and_resize($i['size']);
				if (!$cropped->write()) $message[]= "Failed image size($size)";
				else $cropped->create($this->master_guid);
			}
			// Resize through the sizes
			else {
				$resize = clone $this;
				//$resize->loadFromPost($upload);
				//print "resize to $size<br>";
				$resize->resize($i['size']);
				if (!$resize->write()) $message[]= "Failed image size($size)";
				else $resize->create($this->master_guid);
			}
		}
		
		// create the thumbnail for the imagepicker
		$this->resize('100x100');
		if (!$this->write()) $message[]= "Failed image thumbnail";
		else $this->create($this->master_guid);
		
		// Not sure what to do if we have $message(s)? as the image upload has basically suceeded?
		if (count($message)) {
			return 0;
		}
		
		// Success - image uploaded and all sizes created ok.
		return 1;
		
	}


	public function uploadFromDesktop($upload, $destination, $max_width=1000, $resize="") {
		
		//print "uFD(".print_r($upload, true).", $destination, $max_width, $resize)<br>\n";
		list($width, $height, $type, $attr) = getimagesize($upload['tmp_name']);
		$mime_type = image_type_to_mime_type($type);		
		//print "got image type($mime_type) h($height) w($width)<br>\n";
		
		if ($width <= $this->upload_max_width) {
			switch($mime_type) {	
				case "image/jpeg": $ext="jpg"; break;
				case "image/gif": $ext="gif"; break;
				case "image/png": $ext="png"; break;
			}
			if ($ext) {
				//print "got ext($ext)<Br>\n";
				$destination.=".".$ext;
				if (!$resize) {
					//print "muf(".$upload['tmp_name'].") $destination)<br>\n";
					if (move_uploaded_file($upload['tmp_name'], $destination)) {
						return $destination;
					}
					else return "Failed to write uploaded file to disk";
				}
				else {
					// More tricky, we need to load the object and resize/crop it.
					if (!$this->loadFromPost($upload)) {
						return "Your image is either not a GIF, JPEG or PNG, or it's dimensions are too large. The maximum length or width should be 800 pixels.";
					}
					else {
						//print "resize to:$resize<br>";
						if (strpos($resize,'xc') != "") {
							$tmp = $this;
							$tmp->loadFromPost($upload);
							$tmp->crop_and_resize($resize);
						}
						// Resize through the sizes
						else {
							$tmp = $this;
							$tmp->loadFromPost($upload);
							$tmp->resize($resize);
						}
						//print "write($destination)<br>\n";
						if (!$tmp->write($destination)) return "Resize write failed at($resize)";
					}
				}				
			}
			else return "Files of this type are not allowed, uploads must be JPG, GIF or PNG only";
		}
		else return "Your file too large, maximum upload width is ".$this->upload_max_width." pixels";
		
		return $destination;
	}

	public function _uploadFromDesktop($upload, $destination, $max_width=1000) {
		
		//print "uFD(".print_r($upload, true).", $destination)<br>\n";
		list($width, $height, $type, $attr) = getimagesize($upload['tmp_name']);
		$mime_type = image_type_to_mime_type($type);		
		//print "got image type($mime_type) h($height) w($width)<br>\n";
		
		if ($width <= $max_width) {
			switch($mime_type) {	
				case "image/jpeg": $ext="jpg"; break;
				case "image/gif": $ext="gif"; break;
				case "image/png": $ext="png"; break;
			}
			if ($ext) {
				$destination.=".".$ext;
				//print "muf(".$upload['tmp_name'].", $destination)<br>\n";
				if (file_exists($upload['tmp_name'])) {
					if (move_uploaded_file($upload['tmp_name'], $destination)) {
						return $destination;
					}
					else return "Failed to write uploaded file to disk";
				}
				else return "Uploaded tmp file does not exist?";
			}
			else return "Files of this type are not allowed, uploads must be JPG, GIF or PNG only";
		}
		else return "Your file too large, maximum upload width is ".$max_width." pixels";
		
		return;
	}
		


	public function getPagination($page,$action,$cat,$subcat){
		$totalpages = $this->getTotalPages();
		if($totalpages>1){
			$html = '<ul class="pagination">';
			
			if( $page > 1 ){
				$html .= '<li class="bookend"><a href="/treeline/images/?action='. $action .'&amp;category='. $cat .'&amp;subcategory='.$subcat.'&amp;p='. ($page-1) .'">Previous</a></li>'."\n";
			}
			for($i=1;$i<=$totalpages;$i++){
				if($page == $i){
					$html .= '<li class="inactive">'.$i.'</li>'."\n";
				}else{
					$html .= '<li><a href="/treeline/images/?action='. $action .'&amp;category='. $cat .'&amp;subcategory='.$subcat.'&amp;p='. $i .'">'. $i .'</a></li>'."\n";
				}
			}
			if( ($totalpages > 1) && ($page < $totalpages)){
				$html .= '<li class="bookend"><a href="/treeline/images/?action='. $action .'&amp;category='. $cat .'&amp;subcategory='.$subcat.'&amp;p='. ($page+1) .'">Next</a></li>'."\n";
			}
			$html .= '</ul>'."\n";
			
			return $html;
		}
		else{
			return false;
		}
	}
	
}

?>