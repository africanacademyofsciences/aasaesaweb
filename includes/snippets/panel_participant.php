<?php 

$query="select * from event_entry ee 
	left join members m on ee.member_id=m.member_id
	where ee.event_guid='".$page->getGUID()."'";
//print "$query<br>";
$results=$db->get_results($query);

// Dont bother showing the participant list if noone is subscribed to the event
if ($db->num_rows) {
	?>
    <div class="panel panel_5">
        <h3>Participant list</h3>
        <?php if ($_GET['action']=="list" && $mode!="edit") { 
            $query="select * from event_entry ee 
                left join members m on ee.member_id=m.member_id
                where ee.event_guid='".$page->getGUID()."'";
            //print "$query<br>";
            if ($results) {
                foreach($results as $result) {
                    if ($result->firstname || $result->surname) {
                        echo '<p><a href="'.$page->drawLinkByGUID($result->pp_guid).'">'.($result->grp_title?$result->grp_title:$result->firstname.' '.$result->surname).'</a></p>';
                    }
                }
            }
        
            ?>
            <p><a href="<?=$page->drawLinkByGUID($page->getGUID())?>">Hide participant list</a></p>
        <?php } else if ($mode=="edit") { ?>
            <p>Participant listing disabled in edit mode</p>    
        <?php } else { ?>
            <p><a href="<?=$page->drawLinkByGUID($page->getGUID())?>?action=list">View all people signed up for this event</a></p>
        <?php } ?>
    </div>
    <?php
   
}


?>