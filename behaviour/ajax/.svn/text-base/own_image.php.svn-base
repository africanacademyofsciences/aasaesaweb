<?
	session_start();
	
	$siteID = $_SESSION['treeline_user_site_id'];
	
	$blog_max_width = 711;
	$forum_max_width = 482;
	$max_width = $forum_max_width;	// Default to this size until I find a way to tell where we are.
	

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");
	// Set the debugging on
	$DEBUG = (read($_GET,'debug',false) !== false)?true:false;	
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");
	
		
	

	// Now, should we rewrite this as an imagepicker class? It's only ever used here, so it seems unnecessary...

	// We're either listing all images, or selecting one [at the correct size] to insert:
	$action = read($_REQUEST,'action','list');

	$category = read($_REQUEST,'category','xx');	
	$subcategory = read($_REQUEST,'subcategory','xx');	
	$search = read($_REQUEST,'search','');
	$source = read($_REQUEST,'source',0);

	if ($_SERVER['REQUEST_METHOD']=="POST") {
		//print "upload image ";
		//print_r($_FILES);	
		
		if ($_FILES['file']['name']) {
			$ext='';
			//print_r($_FILES['file']);
			// Check image is less than 650px wide.
			switch($_FILES['file']['type']) {
				case "image/jpeg": $ext="jpg"; break;
				case "image/pjpeg": $ext="jpg"; break;
				case "image/gif": $ext="gif"; break;
				case "image/png": $ext="png"; break;
				default : 
					$message[]="Invalid image type(".$_FILES['file']['type']."). Please save the file as a jpeg, gif or png file and try again";
					break;				
			}
			if ($ext) {
				$sz=getimagesize($_FILES['file']['tmp_name']);
				if ($sz[0]>$max_width) $message[]="Your image is too wide for the page. Please ensure your image is less than ".$max_width." pixels wide";
				else {
					$newfile=$_SESSION['user_logged_in']."_".time().".jpg";
					//print "got ext($ext) upload to ($newfile)<br>";
					if (move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/silo/images/blogs/".$newfile)) {
						$filename="/silo/images/blogs/".$newfile;
						$title=$_POST['title'];
					}
					else $message[]="Unable to copy file";
				}
			}
		}
	}


	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="robots" content="noindex,nofollow" />
<title>Upload an image to you page</title>
<style type="text/css">
@import url('/treeline/style/global.css');
@import url('/treeline/style/imagePicker.css');
</style>
<link href="./css/new_image.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce_popup.js"></script>
<!-- <script type="text/javascript" src="js/dialog.js"></script> -->
<script type="text/javascript">

function resize() 
{
	// Dont do anything?
}


<?php
include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/tiny_mc3/jscripts/tiny_mce/plugins/own_image/js/dialog.js");
/*
<body id="treeline" class="imagePicker" style="margin: 0px" <?php if ($action == 'select') { ?>onload="tinyMCEPopup.executeOnLoad('init();'); resize()"<?php }else{ ?>onload="resize()"<?php } ?>>
*/
?>
</script>
</head>
<body id="treeline" class="imagePicker" style="margin: 0px" onload="resize()">


<?php

// Just need to someval value to the image to insert here.


// Later we can look into doing something with the custom arg?


?>

<form method="post" id="imagepicker" action="#" enctype="multipart/form-data" >
	<fieldset>
	<input type="hidden" name="action" value="<?=$action?>" />
	<!-- <p>Selected text: <input id="someval" name="someval" type="text" class="text" /></p> -->


	<div id="structure">
		
        <div id="inside_structure">
			<img src="/treeline/img/imagepicker/images.gif" alt="Images" style="margin: 0px 34px 0px 10px; float: left" />
			<?php if ($action == 'list') { ?>
			<div style="float: left">
				<div class="field">
					<label for="f_file">Image file</label>
                    <input type="file" name="file" id="f_file" />
				</div>
				<div class="field">
					<label for="f_title">Image title</label>
                    <input type="text" name="title" id="f_title" />
				</div>
				<div class="field">
					<input type="submit" class="button" name="submit" value="Upload file" />
				</div>
			</div>	
			<?php } ?>
		</div>

		<div id="content">

           	<div style="margin: 20px auto; float:left; width: 650px; margin-left:40px;">
			<?php
            if (count($message)) {
            	foreach($message as $msg_item) {
					$err_msg.='<p>'.$msg_item.'</p>';
                }
				echo $err_msg;
				@mail("phil.redclift@ichameleon.com", "pp image upload filed", $err_msg);
            }
			?>
            
            <?php if ($filename) { ?>
                <input type="hidden" name="page" value="<?=$page?>" />
                
                <!-- TinyMCE fields: -->
                <input type="hidden" name="filename" value="<?=$filename?>" />
                <input type="hidden" name="alttitle" value="<?=$title?>" />
                <!-- end of TinyMCE fields -->
	                <p align="center">Upload image <strong><?=$title?></strong></p>
    	            <p align="center"><img src="<?=$filename?>" alt="<?=$title?>" /></p>
                
            
                    <div class="mceActionPanel field">
                        <div style="float: left">
                            <input type="button" id="button_insert" class="button" name="insert" value="{#insert}" onclick="OwnImageDialog.insert();" />
                        </div>
                
                        <div style="float: right">
                            <input type="button" id="button_cancel" class="button" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
                        </div>
                    </div>
    
        	<?php } else { ?>
            
            	<p>Please upload an image in jpeg, gif or png format up to <?=$max_width?> pixels in width.</p>
                
            <?php } ?>
            </div>
        </div>
	</div>
</fieldset>    


</form>

</body>
</html>
