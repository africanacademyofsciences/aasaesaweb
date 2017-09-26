<?
header ("Content-Type: text/xml; charset=utf-8");

/*
The main change I would make is to encourage better use of the meta description so that could
replace using the full page content here.  That's just a temporary measure!

By using the one channel we're keeping code down.

This could easily be adapted to get selected feeds from an 'RSS' column in the page database.
*/

$guid = htmlentities(trim($_GET['guid']));
$page->loadByGUID($guid);
$content = new HTMLPlaceholder();
$content->load($page->getGUID(), 'content');
$content->setMode('view');

$term = urldecode(read($_GET,'keywords',''));

echo "<?xml version=\"1.0\" ?>\n";
echo "<rss version=\"2.0\">\n\n";
echo "<channel>\n\t<title>".$website->config['site_name'].": ". htmlentities($page->drawTitle());
if($page->getName()=='search' && $term){echo " for '".$term."'";}
echo "</title>\n\t";
echo "<link>http://".$website->config['site_url']."/</link>\n\t";
echo "<description>". htmlentities($page->drawMeta('description')) ."</description>\n\t";
echo "<language>en-gb</language>\n\t\t";

echo "<item>\n\t\t\t";
echo "<title>".$website->config['site_name']." ". htmlentities($page->drawFullTitle()) ."</title>\n\t\t\t";
if($page->getName()=='sitemap'){
	//// get sitemap content...
	echo "<description>". htmlentities($menu->drawSiteMapByParent()) ."</description>\n\t\t\t";
}else if($page->getName()=='search' && $term){
	$search = new Search('content',$term);
	echo "<description>". htmlentities($search->drawResults(1)) ."</description>\n\t\t\t";
}else{
	echo "<description>". htmlentities($content->draw()) ."</description>\n\t\t\t";
}
echo "<pubDate>". date("D, j M Y H:i:s T",strtotime($page->getDatePublished())) ."</pubDate>\n\t\t\t";
echo "<link>http://".$website->config['site_url'] . $page->drawLink() ."</link>\n\t\t";
echo "</item>\n\t";
echo "</channel>\n</rss>\n";
?>