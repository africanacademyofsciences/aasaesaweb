<?php
	$query="SELECT SUM(amount)
		FROM store_orders_sponsorships sos
		LEFT JOIN store_orders so on sos.order_id=so.order_id
		WHERE sos.member_id=".$event->pp['member_id']." AND sos.event_id='".$page->getParent()."'
		AND sos.amount>0
		AND so.status>0";
	//print "<!-- $query -->";
	$store_sum=$db->get_var($query);

	$query="SELECT SUM(sponsorship)
		FROM event_entry
		WHERE member_id=".$event->pp['member_id']." AND event_guid='".$page->getParent()."'";
	//print "$query<br>";
	$treeline_sum=$db->get_var($query);

	$sum=number_format(($store_sum+$treeline_sum),2);
	if ($sum > $db->get_var("select show_sponsorship from events where guid='".$page->getParent()."'")) {
		?>
        <div class="panel sponsor">
            <h3><?=($event->pp['grp_title']?"":$event->pp['firstname']."'s ")?>sponsorship total</h3>
            <h1 class="red">£<?=$sum?></h1>
            <p class="red"><?=$site->name?> would like to thank <?=($event->pp['grp_title']?"all this groups":$event->pp['firstname']."'s")?> friends and supporters for their generosity! </p>
            <p class="red"></p>
        </div>
        <?php
	}
?>

<div class="panel" style="height:79px;padding-bottom:0px;">
<a id="sponsor_button" href="<?=$siteLink?>shopping-basket/?eid=<?=$page->getParent()?>::<?=$event->pp['member_id']?>">Sponsor me</a>
</div>