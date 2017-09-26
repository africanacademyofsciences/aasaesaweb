<?php

// First gateway into any Treeline website.

// testing variable is a simple way to display semi random debug info.
$testing=true;
$testing=false;
if ($_GET['testing']==1) $testing=true;
if ($testing) ini_set("display_errors", 1);
if ($testing) print "<!-- Started rewrite -->\n";

$http = "http";

if ($_SERVER['HTTPS']=="on") $http.="s";


// LIMIT ACCESS TO THIS SITE
// You can limit by adding to the allowed_ip array or add any other criteria in here
$allowed_ip=array("80.177.11.158", "localhost");
unset($allowed_ip);
if (count($allowed_ip) && !in_array($_SERVER['REMOTE_ADDR'], $allowed_ip)) {
	header("location: http://\n\n");
}

if ($_SERVER['HTTP_HOST']=="localhost") {
	header("location: http://localhost/treeline\n\n"); exit();
}

// To be lazy and clearish, just replace the = sign with a == to toggle this
if ($couldnotbebotheredtopay==1) {
	include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/holding/index.php");
	exit();
}


$publicationcategories = "6,7,8,9";

$storeURL = '/shop'; // there should be a dynamic way of getting this...!?!
$global_template_dir = '';

$USEFLIR = false;	// Switch FLIR replacement on

// set functions that are not allowed to run (another measure to protect form input)
ini_set('disable_functions','eval,phpinfo,system,shell_exec,passthru,proc_open,popen');
 
session_start();
include $_SERVER["DOCUMENT_ROOT"].'/includes/snippets/protect.site.php';


// Need to change DOC ROOT if we have SSL access
if( $_SERVER['SERVER_PORT']==443 ){ // replace with a regex
	//$_SERVER['DOCUMENT_ROOT'] = '';
}

include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");

// Set the debugging on
//$DEBUG = (read($_GET,'debug',false) !== false) ? true : false;

include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/placeholder.class.php");		
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/menu.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/user.class.php");	
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/search.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/tags.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/comment.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/image.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/site.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/captcha.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/news.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/forms.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/tasks.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/recaptcha.class.php");


// Just in case we happen to need it anywhere later (like header images?)	
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/browserdetect.php");

	// Mobile detect
	/*
	// Live mobile functions
	if(
		strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') || 
		strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') ||
		$_SESSION['mobile']=="on" ||
		$_GET['mobilet']==1
		) {
		$global_mobile = true;
	}
	*/
	// Testing mobile functions
	if($_SESSION['mobile']=="on" || $_GET['mobilet']==1) {
		$global_mobile = true;
	}

	$request = read($_SERVER,'REQUEST_URI','');
	$qs = read($_SERVER,'QUERY_STRING',false);
	if ($testing) print "<!-- request($request) qs($qs) -->\n";
	//print "<!-- req($request) --> \n";
		
	// Save the referer in case we need to jump back later.
	$referer = urldecode(read($_REQUEST,'referer',''));
	if (strpos($referer,'?') > 0) $referer .= '&';
	else $referer .= '?';

	if ($qs) {
		// Strip the querystring out of the URI
		$request = str_replace("?$qs",'',$request);
	}
	//print "<!-- request($request) -->\n";
	
	$location = explode('/',$request);
	$first = array_shift($location); // We can disregard the first item, as this is the element before the first /
	$last  = end($location);
	
	if (!$last) {
		// if the URL ends in '/', eg /organisation/, we want the page 'organisation'
		array_pop($location);
		$last = end($location);
	}

	// What's the name of the page we're looking for?
	//print "<!-- location (".print_r($location, true).") -->\n";
	$name = $db->escape(str_replace('.html','',$last));
	if ($testing) print "<!-- Find page $name  -->\n";
	
	// Members area fix
	if (isset($location[0]) && $location[0]=="members") $name=$location[0];
	
	// Blogs area fix
	//if (isset($location[0]) && $location[0]=="blogs") $name=$location[0];
	//if (isset($location[0]) && isset($location[1]) && $location[1]=="blogs") $name=$location[1];
	//if ($testing || 1) echo '<!-- '. print_r($location,true) ." --> \n";
	
	// Check if we are hitting a tags page
	if ($location[0]=="tags" || $location[1]=="tags" || $location[2]=="tags") include ($_SERVER['DOCUMENT_ROOT']."/includes/snippets/checkTags.inc.php");
	
	// First --------------------------
	// Check for Short URL ------------
	// Valid if 
	// 1 - Only one thing passed to us on the qs
	// 2 - Page linked to is online
	$msid=0;
	//print "<!-- Short loca[0](".$location[0].") count loc(".count($location).") msid($msid) -->\n";
	if ($location[1] && count($location)==2) {
		// Test for short URL on a microsite (addressed in mainsite/microsite/ format)
		// Note that shorturls do not work when addresses as mainsite/microsite/lang/ format.

		// Make sure location[0] is a valid microsite name
		$query = "SELECT microsite FROM sites WHERE name = '".$location[0]."'";
		$sqms = $db->get_var($query);
		if ($sqms>0) {
			$short_query="SELECT s.msv, longurl, s.guid 
				FROM shorturls s 
				LEFT JOIN pages p on s.guid=p.guid 
				WHERE shorturl='".$location[1]."'
				AND p.offline=0
				AND p.msv = $sqms
				";
		}
	}
	else if ($location[0] && count($location)==1) {
		$short_query="SELECT s.msv, longurl, s.guid 
			FROM shorturls s 
			LEFT JOIN pages p on s.guid=p.guid 
			WHERE shorturl='".$location[0]."'
			AND p.offline=0
			";
	}
	/*
	if ($location[0] && count($location)==1 && !$msid) {
		$short_query="SELECT s.msv, longurl, s.guid 
			FROM shorturls s 
			LEFT JOIN pages p on s.guid=p.guid 
			WHERE shorturl='$location[0]'";
	}
	*/
	if ($testing) print "<!-- Short loca[1](".$location[1].") count loc(".count($location).") msid($msid) -->\n";

	
	//Its a bit of a pig but the way microsites redirect means the short URL is actually at position[1]
	// For urls like http://www.repointed-domain.com/shorturl
	// Translate to http://www.actual-url/microsite/shorturl internally - SWITCHED OFF USING 301 NOW
	/*
	if ($location[1] && count($location)==2 && !$msid) {
		$short_query="SELECT s.msv, longurl, s.guid 
			FROM shorturls s 
			LEFT JOIN pages p on s.guid=p.guid 
			WHERE shorturl='$location[1]'";
	}
	*/
	if ($short_query) {
		if ($testing) print "<!-- shorturl - $short_query -->\n";
		if ($row=$db->get_row($short_query)) {
			$msv=$row->msv;
			$shortGUID=$row->guid;
			//$name=str_replace("/", '', $row->longurl);
			$query="select * from get_site_details where msv=$msv";
			if ($testing) print "<!-- Get siteData- $query --> \n";
			if ($siteData=$db->get_row($query)) {
				$language=$siteData->language;
				$msid=$siteData->microsite;
			}
			if ($testing) print "<!-- got msid($msid) name($name) guid($shortGUID) -->\n";
		}
	}
	
	// If we have a real domain name try to get the site details from this
	// We do this even if we have an msid already
	$tmp_real_domain='';
	$query = "SELECT sv.microsite, sv.msv FROM sites s
		LEFT JOIN sites_versions sv ON s.microsite=sv.microsite
		LEFT JOIN sites_domains sd ON sv.msv=sd.msv
		WHERE sd.domain='".$_SERVER['SERVER_NAME']."'
		LIMIT 1";
	if ($testing) print "<!-- $query  -->\n";
	if ($row = $db->get_row($query)) {

		if ($testing)print "<!-- Matched a domain for site(".$row->microsite.") dom(".$_SERVER['SERVER_NAME'].") -->\n";
		$tmp_real_domain=$_SERVER['SERVER_NAME'];
		$tmp_real_msid=$row->microsite;
		if ($tmp_real_msid != $msid) {
			// Buggers, we found a short URL on a site that does not 
			// match the forwwared domain name. Forget the short URL.
			if ($testing) print "<!-- SHORT URL($shortGUID) was overridden by DOMAIN redirect --> \n";
			$msid=$tmp_real_msid;
			$shortGUID = '';
			$msv = $row->msv;
			$siteData = $db->get_row("SELECT * FROM get_site_details WHERE msv=".$row->msv);
		}
	}
	else if ($msid>1) {
		/*
		Lets remove this PMR 17th Oct 2014 - Seems we should trust the short URL
		if ($testing) print "<!-- MSID ($msid) main site but got an msid?? --> \n";
		$msid=$msv=0;
		$shortGUID = '';
		unset($siteData);
		*/
	}
	else if ($testing) print "<!-- this server has no domain set up so show the main site -->\n";

	if ($testing) print "<!-- got msid($msid) loc0[".$location[0]."] --> \n";
	
	// Second ----------------------------------
	// If it was not a short URL
	// Get Microsite and language --------------
	if ($location[0] && !$msid) {
		$query="select microsite from sites where name='".$db->escape($location[0])."'";
		if ($testing) print "<!-- Get msid - $query -->\n";
		$msid=$db->get_var($query);
		if ($msid) array_shift($location);
		else $msid=1;	// In the default site so array 0 is now a valid language or a pagename
		if (strlen($location[0])==2) {
			// Attempt to get a language version of the site
			$query="select * from get_site_details where language='".$location[0]."' and microsite=$msid";
			if ($testing) print "<!-- Get msv - $query -->\n";
			if ($siteData=$db->get_row($query)) {
				$language=$location[0];
				array_shift($location); 
			}
		}
	}
	//if ($testing) print "Got msid($msid)<br>";

	// If its a language version of a site we already have siteData filled.
	// If its the root of a microsite or a short URL we collect it now	
	if (!$siteData && $msid>0) {
		// Collect the default site data for this site
		if ($testing) print "<!-- need to collect site data array... --> \n";
		$query="select sv.msv from sites_versions sv left join sites s on sv.msv=s.primary_msv where s.microsite=$msid";
		if ($testing) print "<!-- $query -->\n";
		if ($msv=$db->get_var($query)) {
			$query="select * from get_site_details where msv=$msv";
			if ($testing) print "<!-- $query -->\n";
			$siteData=$db->get_row($query);
		}
	}

	// Check we have loaded our site ok? We shudda got it by now
	if (!$siteData) {
		// This is a pretty big problemo, we really should have found a valid site by now.
		// Wot do we do? for now just default to the main version of the main site.
		$query="select sv.msv from sites_versions sv left join sites s on sv.msv=s.primary_msv where s.microsite=1";
		if ($testing) print "<!-- SITE DATA WAS NOT FOUND ??? \n $query -->\n";
		if ($msv=$db->get_var($query)) {
			$query="select * from get_site_details where msv=$msv";
			if ($testing) print "<!-- $query -->\n";
			$siteData=$db->get_row($query);
		}
	}
	
	// Save some good stuff for later.
	$siteID = $siteData->msv;
	if ($testing) print "<!-- Load site $siteID --> \n";
	$site = new Site($siteData->msv);
	$site->setDomain($tmp_real_domain);	// Set the domain name we are using to access this microsite
	

	// Do we want to show a holding page for now?
	if ($site->getConfig('holding')) {
		include ($_SERVER['DOCUMENT_ROOT']."/includes/templates/holding/index.php");
		exit();
	}
	
	$storeVersion = "v".$site->getConfig("setup_store");

	//print "<!-- server(".$_SERVER['SERVER_NAME'].") msv(".$site->id.") siteID($siteID) preview ".print_r($site->preview, true). " --> \n";
	// I dont like these we should try to ensure they are not used anywhere and remove them
	// Try to take out siteID too
	$siteLang = $site->properties['language'];
	$siteName = $site->properties['name'];
	$siteLink = $site->link;

	// Set up $addthidcode as a global in case we ever feel like using it.
	$addthiscode = '';
	$addthisfile = $_SERVER['DOCUMENT_ROOT']."/includes/snippets/addthis.".$site->lang.".inc.php";
	if (file_exists($addthisfile)) $addthiscode = file_get_contents($addthisfile);

	//print "<!-- got siteLink($siteLink) -->\n";
	if ($testing) print "<!-- Loading the ".$site->id." - $siteLang - $siteName - link($siteLink) - url({$site->url}) -->\n";
	if ($testing) print "<!-- site <pre>".print_r($site, 1)."</pre> -->\n";
	if ($testing && 0) {
		foreach($siteData as $k=>$siteDataItem) {
			print "<!-- siteData -> $k = $siteDataItem -->\n";
		}
	}
	
	// What's the full URL of the page we're looking for?
	$target = '/' . implode("/",$location);

	// are we in view/edit/preview mode?
	// need to know now as certain pages are not available in offline mode.
	$mode = read($_REQUEST,'mode','view'); 

	if (read($_GET,'pass',false) !== false){
		// If we want to access page directly, add ?pass to the URL [just for debugging]
		$page = $_SERVER['DOCUMENT_ROOT'].$request;
		include($page);
		exit();
	}
	else {
		$include = ''; $match='';
		$page = new Page();

		// Generate a list of translated labels for this site.
		$labels=$page->getTranslations($site->id, $siteLang);
		
		// If we've removed all items from the array, the requested URI must have been '/', so we're looking for the homepage
		if (!$location) {
			$query = "SELECT guid FROM pages WHERE parent = '0' and msv=".($site->id+0);
			if ($testing) print "<!-- $query -->";
			$pages = $db->get_results($query);
		}
		else{
			
			// SITEMAP (XML)
			if($name == 'sitemap.xml'){
				$name = 'sitemap';
				$_GET['type'] = 'xml';
				unset($location);				
			}
			else if($name == 'sitenews.xml'){
				$name = 'sitemap';
				$_GET['type'] = 'xmlnews';
				unset($location);				
			}
			else if ($name == "sitemapindex.xml") {
				$name = 'sitemap';
				$_GET['type'] = 'xmlmap';
				unset($location);				
			}
			

			//************************************************************
			///////// STORE MANAGEMENT /////////
			if ($site->getConfig('setup_store')) {
				// SET cookie if you hit the store or shopping basket!
				//print "in store? (".$location[0].", ".$storeURL.")<br>\n";
				if($location[0]==substr($storeURL,1) || $location[0]=='shopping-basket' ){

					include_once($_SERVER['DOCUMENT_ROOT'] .'/treeline/store/'.$storeVersion.'/includes/store.class.php');
					include_once($_SERVER['DOCUMENT_ROOT'] .'/treeline/store/'.$storeVersion.'/includes/basket.class.php');
					$store = new Store();

					if(isset($_COOKIE['cartID']) ){
						$cartID = $_COOKIE['cartID'];
						//print "Collected cart ID($cartID) from cookie<br>\n";
					}
					if( isset($_GET['cartID']) ){
						//print "Collected cart ID from qs<br>\n";
						$cartID = $_GET['cartID'];
					}				
					if( isset($_POST['cartID']) ){
						//print "Collected cart ID from post<br>\n";
						$cartID = $_POST['cartID'];
					}	

					// Before loading the basket ensure this cartID is valid
					// Invalid if
					// 		1 = We have paid this order already and are not attempting to view the thanks. Happens if you navigate away from paypal after payment then back to the site
					// 		2 = Order does not exist, happens if COOKIE is reset during order/shopping
					// Setting cardID = 0 ensures we create a new record and a new COOKIE.

					$query = "SELECT order_id, `status` FROM store_orders WHERE order_id='$cartID' AND msv=".($site->id+0)." ";
					if ($row = $db->get_row($query)) {
						if ($row->status > 0 && $_GET['payment_status']!="Completed") {
							$cartID = 0;
						}
					}
					else {
						$cartID = 0;
					}
		
					$basket = new Basket($cartID);
					if ($testing) echo 'hit store - cartID('. $cartID .') basketID('.$basket->cartID.')'."\n";
					//echo 'Hit store - cartID('. $cartID .') basketID('.$basket->cartID.') order('.$db->get_var("SELECT order_id FROM store_orders where order_id='".$basket->cartID."'").')'."\n";
					$cartID = $basket->cartID;		// Loading the basket can change the cart id.
					
					switch($location[1]){
						case 'account':
						case 'checkout':
							include($_SERVER['DOCUMENT_ROOT'] .'/treeline/store/'.$storeVersion.'/includes/account.class.php');
							$memberID = isset($_COOKIE['memberID']) ? $_COOKIE['memberID'] : false;
							$account = new Account($memberID);
							break;
						case 'search':
							break;
					}
	
					if( 
						(!isset($_COOKIE['cartID']) && !$_COOKIE['cartID'] && $_POST['stage']!='complete') ||
						$_COOKIE['cartID']!=$basket->cartID
						) {
						$basket->setCookie();
						/*
						$expires = (time()+3600*24);
						$path = '/';
						if( $basket->cartID) {
							$local = !strchr($_SERVER['HTTP_HOST'], ".");
							setcookie('cartID', $basket->cartID, $expires, $path, $local?'':$_SERVER['HTTP_HOST']);
						}
						*/
					}
				}
				
				// Store navigation
				if($location[0]==substr($storeURL, 1)){
					if( in_array($location[1],array('account','shopping-basket','store-search','checkout','delivery-and-returns-policy')) ){
						$name = $location[1];
					}
					else {
						$name = $location[0];
						$target = $storeURL;
					}
					$categoryName = $last!= substr($storeURL,1) ? urldecode($last) : false;
					$productName = read($_GET,'product',false);
					$storeBreadcrumb = '';
					if( $bc = $store->getCategoryBreadcrumb($categoryName) ){
						if( $store->breadcrumb->parent_title ){
							$storeBreadcrumb .= ' | <a href="'. $storeURL .'/'. $store->breadcrumb->parent_name .'">'. $store->breadcrumb->parent_title .'</a> | ';
							$storeBreadcrumb .= ($productName) ? '<a href="'. $storeURL .'/'. $store->breadcrumb->name .'">'.$store->breadcrumb->title .'</a>' : $store->breadcrumb->title;
						}else{
							$storeBreadcrumb .= ' | '. (($productName) ? '<a href="'. $storeURL .'/'. $store->breadcrumb->name .'">'.$store->breadcrumb->title .'</a>' : $store->breadcrumb->title);
						}
					}
					
				}
				//print "got name($name) product($productName) category($categoryName)<br>\n";
				if ($siteLang && $site->id>1) {
					$storeURL = "/".$site->name."/".$siteLang.$storeURL;
					//print "set storeURL($storeURL)<br>\n";
				}
			}
			/////// END STORE MANAGEMENT ////////
			//************************************************************

			

			$query = "SELECT p.guid FROM pages p 
				WHERE p.name = '".$db->escape($name)."' 
				AND p.msv=". $site->id;
			//if ($mode != "edit") $query.=" AND p.offline=0";
			if ($testing) print "<!-- Get page - $query -->\n";
			$pages = $db->get_results($query);
		}
		
		if ($db->num_rows == 1) {
			// if there's only one page in the database with the name we want, select it
			$match = $pages[0]->guid;	
		}
		else if ($db->num_rows > 1) {
			// if there's more than one page in the database with the name we want,
			// compare the link of each page with the target link we're after
			/// if it's a match, we're looking at the right page:
			foreach ($pages as $p) {
				$tmp = $page->drawLinkByGUID($p->guid);
				$tmp_target1 = substr($site->link,0,-1).$target."/";
				$tmp_target2 = substr($site->link,0,-1).str_replace($site->name."/".$site->lang."/", "", $target)."/";
				//if ($site->domain) $tmp_target=str_replace("/".$site->name, "", $target);
				if ($testing) echo "<!-- link(".$site->link.") sitelink(".$siteLink.") -->
<!-- domain(".$site->domain.") target(".$target.") -->
<!-- sent link == treeline generated link -->
<!-- $tmp == $tmp_target1 -->
<!-- $tmp == $tmp_target2 -->
";
				if ($tmp == $tmp_target1 || $tmp==$tmp_target2) {
					$match = $p->guid;
					if ($testing) echo "<!-- Matched got guid($match) -->\n";
					break;
				}
			}
			unset($tmp, $tmp_target);
		}
	}


	if ($testing) print "<!-- got match($match) short($shortGUID) -->\n"; 

	ob_start();
	// This is necessary for debugging, so that we can debug ezsql and still set $_SESSION

	if ($match>'' || $shortGUID>'') {

		$pageGUID = $match>''?$match:$shortGUID;
		$menu = new menu(); // navigation
		$page->loadByGUID($pageGUID); // get page from database
		if ($testing) print "<!-- loaded page(".print_r($page, 1).") -->\n";
		//$pagelabel = $page->getPageLabels( $site->properties['language'] );

		if ($shortGUID) {
			$redirectURL = $page->drawLinkByGUID($shortGUID);
			//print "<!-- short link(".$redirectURL.") -->\n";
			Header("HTTP/1.1 301 Moved Permanently" );
			Header("Location: ".$redirectURL);
			exit();
		}

		// If we are attempting to view an offline page dump em back to the homepage
		if ($page->offline) {
			if ($mode=="edit" || $mode=="preview") ;
			else {	
				//$task=new Tasks($site->id); 
				//$task->add(0, "Offline page hit", $pageGUID, $_SERVER['HTTP_REFERER']);
				header("Location: /");
				exit();
			}
		}
				
		if( $mode=='edit' ){
			$page->checkPermissions($mode);	 // can user view this page (in this mode)

			// Try to grab a lock on the page
			// we do this every time we hit the page as it will update our lock if we have one already
			$lock_user = $page->getLock($_SESSION['treeline_user_id'], $page->getGUID());
			if ($lock_user) {
				$redirectURL = $referer.'action=edit&'.createFeedbackURL('error',"This page is already locked for edit by $lock_user");
				//print "redirect($redirectURL)<br>\n";
				redirect($redirectURL);
			}
		}

		// Check if we need to do any captcha stuff
		// we always create the variable even if config['setup_use_captcha'] is false in case we need it
		$captcha=new Captcha(isset($_POST['captcha'])?true:false);
		$recaptcha=new reCaptcha();

		if ($testing) echo "<!--Page Get Template: ".$page->getTemplate()." -->\n";
		$template = $page->getTemplate();
		
		if ($testing) print "<!-- got template($template) -->\n";
		$tmp = substr($template,0,strpos($template,'.'));

		
		if ($testing) print "<!-- ".$_SERVER['DOCUMENT_ROOT'].'/'.$template." -->";
		
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
		$header_img->setMode($mode);

		// footer text
		$footer = new HTMLPlaceholder();
		$footer->load($site->id, 'footer');
		$footer->setMode($page->getGUID()==$site->id?$mode:"view");		// You can only edit the footer on the homepage.
		
		//print "status mode[".$site->properties['status']."] site ID(".$site->id.") sess[trl_prv]=".$_SESSION['treeline_preview']."<br>";
		if( $site->properties['status']==1 || 
			($site->properties['status']==0 && $_SESSION['treeline_preview']==$site->id) 
		  ) {

		  	if ($page->template_type=="panel") $template="panel.php";
			if ($testing) print "<!-- include template(".$template.") in mode(".$page->getMode()." - $mode) -->\n";

                        if (file_exists($_SERVER['DOCUMENT_ROOT']."/includes/html/".($site->id.$global_template_dir)."/site.init.php")) {
                            if ($testing) print "<!-- Add site initialisation -->\n";
                            include($_SERVER['DOCUMENT_ROOT']."/includes/html/".($site->id.$global_template_dir)."/site.init.php");					
                        }

                        if (substr($template, 0, 6)=="store/") $template="store/".$storeVersion."/".substr($template, 6);
                        
			$site_template = $_SERVER['DOCUMENT_ROOT']."/includes/html/".($site->id.$global_template_dir)."/".$template;
			if (file_exists($site_template)) {
				if ($testing) print "<!-- include site template($site_template)  -->\n";
				include($site_template);
			}
			else {	
				if ($testing) print "<!-- There is no site template($site_template)  -->\n";
                                if ($testing) print "<!-- include($template) -->\n"; 
				include($_SERVER['DOCUMENT_ROOT'].'/'.$template); // Load PHP page	
			}
                    
                    
		}
		else{
			include($_SERVER['DOCUMENT_ROOT'].'/preview.php'); // Load preview page...	
		}
	}
	// Failed to find requested page = show 404
 	else {
		
		$page=new Page();
		$menu=new Menu();
	
		$_GET['error'] = 404; // set error number
		header ('HTTP/1.1 404 Page Not Found');
		include($_SERVER['DOCUMENT_ROOT'].'/error.php');
	}
	
	
if (isset($_GET['viewaspdf'])) {
	$buffer=ob_get_contents();
	ob_end_clean();
	ob_end_clean();	// We call this twice since ob_start has been called twice when we need a PDF version
	//$buffer='<html><head></head><body>Hello World</body></html>'; 
	generatePDF($buffer);
}
else {	
	ob_end_flush();
}

//print "END (".print_r($_COOKIE, true).")<br>\n";

?>