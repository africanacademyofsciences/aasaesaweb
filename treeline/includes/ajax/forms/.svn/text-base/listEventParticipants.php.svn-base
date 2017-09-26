<?php
if (!$guid) {
	?>
    <p>No event was selected, please select an event from the drop down list.</p>
    <?php
}
else {
	$query="SELECT ee.id as entry_id,
		eed.title, eed.forenames, eed.surname,
		ee.member_id as member_id, ee.registered,
		ee.`status`,
		eed.email as email,
		IF (ee.registered=0,2,ee.registered) AS order_by
		FROM event_entry ee 
		LEFT OUTER JOIN members m on ee.member_id=m.member_id
		INNER JOIN event_entry_data eed on ee.id=eed.entry_id
		WHERE ee.event_guid='".$guid."'
		AND ee.`status` <> 'New'
		ORDER BY order_by DESC, eed.surname";
	//print "Q - $query<Br>";
	if ($results=$db->get_results($query)) {
		$total=$db->num_rows;
		?>
		<table class="tl_list">
		<caption><?php echo getShowingXofX(200, 1, sizeof($results), $total); ?> Members</caption>
		<thead>
			<tr>
				<th scope="col">Member name</th>
				<th scope="col">Email address</th>
				<th scope="col">Status</th>
				<th scope="col">Manage entrant</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$no_link='<span class="no-action"></span>';
		foreach($results as $result){ // loop through and show results
	
			//remove from event
			//sponsor
			$invitelink='&nbsp;';
			$datalink = $ticketlink = $invitelink = $rejectlink = $memberlink = $no_link;

			if (file_exists($_SERVER['DOCUMENT_ROOT']."/silo/pdf/events/".$guid."/ticket-".$result->entry_id.".pdf")) {
				$ticketlink = '<a '.$help->drawInfoPopup("View PDF ticket").' class="ticket" href="http://'.$_SERVER['SERVER_NAME'].'/silo/pdf/events/'.$guid.'/ticket-'.$result->entry_id.'.pdf" target="_blank">View ticket</a>';
			}
			$datalink='<a '.$help->drawInfoPopup("View application data").' class="preview" href="/treeline/events/?guid='.$guid.'&entry='.$result->entry_id.'&action=preview-register-form" title="View application data">View application data</a>';
			
			switch ($result->registered) {
				case -1: 
					$ticketlink = $datalink = $no_link;
					break;
				case 1: 
					$rejectlink='<a '.$help->drawInfoPopup("Remove from event").' class="delete" href="/treeline/events/?guid='.$guid.'&entry='.$result->entry_id.'&action=remove-entry" title="Delete from event">Delete from event</a></td>';
					break;
				case 0:
				default: 
					$invitelink='<a '.$help->drawInfoPopup("Accept application").' class="edit" href="/treeline/events/?guid='.$guid.'&entry='.$result->entry_id.'&action=accept" title="Invite member">Invite this person</a>';
					$rejectlink='<a '.$help->drawInfoPopup("Reject application").' class="reject" href="/treeline/events/?guid='.$guid.'&entry='.$result->entry_id.'&action=reject" title="Reject application">Reject this application</a></td>';
					break;
			}
			$mem_name = ($result->title?$result->title." ":"").($result->forenames?$result->forenames." ":"").$result->surname;
			
			if (!$mem_name) $mem_name="No name"; 
			if ($result->member_id>0) $memberlink='<a '.$help->drawInfoPopup("View member data").' class="preview" href="/treeline/members/?id='.$result->member_id.'" title="Preview">Preview this member</a>';
			
			?>
			<tr>
			<td><?=$mem_name?></td>
			<td><?=$result->email?></td>
			<td><?=$result->status?></td>
			<td class="action">
				<?=$memberlink?>
				<?=$invitelink?>
            	<?=$datalink?>
                <?=$ticketlink?>
				<?=$rejectlink?>
            </td>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>
		<?php
	}
	else {
		?>
		<p>There are no members attending in this event</p>
		<?php
	}
}
?>



