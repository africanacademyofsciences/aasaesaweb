<?php

/*
	REMOVE PLUGIN FORM
	
*/

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$event->delete($eventId);
	}

?>
<form id="deleteForm" action="" method="post">
    <fieldset>
        <legend>Delete event?</legend>
        <p class="instructions">Are you sure you want to delete this event?</p>
        <fieldset class="buttons">
            <button type="submit" class="submit">Yes</button>
        </fieldset>	
    </fieldset>
</form>
