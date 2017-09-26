<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<p>test</p>

<script language="javascript">
var CKEDITOR = window.parent.CKEDITOR;
var okListener = function(event){
	alert("ok pressed");
	this._.editor.insertHtml('<?php echo 'content to send in the CKEditor window';?>');
	CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
};
CKEDITOR.dialog.getCurrent().on("ok", okListener);
</script>
</html>


