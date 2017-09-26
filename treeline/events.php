<?

//ini_set("display_errors", 1);
//error_reporting(E_ALL);
//print "memory limit(".get_cfg_var('memory_limit').")<br>\n";

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/event.class.php");

	include($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/newsletter.class.php");
	include ($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
	include ($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

	if ($_SESSION['treeline_user_group']=='Author') redirect("/treeline/");

	
	$action = str_replace("-", " ", strtolower(read($_REQUEST,'action','')));
	//print "got action($action)<br>";
	$guid = read($_REQUEST,'guid','');
	$member_id = read($_REQUEST,'m_id','');
	$entry_id = read($_REQUEST, "entry", 0);

	$event = new Event($guid);
	
	//print "got guid($guid) event-guid(".$event->id.")<br>\n";
		
	
	
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');

	$nextsteps='';
	
	$title = read($_POST,'title','');

	if ($_SERVER['REQUEST_METHOD'] == 'GET') {

		if ($action=="remove entry") {
			// How do we take someone off an event???
			$query="UPDATE event_entry SET `status`='Deleted', registered=-1 WHERE id=".$entry_id;
			if ($db->query($query)) {
				$message[]="This entry has been removed from the event";
				$feedback="success";
				$action = "list entries";
			}
		}

		
		if ($action=="accept") {
		
			// NEed to create the PDF ticket for this event
			
			// Email the ticket to the entrant or to $site->name set email addy
			// if the entrant has no email address
			$ticket_path = $_SERVER['DOCUMENT_ROOT']."/silo/pdf/events/".$guid;
			if (!file_exists($ticket_path)) {
				//print "create ticket dir($ticket_path)<br>\n";
				if (mkdir($ticket_path)) {
					if (!chmod($ticket_path, 0777)) {
						$message[]="Failed to set directory permissions";
					}
				}
				else $message[]="Failed to create ticket directory";
			}


			// Create ticket		
			if (file_exists($ticket_path)) {
				
				ini_set("memory_limit", "32M");
				if ($event->sendTicket($entry_id)) {
				
				
					// Update the event entry to registered = 1
					$query="UPDATE event_entry SET registered=1, `status`='Complete'
						WHERE id=".$entry_id;
					$db->query($query);
		
					
					if ($_GET['tid']>0) $nextsteps.='<li><a href="/treeline/tasks/?tid='.$_GET['tid'].'">Return to task</a></li>';
					$nextsteps.='<li><a href="/treeline/events/">Manage events</a></li>';
					$action='list entries';
				}
				else $message[]="There was a problem creating the ticket for this entry";
			}
			else $message[]="Ticket directory($ticket_path) does not exist";
		}


		// Reject an application to attend an event
		if ($action == "reject") {
		
			// Notify the entrant their application has been rejected.
			// I dont know why its been rejected but for now we just send some standard text.
			
			// Update the event entry to registered = -1
			$query="UPDATE event_entry SET registered=-1, `status`='Rejected' WHERE id=".$entry_id;
			$db->query($query);
			//print "$query<br>\n";
			
			if ($_GET['tid']>0) $nextsteps.='<li><a href="/treeline/tasks/?tid='.$_GET['tid'].'">Return to task</a></li>';
			$nextsteps.='<li><a href="/treeline/events/">Manage events</a></li>';
			$action='list entries';
		}

		if ($action=="show link") {
			$link_text=$page->drawLinkByGUID($guid);
			$action="list";
		}
		
	}
	else {	
		//print "posted action($action)<br>\n";
		
		if ($action=="add participant") {
			if ($member_id>0) {
				$msg=$event->addMember($member_id, false, $_POST['grp_title']);
				if ($msg>0) {
					$message[]="This member has been subscribed to the event";
					$feedback="success";
				}
				else $message=$event->error;
			}
			else $message[]="No member ID existed to add to this event";
		}

		if ($action=="save register form" && $guid) {
			//print "saving form<br>";	
			//print_r($_POST);
			foreach($_POST as $k=>$v) {
				//print "got ($k) = ($v)<br>";
				if (substr($k, 0, 6)=="chk_h_") {
					$set.="chk_".substr($k,6)."=".($_POST['chk_'.substr($k,6)]+0).",";
				}
				if (substr($k, 0, 6)=="tnc_d_" && $v==1) {
					$query="delete from event_config_tnc where id=".substr($k, 6);
					//print "remove a tnc - $query<br>\n";
					if (!$db->query($query)) $message[]="Failed to remove T&C number ".substr($k, 6);
				}
			}
			
			$query="update event_config set ".substr($set, 0, -1)." where guid='$guid'";
			////print "update config($query)<br>";
			if ($db->query($query)) {
				$message[]="Changes to form saved";
				$feedback="success";
			}
			
			if ($_POST['tnc']) {
				//print "Got a new TNC<br>\n";
				$neworder=$db->get_var("select max(sort_order) from event_config_tnc where guid='$guid'");
				$query="insert into event_config_tnc (guid, description, sort_order) 
					values ('$guid', '".$db->escape($_POST['tnc'])."', ".($neworder+1).")";
				//print "add tnc ($query)<br>\n";
				if (!$db->query($query)) $message[]="Failed to add new condition $query";
			}

			
		}
	
		if ($action=="add sponsorship") {
			$query="update event_entry set sponsorship=sponsorship+".($_POST['sponsorship']+0)." where member_id=".$_POST['m_id']." AND event_guid='".($guid?$guid:$_POST['fund-guid'])."'";
			//print "$query<br>";
			if ($db->query($query)) {
				$message[]="Sponsorship of £".($_POST['sponsorship']+0)." was successfully added";
				$feedback="success";
			}
		}
		
		
		else if (strtolower($action)=="edit event data") {
			if ($guid) redirect("/treeline/pages/?action=edit&guid=".$guid);
			else $message[]="No event selected";
			$action="list";
		}
	}


	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

table.mceLayout {
	float:left;
}

input.submit {
	clear:none;
	margin-right:5px;
}

';

	// Page title	
	$pageTitleH2 = ($action) ? 'Events : '.ucwords(str_replace("-", " ", $action)) : 'Events';
	$pageTitle =  $pageTitleH2;
	$pageClass = 'events';

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');


//print "action ($action)<br>\n";

?>


<div id="primarycontent">
<div id="primary_inner">

	<?php 
	if ($message && !$feedback) $feedback="error";
	echo drawFeedback($feedback,$message);

	if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");
	?>	

    <h2 class="pagetitle rounded">Manage events</h2>
	
    <!--
	<ul class="submenu">
    	<li>View <a href="/treeline/blogs/">personal pages and fundraisers</a> that require <?=$site->name?> approval</li>
    </ul>
	-->
    
	<?php 
	if ($action == 'create' || $action=="edit") { 
		$page_html = '
        <p>To create an event you must use the <a href="/treeline/?action=create">Create content</a> | <a href="/treeline/pages/?action=create">Create a page</a> function.</p>
        <p>To edit an event you select <a href="/treeline/?section=edit">Edit content</a> | <a href="/treeline/pages/?action=edit">Edit a page</a> function and select the specific events page you wish to modify</p>
        <p>You should ensure all events are in the get-involved section and select the template type "Event page" is selected.</p>
        <p>All event specific data should be entered as page attributes when creating the event</p>
		';
    }
	if ($action == 'delete' || $action=="remove") { 
		$page_html = '
        <p>You have tried to delete an event. Events can only be deleted by using the Delete page function on the event page.</p>
        <p>Please note that deleting an event does not delete entries and personal pages associated with the event</p>
		';
   	}

	if ($link_text) { 
		$page_html = '
        <p>To link directly to this event or the registration form please copy the link below</p>
        <p>Event page: <strong>'.$link_text.'</strong></p>
        <p>Event registration: <strong>'.$link_text.'?register=1</strong></p>
		';
    } 
	
	// If there is any info to show at the top of the page do that first.
	if ($page_html) echo treelineBox($page_html, $page_title?$page_title:"Option information", "blue");
	

	// Show the event select form...
	$tmp_evt_options = $event->drawSelectList("guid");
	if (!$tmp_evt_options) $tmp_evt_options='<p>There are no current events to manage.</p>';
	else $tmp_evt_options.='<input type="submit" class="submit" name="action" value="select" style="margin-left:10px;" />';
	$page_html = '
		<form id="treeline" action="'.$_SERVER['PHP_SELF'].($DEBUG?'?debug':'').'" method="post">
		<input type="hidden" name="m_id" value="'.$member_id.'" />
		<input type="hidden" name="id" value="'.$member_id.'" />
		<!-- <input type="hidden" name="action" value="assoc" /> -->
		<fieldset class="buttons">
			'.$tmp_evt_options.'
        </fieldset>
	';
	unset($tmp_evt_options);
	// Show manage event options
	if ($guid) $page_html .= '<table class="tl_list">
<thead>
	<tr>
	<th scope="col">Title</th>
	<th scope="col">Manage this event</th>
	</tr>
</thead>
<tbody>
	<td>'.$event->title.'</td>
	<td nowrap class="action">
		<a class="participants" '.$help->drawInfoPopup("Show attendees for this event").' href="/treeline/events/?action=List-Entries&amp;guid='.$guid.'">List attendees</a>
		<a class="event-links" '.$help->drawInfoPopup("Links to this event").' href="/treeline/events/?action=Show-Link&amp;guid='.$guid.'">Show event links</a>
		<a class="edit" '.$help->drawInfoPopup("Edit event data").' href="/treeline/pages/?action=edit&amp;guid='.$guid.'">Edit event data</a>
		<a class="preview" '.$help->drawInfoPopup("Preview registration form").' href="/treeline/events/?action=Preview-Register-Form&amp;guid='.$guid.'">Preview registration form</a>
		<a class="edit-form" '.$help->drawInfoPopup("Edit registration form").' href="/treeline/events/?action=Edit-Register-Form&amp;guid='.$guid.'">Edit registration form</a>
	</td>
</tbody>
</table>
';
	$page_html.='</form>';
	echo treelineBox($page_html, "Select event", "blue");



	// Attendee management.
    if ($action=="list entries") {
        include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/listEventParticipants.php";
    } 

	// Edit the event form setup.
	if ($action=="edit register form" || $action=="save register form") {
		?>
		<form id="f-event-form" action="<?=$_SERVER['PHP_SELF']?><?php if ($DEBUG) echo '?debug'?>" method="post">
        <?php
		include $_SERVER['DOCUMENT_ROOT']."/treeline/includes/ajax/forms/editEventConfiguration.php";
		?>
        </form>
        <?php
	}
	
	// Preview event registration form.	
	if ($action=="preview register form") {
		?><p><strong>NOTE :</strong> In preview mode the submit button has been removed</p><?php
		$event->load($guid);
		$form_mode="PREVIEW";
		include $_SERVER['DOCUMENT_ROOT']."/includes/snippets/event_entry_form.php";
	}
		

	if ($member_id>0) {
	
		if ($action=="book") {
			$member_name = $db->get_var("select concat('member number - ',member_id,' ',firstname,' ',surname,' (',email,')') from members where member_id=".$_GET['m_id']);
			?>
			<p>Please select the event that <?=$member_name?> would like to participate in.</p>
			<!-- <p>If this is a group event please also enter a title for this team/group participating.</p> -->
			<form id="treeline" action="<?=$_SERVER['PHP_SELF']?><?php if ($DEBUG) echo '?debug'?>" method="post">
			<fieldset class="buttons">
            	<input type="hidden" name="guid" value="<?=$guid?>" />
				<!--
				<label for="f_grp_title">Group title :</label>
				<input type="text" name="grp_title" value="" id="f_grp_title" />
				-->
				<label for="submit" style="visibility:hidden">Submit:</label>
				<input type="submit" class="submit" name="action" value="Add participant" />
			</fieldset>
            </form>
			<?php	
		}
		if ($action=="sponsor") {
			if (!$guid && !$_GET['fund-guid']) {
				?>
				<p>Please select the event that <?=$member_name?> would like to participate in </p>
				<?php
			}
			?>
			<p>Please enter the amount you would like to sponsor <?=$mem_name?> for</p>
			<form id="treeline" action="<?=$_SERVER['PHP_SELF']?><?php if ($DEBUG) echo '?debug'?>" method="post">
			<fieldset class="buttons">
            	<input type="hidden" name="guid" value="<?=$guid?>" />
				<input type="hidden" name="fund-guid" value="<?=$_GET['fund-guid']?>" />
				<label for="f_sponsorship">Amount £:</label>
				<input type="text" name="sponsorship" value="" id="f_sponsorship" />
				<label for="submit" style="visibility:hidden">Submit:</label>
				<input type="hidden" name="m_id" value="<?=$_GET['m_id']?>" />
				<input type="submit" class="submit" name="action" value="Add sponsorship" />
			</fieldset>
            </form>
			<?php
		}
	}
	?>
        
    
</div>
</div>

<?php if ($action=="edit register form" || $action=="save register form") { ?>
	<script type="text/javascript" src="/treeline/includes/tiny_mc3/jscripts/tiny_mce/tiny_mce.js"></script>
<?php } ?>

<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>

<?php if ($action=="edit register form" || $action=="save register form") { ?>
	<script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_termsandconditions.js"></script>
<?php } ?>
