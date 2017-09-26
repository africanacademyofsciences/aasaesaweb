
<?php if ($page->getMode()=="edit") { ?>


	<p>Form disabled in edit mode</p>


<?php } else { ?>
        <form id="form-email-news" method="post" action="<?=$siteLink?>enewsletters/" class="homepage-form" >
        <fieldset>
	        <input type="hidden" name="homelink" value="1" />
            <label for="email-name"><?=$labels['name']['txt']?></label>
            <input type="text" name="name" id="email-name" class="text" />
            <label for="email-email"><?=$labels['email']['txt']?></label>
            <input type="text" name="email" id="email-email" class="text noclear" />
			<a class="privacy-link" href="<?=$siteLink?>privacy-policy/"><?=$labels['privacy']['txt']?></a>            
            <input type="submit" name="submit" value="<?=$labels['register-button']['txt']?>" class="submit noclear" />
        </fieldset>
        </form>     


<?php } ?>
