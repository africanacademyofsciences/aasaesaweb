<?php

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

$guid=read($_REQUEST, "guid", "");
$success=false;

if ($_SERVER['REQUEST_METHOD']=="POST") {

	$action=strtolower(read($_POST, "action", ''));
	if ($action=="create note") {
		if ($_POST['newnote']) {
			if ($tasks->add($_POST['user'], 'Page note', $guid, $_POST['newnote'], 3, $_SESSION['treeline_user_id'])) {
				if ($_POST['user']>0) $tasks->notify("new-note", array(), $_POST['user']);
				$message[]="New note has been created";
				$success=true;
			}
			else $message[]="Failed to create note";
		}
		else $message[]="You did not enter any text for your note";
	}
}
$css = array('forms','editor_notes'); // all CSS needed by this page

// Grab a list of valid administrators
$query = "SELECT u.id, full_name FROM users u
	LEFT JOIN permissions p on u.`group`=p.`group`
	WHERE u.blocked=0 
	AND p.guid=".$site->id."
	ORDER BY full_name";
//print "$query<br>\n";
if ($results=$db->get_results($query)) {
	foreach($results as $result) {
		$userlist.='<option value="'.$result->id.'">'.$result->full_name.'</option>';
	}
}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=($_SESSION['treeline_user_encoding']?$_SESSION['treeline_user_encoding']:"iso-8859-1")?>" />
<meta name="robots" content="noindex,nofollow" />
<title>&nbsp;</title>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/commonCSS.inc.php"); ?>
<script type="text/javascript">
</script>
</head>

<body onload="javascript:window.focus();">

<h1 class="notes-header">
	<span>Editor notes</span>
	<a href="javascript:openhelp('<?=$help->helpLinkByID($helpid)?>')" class="tl-help-link">Get help with this</a>
</h1>

<form method="post">
<?php
if (count($message)) {
	foreach ($message as $tmp) {
		print '<p class="message '.($success?"":"failure").'">'.$tmp.'</p>';	
	}
}
?>
<fieldset>
	<label for="fNote">Manually add a new note</label>
    <textarea name="newnote" id="fNote"></textarea>
    <label for="fUser">Send notification to the selected user (optional)</label>
    <select name="user" id="fUser">
    	<option value="0">Please select</option>
        <?=$userlist?>
    </select>
    <input type="submit" class="submit" name="action" value="Create note" />
</fieldset>
</form>

<?php
	// Show all page related notes.
	$query = "SELECT 
		t.title as title, t.description as info, 
		t.date_added as order_date, date_format(t.date_added, '%H:%i on %D %b %Y') note_date, 
		IF(user_added,0,1) as auto, u.full_name
		FROM tasks t 
		LEFT JOIN users u ON t.user_added = u.id
		WHERE guid='$guid' 
		AND (t.user_id=0 OR t.user_id=".$_SESSION['treeline_user_id'].")
		UNION 
		SELECT '' as title, h.info as info, 
		h.date_added as order_date, date_format(h.date_added, '%H:%i on %D %b %Y') note_date, 
		1 as auto, u.full_name
		FROM history h
		LEFT JOIN users u on h.user_id = u.id
		WHERE guid='$guid'
		ORDER by order_date DESC";
	//print "$query<br>";
	if ($results=$db->get_results($query)) {
		foreach ($results as $result) {
			if ($result->auto) echo '<p class="note-auto">Automatic note<br />'.($result->title?$result->title:$result->info).($result->full_name?": ".$result->full_name.",":" at").' '.$result->note_date.'</p>';
			else {
				echo '<div class="note-user">
<p class="header">
	<span class="name">'.$result->full_name.'</span>
	<span class="date">'.$result->note_date.'</span>
</p>
<p class="info">'.$result->info.'</p>
</div>
';
			}				
		}
	}	
	else {
		// Nothing has ever happened to this page???
	}
?>
    
<script type="text/javascript">
	function openhelp(lnk) {
		var settings="menubar=no,top=100,left=100,width=600,height=600";
		var helpwindow = window.open(lnk, "helpwin", settings)
		if (window.focus) { helpwindow.focus(); }	
	}
</script>    
</body>
</html>
