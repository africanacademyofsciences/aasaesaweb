<form action="/shop/search/" method="get" id="storesearch">
	<fieldset>
		<h3>Search the <?=$site->name?> shop</h3>
		<label for="ssk">Search for</label>
		<input type="text" name="ssk" id="ssk" maxlength="20" value="<?= $search_term ?>" />
		<label for="ssc">in category</label>
		<select name="ssc" id="ssc">
		<?= $store->getSelectCategories(0,$search_category) ?>
		</select>
		<button type="submit">OK</button>
	</fieldset>
</form>