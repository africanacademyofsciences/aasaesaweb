<?php

/*
	REMOVE PLUGIN FORM
	
*/

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$plugin->delete($pluginId);
	}

?>
<form id="deleteForm" action="" method="post">
    <fieldset>
        <legend>Delete plugin?</legend>
        <p class="instructions">Are you sure you want to delete this plugin?</p>
        <fieldset class="buttons">
            <button type="submit" class="submit">Yes</button>
        </fieldset>	
    </fieldset>
</form>
