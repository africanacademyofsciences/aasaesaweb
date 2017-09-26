<?php 
if(!$noUserInfo) { 
	?>
    <p class="userinfo"><?=$page->drawLabel("tl_head_signed", "You are signed in as")?> <strong><?=$user->getFullName()?></strong></p>
    <p class="userinfo"><?=$page->drawLabel("tl_head_access", "Your access level is")?> <strong><?=$user->drawGroup()?></strong></p>
	<?php 
	$language_link='';
	if ($site->getConfig("setup_languages")) { 	
		$language_link = '<a href="/treeline/?section=language">'.$_SESSION['treeline_user_language_title'].'</a> language';
		//$language_link = '<a href="/treeline/languages/?action=switch">'.$_SESSION['treeline_user_language_title'].'</a> language';
	}
	?>
	<p class="userinfo"><?=$page->drawLabel("tl_head_edit", "You are editting the")?> <?=$language_link?> <?=$page->drawLabel("tl_head_site", "site at")?> <a href="<?=$site->link?>" target="_blank"><?=$site->url?></a></p>
	<?php 
} 
?>
