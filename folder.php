<?php

	$page = new page();
	$page->loadByGUID($results['guid']);
	$page->setMode($mode);

	$this_page_guid = $page->getGUID();
	
	$i = 0; // counter
	$results=0;


	if($results && $i > 0){
		// Page specific options
	
		$pageClass = 'landingpage style'.$results['style']; // used for CSS usually
		
		$css = array('folder'.$results['style']); // all attached stylesheets
		$extraCSS = ''; // extra page specific CSS
		
		if ($results['style']==2) {
			$extraCSS .= getBackgroundImage('div#banner',$results['banner_image']);
			if ($_COOKIE['graphics_mode']=="low") {
				$extraCSS .= 'div#banner {  background:#FFF; color:#000; }';
			}
		}
		//$extraCSS .= ($results['style'] == 2) ? getBackgroundImage('div#banner',$results['banner_image']) : '';
		
		$js = array('vjustify','jquery.newsticker.pack','donate_ticker','clickableAreas', 'flash','floatImages'); // all atatched JS behaviours

		$extraJS = ''; // etxra page specific  JS behaviours
		
		$noMenu = true;
		// header
		$donate_button=1;
		//$css = array(''); // all attached stylesheets
		$meta_description = strip_tags($results['content']);
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
		?>
        <h1><?=$pageTitle?></h1>
       <div id="primarycontent">
        <?php		
		$html = validateContent($results['content']);
		
		if($results['style'] == 1){
			if($results['content']){
				echo $html."\n";			
			}
		}
		else if($results['style'] == 2){
			if($results['content']){
				echo '<div id="banner">'."\n";
				echo $html."\n";	
				echo '</div>'."\n";		
			}
		}
		

		// sections
		
		if(!isset($_GET['showall'])){
			$i = 0;
			foreach($pages as $item){
				if($item['page_guid'] && $item['sort_order']){
					$content = ($item['content']) ? $item['content'] : $item['meta_description'];
					$h3_class = (($i > 1 && $results['style'] == 1) || $results['style'] == 2 || $pageGUID = '46dfcd0c5b8db') ? ' class="column_h3"': '';
					$html = validateContent($content);
					$panels[$i] = '<div class="panel style'.$item['style'].' column clickable">
					 <h3'.$h3_class.'><a href="'.$page->drawLinkByGUID($item['page_guid']).'">'.$item['title'].'</a></h3>
					 '.$html.'
					</div>'."\n";
					$i++;

					}
			}
			if($results['style'] == 1){ // LANDING PAGE STYLE 1
				echo '</div>'."\n";
				echo '<div id="secondarycontent">'."\n";
				if($this_page_guid == '469631da53ec9'){
					echo $panels[0]; // print 1st panel only if it Donate -- HACK--
				}
				/*echo '<div class="button">
            	<h2>Button</h2>
                <p>text</p>
            	</div>';*/
            	if ($results['donate'] == 1){
					/*
					echo '<div class="button">
						  <h2>
						  <a href="/donate/">Donate now</a>
						  </h2>
						  <p>Click here to change a life. </p>
						  </div>';
					*/
					include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/donatebutton.php');
				}
				echo '</div>'."\n";
				echo '<div id="tertiarycontent">'."\n";
				if($this_page_guid == '469631da53ec9'){
					array_shift($panels); // 1st panel's been used so ditch it only if it's Donate -- HACK--
				}
				foreach($panels as $panel){
					//if( $item['x_sort_order']!='200' ){
						echo $panel;
					//}
				}
				echo '<p id="viewall"><a href="?showall">View all pages in this section</a></p>'."\n";
				echo '</div>'."\n";
			}
			else { // LANDING PAGE STYLE 2
				
				foreach($panels as $panel){
					echo $panel;
				}
				echo '<p id="viewall"><a href="?showall">View all pages in this section</a></p>'."\n";
				echo '</div>'."\n";
				echo '<div id="secondarycontent">'."\n";
				// Personal Story
				$query = "SELECT story_guid FROM pages_stories_relationship WHERE guid = '".$pageGUID."' LIMIT 1";
				$story_guid = $db->get_var($query);

				$story_guid = read($_POST,'story_guid',$story_guid);
				$story = new PersonalStory($story_guid); // this would not normally have a GUID specified
				include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/storypanel.php');
				echo '</div>'."\n";
				echo '</div>'."\n";
				
			}
			
			?>
            
            
            <?php
		}
		else{ //Show all
			if($results['style'] == 1){
				echo '</div>'."\n";
				echo '<div id="secondarycontent">'."\n";
				/*echo '<div class="button">
            	<h2>Button</h2>
                <p>text</p>
            	</div>*/
            	if ($results['donate'] == 1){
					/*
					echo '<div class="button">
						  <h2>
						  <a href="/donate/">Donate now</a>
						  </h2>
						  <p>Click here to change a life. </p>
						  </div>';
					*/
					$donate_button=1;
					include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/donatebutton.php');
				}
				echo '</div>';
				
				?>
                <div id="tertiarycontent">
                    <ul>
                    <?php foreach($pages as $item){ ?>
                        <li class="arrow"><a href="<?=$page->drawLinkByGUID($item['page_guid'])?>"><?=$item['title']?></a></li>            
                    <?php } ?>
                    </ul>
                    <p id="viewall"><a href="./">View a list of this section's page</a></p>
            	</div>
            <?php
				
			} 
			else if($results['style'] == 2){
			?>
            <ul>
        	<?php foreach($pages as $item){ ?>
            	<li class="arrow"><a href="<?=$page->drawLinkByGUID($item['page_guid'])?>"><?=$item['title']?></a></li>            
            <?php } ?>
            </ul>
            <p id="viewall"><a href="./">View this section's landing page</a></p>
            <?php
				echo '</div>'."\n";
				echo '<div id="secondarycontent">'."\n";
				// Personal Story
				$query = "SELECT story_guid FROM pages_stories_relationship WHERE guid = '".$pageGUID."' LIMIT 1";
				$story_guid = $db->get_var($query);
				$story_guid = read($_POST,'story_guid',$story_guid);
				$story = new PersonalStory($story_guid); // this would not normally have a GUID specified
				include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/storypanel.php');
				echo '</div>'."\n";
				echo '</div>'."\n";
			}
		?>

        <?php
		}
		// footer
		include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php');
	}
	else{
		// This page is used for pages that contain other pages, but don't have any content themselves
		
		//$query = "SELECT guid FROM pages WHERE parent = '".$pageGUID."' AND date_published IS NOT NULL ORDER BY sort_order LIMIT 1";
		
		//*
		$query = "SELECT guid 
			FROM pages 
			WHERE parent = '".$pageGUID."' 
			AND date_published IS NOT NULL 
			AND unix_timestamp(date_published)>0 ";
		if ($location[0]=='news') $query.="ORDER BY date_published DESC LIMIT 1";
		else $query.= "ORDER BY sort_order LIMIT 1";
		if ($testing) print "q($query)";

		$data = $db->get_row($query);
	
		// We need to preserve the "action" flag when redirecting, so --
		$mode = $page->getMode();
		
		if ($db->num_rows > 0) { /* a child of this section exists */
		
			// no landing page defined so shwo the first page from the section
				$page = new page();
				$page->loadByGUID($data->guid);
				$pageGUID = $data->guid;
				$page->setMode($mode);
				$pageDate = $page->date_created;
				
				// If we are attempting to view an offline page dump em back to the homepage
				if ($page->offline && $mode!="edit") {	
					$task=new Tasks($site->id); 
					$task->add(0, "Offline page hit", $pageGUID, $_SERVER['HTTP_REFERER']);
					header("Location: /");
					exit();
				}
				
				include($_SERVER['DOCUMENT_ROOT'].'/'.$page->getTemplate());
		}
		else { // Section Doesn't have any children so show
		
			$_GET['error'] = 404; // set error number
			header ('HTTP/1.1 404 Page Not Found');
			include($_SERVER['DOCUMENT_ROOT'].'/error.php');
			//include($_SERVER['DOCUMENT_ROOT'].'/index.php');
		}
	
	}
	
?>