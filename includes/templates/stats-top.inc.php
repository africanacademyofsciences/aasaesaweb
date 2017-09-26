<!-- Page top stats (<?=$mode?>) pm(<?=$page->getMode()?>)-->
<?php
if ($page->getMode()=="view" || $mode=="view") {

	if($site->google_js){
		echo $site->google_js;
	}
	
}	
?>
<!-- End top stats -->
