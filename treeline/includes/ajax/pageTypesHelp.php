<?php

	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/ezSQL.class.php');
	
	// get data
	$query = "SELECT * FROM pages_templates WHERE user_selectable = 1";
	
	$results = $db->get_results($query);

?>
<h3>Page types explained</h3>
<p>Treeline has several page types</p>
<?php if($results) { // Query has worked and results are in ?>
<dl>
	<?php 
	foreach($results as $result) {  // loop through results
		if($result->template_description){ // only show reuslts with an actual description
	?>
	<dt><?php echo $result->template_title; ?></dt>
    <dd><?php echo $result->template_description; ?></dd>
    <?php 
		} 
	} 
	?>
</dl>
<?php } ?>