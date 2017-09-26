<?php
//print "in bugs method(".$_SERVER['REQUEST_METHOD'].")<br>\n";
$autoContent = ($_GET['bug']) ? $_SERVER['HTTP_REFERER']."\n\n" : '';
$autoTitle = ($_GET['bug']) ? 'Bug report' : '';

$page_html = '
<form id="bugsForm" action="'.$_SERVER['REQUEST_URI'].'" method="post">
    <fieldset>
        <legend>'.$page->drawLabel("tl_feedb_bug_legend", "Report a bug").'</legend>
        <p class="instructions">'.$page->drawLabel("tl_feedb_bug_msg1", "Fill in all the details below and press the Report bug button. We will respond as quickly as we can").'</p>
		<div class="field">
			<label for="title" class="required">'.$page->drawGeneric("title", 1).':</label>
			<input type="text" value="'.$autoTitle.$_POST['title'].'" id="title" name="title" class="required" />
		</div>
		<div class="field">
			<label for="description" class="required">'.$page->drawGeneric("description", 1).':</label>
			<textarea id="description" name="description" rows="10" cols="10" class="required">'.$autoContent.$_POST['description'].'</textarea>
		</div>
        <input type="hidden" name="action" value="'.$action.'" />
        <fieldset class="buttons">
            <input type="submit" class="submit" value="'.$page->drawLabel("tl_feedb_bug_butrep", "Report Bug").'" />
        </fieldset>	
    </fieldset>
</form>
<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_adminitems.js"></script>
';

?>