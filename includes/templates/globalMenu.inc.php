<ul id="globalMenu">
	<?php if( $mode == 'edit' || $mode=="preview") { ?>
    <li><a href="#" title="This link is not clickable in edit mode">Placeholder link</a></li>
    <li><a href="#" title="This link is not clickable in edit mode">Placeholder link</a></li>
    <li><a href="#" title="This link is not clickable in edit mode">Placeholder link</a></li>
    <li><a href="#" title="This link is not clickable in edit mode">Placeholder link</a></li>
    <li><a href="#" title="This link is not clickable in edit mode">Placeholder link</a></li>
	<?php } else { ?>
    <li id="site-map-link"><a href="<?=$siteLink?>sitemap/"><?=$page->drawLabel('sitemap', "Sitemap")?></a></li>
    <li id="subscribe-newsletter-link"><a href="<?=$siteLink?>enewsletters/"><?=$page->drawLabel('subscribe-link',"Subscribe")?></a></li>
    <li id="rss-feed-link"><a href="<?=$siteLink?>rss/"><?=$page->drawLabel('rss',"RSS")?></a></li>
    <li id="send-to-friend-link"><a xml:lang="<?=$siteLang?>" href="<?=$siteLink?>send-to-friend/?page=<?=str_replace("&","_AMP_", $_SERVER['REQUEST_URI'])?>"><?=$page->drawLabel('send2friend',"Send to friend")?></a></li>
    <li id="contact-us-link"><a href="<?=$siteLink?>contact-details/"><?=$page->drawLabel('contact', "Contact")?></a></li>
	<?php } ?>
</ul>

<div id="search-form-div">
	<?php 
	if ($mode != "edit" && $mode!="preview") { 
		?>
        <form action="<?=$site->link?>search/" id="form-search" method="get" class="">
        <fieldset>
            <input type="text" name="keywords" class="text" />
            <input type="submit" value="<?=$page->drawLabel('search', 'Search')?>" class="submit" />
            <p class="advanced-link"><a href="<?=$site->link?>search/?adv"><?=$page->drawLabel("search-adv", "Advanced search options")?></a></p>
        </fieldset>
        </form>
		<?php 
	} 
	else {
		?>
        <p id="searchForm">
        <?php
		if (file_exists($_SERVER['DOCUMENT_ROOT']."/images/editmode/search.gif")) {
			?>
			<img src="/images/editmode/search.gif" alt="" />
			<?php 
		}
		?>
        </p>
        <?php
	}
	?>
</div>
