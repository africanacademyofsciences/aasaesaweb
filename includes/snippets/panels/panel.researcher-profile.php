<?php

global $member, $pledge, $global_panelmode;
//echo "print member(".print_r($member->details, 1)."\n";
//echo "Pledge p(".print_r($pledge, 1)."\n";

?>
<h4>Project details</h4>
<ul>
<li>Ref#: <strong><?=$pledge->reference?></strong></li>
<?php
if ($member->details->profile) {
	?>
	<li>Organisation: <strong><?=$member->details->organisation?></strong></li>
	<?php
}
?>
<li>Name: <strong><?=$member->details->firstname?> <?=$member->details->surname?></strong></li>
<?php
if ($member->details->profile) {
	?>
    <li class="hide">Bio: <?=$member->details->profile?></li>
    <?php
}
?>
<li>Target: <?=$pledge->currency?><?=number_format($pledge->target, 0, ".", ",")?></li>
<?php
if ($pledge->count) {
	?>
	<li>Total pledged: <?=$pledge->currency?><?=(number_format($pledge->total, 0, ".", ","))?></li>
	<li>Number of pledges: <?=$pledge->count?> </li>
    <?php
}

if ($global_panelmode!="pdf" && $auto_pdf_mode=="show") {
	?>
	<li><a href="?pdf" target="_blank">View PDF</a></li>
    <?php
}

//print "Got member(".print_r($member, 1).")<br>\n";
if ($member->getMemberImage($member->details->access_id)) {
//if ($member->getMemberImage($member->details->member_id)) {
	$photo = $member->getMemberImage($member->details->access_id);
	//print "Got photo($photo)<br>\n";
	?>
	<li class="photo"><img src="<?=$photo?>" /></li>
    <?php
}
?>
</ul>
