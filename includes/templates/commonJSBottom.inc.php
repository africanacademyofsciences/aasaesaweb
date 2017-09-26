<?php

if (is_array($jsBottom)){
	foreach($jsBottom as $src){
		echo "\t".'<script type="text/javascript" src="/behaviour/'.$src.'.js"></script>'."\n";
	}
}


if ($USEFLIR) {
	?>
	<script type="text/javascript">
	FLIR.init();
	//FLIR.replace( ['h1.neue','a.neue'], new FLIRStyle( { realFontHeight:true } ) );
	FLIR.replace( 'p.shadow', new FLIRStyle( { realFontHeight:true } ) );
	FLIR.replace( 'p.shadowblack', new FLIRStyle({mode:'wrap', realFontHeight:true }) );
	FLIR.replace( '*.flirneue', new FLIRStyle( { realFontHeight:true } ) );
	//FLIR.replace( ['li.flirneue','a.flirneue','h2.flirneue'], new FLIRStyle( { realFontHeight:true } ) );
	</script>
	<?php
}



if($extraJSbottom){
	?>
	<script type="text/javascript">
	<?=$extraJSbottom?>
	</script>
    <?php
}

?>


<?php
/*
<script type="text/javascript">
<!-- Script to avoid space appearing below the footer -- only if you use #footer or recode
  
  $(document).ready(function() {
   
   var docHeight = $(window).height();
   var footerHeight = $('#footer').height();
   var footerTop = $('#footer').position().top + footerHeight;
   
   if (footerTop < docHeight) {
    $('#footer').css('margin-top', 10 + (docHeight - footerTop) + 'px');
   }
  });
  
// -->
</script>

*/
?>

