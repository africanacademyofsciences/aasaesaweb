<?php 
include_once ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/gallery.class.php");
ob_start(); 
$gallery = new Gallery('', $gallery_id);
$gallery->linktext = $linktext;
?>


<!-- EMBEDDED SLIDESHOW -->
<script type="text/javascript">
<?php
include_once($_SERVER['DOCUMENT_ROOT']."/behaviour/lytebox.js");
?>
</script>
<style type="text/css">
<?php
include_once($_SERVER['DOCUMENT_ROOT']."/style/lytebox.css");
?>
</style>


<?=$gallery->drawSlideshow()?>
<!-- END EMBEDDED SLIDESHOW -->



<?php
$replace .= ob_get_contents();
ob_end_clean();
?>