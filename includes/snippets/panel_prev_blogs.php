<?php 

	// get list of previous blogs starting with most recent
	$query="SELECT if(title is null,'untitled',title) as title, revision_id FROM content WHERE parent='".$page->getGUID()."' AND placeholder='content'";
	if (!$_SESSION['user_logged_in']) $query.=" AND revision_id<1";
	$query.=" ORDER BY revision_id desc";
	//print "$query<br>";
	
	$html=''; $addlink=1;
	if ($results=$db->get_results($query)) {
		foreach($results as $result) {
			if ($result->revision_id==1) $addlink=false;
			$html.='<p>
<a class="arrow" href="'.$page->drawLinkByGUID($page->getGUID()).'?revid='.$result->revision_id.'">'.$result->title.'</a> 
'.(($_SESSION['user_logged_in'] && $result->revision_id<0)?'<a style="padding-left:30px;" href="'.$page->drawLinkByGUID($page->getGUID()).'?action=delete&revid='.$result->revision_id.'">-- delete this blog --</a>':'').'
'.(($_SESSION['user_logged_in'] && $result->revision_id==1)?'<a style="padding-left:30px;" href="'.$page->drawLinkByGUID($page->getGUID()).'?revid='.$result->revision_id.'">-- edit this blog --</a>':'').'
</p>';
		}
	}

	?>
    
<div class="panel panel_2">
    <h3>Previous blogs</h3>
    <?=(($_SESSION['user_logged_in'] && $addlink)?'<p><a class="arrow" href="'.$page->drawLinkByGUID($page->getGUID()).'?action=add">Add new blog</a></p>':"")?>
    <?=$html?>
</div>

