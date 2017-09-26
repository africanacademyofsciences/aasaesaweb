<?php 

// Prefill campaign if we are in edit mode.
$campaign=$db->get_row("SELECT * FROM campaigns WHERE id = ".$campaign_id);
$title = $_SERVER['REQUEST_METHOD']=="POST"?$_POST['title']:$campaign->title;
$newsletter_id = $newsletter_id?$newsletter_id: $campaign->newsletter_id;

$newsopts='';
foreach($newsletters as $newsletter){
	if(!empty($newsletter->subject)){ 
		if (($newsletter_id==$newsletter->id) || !$newsletter->campaign_id) {
			$newsopts.='<option value="'.$newsletter->id.'" '.(($newsletter_id==$newsletter->id)? 'selected="selected"' : '').'>'.$newsletter->subject.'</option>'."\n";
		}
	} 
} 

?>


<form action="<?=$_SERVER['PHP_SELF']?>" method="post" id="frmCampaign">
<fieldset>
    <p class="instructions">Fields marked * are required.</p>
    <input type="hidden" name="id" value="<?=$campaign_id?>" />
    <input type="hidden" name="newsletter" value="<?=$newsletter_id?>" />
    <input type="hidden" name="action" value="<?=$action?>" />
    <input type="hidden" name="old_title" value="<?=$title?>" />
    
    <label for="title" class="requried">Title:</label>
    <input type="text" id="title" name="title" maxlength="255" size="40" value="<?=$title?>" class="requried" />

    <label class="" for="newsletters">Campaign newletter:</label>
    <select id="newsletters" name="newsletter" <?=($action=="edit"?'disabled="disabled"':"")?>>
        <option value="">select a newsletter</option>
        <?=$newsopts?>
    </select>
	
	<label for="f_submit" style="visibility:hidden;">submit</label>
    <input type="submit" id="f_submit" name="save" class="submit" value="Save" />        
    
</fieldset> 
</form>
