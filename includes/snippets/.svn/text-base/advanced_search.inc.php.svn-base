

<form id="advancedSearch" class="contact" action="" method="post">
<fieldset class="border">
	<input type="hidden" name="adv" value="1" />
    <fieldset>
        <label for="f_term" class="required">Search term:</label>
        <input type="text" name="keywords" id="f_term" class="required" value="<?=$search->getTerm()?>" /><br />
    </fieldset>
    <fieldset>
        <label for="f_filter">Filter:</label>
        <select name="filter" id="f_filter">
            <option value="0"<?=($filter=="0"?' selected="selected"':"")?>>All content types</option>
            <option value="pages"<?=($filter=="pages"?' selected="selected"':"")?>>Content page</option>
            <option value="files"<?=($filter=="files"?' selected="selected"':"")?>>Document library</option>
            <option value="media"<?=($filter=="media"?' selected="selected"':"")?>>Media library</option>
            <option value="events"<?=($filter=="events"?' selected="selected"':"")?>>Event listings</option>
        </select>
        <br />
    </fieldset>
    <fieldset>
        <label for="f_range">Last updated:</label>
        <select name="daterange" id="f_range">
            <option value="0">Anytime</option>
            <option value="0.25">Within the past week</option>
            <option value="1">Within the past month</option>
            <option value="3">Within the past 3 months</option>
            <option value="6">Within the past 6 months</option>
            <option value="12">Within the past year</option>
        </select>
        <br />
    </fieldset>
    <fieldset>
        <label for="search_submit" style="visibility:hidden;">Submit</label>
        <input type="submit" id="search_submit" class="submit" name="submit" value="Search" />
    </fieldset>
</fieldset>
</form>