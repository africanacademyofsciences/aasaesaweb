<?php
$page_html = '
<form id="requestsForm" action="'.$_SERVER['REQUEST_URI'].'" method="post">
    <fieldset>
        <p class="instructions">Fill in all the details below and press the Submit request button. <br />
We will respond as quickly as we can.</p>
		<div class="field">
	        <label for="title" class="required">Title:</label>
        	<input type="text" value="'.$_POST['title'].'" id="title" name="title" class="required" /><br />
		</div>
		<div class="field">
	        <label for="description" class="required">Description:</label>
    	    <textarea id="description" name="description" rows="10" cols="10" class="required">'.$_POST['description'].'</textarea><br />
        </div>
		<input type="hidden" name="action" value="'.$action.'" />
        <fieldset class="buttons">
            <input type="submit" class="submit" value="Send request" />
        </fieldset>	
    </fieldset>
</form>
<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_adminitems.js"></script>
';
?>
