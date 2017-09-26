<?php

//ini_set("display_errors", true);
//error_reporting(E_ALL);

$feedback = 'error';
$message = array();

$maxw = 680;

$image = new Image;
$access_id = $memberDetails->access_id;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// check for data
		
	if ($memberDetails->access_id>0) {
		$dest = "mem-".$access_id;
		$destination = $_SERVER['DOCUMENT_ROOT']."/silo/upload/members/".$dest;
		//print "save to($destination)<br>\n";
		$category = 2;
		$title = 'Member - ';
		$description =  'Photograph of ';
		
		$upload = read($_FILES,'image', NULL);
		if ($upload['tmp_name']) {
			if ($upload['type']=="image/jpeg") {
				$err = $image->uploadFromDesktop($upload, $destination, $maxw, "190x0");
				//print_r($message);
				//print "e($err) d($destination)<br>\n";
				if (substr($err,0,strlen($destination))==$destination) {
					$feedback = 'success';
					$message[] = 'You have updated your image. You may need to clear your browsers cache before your new image will display.';
				}
				else $message[]=$err;
			}
			else $message[]="You can only upload JPEG/JPG formatted images to you profile";
		}
		else if ($_POST['delete-photo']==1) {
			if ($photo = $member->getMemberImage($access_id, $memberDetails->img_ext)){
				//$message[]="Need to delete the main photo($photo)";
				$photo_file = $_SERVER['DOCUMENT_ROOT'].$photo;
				if (file_exists($photo_file)) {
					//print "delete it<br>\n";
					if (!unlink($photo_file)) $message[]="Failed to remove your main photograph";
				}
			}
		}
		
		if ($_POST['email2']) {
			if (!is_email($_POST['email2'], true, true)) $message[] = "Alternative email address is not valid";
			else $email2 = $_POST['email2'];
		}
		
		$yearborn = 0;
		if (isset($_POST['yearborn'])) {
			if ($_POST['yearborn']>1600 && $_POST['yearborn']<2300) {
				$yearborn = $_POST['yearborn'];
			}
			else $message[] = "The value entered for the year you were born is not valid";
		}
		
		$countryname = $country_id = '';
		if ($_POST['country']) {
			$countryname = $_POST['country'];
			$query = "SELECT country_id FROM store_countries WHERE title = '$countryname'";
			if ($country_id = $db->get_var($query)) $countryname = '';
		}
		
		// Check if the blog name is unique 
		$blog_name = _generateName($db->escape($_POST['blogtitle']));
		if ($blog_name) {
			$query = "SELECT id FROM member_profile WHERE blog_name='$blog_name' AND id<>".$memberDetails->profile_id;
			if ($db->get_var($query)) {
				$message[]="This blog name[$blog_name] already exists";
				$blog_name = '';
			}
		}
		
		// Update their member record
		$query = "UPDATE members SET 
			country = ".($country_id+0).", countryname='$countryname',
			telephone = '".$db->escape($_POST['telephone'])."',
			organisation = '".$db->escape($_POST['organisation'])."',
			gender = '".$_POST['gender']."' ";
		if ($yearborn) $query .= ", year_born = '".$db->escape($_POST['yearborn'])."' ";
		$query .= "WHERE member_id = ".$member_id;
		
		//$message[] = $query;
		$db->query($query);
		if (!$db->last_error) {
			// Update their profile text and their blog comment status
			$query = "UPDATE member_profile 
				SET 
				".($blog_name?"blog_title = '".$db->escape($_POST['blogtitle'])."', blog_name = '".$blog_name."',":"")."
				blog_comments = ".($_POST['blogcomments']+0).", 
				expertise = '".$db->escape($_POST['expertise'])."',
				profile_text='".$db->escape(censor($_POST['profile']))."' ";
			if ($email2) $query .= ", email2='$email2' ";
			$query .= "WHERE id=".$memberDetails->profile_id;
			//$message[] = $query;
			$db->query($query);
			if ($db->last_error) {
				// print "$query<br>\n";
				// print $db->last_error;
				$message[]="Failed to update your profile data";
			}
		}
		else {
			// print "$query<br>\n";
			//print $db->last_error."<br>\n";
			$message[] = "Failed to update member record";
		}
		
		//$message[] = print_r($memberDetails, true);
	}
	else $message[] = "Failed to located your access ID";
	
	if (!count($message)) {
		$feedback="success";
		$message[]="Your profile has been updated";
	}
	
	// Reload the memberDetails object
	$memberDetails = $member->getById($member_id, "all"); // get details of member
}

echo drawFeedback($feedback, $message);

?>

<form id="updatePassword" action="" method="post" enctype="multipart/form-data" class="std-form">

	<div class="form-group">
    	<legend>Add an image</legend>
        <p class="instructions">Photos must not be bigger than <?=$maxw?> pixels wide.</p>
        <label for="image" class="sr-only">New photo:</label>
        <input type="file" placeholder="My photograph" class="form-control" name="image" id="image" />
	</div>

	<div class="form-group">
    	<label for="f_desc" class="sr-only">Profile</label>
        <textarea class="form-control" placeholder="My profile text" name="profile"><?=$memberDetails->profile?></textarea>
    </div>        
    <?php
	if ($memberDetails->type_name=="Fellow") {
		$country = $memberDetails->countryname?$memberDetails->countryname:$memberDetails->countrytitle;
		$gender = $memberDetails->gender;
		?>
		<div class="form-group">
            <label for="f_country" class="sr-only">Country</label>
            <input class="form-control" placeholder="Country" type="text" id="f_country" name="country" value="<?=$country?>" />
        </div>
		<div class="form-group">
            <label for="f_org" class="sr-only">Organisation</label>
            <input class="form-control" type="text" id="f_org" placeholder="Organisation" name="organisation" value="<?=$memberDetails->organisation?>" />
        </div>
		<div class="form-group">
            <label for="f_field" class="sr-only">Field of expertise</label>
            <input class="form-control" placeholder="Field of expertise" type="text" id="f_field" name="expertise" value="<?=$memberDetails->expertise?>" />
        </div>
		<div class="form-group">
            <label for="f_em2" class="sr-only">Alternative email</label>
            <input class="form-control" placeholder="Alternative email address" type="text" id="f_em2" name="email2" value="<?=$memberDetails->email2?>" />
        </div>
		<div class="form-group">
            <label for="f_fone" class="sr-only">Telephone</label>
            <input class="form-control" placeholder="Telephone number" type="text" id="f_fone" name="telephone" value="<?=$memberDetails->telephone?>" />
        </div>
		<div class="form-group">
            <label for="f_gender" class="sr-only">Gender</label>
            <select class="form-control" id="f_gender" name="gender">
            	<option value="">Select gender</option>
                <option value="M"<?=($gender=="M"?' selected="selected"':"")?>>Male</option>
                <option value="F"<?=($gender=="F"?' selected="selected"':"")?>>Female</option>
            </select>
        </div>
		<div class="form-group">
            <label for="f_year" class="sr-only">Year born</label>
            <input class="form-control" placeholder="Year born" type="text" id="f_year" name="yearborn" value="<?=$memberDetails->year_born?>" />
        </div>
        <?php
		/*
		<div class="form-group">
            <label for="f_" class="sr-only"></label>
            <input class="form-control" placeholder="" type="text" id="f_" name="" value="<?=$memberDetails->?>" />
        </div>
		*/
	}
	else if ($memberDetails->type_name=="Researcher") {
		$country = $memberDetails->countryname?$memberDetails->countryname:$memberDetails->countrytitle;
		$gender = $memberDetails->gender;
		?>
		<div class="form-group">
            <label for="f_country" class="sr-only">Country</label>
            <input class="form-control" placeholder="Country" type="text" id="f_country" name="country" value="<?=$country?>" />
        </div>
		<div class="form-group">
            <label for="f_org" class="sr-only">Organisation</label>
            <input class="form-control" type="text" id="f_org" placeholder="Organisation" name="organisation" value="<?=$memberDetails->organisation?>" />
        </div>
        <!--
		<div class="form-group">
            <label for="f_field" class="sr-only">Field of expertise</label>
            <input class="form-control" placeholder="Field of expertise" type="text" id="f_field" name="expertise" value="<?=$memberDetails->expertise?>" />
        </div>
		<div class="form-group">
            <label for="f_em2" class="sr-only">Alternative email</label>
            <input class="form-control" placeholder="Alternative email address" type="text" id="f_em2" name="email2" value="<?=$memberDetails->email2?>" />
        </div>
		<div class="form-group">
            <label for="f_fone" class="sr-only">Telephone</label>
            <input class="form-control" placeholder="Telephone number" type="text" id="f_fone" name="telephone" value="<?=$memberDetails->telephone?>" />
        </div>
		<div class="form-group">
            <label for="f_gender" class="sr-only">Gender</label>
            <select class="form-control" id="f_gender" name="gender">
            	<option value="">Select gender</option>
                <option value="M"<?=($gender=="M"?' selected="selected"':"")?>>Male</option>
                <option value="F"<?=($gender=="F"?' selected="selected"':"")?>>Female</option>
            </select>
        </div>
		<div class="form-group">
            <label for="f_year" class="sr-only">Year born</label>
            <input class="form-control" placeholder="Year born" type="text" id="f_year" name="yearborn" value="<?=$memberDetails->year_born?>" />
        </div>
		-->

        <?php
		/*
		<div class="form-group">
            <label for="f_" class="sr-only"></label>
            <input class="form-control" placeholder="" type="text" id="f_" name="" value="<?=$memberDetails->?>" />
        </div>
		*/
	}

	else if ($memberDetails->type_name=="Funder") {
		?>
		<div class="form-group">
            <label for="f_org" class="sr-only">Organisation</label>
            <input class="form-control" type="text" id="f_org" placeholder="Organisation" name="organisation" value="<?=$memberDetails->organisation?>" />
        </div>
        <?php
		/*
		<div class="form-group">
            <label for="f_" class="sr-only"></label>
            <input class="form-control" placeholder="" type="text" id="f_" name="" value="<?=$memberDetails->?>" />
        </div>
		*/
	}
	

	
	if ($memberDetails->bloggable) { 
		?>
		<div class="form-group">
            <label for="f_blogtitle">My blog title</label>
            <input class="text" type="text" id="f_blogtitle" name="blogtitle" value="<?=$memberDetails->blogtitle?>" />
        </div>
		<div class="form-group">
            <label for="f_desc">Allow comments</label>
            <input type="checkbox" name="blogcomments" <?=($memberDetails->blogcomments?'checked="checked"':"")?> value="1" />
        </div>
    	<?php
	}
	?>

    <button type="submit" class="btn btn-sm btn-info pull-right">Save</button>

<?php
if ($photo = $member->getMemberImage($access_id)) {
	?>
    <p>Current photo: <input type="checkbox" id="f_delete" name="delete-photo" value="1" /> <label for="f_delete">delete it?</label><br />
        <img src="<?=$photo?>" alt="" />
    </p>
	<?php
}
?>

</form>
