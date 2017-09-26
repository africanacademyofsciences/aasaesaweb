<?php
$page_html = '
<form id="feedbackForm" action="'.$_SERVER['PHP_SELF'].'" method="post">
    <fieldset>
        <legend>Give Feedback</legend>
        <p class="instructions">Fill in all the details below and press the Send Feedback button. <br />
We will respond as quickly as we can.</p>
		<div class="field">
			<label for="title" class="required">Title:</label>
			<input type="text" value="'.$_POST['title'].'" id="title" name="title" class="required" />
		</div>
		<div class="field">
			<label for="description" class="required">Description:</label>
			<textarea id="description" name="description" rows="10" cols="10" class="required">'.$_POST['description'].'</textarea>
        </div>
		<input type="hidden" name="action" value="'.$action.'" />
        <fieldset class="buttons">
            <button type="button" class="cancel">Cancel</button>
            <button type="submit" class="submit">Send Feedback</button>
        </fieldset>	
    </fieldset>
</form>
<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_adminitems.js"></script>
';
?>