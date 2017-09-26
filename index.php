<?php
	
	// HOME PAGE CONTENT	
	
	// Second news story
	$news1 = new HTMLPlaceholder();
	$news1->load($pageGUID, 'news1');
	$news1->setMode($page->getMode());

	// third news story
	$news2 = new HTMLPlaceholder();
	$news2->load($pageGUID, 'news2');
	$news2->setMode($page->getMode());

	// third news story
	$news3 = new HTMLPlaceholder();
	$news3->load($pageGUID, 'news3');
	$news3->setMode($page->getMode());
	
	if ($site->id == 18)
	{
		$contentBox1 = new HTMLPlaceholder();
		$contentBox1->load($pageGUID, 'contentBox1');
		$contentBox1->setMode($page->getMode());
		
		$contentBox2 = new HTMLPlaceholder();
		$contentBox2->load($pageGUID, 'contentbox2');
		$contentBox2->setMode($page->getMode());
		
		$contentBox3 = new HTMLPlaceholder();
		$contentBox3->load($pageGUID, 'contentbox3');
		$contentBox3->setMode($page->getMode());
		
		$contentBox4 = new HTMLPlaceholder();
		$contentBox4->load($pageGUID, 'contentbox4');
		$contentBox4->setMode($page->getMode());
		
		$contentBox5 = new HTMLPlaceholder();
		$contentBox5->load($pageGUID, 'contentbox5');
		$contentBox5->setMode($page->getMode());
		
		$contentBox6 = new HTMLPlaceholder();
		$contentBox6->load($pageGUID, 'contentbox6');
		$contentBox6->setMode($page->getMode());
		
		$tagline = new HTMLPlaceholder();
		$tagline->load($pageGUID, 'tagline');
		$tagline->setMode($page->getMode());
	}

	// TAGS - all pages need a tags obejct
	$tags = new Tags($site->id, 1);
	
	// Create news item, lots of news to see here.
	//$news = new News();
	
	// Process and actions sent to the page	
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		
		if( $mode=='edit' ){
		
			$action = read($_POST,'treeline','');
			if ($_POST['post_action']) $action = $_POST['post_action'];
			
			// SAVE CHANGES
			if ($action == 'Save changes') {

				// Save all content blocks
				$news1->save();
				$news2->save();
				$news3->save();
				
				if ($site->id == 18)
				{
					$contentBox1->save();
					$contentBox2->save();
					$contentBox3->save();
					$contentBox4->save();
					$contentBox5->save();
					$contentBox6->save();
					$tagline->save();
				}
				if (is_object($footer)) $footer->save();
				if (is_object($header_img)) $header_img->save();
				
				// Save the page data
				$page->save(true);
				
				// Generate nice pretty success message
				$feedback = 'feedback=success&message='.urlencode($page->getLabel("tl_pedit_msg_saved", true));
		
				// Can this user publish pages, if so redirect to the publish option
				if($_SESSION['treeline_user_group']=='Superuser' || $_SESSION['treeline_user_group']=='Publisher') { 
					redirect('/treeline/pages/?action=saved&guid='.$page->getGUID().'&'.$feedback); 
				} 
				// otherwise take the user back to the edit pages page
				else {
					redirect('/treeline/pages/?action=edit&'.$feedback); 
				}
			}
			
			// Posted in preview mode
			else if ($action=="Preview") {
	
				// Dont actually need to save anything 
				$mode="preview";
				$page->setMode($mode);
				$news1->setMode($mode);
				$news2->setMode($mode);
				$news3->setMode($mode);
				
				if ($site->id == 18)
				{
					$contentBox1->setMode($mode);
					$contentBox2->setMode($mode);
					$contentBox3->setMode($mode);
					$contentBox4->setMode($mode);
					$contentBox5->setMode($mode);
					$contentBox6->setMode($mode);
					
					$tagline->setMode($mode);
				}

				$showPreviewMsg=true;
			}
			
			// DISCARD CHANGES
			else if ($action == 'Discard changes') {

				// Release our lock on the file
				// We have to manually release the page here as we are not saving the page.
				$page->releaseLock($_SESSION['treeline_user_id']);			
				redirect ('/treeline/pages/?action=edit&feedback=notice&message='.urlencode($page->getLabel("tl_pedit_err_nosave", true)));
			}
		}
		else {

			// 	Currently there is no other data that can be posted to this page
		
		}
	}

	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");
	
	
	// Page specific options
	$pageClass = 'home';
	$disablePageStyle=true;
	
	$css = array('home'); // all attached stylesheets
	if ($page->getMode() == 'edit') {
		$extraCSS .= ' ';
	}
	
	$extraCSS .= '';

	$js = array(); // all atatched JS behaviours
	if($page->getMode() != 'edit'){
		;
	}
	
	$extraJS = ' '; // etxra page specific JS behaviours
	

	$extraJSbottom = '';
	if ($mode=="edit") {
		
		
		if ($site->id == 18)
		{
			$extraJSbottom .= '
			CKEDITOR.replace(\'treeline_contentBox1\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_contentbox2\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_contentbox3\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_contentbox4\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_contentbox5\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_tagline\', { toolbar : \'contentStandard\', height: \'200px\' });
			';
		}
		else
		{
			$extraJSbottom .= '
			CKEDITOR.replace(\'treeline_news1\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_news2\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_news3\', { toolbar : \'contentStandard\', height: \'200px\' });
			CKEDITOR.replace(\'treeline_footer\', { toolbar : \'contentStandard\', height: \'60px\' });
		';
		}
		
	}
	
	$global_meta_desc = $site->properties['description'];
	$global_meta_keyw = $site->properties['keywords'];
	
	
	$news = new News("news");// Create news item, lots of news to see here.
	$blogs = new News("blogs");
	$events = new News("events");

	$news->drawLatestPanel(2);
	$blogs->drawLatestPanel(2);
	$events->drawLatestPanel(2);
	$latest['news'] = $news->items;
	$latest['blogs'] = $blogs->items;
	$latest['events'] = $events->items;
	
	$usepubfiles = false;
	if ($usepubfiles) {
		$query = "SELECT f.title AS title, f.guid AS guid,
			f.description as summary, 
			DATE_FORMAT(f.date_created, '%d %m %Y') AS created,
			u.full_name AS author
			FROM files f 
			INNER JOIN filecategories fc ON fc.id = f.category
			INNER JOIN users u ON u.id = f.user_created
			WHERE f.site_id=". $site->id." AND f.resource=1 AND fc.id IN ($publicationcategories) 
			ORDER BY f.date_created DESC
			LIMIT 2";
		//print "<!-- $query -->\n";
		if ($results = $db->get_results($query)) {
			$i = 0;
			foreach($results as $result) {
				$latest['pubs'][$i]['title'] = $result->title;
				$latest['pubs'][$i]['link'] = "/download/".$result->guid."/";
				$latest['pubs'][$i]['author'] = $result->author;
				$latest['pubs'][$i]['date'] = $result->created;
				$latest['pubs'][$i]['summary'] = $result->summary;
				$latest['pubs'][$i]['location'] = '';
				//$latest['pubs'][$i]['
				$i++;
			}
		}
	}
	else {
		$pubs = new News("publications");
		$pubs->drawLatestPanel(2);
		$latest['pubs'] = $pubs->items;
	}
	
	//print "<!-- latest(".print_r($latest, 1).") -->\n";

	$news1HTML = $news1->draw();
	$news2HTML = $news2->draw();
	
	

	//include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
	//print '<!--'.$site->id.'-->';
	if ($site->id == 18)
	{
		include($_SERVER['DOCUMENT_ROOT'].'/includes/html/18/home-content.php');
	}
	else
	{
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
		include($_SERVER['DOCUMENT_ROOT'].'/includes/html/index2.php');
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
	}
	
	

?>