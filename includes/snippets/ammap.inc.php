<?php
ob_start();
?>

<style type="text/css">
<?php
echo file_get_contents($_SERVER['DOCUMENT_ROOT']."/ammap/map.css");
?>
</style>

<script type="text/javascript" src="/ammap/swfobject.js"></script>
<div id="flashcontent">
    <strong>You need to upgrade your Flash Player to view maps</strong>
</div>
<script type="text/javascript">
	// <![CDATA[
	var so = new SWFObject("/ammap/ammap.swf", "ammap", "485", "610", "8", "#ffffff");
	so.addVariable("path", "/ammap/");
	so.addVariable("settings_file", escape("/ammap/ammap_settings.xml"));
	so.addVariable("data_file", escape("/behaviour/ajax/ammap_data.php?<?=rand(1,9999999)?>"));
	so.write("flashcontent");
	// ]]>
</script>


<?php
$replace = ob_get_contents();
ob_end_clean();

?>