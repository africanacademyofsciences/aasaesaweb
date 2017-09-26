<?
//ini_set("display_errors", true);
//error_reporting(E_ALL);

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/comment.class.php");

	if (!$site->getConfig("setup_comments")) redirect("/treeline/");
	
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
	include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include ($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

	$action = read($_REQUEST,'action','list');
	$guid = read($_REQUEST,'guid','');
		
	$message = array();
	$feedback = read($_REQUEST,'feedback','error');

	$title = read($_POST,'title','');

	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if ($action=="reject") {
			$feedback="error";
			$comment_id=$_POST['id'];
			if ($comment_id) {
				if ($db->query("UPDATE pages_comments set status='X' where id=$comment_id")) {
					$feedback='success';
					$message[]=$page->drawLabel("tl_comm_err_reject", 'That comment has been rejected');

					// Update this page in the history table also
					$query = "UPDATE history 
						SET completed_action='COMMENT-REJECTED', completed_date=now(), completed_by=".$user->id." 
						WHERE action='publish' 
						AND completed_action is null
						AND info = 'Comment ".$comment_id." saved'";
					//print "$query<br>\n";
					$db->query($query);
				}
				else $message[]=$page->drawLabel("tl_comm_err_rejfail", "Failed to reject that comment");
			}
			else $message[]="No comment ID passed, cannot reject";
			$action="list";
		}
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
		//// ADD a new language to this site...
		if ($action == 'approve') {
			$feedback="error";
			$comment_id = $_GET['id'];
			if ($comment_id>0) {
				$db->query("UPDATE pages_comments set status='A' where id=".$comment_id);
				$feedback='success';
				$message[]=$page->drawLabel("tl_comm_err_publish", 'That comment has been published');

				// Update this page in the history table also
				$query = "UPDATE history 
					SET completed_action='PUBLISHED', completed_date=now(), completed_by=".$user->id." 
					WHERE action='publish' 
					AND completed_action is null
					AND info = 'Comment ".$comment_id." saved'";
				//print "$query<br>\n";
				$db->query($query);
			}
			else $message[]="No comment ID passed, cannot publish";
			$action="list";
		}
	}


	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

	
';


	// Page title	
	$pageTitleH2 =  ucfirst($page->drawLabel("tl_generic_comments", 'Comments'));
	$pageTitleH2 .= ($action)?' : '.$page->drawLabel("tl_generic_h2t_".substr($action, 0, 6), ucwords(str_replace("-", " ", $action))):'';
	$pageTitle = $pageTitleH2;

	$pageClass = 'publish-content';

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');




?>


<div id="primarycontent">
<div id="primary_inner">
<?php

echo drawFeedback($feedback,$message);


if ($action=="list" || $action=="edit" || $action=="download") { 
	?>
    <h2 class="pagetitle rounded"><?=$page->drawLabel("tl_comm_header", "Manage comments")?></h2>
    <?php 
	// Are we vetting or viewing
	if ($action=="list" || $action=="download") {
		$where = "AND pc.`status`='N' ";
		$dir = "ASC";
	}
	else {
		$where = "AND pc.`status`='A' ";
		$dir = "DESC";
	}

	$p = $_GET['page']; if (!$p) $p=1;
	$per_page = 10;
	$startp = ($p-1)*$per_page;
	$endp = $startp+$per_page;

	// Set error text if there are no comments
	if ($action == "edit") $nocomments = $page->drawLabel("tl_comm_err_nocomment", 'There are no comments on this site');
	else $nocomments = $page->drawLabel("tl_comm_err_noapprove", 'There are no comments needing approval');
	$nocomments = '<td colspan="5">'.$nocomments.'</td>';
	

	$query="SELECT pc.id, pc.name, pc.comment, pc.email, 
		date_format(pc.date_created, '%D %M %Y') as date_created, 
		datediff(now(), pc.date_created) as age, pc.guid
		FROM pages_comments pc 
		LEFT JOIN pages p ON pc.guid=p.guid
		WHERE p.msv=".$site->id."
		$where
		";
	if ($db->query($query)) {

		$total_results = $db->num_rows;
		//print "got p($p) tot($total_results) per($per_page) start($startp) and($endp)<br>\n";
		$query.="
			ORDER BY pc.date_created $dir
			LIMIT $startp, $endp
			";
		//print "$query<br>";

		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
			
				$publish_link = $preview_link = $no_link;
	
				$reject_link = '<a '.$help->drawInfoPopup($page->drawLabel("tl_dEC_commentdelete", "Delete this comment")).' class="delete" href="/treeline/comments/?action=reject&id='.$result->id.'">Delete comment</a>';
				if ($action=="list" || $action=="download") {
					$publish_link = '<a '.$help->drawInfoPopup($page->drawLabel("tl_dEC_commentpublish", "Publish this comment")).' class="publish" href="/treeline/comments/?action=approve&id='.$result->id.'">Approve comment</a>';
					$reject_link = '<a '.$help->drawInfoPopup($page->drawLabel("tl_dEC_commentreject", "Reject this comment")).' class="reject" href="/treeline/comments/?action=reject&id='.$result->id.'">Reject comment</a>';
				}
				$preview_link = '<a class="preview" '.$help->drawInfoPopup($page->drawLabel("tl_dEC_pagepreview", "Preview this page")).' href="'. $page->drawLinkByGUID($result->guid) .'?mode=preview&amp;showcomments=1&amp;commentid='.$result->id.'#comments" target="_blank">Preview this page</a>';
				
				$list_html .= '<tr>
	<td nowrap valign="top">'.$result->name.'</td>
	<td valign="top">'.$result->email.'</td>
	<td valign="top">'.comment_summary($result->comment).'</td>
	<td nowrap valign="top">'.($result->age>3?$result->age.' days ago':($result->age==0?"today":$result->date_created)).'</td>
	<td valign="top" class="action">
		'.$preview_link.$publish_link.$reject_link.'	
		
	</td>
	</tr>
	';
			}
			$list_html.=drawPagination($total_results, $per_page, $p, "?action=".$action);
		}
		else $list_html=$nocomments;
	}
	else $list_html=$nocomments;
	
	$html='';
	$html.='<p>'.ucfirst($page->drawLabel("tl_generic_show", "Show")).'
		<a href="/treeline/comments/">'.$page->drawLabel("tl_comment_menu_new", "new comments").'</a> 
		| 
		<a href="/treeline/comments/?action=edit">'.$page->drawLabel("tl_comment_menu_exist", "existing comments").'</a> 
		| 
		<a href="/treeline/comments/?action=download">'.$page->drawLabel("tl_comment_menu_csv", "CSV output of all comments").'</a>
		</p>
		';
	
	if ($action == "download") {
		include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/csv.class.php");
		$query = "SELECT pc.name AS Full_name, 
			IF (pc.`status`='A','Active',
				(IF (pc.`status`='N','New',
					(IF (pc.`status`='X','Deleted','Unknown') )
				) )
			) AS `Status`,
			pc.email AS Email,
			date_format(pc.date_created, '%D %M %Y') as `Date`,
			pc.`comment` AS `Comment`
			FROM pages_comments pc 
			LEFT JOIN pages p ON p.guid=pc.guid
			WHERE p.msv=".$site->id."
			ORDER by pc.date_created DESC
			";
		$csv = new CSV($query, true);
		if ($csv->filename && $csv->num_rows) {
			?>
            <p><?=$page->drawLabel("tl_comm_download_msg", "Download CSV listing file")?> <a href="/silo/tmp/<?=$csv->filename?>" target="_blank"><?=$csv->filename?></a></p>
            <?php
		}
		else print "Failed to generate CSV file($query)<br>\n";
	}
	if ($action=="list") $html.='<p>'.$page->drawLabel("tl_comment_approve_msg", "The following is a list of comments that currently require approval").'</p>'."\n";
	$html.='
<table class="tl_list">
<thead>
	<tr>
		<th scope="col">'.ucfirst($page->drawLabel("tl_generic_author", "Author")).'</th>
		<th scope="col">'.ucfirst($page->drawLabel("tl_generic_email", "Email")).'</th>
		<th scope="col">'.ucfirst($page->drawLabel("tl_generic_comment", "Comment")).'</th>
		<th scope="col">'.ucfirst($page->drawLabel("tl_generic_created", "Created")).'</th>
		<th scope="col">'.$page->drawLabel("tl_comm_act_manage", "Manage comment").'</th>
   </tr>
</thead>
<tbody>
	'.$list_html.'
</tbody>
</table>
	';
	echo treelineBox($html, $page->drawLabel("tl_comm_title", "Updated comments list"), "blue");
} 

if ($action=="view") { ?>

	<p>Display comment in edit mode with a publish button rather than a save button and redirect here</p>
    
<? }

else if ($action=="reject") {
	?>
	<h2 class="pagetitle rounded"><?=$page->drawLabel("tl_comm_reject_header", "Reject comment")?></h2>
    <?php
	$page_html='

	<p>'.$page->drawLabel("tl_comment_reject_message", "You are about to reject this comment, please confirm you wish to do this").'</p>
	
	<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':"").'" method="post">
	<input type="hidden" name="action" value="reject" />
	<input type="hidden" name="id" value="'.$_GET['id'].'" />
	<fieldset>
		<fieldset class="buttons">
			<label for="submit" style="visibility:hidden">Submit:</label>
			<input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_reject", "Reject")).'" />
		</fieldset>
	</fieldset>
	</form>
	';
	echo treelineBox($page_html, $page->drawLabel("tl_comm_reject_title", "Please confirm comment rejection"), "blue");
	
}

?>
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>


<?php

function comment_summary($s) {
	if (strlen($s)>100) return substr($s, 0, 100)."...";
	return $s;
}

?>