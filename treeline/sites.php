<?php
	//ini_set("display_errors", true);
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
	$action = read($_REQUEST,'action','');
	if (!$action) header("Location: /treeline");
	$guid = read($_REQUEST,'guid','');
		
	$message = array();
	$feedback = "error";
	
	$title = read($_POST,'title','');
	$shortcut = htmlentities( read($_POST,'shortcut','') );
	$tagline = read($_POST,'tagline','');
	//$colour = read($_POST,'colour','');
	//$text_colour = read($_POST,'text_colour','');	
	$contact = read($_POST,'contact','');
	$contact_name = read($_POST,'contact_name','');
	$contact_phone = read($_POST,'contact_phone','');
	
	$comment = htmlentities( read($_POST,'comment','') );
	$meta_desc = htmlentities( read($_POST,'meta_desc','') );
	$meta_keywords = htmlentities( read($_POST,'meta_keywords','') );
	$news = read($_POST,'news','');
	$language = read($_POST,'language','');
	
	$preview_username = read($_POST,'preview_username','');	
	$preview_password = read($_POST,'preview_password','');	
	
	$superuser_full_name = htmlentities( read($_POST,'superuser_full_name','') );
	$superuser_name = htmlentities( read($_POST,'superuser_name','') );
	$superuser_email = htmlentities( read($_POST,'superuser_email','') );	
	$superuser_password = htmlentities( read($_POST,'superuser_password','') );		

	// new site sections
	$numSections = 8;
	for($i=1; $i<=$numSections; $i++){
		${'section_'.$i} = read($_POST,'section_'.$i,false);
		${'section_template_'.$i} = read($_POST,'section_template_'.$i,11); // default to 'folder'
	}



	
	$homeText = read($_POST,'treeline_content','');	


	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		if ($action == 'create') {
			//echo '<pre>'. print_r($_POST,true) .'</pre>';
			
			if (!$title) {
				$message[] = 'Please enter a title for this site';
			}
			if (!$shortcut) {
				$message[] = 'Please enter a shortcut for this site';
			}				
			if ($contact == '') {
				$message[] = 'Please enter the email contact for this site';
			}
			
			if ($preview_username == '') {
				$message[] = 'Please enter a preview username for this site';
			}
			if ($preview_password == '') {
				$message[] = 'Please enter a preview password for this site';
			}
								
			if ($superuser_full_name == '') {
				$message[] = 'Please enter the full name of the superuser who will maintain this site';
			}
			if ($superuser_email == '') {
				$message[] = 'Please enter the email address of the superuser who will maintain this site';
			}						
			if ($superuser_name == '') {
				$message[] = 'Please enter a username for the superuser who will maintain this site';
			}
			if ($user->userExists($superuser_name)) {
				$message[] = 'That username is already taken';			
			}
			if ($superuser_password == '') {
				$message[] = 'Please choose a password for this site\'s superuser';
			}
			// NOTE: we should check that all section names are unique here, otherwise we'll have clashes
			// Just load them into an array, do a count, run array_unique, and then do another count. The two values should be the same.
			if( !count($message) ) {
				
				$shortcut=str_replace(" ", "-", $shortcut);
			
				$thisSiteID = $site->getNextSiteID(true);
				//echo 'siteID: '.$thisSiteID.'<br />';
				//$thisSiteID = ($thisSiteID>'' ? $thisSiteID : 3);

				// Generate a unique name for the page:
				if ($site->checkSiteName($shortcut)) {
					$message[] = 'A site with that name already exists';
				}
				else if ($site->checkPageNames($shortcut)) {
					$message[] = "Your site name conflicts with a page on the main website";
					$message[] = "Please try a different name for your new microsite";
				}				
				else {
				
					if ($site->addPages($thisSiteID, $shortcut, $meta_desc, $meta_keywords)) {
						
						// Create SECTIONS
						for ($i = 1; $i<=$numSections; $i++) {
							$section = ${'section_'.$i};
							if ($section) {
								$newPage = new Page;
								$newPage->setParent($thisSiteID);
								$newPage->setTitle( htmlentities($section) );
								$newPage->generateName();
								$newPage->setHidden(0);
								$newPage->setSortOrder(($i+1));				
								$newPage->setTemplate( ${'section_template_'.$i} );
								$newPage->setLocked(1);	
								$newPage->setSiteID($thisSiteID);
								$newPage->setMetaDescription($meta_desc);
								$newPage->setMetaKeywords($meta_keywords);
								$newPage->create();
								//echo 'section "'. $section .'" created!<br />';

								// We need to publish the page record for it to appear in the main menu
								$query = "UPDATE pages SET date_published = NOW(), user_published = {$user->getID()} WHERE guid = '{$newPage->getGUID()}'";
								//print "$query<br>";
								$db->query($query);
							}
						}

						// Create three new records in the Groups table; su/publisher/admin for the domain [name]
						// Do we need a Group class? Or can we just "hardcode" this?							
						$db->query("INSERT INTO groups(name, domain) VALUES ('Superuser','".$thisSiteID."');");
						$superuser_group = $db->insert_id;								
						$db->query("INSERT INTO groups(name, domain) VALUES ('Publisher','".$thisSiteID."');");
						$publisher_group = $db->insert_id;
						$db->query("INSERT INTO groups(name, domain) VALUES ('Author','".$thisSiteID."');");											
						$author_group = $db->insert_id;						

						// Create one new record in the users table, in the su/[name] group
						$superuser = new User;
						$superuser->setName(htmlentities($superuser_name,ENT_QUOTES , 'utf-8'));
						$superuser->setFullName( htmlentities($superuser_full_name,ENT_QUOTES , 'utf-8'));
						$superuser->setEmail($superuser_email);
						$superuser->setPassword( htmlentities($superuser_password,ENT_QUOTES , 'utf-8'));
						$superuser->setGroup($superuser_group);
						$superuser->setStatus(0);
						$superuser->save();																												
							
						// Create three new records in the permissions table, one for each of su/publisher/admin for GUID	
						// Do we need a Permissions class? Surely not...
						// Note that I've hardcoded the permission bitmasks, though, which probably isn't ideal
						$db->query("INSERT INTO permissions(`group`, guid, level) VALUES ($superuser_group,'{$thisSiteID}',15);");												
						$db->query("INSERT INTO permissions(`group`, guid, level) VALUES ($publisher_group,'{$thisSiteID}',7);");											
						$db->query("INSERT INTO permissions(`group`, guid, level) VALUES ($author_group,'{$thisSiteID}',3);");																								
						//echo 'User, Group & Permissions created!<br />';						
						
						//$site = new Site;
						$tmpdata = array();
						$tmpcontact = array();
						$tmppreview = array();

						// properties
						$tmpdata['guid'] = $thisSiteID;
						$tmpdata['site_name'] = $shortcut;
						$tmpdata['tagline'] = $tagline;
						$tmpdata['site_title'] = htmlentities($title);
						$tmpdata['owner_id'] = $superuser->getID();
						$tmpdata['status'] = 1;
						$tmpdata['style'] = 0;
						$tmpdata['language'] = $language;
						$tmpdata['meta_desc']=$meta_desc;
						$tmpdata['meta_keywords']=$meta_keywords;
						
						// site contact
						$tmpcontact['name'] = $contact_name;
						$tmpcontact['email'] = $contact;
						$tmpcontact['phone'] = $contact_phone;
						
						
						// preview access details
						$tmppreview['username'] = htmlentities($preview_username);
						$tmppreview['password'] = htmlentities($preview_password);
						
						$site->properties = $tmpdata;
						$site->preview = $tmppreview;
						$site->contact = $tmpcontact;
						
						$site->palate = $_POST['opt_palate'];
						$site->font = $_POST['opt_font'];
						
						// Attempt to copy the requested palate file.
						$palate_file=$_SERVER['DOCUMENT_ROOT']."/style/scheme/palate".(($site->palate<10?"0":"").($site->palate+0)).".css";
						if (file_exists($palate_file)) {
							$scheme_file=$_SERVER['DOCUMENT_ROOT']."/style/microsite/scheme".($thisSiteID+0).".css";
							//print "copy($palate_file, $scheme_file)<br>\n";
							copy($palate_file, $scheme_file);	
						}
						//else print "Failed to locate palate($palate_file)<br>\n";
						
						if( $site->save($thisSiteID) ){
						
							// Add a newsletter preference
							$query = "INSERT INTO newsletter_preferences
								(	
									preference_title, preference_description, 
									deleted, site_id
								)
								VALUES
								(
									'Main newsletter', 
									'This is the default newsletter people will be subscribed to',
									0, ".$thisSiteID."
								)
								";
							mail("phil.redclift@ichameleon.com", "create pref", $query);
							$db->query($query); 	// We hope this is ok but we are not going to fail/reverse the process if not
							
							$query = "INSERT INTO store_types 
								(title, name, msv)
								VALUES
								('Single Item', 'single-item', $thisSiteID)
								";
							$db->query($query);		// Again we hope it works. We can always do it manually in TL if not. Too much done to muck around failing the process at this stage.
							
							$success_message = 'Microsite created<br />You can access it at: <a href="http://'.$_SERVER['HTTP_HOST'].'/'. $tmpdata['site_name'].'/" target="_blank">http://'.$_SERVER['HTTP_HOST'].'/'.$tmpdata['site_name'].'/</a>';
							redirect("/treeline/?".createFeedbackURL("success", $success_message));
						}
						else{
							$message[] = 'Microsite could not be saved';
							$site->delete($thisSiteID);
						}
						
					}// end IF homepage created...				
					else{ 
						$message[] = 'Site could not be created('.$OK.')!';
						
					}// END of $site->addPages();
				}		
			}

		}
		else if ($action == 'delete') {
			if ($guid>'') {
				$microsite = new Site;
				if ($microsite->loadByMicrosite($guid)) {
					if ($microsite->delete()) {
						//redirect("/treeline/sites/?".createFeedbackURL('success','Site has been successfully deleted'))
		
						// What do we want to do next ?			
						$nextsteps='<li><a href="/treeline/sites/?action=create">Create a new microsite</a></li>';
						$nextsteps.='<li><a href="/treeline/?section=sites">Manage sites</a></li>';
						$action="";
					}
					else $message[]="Could not delete site(".$microsite->name.")";
				}
				else $message[]="Failed to load microsite(".$guid.")";
			}
			else $message[]="No microsite ID passed to delete";
		}				
	}
	
	// PAGE specific HTML settings
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
		
		
			div.field span#shortcutURL {
				font-size:90%;
				float: left;
				margin-top:8px;
			}
			div.field input#shortcut {
				width:9.5em;
			}


			label.newssection {
				margin-left:140px;
				width:auto;
			}
			
			
			
			fieldset#sections select {
				width:auto;
				margin-left:2em;
			}
	
	'; // extra on page CSS
	
	$js = array('palate_preview'); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitle = ($action) ? 'Sites : '.ucwords($action) : 'Sites';
	$pageTitleH2 = $pageTitle;
	
	$pageClass = 'sites';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
			
?>

<div id="primarycontent">
<div id="primary_inner">
<?php
	echo drawFeedback($feedback,$message);

	if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");
	
	if ($action == 'create') { 
		
		?>
        <h2 class="pagetitle rounded">Create a site</h2>

		<form method="post" action="<?=$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"")?>" style="clear:left;">
		<fieldset>
		<?php
		
		$page_html = '
			<input type="hidden" name="treeline_content" value="<p>This is some temporary homepage text.<br />Please log into Treeline to replace this with your own content.</p>" />
			<p class="instructions">To create a new site, please complete the form below.</p>
			<fieldset>
				<div class="field">
					<label for="title">Title'.$help->drawSmallPopupByID(0).'</label>
					<input type="text" class="text" id="title" name="title" value="'.$title.'" maxlength="50" />
				</div>
				<div class="field">
					<label for="shortcut">Shortcut'.$help->drawSmallPopupByID(0).'</label>
					<span id="shortcutURL">http://'.$_SERVER['HTTP_HOST'].'/</span><input type="text" class="text" name="shortcut" id="shortcut" value="'.str_replace("-", " ", $shortcut).'" maxlength="25" />
				</div>
				<div class="field">
					<label for="title">Tagline'.$help->drawSmallPopupByID(0).'</label>
					<input type="text" class="text" id="tagline" name="tagline" value="'.$tagline.'" maxlength="150" />
				</div>
				<div class="field">
					<label for="meta_desc">Description'.$help->drawSmallPopupByID(0).'</label>
					<textarea name="meta_desc" id="meta_desc">'.$meta_desc.'</textarea>
				</div>
				<div class="field">
					<label for="meta_keyw">Keywords'.$help->drawSmallPopupByID(0).'</label>
					<textarea name="meta_keywords" id="meta_keyw">'.$meta_keywords.'</textarea>
				</div>
			';
		if ($site->getConfig['setup_languages']) { 
			$page_html.='
				<div class="field">
					<label for="language">Default language</label>
					'.$page->drawSelectLanguages('language',$_POST['language']).'
				</div>
			';
		}
		else $page_html.='<input type="hidden" name="language" value="en" />';
		
		$page_html.='
			</fieldset>
        ';
		$page_title="Site Title &amp; Theme";
        echo treelineBox($page_html, $page_title, "blue");

		// -------------------------------------------
		// Set up hte HTML for the contact details box.
		$page_html='       
           <fieldset>
                <p class="instructions">Who will be the person who receives emails sent from this site and from whose address enewletters are sent?</p> 
			<div class="field">
				<label for="contact">Contact form email'.$help->drawSmallPopupByID(0).'</label>
				<input type="text" class="text" name="contact" id="contact" value="'.$contact.'" />
			</div>
			<div class="field">
				<label for="contact_name">Contact form name'.$help->drawSmallPopupByID(0).'</label>
				<input type="text" class="text" name="contact_name" id="contact_name" value="'.$contact_name.'" />
			</div>
			
			<div class="field">
				<label for="contact_phone">Contact form phone'.$help->drawSmallPopupByID(0).'</label>
				<input type="text" class="text" name="contact_phone" id="contact_phone" value="'.$contact_phone.'" />
			</div>	
					
			</fieldset>
			<fieldset>
				<legend>Site Administrator</legend>
				<p class="instructions">Who will maintain this site? Please enter some information about this person:</p>
				<div class="field">
					<label for="superuser_full_name">Full name</label>
					<input type="text" class="text" name="superuser_full_name" id="superuser_full_name" value="'.$superuser_full_name.'"/>
				</div>	
				<div class="field">
					<label for="superuser_email">Email address</label>
					<input type="text" class="text" name="superuser_email" id="superuser_email" value="'.$superuser_email.'"/>
				</div>				
				<div class="field">
					<label for="superuser_name">Username</label>
					<input type="text" class="text" name="superuser_name" id="superuser_name" value="'.$superuser_name.'"/>
				</div>			
				<div class="field">
					<label for="superuser_password">Password</label>
					<input type="password" class="text" name="superuser_password" id="superuser_password" value="'.$superuser_password.'"/>
				</div>
			</fieldset>
		
			<fieldset>
				<legend>Preview Access</legend>
				<p class="instructions">Before this site goes live, or if it has been suspended, it can be viewed only be people with the correct access details</p>			
				<div class="field">
					<label for="preview_username">Username'.$help->drawSmallPopupByID(0).'</label>
					<input type="text" class="text" name="preview_username" id="preview_username" value="'.$preview_username.'"/>
				</div>			
				<div class="field">
					<label for="preview_password">Password</label>
					<input type="password" class="text" name="preview_password" id="preview_password" value="'.$preview_password.'"/>
				</div>
			</fieldset>	
		';
		echo treelineBox($page_html, "New microsite contact details", "blue");

		// Set up options
		$setupOptions = $site->drawOptions(array("palate", "font", "logo"));
		if ($setupOptions) echo treelineBox($setupOptions, "Set up options", "blue");

		// Create new sites sections HTML
		$maxChars = 40;
		$numberText = array('one'=>1, 'two'=>2, 'three'=>3, 'four'=>4, 'five'=>5, 'six'=>6, 'seven'=>7, 'eight'=>8); 
		$numberText2 = array_switch($numberText);  // this is so I can put an integer in to get the text out...

		$page_html='		
			<fieldset id="sections">
				<p class="instructions">Your site will automatically have the following sections:</p>
				<ul>
					<li><strong>Home page</strong></li>
				</ul>
				<p class="instructions">...and the following pages:</p>
				<ul>
					<li><strong>Accessibility Statement</strong></li>
					<li><strong>Contact Us</strong></li>
					<li><strong>Newsletter Subscription</strong></li>
					<li><strong>Privacy Policy</strong></li>
					<li><strong>RSS feed (most recent updates)</strong></li>
					<li><strong>Search Results</strong></li>
					<li><strong>Site Map</strong></li>
					<li><strong>Tags</strong></li>
				</ul>
				
				<p class="instructions">You can optionally enter up to '.$numberText2[$numSections].' sections for this site<br />
				You can also set what type of section you would like to use.<br />
				Sections can be added, removed and re-ordered later.</p>     
			';
			
		for($i=1; $i<=$numSections; $i++) { 
			$numTxt = $numberText2[$i];
				$page_html.='
					<div class="field">
						<label for="section_'.$i.'">Section '.$numTxt.'</label>
						<input type="text" class="text" name="section_'.$i.'" id="section_'.$i.'" maxlength="'.$maxChars.'" value="'.${'section_'.$i}.'"/>
						<br />
						<label for="section_template_'.$i.'" class="newssection">Section type:</label>
						'.$treeline->drawSectionTemplates(${'section_template_'.$i}, $append='', $name='section_template_'.$i).'
					</div>
				';
		} 
				
		$page_html.='
			</fieldset>
			
			<fieldset class="buttons">
				<input type="submit" class="submit" value="Submit" />
			</fieldset>
		';
			
		echo treelineBox($page_html, "Set up sections");
		?>
        		
		</fieldset>
		</form>
        
        <?php
	} 

	// Delete a site
	else if ($guid && $action == 'delete') { 
	
		$delsite=new Site();
		if ($delsite->loadByMicrosite($guid)) {
			$page_html = '
			<form method="post" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'">
			<fieldset>
				<p class="instructions">You are about to <strong>permanently delete</strong> this site and all associated sections, 
				pages, users and media.<br /><strong>Are you sure you want to do this?</strong> This action <strong>cannot</strong> be undone</p>
				<p class="instructions">To <strong>preview</strong> this site first, <a href="'.$delsite->link.'" target="_blank">click here</a>.</p>			
				<input type="hidden" name="action" value="delete" />
				<fieldset class="buttons">
					<input type="submit" class="submit" value="Delete" />
				</fieldset>
			</fieldset>
			</form>
			';
		}
		else $page_html = 'Could not load that microsite';
		$page_title='Delete site: '.$delsite->properties['site_title'];
		echo treelineBox($page_html, $page_title, "blue");
	}
	else if (!$guid && $action=="delete") {

		$page_html = '
		<form action="/treeline/sites/?action=delete" method="GET">
		<input type="hidden" name="action" value="delete" />
		<fieldset>
				<p class="instructions">If you want to remove an existing site please select it below. This action will NOT be reversible please ensure you are sure you wish to continue.</p>	
				<label for="guid">Current sites:</label>
				'.$site->drawSelectMicrositeList('guid', array(1)).'<br />
				<fieldset class="buttons">
					<input type="submit" class="cancel" value="Delete" />
				</fieldset>
			</fieldset>
		</form>
		';
		echo treelineBox($page_html, "Delete a microsite");

		/*
		?>
		<form method="GET" action="/treeline/sites/">
        <input type="hidden" name="action" value="delete" />
		<fieldset>
            <h2>Delete content</h2>
            <p class="instructions">To delete a site, please select it from the list below below:</p>
            <p><?=$site->drawSelectMicrositeList('guid')?></p>
            <fieldset class="buttons">
                    <button type="submit" class="cancel">Delete</button>
            </fieldset>
		</fieldset>
		</form>
		<?
		*/
	} 


	else { 
        // This is ok, probably means a site has been deleted.
	} 
	?>
    
</div>
</div>

<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>