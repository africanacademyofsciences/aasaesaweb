<?php
//ini_set("display_errors", 1);
/*
print "<!-- vh(
$valid_html
) -->\n";
*/
if(preg_match_all('/<p><a href=(.*?)>Read more<\/a><\/p>(.*?)<p><a href=(.*?)>Read less<\/a><\/p>/s', $valid_html, $reg, PREG_SET_ORDER)) {

	//print "<!-- match in: $valid_html -->\n";
	//print "<!-- read more found(".print_r($reg, 1).") -->\n";
	ob_start();
	?>
<style type="text/css">
	div.concertina {
		float: left;
		float: none;
		clear: left;
		overflow:hidden;
	}
</style>    
<script type="text/javascript">
	function readmore(bloc) {
		var block = document.getElementById("concertina"+bloc+"-inner");
		//alert("open block("+bloc+") b("+block+") should oh("+block.offsetHeight+") h("+block.style.height+"):");
		document.getElementById("readmore"+bloc).style.display="none";
		$('#concertina'+bloc).animate({height: block.offsetHeight}, "slow");
	}
	function readless(bloc) {
		var block = document.getElementById("concertina"+bloc+"-inner");
		//alert("open block("+bloc+") b("+block+") should oh("+block.offsetHeight+") h("+block.style.height+"):");
		document.getElementById("readmore"+bloc).style.display="block";
		$('#concertina'+bloc).animate({height: 0}, "slow");
	}	
</script>
    <?php
	$xcssjs = ob_get_contents();
	ob_end_clean();
		
	$i=0;
	//foreach ($reg as $concertinablock) {
		//print "<!-- Found cb(".print_r($concertinablock, 1).") -->\n";
		foreach ($reg as $concertina) {
			//print "<!-- Found c(".print_r($concertina, 1).") i($i) -->\n";
			$newHTML = '
<div class="concertina" id="concertina'.$i.'" style="height:0px;">
	<div class="concertina-inner" id="concertina'.$i.'-inner" style="padding-bottom: 10px;">
		<div id="concertina-content'.$i.'">
			'.$concertina[2].'
		</div>
		<p><a id="readless'.$i.'" href="javascript:readless('.$i.')">Read less</a></p>
	</div>
</div>
<p><a id="readmore'.$i.'" href="javascript:readmore('.$i.');">Read more</a></p>
';
			$oldHTML = $concertina[0];
			//$oldHTML = '<p><a href='.$concertina[2].'>Read more</a></p>'.$concertina[3].'<p><a href='.$concertina[4].'>Read less</a></p>';
			//print "<!-- replace: \n $oldHTML \n with \n $newHTML\n -->\n";
			$i++;
			$valid_html = str_replace($oldHTML, $newHTML, $valid_html);
		}
	//}
	$valid_html = $xcssjs.$valid_html;

}
//else print "---------<br>\n NO MATCH<br>\n----------<br>\n";


?>