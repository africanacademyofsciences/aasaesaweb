<?php
include_once($_SERVER['DOCUMENT_ROOT'] .'/treeline/includes/poll.class.php');
//print "Load poll($panelGUID)<br>\n";
$poll = new Poll($panelGUID);
$polldata = $poll->structure;


if ($page->getMode()=="inline") {
 	echo '
	<div id="panel-editor-'.$panelGUID.'" class="panel-editor panel '.$panelStyle.'" style="display:none;">
	<h3>'.$page->drawTitle().'</h3>
	<p>This panel type cannot be edited inline. You will need to use the Treeline panel edit function to modify this panel content.</p>
	</div>		
	';
}

?>
<!-- SHOW ACTUAL PANEL CONTENT -->

<div id="panel-<?=$panelGUID?>" class="panel <?=$panelStyle?>">
    <h3><?= $page->drawTitle()?></h3>
    <?php 	
	if ($mode=="edit" || $page->getMode()=="inline") {
		?>
        <p>Poll form disabled in edit mode</p>
        <?php
	}
	else if( 
		($_POST['poll_vote']!=$polldata['guid'] && $_SESSION['voted_poll_'.$page->getGUID()]!=1) ||
		$mode=="preview") 
		{ 
		?>
    	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" class="pollpanel" name="poll">
        <fieldset>
        	<input type="hidden" name="poll_vote" value="<?=$polldata['guid']?>" />
            <input type="hidden" name="poll_guid" value="<?=$polldata['guid']?>" />
        	
            <p class="pollquestion"><?= $polldata['question'] ?></p>

			<?php
			if (is_array($polldata['answers'])) {
				foreach( $polldata['answers'] as $id => $answer ){ 
				?>
                <div class="pollanswer">
                    <input type="radio" name="poll_answer" class="radio" id="poll_<?=$page->getGUID()?>_answer<?= $id ?>" value="<?= $id ?>" />
                    <label for="poll_<?=$page->getGUID()?>_answer<?= $id ?>"><?= $answer['text'] ?></label><br />
                </div>
                <input type="hidden" name="poll_totalvotes_<?= $id ?>" value="<?= $answer['votes']?>" />
                <?php
				}
            }
			?>
            <input type="submit" id="pollsubmit" value="Vote" <?=($mode=="preview"?'disabled="disabled"':"")?> />
        </fieldset>
        </form>
    	<? 
	} 
	else { 
		?>
        <p class="pollquestion"><?= $polldata['response'] ?></p>
        <ul class="pollresults">
        <?php
		if (is_array($polldata['answers'])) {
			foreach( $polldata['answers'] as $id => $answer ){ 
			?>
            <li<?= ( $answer['default']==1 ? ' class="poll_correct_answer"' : '' ) ?>>
                <span class="poll_answer"><?= $answer['text'] ?></span>
                <span class="poll_votes"><?= $answer['votes'] ?></span>
                <span class="poll_percentage"><?= $answer['percentage'] ?>%</span>
                <div class="poll_result_bar">
                    <span class="colourBar" style="width: <?=$answer['percentage']?>% !important"></span>
                    <span class="whiteBar" style="width: <?=(100 - $answer['percentage'])?>% !important"></span>
                </div>
            </li>
            <?php
			}
        }
		?>
        </ul>
		<? 
	} 
	?>

</div>