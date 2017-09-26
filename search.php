<?
//ini_set("display_errors",1);

$content = new HTMLPlaceholder();
$content->load($page->getGUID(), 'content');
$content->setMode($page->getMode());

$filter = read($_REQUEST,'filter','');
$range = read($_REQUEST,'daterange','');
$term = read($_REQUEST,'keywords','');
$blogguid = read($_REQUEST, 'blogguid', '');
$term = urldecode($term);

// Should we just show the advanced search options??
$advanced = ($_SERVER['REQUEST_METHOD']=="GET" && isset($_GET['adv']));

$global_canonicalURL = $page->drawLinkByGUID($page->getGUID());

$event_date = read($_REQUEST, "ed", '');	// Check if we are searching for events.

$thispage = read($_GET,'page',1);

$perPage = read($_GET,'show',10);
if ($event_date) $search = new Search('events', $event_date);
else if ($blogguid) $search = new Search('blogs', $term, $blogguid);
else $search = new Search('content', $term, $filter, 0, $range);
$search->setPage($thispage);
//print "sbg($blogguid)<br>\n";
//$search->setBlogGUID($blogguid);

$tags = new Tags();
// Panels
$search_panels = new PanelsPlaceholder();
$search_panels->load($page->getGUID(), 'search-panels');
$search_panels->setMode($mode);

// Header image
$header_img = new HTMLPlaceholder();
$header_img->load($page->getGUID(), 'header_img');
if ($mode!="edit") {
	if (!$header_img->draw()) {
		$header_img->load($site->id, 'header_img');
		if (!$header_img->draw()) {
			$header_img->load($siteData->primary_msv, 'header_img');
			if (!$header_img->draw()) {
				$header_img->load(1, 'header_img');
				//print "<!-- got header for site master site 1 --> \n";
			}
			//else print "<!-- got header for primary (".$siteDate->primary_msv.") --> \n";
		}
		// else print "<!-- got header for microsite (".$site->id.") --> \n";
	}
	//else print "<!-- got header for page ($pageGUID) --> \n";
}
$header_img->setMode("view");

// footer text
$footer = new HTMLPlaceholder();
$footer->load($site->id, 'footer');
$footer->setMode("view");	// You can only edit the footer on the homepage.

// If the URL has a QUERY STRING then rediretc it to a nice URL /search/keywords/
if (ereg("^/search/\?keywords=", $_SERVER['REQUEST_URI'])) {
	$newURL = '/search/'.urlencode($term).'/';
	$newURL .= ($thispage > 1) ? '?p='.$thispage : '';
	//print "redirect to $newURL"; exit();
	
	//OR DONT - WTF is this all about???
	// Really does not work well with microsites/languages/shorturls on the URL too :o(
	//redirect($newURL);
}

// Page specific options

$pageClass = 'search'; // used for CSS usually

$css = array('forms','page','search','contact'); // all attached stylesheets
if($page->style){
	$css[] = $page->style;
}
$extraCSS = ''; // extra page specific CSS

$js = array(); // all atatched JS behaviours
$extraJS = ''; // etxra page specific  JS behaviours

$pagetitle = "Search results";

include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	

include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');

?>
<div class="main-content">
    <div class="container">
		<div class="col-lg-12" id="primarycontent">

            <p>
            <?php 
            if (!$advanced) {
                $search_results = $search->drawResults($thispage);
                if( $search->getTotal()<=0 ){
                    echo $page->drawLabel('noresult', 'No results were found in your search for:')." ['<strong>".$search->getTerm()."</strong>']";
                    //echo $labels['noresult']['txt']." ['<strong>".$search->getTerm()."</strong>']";
                    $opts = $search->didYouMean(3);
                    if (is_array($opts) && count($opts)>0) {
                        foreach ($opts as $possy) {
                            $dym .= '<a href="/search/?keywords='.$possy.'">'.$possy.'</a>,';
                        }
                        echo '<p>'.$page->drawLabel("did-you-mean", "Did you mean").' '.substr($dym, 0, -1).'</p>'; 
                    }
                }
                else echo $search->drawTotal();
            }
            else echo "Advanced search";
            ?>
            </p>
            
			<?php 
            if ($search_results) echo $search_results;
            if (isset($_REQUEST['adv'])) {
                $adv_search_file = $_SERVER['DOCUMENT_ROOT']."/includes/snippets/advanced_search.inc.php";
                //print "inc($adv_search_file)<br>\n";
                include ($adv_search_file);
            }
            ?>
            
		</div>            
	</div>
</div>			
	
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>	