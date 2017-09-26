<form id="requestsForm" action="<?=$_SERVER['PHP_SELF']?>" method="post">
    <fieldset>
        <legend>Send a request</legend>
        <p class="instructions">Fill in all the details below and press the Submit Request button. We'll respond as quickly as we can.</p>
        <label for="title" class="required">Title:</label>
        <input type="text" value="<?=$_POST['title']?>" id="title" name="title" class="required" /><br />
        <label for="description" class="required">Description:</label>
        <textarea id="description" name="description" rows="10" cols="10" class="required"><?=$_POST['description']?></textarea><br />
        <input type="hidden" name="action" value="<?=$action?>" />
        <fieldset class="buttons">
            <button type="submit" class="cancel">Cancel</button>
            <button type="submit" class="submit">Submit Request</button>
        </fieldset>	
    </fieldset>
</form>