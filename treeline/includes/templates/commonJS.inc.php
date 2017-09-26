
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

<script type="text/javascript" src="/treeline/behaviour/jquery.js"></script>
<script type="text/javascript" src="/behaviour/jquery.corner.js"></script>
<script type="text/javascript" src="/treeline/behaviour/vjustify.js"></script>
<script type="text/javascript" src="/treeline/behaviour/usableForms.js"></script>
<script type="text/javascript" src="/treeline/behaviour/clickableAreas.js"></script>
<script type="text/javascript" src="/treeline/behaviour/helpPopup.js"></script>
<script type="text/javascript" src="/treeline/behaviour/hideFeedback.js"></script>
<script type="text/javascript" src="/treeline/behaviour/thickbox/thickbox.js"></script>


<?php
		// common JS
		if (is_array($js)){
			foreach($js as $src){
				echo "\t".'<script type="text/javascript" src="/treeline/behaviour/'.$src.'.js"></script>'."\n";
			}
		}

        if($extraJS){
            echo '<script type="text/javascript">'."\n";
            echo $extraJS."\n";
            echo '</script>'."\n";
        }
?>

<script type="text/javascript">

function setAction(a) {
	var f = document.getElementById('treeline');
	if (f) {
		//alert("set post act("+a+")");
		f.post_action.value=a;
		f.submit();
		return true;		
	}
	return false;
}

$(window).load(function()
{
	// -------------------- Keep large images within the design --------------------
	
	$("div.rounded").each(function()
	{	
		$(this).corner("7px");
	});
	$("h2.rounded").each(function()
	{	
		$(this).corner("8px");
	});
	
	<?php if ($extraOnloadJS) echo $extraOnloadJS; ?>

});
</script>
