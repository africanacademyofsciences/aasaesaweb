<?php

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");

session_start();

function htmlShortDate($strDate){
// Formats a date/time string for output to HTML
	if($strDate != "")return("" . date("j M Y", strtotime($strDate)) . "");
	else return("N/A");
}

$action="newsletter";

$showFrom = addslashes($_REQUEST["sf"]);
if(!$showFrom) $showFrom = 0;

$showRows = addslashes($_REQUEST["sr"]);
if(!$showRows) $showRows = 10;

/*$searchEmail = $_REQUEST["se"];
$searchFullname = $_REQUEST["sfn"];*/


$orderBy = addslashes($_REQUEST["ob"]); // Column to orderby
$orderDir = strtoupper(addslashes($_REQUEST["od"])); // Direction to orderby - DESC or ASC
if(!$orderBy){
	$orderBy = "date_changed";
	if(!$orderDir){
		$orderDir = "DESC";
	}
}
if($orderDir != "DESC") $orderDir = "ASC";



$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');


	// Error reporting
	// err=1 update ok, err=2 update failed
	$message="";
	if ($_GET['msg']) $message=$_GET['msg'];
	else if ($_GET['err']) {
		switch($_GET['err']) {
			case 1 : $feedback = "success"; $message="Preference updated"; break;
			case 2 : $feedback = "error"; $message="Preference update failed"; break;
			case 4 : $feedback = "error"; $message="Preference data invalid"; break;
			//case 5 : $message="Email address exists already"; break;
			default : $feedback = "error"; $message="Processing error($err)"; break;
		}
	}

	
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = 'Browse Newsletter Preferences';
	$pageTitle = 'Browse Newsletter Preferences';
	
	$pageClass = 'newsletters';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
<div id="primary_inner">
<?php
	echo drawFeedback($feedback,$message);
	
	?>
    <p><a href="/treeline/newsletters/prefedit/?action=create">Add a new preference</a></p>
    <?php


	//================================================================================================
	// PAGINATION
	// Get the pagination data now, so we can copy it at the top and bottom of the results list

	$strPagination = '';

	/*
	Notes
	showFrom refers to the first record to show - NOT page
	*/

	// Run stripped down SQL for pagination data
	$strSQL = "SELECT count(*) FROM newsletter_preferences WHERE site_id = ".$site->id;
	$hits = $db->get_var($strSQL);


	$num_pages = ceil($hits / $showRows);
	$last_page = $num_pages;
	$cur_page = ceil($showFrom / $showRows) + 1;

	$strCurURL = "../prefbrowse/?sr=" . $showRows . "&amp;ob=" . $orderBy . "&amp;se=" . $searchEmail . "&amp;sfn=" . $searchFullname . "&amp;od=" . $orderDir;

	if($showFrom > 0){

		$prevSF = $showFrom-$showRows;

		$strPagination .= '
<li><a title="go to start" href="'.$strCurURL.'&amp;sf=0"></a></li>
<li><a title="go to previous" href="'.$strCurURL.'&amp;sf='.$prevSF.'"></a></li>
';

	}

	$showPageLink = (($cur_page - 2) > 0 ? $cur_page - 2 : 1);

	if($last_page - $showPageLink < 5)$showPageLink = $last_page - 4;
	if($showPageLink < 1)$showPageLink = 1;

	for($i = 0; $i < 5 && $showPageLink <= $last_page; $i++, $showPageLink++){
		if($showPageLink == $cur_page){
			$strPagination .= "<li class=\"chosen_page\">" .$showPageLink . "</li>";
		}else{
			$strPagination .= "<li><a title=\"go to page " . $showPageLink . "\" href=\"" . $strCurURL . "&amp;sf=" . (($showPageLink - 1) * $showRows) ."\">" . $showPageLink . "</a></li>";
		}
	}



	if($cur_page < $last_page){

		$nextSF = ($showFrom+$showRows);
		$endSF = ($last_page - 1) * $showRows;

		$strPagination .= <<<EOD
<li><a title="go to next" href="{$strCurURL}&amp;sf={$nextSF}">&gt;</a></li>
<li><a title="go to end" href="{$strCurURL}&amp;sf={$endSF}">&gt;&gt;</a></li>
EOD;

	}



	// /PAGINATION
	//================================================================================================


	
	// Fetch old newsletters


	
	$query = "SELECT preference_id, preference_title, preference_description, deleted 
		FROM newsletter_preferences 
		WHERE site_id = ".$site->id."
		ORDER BY preference_title";
	echo $query;
	if($db->query($query)){
		$results = $db->get_results(null);
		//$idx = 0;
		?>
<table class="treeline">
<caption>Preferences - <?php echo ($hits . " result" . ($hits == 1 ? "" : "s") . " found"); ?></caption>
<thead>
<tr>
  <th scope="col">Preference Title</th>			
  <th scope="col">Edit</th>
  <th scope="col">Delete</th>
  <th scope="col">Re-enable</th>
</tr>
</thead>
<tbody>
        <?php
		//echo "<pre>".print_r($results, true)."</pre>";
		foreach($results as $result){

			$editlink = "<a href=\"/treeline/newsletters/prefedit/?action=edit&amp;preference_id=" . $result->preference_id . "\">Edit</a>";
			if ($result->deleted == 1){
				$dellink = '';
				$reenablelink = '<a href="/treeline/newsletters/prefproc.php?preference_id='.$result->preference_id.'&amp;action=re_enable" title="Re-enable">Re-enable</a>';
			} else {
				$dellink = '<a href="/treeline/newsletters/prefproc.php?preference_id='.$result->preference_id.'&amp;action=delete" title="Delete">Delete</a>';
				$reenablelink = '';
			}
			$date = ($result->date_changed) ? $result->date_changed : $result->date_added;
			?>
        	<tr>
                <td><?php echo smartTruncate(stripslashes($result->preference_title), 40); ?></td>
                <td class="action edit"><?php echo $editlink; ?></td>
                <td class="action delete"><?php echo $dellink; ?></td>
                <td class="action publish"><?php echo $reenablelink; ?></td>
        	</tr>
        <?php } ?>
      </tbody>
    </table>
    <ul class="pagination">
		<?php echo $strPagination; ?>
    </ul>
    <?php	}else{ ?>
    <p class="warn">No preferences found</p>
      <?php } ?>
  </div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>