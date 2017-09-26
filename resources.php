<?

	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/resources.class.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/gallery.class.php");

	$tags = new Tags($site->id, 1);
	$tags->setMode($page->getMode());

	$term = read($_REQUEST,'keywords','');
	$thispage = read($_GET, 'page', 1);
	$perPage = read($_GET, 'show', 5);

	$orderBy = read($_GET,'filter','date_created');
	$orderDir = read($_GET,'order','desc');	
	$tagFilter = read($_GET,'tag',false);
	$filetype = read($_GET,'filetype',false);

	$search = new Resource($term, $tags->drawTags($page->getGUID()), $page->getMetaDescription());
	$search->setPage($thispage);
	$search->setPerPage($perPage);

	// Panels
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');
	$panels->setMode($mode);
	
	$panellist = array();
	$panellist[] = "twitter-timeline";

	foreach ($panellist as $addpanel) {
		$query = "SELECT guid FROM pages WHERE name = '$addpanel' AND template IN (6, 24)";
		if ($addpanelguid = $db->get_var($query)) {
			$panels->panels[] = $addpanelguid;
		}
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
	
		if ($action == 'Save changes') {
		
			if (is_object($header_img)) $header_img->save();
			$page->save(true);
			
			// Content is saved so redirect the user
			$feedback .= createFeedbackURL('success',"Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>".$page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
			$referer .= $feedback;
			$referer .= '&action=edit';
			
			$publish_redirect = '/treeline/pages/?action=saved&guid='.$page->getGUID();
			$publish_redirect .= '&'.$feedback;
			
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.class.php");
			if($user->drawGroup() == 'Superuser' || $user->drawGroup() == 'Publisher'){ // can this user publish pages?
				//print "would go to $publish_redirect<br>";
				redirect($publish_redirect); // show them the publish option
			} else{
				redirect($referer); // otherwise take the user back to the edit pages page
			}

		}
		else if ($action == 'Discard changes') {			
			$page->releaseLock($_SESSION['treeline_user_id']);			
			$referer .= 'action='.$page->getMode().'&'.createFeedbackURL('error','Your changes were not saved');
			redirect ($referer);
		}

	}
	
	// Page specific options
	$pageClass = 'resources'; // used for CSS usually
	
	$css = array('resources','page','lytebox'); // all attached stylesheets
	$extraCSS = ''; // extra page specific CSS
	
	$js = array("lytebox"); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	$jsBottom[] = "../includes/html/js/jquery.isotope.min";

	$extraJSbottom .= '
		// Isotope for news page
		// ================================================== 
		$(function(){
		  
		  var $container = $(\'#filter-container\'),
			  $filterLinks = $(\'#filters a\');
		  
		  $container.isotope({
			itemSelector: \'.download\'
		  });
		  
		  $filterLinks.click(function(){
			var $this = $(this);
			
			// don\'t proceed if already selected
			if ( $this.hasClass(\'selected\') ) {
			  return;
			}
			
			$filterLinks.filter(\'.selected\').removeClass(\'selected\');
			$this.addClass(\'selected\');
			
			// get selector from data-filter attribute
			selector = $this.data(\'filter\');
			
			$container.isotope({
			  filter: selector
			});
			
			
		  });
		  
		});
	';
	
	$mceFiles[]="headerimage";

	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/previewmode.inc.php");

	$pagetitle = $page->getTitle();
	$pagetitle = "Publications";	
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
?>


<div class="main-content">
    <div class="container">
    
        <div class="col-xs-12 col-sm-8" id="primarycontent">
    
            <div class="filter-buttons nooverflow" id="filters">
                <a class="btn btn-default btn-sm" data-filter="*"><i class="ion-ios-keypad-outline hidden-sm hidden-xs"></i> All</a>
                <a class="btn btn-primary btn-sm" data-filter=".spa"><i class="ion-ios-book-outline hidden-sm hidden-sm hidden-xs"></i> Science Policy Africa</a>
                <a class="btn btn-success btn-sm" data-filter=".innovation"><i class="ion-ios-lightbulb-outline hidden-sm hidden-sm hidden-xs"></i> Innovation</a>
                <a class="btn btn-warning btn-sm" data-filter=".reports"><i class="ion-ios-pie-outline hidden-sm hidden-sm hidden-xs"></i> Reports</a>
                <a class="btn btn-info btn-sm" data-filter=".policies"><i class="ion-ios-copy-outline hidden-sm hidden-sm hidden-xs"></i> Policies</a>
            </div>
            <hr>
            
            <!--
            <ul class="filter-list block" id="filter-container-zzz">
                <li class="download innovation">
                    <a href="#" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-lightbulb-outline"></i>
                            <h6>An innovation download</h6>
                        </div>
                        <div class="meta">
                            <p><i class="ion-ios-cloud-download-outline"></i> This-is-where-the-file-name-goes.pdf</p>
                            <p><i class="ion-ios-information-outline"></i> 228k</p>
                        </div>
                        <div class="abstract">
                            This list of publications appears in chronological order, and can be filtered using the buttons above. Press one to see what happens.
                        </div>
                    </a>
                </li>
                
                <li class="download spa">
                    <a href="#" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-book-outline"></i>
                            <h6>Science Policy Africa</h6>
                        </div>
                        <div class="meta">
                            <p><i class="ion-ios-cloud-download-outline"></i> This-is-where-the-file-name-goes.pdf</p>
                            <p><i class="ion-ios-information-outline"></i> 228k</p>
                        </div>
                        <div class="abstract">
                            The icon and colour match the filter button above, so you can quickly and easily see the category of each download file. This text helps to explain the file.
                        </div>
                    </a>
                </li>
			</ul>
    		-->
                    
			<?php
            $searchResults = $search->drawResourceResults($thispage,$orderBy,$orderDir);
    
            if( $search->getTotal()<=0){
                $resulthtml='<p>You have no resources ';
                if( $term > '') $resulthtml.='matching the term <strong>'.$search->getTerm().'</strong> ';
                if( $filetype>'') $resulthtml.='of type <strong>'.$filetype.'</strong> ';
                $resulthtml.='in this site.</p>';
            }
			echo $searchResults;
			?>			
            
		</div>
        
		<div class="sidebar col-xs-12 col-sm-4 col-md-3 col-md-offset-1" id="secondarycontent">
        
            <!--PANELS-->
            <?=$panels->draw(array(), array(13))?>
        </div>
        
    </div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>