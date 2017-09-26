<style type="text/css" media="all">
<?php

if ($mode=="preview") $css[] = "preview";

if (file_exists($_SERVER['DOCUMENT_ROOT']."/style/microsite/scheme".$site->id.".css") && !$_SESSION['palate']) $css[] = "microsite/scheme".$site->id;;

// Temp disable the crusher to allow development
$crushCSS = false;
if ($crushCSS) {
	echo '@import url("/treeline/includes/cssCrusher.php?type=css&params[]=';
	echo 'global,panel,';
	if(is_array($css)){
		echo join(',',$css); /* turn $css array into CSV */
	}
	echo '");'."\n";
}
else {
    echo '
    @import url("/style/global.css");
    @import url("/style/panel.css");
';
	
	foreach ($css as $file) {
		echo '    @import url("/style/'.$file.'.css");'."\n";
	}
}


if($extraCSS){
	echo $extraCSS."\n";
}

?>		
</style>


<?php
/* Style Switcher (alternate stylesheets) */
if($mode == 'edit'){ 
        echo '<link rel="stylesheet" type="text/css" href="/treeline/style/thickbox.css" media="all" />'."\n";
        $currentStyle = ($_POST['style']) ? $_POST['style'] : '';
        echo $page->drawStyleHeadLinks($currentStyle); 
} 
?>

