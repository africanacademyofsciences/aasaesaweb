<?php
class Gallery extends Page
{
	/* 1 means we are working with the non-live gallery
	0 means we are working with the live gallery. */
	private $revision = 1;
	
	public function getRevision() {return $this->revision;}
	public function setRevision($revision) {$this->revision = $revision;}
	
	
	
	
	/* Accepts a min and max width and returns all images
	associated with this gallery page around that range. */
	public function getImages($min_width, $max_width)
	{
		global $db;
		
		// Get the images that belong to this gallery page
		$gallery_images = $db->get_results
		("
			SELECT images_galleries.*, images_sizes.filename, images_sizes.width, images_sizes.height
			FROM images_galleries LEFT JOIN
			(
				SELECT guid, width, height, filename
				FROM images_sizes
				WHERE width >= $min_width
				AND width <= $max_width
				ORDER BY width ASC
			) images_sizes
			ON images_galleries.image_guid = images_sizes.guid
			WHERE images_galleries.page_guid = '".$this->getGUID()."'
			AND images_galleries.revision = ".$this->getRevision()."
			GROUP BY images_galleries.image_guid
			ORDER BY images_galleries.sort_order
		");
		if (!$gallery_images) {	// No images in this gallery
			return false;
		}
		// We do have images...
		foreach ($gallery_images as &$image)
		{
			// No image found within the range, so try to get another - daft I know
			if (!$image->filename)
			{
				$tmp = $db->get_row
				("
					SELECT filename, width, height
					FROM images_sizes
					WHERE guid = '$image->image_guid'
					AND width > $min_width
					ORDER BY width ASC
					LIMIT 1
				");
				$image->filename = $tmp['filename'];
				$image->width = $tmp['width'];
				$image->height = $tmp['height'];
			}
		}
		return $gallery_images;
	}
		
	
	
	/* Accepts an image guid of this gallery page
	and returns an array of image data. */
	public function getImage($image_guid)
	{
		global $db;
		$images = $db->get_results
		("
			SELECT images_galleries.*, images_sizes.filename
			FROM images_galleries
			LEFT JOIN images_sizes
			ON images_galleries.image_guid = images_sizes.guid
			WHERE images_galleries.page_guid = '".$this->getGUID()."'
			AND images_galleries.image_guid = '$image_guid'
			AND images_galleries.revision = ".$this->getRevision()."
			ORDER BY width ASC
		");
		$image = (Array)$images[0];	// We only need one row of data, but the filenames from all the rows
		array_pop($image);			// Remove the single filename key
		$image['filenames'] = array();// Replace it with the filenames array
		foreach ($images as $i) {	
			array_push($image['filenames'], $i->filename);
		}
		unset($images);
		return $image;
	}
	
	
	
	/* Accepts an image guid to update, and an array of
	details to change. */
	public function updateImage($image_guid, $details)
	{
		global $db;
		
		// Build update query
		foreach ($details as $key => $val)
		{
			$value = $db->escape($val);
			$value = (is_numeric($val)) ? $value : "'$value'";
			$update .= "$key = $value, ";
		}
		$update = rtrim($update, ', ');
		
		$db->query
		("
			UPDATE images_galleries SET
			$update
			WHERE image_guid = '$image_guid'
			AND page_guid = '".$this->getGUID()."'
			AND revision = ".$this->getRevision()."
		");
		return $db->rows_affected;
	}
	
	
	
	/* Accepts an image guid, and transfers the data
	from the images table to the images_galleries table. */
	public function addExistingImage($image_guid)
	{
		global $db;
		if ($this->imageExists($image_guid))
		{
			return false; // Don't add duplicates
		}
		
		// Copy over to gallery table
		$db->query
		("
			INSERT INTO images_galleries
			(id, image_guid, page_guid, description, sort_order, revision)
			SELECT
			(0),
			('$image_guid'),
			('".$this->getGUID()."'),
			description,
			(
				SELECT MAX(sort_order)+1
				FROM images_galleries
				WHERE page_guid = '".$this->getGUID()."'
				AND revision = ".$this->getRevision()."
			),
			(".$this->getRevision().")
			FROM images
			WHERE guid = '$image_guid'
		");
		return $db->rows_affected;
	}
	
	
	
	/* Accepts an image guid and checks to see
	if the image is already in the gallery. */
	public function imageExists($image_guid)
	{
		global $db;
		return (boolean)$db->get_var
		("
			SELECT COUNT(image_guid)
			FROM images_galleries
			WHERE image_guid = '$image_guid'
			AND page_guid = '".$this->getGUID()."'
			AND revision = ".$this->getRevision()."
		");
	}
	
	
	
	/* Publish the page first, then the images
	associated with it. */
	public function publish()
	{
		parent::publish();
		$this->publishImages();
	}
	
	
	
	/* Publish the images in this gallery */
	private function publishImages()
	{
		global $db;
		// Delete the live gallery images
		$this->deleteImages(0);
		// Set the non-live gallery images as live
		$db->query
		("
			UPDATE images_galleries
			SET revision = 0
			WHERE page_guid = '".$this->getGUID()."'
			AND revision = 1
		");
		// Duplicate the new live gallery images as non-live
		$live_images = $db->get_results
		("
			INSERT INTO images_galleries
			(id, image_guid, page_guid, description, sort_order, revision)
			SELECT (0), image_guid, page_guid, description, sort_order, (1)
			FROM images_galleries
			WHERE page_guid = '".$this->getGUID()."'
			AND revision = 0
		");
	}
	
	
	
	/* Delete the page, and all the images
	associated with this gallery page */
	public function delete()
	{
		parent::delete();
		$this->deleteImages();
	}
	
	
	
	/* Accepts an image guid, and deletes it
	from this gallery page. */
	public function deleteImage($image_guid)
	{
		global $db;
		$db->query
		("
			DELETE FROM images_galleries
			WHERE image_guid = '$image_guid'
			AND page_guid = '".$this->getGUID()."'
			AND revision = ".$this->getRevision()."
		");
		return $db->rows_affected;
	}
	
	
	
	/* Delete all the images associated with this gallery
	unless a revision is specified. In which case, only
	delete from that revision. */
	private function deleteImages($revision = null)
	{
		global $db;
		if ($revision === 1 || $revision === 0)
		{
			$revision = "AND revision = $revision";
		}
		$db->query
		("
			DELETE FROM images_galleries
			WHERE page_guid = '".$this->getGUID()."'
			$revision
		");
		return $db->affected_rows;
	}

	
	
	/* Because the user can directly change the sort order
	of images, they might a mistake e.g. 1, 2, 5, 6, 7, 8 etc.
	So we need to go through and fix it */
	public function fixSortOrder()
	{
		global $db;
		$gallery_images = $db->get_results
		("
			SELECT image_guid, sort_order
			FROM images_galleries
			WHERE page_guid = '".$this->getGUID()."'
			AND revision = ".$this->getRevision()."
			ORDER BY sort_order ASC
		");
		$i = 1;
		foreach ($gallery_images as $image)
		{
			$db->query
			("
				UPDATE images_galleries
				SET sort_order = $i
				WHERE image_guid = '$image->image_guid'
				AND page_guid = '".$this->getGUID()."'
				AND revision = ".$this->getRevision()."
			");
			$i++;
		}
	}
	
}
?>