<?php
	// Page specific options

	// First will do some tracking on why we have moved to an error page???
	//$err_msg.=$site->name." Error page has been hit \n";
	

	//Ok, see if this was a fixed page on the old site???
	$legacy_urls=array("example.php"=>"new_page");
	unset($legacy_urls);
	
	// List all Legacy URLs
	$query = "SELECT * FROM legacy_url ORDER BY old_url";
	if ($results = $db->get_results($query)) {
		foreach ($results as $result) {
			$legacy_urls[$result->old_url]=$result->new_url;
		}
	}
	
	$requested = $_SERVER['REQUEST_URI'];
	//print "r($requested)<br>\n";
	if (substr($requested, 0, 1)=="/") $requested = substr($requested, 1);
	//print "r($requested)<br>\n";
	if (substr($requested, -1, 1)=="/") $requested = substr($requested, 0, -1);
	//print "r($requested)<br>\n";

	foreach($legacy_urls as $url_name=>$url_page) {
		//print "<!-- check if ($url_name)==($name) req(".$requested.") for($url_page) -->\n";
		//print "check if ($url_name)==($name) req(".$requested.") for($url_page)<br>\n";
		if ($url_name==$requested) {
			$redirect_page_name=$url_page;
			//header ('HTTP/1.1 301 Moved Permanently');
			if ($redirect_page_name) {
				if (substr($redirect_page_name, -1, 1)!="/") $redirect_page_name.="/";
			}
			//print "would go to $redirect_page_name<br>\n";
			$query = "UPDATE legacy_url SET `count`=`count`+1 WHERE old_url = '".$url_name."'";
			$db->query($query);
			//mail("phil.redclift@ichameleon.com", "legacy add", $query);
			
			redirect($redirect_page_name, 301);
		}
	}


	$ignore_urls = array (
		"server-status", 
		"error.php", 				// Not sure why the site should be asking for this ?
		"_vti_inf",
		"cltreq.asp", "owssvr.dll"	// This always seem to come together must be hacking?
		);
	if (in_array($name, $ignore_urls)) ;
	else if ($name) {
		$err_msg.="FAILED to redirect to legacy page name($name) \n";
		
		// Ok we got a page requested that we dont like lets see if this is and old request from a bot?
		// If so tell the bot the page has moved permenantly in the hope that they can remove it from their
		// listings or index the redicted site?
		$bot_links=array(
			"Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)", 
			"Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)"
			);
		if (in_array($_SERVER['HTTP_USER_AGENT'], $bot_links)) {
			header ('HTTP/1.1 301 Moved Permanently');
			redirect($page->drawLinkByGUID('1'));
		}
	}

	$tags = new Tags();
	$tags->setMode($page->getMode());
	

	$pageClass = 'error'; // used for CSS usually
	
	$css = array("forms"); // all attached stylesheets
	if($page->style){
		$css[] = $page->style;
	}
	$extraCSS = '

	div#primarycontent ul li{
		background:url(\'/images/icons/3dots_bullet.gif\') no-repeat 0 5px; 
		display: block;
		list-style: none;
		margin-left: 10px;
		padding-left: 8px;
	}
	
'; // extra page specific CSS
	
	$js = array(); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	$google_analytics_extra = '<script type="text/javascript">/* STORE ERROR TYPE+URI IN GA\'s CONTENT DRILLDOWN */
	if (typeof urchinTracker=="function") {
		urchinTracker("error/'.$_GET['error'].$_SERVER['REQUEST_URI'].'");
	}</script>';

	$pagetitle = "Website Error";
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/snippets/pagetitle.inc.php');
	
?>
<div class="main-content">
	<div class="container">
		<div class="col-lg-12" id="primarycontent">
			<?php 
			if($_GET['error']){
				switch($_GET['error']){
					default:
					case 404:
						?>
							<p>The page you have requested, <em><?=$site->link?><?=$request?></em>, is missing. </p>
							<h3>Why has this happened?</h3>
							<ul>
								<li>you may have mistyped the web address</li>
								<li> a search engine may be listing an old web address</li>
								<li>there may be an error on our part </li>
							</ul>
						<?php
						break;
	
					case 500:
						?>
							<p>Our website has encountered an error and is not allowing you to view this page. This error has been reported and our technical team will be try to fix it as quickly as possible.</p>
						<?php		
						break;
	
					case 401:
						?>
							<p>Your are not authorised to view the page you have requested. </p>
						<?php			
						break;
	
					case 403:
						?>
						<p>403</p>
						<?php			
						break;
				}
				?>
				
				<h2>Find what you're looking for</h2>
				<p>A full list of all the pages on this website.</p>
				
				<?php
				if( $site->id==1 ){
					$filename = 'sitemap.inc';
				}
				else{
					$filename = 'sitemap_'. $site->id .'.inc';
				}
	
				// Load sitemap from cache if it exists.
				if (file_exists($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename) && (time()-filemtime($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename))<(60*60*24)) {
					include($_SERVER['DOCUMENT_ROOT'].'/cache/'.$filename);
				}
				else {
					//  file doesn't exists, do create it and include it 
					$contents = $menu->drawSiteMapByParent(0, $site->id);
					createCache($filename, $contents); 
				}
			}  
			?>
		</div>
	</div>
</div>

<?php 
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 

	// Ignore failed attempt to load invalid files...
	$extension=substr($name, -3, 3);
	//$err_msg.="ERROR - FAILED TO load page($name)  with extension($extension) \n";
	
	if ($name=="sample name") {
		$err_msg="These pages dont actually exist so not sure why we are hitting them?";
	}
	
	if ($err_msg) {
		$err_msg.="FAILED TO FIND A PAGE - LOG STUFF \n";
		$err_msg.="================================= \n";
		$err_msg.="Request (".$_SERVER['HTTP_HOST']." / $request /? $qs) \n";
		$err_msg.="From IP(".$_SERVER['REMOTE_ADDR'].") \n";
		if ($_SERVER['REQUEST_METHOD']=="GET") {
			foreach ($_GET as $k => $v) {
				$err_msg.="GET[$k] => $v \n";
			}
			foreach($_SERVER as $k=>$v) {
				$err_msg.="SERVER[$k] => $v \n";
			}
		}
		if ($_SERVER['REQUEST_METHOD']=="POST") {
			foreach ($_POST as $k => $v) {
				$err_msg.="POST[$k] => $v \n";
			}
		}
		$headers="From: Phil Redclift <".$site->name.".errors@ichameleon.com>"."\r\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();
		if (strtolower($_SERVER['SERVER_NAME'])!="csipu") {
			//mail("phil.redclift@ichameleon.com", $site->name." error page", $err_msg, $headers);
		}
	}

?>
