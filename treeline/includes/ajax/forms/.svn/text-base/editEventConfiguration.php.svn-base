<?php

	if ($guid) {
		$evt_config=$db->get_row("select * from event_config where guid='$guid'");
		if (!$evt_config) {
			$db->query("insert into event_config(guid) values ('$guid')");
			$evt_config=$db->get_row("select * from event_config where guid='$guid'");
		}	
		
		foreach ($evt_config as $field=>$value) {
			if (substr($field, 0, 4)=="chk_") {
				$this_section=substr($field, 4);
				//print "got field($field) value($value) evt->config val(".${$evt_config->$")<br>";
				$sectionList.='<input type="hidden" name="chk_h_'.$this_section.'" value="1" />';
				$sectionList.='<fieldset class="noborder">
	<input class="check" id="f_chk_'.$this_section.'" type="checkbox" name="chk_'.$this_section.'" value="1" '.($value==1?"checked":"").'>
	<label for="f_chk_'.$this_section.'">'.ucfirst($this_section).'</label>
	</fieldset>
	';
			}
		}
		
		if ($results=$db->get_results("select id, description from event_config_tnc where guid='$guid' order by sort_order")) {
			foreach($results as $result) {
				$conditionsList.='<p><input style="margin-left:0px;" type="checkbox" value="1" name="tnc_d_'.$result->id.'" />'.str_replace("<p>","",str_replace("</p>","",$result->description)).'</p>';
			}
		}
					
			
	}	
	
?>
<fieldset id="evt_config">
<input type="hidden" name="guid" value="<?=$guid?>" />

<h4>Visible sections</h4>
<p>Sections to display on the registration form</p>
<?=$sectionList?>

<h4>Terms and conditions</h4>
<p>Add text below to create a new line in the terms and conditions section. Each line will require the member to click a box to agree to this entry. You can only add one line to the form at a time.</p>
<label for="f_tnc">New condition</label>
<textarea class="mceEditor" id="f_tnc" name="tnc"></textarea><br />
<p>Below are the currently set up conditions for this event<br />
Check the box next to any condition to remove it from the list</p>
<?=$conditionsList?>

<!--
<h4>Passport text</h4>
<label for="f_passport">Enter text to appear in password section of the form.</label>
<textarea id="f_passport" name="passport"><?=$evt_config->passport?></textarea><br />
-->

<fieldset class="buttons">
    <label for="submit" style="visibility:hidden">Submit:</label>
    <input type="submit" class="submit" name="action" value="Save register form" />
</fieldset>
</fieldset>