<h2>Newsletters</h2>
<p>Sign up for our regular bulletin</p>
<?php if( $mode!='edit' ){ ?>
<form id="newslettersForm" action="<?= ($siteID==1 ? '' : '/'.$site->properties['site_name']) ?>/enewsletters/" method="post">
    <fieldset>
        <label for="name">Name</label><input type="text" id="name" name="name" />
        <label for="email">Email</label><input type="text" id="email" name="email" />
        <a href="<?= ($siteID==1 ? '' : '/'.$site->properties['site_name']) ?>/privacy-policy/" style="clear: both">Privacy policy</a> <button type="submit">Sign up</button>
    </fieldset>
</form>
<?php }else{ ?>
<p><img src="/images/editmode/newsletters.gif" alt="" /></p>
<?php } ?>