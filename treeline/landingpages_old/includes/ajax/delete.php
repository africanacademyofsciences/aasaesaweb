<?php

	/*
		DELETE ITEM FORM
		
	*/

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if($_POST['action'] == 'delete'){
			$plugin->delete($id);
		}
	}

?>
<form id="deleteForm" action="" method="post">
    <fieldset>
        <legend>Delete <?=$results['title']?>?</legend>
        <p class="instructions">Are you sure you want to delete the landing page <?=$results['title']?>?</p>
        <input type="hidden" name="id" value="<?=$id?>" />
        <input type="hidden" name="action" value="<?=$action?>" />
        <fieldset class="buttons">
            <button type="submit" class="submit">Yes</button>
        </fieldset>	
    </fieldset>
</form>
<hr />
<p><a href="./?id=<?=$id?>">No I don't want to delete this landing page after all</a>.</p>
