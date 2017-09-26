<form action="<?= $formAction ?>" method="post" id="forum_edit" class="std_form">
<fieldset class="border">
   	<legend><?=($term=="post"?"Enter your response below":"Add a new thread")?></legend>
    <input type="hidden" name="action" value="<?= $action ?>" />
    <input type="hidden" name="forum_parent_id" value="<?= $post_id ?>" />
    <input type="hidden" name="post" value="<?= $post_id ?>" />

	<?php 
	if ($term=="post") { 
		?>
        <input type="hidden" name="forum_title" value="RE: <?=$db->get_var("SELECT title FROM forum_posts WHERE post_id = ".($post_id+0))?>"  />
        <?php
	}
	else {
		?>
        <fieldset class="field">
            <label for="f_title">Title</label>
            <input type="" name="forum_title" id="f_title" value="" />
        </fieldset>
		<?php
	}
	?>
            
    <fieldset class="field">
	    <label for="forum_message">Message</label>
    	<textarea name="forum_message" id="forum_message"><?= $forum_message ?></textarea>
    </fieldset>

    <fieldset class="buttons">
	    <label for="submit-button" style="visibility:hidden;">submit</label>
        <input name="account_function" id="submit-button" class="submit" value="save" type="submit" />
    </fieldset>

</fieldset>
</form> 