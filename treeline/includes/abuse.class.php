<?php


class Abuse {

	public $errmsg = array();
	
	public function Abuse() {
		//print "abuse created";
	}
	
	public function report($id, $type, $from_email="") {
		global $db, $site;
		//print "A::r($id, $type, $from_email, $captcha)<br>\n";
		
		if (!$id || !$type) $this->errmsg[]="Failed to report abuse - data error";
		else if (!$from_email || !is_email($from_email)) $this->errmsg[]="Email address invalid";
		else {
			if ($history_id = addHistory(($_SESSION['member_id']+0), "abuse-report", $id, $from_email, $type)) {				
				// Send email to the reporter
				//print "got history id($history_id)<br>\n";
				include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
				include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
				include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
				$report_link = '<a href="'.$site->link.'/'.$type.'/?action=report&id='.$history_id.'">Report this item as abusive</a>';
				$data = array("REPORT_LINK"=>$report_link);
				$newsletter = new Newsletter();
				if ($newsletter->sendText($from_email, "ABUSE-REQUEST", $data)) return true;
				else $this->errmsg[]="Failed to send report email to $from_email";

				//return $this->suspend($type, $id);
				return true;
			}
			else $this->errmsg[]="Unexpected failure to log report";
		}
		return false;
	}
	
	// Suspend an item from the history table.
	// we should be able to get the table, field and post id from the history id.
	public function suspendFromHistory($history_id) {
		global $db;
		$task = new Tasks();
		$max_report_days = 7;
		
		//print "sFH($history_id)<br>\n";
		// We can report and or suspend confirmed abuse reports
		$report_abuse_to_admin = true;
		$suspend_confirmed_abuse = true;
		
		if ($info = $this->getReportInfo($history_id)) {
			//print "got details (".print_r($info, true).")<br>\n";
			if ($info->complete) $this->errmsg[]="This abuse report has already been finalised. You can only click this link one time.";
			else if ($info->age > $max_report_days) $this->errmsg[]="This abuse report was submitted ".$info->age." days ago. You will need to submit a new abuse report and click the follow up link within $max_report_days days if you still wish to report this item as abuse.";
			else {
				$success = true;
				// DO WE JUST REPORT IT?
				if ($report_abuse_to_admin) {
					//print_r($info);
					if ($info->firstname) $data['ABUSE-NAME']="Reported by ".$info->firstname." ".$info->surname;
					if ($info->email) $data['ABUSE-EMAIL']="Confirmed report by ".$info->email;
					$data['ITEM-TITLE']=$info->title;
					$data['ABUSE-SECTION']=$info->table;
					$data['ABUSE-PAGE']=$info->table;
					$task->notify('ABUSE-REPORT', $data);
				}
				
				// Update the history record to shcw the abuse report has been completed
				$query = "UPDATE history SET completed_date=NOW(), completed_by=0, completed_action='CONFIRMED' WHERE id = ".$history_id;
				$db->query($query);

				// DO WE ACTUALLY SUSPEND IT?
				if ($suspend_confirmed_abuse) {
					$success = $this->suspend($info->table, $info->id, $history_id);
					$task->add(0, "Abuse reported", '', $info->table." id:".$info->id, 1);
				}
				
				return $success;
			}
		}
		else $this->errmsg[]="Failed to collect data for this report ID";
		return false;

	}
	
	public function suspend($table, $id, $history_id=0) {
		global $db, $site;
		
		$email = '';
		if (!$table || !$id) {
			$this->errmsg[]="Suspend not passed enough information to proceed";
		}
		else {
			$field = "id";
			
			// 1 - Suspend the item
			if ($table=="forum") { 
				// Little bit of fixing for forums.
				$title = $db->get_var("SELECT title from forum_posts WHERE post_id=".$id);
				$query = "UPDATE forum_posts SET suspended=-1 WHERE post_id=$id";
			}
			else {
				$title = $db->get_var("SELECT title from $table WHERE $field=".$id);
				$query = "UPDATE $table SET suspended=-1 WHERE $field=$id";
			}
			//print "$query<br>\n";
			if ($db->query($query)) {
				// 2 - Add report and email admin team to let them know they should investigate.
				addHistory(-1, ($history_id>0?"abuse":"suspend"), $id, $history_id?"history-".$history_id:'', $table);
				return true;
			}
			else $this->errmsg[]="Failed to mark this post as suspended. Its possible this post has already been suspended.";
		}
		return false;
	}

	// This updates any outstanding reports associated with and id
	// there might not be any so we dont worry if the query does not update any records.
	public function update($id, $type, $status) {
		global $db, $site;
		//print "u($id, $type, $status)<br>\n";
		$query = "UPDATE history SET 
			completed_date=NOW(), completed_by=".$_SESSION['treeline_user_id'].",
			completed_action = '".strtoupper($status)."'
			WHERE msv=".$site->id." 
			AND action='abuse' AND `table`='$type' 
			AND guid='$id' AND completed_date IS NULL";
		$db->query($query);
		//print "$query<br>\n";
		
		// Also need to check if there are any tasks relating to this report and remove them from 
		// the task list
		$query = "UPDATE tasks SET completed=NOW() 
			WHERE description='".$type.' id:'.$id."'
			AND completed IS NULL";
		//print "$query<br>\n";
		$db->query($query);
	}
	

	// Manage abuse reports....
	public function manage($type) {
		global $db, $page, $site, $help;
		$html = '';
		
		$table=$type; 
		$field="id";
		$action_page = '';
		if ($type=="forum") { $table="forum_posts"; $field="post_id"; $action_page="forum"; $action_field="post"; }
		else if ($type=="blogs") {  $action_page="blogs"; $action_field="bid"; }
				
		$query = "SELECT h.guid as id, 
			date_format(h.date_added, '%D %b %Y') as added,
			t.* 
			FROM history h
			INNER JOIN $table t ON h.guid = t.$field
			WHERE h.msv=".$site->id."
			AND h.action='abuse' AND h.completed_date IS NULL 
			AND `h`.`table`='$type'
			ORDER BY date_added DESC";
		//print "$query<br>\n";
		
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$html.='<tr>
<td><a '.$help->drawInfoPopup($page->drawLabel("tl_abuse_help_view", "View full post")).' href="'.$site->link.$action_page.'/?'.$action_field.'='.$result->id.'&amp;admin" target="_blank">'.$result->title.'</a></td>
<!-- <td>'.$result->report_count.'</td> -->
<td align="" nowrap>'.$page->languageDate($result->added).'</td>
<td class="action">
	<a '.$help->drawInfoPopup($page->drawLabel("tl_abuse_help_delete", "Delete this item")).' href="?'.$action_field.'='.$result->id.'&amp;action=abuse-delete" class="delete">Suspend</a>
	<a '.$help->drawInfoPopup($page->drawLabel("tl_abuse_help_unsuspend", "Unsuspend this item")).' href="?'.$action_field.'='.$result->id.'&amp;action=abuse-restore" class="publish">Unsuspend this item</a>
</td>
</tr>
';
			}
			if ($html) $html = '<table class="tl_list"><thead>
<tr>
	<th>'.$page->drawGeneric("author", 1).'</th>
	<!-- <th>'.$page->drawLabel("tl_abuse_field_report", "Reports").'</th> -->
	<th>'.$page->drawLabel("tl_abuse_field_last", "Last report").'</th>
	<th>'.$page->drawGeneric("manage", 1).'</th>
</tr>
</thead>
<tbody>
	'.$html.'
</tbody>
</table>';
		}
		return $html;
		
		
	}
		
	private function getReportInfo($report_id) {
		global $db, $site;
		$msg = "<!-- Compile report info for abuse report ($report_id) --> \n";
		$query = "SELECT 
			h.guid as id, h.guid, h.table, h.info AS email,
			datediff(NOW(), h.date_added) AS age,
			IF(h.completed_date IS NULL,0,1) AS complete,
			m.member_id, m.firstname, m.surname
			FROM history h
			LEFT JOIN members m ON m.member_id=h.user_id 
			WHERE h.id=".$report_id."
			LIMIT 1
			";
		if ($type) $query.="tt.title AS item_title, ";
		if ($type) $query.="LEFT JOIN $type tt ON tt.id = h.giud ";
		$msg .= "<!-- $query -->\n";
		if ($info = $db->get_row($query)) {
			if ($info->table == "blogs") {
				$query = "SELECT * FROM blogs WHERE id = ".$info->guid;
				//print "$query<br>\n";
				if ($row = $db->get_row($query)) {
					$msg.="<!-- to table data(".print_r($row, true).") -->\n";
					$info->title = $row->title;
				}
			}
		}
		$msg.="<!-- r info( \n".print_r($info, true).") -->\n";
		//print $this->uncomment($msg);
		//mail("phil.redclift@ichameleon.com", $site->title." abuse report checked(comments)", $msg);
		return $info;
	}

	private function uncomment($s) {
		return str_replace(array("<!--","-->","\n"), array("", "", "<br>\n"), $s);
	}
		
}


?>