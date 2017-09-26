<?php
	global $db;
	
	//Find all fellow pages
	$query ="SELECT name, title FROM pages WHERE title LIKE'Fellows from %' AND guid !='" .$page->getGUID(). "'";
	$results = $db->get_results($query);
	
	//find the current page
?>


<div class="panel panel-default">
    <div class="panel-heading">
    Related Countries
    </div>
    <div class="panel-body">
	Fellows from other countries:
	<ul style="list-style-type: none; padding 0px;">
		<?php
		foreach ($results as $result)
		{
			$splitTitle = explode(" ", $result->title);
			
			$title = $splitTitle[2];
			
			$link = '<li>';
			$link .= '<a href="http://aas.treelinesoftware.com/aas/en/programmes/' .$result->name. '">';
			$link .= $title. '</a>';
			$link .= '</li>';
			
			print $link;
		}
		?>
	</ul>
    </div>
</div>