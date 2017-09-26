<?

	//print "<!-- page(".print_r($page, 1).") -->\n";
	ini_set("display_errors", 1);
	
	//include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/news.class.php');
	$blogguid = '';
	$blogsGUID = array("55c33a5b0c6e1");
	$eventGUID = array("55c48af15dd20");
	$type = "news";
	if (in_array($page->getGUID(), $blogsGUID)) {
		$type="blogs";
		$blogguid = $page->getGUID();
	}
	else if (in_array($page->getGUID(), $eventGUID)) {
		$type="events";
		$eventguid = $page->getGUID();
	}

	$newsdate = $_GET['y']."-".$_GET['m']."-".$_GET['d'];
	//print "Check for news($newsdate)<br>\n";
	$news = new News($type, "", $newsdate);
	
	$perPage = 5;
	$currentPage = read($_REQUEST,'page',1);
	
	$tags=new Tags();
	// Page specific options
	

	// Panels
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');
	$panels->setMode($mode);
	
	$panellist = array();
	if ($news->isBlogs()) {
		$panellist[] = 'search-blogs';
		$panellist[] = 'blog-calendar';
		//$panellist[] = 'tag-cloud';
	}
	else {
		$panellist[] = "twitter-timeline";
		//$panellist[] = "news-alerts";
	}
	if ($site->id == 18)
	{
		//$panellist[] = "contact-us";
		$panellist[] = "global-innovation-exchange";
		$panellist[] = "get-in-touch";
	}
	foreach ($panellist as $addpanel) {
		$query = "SELECT guid FROM pages WHERE name = '$addpanel' AND template IN (6, 24)";
		//print "$query<br>\n";
		if ($addpanelguid = $db->get_var($query)) {
			//print "Add panel($addpanelguid)<br>\n";
			$panels->panels[] = $addpanelguid;
		}
		//else print "Failed to locate panel($addpanel)<br>\n";
	}

	$comment = new Comment($page->getGUID());

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		//$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
		$redirect = true;
		if ($_POST['post_action']) $action = $_POST['post_action'];
		//print "got action ($action)<br>\n"; //exit();
		if ($action == 'Save changes') {
		
			$page->save();
			$feedback = 'feedback=success&message='.urlencode($page->getLabel("tl_pedit_msg_saved", true));
			
			$author_redirect = "/treeline/pages/?action=edit&".$feedback;
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();

			// Publish this page
			$query = "UPDATE pages SET date_published = NOW(), user_published= ".$_SESSION['treeline_user_id']." WHERE guid='".$page->getGUID()."'";
			if ($db->query($query)) {
				$redirectURL = $author_redirect; 
				$page->releaseLock($_SESSION['treeline_user_id']);			
				if ($redirect) redirect($redirectURL);
			}
			else print "$query<br>\n";
		}
		// Discard changes was pressed
		else if ($action == 'Discard changes') {
			// We have to manually release the page here as we are not saving the page.
			$page->releaseLock($_SESSION['treeline_user_id']);			
			if ($redirect) redirect ('/treeline/pages/?action=edit&feedback=notice&message='.urlencode($page->getLabel("tl_pedit_err_nosave", true)));
		}
	}
		
	
	$pageClass = 'news'; // used for CSS usually
	
	$css = array('news','forms'); // all attached stylesheets
	if($page->style!=NULL && $mode=="edit") $css[] = $page->style;
	$extraCSS = ''; // extra page specific CSS
	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	$pagetitle = ucfirst($page->getTitle());
	
	//print "<!--1  page(".print_r($page, 1).") -->\n";

	ob_start();
	if ($mode=="edit") {
		?>
		<p>This page cannot be edited as it displays a listing of all child pages in news listing format.</p>
		<p>However you will need to Save the page using the button at the top in order for this page to appear on your site.</p>
		<?php
	}
	else {
		echo $news->drawNews($page->getGUID(),$perPage,$currentPage);
	}
	$newsHTML = ob_get_contents();
	ob_end_clean();

	//print "<!-- 2 page(".print_r($page, 1).") -->\n";
	
	if ($site->id != 18)
	{
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
		include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
		include($_SERVER['DOCUMENT_ROOT'].'/includes/html/news.php'); 
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
	}
	else
	{
		include($_SERVER['DOCUMENT_ROOT'].'/includes/html/18/header.inc.php');	
		//include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
		include($_SERVER['DOCUMENT_ROOT'].'/includes/html/news.php'); 
		include($_SERVER['DOCUMENT_ROOT'].'/includes/html/18/footer.inc.php');
	}
	
?>