<div class="panel panel_1">
    <h3>Tools for this page</h3>
    <p><a class="arrow" href="javascript:addBookmark('<?=$page->drawLinkByGUID($page->getGUID())?><?=($revid!=0?"?revid=".$revid:"")?>', '<?=$page->getTitle()?>');">Bookmark this page</a></p>
    <p><a class="arrow" href="/send-to-friend/">Tell a friend about this page</a></p>
    <?php if ($page->getTemplate()=="event_page.php" && !$_SESSION['user_logged_in']) { ?>
	    <p><a class="arrow" href="<?=$page->drawLinkByGUID($page->getGUID())?>?action=showlogin">Log in to edit your own blog</a></p>
	<?php } ?>
    <!-- <p><?=$page->getTemplate()?></p> -->
</div>
