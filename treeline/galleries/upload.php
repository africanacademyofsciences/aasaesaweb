<?php
$SWFPath = "testing/dev/SWFUpload v2.2.0.1 Core";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script type="text/javascript" src="http://<?php echo $SWFPath; ?>/swfupload.js"></script> 
<script type="text/javascript" src="http://<?php echo $SWFPath; ?>/swfsetup.js"></script>
<style type="text/css">
#uploader {
}
	#uploader div#upload-info {
		width: 200px;
		height: 150px;
		border: 1px solid #000;
	}
	#uploader div#upload-buttons {
		margin: 0;
		padding: 0;
	}
		#uploader div#upload-buttons object {
			float: left;
		}
		#uploader div#upload-buttons .uploader-button {
			height: 24px;
			float: left;
			width: 100px;
			margin: 0;
		}
</style>

</head>

<body>

<h1>Upload multiple images</h1>

<!-- Multiple uploader form -->
<div id="uploader">
    <div id="upload-info"></div>
    <div id="upload-buttons">
	    <div id="upload-button" class="uploader-button"></div>
		<button onclick="javascript:swfCancelUpload();" id="upload-cancel" class="uploader-button">Cancel</button>
    </div>
</div>
<!-- // End of multiple uploader html -->

</body>
</html>
