<style type="text/css" media="all">
	<?php
		/*
		if (is_array($css)){
			foreach($css as $import){
				echo "\t".'@import url("/style/'.$import.'.css");'."\n";
			}
		} 
		else{
			echo "\t".'@import url("/style/'.$css[0].'.css");'."\n";
		}
		*/
		

if (0) {
		echo "\t".'@import url("/treeline/includes/cssCrusher.php?type=css&admin=true&params[]=';
		echo 'global,thickbox,';
		echo join(',',$css);  /* turn $css array into CSV */
		echo '");'."\n";
}else {
		echo '
@import url("/treeline/style/global.css");
@import url("/treeline/style/thickbox.css"); 
';		
		foreach ($css as $cssfile) {
			echo '@import url("/treeline/style/'.$cssfile.'.css");';
		}
}

		
		
		if($extraCSS){
			echo $extraCSS."\n";
		}

	?>
</style>
<link rel="stylesheet" media="print" type="text/css" href="/treeline/style/print.css" />
<!--<link rel="stylesheet" type="text/css" href="/treeline/style/thickbox.css" />-->
<?php //Internet Explorer Specific CSS ?>
<!--[if IE 7]><link rel="stylesheet" type="text/css" href="/treeline/style/ie7.css" media="all"><![endif]-->
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="/treeline/style/ie.css" media="all"><![endif]-->
