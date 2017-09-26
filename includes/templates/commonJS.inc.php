
<?php
if (is_array($js)){
	foreach($js as $src){
		echo "\t".'<script type="text/javascript" src="/behaviour/'.$src.'.js"></script>'."\n";
	}
}

if($extraJS){
	echo '<script type="text/javascript">'."\n";
	echo $extraJS."\n";
	echo '</script>'."\n";
}

if ($mode=="edit" || $blogmode=="blog") {
	?>
	<script type="text/javascript" src="/treeline/includes/ckeditor/ckeditor.js"></script>
	<!-- <script type="text/javascript" src="/treeline/includes/ckeditor447/ckeditor.js"></script> -->
	<script type="text/javascript">
	function showOkButton(show) {
		var display = "none";
		if (show==1) display = "";
		/*
		if (document.getElementById("cke_66_uiElement")) {
			document.getElementById("cke_66_uiElement").style.display=display;
		}
		*/
		var f = document.getElementsByClassName("cke_dialog_ui_button cke_dialog_ui_button_ok");
		for(var i = 0; i < f.length; i++){
			var s = f[i].getElementsByTagName("span");
			if (s.length==1) {
				//alert ("hide("+display+") the button");
				s[0].style.display=display;
			}
			//alert ("Got "+s.length+" spans");
			//for (var j = 0; j<s.length; j++) {
			//}
		}
		//alert("f("+f+") Show("+show+") display("+display+")");
	}
	</script>
	<script>CKEDITOR.dtd.$removeEmpty['span'] = false;</script>
    
    <?php
}

?>

<script type="text/javascript">
	function translate(l) {
		document.cookie="googtrans="+l;		
		location.reload();
	}
</script>
