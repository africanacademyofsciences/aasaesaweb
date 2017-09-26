<?

$type = read($_GET,'type','');
$timeout = 3600;
$timeout = 1;
//print "Running sitemap($type) timeout($timeout)<br>\n";

if(!$type){

	$content = new HTMLPlaceholder();
	$content->load($page->getGUID(), 'content');
	$content->setMode($page->getMode());
	$content->setHeight('500px');
	
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');
	
	$tags = new Tags($site->id, 1);
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$referer.=(strpos($referer, "?")?"&":"?");
		$action = read($_POST,'treeline','');
	
		if (read($_POST,'treeline','') == 'Save changes') {
			$content->save();
			$page->save();
			$referer .= 'message='.urlencode("Changes saved to page '<strong>".$page->getTitle()."</strong>' in section <strong>". $page->drawTitleByGUID($page->getSectionByPageGUID($page->getGUID()))."</strong>");
			$referer .= '&action=edit';
			redirect ($referer);

		}
		else if (read($_POST,'treeline','') == 'Discard changes') {
			$referer .= 'message='.urlencode("Changes discarded");
			$referer .= '&action=edit';
			redirect ($referer);
		}
	}
	
	// Page specific options
	
	$pageClass = 'sitemap'; // used for CSS usually
	
	$css = array('forms'); // all attached stylesheets
	if($page->style) $css[] = $page->style;

	
	// extra page specific CSS
	$extraCSS = ''; 
	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	$pagetitle = "Sitemap";

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
	
	?>
	<div class="main-content">
		<div class="container">
			<div class="col-lg-12" id="primarycontent">
	
                <!-- <p><?= $page->getMetaDescription() ?></p> -->
                <?php 
                    if( $siteID==1 ) $filename = 'sitemap.inc';
                    else $filename = 'sitemap_'. $site->id .'.inc';
            
                    //print "look for map ".$_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename."<br>";
                    if(validCache($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename)){
                        //print "cache still valid<br>\n";
                        include($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename);
                    }
                    else{
                        //print "drawSiteMap(0, ".$site->id.")<br>";
                        $contents = $menu->drawSiteMapByParent(0, $site->id);
                        createCache($filename, $contents); 
                    }
                ?>	
            </div>
            <!-- end content div (id="content") -->
		</div>
    </div>	
	<?php 
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
	?>	
	
	<?php 
} 
else if($type == 'xml' || $type=="xmlnews"){ 

	// mime-type
	header("HTTP/1.0 200 OK", true);
	header("Content-type: text/xml; charset: UTF-8");
	
	//$timeout = 1;
	//echo $menu->drawXMLSiteMapByParent();
	$filename = 'sitemap'.$site->id.($type=="xmlnews"?"news":"").'.xml';

	$xmlfile = $_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename;
	if(!validCache($xmlfile, $timeout)){
		//file doesn't exists, do create it and include it
		//print "create file($xmlfile)<br>\n";
		$contents = $menu->drawXMLSiteMapByParent($site->id, 0, $type=="xmlnews");
		//print "content($contents)<br>\n";
		createCache($filename, $contents, false); 
		$menu->drawSiteMapIndex();
	}
	
	if (file_exists($xmlfile)) {
		readfile($xmlfile);
	}
	
}
else if($type == 'xmlmap') { 

	$xmlsitemap = $_SERVER['DOCUMENT_ROOT']."/includes/snippets/sitemapindex.".($site->id+0).".xml";
	header("Content-type: text/xml; charset: UTF-8");

	if (file_exists($xmlsitemap)) {
		echo file_get_contents($xmlsitemap);
	}
	else {
		echo "<";
		?>?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>http://<?=$_SERVER['HTTP_HOST']?>/sitemap.xml</loc>
        <lastmod>2011-06-17T12:53:29+00:00</lastmod>
    </sitemap>
</sitemapindex><?php
	}
}

?>