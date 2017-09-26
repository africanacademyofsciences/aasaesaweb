<?php
class TO_Gallery extends Treeline_object
{
	// Where this gallery object is saved
	public $table_name = 'galleries';

	/*
	public $types = array
	(
		'image'		=> 'image gallery',
		'report'	=> 'photographic report gallery'
	);
	*/
	public $types = array('image' => 'image gallery');
	
	// An array of all the images associated with this gallery
	public $images = array();

	public $tags='';

	// An array of database fields that relate to the details of this object
	public $fields = array
	(
		'id'			=> 0,
		'type'			=> 'image',
		'sort_order'	=> 0,
		'title'			=> '',
		'description'	=> '',
		'main_image_id'	=> 0,
		'live'			=> false,
		'memberonly'	=> false,
		'msv'			=> 1,
		'pageguid'		=> ''
	);
	
	// -------------------- Getters --------------------
	
	//public function __construct($msv=1) {
	//	$this->set("msv", $msv);
	//}
	
	public function get_images()	{return $this->images;}
	private function get_path()
	{
		return $_SERVER['DOCUMENT_ROOT'].'/silo/images/galleries/'.$this->fields['id'];
	}
	
	// -------------------- Setters --------------------
	
	public function set_images($images) {$this->images = $images;}

	/* -------------------------------------------------
	Load this gallery object from the database and then
	load all its associated images.
	------------------------------------------------- */
	public function load($id = null)
	{
		global $db;
		
		$loaded = parent::load($id);
		if ($loaded)
		{
			$this->images = @$db->get_results
			("
				SELECT *
				FROM gallery_images
				WHERE gallery_id = $id
				ORDER BY sort_order ASC
			", ARRAY_A);
		}
		return $loaded;
	}
	
	/* -------------------------------------------------
	Go through this gallery's images array and update
	the corresponding entries in the database.
	If the image is marked for deletion, delete it.
	If the image is set as the main one, update the
	gallery accordingly.
	------------------------------------------------- */
	public function update_images()
	{
		global $db;
		
		$temp = new Treeline_object;
		$temp->set_table_name('gallery_images');
		
		foreach ($this->images as $image)
		{
			$temp->set('id', $image['id']);
			
			if ($image['marked_for_deletion'])
			{
				// Delete db record and actual files
				$temp->delete();				
				@unlink($this->get_path().'/t_'.$temp->get('id').'.jpg');
				@unlink($this->get_path().'/b_'.$temp->get('id').'.jpg');
				@unlink($this->get_path().'/m_'.$temp->get('id').'.jpg');
			}
			else
			{
				$temp->set('title',			$image['title']);
				$temp->set('sort_order',	$image['sort_order']);
				$temp->set('credit',		$image['credit']);
				$temp->set('description',	$db->escape($image['description']));
				$temp->save();
				if	($image['main_gallery_image'])
				{
					$db->query("UPDATE galleries SET main_image_id = ".($image['id']+0)." WHERE id = ".$this->fields['id']);
				}

			}
		}
	}
	
	/* -------------------------------------------------
	Remove the database entries and files for
	this gallery.
	------------------------------------------------- */
	public function delete()
	{
		parent::delete($id);				// Remove gallery record
		$this->delete_images_from_silo();	// Delete the images themselves
		$this->delete_images_from_db();		// And the assoicated image records
	}
	
	/* -------------------------------------------------
	Remove this gallery's images from the database
	------------------------------------------------- */
	private function delete_images_from_db()
	{
		global $db;
		$gallery_id = $this->db_safe($this->fields['id']);
		
		if ($gallery_id)
		{
			$db->query
			("
				DELETE FROM gallery_images
				WHERE gallery_id = $gallery_id
			");
		}
	}
	
	/* -------------------------------------------------
	Remove this gallery's images from the silo
	------------------------------------------------- */
	private function delete_images_from_silo()
	{
		$gallery_id = $this->fields['id'];
		
		if ($gallery_id)
		{
			$path = $this->get_path();
			
			foreach (glob($path.'/*.*') as $image) @unlink($image);
			@unlink($path);
		}
	}

	/* -------------------------------------------------
	Draw a select box of the different types of gallery
	If this gallery has a certain type then highlight it.
	Or, highlight the one passed as a parameter.
	------------------------------------------------- */
	public function draw_gallery_types_select_box($selected = null)
	{
		global $db;
	
		?>
        <input type="hidden" name="type" value="image" />
        <?php
		return;
		
		// STuff all this junk. Photo reports were a bad idea.
		?>
		<label for="type">Type</label>
		<select name="type" id="type">
		<?php
		if (!is_null($selected))
		{
			$highlight_type = $selected;	// Highlight is overrided
			?><option value="0">All galleries</option><?php }
		else
		{
			$highlight_type = $this->fields['type']; // Highlight this gallery's type
		}
		foreach ($this->types as $key => $val)
		{
			$s = ($highlight_type == $key) ? ' selected="selected"' : '';			
			?><option value="<?=$key?>"<?=$s?>><?=ucfirst(htmlentities($val))?></option><?php
		}
		?></select><?php
	}
	
	
	public function drawSelectPageList() {
		global $db, $page;
		$query = "SELECT guid, title FROM pages WHERE template = 68 AND offline=0";
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$html.= '<option value="'.$result->guid.'"'.($result->guid==$this->fields['pageguid']?'selected="selected"':'').'>'.$result->title.'</option>';
			}
		}
		if ($html) $html = '<select name="pageguid"><option value="">'.$page->drawLabel("tl_gall_crea_pagelist", "Select gallery for this slideshow").'</option>'.$html.'</select>';
		else $html = '<span>'.$page->drawLabel("tl_gall_err_nogallery", "No gallery pages have been created yet").'</span>';
		return $html;
	}
}
?>