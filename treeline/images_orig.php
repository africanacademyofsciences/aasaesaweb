<?php

	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	
	$tags = new Tags();// TAGS

	$action = read($_REQUEST,'action','');
	if (!$action) { // No action: send back to Treeline home: Why?
		header("Location: /treeline/");
	}
	$guid = read($_REQUEST,'guid','');

	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	$title = read($_POST,'title','');
	$credit = read($_POST,'credit','');
	$description = read($_POST,'description','');
	$tagslist = read($_POST,'tagslist','');
	$category = read($_REQUEST,'category','xx');
	$newcategory = read($_POST,'newcategory',false);
	
	$findcat = read($_POST,'findcat',false);

	$upload = read($_FILES,'image',false);

	// Create a new image:
	$image = new Image;
		
	$thispage = read($_GET,'p',1);
	$image->setPage($thispage);

//echo '<!--new category'. $newcategory.'<br />-->';
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($action == 'create') {
			if (!$title) {
				$message = 'Please enter a title for this image';
				$feedback = 'error';
			}
			else if ($category == 'xx' && $newcategory == '') {
				$message = 'Please select a category for this image, or add a new category';
				$feedback = 'error';
			}
			else {
				$image->setTitle($title);
				$image->setDescription($description);
				$image->setCredit($credit);								
				$name = $image->generateName();
				if (!$name) {
					$message = 'An image with that name already exists in the library';
					$feedback = 'error';
				}	
				else {
					if ($category == 'xx') {
						$categoryOK = $image->setCategory($newcategory);
					}
					else {
						$image->setCategory($category);
						$categoryOK = true;
					}
					
					if (!$categoryOK) {
						// Check that we're not creating a duplicate category
						$message = 'A category with that name already exists';
						$feedback = 'error';
					}
					else {
						// Now -- take the image that's been uploaded, check it's there, set the original name, filesize and mime						
						if (!$upload) {
							$message = 'There was a problem uploading your image. It may be too large.';
							$feedback = 'error';
						}
						else if ($error = $image->getUploadError($upload['error'])) {
							$message = $error; 
							$feedback = 'error';
						}
						else {
							
							// We now set the image dimensions, size and type
							// Do we need to check the filesize here, or is that covered in the preceding clauses?
						
							if (!$image->loadFromPost($upload)) {
								$message[] = 'Your image is either not a GIF, JPEG or PNG, or it\'s dimensions are too large.';
								$message[] = 'The maximum length or width should be 800 pixels.';
								$feedback = 'error';
							}
							else {
								$writeOK = true;
								
								//// save the original image
								$image->resize('');
								
								
								
								// Save the image to disc
								if (!$writeOK = $image->write()) {
									break;
								}
								$image->create();
								$imgGUID = $image->getGUID();
								

								foreach ($image->sizes as $size) {												
									
									// Resize and crop centrally from the original
									print "first x pos(".strpos($size, 'x').")<br>";
									if (strpos($size,'x') != "") {
										print "crop to $size<br>";
										$cropped = $image;
										$cropped->loadFromPost($upload);
										$cropped->crop_and_resize($size);
										if (!$writeOK = $cropped->write()) {
											break;
										}
										$cropped->create($imgGUID);
									}
									// Resize through the sizes
									else {
										print "resize to $size<br>";
										$image->resize($size);
										if (!$writeOK = $image->write()) {
											break;
										}
										$image->create($imgGUID);
									}
									
								}
								
							
								// create the thumbnail for the imagepicker
								$image->resize('100x100');
								if (!$writeOK = $image->write()) {
									break;
								}								
								
									
								if (!$writeOK) {
									$message = 'Image cannot be saved to disk';

								}

								else {
									$tags->addTagsToContent($imgGUID, $tagslist, 2);//addTags
									$image->close();
								// NOTE -- guid is eroneous here as we've just created multiple images of various sizes	
									$redirectURL="/treeline/images/?action=edit&guid={$imgGUID}&".createFeedbackURL('success','Your image has been added');
									redirect ($redirectURL);	
								}
							}
						}					
					}
				}
			}
		}

		else if ($action == 'edit') {//////////////////// NEEDS WORK
			if(!$_POST['findcat']){
				if (!$title) {
					$message = 'Please enter a title for this image';
					$feedback = 'error';
				}
				else if ($category == 'xx') {
					$message = 'Please select a section for this image';
					$feedback = 'error';
				}			
				else {
					$image->loadImageByGUID($guid);
					$image->setTitle($title);
					$image->setCategory($category);
					$credit = read($_POST,'credit','');
					$image->setCredit($credit);
					$image->setDescription($description);
					$image->generateName();
	
					if (!$name) {
						$message = 'An image with that name already exists in that section';
						$feedback = 'error';
					}				
					else {			
						$image->save($guid);
						$tags->addTagsToContent($guid, $tagslist, 2); //add tags
						redirect('/treeline/images/?action=edit&'.createFeedbackURL('success','This image has been updated'));
					}
				}
			}
		}
		else if ($action == 'delete') {
			if(!$_POST['findcat']){
				//$image->loadImageByGUID($guid);
				if($image->delete($guid)){
					redirect("/treeline/images/?action=delete&".createFeedbackURL('success','That image hass been deleted'));
				}else{
					$message = "There was a problem deleting your image.<br />Please try again.";
					$feedback = 'error';
				}
			}
		}				
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Images : '.ucwords($action) : 'Images';
	$pageTitle = ($action) ? 'Images : '.ucwords($action) : 'Images';
	
	$pageClass = 'images';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
            <div id="primarycontent">
                <div id="primary_inner">
                  <?=drawFeedback($feedback,$message)?>	
                  <?php if ($action == 'create') { ?>
                  <form id="treeline" enctype="multipart/form-data" action="<?=$_SERVER['REQUEST_URI']?><?php if ($DEBUG) echo '?debug'?>" method="post">
                    <fieldset>
                        <legend>Create an image</legend>
                        <input type="hidden" name="action" value="<?=$action?>" />
                        <input type="hidden" name="guid" value="<?=$guid?>" />
                        <p class="instructions">To upload a new image to the image-library, please complete the form below.<br />
                        Images must be <?php foreach($image->extensions as $ext){ echo strtoupper($ext).','; } ?> and maximum width or height of 800 pixels.</p>
                        <label for="image">Select an image:</label>
                        <input type="file" class="text" name="image" id="image" value="<?=$upload['name']?>" /><br />     
                        <label for="title">Title:</label>
                        <input type="text" class="text" name="title" id="title" value="<?=$title?>" /><br /> 
                        <label for="description">Description:</label>
                        <textarea name="description" id="description"><?=$description?></textarea><br />  
                        <label for="credit">Credit: <span style="font-weight:normal; font-style: italic">optional</span></label>
                        <input type="text" class="text" name="credit" id="credit" value="<?=$credit?>" /><br />    
                        <label for="tagslist">Tags:</label>
                        <textarea id="tagslist" name="tagslist" class="lessText" rows="2" cols="30"><?=$tagslist?></textarea><br />     
                        <label for="category">Category:</label>
                        <select name="category" id="category">
                          <option value="xx">Select:</option>
                          <?php // Should this be a method of Image, rather than Treeline? ?>
                          <?=$image->drawCategories($category)?>
                        </select><br />
                        <label for="newcategory"><em style="font-weight: normal; font-style: italic">Or</em> Add category:</label>
                        <input type="text" class="text" name="newcategory" id="newcategory" value="<?=$newcategory?>" />
                        <fieldset class="buttons">
                            <button type="submit" class="submit">submit</button>
                        </fieldset>
                    </fieldset>
                  </form>
               <?php }
                else if ($action == 'edit') { ?>
               <?php if (!$guid) { ?>
                  <form id="treeline" enctype="multipart/form-data" action="/treeline/images/?action=edit<?php if ($DEBUG) echo '?debug'?>" method="post">
                    <fieldset>
                        <legend>Edit the details of an image</legend>
                        <input type="hidden" name="action" value="<?=$action?>" />
                        <input type="hidden" name="guid" value="<?=$guid?>" />
                        <p class="instructions">To edit an image's details, or move an image, please select it from the list below below:</p>
                        <label for="category">Category:</label>
                        <select name="category" id="category">
                            <option value="xx">Select:</option>
                            <?=$image->drawCategories($category)?>
                        </select><br />
                        <input type="hidden" name="findcat" value="1" />
                        <fieldset class="buttons">
                          <button type="submit" class="submit">submit</button>
                        </fieldset>
                    </fieldset>
                  </form>
                <?
                        $category = ($category=='xx')?'':$category;
                        echo $image->drawImageList($thispage,$action,$category);
                } else {
                    $thisImg = new Image();
                    $thisImg->loadImageByGUID($guid);
                ?>
                <form id="treeline" enctype="multipart/form-data" action="<?=$_SERVER['REQUEST_URI']?><?php if ($DEBUG) echo '?debug'?>" method="post">
                    <fieldset>
                        <legend>Edit Image: <?=$thisImg->o_filename?></legend>
                        <input type="hidden" name="action" value="<?=$action?>" />
                        <input type="hidden" name="guid" value="<?=$guid?>" />
                        <p class="instructions">To edit the details of this file, complete the form below and press submit.</p>
                        <label for="title">Title:</label>
                        <input type="text" class="text" name="title" id="title" value="<?=$thisImg->title?>" /><br />      
                        <label for="description">Description:</label>
                        <textarea name="description" id="description"><?=$thisImg->description?></textarea><br />      
                        <label for="tagslist">Tags:</label>
                        <textarea id="tagslist" name="tagslist" class="lessText" rows="2" cols="30"><?=$tags->drawTags($guid,'list',2)?></textarea><br />   
                        <label for="credit">Credit: <em style="font-weight:normal;">optional</em></label>
                        <input type="text" class="text" name="credit" id="credit"value="<?=$thisImg->credit?>" /><br />      
                        <label for="category">Move to category:</label>
                        <select name="category" id="category">
                          <option value="xx">Select:</option>
                          <?=$image->drawCategories($thisImg->category)?>
                        </select><br />      
                        <label for="newcategory"><em style="font-weight: normal;">Or</em> create new category:</label>
                        <input type="text" class="text" name="newcategory" id="newcategory" value="<?=$newcategory?>" /><br />
                        <fieldset class="buttons">
                            <button type="submit" class="submit">submit</button>
                        </fieldset>
                        <p><img src="/silo/images/<?=$thisImg->imgsrc?>" width="<?=$thisImg->width?>" height="<?=$thisImg->height?>" alt="<?=$thisImg->title?>" title="<?=$thisImg->title?>" /></p>
                    </fieldset>
                </form>
                      <?php 
					  } 
                  }
                
                    else if ($action == 'delete') { ?>
                      <?php if (!$guid) { ?>
                        <form id="treeline" enctype="multipart/form-data" action="<?=$_SERVER['REQUEST_URI']?><?php if ($DEBUG) echo '?debug'?>" method="post">
                            <fieldset>
                                <legend>Delete an image</legend>  
                                <input type="hidden" name="action" value="<?=$action?>" />
                                <input type="hidden" name="guid" value="<?=$guid?>" />   
                                <p class="instructions">To delete a file, please select it from the list below below:</p>		
                                <label for="category">Category:</label>
                                <select name="category" id="category">
                                <option value="xx">Select:</option>
                                <?=$image->drawCategories($category)?>
                                </select><br />
                                <input type="hidden" name="findcat" value="1" />
                                <fieldset class="buttons">
                                    <button type="submit" class="submit">submit</button>
                                </fieldset>
                            </fieldset>
                        </form>
                    <?
                            $category = ($category=='xx')?'':$category;
                            echo $image->drawImageList($thispage,$action,$category,siteID);
                    } 
                    else {
                        $thisImg = new Image();
                        $thisImg->loadImageByGUID($guid);
                    ?>
                    <form id="treeline" enctype="multipart/form-data" action="<?=$_SERVER['REQUEST_URI']?><?php if ($DEBUG) echo '?debug'?>" method="post">
                        <fieldset>
                            <legend>Delete image: <?=$thisImg->getTitle()?></legend>
                          <input type="hidden" name="action" value="<?=$action?>" />
                          <input type="hidden" name="guid" value="<?=$guid?>" />
                          <p class="instructions">You are about to delete this image from the image library. <strong>Are you sure?</strong></p>
                          <fieldset class="buttons">
                            <button type="submit" class="submit">Yes, deleteit</button>
                          </fieldset>
                        </fieldset>
                    </form>
                      <?php }	?>
                      <?php } ?>  
                </div>
            </div>
        <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>