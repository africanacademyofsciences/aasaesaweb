<?php
$page_html = '
<form id="removeMemberForm" action="" method="post">
<fieldset>
	<legend></legend>
	<p class="instructions">'.$page->drawLabel("tl_mem_del_msg1", "Are you sure you want to remove").' '.$result->firstname.' '.$result->surname.'?</p>
	<fieldset class="buttons">
		<input type="submit" class="submit" value="'.$page->drawGeneric("delete", 1).'" />
    </fieldset>	
</fieldset>
</form>
';
echo treelineBox($page_html, $page->drawGeneric("delete", 1).' '.$result->firstname.' '.$result->surname.'?', "blue");
?>