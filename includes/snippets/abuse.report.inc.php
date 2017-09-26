<?php

if ($name=="forum") $abuse_id=$post_id;
else if ($name=="blogs") $abuse_id = $bid;
?>


<form method="post" id="abuse-report" class="std-form">
<fieldset class="border">
   	<legend>Submit abuse report</legend>
    <input type="hidden" name="action" value="<?=$action?>" />
    <input type="hidden" name="bid" value="<?= ($abuse_id+0) ?>" />
    <input type="hidden" name="post" value="<?= ($abuse_id+0) ?>" />

	<?php 
	if ($_SESSION['member_id']>0 && $email=$db->get_var("SELECT email FROM members WHERE member_id = ".$_SESSION['member_id'])) {
		?>
	    <input type="hidden" name="email" value="<?=$email?>" />
        <?php
	}
	else {
		?>
        <fieldset class="field">
            <label for="f_email">Your email</label>
            <input type="text" name="email" class="text" id="femail" value="<?=($_POST['email'])?>" />
        </fieldset>
		<?php
	}
	
	if ($site->getConfig("setup_use_captcha")) echo $captcha->drawForm();
	
	?>
        
	    
    <fieldset class="field buttons">
	    <label for="submit-button" style="visibility:hidden;">submit</label>
        <input name="" id="submit-button" class="submit" value="Report" type="submit" />
    </fieldset>

</fieldset>
</form> 	
</form>