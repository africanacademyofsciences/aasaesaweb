<?
	//CREATE GENERIC RSS FEED FOR ALL CONTENT ON THE SITE

	/*
	$query = "SELECT c.guid, c.parent, c.content, c.revision_date as date, p.title 
				FROM content c, pages p WHERE c.revision_id  = 0 AND c.placeholder = 'content' 
				AND p.guid=c.parent AND hidden = 0 ORDER BY c.revision_date DESC LIMIT 25";
	*/
	$query = "SELECT p.guid, c.parent, c.content, c.revision_date, p.title, u.full_name, p.meta_description 
				FROM content c
				LEFT JOIN pages p ON c.parent=p.guid
				LEFT JOIN pages p2 on p.parent=p2.guid 
				LEFT JOIN users u ON p.user_created=u.id
				WHERE c.revision_id  = 0 AND c.placeholder = 'content' AND p.msv=". $siteID ."
				AND p.template IN (1,2,4) AND p.name NOT IN ('accessibility-statement','privacy-policy')
				AND c.revision_date > now() - INTERVAL 18 MONTH AND p.hidden=0
				AND p2.name='news' AND p.private=0
				ORDER BY c.revision_date DESC LIMIT 25";
	//mail("phil.redclift@ichameleon.com", "rssqry", getcwd()."\n\n".$query);
	//mail("phil.redclift@ichameleon.com", "rssqry", $query);
	$results  = $db->get_results($query);

	// Header image
	$header_img = new HTMLPlaceholder();
	$header_img->load($siteID, 'header_img');
	if (!$header_img->draw()) {
		$header_img->load($siteData->primary_msv, 'header_img');
		if (!$header_img->draw()) {
			$header_img->load(1, 'header_img');
		}
	}
	if (preg_match("/(.*)src=\"(.*)\/silo\/(.*?)\"(.*)/", $header_img->draw(), $reg)) {
		$rss_img="silo/".$reg[3];
	}
	else $rss_img="/silo/images/disarming-mine_716x76.jpg";

header ("Content-Type: text/xml; charset=". $site->properties['encoding']);
 //echo '<pre>'. print_r($results,true) .'</pre>';
/*
The main change I would make is to encourage better use of the meta description so that could
replace using the full page content here.  That's just a temporary measure!

By using the one channel we're keeping code down.

This could easily be adapted to get selected feeds from an 'RSS' column in the page database.
*/
ob_start();
echo "<?xml version=\"1.0\" encoding=\"". $site->properties['encoding'] ."\" ?>\n";
echo "<rss version=\"2.0\">\n\n";
echo "<channel>\n\t";
echo "<title><![CDATA[".$site->title." Latest Content]]></title>\n\t";
echo "<link>http://". $_SERVER['HTTP_HOST'] ."</link>\n\t";
echo "<description><![CDATA[". html_entity_decode($site->comment, ENT_QUOTES, $site->properties['encoding'] ) ."]]></description>\n\t";
echo "<language>". $site->properties['language'] ."</language>\n\t";
echo "<generator>Treeline CMS</generator>\n\t";
echo "<lastBuildDate>". date("D, d M Y H:i:s O",getDateFromTimestamp($results[0]->revision_date)) ."</lastBuildDate>\n\t";
echo "<copyright><![CDATA[Copyright ". $site->properties['site_title'] ." ". date('Y') ."]]></copyright>\n\t";
echo "<image>\n\t\t";
echo "<link>http://". $_SERVER['HTTP_HOST'] ."</link>\n\t";
echo "<url>http://". $_SERVER['HTTP_HOST'] ."/". $rss_img ."</url>\n\t\t";
echo "<title><![CDATA[". $site->properties['site_title'] ."]]></title>\n\t\t";
echo "<description><![CDATA[". $site->comment ."]]></description>\n\t";
echo "</image>\n\n\t";

foreach($results as $result){
	$page = new Page();
	$page->loadByGUID($result->parent);
	$content = new HTMLPlaceholder();
	$content->load($result->parent, 'content');
	$content->setMode($page->getMode());
	echo "<item>\n\t\t\t";
	echo "<title><![CDATA[". html_entity_decode($page->drawTitle(), ENT_QUOTES, $site->properties['encoding'] ) ."]]></title>\n\t\t\t";
	echo "<description><![CDATA[". substr(str_replace("\n\r", "", strip_tags(html_entity_decode($content->draw(), ENT_QUOTES, $site->properties['encoding'] ))), 0, 300) ."...]]></description>\n\t\t\t";
	echo "<author>". htmlentities($result->full_name ." <no-reply@". $_SERVER['HTTP_HOST'] .">")."</author>\n\t\t\t";
	echo "<pubDate>". date("D, d M Y H:i:s O",getDateFromTimestamp($result->revision_date)) ."</pubDate>\n\t\t\t";
	//echo "<link>http://". $_SERVER['HTTP_HOST'] . $page->drawLink() ."</link>\n\t\t";
	//echo "<guid>http://". $_SERVER['HTTP_HOST'] . $page->drawLink() ."</guid>\n\t\t";
	echo "<link>" . $page->drawLink() ."</link>\n\t\t";
	echo "<guid>" . $page->drawLink() ."</guid>\n\t\t";
	if( $sectionGUID = $page->getSectionByPageGUID( $result->guid ) ){
		echo "<category><![CDATA[". html_entity_decode($page->drawTitleByGUID( $sectionGUID ), ENT_QUOTES, $site->properties['encoding'] )  ."]]></category>\n\t\t";
	}
	echo "</item>\n\t\t";
}
echo "</channel>\n</rss>\n";
ob_end_flush();
?>