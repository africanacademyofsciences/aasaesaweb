

<form method="post" class="std-form" id="blog_comment">
<fieldset class="border">
	<p>All comments must be approved by the blog owner before they appear on the site.</p>
	<input type="hidden" name="action" value="comment" />
	<input type="hidden" name="bid" value="<?=$blogs->blog['id']?>" />
	<fieldset>
		<label for="f_name" class="required">Full name</label>
		<input type="text" id="f_name" name="fullname" value="<?=$_POST['fullname']?>" />
    </fieldset>
	<fieldset>
		<label for="f_email" class="required">Email address</label>
		<input type="text" id="f_email" name="email" value="<?=$_POST['email']?>" />
    </fieldset>
    <fieldset>
		<label for="f_comment" class="required">Your comment</label>
		<textarea name="comment" id="f_comment"><?=$_POST['comment']?></textarea>
    </fieldset>
    <?=($site->getConfig("setup_use_captcha")?$captcha->drawForm():'')?>
    <fieldset>
		<label for="f_submit" style="visibility:hidden;">Submit</label>
	    <input type="submit" id="f_submit" value="Post comment" />
    </fieldset>
</fieldset>
</form>