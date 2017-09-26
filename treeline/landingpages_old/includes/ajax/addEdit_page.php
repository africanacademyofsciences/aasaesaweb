<?php

	/*
	
		ADD/EDIT ITEM
	
	*/
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/landingpages/includes/landingpage.class.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/landingpages/includes/functions.php");
	
	if(!$plugin){
		$plugin = new LandingPage(1, 1, 'date', NULL, $page_id, NULL);
		$results = $plugin->properties;	
	}
	
	// VARIABLES
	$style = read($_REQUEST,'style',$results['style']);
	$content = read($_REQUEST,'content',$results['content']);
	$sort_order = read($_REQUEST,'sort_order',$results['sort_order']);
	$page_id = read($_REQUEST,'page_id',$page_id);
	$action = read($_REQUEST,'action',$action);

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		
		if($_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'create'){
			$message = $plugin->addPage();
		}
		else if($_REQUEST['action'] == 'edit'){
			$message = $plugin->editPage($page_id);
		}
	}

echo drawFeedback('error',$message);
?>
<h2>Step 4: Choose panel style and add content</h2>
<p><a href="includes/ajax/help.php?helpsection=create&amp;mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=400&amp;width=720" class="thickbox">Step 4? What were steps 1, 2 &amp; 3?</a></p
><form id="<?=$action?>Form" action="" method="post">
    <fieldset>
        <legend><?= ($action == 'add' || $action == 'create') ? 'Add a new ' : 'Edit '.$results['title']; ?> panel</legend>
        <p class="instructions"><?= ($action == 'add' || $action == 'create') ? 'Create a new panel using the form below' : 'This panel is now on the landing page, you can customise its content and appearance using this form:'; ?></p>
        <fieldset>
        <legend>Panel content</legend>
        <p class="instructions">You can add content for this panel or leave it blank. <br /><strong>N.B.</strong>
        <?=($results['meta_description']) ? 'This page has a meta description so if you leave custom content blank this will be sued instead.' : 'Enter some custom content or your panel will appear empty.'?></p>
        <label for="content">Custom content:</label>
        <textarea id="content" name="content" class="mceEditor" rows="5" cols="5"><?=$content;?></textarea><br />
        </fieldset>
        <fieldset>
        	<legend>Choose appearance</legend>
            <p class="instructions">Choose how your panel will appear. Pick one of style options below.</p>
        	<?php
				echo drawStyleCheckboxes('panel',$style);
			?>
        </fieldset>
        <fieldset>
        <legend>Sort order</legend>
        	<p class="instructions">Choose in what order this panel will appear. Select from 1 to 9.</p>
            <label for="sort_order">Sort order</label>
            <input type="text" name="sort_order" id="sort_order" class="int" value="<?=$sort_order?>" />
        </fieldset>
        <input type="hidden" name="page_id" id="page_id" value="<?=$page_id?>" />
        <input type="hidden" name="action" id="action" value="<?=$action?>" />
        <fieldset class="buttons">
            <button type="submit" class="submit">Submit</button>
        </fieldset>	
    </fieldset>
</form>
<script type="text/javascript" src="/treeline/includes/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_landingpanel.js"></script>