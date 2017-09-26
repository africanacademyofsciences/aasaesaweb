<?
	//ini_set("display_errors", 1);
	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	//if (!$site->getConfig("setup_comments")) redirect("/treeline/");
	
	$action = read($_REQUEST,'action','');
	$tid = read($_REQUEST,'tid',0);
	$guid = read($_REQUEST, 'guid', '');
	$hid = read($_REQUEST, "hid", 0);
	//print "got tid($tid) guid($guid) hid($hid)<br>\n";
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');

	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if ($action=="reject") {
			$feedback="error";
			$message[]="invalid action requested";
			$action="";
		}
		
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
		if ($action == 'mark-as-completed' && $tid>0) {
			$tasks->completed($tid);
			$tasks->loadList();
			$action="list";
			$tid=$guid='';
		}
		
		if ($action == 'approve') {
			$feedback="error";
			$message[]="Invalid action requested";
			$action="";
		}
		
	}


	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '

table.tl_list td {
	padding-right: 20px;
}
	
';

	// Page title	
	$pageTitle =  $pageTitleH2 = $page->drawPageTitle("my tasks", $action);
	$pageClass = 'tasks';

	if (!$action) $action="list";
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');

?>
<div id="primarycontent">
<div id="primary_inner">
<?php

echo drawFeedback($feedback,$message);


if ($tid>0 || $guid>'') { 

	if ($tid>0) $tasks->loadByID($tid);

	// If there is a page/panel related to this task then load that too.
	if ($guid>'' && !$tasks->guid>'') {
		$tasks->loadByGUID($guid, $hid);
	}

	$extra_info='';

	if ($tasks->guid>'') { 
		//print "info(".$tasks->info.") title(".$tasks->title.")<br>\n";
		if ($tasks->info=="Page saved" || $tasks->info=="Panel saved") {
			$pageactions = $page->drawEditCheckboxes($tasks->guid, $tasks->type, $tasks->placeholder, $tasks->template, $tasks->publishable, $tasks->pagelocked, $tasks->pageoffline);
			$pagetitle = $page->drawLabel("tl_".strtolower($tasks->type)."_publish", ucfirst($tasks->type)." awaiting publishing");
		}
		else if ($tasks->title=="Page note") {
			$pageactions = $page->drawEditCheckboxes($tasks->guid, $tasks->type, $tasks->placeholder, $tasks->template, $tasks->publishable, $tasks->pagelocked, $tasks->pageoffline);
		}
		else if (substr($tasks->info,0,7)=="Comment") {
			$comment_id=substr($tasks->info,8,strpos($tasks->info," ", 9)-8);
			$pageactions=$page->drawCommentsCheckboxes($tasks->guid, $comment_id);
			$pagetitle = $page->drawLabel("tl_task_new_comment", "A new comment has been added to")." <strong>".$tasks->pagename."</strong>";
		}
		else if (substr($tasks->info,0,9)=="ENTRY ID:") { 
			$pageactions = $page->drawEditCheckboxes($tasks->guid, $tasks->type, $tasks->placeholder, $tasks->template, $tasks->publishable, $tasks->pagelocked, $tasks->pageoffline);
			// Set up options allowed for events applications
			$entry_id = substr($tasks->info, 9);
			$query = "SELECT * FROM event_entry ee 
				LEFT JOIN event_entry_data eed on ee.id=eed.entry_id
				WHERE ee.id=".$entry_id;
			//print "$query<br>\n";
			if ($entry=$db->get_row($query)) {
				$entrant=($entry->title?$entry->title." ":"").($entry->forenames?$entry->forenames." ":"").$entry->surname;
				$extra_info.='<tr><td>Requested by: </td><td>'.$entrant.'</td></tr>';			
				if ($entry->registered==-1) $extra_info.='<tr><td>Status: </td><td>Application rejected</td></tr>';
				// Registered, do they need a printed ticket??
				else if ($entry->registered==1) {
					$extra_info.='<tr><td>'.$page->drawGeneric("status", 1).'</td><td>'.$page->drawLabel("tl_task_evt_appsuc", "Application successful").'</td></tr>';
					if ($tasks->title=="Event ticket") {
						$ticket_link = "/silo/pdf/events/".$entry->event_guid."/ticket-".$entry->entry_id.".pdf";
						//print "details ".print_r($entry, true)."<br>\n";
						$applicant_data = $entry->forenames.($entry->surname?" ".$entry->surname:"");
						$applicant_data .= $entry->address?"<br />".nl2br($entry->address):"";
						$applicant_data = '<tr><td valign="top">Applicant</td><td><p class="taskinfo">'.($applicant_data?$applicant_data:"This applicant did not enter their name or address").'</p></td></tr>';
						if (!$applicant_data) $no_applicant_data = " this applicant";
						$extra_info.='<tr><td>Suggested action: </td><td>Please ensure a copy of the <a href="'.$ticket_link.'" target="_blank">ticket for this event</a> has been printed off and posted to'.$no_applicant_data.'</td></tr>';
						$extra_info.=$applicant_data;
							
						//print "Send to ".$entry->member_id."<br>\n";
						//print "ticket ".$ticket_link."<br>\n";
						
					}
				}
				else $extra_info.='<tr>
					<td>'.$page->drawLabel("tl_task_evt_sugact", "Suggested action").'</td>
					<td>
						<a href="/treeline/events/?guid='.$tasks->guid.'&amp;action=preview-register-form&amp;entry='.$entry_id.'">View entry form</a> 
						then <a href="/treeline/events/?guid='.$tasks->guid.'&amp;entry='.$entry_id.'&amp;action=accept&amp;tid='.$tasks->id.'">accept</a> 
						or <a href="/treeline/events/?guid='.$tasks->guid.'&amp;entry='.$entry_id.'&amp;action=reject&amp;tid='.$tasks->id.'">reject</a> their application
					</td>
					</tr>';			
			}
			else $extra_info.='<tr><td>Entry data: </td><td><strong>Could not locate eventy entry('.$tasks->info.')</strong></td></tr>';			
			//print "set xi($extra_info)<br>\n";
		}
		else {
			//print_r($tasks);
		}
	}
	// Task only processing..
	else {

		if (preg_match("/^(blogs|forum) id:(\d*)$/", $tasks->info, $reg)) {
			$tmp_pagelink = ($reg[1]=="forum"?"forums":$reg[1]);
			$info_text='<tr><td>'.$page->drawLabel("tl_task_please_use", "Please use the").' <a href="/treeline/'.$tmp_pagelink.'/">Manage '.$reg[1].'</a> '.$page->drawLabel("tl_task_process_abuse", "page to view and process abuse reports. This task will be automatically removed from the list once this report has been dealt with").'</td></tr>';
			$deny_completed=true;
		}
		else if ($tasks->title=="Offline page hit") {
			$info_text=$page->drawLabel("tl_task_check_page", "Check the page")." ".$tasks->info." ".$page->drawLabel("tl_task_offline_hit", "to ensure this offline page is not linked");
		}
	}
	
	if (!$pagetitle) $pagetitle = $page->drawLabel("tl_t_".substr(str_replace(" ", "_", $tasks->title),0,10), $tasks->title);
	?>

    <h2 class="pagetitle rounded"><?=$page->drawGeneric("step", 1)?> 2 <?=$page->drawGeneric("of")?> 2: <?=$page->drawLabel("tl_task_header", "View task and take action")?></h2>
    <h3><?=$pagetitle?></h3>

	<?php 
	// Create a pretty info box if we need one.
	if ($tasks->info>'' && $tasks->id>0) { 
		if ($entry_id>0) ;	// Dont show info for events tasks.
		else {
			if (!$info_text) $info_text=$tasks->info;
			if ($info_text) $info_text='<p class="taskinfo">'.$info_text.'</p>';
			echo $info_text;
		}
	} 
	?>

	<table class="tl_list" style="width: auto">
    <?php if ($tasks->creator) { ?>
    <tr>
    	<td><?=$page->drawLabel("tl_task_added_by", "Added by")?></td>
    	<td><?=$tasks->creator?></td>
    </tr>
    <?php } ?>
    
    <?=$extra_info?>
    
    <?php if ($tasks->id>0 && !$deny_completed) { ?>
    <tr>
    	<td><?=$page->drawLabel("tl_task_date_complete", "Date completed")?></td>
    	<td><?=($tasks->completed?$tasks->completed:'<a href="/treeline/tasks/?tid='.$tid.'&amp;action=mark-as-completed">Mark as completed</a>')?></td>
    </tr>
    <?php } ?>
    </table>

	<?php if ($tasks->guid>'') { ?>
		
	   	<h3><?=$page->drawLabel("tl_task_refers_header", "This tasks refers to the following")?></h3>
        <table class="tl_list">
        <thead>
            <tr>
            <th scope="col"><?=$page->drawGeneric("title", 1)?></th>
            <th scope="col"><?=$page->drawGeneric("type", 1)?></th>
            <th scope="col"><?=$page->drawGeneric("updated", 1)?></th>
            <th scope="col"><?=$page->drawLabel("tl_task_last_usedby", "Last used by")?></th>
            <th scope="col" colspan="5"><?=$page->drawLabel("tl_task_manage_page", "Manage this page")?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
            	<td><strong><?=$tasks->pagename?></strong></td>
                <td><?=(ucfirst($tasks->type))?></td>
                <td><?=$page->languageDate($tasks->updated)?></td>
                <td><?=$tasks->updated_by?></td>
                <td class="action"><?=$pageactions?></td>
            </tr>
        </tbody>
        </table>
    <?php } ?>
        
    
    <?php
}
else if ($action=="history") {
	$limit = 0;
	$query = "SELECT h.*, 
		DATE_FORMAT (h.date_added, '%d/%m/%Y %H:%i') as `date`,
		u.full_name AS author,
		CONCAT(m.firstname, ' ', m.surname) AS member,
		p.title
		FROM history h 
		LEFT OUTER JOIN users u ON u.id = h.user_id
		LEFT OUTER JOIN members m ON m.member_id = h.user_id
		LEFT OUTER JOIN pages p ON p.guid = h.guid
		ORDER BY h.date_added DESC
		";
	if ($limit) $query .= "LIMIT $limit ";
	
	//print "$query<br>\n";
	if ($results = $db->get_results($query)) {
		print '<table class="history tl_list"><tr><th>Date</th><th>Action</th><th>Page title</th><th>Author</th></tr>'."\n";
		$maxlen=40;
		foreach ($results as $result) {
			$action = $pagetitle = '';
			//print "Switch (".$result->action.")<br>\n";
			switch($result->action) {
				case "register": 
					//print "register<br>\n";
					$author = $result->member;
					break;
				case "publish":
				case "": 
					//print "No action<br>\n";
					if (substr($result->info, 0, 7)=="Comment") $action=$result->info;
					else {
						switch ($result->info) {
							case "Page published": $action = "Publish page"; break;
							case "Panel published": $action = "Publish panel"; break;
							case "Page created": $action = "Add page"; break;
							case "Panel created": $action = "Add panel"; break;
							case "Page saved": $action = "Save page"; break;
							case "Panel saved": $action = "Save panel"; break;
							case "Page deleted": $action = "Delete page"; break;
							case "Attributes modified": $action = "Attribute update"; break;
							default: $action = "??".$result->info;
						}
					}
					if ($result->guid) $pagetitle = $result->title;
				default :
					$author = $result->author;
					break;
			}
			if (!$action) $action = $result->action?ucfirst($result->action):print_r($result, 1);
			print '<tr>
				<td nowrap>'.$result->date.'</td>
				<td nowrap>'.$action.'</td>
				<td>'.(strlen($pagetitle>$maxlen)?substr($pagetitle, 0, $maxlen)."...":$pagetitle).'</td>
				<td nowrap>'.$author.'</td>
			</tr>
			';
		}
		print "</table>\n";
	}
}
// List all tasks allocated to this user
else if ($action=="list") { 
	?>

    <h2 class="pagetitle rounded"><?=$page->drawGeneric("step", 1)?> 1 <?=$page->drawGeneric("of")?> 2 : <?=$page->drawLabel("tl_task_choose_header", "Choose from tasks allocated to you")?></h2>
	<p>View event <a href="?action=history">history</a></p>
    <?php 
	if ($tasks->total>0) {
		if (is_array($tasks->tasklist)) {
			foreach($tasks->tasklist as $result) {
			
				$ex_link = $ex_class = '';
				//print_r($result);
				
				//print "switch (".$result->title.")<br>\n";
				switch($result->title) {
					case "pages-comments" : 
						$task_title=$page->drawLabel("tl_task_title_comment", "New comment");
						break;
					case "panels" :
						$ex_link = "&KeepThis=true&TB_iframe=true&height=500&width=".$site->getConfig('site_page_width');
						$ex_class="thickbox";
					case "pages" :
						$task_title=$page->drawLabel("tl_task_title_publish", "Awaiting publication");
						break;
					case "Page note": 
						$task_title=$page->drawLabel("tl_task_title_note", "Page note from")." ".$result->from;
						break;
					default: 
						$task_title=$page->drawLabel("tl_task_t_".substr(str_replace(" ", "_", $result->title),0,10), $result->title);
						break;
				}
				
				$allocated_to=$result->uid>0?$page->drawGeneric("you", 1):$page->drawGeneric("any", 1)." ".$page->drawGeneric($result->group);
				
				if (!$result->id && $result->guid) $action_link="/treeline/tasks/?guid=".$result->guid."&amp;hid=".$result->hid;
				else if ($result->id) $action_link="/treeline/tasks/?tid=".$result->id."&amp;guid=".$result->guid."&amp;hid=".$result->hid;
				
				$html.='<tr>';
				$html.='<td nowrap><a href="'.$action_link.'">'.$task_title.'</a></td>';
				$html.='<td>';
				if ($result->page_title && $result->guid) $html.='<a class="'.$ex_class.'" href="'.$page->drawLinkByGUID($result->guid).'?mode=preview'.$ex_link.'" target="_blank">'.$result->page_title.'</a>';
				$html.='</td>';
				$html.='<td nowrap>'.$result->added.'</td>';
				$html.='<td nowrap>'.$allocated_to.'</td>';
				$html.='</tr>';
			}
		}
	}
	else $html='<tr><td>'.$page->drawLabel("tl_task_alloc_none", "There are no tasks allocated to you").'</td></tr>';
	
	if ($html) {
		?>
        <table class="tl_list" style="width: auto;">
        <thead>
            <tr>
                <th scope="col"><?=$page->drawGeneric("task", 1)?></th>
                <th scope="col"><?=$page->drawGeneric("page", 1)?></th>
                <th scope="col"><?=$page->drawGeneric("date", 1)?></th>
                <th scope="col"><?=$page->drawLabel("tl_task_alloc_to", "Allocated to")?></th>
            </tr>
        </thead>
        <tbody>
            <?=$html?>
        </tbody>
        </table>
		<?php 
    } 
}



?>
</div>
</div>
<?php 

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php");

?>