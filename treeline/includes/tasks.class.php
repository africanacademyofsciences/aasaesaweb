<?php

class Tasks {

	public $msv;
	public $total;
	public $tasklist=array();
	
	public $table;
	public $info;
	
	// 9th Jan 2009 - Phil Redclift
	// Construct the tasks class and load a list of tasks if we are loading from Treeline
	public function Tasks($msv=1, $userid=0) {
		
		$this->msv=$msv;
		if ($userid>0) $this->loadList();
		
	}
	
	// 9th Jan 2009 - Phil Redclift
	// Function to add new tasks to the tasks list.
	// Groups are 1 - Superuser, 2 - Publisher, 3 - Author
	public function add($userid, $title, $guid='', $info='', $group=3, $addedby=0) {
		global $db;
		$query="INSERT INTO tasks
			(user_id, date_added, title, msv, `group`, guid, user_added, description)
			VALUES 
			($userid, NOW(), '$title', ".$this->msv.", 
			".($group+0).", '$guid', ".($addedby>0?$addedby:"NULL").", '$info')";
		//print "$query<br>\n";
		return $db->query($query);
	}
	
	// 9th Jan 2009 - Phil Redclift
	// Mark a task as completed.
	public function completed($id) {
		global $db;
		$query="update tasks set completed=NOW() where id=$id";
		//print "$query<br>\n";
		return $db->query($query);
	}


	// 9th Jan 2009 - Phil Redclift
	// Notify all applicable Treeline users of an event
	// Permissions 
	// Author(+) at the end signifies everyone that level and up
	// Author=7 Publisher=7, Super=15. I know it makes no sense.
	public function notify($action, $data, $user_id=0, $access="Publisher+") {
		global $db, $site;
		$testing = false;
		if ($testing) print "<!-- t::n($action, ".print_r($data, true).", $user_id, $access) -->\n";
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/subscriber.class.php');
		include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

		$newsletter = new Newsletter();

		switch($access) {
			case "Author":
				$access_q="AND p.level=3 ";
				break;
			case "Author+":
				//This is everybody!
				//$access_q="AND p.level>=3 ";
				break;
			case "Publisher":
				$access_q="AND p.level=7 ";
				break;
			case "Publisher+":
				$access_q="AND p.level>=7 ";
				break;
			default : 
			case "Superuser":
			case "Superuser+":
				$access_q="AND p.level=15 ";
				break;
		}
		$query="select u.id AS user_id,
			full_name, u.`group`, email 
			FROM users u
			LEFT JOIN groups g on u.`group`=g.id
			LEFT JOIN sites_versions sv ON g.domain=sv.msv 
			LEFT JOIN permissions p ON g.id=p.`group`
			WHERE u.blocked=0
			$access_q ";
		if ($action != "shared-delete") $query.="AND sv.msv=".$site->id." ";
		if ($user_id>0) $query.="AND u.id=".$user_id;
		if ($testing) print "<!-- find admins ($query) -->\n";
		if ($results=$db->get_results($query)) {
			$send_counter = 0;
			foreach ($results as $result) {
				$data['treeline_user_id']=$result->user_id;
				//print "added uid(".$data['treeline_user_id'].")<br>\n";
				if ($testing) print "<!-- send to admin(".$result->email.") -->\n";
				switch (strtolower($action)) {
					case "publish" :
						$newsletter->sendText($result->email, "PUBLISH-REQUIRED", $data, false); 
						//mail("phil.redclift@ichameleon.com", "Cam notify", getcwd()."\n\n"."Nofify ".$result->email." of ".$action."<br>\n");
						break;
					case "new-note" : 
						if ($user_id>0) {
							$newsletter->sendText($result->email, "EDIT-NOTE-NOTIFY", $data, false);
						}
						break;
					case "abuse-report": 
						if ($data['MEMBER-ID']>0) {
							$query = "SELECT firstname, surname, email FROM members WHERE member_id=".$data['MEMBER-ID'];
							//print "<!-- collect member data($query) -->\n";
							if ($row=$db->get_row($query)) {
								$data['ABUSE-DETAILS']="Reported by ".$row->firstname." ".$row->surname." email: ".$row->email;
							}
						}
						if (!$data['ABUSE-DETAILS']) $data['ABUSE-DETAILS']="";
						$newsletter->sendText($result->email, "ABUSE-REPORT", $data, false);
						break;
					case "publish-comment" : 
						$newsletter->sendText($result->email, "COMMENT-NOTIFY", $data, false);
						break;
					case "shared-delete" : 
					case "event-apply" :
					case "event-ticket-print" :
						$newsletter->sendText($result->email, strtoupper($action), $data, false);
						break;
					case "new-registration":
						if ($testing) {
							print "<!-- actually sending now to (".$result->email.") -->\n";
							$newsletter->sendText("phil@treelinesoftware.com", strtoupper($action), $data, false, true);
						}
						else {
							$newsletter->sendText($result->email, strtoupper($action), $data, false);
						}
						break;
					default : 
						mail("phil.redclift@ichameleon.com", $site->name, "FAILED to notfiy ".$result->full_name." at ".$result->email." of ".$action."\n");
						break;
				}	
				$send_counter++;		
			}
		}
		//else print "No users to notify<br>\n";
	}
	
	
	// 9th Jan 2009 - Phil Redclift
	// Load a list of items awaiting action by this user
	// This includes uncompleted items in the task table
	// and unpublished items in the history table that have a valid page relating to them.
	public function loadList() {
	
		global $db, $user;
		
		// Compile task query
		$task_query="SELECT t.id, t.user_id as uid, 0 as hid, t.guid as guid, 0 as template, 
			t.date_added, date_format(t.date_added, '%D %M %Y') as added, 
			t.title, pg.title as page_title,
			g.name as `group`,
			u2.full_name as `from`
			FROM tasks t
			INNER JOIN sites_versions sv ON sv.msv = t.msv
			LEFT OUTER JOIN permissions p ON sv.microsite=p.guid
			LEFT JOIN groups g on p.`group`=g.id
			LEFT OUTER JOIN pages pg ON t.guid = p.guid
			LEFT OUTER JOIN users u on (p.`group` = u.`group` AND t.user_id=u.id)
			LEFT OUTER JOIN users u2 ON t.user_added=u2.id
			WHERE t.msv=".$this->msv." AND 
				(
				t.user_id=".$user->id." 
				OR (t.user_id=0 AND p.level>=t.`group`)
				) 
			AND completed is NULL
			GROUP by t.id";
		$publish_query="SELECT 0 as id, 0 as uid, h.id as hid, p.guid, p.template, 
			h.date_added, date_format(h.date_added, '%D %M %Y') as added, 
			h.table as title, p.title as page_title,
			'Publisher' as `group`, 
			'' AS `from`
			FROM history h
			INNER JOIN pages p on h.guid=p.guid
			WHERE h.action='publish' AND h.completed_action IS NULL
			AND h.msv=".$this->msv."
			GROUP by p.guid, h.info
			ORDER BY date_added ASC";
		//print "$task_query<br>\n";
		//print "<!-- $task_query UNION $publish_query --> \n";
		
		// Get the task count for this user
		$this->tasklist=$db->get_results($task_query." UNION ".$publish_query);
		$this->total=$db->num_rows;
		
		return;
	}

	public function loadByGUID($guid, $hid=0) {
		global $db;
		$query = "SELECT p.*, 
			IF(date_published>date_modified,date_format(date_published, '%D %b %Y'),IF(date_modified>date_created,date_format(date_modified, '%D %b %Y'),date_format(date_created, '%D %b %Y'))) as updated,
			IF(date_published>date_modified,user_published,IF(date_modified>date_created,user_modified,user_created)) as updated_by,
			IF(max(revision_id>0),1,0) AS publishable, IF (u.lock_guid,1,0) AS being_edited,
			pt.template_type,
			h.info
			FROM pages p
			LEFT JOIN content c on c.parent=p.guid
			LEFT JOIN history h ON p.guid=h.guid
			LEFT JOIN pages_templates pt on p.template=pt.template_id
			LEFT JOIN users u on u.lock_guid=p.guid
			WHERE p.guid='$guid'
			".($hid>0?"AND h.id=".$hid:"")."
			GROUP by p.guid
			LIMIT 1";
		//print "$query<br>\n";
		if ($row=$db->get_row($query)) {

			$uquery="SELECT full_name FROM users where id=".$row->updated_by;
			//print "$uquery<br>\n";
			$this->guid=$guid;
			$this->type=$row->template_type==2?"panel":"page";
			$this->pagename=$row->title;
			$this->template=$row->template;
			$this->pagehidden=$row->hidden;
			$this->pagelocked=$row->being_edited;
			$this->pageoffline=$row->offline;
			$this->publishable = $row->publishable;
			$this->updated=$row->updated;
			$this->updated_by=$db->get_var($uquery);
			$this->info = $row->info;
		}
	}
	
	public function loadByID($id) {
		global $db;
		$query = "SELECT t.*, 
			date_format(t.date_added, '%D %b %Y') as added, 
			date_format(t.completed, '%D %b %Y') as completed_date, 
			p.title as page_title, p.template, p.hidden, p.offline,
			IF(date_published>date_modified,date_format(date_published, '%D %b %Y'),IF(date_modified>date_created,date_format(date_modified, '%D %b %Y'),date_format(date_created, '%D %b %Y'))) as updated,
			IF(date_published>date_modified,user_published,IF(date_modified>date_created,user_modified,user_created)) as updated_by,
			IF(max(c.revision_id>0),1,0) AS publishable, IF (u2.lock_guid,1,0) AS being_edited,
			g.name as group_name,
			pt.template_type
			FROM tasks t
			LEFT JOIN pages p on t.guid=p.guid
			LEFT JOIN content c on c.parent=p.guid
			LEFT JOIN pages_templates pt ON p.template=pt.template_id
			LEFT JOIN groups g on t.`group`=g.id
			LEFT JOIN users u on t.user_added = u.id
			LEFT JOIN users u2 on u2.lock_guid=p.guid
			WHERE t.id=$id
			GROUP by p.guid";
		//print "$query<br>\n";
		if ($row=$db->get_row($query)) {
			$this->id = $id;
			$this->title = $row->title;
			$this->info = $row->description;
			$this->guid = $row->guid;
			$this->type=$row->template_type==2?"panel":"page";
			$this->pagename = $row->page_title;
			$this->placeholder=$row->placeholder;
			$this->pagehidden=$row->hidden;
			$this->pagelocked=$row->being_edited;
			$this->pageoffline=$row->offline;
			$this->template = $row->template;
			$this->publishable = $row->publishable;
			$this->added = $row->added;
			$this->added_by = $row->full_name;
			$this->creator = $row->full_name;
			$this->completed = $row->completed_date;
			$this->group = $row->group_name;
			$this->updated=$row->updated;
			if ($row->updated_by) {
				$uquery="SELECT full_name FROM users where id=".$row->updated_by;
				$this->updated_by=$db->get_var($uquery);
			}
		}
	}

}

?>