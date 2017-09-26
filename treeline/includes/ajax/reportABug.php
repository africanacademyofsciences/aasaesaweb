<?php

	if($_SERVER['REQUEST_METHOD'] == 'POST'){ // Create/post new item
		$bug->createItem($itemType);
	}

?>
<script type="text/javascript" src="/treeline/includes/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/js/tiny_mce/tiny_mce_adminItems.js"></script>
<?//=drawFeedback($feedbackClass,$message)?>
<form id="bugsForm" action="<?=$_SERVER['PHP_SELF']?>" method="post">
    <fieldset>
        <legend>Report a bug</legend>
        <p class="instructions">Fill in all the details below and press the Report bug button. <br />
We'll respond as quickly as we can.</p>
        <label for="title" class="required">Title:</label>
        <input type="text" value="<?=$_POST['title']?>" id="title" name="title" class="required" /><br />
        <label for="description" class="required">Description:</label>
        <textarea id="description" name="description" rows="10" cols="10" class="required"><?=$_POST['description']?></textarea><br />
        <input type="hidden" name="action" value="<?=$action?>" />
        <fieldset class="buttons">
            <button type="button" class="cancel">Cancel</button>
            <button type="submit" class="submit">Report Bug</button>
        </fieldset>	
    </fieldset>
</form>