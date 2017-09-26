<?php 
	if ($mode != 'edit') { 
	?>
    <form id="searchForm" method="get" action="<?=$siteLink?>search/">
    <fieldset>
        <label for="keywords" class="hide"><?= $pagelabel['entersearch']?></label>
        <input type="text" id="keywords" name="keywords" value="" />
        <button type="submit"><?= ucfirst($pagelabel['search']) ?></button>
    </fieldset>
    </form>
	<?php 
} 
else { 
	?>
	<p id="searchForm">
	<?php
	if (file_exists($_SERVER['DOCUMENT_ROOT']."/images/editmode/search.gif")) {
		?>
		<img src="/images/editmode/search.gif" alt="" />
		<?php 
	}
	?>
	</p>
	<?php
} 
?>