<?php

class Site{
/*
Treeline Site class
====================
Author: Dan Donald
Created On: 1st Feb 2007
Comment:
	Manages all sites created within a single Treeline
====================
*/

//// Define attributes

	// Nice easy way to access commonly used data
	public $id, $msv, $site_id;
	public $microsite;
	public $name, $title;
	public $lang, $language;
	public $url;
	public $root, $link, $path;
	public $domain; 				// Domain name used to access the site (USU same as SERVER_NAME)
	public $palate, $font, $logo_filename;
	public $google_js;				// Any tracking codes etc for google?
	
	// site properties
	private $properties = array(); // store root guid, name, title, etc in an array - site's home page
	//private $colours = array(); // default site colour & text colour in an array
	private $contact = array(); // for the contact form
	
	private $versions = array(); // List language versions of this site
	
	private $comment = ''; // for internal reference
	
	private $preview = array();// used for previewing the site...
	
	//private	$editablesections=array('name'=>'Site Name', 'title'=>'Site Title', 'contact_email'=>'Contact Email', 'region'=>'Region', 'url'=>'External URL');
		
	// internal values
	private $title_limit; // number of characters used for titles
	private $section_total; // number of sections
	private $sitelist = array(); // store basic site details of all sites in an array 
	
	public $isLoaded = false;
	
	private $config = array();
	
	public $errmsg=array();
	
	//// Constructor
	public function __construct($msv=0, $guid=0){
		$this->title_limit = 15;
		$this->section_total = 8;
		//print "init site($guid)<br>";

		$this->loadConfig();
		
		if($msv) $this->loadBySiteID($msv);
		else if($guid) $this->loadByPageGUID($guid);
		
		return;
	}
	
	public function __toString(){
		return (string)$this->contact_email;
	}
	
	
	//// Get/set methods

	// this can be used to get an attribute, unless a specialised method exists.
	// methods need to be in the format getThisMethodName.
	public function __get($attribute){	
		$method = str_replace(' ','','get'.ucwords( str_replace('_',' ',$attribute) ) );
		
		if( isset($this->$attribute)  ){
			return $this->$attribute;
		} else if( method_exists($this,$method) ){
			return call_user_method($method,$this);
		} else {
			return false;
		}
	}

	public function __set($attribute,$value){
		if( isset($this->$attribute) ){
			$this->$attribute = $value;
			return true;
		}else{
			return false;
		}
	}


	public function saveStatus($status){
		global $db;
		$query="UPDATE sites_versions SET preview_mode='". $status ."' WHERE msv='". $this->properties['msv'] ."'";
		if( $db->query($query) ){
			$this->properties['status'] = $status;
			return true;
		}else{
			return false;
		}
	}


	
//// Core functionality
// This requires a specific page guid. Poss need a version of this that just loads a site?

	// Load the site object based on a page GIUD
	public function loadByPageGUID($guid){
		global $db;
		if (($msv=$db->get_var("select msv from pages where guid='$guid'"))>0) {
			$this->loadBySiteID($msv);
		}
	}		

	public function loadByMicrosite($microsite){
		global $db;
		$query="select primary_msv from sites where microsite=$microsite";
		//print "$query<br>\n";
		if (($msv=$db->get_var($query))>0) {
			return $this->loadBySiteID($msv);
		}
		return false;
	}		
	
	public function loadBySiteID($msv) {
		global $http;
		//print "<!-- lBSI($msv) http($http) -->\n";
		//print "Load site($msv)<br>\n";
		if ($msv>0) {
			if( $sitelist = $this->getSiteList($msv) ){
				
				$this->id=$msv;
				$this->msv=$msv;
				$this->site_id=$msv;
				
				foreach($sitelist as $data){
		
					$this->microsite = $data->microsite;
					$this->name=$data->name;
					$this->lang=$data->language;
					$this->language=$data->language;
					$this->comment = $data->comment;
					
					$this->palate = $data->palate;
					$this->font = $data->font;
					$this->logo_filename = $data->logo_filename;
					
					$encoding=$data->encoding;
					$this->properties['msv'] = $data->msv;
					$this->properties['microsite'] = $data->microsite;
					$this->properties['primary_msv'] = $data->primary_msv;
					$this->properties['site_name'] = $data->name;
					$this->properties['site_title'] = htmlentities($data->title, ENT_QUOTES, $encoding);
					$this->title=$this->properties['site_title'];
					
					//$this->properties['contact_email'] = $data->contact_email;
					//$this->properties['page_name'] = $data->page_name;
					//$this->properties['page_title'] = $data->page_title;
					$this->properties['tagline'] = htmlentities($data->tagline, ENT_QUOTES, $encoding);
					//$this->properties['speciality'] = $data->speciality;
					$this->properties['owner_id'] = $data->owner_id;
					$this->properties['owner_name'] = $data->full_name;
					$this->properties['owner_email'] = $data->owner_email;
					$this->properties['status'] = $data->status;
					$this->properties['style'] = $data->style;
					$this->properties['language'] = $data->language;
					$this->properties['encoding'] = $encoding;
					$this->properties['ltr']=$data->ltr;
					$this->properties['switches']=$data->switches;
	
					$this->properties['description'] = htmlentities($data->description, ENT_QUOTES, $encoding);
					$this->properties['keywords'] = htmlentities($data->keywords, ENT_QUOTES, $encoding);
	
					// dates
					$this->properties['dates']['datecreated'] = $data->created_date;
					$this->properties['dates']['datemodified'] = $data->modified_date;
					$this->properties['dates']['datesuspended'] = $data->datesuspended;	// Not used at min
					
					// supplimentary fields
					$this->contact['email'] = $data->contact_email;
					$this->contact['name'] = $data->contact_name;
					$this->contact['phone'] = $data->contact_phone;
	
					
					// preview access details
					$this->preview['username'] = $data->preview_username;
					$this->preview['password'] = $data->preview_password;
	
					// Guid for the email header for this microsite version
					$this->properties['email_logo'] = $data->email_logo;
					$this->google_js = $data->google_js;
					
					$this->getVersionList($data->microsite);
					$this->loadSiteConfig();
					
					// if this works, let's recognise it!
					$this->isLoaded = true;
	
					// Links to this site

					$this->root = $http."://".$this->getConfig("site_url")."/";	// Independent of webserver environment
					$this->link = $http."://".$_SERVER['SERVER_NAME'].
					//$this->root = "http://".$this->getConfig("site_url")."/";	// Independent of webserver environment
					//$this->link = "http://".$_SERVER['SERVER_NAME'].
						(($msv>1)?"/".$this->properties['site_name']:"").
						(($this->id!=$this->properties['primary_msv'] && $this->lang)?"/".$this->lang."/":"/");
					$this->url = substr($this->link, 7);
					//print "<!-- created link(".$this->link.") server(".$_SERVER['SERVER_NAME'].")--> \n";
                   	if ($this->id == 19) $this->path = "/includes/html/";
                    else if (is_dir($_SERVER['DOCUMENT_ROOT']."/includes/html/".$msv."/")) $this->path = "/includes/html/".$msv."/";
                    else if (is_dir($_SERVER['DOCUMENT_ROOT']."/includes/html/")) $this->path = "/includes/html/";
                    else $this->path = "/";
					
					return true;
				}
			}
		}
		return false;
	}

	public function setDomain($domain) {
		if ($domain) {
			$this->domain = $domain;
			$this->link = "http://".$this->domain.
				(($this->id!=$this->properties['primary_msv'] && $this->lang)?"/".$this->lang."/":"/");
		}
	}
	public function setLanguage($language) {
		$this->properties['language']=$language;
	}
	public function setTagline($tagline) {
		$this->properties['tagline']=$tagline;
	}

	public function save($msv=0){
		global $db, $user;
		
		if ($msv>1) {
			if( count($this->properties)>0 ){
				// add default language in here!
				$query = "INSERT INTO sites (name, title, primary_msv) values ('". $this->properties['site_name'] ."', '". mysql_real_escape_string( $this->properties['site_title'] ) ."', $msv)";
				if ($db->query($query)) {
					return $this->saveVersion($db->insert_id);
				}
			}
		}
		return false;
	}
	
	public function saveVersion($microsite) {
		global $db, $user;
		//print_r($this->properties);
		$query = "INSERT INTO sites_versions 
			(microsite, tagline, created_user_id, preview_mode, 
			created_date, contact_email, preview_username, preview_password, description, keywords,
			language, contact_name, contact_phone,
			palate, font) 
			VALUES 
			('". $microsite ."', '". mysql_real_escape_string( $this->properties['tagline'] ) ."',
			 ". ($this->properties['owner_id']+0) .", '0', NOW(), 
			 '". $this->contact['email'] ."', '". $this->preview['username'] ."', '". $this->preview['password'] ."', 
			 '". mysql_real_escape_string( $this->properties['meta_desc'] ) ."', '". mysql_real_escape_string( $this->properties['meta_keywords'] ) ."', 
			 '". $this->properties['language'] ."', '". $this->contact['name'] ."','". $this->contact['phone'] ."',
			 ".($this->palate+0).", ".($this->font+0).")";
		//print "$query<Br>";
		if ($db->query($query)) return $db->insert_id;
		return false;
	}


	public function update(){
		global $db, $user;
		
		if( count($this->properties)>0 ){
			$query = "UPDATE sites SET name='". $this->properties['site_name'] ."', title='". mysql_real_escape_string( $this->properties['site_title'] ) ."', 
						datemodified=NOW(), contact_email='". mysql_real_escape_string( $this->contact_email ) ."', 
						preview_username='". mysql_real_escape_string( $this->preview['username'] ) ."', 
						preview_password='". mysql_real_escape_string( $this->preview['password'] ) ."', 
						comment='". mysql_real_escape_string( $this->comment ) ."',
						cvt='". $this->cvt ."' 
						WHERE microsite='". $this->properties['guid'] ."'";
						
			//echo $query;
			//exit();
			if( $db->query($query) ){
				$query = "UPDATE pages SET name='". $this->properties['site_name'] ."', meta_description='". $this->meta['description'] ."', 
							meta_keywords='".$this->meta['keywords']."' WHERE guid='". $this->properties['guid'] ."'";
				
			//echo $query;
			//exit();
			
				$db->query($query);
				return true;
			}
		}else{
			return false;
		}

	}
	
	public function deleteVersion($msv, $debug=false) {	
		global $db, $page;
		
		// Get all pages in this site
		if($debug) print "get all pages for site($msv)<br>";
		$pages = $page->getDescendentsByGUID($msv);
		$list = "'".$msv."',";
		foreach($pages as $p){
			$list .= "'$p',";
		}
		if ($list) {
			$list = substr($list,0,strlen($list)-1);
			$query = "DELETE FROM pages WHERE guid IN (". $list .")";
			if ($debug) print "$query<br>";
			$db->query($query);
		}

		// Move all files from this site
		$query = "UPDATE files SET site_id='1' WHERE site_id='". $msv ."'";
		if ($debug) print "$query<br>";
		$db->query($query);
		
		// Move all images from this site
		$query = "UPDATE images SET site_id='1' WHERE site_id='". $msv ."'";
		if ($debug) print "$query<br>";
		$db->query($query);

		// REmove all translations
		$query = "delete from labels_translations where msv=$msv";
		if ($debug) print "$query<br>";
		$db->query($query);
		
		// Remove any short urls
		$query = "delete from shorturls where msv=$msv";
		if ($debug) print "$query<br>";
		$db->query($query);
		
		// SITE versions record
		$query = "DELETE FROM sites_versions WHERE msv=".$msv;
		if ($debug) print "$query<br>";
		$db->query($query);

	}
	
	public function delete($microsite=0, $debug=false){
		global $db, $page;
		
		if (!$microsite) $microsite=$this->microsite;
		if ($debug) print "delete site ($microsite)<br>";
		
		if(isset($microsite) && is_object($page) && $microsite>1){
		
			// Save primary msv
			$primary_msv=$db->get_var("select primary_msv from sites where microsite=$microsite");
			
			// PAGES & SECTIONS & VERSION
			$query="select msv from sites_versions where microsite=$microsite";
			if ($debug) print "$query<br>";
			if ($site_list=$db->get_results($query)) {

				foreach($site_list as $site_tmp) {
					$this->deleteVersion($site_tmp->msv, $debug);
				}
			}
			
			// SITE record
			$query = "DELETE FROM sites WHERE microsite=".$microsite;
			if ($debug) print "$query<br>";
			$db->query($query);

			
			// USERS
			// groups...
			$query="SELECT id FROM groups WHERE domain='".$primary_msv."'";
			if ($debug) print "$query<br>";
			$groups = $db->get_results($query,"ARRAY_A");
			$groupslist = '';
			foreach($groups as $g){
				$groupslist .= '\''.$g['id'].'\',';
			}
			$groupslist = substr($groupslist,0,strlen($groupslist)-1);
			$query = "DELETE FROM groups WHERE domain='". $primary_msv ."'";
			if ($debug) print "$query<br>";
			$db->query($query);
			// permissions
			$query = "DELETE FROM permissions WHERE guid='". $primary_msv ."'";
			if ($debug) print "$query<br>";
			$db->query($query);
			// users records
			if ($groupslist) {
				$query = "DELETE FROM users WHERE `group` IN (". $groupslist .")";
				if ($debug) print "$query<br>";
				$db->query($query);
			}
			
			return true;
			
		}
		else if ($microsite==1) {
			$this->errmsg[]="You cannot delete the main site";
			if ($debug) print "You cannot delete the main site";
		}
		return false;
	}



	public function addPages($thisSiteID, $shortcut='', $meta_desc='', $meta_keywords='') {
	
		//print "add pages($thisSiteID, $shortcut, desc, keyw)<br>";
		$homepage = new Page;
		$homepage->setParent(0); // Note that we've hardcoded the root of the site with a GUID of 1. I think that's reasonable.
		$homepage->setTitle($shortcut);
		$homepage->setGUID($thisSiteID);
		
		if( $homepage->generateName()){
			$homepage->setTitle('Home');
			//$homepage->setName($name);
			$homepage->setHidden(0);
			$homepage->setLocked(0);	
			$homepage->setSortOrder(0);
			$homepage->setTemplate(1);
			$homepage->setStyle(0);
			$homepage->setSiteID($thisSiteID);
			$homepage->setMetaDescription($meta_desc);
			$homepage->setMetaKeywords($meta_keywords);
			$homeContent = new HTMLPlaceholder();
			$homeContent->load($thisSiteID, 'content');
			$homeContent->save();
			if( $homepage->create(1) ){
										
				// Create the sub-pages required: news, contact us and the sections requested							
				// 	[Note: when editing/creating a site, you should be told that you have x 'slots' free for sections, up to the maximum of five. This will allow you to add a fifth section, say, if you only add four initially].	

				// SEARCH page
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Search');
				$newPage->generateName();
				$newPage->setTitle('Search Results');
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(1);					
				$newPage->setTemplate(8);
				$newPage->setMetaDescription('Search results page');
				$newPage->setMetaKeywords('Search results, search, search this site');
				$newPage->create(1);
				//echo 'search created!<br />';	

				// NEWSLETTER VIEWING PAGE
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Newsletter');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(1);					
				$newPage->setTemplate(32);
				$newPage->setMetaDescription('Newsletter online viewer');
				$newPage->setMetaKeywords('View newsletter, online newsletter viewer');
				$newPage->create(1);

				
				// CONTACT page						
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Contact Details');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(0);							
				$newPage->setStyle(18); 		// 2 Col R
				$newPage->setSortOrder(99);					
				$newPage->setTemplate(3);
				$newPage->setSiteID($thisSiteID);
				$newPage->setMetaDescription('A Contact Us page');
				$newPage->setMetaKeywords('Contact, contact us, contact details');
				$newPage->create(1);	
				//echo 'contact page created!<br />';


				// SITEMAP page
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Sitemap');
				$newPage->generateName();
				$newPage->setTitle('Site Map');
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setTemplate(9);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Site map');
				$newPage->create(1);												
				//echo 'sitemap created!<br />';

				// -----------------------------------
				// STORE PAGES
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Shop');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setTemplate(70);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Site store');
				$newPage->create(1);												
				unset($newPage);
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Shopping basket');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setTemplate(69);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Shopping basket');
				$newPage->create(1);												
				unset($newPage);
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Checkout');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setTemplate(73);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Store checkout process');
				$newPage->create(1);												
				unset($newPage);
				// -----------------------------------


				// TAGS page
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Tags');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setSiteID($thisSiteID);
				$newPage->setLocked(1);	
				$newPage->setStyle(1); 
				$newPage->setTemplate(14);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Tags');
				$newPage->create(1);
				//echo 'tags page created!<br />';


				// RSS page
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('RSS');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setTemplate(10);
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('RSS');
				$newPage->create(1);
				//echo 'RSS created!<br />';



				// Newsletters page							
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Enewsletters');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(0);	
				$newPage->setStyle(18); 		// 2 Col R
				$newPage->setSiteID($thisSiteID);
				$newPage->setTemplate(5);				
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Subscribe to our email updates');
				$newPage->create(1);
				//echo 'newsletters created!<br />';


				// Accessibility page							
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Accessibility Statement');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(0);	
				$newPage->setTemplate(2);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Accessibility Statement');
				$newPage->create(1);
				$newPage->copyContent(1, 'accessibility-statement');
				//echo 'accessibility page created!<br />';


				// Privacy Policy page							
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Privacy Policy');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(0);	
				$newPage->setTemplate(2);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Privacy Policy');
				$newPage->create(1);
				//echo 'privacy policy page created!<br />';

				// Terms and conditions page
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Terms and conditions');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(0);	
				$newPage->setTemplate(2);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Privacy Policy');
				$newPage->create(1);
				//echo 'privacy policy page created!<br />';

				// What are tags page							
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('What are tags?');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(1);	
				$newPage->setTemplate(2);
				$newPage->setStyle(1);					
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('What are tags');
				$newPage->create(1);
				$newPage->copyContent(1, 'what-are-tags');
				//echo 'privacy policy page created!<br />';

				// Send to friend							
				$newPage = new Page;
				$newPage->setParent($thisSiteID);
				$newPage->setTitle('Send to friend');
				$newPage->generateName();
				$newPage->setHidden(1);
				$newPage->setLocked(0);	
				$newPage->setTemplate(33);
				$newPage->setStyle(99);	
				$newPage->setSiteID($thisSiteID);
				$newPage->setSortOrder(99);					
				$newPage->setMetaDescription('Send page link to a friend');
				$newPage->create(1);
				//echo 'Send to friend page created!<br />';

				// Resources page							
				if ($this->getConfig("setup_resources")) {
					$newPage = new Page;
					$newPage->setParent($thisSiteID);
					$newPage->setTitle('Resources');
					$newPage->generateName();
					$newPage->setHidden(0);
					$newPage->setLocked(0);	
					$newPage->setTemplate(16);
					$newPage->setStyle(1);					
					$newPage->setSiteID($thisSiteID);
					$newPage->setSortOrder(2);					
					$newPage->setMetaDescription('Resources on this site');
					$newPage->create(1);

					$newPage = new Page;
					$newPage->setParent($thisSiteID);
					$newPage->setTitle('Multimedia');
					$newPage->generateName();
					$newPage->setHidden(0);
					$newPage->setLocked(0);	
					$newPage->setTemplate(16);
					$newPage->setStyle(1);					
					$newPage->setSiteID($thisSiteID);
					$newPage->setSortOrder(3);					
					$newPage->setMetaDescription('Multimedia resources on this site');
					$newPage->create(1);

					$newPage = new Page;
					$newPage->setParent($thisSiteID);
					$newPage->setTitle('Media player');
					$newPage->generateName();
					$newPage->setHidden(1);
					$newPage->setLocked(1);	
					$newPage->setTemplate(28);
					$newPage->setStyle(1);					
					$newPage->setSiteID($thisSiteID);
					$newPage->setSortOrder(99);					
					$newPage->setMetaDescription('Video/Audio player for this site');
					$newPage->create(1);
				}
				//echo 'Videos page created!<br />';

				// Members log in page
				if ($this->getConfig("setup_members_area")) {
					$newPage = new Page;
					$newPage->setParent($thisSiteID);
					$newPage->setTitle('Member Login');
					$newPage->generateName();
					$newPage->setHidden(1);
					$newPage->setLocked(1);	
					$newPage->setTemplate(12);
					$newPage->setStyle(18);					
					$newPage->setSiteID($thisSiteID);
					$newPage->setSortOrder(99);					
					$newPage->setMetaDescription('Log in to members area');
					$newPage->create(1);
				}

				// Personal blogs page
				if ($this->getConfig("setup_blogs")) {
					$newPage = new Page;
					$newPage->setParent($thisSiteID);
					$newPage->setTitle('Blogs');
					$newPage->generateName();
					$newPage->setHidden(0);
					$newPage->setLocked(0);	
					$newPage->setTemplate(29);
					$newPage->setStyle(3);					
					$newPage->setSiteID($thisSiteID);
					$newPage->setSortOrder(1);					
					$newPage->setMetaDescription('Members blogs page');
					$newPage->create(1);
				}

	
				return true;
			}
		}
		return false;
	}

	// Utility methods
	public function getVersionList($microsite) {
		global $db;
		$query="select msv.msv, msv.language, l.title, c.flag from sites_versions msv
		LEFT JOIN languages l ON msv.language=l.abbr
		LEFT JOIN country c ON l.code2=c.code2
		WHERE msv.microsite=$microsite
		AND preview_mode=1";
		//print "$query<Br>";
		if ($results=$db->get_results($query)) {
			$i=0;
			foreach ($results as $result) {
				$this->versions[$i]['msv']=$result->msv;
				$this->versions[$i]['lang']=$result->language;
				$this->versions[$i]['title']=$result->title;
				$this->versions[$i]['flag']=$result->flag;
				$i++;
			}
			
			return true;
		}
		return;
	}
	

	public function getSiteList($msv=0,$orderby=false){
		global $db;
		$orderby = ($orderby) ? ' ORDER BY '. $orderby : '';
		$query = "SELECT gsd.*
			FROM get_site_details gsd
			".($msv>0?" WHERE msv=$msv ":'')."
			".$orderby;
		//print "<!-- $query -->\n";
		if( $this->sitelist = $db->get_results($query) ){
			return $this->sitelist;
		}else{
			return false;
		}		
	}
	
	
	public function getNextSiteID($add=false){
		global $db;
		$query="show table status like 'sites_versions'";
		//print "$query<br>\n";
		$sv_data=$db->get_row($query);
		if( ($next = $sv_data->Auto_increment) > 0 ){
			//print "Next site id = $next<br>\n";
			return $next;
		}else{
			return false;
		}
	}
	
	
	public function checkSiteName($name){
		global $db;
		$query = "SELECT microsite FROM sites WHERE name='". $name ."'";
		//print "$query<br>\n";
		if( $guid = $db->get_var($query) ){
			return $guid;
		}else{
			return false;
		}		
	}

	// 15th Jan 2009 - Phil Redclift
	// Ensure that a site name does not conflict with any pages on the main website
	public function checkPageNames($name) {
		global $db;
		$query = "SELECT guid FROM pages WHERE name = '$name' and msv=1 LIMIT 1";
		//print "$query<br>\n";
		return $db->get_var($query);
	}

	public function getSiteLanguages($siteID=false){
		global $db;
		
		$siteID = ($siteID) ? "WHERE p.guid='". $siteID ."'" : '';
		$query = "SELECT l.abbr, l.title, l.title_local FROM languages l LEFT JOIN pages p ON l.abbr=p.lang ".$siteID." ORDER BY l.sort_order";

		if($list = $db->get_results($query, "ARRAY_A") ){
			$langlist = array();
			
			foreach($list as $l){
				$langlist[] = $l['abbr'];
			}
			return $langlist;
		}else{
			return false;
		}
	}
	

	
	public function getMicrositeList($exclude=array()) {
		global $db;
		$query = "SELECT * FROM sites ";
		if (count($exclude)) {	
			$query.="WHERE microsite not in (";
			foreach($exclude as $site_id) $query.=$site_id.",";
			$query=substr($query,0,-1).") ";
		}
		$query .= "ORDER BY title";
		//print "$query<br>\n";
		return $db->get_results($query);
	}
	
	// Show a list of microsites
	public function drawSelectMicrositeList($name='sitelist', $exclude=array()){
		global $siteID;
		
		if($sites = $this->getMicrositeList($exclude)){
			$html = '<select name="'. $name .'" id="'. $name .'">'."\n";
			if($blank){
				$html .= "\t". '<option></option>' ."\n";
			}
			foreach($sites as $site){
				$html .= "\t". '<option value="'. $site->microsite .'">'. $site->title .'</option>' ."\n";
			}
			$html .= '</select>'."\n\n";
			return $html;
		}else{
			return false;
		}
	}


	public function loadSiteConfig() {
		global $db;
		//print "Load site Config for (".$this->properties['switches'].") <br>\n";
		$query = "SELECT * FROM sites_switches WHERE value & ".($this->properties['switches']+0);
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				//print "add (".$result->title.") to config<br>\n";
				$this->config[$result->title]=1;
			}
		}
		//print "loaded config(".print_r($this->config, true).")<br>\n";
	}

	// Collect global configuration paramenters
	// These parameters apply to all sites/microsites 
	public function loadConfig() {
		global $db;
		if ($results=$db->get_results("select name, value from config")) {
			foreach($results as $result) {
				if ($result->name == "image_sizes") {
					$sizes=explode(",",$result->value);
					if (is_array($sizes)) {
						$i=0;
						foreach($sizes as $size) {
							$this->config['size'][$i]['index']=$i;
							if (preg_match("/(.*):(.*)/", $size, $reg)) {
								$this->config['size'][$i]['size']=$reg[1];
								$this->config['size'][$i]['desc']=html_entity_decode($reg[2]);
							}
							else if ($size) {
								$this->config['size'][$i]['size']=$size;
							}
							$i++;
						}
					}
				}
				else $this->config[$result->name]=$result->value;
			}
			return true;
		}
		return;
	}
	
	public function getConfig($s) {
		//print "checking config(".print_r($this->config, true).")<br>\n";
		return $this->config[$s];
	}
	
	// 20th Jan 2009 - Phil Redclift
	// Check any optional items in the sites_options table that we want to allow users to configure here.
	public function drawOptions($aOpts) {
		global $db;
		$html='';
		foreach($aOpts as $opt) $fields.="'$opt',";
		$query = "SELECT value, name, title, data FROM sites_options 
			WHERE name in (".substr($fields,0,-1).")
			ORDER by name, value";
		//print "$query<br>\n";
		if ($results=$db->get_results($query)) {
			foreach ($aOpts as $opt) {
				$tmp='';
				foreach($results as $result) {
					if ($result->name==$opt) {
						$optStyle='';
						if ($opt=="palate" && $result->data) {
							$optStyle='color: #'.$result->data.'; background-color: #'.$result->data.';text-indent:-1000px;';
							$optTitle = 'Palate #'.$result->value.' (<span style="background-color:#'.$result->data.';">#'.$result->data.'</span>)';
						}
						else $optTitle=$result->title;
						$tmp.='<option value="'.$result->value.'" style="'.$optStyle.'">'.$optTitle.'</option>'."\n";
					}
				}
				$preview_link=($opt=="palate"?' (<a href="javascript:palate_preview();">example</a>)':"");
				$html.='<label for="opt_'.$opt.'">'.ucfirst($opt).$preview_link.'</label><select id="config_'.$opt.'" name="opt_'.$opt.'">'.$tmp.'</select>';
			}
		}
		return $html;
	}


	
	//// Destructor
	public function __destruct(){
		
	}



}
?>