<?php

	/*
	
		IMAGES FUNCTIONS
		
		
		Contents:
		# - uploadImage()
	
	*/

	function uploadImage($upload, $imgName, $category, $title, $description){
		global $image;
			
		$title .= imgName.rand(srand((double)microtime()*1000000),100);
		$description .= $imgName;
		$credit = $imgName;
			
		$image->setTitle($title);
		$image->setDescription($description);
		$image->setCredit($credit);								
		$name = $image->generateName();
		if (!$name) {
			$feedback['message'][] = 'An image with that name already exists in the library';
			$feedback[0] = 'error';
		}	
		else {
		
			$image->setCategory($category);
			$categoryOK = true;
			
			
			// Now -- take the image that's been uploaded, check it's there, set the original name, filesize and mime						
			if (!$upload) {
				$feedback['message'] = 'There was a problem uploading your image. It may be too large.';
				$feedback[0] = 'error';
			}
			else if ($error = $image->getUploadError($upload['error'])) {
				$feedback['message'] = $error; 
				$feedback[0] = 'error';
			}
			else {
				
				// We now set the image dimensions, size and type
				// Do we need to check the filesize here, or is that covered in the preceding clauses?
			
				if (!$image->loadFromPost($upload)) {
					$feedback['message'][] = 'Your image is either not a GIF, JPEG or PNG, or it\'s dimensions are too large.';
					$feedback['message'][] = 'The maximum length or width should be 800 pixels.';
					$feedback[0] = 'error';
				}
				else {
					$writeOK = true;
					
					//// save the original image
					$image->resize('');
					// Save the image to disc
					if (!$writeOK = $image->write()) {
						$feedback[0] = 'error';
						break;
					}
					$image->create();
					$imgGUID = $image->getGUID();
				
					foreach ($image->sizes as $size) {
						//echo $size;
						$image->resize($size);
						// Save the image to disc
						if (!$writeOK = $image->write()) {
							$feedback[0] = 'error';
							break;
						}
						// Creates a new record in the database, with the original image as the parent.
						$image->create($imgGUID);
					}
					
					// create the thumbnail for the imagepicker
					$image->resize('100x100');
					if (!$writeOK = $image->write()) {
						$feedback[0] = 'error';
						break;
					}								
					
						
					if (!$writeOK) {
						$feedback[0] = 'error';
						$feedback['message'] = 'Image cannot be saved to disk';

					}

					else {
						$image->close();
						$feedback[0] = 'success';
						$feedback['message'] = '';
						$feedback['guid'] = $imgGUID;
					}
				}					
			}
		}
		return $feedback;
	}

?>