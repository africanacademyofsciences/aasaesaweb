<form id="frmSearch" action="<?=$_SERVER['PHP_SELF']?>" method="get">
<fieldset>
    <input type="hidden" name="hidden" value="search"  />
    <label for="subject">Search Title:</label>
    <input type="text" id="subject" name="searchTitle" maxlength="255" size="30" value="<?=(!empty($_GET['searchTerm']))? escape($_GET['searchTerm']) : '' ?>" />
    <br />
    <label for="orderBy">Order by:</label>
    <select  name="orderBy" id="orderBy" style="width:140px; margin-right: 12px;">
        <option value="date_created" <?=($orderBy == "date_created" ? " selected=\"selected\"" : "")?> >Date created</option>
        <option value="date_sent"<?=($orderBy == "date_sent" ? " selected=\"selected\"" : "")?>>Date sent</option>
        <option value="title"<?=($orderBy == "title" ? " selected=\"selected\"" : "")?>>Title</option>
    </select>
    <label for="orderDir" class="hide">Order by:</label>
    <select name="orderDir" id="orderDir"  style="width:140px; margin-top: .5em;">
        <option value="ASC" <?=($orderDir == "ASC" ? " selected=\"selected\"" : "") ?> >ascending</option>
        <option value="DESC" <?=($orderDir == "DESC" ? " selected=\"selected\"" : "") ?> >descending</option>
    </select>
    <br />
    <label for="rowsPerPage">Show Results:</label>
    <select name="rowsPerPage" id="rowsPerPage">
        <option value="5"<?=($showRows == "5" ? " selected=\"selected\"" : "")?>>5 per page</option>
        <option value="10"<?=($showRows == "10" ? " selected=\"selected\"" : "")?>>10 per page</option>
        <option value="20"<?=($showRows == "20" ? " selected=\"selected\"" : "")?>>20 per page</option>
        <option value="50"<?=($showRows == "50" ? " selected=\"selected\"" : "")?>>50 per page</option>
    </select>
    <br />
    <fieldset class="buttons">
        <input type="submit" class="submit" value="Search" />
    </fieldset>
</fieldset>
</form>
