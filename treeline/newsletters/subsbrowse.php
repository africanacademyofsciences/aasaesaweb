<?php

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/subscriber.class.php");
include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");

session_start();

$action=read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "action", "newsletter");

/*
$showFrom = addslashes($_REQUEST["sf"]);
if(!$showFrom) $showFrom = 0;
$showRows = addslashes($_REQUEST["sr"]);
if(!$showRows) $showRows = 10;
*/

$searchEmail = $_REQUEST["se"];
$searchFullname = $_REQUEST["sfn"];

$totalPerPage = 10;
$currentPage = read($_REQUEST, 'page', 1);

$orderDir = "ASC";
if ($searchFullname || $searchEmail) $orderBy = "m.firstname";
else {
	$orderBy = "m.date_edited";
	$orderDir = "DESC";
}

$message = array();
$feedback = 'notice';

	// Error reporting
	// err=1 update ok, err=2 update failed
	if ($_GET['msg']) $message=$_GET['msg'];
	else if ($_GET['err']) {
		switch($_GET['err']) {
			case 1 : $feedback = 'success'; $message[]="Subscriber updated"; break;
			case 2 : $message[]="Subscriber update failed"; break;
			case 4 : $message[]="Subscriber data invalid"; break;
			case 5 : $message[]="Email address exists already"; break;
			default : $message[]="Processing error($err)"; break;
		}
	}

	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	//$pageTitleH2 = 'Browse Newsletter Subscribers';
	//$pageTitle = 'Browse Newsletter Subscribers';
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("newsletters", "Browse subscribers");
	
	$pageClass = 'newsletters';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
  <div id="primary_inner">
    <?=drawFeedback($feedback,$message)?>
    <!--<p><a href="/treeline/newsletters/subsedit/?action=create">Add a new subscriber</a></p> Hidden as new subscribers can be added from the front-end.-->
    <?php


	$page_html = '
		<form id="frmShowSubscribers" action="" method="post">
		<fieldset>
			<input type="hidden" name="page" value="1" />
			<div class="field">
				<label for="fullname">'.$page->drawLabel("tl_nl_susb_schname", "Search name").'</label>
				<input type="text" class="text" name="sfn" id="fullname" maxlength="255" size="30" value="'.$_POST['sfn'].'" />
			</div>
			<div class="field">
				<label for="email">'.$page->drawLabel("tl_nl_subs_schemail", "Search Email").'</label>
				<input type="text" class="text" id="email" name="se" maxlength="255" size="30" value="'.$_POST['se'].'"/>
			</div>
			<fieldset class="buttons">
				<input type="submit" class="submit" value="'.$page->drawGeneric("search", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
	echo treelineBox($page_html, $page->drawLabel("tl_nl_subs_findtitle", "Find subscribers"), "blue");

	// Run subscriber search
	$query = "FROM members m
		LEFT JOIN newsletter_user_preferences nup ON m.member_id=nup.member_id
		LEFT JOIN newsletter_preferences np on nup.preference_id=np.preference_id
		WHERE np.deleted=0
		AND site_id = ".$site->id." ";
	if($searchEmail) $query .= "AND m.email LIKE '%" . addslashes($searchEmail) . "%' ";
	if($searchFullname) $query .= "AND concat(m.firstname, ' ', m.surname) LIKE '%" . addslashes($searchFullname) . "%' ";
	$query .= "GROUP BY m.member_id ";

	//print "total q(SELECT m.member_id $query)<br>\n";
	if ($results = $db->get_var("SELECT m.member_id ".$query)) {
	
		$hits = $db->num_rows;
	
		$query = "SELECT m.member_id as id, m.email, concat(m.firstname, ' ', m.surname) as fullname, 
			m.date_edited, date_added, DATE_FORMAT(date_edited, '%D %M %Y') AS nice_date ".$query;
		
		$query .= 	"ORDER BY $orderBy $orderDir";
		$startLimit = ($currentPage-1)*$totalPerPage; // work out which record to start from in the database	
		$limitQuery = $startLimit.', '.$totalPerPage;
		$query .= " LIMIT ".$limitQuery;
		//print "$query<Br>";
	
			
		if($results = $db->get_results($query)) {
			//$idx = 0;
			?>
			<table class="tl_list">
			<caption><?=$page->drawGeneric("subscribers", 1)?> - <?=$hits?> <?=$page->drawGeneric("result".($hits==1?"":"s"))?> <?=$page->drawGeneric("found")?></caption>
			<thead>
			<tr>
				<th scope="col"><?=$page->drawGeneric("email",1)?></th>
				<th scope="col"><?=$page->drawgeneric("fullname", 1)?></th>
				<th scope="col"><?=$page->drawGeneric("date", 1)?></th>
				<th scope="col"><?=$page->drawLabel("tl_nl_subs_manage", "Manage subscriber")?></th>
			</tr>
			</thead>
			<tbody>
			<?php
	
			foreach($results as $result){
				$editlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_edsubs", "Edit subscriptions")).' class="edit" href="/treeline/newsletters/subsedit/?action=edit&amp;id='.$result->id.'">Edit subscriptions</a>';
				$memlink = '<a '.$help->drawInfoPopup($page->drawLabel("tl_nl_help_edmem", "Edit member data")).' class="publish" href="/treeline/members/?action=edit&amp;id='.$result->id.'">Edit member</a>';

				$date = ($result->date_changed) ? $result->date_changed : $result->date_added;
				?>
				<tr>
				  <td><?php echo smartTruncate(stripslashes($result->email), 40); ?></td>
				  <td><?php echo smartTruncate(stripslashes($result->fullname), 40); ?></td>
				  <td><?=$page->languageDate($result->nice_date)?></td>
				  <td class="action"><?=$editlink.$memlink?></td>
				</tr>
				<?php 
			} 
			?>
	
			</tbody>
			</table>
	
			<?=drawPagination($hits, $totalPerPage, $currentPage, "/treeline/newsletters/subsbrowse/?sfn=$searchFullname&amp;se=$searchEmail")?>
			<?php
		}
	} 
	else { 
		?>
	    <p"><?=$page->drawLabel("tl_nl_subs_nofound", "No subscribers found")?></p>
      	<?php 
	} 
	?>

</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>
