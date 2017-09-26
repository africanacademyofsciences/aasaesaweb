<?
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
	//instatiate campaignstats object 
		
	$action = read($_REQUEST,'action','');
	$campaign_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "id", 0);
	

	$report = $_GET['report'];

	$message = array();
	$feedback = "error";
	

	/****Get all campaigns******/ 
	if ($campaign_id>0) {
		$query="SELECT c.title, c.date_sent, c.mail_count,
			date_format(c.date_sent, '%d %b %Y %H:%i') as dateSent,
			n.`subject`,
			(SELECT count(*) FROM campaign_link_stats WHERE campaign_id=$campaign_id AND unsub=0) as link_total,
			(SELECT count(*) FROM campaign_link_stats WHERE campaign_id=$campaign_id AND unsub=1) as link_unsub,
			(SELECT count(*) FROM newsletter_outbox nob WHERE newsletter_id=n.id AND delivered=1) as delivered,
			(
				SELECT count(*) FROM newsletter_outbox nob 
				LEFT JOIN newsletter_bounce_type nbt ON nob.bounced=nbt.id
				WHERE nob.newsletter_id=n.id AND nbt.soft=0
			) as hard_bounce,
			(
				SELECT count(*) FROM newsletter_outbox nob 
				LEFT JOIN newsletter_bounce_type nbt ON nob.bounced=nbt.id
				WHERE nob.newsletter_id=n.id AND nbt.soft=1
			) as soft_bounce
			FROM campaigns c
			LEFT JOIN newsletter n on n.id=c.newsletter_id
			WHERE c.id=".$campaign_id; 
		//print "$query<br>\n";
		if (!$campaign = $db->get_row($query)) $message[]="Failed to get campaign statistics";
		else {
			include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/csv.class.php");
			$csvlocation = "/silo/tmp/";
			switch ($report) {
				case "click":
					$csvfilename = "click.".date("dmyHi").".csv";
					$query = "SELECT 
						IF (nob.member_id,m.email,nob.email) AS email, 
						cls.link AS link, 
						DATE_FORMAT(cls.date_clicked, '%d %b %Y %H:%i') AS date_clicked, 
						nob.id AS oid
						FROM campaign_link_stats cls
						LEFT JOIN newsletter_outbox nob ON cls.outbox_id = nob.id
						LEFT JOIN members m on nob.member_id = m.member_id
						WHERE cls.campaign_id = ".$campaign_id." 
						ORDER BY cls.date_clicked
						";
					$csv = new CSV($query, true, $csvfilename);
					if ($csv->num_rows) {
						$message[] = 'Click report generated <a href="'.$csvlocation.$csvfilename.'" target="_blank">'.$csvfilename.'</a>';
						$feedback="success";
					}
					else if ($csv->errmsg) $message=$csv->errmsg;
					else $message[]="No results returned";
					break;
				case "read":
					$csvfilename = "read.".date("dmyHi").".csv";
					$query = "SELECT
						IF (nob.member_id,m.email,nob.email) AS email, 
						IF(nob.delivered=1,'Y','N') AS `read`,
						nbt.title AS bounce,
						nob.id AS oid
						FROM campaigns c
						INNER JOIN newsletter_outbox nob ON nob.newsletter_id = c.newsletter_id
						LEFT JOIN members m on nob.member_id = m.member_id
						LEFT JOIN newsletter_bounce_type nbt ON nbt.id=nob.bounced
						WHERE c.id=".$campaign_id."
						ORDER BY email
						";
					$csv = new CSV($query, true, $csvfilename);
					if ($csv->num_rows) {
						$message[] = 'Read report generated <a href="'.$csvlocation.$csvfilename.'" target="_blank">'.$csvfilename.'</a>';
						$feedback="success";
					}
					else if ($csv->errmsg) $message=$csv->errmsg;
					else $message[]="No results returned";
					break;
				case "hbnc":
					$csvfilename = "read.".date("dmyHi").".csv";
					$query = "SELECT
						IF (nob.member_id,m.email,nob.email) AS email, 
						nbt.title AS bounce,
						nob.id AS oid
						FROM campaigns c
						INNER JOIN newsletter_outbox nob ON nob.newsletter_id = c.newsletter_id
						LEFT JOIN members m on nob.member_id = m.member_id
						LEFT JOIN newsletter_bounce_type nbt ON nbt.id=nob.bounced
						WHERE c.id=".$campaign_id."
						AND nob.bounced>0
						ORDER BY email
						";
					$csv = new CSV($query, true, $csvfilename);
					if ($csv->num_rows) {
						$message[] = 'Read report generated <a href="'.$csvlocation.$csvfilename.'" target="_blank">'.$csvfilename.'</a>';
						$feedback="success";
					}
					else if ($csv->errmsg) $message=$csv->errmsg;
					else $message[]="No results returned";
					break;
				default : break;
			}
		}
	}
	else $message[]="No campaign ID was found";
	/////////////////////////////
	
	


	// PAGE specific HTML settings
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Campaign Statistics : '.ucwords($action) : 'Campaign Statistics';
	$pageTitle = ($action) ? 'Campaign Statistics : '.ucwords($action) : 'Campaign Statistics';
	
	$curPage = "campaignstats_home";
	$pageClass = 'campaignstats';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
<?php
	echo drawFeedback($feedback,$message);

	?>
	<h2 class="pagetitle rounded">Show campaign statistics</p></h2>
	<?php
	
	echo treelineList('<li><a href="/treeline/campaign/read/">Manage campaigns</a></li>', 'Create or edit a campaign', 'blue');

	?>
    <table id="stats" class="tl_list">
        <tr><td>Name: </td><td><?=$campaign->title?></td></tr>
        <tr><td>Newsletter: </td><td><?=$campaign->subject?></td></tr>
        <tr><td>Sent: </td><td><?=$campaign->dateSent?></td></tr>
        <tr><td>Total mails: </td><td><?=$campaign->mail_count?></td></tr>
        <tr><td><a href="/treeline/campaign/stats/?id=<?=$campaign_id?>&report=read">Number delivered:</a> </td><td><?=($campaign->delivered+0)?></td></tr>
        <tr><td><a href="/treeline/campaign/stats/?id=<?=$campaign_id?>&report=click">Links clicked:</a> </td><td><?=($campaign->link_total+0)?></td></tr>
        <tr><td><a href="/treeline/campaign/stats/?id=<?=$campaign_id?>&report=hbnc">Hard bounces:</a> </td><td><?=($campaign->hard_bounce+0)?></td></tr>
        <tr><td>Soft bounces: </td><td><?=($campaign->soft_bounce+0)?></td></tr>
        <tr><td>Unsubcriptions: </td><td><?=($campaign->link_unsub+0)?></td></tr>
    </table>
    <?php
?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>