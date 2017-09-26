<?php

	/*
		DELETE ITEM FORM
		
	*/

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if($_POST['action'] == 'delete'){
			$plugin->deletePage($page_id);
		}
	}

?>
<form id="deleteForm" action="" method="post">
    <fieldset>
        <legend>Delete <?=$results['title']?>?</legend>
        <p class="instructions">Are you sure you want to delete the landing page section <em><?=$results['title']?></em>?</p>
        <input type="hidden" name="page_id" value="<?=$page_id?>" />
        <input type="hidden" name="action" value="<?=$action?>" />
        <fieldset class="buttons">
            <button type="submit" class="submit">Yes</button>
        </fieldset>	
    </fieldset>
</form>
<hr />
<p><a href="./?page_id=<?=$page_id?>">No I don't want to delete this panel after all</a>.</p>

