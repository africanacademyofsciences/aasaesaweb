
<?php
$query = "SELECT * FROM slideshows WHERE msv=".$site->id." ORDER BY sortorder";
//print "$query<br>\n";
if ($results = $db->get_results($query)) {
	foreach ($results as $result) {	
		$i=$result->guid;
		$page_html.='
		<fieldset>
		<legend style="background-color:#000;padding:3px 10px;color:#FFF">'.$result->firstline.'</legend>
		<div class="field">
	        <label for="f_title-'.$i.'">Title</label>
    	    <input type="text" id="f_title-'.$i.'" name="title-'.$i.'" value="'.$result->firstline.'" />
		</div>
		<div class="field">
			<label for="f_image-'.$i.'">Image</label>
			<div class="ckeditor-holder">
				<textarea class="mceEditorImage" name="image-'.$i.'" id="f_image-'.$i.'">'.$result->image.'</textarea>
			</div>
		</div>
		<div class="field">
			<label for="f_desc-'.$i.'">Description</label>
			<div class="ckeditor-holder">
				<textarea class="mceEditor" name="desc-'.$i.'" id="f_desc-'.$i.'">'.$result->secondline.'</textarea>
			</div>
		</div>
		<div class="field">
			<label for="f_order-'.$i.'">Sort order</label>
			<input type="text" id="f_order-'.$i.'" name="order-'.$i.'" value="'.$result->sortorder.'" />
		</div>
		<div class="field">
			<label for="f_delete-'.$i.'">Delete tab</label>
			<input type="checkbox" id="f_delete-'.$i.'" name="delete-'.$i.'" value="1" style="width: auto;" />
		</div>
		</fieldset>
		';
	}
}


$page_html = '

<form id="'.$action.'form" action="" method="post">
<fieldset>
	<input type="hidden" name="action" value="'.$action.'" />
	<p class="instructions">Modify block data below or add a new block</p>
	'.$page_html.'
	<hr/>
	<fieldset>
		<legend style="background-color:#FFF;padding:3px 10px;">Add new block</legend>
		<div class="field">
			<label for="f_title-new">Title</label>
			<input type="text" id="f_title-new" name="title-new" value="'.$_POST['title-new'].'" />
		</div>
		<div class="field">
			<label for="f_image-new">Icon</label>
			<div class="ckeditor-holder">
				<textarea class="mceEditorImage" name="image-new" id="f_image-new">'.$_POST['image-new'].'</textarea>
			</div>
		</div>
		<div class="field">
			<label for="f_desc-new">Content</label>
			<div class="ckeditor-holder">
				<textarea class="mceEditor" name="desc-new" id="f_desc-new">'.$_POST['desc-new'].'</textarea>
			</div>
		</div>
		<div class="field">
			<label for="f_order-new">Sort order</label>
			<input type="text" id="f_order-new" name="order-new" value="'.$_POST['order-new'].'" />
		</div>
	</fieldset>
	<label for="f_submit" style="visibility:hidden;">Submit</label>
	<input type="submit" class="submit" id="f_submit" value="Save" />
</fieldset>
</form>
';

$page_title = "Manage homepage block tabs";
echo treelineBox($page_html, $page_title, "blue");

ob_start();
?>	

<script type="text/javascript">
	CKEDITOR.replaceAll( function(textarea,config) {
		if (textarea.className!="mceEditorImage") return false; //for only assign a class
		config.toolbar = 'contentImageOnly';
		config.width = "500px";
		config.height = "150px";
	});	
	CKEDITOR.replaceAll( function(textarea,config) {
		if (textarea.className!="mceEditor") return false; //for only assign a class
		config.toolbar = 'contentStandard';
		config.width = "500px";
		config.height = "300px";
	});	
</script>

<?php
$global_cke_init = ob_get_contents();
ob_end_clean();
