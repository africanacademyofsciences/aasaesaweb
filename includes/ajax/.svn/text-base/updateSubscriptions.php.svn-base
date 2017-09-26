
<p class="instructions">Please select the type of email news you would like to receive below.</p>

<?php 
	$subs_html = Newsletter::drawPreferences($site->id);
	if ($subs_html) {
	?>
    <div id="subslist">
        <?php //echo Newsletter::listPreferences("", $_SESSION['member_id']); ?>
        <form method="post" id="manage-subscriptions" class="contact">
        <fieldset class="border">
        	<?=$subs_html?>
            <input type="submit" name="action" value="Update" class="submit" />
        </fieldset>
        </form>
    </div>
    <?php
	}
	else {
		?>
  		<p><strong>This site has not configured any newsletters yet.</strong></p>
        <?php
	}
?>
