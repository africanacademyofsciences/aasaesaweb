<?php

/*
	APPROVE PROSPECTIVE MEMBER FORM
	
*/

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$member->approve($memberId);
	}

?>
<form id="approveMemberForm" action="" method="post">
  <fieldset>
  <legend><?php echo 'Approve '.$result->firstname.' '.$result->surname.'?'; ?></legend>
        <p class="instructions">Are you sure you want to <strong>approve</strong> <?php echo $result->firstname.' '.$result->surname.'?'; ?></p>
  <fieldset class="buttons">
  <button type="submit" class="submit">Yes</button>
        </fieldset>	
    </fieldset>
</form>