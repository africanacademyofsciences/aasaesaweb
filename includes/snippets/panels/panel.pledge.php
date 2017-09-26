<div class="panel-heading">Financial support</div>
<div class="panel-body">
<!-- <p>M(<?=$mode?>) pm(<?=$page->getMode()?>)</p> -->
<?php
if ($mode!="edit") {
	global $global_currency;
	?>
    <form method="post">
    <p>I would like to pledge <?=$global_currency?></p>
    <input type="text" name="pledge" value="<?=($_POST['pledge']+0)?>" />
    <p>To this research project</p>
    <input type="hidden" name="type_id" value="1" />
    <input type="submit" value="Pledge now" />
    </form>
    <?php
}
else {
	?>
    <p>Pledge form is disabled while editing this page</p>
    <?php
}
?>
</div>


</div>
<div class="panel panel-primary">

<div class="panel-heading">Other forms of support</div>
<div class="panel-body">
<?php
if ($mode!="edit") {
	?>
    <form method="post">
    <select name="type_id" style="width:100%;padding: 8px 1px; margin-bottom: 8px;">
    	<option value="0">Type of support offered</option>
        <?php
		$query = "SELECT id, title FROM pledge_type WHERE id>1 ORDER BY title";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				?>
                <option value="<?=$result->id?>"><?=$result->title?></option>
                <?php
			}
		}
		?>
    </select>
    <input type="submit" value="Pledge now" />
    </form>
    <?php
}
else {
	?>
    <p>Pledge form is disabled while editing this page</p>
    <?php
}
?>
</div>