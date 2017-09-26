<?php
$uploader = $_SESSION['uploader'];
?>
<script type="text/javascript" src="/treeline/includes/mass_uploader/swfupload.js"></script>
<script type="text/javascript" src="/treeline/includes/mass_uploader/swfupload.graceful_degradation.js"></script>
<script type="text/javascript" src="/treeline/includes/mass_uploader/swfupload.queue.js"></script>
<script type="text/javascript" src="/treeline/includes/mass_uploader/handlers.js"></script>
<script type="text/javascript">
var upload1;
$(document).ready(function()
{
	upload1 = new SWFUpload
	({
		// Backend Settings
		upload_url: "/treeline/includes/mass_uploader/upload.php",
		post_params:
		{
			"PHPSESSID": "<?php echo session_id(); ?>"<?php
			if ($uploader['post_params']) echo ",\n";
			foreach ($uploader['post_params'] as $k => $v) $p .= "\t\t\t".'"'.$k.'": "'.$v.'", '."\n";
			echo rtrim(rtrim($p), ",")."\n";
		?>
		},
		// File Upload Settings
		file_size_limit: "<?=$uploader['file_size_limit']?>",
		file_types: "<?php
			foreach ($uploader['extension_whitelist'] as $ext) $e .= '*.'.$ext.';';
			echo rtrim($e,';');
		?>",
		file_upload_limit: "<?=$uploader['file_upload_limit']?>",
		file_queue_limit: "0",
		// Event Handler Settings (all my handlers are in the Handler.js file)
		file_dialog_start_handler: fileDialogStart,
		file_queued_handler: fileQueued,
		file_queue_error_handler: fileQueueError,
		file_dialog_complete_handler: fileDialogComplete,
		upload_start_handler: uploadStart,
		upload_progress_handler: uploadProgress,
		upload_error_handler: uploadError,
		upload_success_handler: uploadSuccess,
		upload_complete_handler: uploadComplete,
		// Flash Settings
		flash_url: "/treeline/includes/mass_uploader/swfupload_f9.swf",
		swfupload_element_id: "flashUI1",
		degraded_element_id: "degradedUI1",
		custom_settings:
		{
			progressTarget: "fsUploadProgress1",
			cancelButtonId : "btnCancel1"
		},
		debug: false
	});	
});
</script>

<?php
$page_html='
<form action="'.$uploader['form_action'].'" method="post">
	<fieldset>
		<div id="flashUI1" style="display: none;">
			<fieldset class="flash" id="fsUploadProgress1">
			</fieldset>
			<div>
				<input id="btnUpload1" type="button" class="submit" value="Browse" onclick="upload1.selectFiles()" style="border:1px solid #cb417f; background: #cb417f url(/treeline/img/layout/button-submit-bg.gif); color:#fff; font-weight:bold; font-style:normal; width:100px; margin-right:1em" />
				<input id="btnCancel1" type="button" value="Cancel uploads" onclick="cancelQueue(upload1);" style="border:1px solid #ccc; background: #acbdd3 url(/treeline/img/layout/button-cancel-bg.gif); color:#333; font-weight:bold; font-style:normal; width:120px" disabled="disabled" />
				
			</div>
		</div>
		<div id="degradedUI1">
			<fieldset>
				<input type="file" name="anyfile1" /><br />
			</fieldset>
			<div>
				<input type="submit" value="Submit" />
			</div>
		</div>
	</fieldset>
	<fieldset class="buttons" style="margin-top:10px;">
		<p>Please wait until all uploads have completed before pressing the finished button</p>
		<input type="submit" class="submit" name="uploader" value="finished" />
	</fieldset>
</form>
';
echo treelineBox($page_html, "Select images to upload", "blue");
?>
