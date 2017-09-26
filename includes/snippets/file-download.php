<?php
	$defdesc = "This type of link can be used to draw attention to a specific download. We think these look better with icons, rather than PDF images.";

	$query = "SELECT * FROM files WHERE guid ='$fileGUID' LIMIT 1";
	if ($row = $db->get_row($query)) {
		print "<!-- Got row(".print_r($row, 1).") -->\n";
		$replace = '
		<ul class="filter-list block">
			<li class="default">
				<a href="#" class="filter-link">
					<div class="title">
						<i class="ion-ios-download-outline"></i>
						<h6>'.$row->title.'</h6>
					</div>
					<div class="meta">
						<p><i class="ion-ios-cloud-download-outline"></i> '.$row->name.'.'.$row->extension.'</p>
						<p><i class="ion-ios-information-outline"></i> '.formatFilesize($row->size).'</p>
					</div>
					<div class="abstract">
						'.($row->description?$row->description:$defdesc).'
					</div>
				</a>
			</li>
		</ul>
		';
	}
	//"<div> Show file block for file($fileGUID)</div>\n";

?>