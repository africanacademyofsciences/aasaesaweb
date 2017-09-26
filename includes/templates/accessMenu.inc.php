<?php 	
	//print "got site url({$site->url})<br>";
	//print_r($site->versions);
	//print "<br># versions(".count($site->versions).")<br>";
	
	// Collect html for flag list
	if (count($site->versions)>1) {
		foreach ($site->versions as $version) {
			$flagpic=''; $flaglink=''; $flagselected='';
			//print "got version msv({$version['msv']}  title({$version['title']}  flag({$version['flag']}<br>";
			//list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/images/languages/'. $site->properties['language'] .'.gif');
			$flagselected=($version['lang']==$site->lang)?" selected":"";
			if ($version['flag']) {
				$flagfile=$_SERVER['DOCUMENT_ROOT']."/img/flags/".strtolower($version['flag']);
				if (file_exists($flagfile)) {
					$flagpic='<img src="/img/flags/'.strtolower($version['flag']).'" class="'.$flagselected.'" alt="'.$version['title'].'" height="12" border="0" />';
				}
			}
			if (!$flagpic) {
				$flagpic='<span class="nopic'.$flagselected.'">['.$version['title'].']</span>';
			}
			$flaglist.='<li><a href="'.$site->root.$site->name.'/'.$version['lang'].'/" class="flag" title="go to '.$version['title'].' site">'.$flagpic.'</a></li>';
			
			$flagselect .='<option value="'.$version['lang'].'"'.($flagselected?' selected="selected"':"").'>'.$version['title'].'</option>';
		}
		$flaglist='<ul id="flaglist">'.$flaglist.'</ul>';

		// Generate automatically once we have site versions
		$flagselect = '<form id="f-country-select" method="post" action="">
			'.$labels['seesitein']['txt'].'
			<select name="switch-lang" onchange="document.location=\'/'.$site->name.'/\'+this.value">
				'.$flagselect.'
			</select>
		</form>
		';

	}
	else $flaglist='';


	//print "flags($flaglist) select($flagselect)<br>\n";
?>
<ul id="accessMenu" dir="<?=$site->properties['ltr']?>">
<?php 

	if( $mode == 'edit' || $mode=="preview") { 
		?>
        <li><a href="#" title="These links are not clickable in edit mode">Placeholder link</a></li>
        <li><a href="#" title="These links are not clickable in edit mode">Placeholder link</a></li>
        <li><a href="#" title="These links are not clickable in edit mode">Placeholder link</a></li>
    	<?php 
	} 
	else { 

		?> 
		<li style="direction:<?=$site->properties['ltr']?>" id="accessibility-statement-link"><a href="<?=$siteLink?>accessibility-statement/"><?=$page->drawLabel('accessibility-link', 'Accessibility')?></a></li>
		<li style="direction:<?=$site->properties['ltr']?>"><a href="<?=CURRENT_PAGE?>?toggle_graphics" rel="nofollow" title="Toggle images"><?=($_COOKIE['graphics_mode']=='low')?$page->drawLabel('textimages-link', 'Text and images'):$page->drawLabel('textonly-link', 'Text only')?></a></li>
		<?php

		// Check if forum/blogs are required
		$query = "SELECT guid, template_php FROM pages p 
			LEFT JOIN pages_templates pt ON p.template=pt.template_id 
			WHERE pt.template_php in ('forum.php', 'blogs.php') 
			AND p.msv=".$site->id;
		//print "$query<br>\n";
		$results = $db->get_results($query);
		foreach($results as $result) {
			if ($result->template_php=="forum.php") $forum_guid=$result->guid;
			if ($result->template_php=="blogs.php") $blogs_guid=$result->guid;
		}
		if ($site->getConfig("setup_forum") && $forum_guid) {
			?>
			<li style="direction:<?=$site->properties['ltr']?>"><a href="<?=$page->drawLinkByGUID($forum_guid)?>" rel="nofollow" title="Visit site forums">Forum</a></li>
			<?php
		} 
		if ($site->getConfig("setup_blogs") && $blogs_guid) {
			?>
			<li style="direction:<?=$site->properties['ltr']?>"><a href="<?=$page->drawLinkByGUID($blogs_guid)?>" rel="" title="Visit site blogs">Blogs</a></li>
			<?php
		} 


		// Member access
		if ($site->getConfig("setup_members_area")) {
			if ($_SESSION['member_id']>0 && $_SESSION['member_site_id']==$site->id) { 
				?>
				<li style="direction:<?=$site->properties['ltr']?>"><a href="<?=$site->link?>member-login/"><?=$page->drawLabel('myaccount','My account')?></a></li>
				<li style="direction:<?=$site->properties['ltr']?>"><a href="<?=$site->link?>member-login/?action=logout"><?=$page->drawLabel('logout', 'Log out')?></a></li>
				<?php 
			} 
			else { 
				?>
				<li style="direction:<?=$site->properties['ltr']?>"><a href="<?=$site->link?>member-login/">Log in</a></li>
				<?php 
			} 
		}
		    
		/*
		<!-- 
		USE GOOGLE TO TRANSLATE THIS PAGE
		<li><script src="http://www.gmodules.com/ig/ifr?url=http://www.google.com/ig/modules/translatemypage.xml&up_source_language=en&w=160&h=60&title=&border=&output=js"></script></li>
		-->
		*/
	

		// Show site language select option
		if ($flagselect) { 
			?>
            <li style="direction:<?=$site->properties['ltr']?>;" id="flags-select"><?=$flagselect?></li>
            <?php
        } 
		if ($flaglist) { 
			?>
            <li style="direction:<?=$site->properties['ltr']?>;" id="flags-list"><?=$flaglist?></li>
        	<?php 
		} 
	
	}
	?> 
</ul>

