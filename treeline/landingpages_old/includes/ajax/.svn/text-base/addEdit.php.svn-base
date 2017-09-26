<?php

	/*
	
		ADD/EDIT ITEM
	
	*/
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/landingpages/includes/landingpage.class.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/landingpages/includes/functions.php");
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/personalStory.class.php');
	// Personal Story
	$query = "SELECT story_guid FROM pages_stories_relationship WHERE guid = '".$results['guid']."' LIMIT 1";
	$result = $db->get_row($query);
	$has = (isset($result->story_guid)) ? true : false;
	$story_guid = read($_POST,'story_guid',$result->story_guid);
	$story = new PersonalStory($story_guid); // this would not normally have a GUID specified
	$stories = $story->getStoriesList();
	
	// VARIABLES
	$style = read($_POST,'style',$results['style']);
	$banner_image = read($_POST,'banner_image',$results['banner_image']);
	$content = read($_POST,'content',$results['content']);
	$donate = read($_POST,'donate',$results['donate']);
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		
		if($_POST['action'] == 'add' || $_POST['action'] == 'create'){
			// Update story panel associated with this page
			$query = ($has == true) ? "UPDATE pages_stories_relationship SET story_guid = '$story_guid' WHERE guid = '{$results['guid']}' LIMIT 1;" : "INSERT INTO pages_stories_relationship VALUES('{$results['guid']}', '$story_guid');";
			$db->query($query);
			$message = $plugin->add();
		}
		else if($_POST['action'] == 'edit'){
			// Update story panel associated with this page
			$query = ($has == true) ? "UPDATE pages_stories_relationship SET story_guid = '$story_guid' WHERE guid = '{$results['guid']}' LIMIT 1;" : "INSERT INTO pages_stories_relationship VALUES('{$results['guid']}', '$story_guid');";
			$db->query($query);
			$message = $plugin->edit($id);
		}
	}

?>
<h2>Step 2: Choose landing page appearance and add introductory content</h2>
<p><a href="includes/ajax/help.php?helpsection=create&amp;mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=400&amp;width=720" class="thickbox">Step 2? What was step 1?</a></p
><form id="<?=$action?>Form" action="" method="post">
    <fieldset>
        <legend><?= ($action == 'add' || $action == 'create') ? 'Add a new '.$pluginName : 'Edit '.$results['title']; ?></legend>
        <fieldset>
        <legend>Introductory content</legend>
        <p class="instructions"><?= ($action == 'add' || $action == 'create') ? 'Add your' : 'Edit this landing page\'s'; ?> introductory content which can include images, text and links</p>
        <label for="content">Content:</label>
        <textarea id="content" name="content" class="mceEditor" rows="5" cols="5"><?=$content;?></textarea><br />
    	<br />
		<fieldset>
	       	<legend>Donate button?</legend>
				<p class="instructions">Note: The donate button will only show with style 1.</p>
                <input type="checkbox" class="checkbox" name="donate" id="donate" value="1"<?=($donate == 1)?' checked="checked"':''?> />
                <label for="donate" class="checklabel">Include donate button?</label><br />
		</fieldset>
		<fieldset>
        	<legend>Banner image</legend>
            <p class="instructions">If you choose style 2 for this landing page you must add a banner image.</p>
            <label for="content" class="hide">Banner image:</label>
        	<textarea id="banner_image" name="banner_image" class="mceEditor" rows="5" cols="5"><?=$banner_image;?></textarea><br />
        </fieldset>
        <fieldset>
        	<legend>Choose appearance</legend>
            <p class="instructions">Choose how your landing page will appear. Pick one of style options below.</p>
        	<?php
				echo drawStyleCheckboxes('page',$style);
			?>
        </fieldset>
        <fieldset>
        	<legend>Choose Story</legend>
            <p class="instructions">If you have picked style 2 you can pick a personal story to display in the sidebar of this landing page.</p>

            <label for="story_guid">Story:</label>
            <select name="story_guid" id="story_guid">
                <option value="">Pick a story</option>
                <option value=""<?=($story_guid == '') ? ' selected="selected"' : '' ?>>Random</option>
                <?php 
                foreach($stories as $this_story){ 
                    $selected = ($this_story['guid'] == $story_guid) ? ' selected="selected"': '';
                ?>
                <option value="<?=$this_story['guid']?>"<?=$selected?>><?=$this_story['title']?></option>
                <?php } ?>
            </select>
        </fieldset>
        <input type="hidden" name="id" value="<?=$id?>" />
        <input type="hidden" name="action" value="<?=$action?>" />
        <fieldset class="buttons">
            <button type="submit" class="submit">Submit</button>
        </fieldset>	
    </fieldset>
</form>
<script type="text/javascript" src="/treeline/includes/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_landingpanel_main.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_homepageimage.js"></script>
