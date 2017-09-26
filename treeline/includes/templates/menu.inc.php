<?php 

/*
	TREELINE NAVIGATION MENUU
*/
if(!$noMenu && $user) {  // only show mneu onn pages where users are logged in.
?>
<ul id="menu" class="column">

<li id="home-link"><a href="/treeline/" accesskey="1"><?=$page->drawLabel("tl_menu_home", "Home")?></a></li>

<?php if ($tasks->total>0) { ?>
<li id="tasks-link"><a href="/treeline/tasks/" accesskey="1"><?=$page->drawLabel("tl_menu_tasks", "My tasks")?>(<?=$tasks->total?>)</a></li>
<?php } ?>
  
<li id="create-content-link"><a href="/treeline/?section=create-content"><?=$page->drawLabel("tl_menu_create", "Create content")?></a></li>
<li id="edit-content-link"><a href="/treeline/?section=edit-content"><?=$page->drawLabel("tl_menu_edit", "Manage existing content")?></a></li>
<li id="edit-assets-link"><a href="/treeline/?section=edit-assets"><?=$page->drawLabel("tl_menu_asset", "Manage asset libraries")?></a></li>
<?php if ($_SESSION['treeline_user_group']!="Author") { ?>
<li id="edit-structure-link"><a href="/treeline/?section=edit-structure"><?=$page->drawLabel("tl_menu_structure", "Manage site structure")?></a></li>
<?php } ?>
	<li><br /></li>

<?php 
if($_SESSION['treeline_user_group']!='Author' && $site->getConfig("setup_newsletters")){  
	?>
	<li id="newsletters-link"><a href="/treeline/newsletters/"><?=$page->drawLabel("tl_menu_newsletter", "Newsletters")?></a></li>
	<?php
} 
    

if($site->getConfig("setup_members_area") && $_SESSION['treeline_user_group']!="Author"){ 
	?><li id="members-link"><a href="/treeline/members/"><?=$page->drawLabel("tl_menu_members", "Members")?></a></li><?php 
} 

if($site->getConfig("setup_events") && $_SESSION['treeline_user_group']!="Author"){ 
	?><li id="events-link"><a href="/treeline/events/"><?=$page->drawLabel("tl_menu_events", "Manage Events")?></a></li><?php 
} 
	
if ($site->config['setup_worldmap']) { 
	?><li id="map-link"><a href="/treeline/map/"><?=$page->drawLabel("tl_menu_worldmap", "Worldmap")?></a></li><?php 
}

// ------------------------------------------------------------------------------
// Store is available on this site
if ($site->getConfig('setup_store')) {
	// And can be viewed by the main site and any microsites with access
	if ($site->id==1 || $site->getConfig("site_store")) { 
		if ($storeVersion=="v1") {
			?>
			<li id="store_inventory-link"><a href="/treeline/store/<?=$storeVersion?>/inventory.php"><?=$page->drawLabel("tl_menu_store_inv", "Store - Inventory")?></a></li>
			<?php 
			// You must be an admin though to see orders and configuration
			if ($_SESSION['treeline_user_group']!="Author") { 
				?>
				<li id="store_order-link"><a href="/treeline/store/<?=$storeVersion?>/orders.php"><?=$page->drawLabel("tl_menu_store_order", "Store - Orders")?></a></li>
				<li id="store_config-link"><a href="/treeline/store/<?=$storeVersion?>/config.php"><?=$page->drawLabel("tl_menu_store_config", "Store - Configuration")?></a></li>
				<?php 
			}
		}
		else if ($storeVersion=="v2") {
			?>
			<li id="store-link"><a href="/treeline/?section=store"><?=$page->drawLabel("tl_menu_store", "Store")?></a></li>
			<?php 
		}
		?>
		<li><br /></li>
		<?php 
	}
} 
// ------------------------------------------------------------------------------



if ($site->config['setup_forum'] && $_SESSION['treeline_user_group']!="Author") { 
	?>
    <li id="forums-link"><a href="/treeline/forums/"><?=$page->drawLabel("tl_menu_forums", "Manage Forums")?></a></li>
	<?php 
} 



// ------------------------------------------------------------------------------
// Blogs are available to supers and publishers if blogs are enabled on this site
if ($site->config['setup_blogs'] && $_SESSION['treeline_user_group']!="Author") {
	// But only for the main site or sites that are specifically allowed 
	if ($site->id==1 || $site->getConfig("site_blogs")) {
		?><li id="blogs-link"><a href="/treeline/blogs/"><?=$page->drawLabel("tl_menu_blogs", "Manage Blogs")?></a></li><?php
	}
} 
// ------------------------------------------------------------------------------


// ------------------------------------------------------------------------------
// Microsites are only ever enabled on the main site
if($_SESSION['treeline_user_group']=='Superuser' && $site->id==1 && $site->getConfig("setup_microsites")){  
	?>
	<li id="microsite-link"><a href="/treeline/?section=sites"><?=$page->drawLabel("tl_menu_sites", "Sites")?></a></li>
	<?php 
}
// ------------------------------------------------------------------------------


// ------------------------------------------------------------------------------
// Languages only availalbe to microsites that have permission
if($_SESSION['treeline_user_group']!='Author' && $site->getConfig("setup_languages")) {  
	if ($site->id==1 || $site->getConfig("site_languages")) {
		?>
		<li id="language-link"><a href="/treeline/?section=language"><?=$page->drawLabel("tl_menu_languages", "Languages")?></a></li>
		<?php
	}
}
// ------------------------------------------------------------------------------


// ------------------------------------------------------------------------------
// Show the form editor
if ($site->id==$_SESSION['treeline_user_default_site_id'] && $_SESSION['treeline_user_group']=="Superuser" && $site->getConfig("setup_forms")) {
	// Forms only available to the main site and any sites that have permission
	if ($site->id==1 || $site->getConfig("site_forms")) {
		?>
		<li id="forms-link"><a href="/treeline/forms/"><?=$page->drawLabel("tl_menu_forms", "Manage forms")?></a></li>
		<?php
	}
}
// ------------------------------------------------------------------------------


if ($site->id==$_SESSION['treeline_user_default_site_id']) {
	?>
    <li id="access-link"><a href="/treeline/?section=access"><?=$page->drawLabel("tl_menu_access", "Access Rights")?></a></li>
	<?php
}


if($_SESSION['treeline_user_group']=='Superuser') {  
    ?>
    <li id="settings-link"><a href="/treeline/?section=settings"><?=$page->drawLabel("tl_menu_settings", "Settings")?></a></li>
	<?php 
}  
?>
    
<li id="help-link"><a href="/treeline/help/"><?=$page->drawLabel("tl_menu_help", "Help and support")?></a></li>			

<?php
if($_SESSION['treeline_user_group']!='Author') {  
	?>
    <li id="statistics-link"><a href="/treeline/stats/"><?=$page->drawLabel("tl_menu_stats", "Statistics")?></a></li>
	<?php
}
?>

<li id="logout-link"><a href="/treeline/?lo=1"><?=$page->drawLabel("tl_menu_logout", "Logout")?></a></li>
<li id="menu-bottom-spacer"></li>
</ul>
<?php } ?>
