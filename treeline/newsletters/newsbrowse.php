<?php

session_start();

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
//include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");

$showFrom = addslashes($_REQUEST["sf"]);
if(!$showFrom) $showFrom = 0;

$showRows = addslashes($_REQUEST["sr"]);
if(!$showRows) $showRows = 10;

$searchSubject = $_REQUEST["ss"];
$currentPage = read($_REQUEST, 'page', 1);

$order = ($_REQUEST['ob']?$_REQUEST['ob']:"added_date")." ".($_REQUEST['od']?$_REQUEST['od']:"ASC")." ";

$action = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "action", '');

$news_status=read($_REQUEST, "status", "N");
$follow_up = ($news_status == "S");


if (!$action) $action="newsletter";
$pageTitle = "Newsletters - Browse";
$curPage = "newsletters_browse";

function htmlShortDate($strDate){
	// Formats a date/time string for output to HTML
	if(!$strDate) return '';
	return(getUFDate($strDate));
	return "FIX THIS(htmlShortDate)";
}

$message = array();
$feedback = "notice";


// Do we need to perform any actions?
if ($_SERVER['REQUEST_METHOD']=="POST") {

}
else {
	if ($action=="delete") {
		$nid = $_GET['id'];
		if ($nid>0) {
			$query = "DELETE FROM newsletter WHERE id = ".$_GET['id'];
			$db->query($query);
			if (!$db->last_error) {
				$feedback = 'success';
				$message[] = $page->drawLabel("tl_nl_brws_delok", 'Newsletter was successfully deleted');
			}
			else $message[] = $page->drawLabel("tl_nl_brws_nodel", 'Newsletter was not deleted due to a technical error');
		}
	}
}




// PAGE specific HTML settings
$css = array('forms','tables'); // all CSS needed by this page
$extraCSS = ''; // extra on page CSS

$js = array(); // all external JavaScript needed by this page
// extra on page JavaScript
$extraJS = '

function deleteNewsletter(id) {
	if (confirm("Are you sure you want to delete this newsletter")) {
		window.location = "/treeline/newsletters/newsbrowse/?action=delete&id="+id;
	}
}
'; 

// Page title	
$pageTitleH2 = $pageTitle = $page->drawPageTitle("newsletters", $action);
$pageClass = 'newsletters';

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	

?>

<div id="primarycontent">
<div id="primary_inner">
<?php
	echo drawFeedback($feedback,$message);

    if ($follow_up) {
		?>
	    <h2 class="pagetitle rounded"><?=$page->drawLabel("tl_nl_brws_head1", "Use the form below to browse and edit follow up emails")?></h2>
    	<?php
	} 
	else { 
		?>
        <h2 class="pagetitle rounded"><?=$page->drawLabel("tl_nl_brws_head2", "Locate newsletter to manage")?></h2>
        <?php
		$page_html='
    	<p>'.$page->drawLabel("tl_nl_brws_msg1", "Use the list of existing newsletters to select one as the basis for a new newsletter or send out a saved one").'</p>
	    <p>'.$page->drawLabel("tl_nl_brws_msg2", "A newsletter can only be sent out once, but you can re-use it as many times as you like to help prepare other newsletters").'</p>
    	'; 
	} 
	
	$page_html.='
    
<form id="frmShowNewsletters" action="/treeline/newsletters/newsbrowse/" method="post">
<fieldset>
	<input type="hidden" name="status" value="'.$news_status.'" />
	<input type="hidden" name="action" value="'.$action.'" />
	<label for="subject">'.$page->drawLabel("tl_nl_brws_search", "Search Subject").'</label>
	<input type="text" id="subject" name="ss" maxlength="255" size="30" value="'.$_POST['ss'].'" />
	<br />
	<!--
	<label for="orderby">Order by:</label>
	<select class="newsfield" name="ob" id="orderby" style="width:140px; margin-right: 12px;">
		<option value="added_date"'.($orderBy == "added_date" ? " selected=\"selected\"" : "").'>created date</option>
		<option value="send_date"'.($orderBy == "send_date" ? " selected=\"selected\"" : "").'>send date</option>
		<option value="subject"'.($orderBy == "subject" ? " selected=\"selected\"" : "").'>subject</option>
	</select>
	<label for="od" class="hide">Order by:</label>
	<select name="od" id="od" class="newsfield" style="width:140px; margin-top: .5em;">
		<option value="ASC"'.($orderDir == "ASC" ? " selected=\"selected\"" : "").'>ascending</option>
		<option value="DESC"'.($orderDir == "DESC" ? " selected=\"selected\"" : "").'>descending</option>
	</select>
	<br />
	<label for="results">Show Results:</label>
	<select name="sr" id="results">
		<option value="5"'.($showRows == "5" ? " selected=\"selected\"" : "").'>5 per page</option>
		<option value="10"'.($showRows == "10" ? " selected=\"selected\"" : "").'>10 per page</option>
		<option value="20"'.($showRows == "20" ? " selected=\"selected\"" : "").'>20 per page</option>
		<option value="50"'.($showRows == "50" ? " selected=\"selected\"" : "").'>50 per page</option>
	</select>
	<br />
	-->
	<fieldset class="buttons">
		<input type="submit" class="submit" value="'.$page->drawGeneric("search", 1).'" />
	</fieldset>
</fieldset>
</form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_nl_brws_findtitle", "Find newsletters"), "blue");


	$totalPerPage  = ($_REQUEST['sr']) ? $_REQUEST['sr'] : 10;

	$select = "SELECT id, `subject`, `text`, 
		IFNULL(send_date, 0) as send_date, 
		IFNULL(done, 0) as done 
		";

	if ($follow_up) {
		
		if ($site->id>1) {
			$select = "SELECT IF(n2.msv,n2.id,n.id) AS id,
				IF(n2.msv,n2.subject,n.subject) AS subject, 
				IF(n2.msv,n2.text,n.text) AS text,
				IF(n2.msv,IFNULL(n2.send_date, 0),IFNULL(n.send_date, 0)) AS send_date, 
				IF(n2.msv,IFNULL(n2.done, 0),IFNULL(n.done, 0)) AS done ";
			$from = "FROM newsletter n 
				LEFT JOIN newsletter n2 ON n2.text3=n.text3 AND n2.msv=".$site->id."
				WHERE n.`status`='S' 
				AND n.msv=1
				AND (n.subject LIKE '%".$db->escape($searchSubject)."%' OR n2.subject LIKE '%".$db->escape($searchSubject)."%')
				";
			$order = "subject ";
		}
		else {
			$from = "FROM newsletter n
				WHERE n.`status`='S'
				AND n.subject LIKE '%".$db->escape($searchSubject)."%'
				AND n.msv=1 
				";
			$order = "n.subject ";
		}
		$select .= ", 0 AS total, 0 AS sent ";
	}
	else {
		$from = "FROM newsletter n
			WHERE n.`status`='N'
			AND n.`subject` LIKE '%".$db->escape($searchSubject)."%'
			AND msv=".$site->id." 
			";
		if ($action=="test" || $action=="send") $from.=" AND send_date IS NULL ";
		$select .= ", 
			(SELECT COUNT(*) FROM newsletter_outbox no WHERE no.newsletter_id = n.id) AS total,
			(SELECT COUNT(*) FROM newsletter_outbox no WHERE no.newsletter_id = n.id AND no.date_sent IS NOT NULL) AS sent
			";
	}
	
	$hits = $db->get_var("SELECT count(*) ".$from);

	// Fetch old newsletters
	$limit = "LIMIT ".(($currentPage-1)*$totalPerPage).', '.$totalPerPage;

	$query = $select.$from."ORDER BY ".$order.$limit;
	//echo "q($query)<br>\n";
	if($newsletters = $db->get_results($query)) {
	
		if ($follow_up) {
			?><p><a href="/treeline/access/?action=notify"><?=$page->drawLabel("tl_nl_brws_mannotify", "Manage notification preferences")?></a></p><?php
		}
		
		$no_link = '<span class="no-action"></span>';
		
		$idx = 0;
		?>
        <table class="tl_list">
        <caption><?=($hits." ".$page->drawGeneric("result".($hits==1?"":"s"))." ".$page->drawGeneric("found"))?></caption>
        <thead>
            <tr>
            <th scope="col"><?=$page->drawLabel("tl_nl_brws_subject", "Newsletter Subject")?></th>
            <?php
			if (!$follow_up) {
			?>
            <th scope="col"><?=$page->drawLabel("tl_nl_brws_datesent", "Date Sent")?></th>
            <th scope="col"><?=$page->drawGeneric("progress", 1)?></th>
            <?php
			}
			?>
            <th scope="col"><?=$page->drawLabel("tl_nl_brws_manage", "Manage newsletter")?></th>
            </tr>
        </thead>
        <tbody>
        <?php

		foreach ($newsletters as $n) {

			if($n->send_date){
				$sendlink = $no_link;
				$editlink = $no_link;
				$dellink = $no_link;
			}
			else {
				$sendlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_send", "Sending options")).' class="send" href="/treeline/newsletters/newssend/?id='.$n->id.'&amp;mode='.$action.'">Send</a>';
				$editlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_edit", "Edit newsletter")).' class="edit" href="/treeline/newsletters/newsedit/?action=edit&amp;id='.$n->id.'">Edit</a>';
				$dellink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_delete", "Delete newsletter")).' class="delete" href="javascript:deleteNewsletter('.$n->id.');">Delete</a>';
			}
			$reuselink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_reuse", "Re-use as a template")).' class="reuse" href="/treeline/newsletters/newsedit/?action=reuse&amp;id='.$n->id.'">Re-use</a>';
			//$previewlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_preview", "Prevew as HTML")).' class="preview thickbox" href="/newsletter/?id='.$n->id .'&amp;mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=600&amp;width=700" target="_blank">Preview</a>';
			$previewlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_preview", "Prevew as HTML")."<br>".$page->drawGeneric("opens in a new window", 1)).' class="preview" href="/newsletter/?id='.$n->id .'&amp;mode=preview" target="_blank">Preview</a>';

			// Lots of things we cant do in follow up mode...
			if ($follow_up) $dellink=$sendlink=$reuselink='';
			
			if($n->send_date){
				$sendDate = htmlShortDate($n->send_date);
			}
			else {
				$sendDate = $page->drawGeneric("n/a");
				$strProgress = $page->drawLabel("tl_nl_brws_notsent", "not sent");
			}

			if($n->send_date){
				/*
				$strSQL =
					"SELECT count(*) " .
					"FROM newsletter_outbox " .
					"WHERE newsletter_id = " . $n->id;
				$tot_mails = $db->get_var($strSQL);
				
				$strSQL =
					"SELECT count(*) " .
					"FROM newsletter_outbox " .
					"WHERE newsletter_id = " . $n->id . " " .
					"AND date_sent IS NOT NULL";
				$sent_mails = $db->get_var($strSQL);
				*/

				if(!$n->total) $strProgress = "N/A";
				else {
					$nsent = $n->sent;
					$ntotal = $n->total;
					$pcprog = ceil(($nsent/$ntotal)*100);
					//print "Got prog($pcprog)<br>\n";
					//$strProgress = . "%"."(".$n->sent."/".$n->total.")";
					$strProgress = $pcprog."%"."(".$n->sent."/".$n->total.")";
					//$strProgress = ceil(($n->sent/$n->$total)*100). "%"."(".$n->sent."/".$n->total.")";
				}
			}

			?>
			<tr>
			  <td><?php echo smartTruncate($n->subject, 40); ?></td>
              <?php if (!$follow_up) { ?>
			  <td><?php echo $sendDate; ?></td>
			  <td><?php echo $strProgress; ?></td>
              <?php } ?>
              <td class="action">
              	<?php 
				if ($action=="send" || $action=="test") { 
			  		echo $sendlink;
				} else {
				 	echo $previewlink; 
				  	echo $editlink; 
					echo $reuselink; 
				  	echo $dellink; 
                } 
				?>
                </td>
			</tr>
			<?php
		}
		//while(++$idx < count($newsletters));
		
		unset($newsletters);
		?>
	    </tbody>
    	</table>
    	<?php
    
	    echo drawPagination($hits, $totalPerPage, $currentPage, "?ss=".$searchSubject."&amp;status=".$news_status."&amp;action=".$action);
	}
	// No newsletters returned for this search
	else {
		?><p class="warn"><?=$page->drawLabel("tl_nl_brws_nonews", "No newsletters found")?></p><?php
	}
	?>
    
  	</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>
