<?php

/*
	REMOVE PLUGIN FORM
	
*/

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$event->approve($eventId);
	}

?>
<form id="approveForm" action="" method="post">
    <fieldset>
        <legend>Approve event?</legend>
        <p class="instructions">Are you sure you want to approve this event?</p>
        <fieldset class="buttons">
            <button type="submit" class="submit">Yes</button>
        </fieldset>	
    </fieldset>
</form>
