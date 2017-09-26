<?php
if (!$guid) {
	?>
    <p>No event was selected, please select an event from the drop down list.</p>
    <?php
}
else {
	$query="select m.*, ee.*, eed.cb_terms from event_entry ee 
		inner join members m on ee.member_id=m.member_id
		left join event_entry_data eed on ee.id=eed.entry_id
		where ee.event_guid='".$guid."'";
	//print "$query<Br>";
	if ($results=$db->get_results($query)) {
		$total=$db->num_rows;
		?>
		<table class="treeline">
		<caption><?php echo getShowingXofX($perPage, $currentPage, sizeof($results), $total); ?> Members</caption>
		<thead>
			<tr>
				<th scope="col">Preview</th>
				<th scope="col">Member name</th>
                <th scope="col">Sponsorship</th>
				<th scope="col">Email address</th>
				<th scope="col">Sponsor</th>
				<th scope="col">Data</th>
				<th scope="col">Remove</th>
				<!-- 
				<th scope="col">Delete</th> 
				<th scope="col">Approve</th>
				-->
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($results as $result){ // loop through and show results
	
			//remove from event
			//sponsor
	
			$mem_name = $result->firstname." ".$result->surname;
			if (!$mem_name) $mem_name="No name"; 
			?>
			<tr>
			<td class="action preview"><a href="/treeline/members/?id=<?php echo $result->member_id; ?>" title="Preview">Preview this member</a></td>
			<td><?=$mem_name?></td>
            <td>&pound;<?=$result->sponsorship?></td>
			<td><?=$result->email?></td>
			<td class="action edit"><a href="/treeline/events/?m_id=<?php echo $result->member_id; ?>&amp;action=sponsor&amp;guid=<?=$guid?>" title="Edit">Sponsor this member</a></td>
			<td class="action preview">
            	<?php if ($result->cb_terms) { ?>
	            	<a href="/treeline/events/?m_id=<?php echo $result->member_id; ?>&amp;action=preview register form&amp;guid=<?=$guid?>" title="Preview data">View member form</a>
              	<?php } ?>
            </td>
			<td class="action delete"><a href="/treeline/events/?m_id=<?php echo $result->member_id; ?>&amp;action=remove-member&amp;guid=<?=$guid?>" title="Edit">Remove member from event</a></td>
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
		<p>There are no members participating in this event</p>
		<?php
	}
}
?>



