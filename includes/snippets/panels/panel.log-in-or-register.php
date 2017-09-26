<h2><?=$page->drawLabel("bg-logp-title", "Log in or register")?></h2>
<hr />
<ul>
<li class="arrow"><a href="<?=$site->link?>member-login/"><?=$page->drawLabel("bg-logp-login", "Log in")?></a> <?=$page->drawLabel("bg-logp-update", "to update your blog")?></li>
<li class="arrow"><a href="<?=$site->link?>enewsletters/?blog=1"><?=$page->drawLabel("bg-logp-apply", "Apply")?></a> <?=$page->drawLabel("bg-logp-forblog", "for a ".$site->name." blog")?></li>
</ul>