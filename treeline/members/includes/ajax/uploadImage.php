<?php

	unset($feedback);
	
	$originalGUID = $result->image;
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions/images.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/image.class.php");
	$image = new Image;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// check for data
		
	$imgName = $result->firstname.' '.$result->surname;
	$category = 2;
	$title = 'Member - ';
	$description =  'Photograph of ';
	
	$upload = read($_FILES,'image', NULL);
	$feedback = uploadImage($upload,$imgName,$category,$title,$descriptio);
	
	if($upload){

		if($feedback['guid']){
		
			$feedback[0] = 'success';
			$feedback['message'] = 'Image has been uploaded';
			if($updateDB = $member->updateImage($memberId, $feedback['guid'])){ // successful dataabse call
				$image->delete($originalGUID);
				$feedback[0] = 'success';
				$feedback['message'] = 'You have updated your image. <a href="?id='.$memberId.'action=image&amp;refresh=1">Refresh to view this image</a>';
			}
			else{ // tehnical error
				$feedback[0] = 'error';
				$feedback['message'] = 'A technical error has occurred. Please try again in a few moments.';
			}
		}
	}
	else{ // user has missed out a field
		$feedback[0] = 'error';
		$feedback['message'][] = 'You have not included an image';
	}
}
	echo drawFeedback($feedback[0],$feedback['message']);
?>
<form id="updatePassword" action="" method="post" enctype="multipart/form-data">
	<fieldset>
    	<legend>Add an image</legend>
        <p class="instructions">Logos can be JPGs, GIFs or PNGs and must not be bigger than 800 pixels wide by 800 pixels high.</p>
        <label for="image">New photo:</label>
        <input type="file" name="image" id="image" />
        <p class="instructions">Here is your current photo: <br />
            <img src="<?php echo $member->getMemberImage($memberId, $result->image,'thumbnail'); ?>" alt="" />
        </p>
        <button type="submit" class="submit">Upload</button>
    </fieldset>
</form>
<p><a href="/treeline/members/">View all members</a> or <a href="?id=<?=$memberId?>&amp;action=edit">Edit <?=$result->firstname?>'s  details</a></p>
