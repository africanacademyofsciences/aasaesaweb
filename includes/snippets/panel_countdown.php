<div class="panel sponsor">
	<?php 
		$query="select datediff(start_date, now()) from events where guid='".$page->getParent()."'";
		//print "$query<br>";
		$days=$db->get_var($query);
		$dayss=(($days>0)?$days." day".(($days>1)?"s":""):'');
		if ($dayss) {
			?>
            <h1 class="dark"><?=$dayss?></h1>
            <p class="dark">Until the <?=$event->title?> begins</p>
        	<?php 
		}
	?>
    <p class="dark"><a class="dark_arrow" href="<?=$siteLink?>shopping-basket/?eid=<?=$page->getParent()?>::<?=$event->pp['member_id']?>">Sponsor <?=($event->pp['grp_title']?"this group":$event->pp['firstname'])?></a></p>
    <?php if (!$event->own_event) { ?>
	    <p class="dark"><a class="dark_arrow" href="<?=$page->drawLinkByGUID($page->getParent())?>?action=list">Sponsor another participant</a></p>
	    <p class="dark"><a class="dark_arrow" href="<?=$siteLink?>shopping-basket/?eid=<?=$page->getParent()?>">Sign up now</a></p>
	<?php } ?>       
    <p class="dark" style="padding-bottom:4px;"></p>
</div>
