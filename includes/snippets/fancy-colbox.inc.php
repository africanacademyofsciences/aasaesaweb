<?php

	ob_start();


	$blockHTML = html_entity_decode($row->code);
?>

<?=$blockHTML?>
	
<?php
	$replace = ob_get_contents();
	ob_end_clean();
?>