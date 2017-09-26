<?php
//print "add edit poll($action) guid($guid)<br>\n";
if( $action=='edit' ){
	
	$mode="edit";
	
	// load poll by ID here...
	$poll->loadByGUID($guid);
	$polldata = $poll->structure;
	//print "got data(".print_r($polldata, true).")<br>\n";	
	$poll_title = $polldata['title'];
	$poll_question = $polldata['question'];
	$poll_response = $polldata['response'];
	$poll_answer_one = $polldata['answers'][1]['text'];
	$poll_answer_two = $polldata['answers'][2]['text'];
	$poll_answer_three = $polldata['answers'][3]['text'];
	$poll_answer_four = $polldata['answers'][4]['text'];
	$poll_answer_five = $polldata['answers'][5]['text'];
	$poll_default_answer = $polldata['default'];
	
	$template_id = $panel->template_id;
}
ob_start();
?>
	<!-- <form action="<?= $thisURL ?>" method="post"> -->
    <input type="hidden" name="action" value="<?=$action?>" />
    <input type="hidden" name="guid" value="<?=$guid?>" />
	<input type="hidden" name="submitted" value="1" />
	<input type="hidden" name="type" value="<?=$template_id?>" />
    <input type="hidden" name="mode" value="<?=$mode?>" />  

    <fieldset>
        <legend><?=ucfirst($page->drawLabel("tl_generic_details", "Details"))?></legend>
        <p class="instructions"><?=$page->drawLabel("tl_poll_message1", "Please set your question and supply some text to show to the user once they've voted on the poll")?></p>
        <label for="poll_title"><?=ucfirst($page->drawLabel("tl_generic_title", "Title"))?></label>
        <input type="text" name="poll_title" value="<?=($_POST?$_POST['poll_title']:$poll_title)?>" /><br />
        
        <label for="poll_question"><?=ucfirst($page->drawLabel("tl_poll_field_q", "Question"))?></label>
        <textarea name="poll_question"><?=($_POST?$_POST['poll_question']:$poll_question)?></textarea><br />
        
        <label for="poll_response"><?=ucfirst($page->drawLabel("tl_poll_field_resp", "Response"))?></label>
        <textarea name="poll_response"><?=($_POST?$_POST['poll_response']:$poll_response)?></textarea><br />              
        
        <label for="f_reset"><?=ucfirst($page->drawLabel("tl_poll_reset", "Reset results"))?></label>
        <input type="checkbox" name="poll_reset" id="f_reset" value="1" style="padding-left:0;width:auto;" /><br />
    </fieldset>

    <fieldset id="answers" style="margin-top:20px;">
        <legend><?=ucfirst($page->drawLabel("tl_poll_answers_title", "Answers"))?></legend>
        <p class="instructions"><?=$page->drawLabel("tl_poll_message2", "You can specify up to 5 answers to your poll. If there is a correct answer to the question, you can select it using the radio button next to it")?></p>

        <label for="poll_answer_one"><?=$page->drawLabel("tl_poll_answer1", "Answer One")?></label>
        <input type="text" name="poll_answer_one" value="<?=($_POST?$_POST['poll_answer_one']:$poll_answer_one)?>" />
        <label for="poll_answer_default" id="poll_default"><?=ucfirst($page->drawLabel("tl_generic_default", "Default"))?></label>
        <input type="radio" name="poll_default_answer" id="poll_default_answer1" class="checkbox" value="1"<?= ($poll_default_answer==1 ? ' checked="checked"' : '') ?> />
        <span class="poll-percent"><?=($polldata['answers'][1]['percentage']+0)?>%</span>
        <br />
        
        <label for="poll_answer_two"><?=$page->drawLabel("tl_poll_answer2", "Answer Two")?></label>
        <input type="text" name="poll_answer_two" value="<?=($_POST?$_POST['poll_answer_two']:$poll_answer_two)?>" />
        <input type="radio" name="poll_default_answer" id="poll_default_answer2" class="checkbox" value="2"<?= ($poll_default_answer==2 ? ' checked="checked"' : '') ?> />
        <span class="poll-percent"><?=($polldata['answers'][2]['percentage']+0)?>%</span>
        <br />

        <label for="poll_answer_three"><?=$page->drawLabel("tl_poll_answer3", "Answer Three")?></label>
        <input type="text" name="poll_answer_three" value="<?=($_POST?$_POST['poll_answer_three']:$poll_answer_three)?>" />
        <input type="radio" name="poll_default_answer" id="poll_default_answer3" class="checkbox" value="3"<?= ($poll_default_answer==3 ? ' checked="checked"' : '') ?> />
        <span class="poll-percent"><?=($polldata['answers'][3]['percentage']+0)?>%</span>
        <br />
        
        <label for="poll_answer_four"><?=$page->drawLabel("tl_poll_answer4", "Answer Four")?></label>
        <input type="text" name="poll_answer_four" value="<?=($_POST?$_POST['poll_answer_four']:$poll_answer_four)?>" />
        <input type="radio" name="poll_default_answer" id="poll_default_answer4" class="checkbox" value="4"<?= ($poll_default_answer==4 ? ' checked="checked"' : '') ?> />
        <span class="poll-percent"><?=($polldata['answers'][4]['percentage']+0)?>%</span>
        <br />
        
        <label for="poll_answer_five"><?=$page->drawLabel("tl_poll_answer5", "Answer Five")?></label>
        <input type="text" name="poll_answer_five" value="<?=($_POST?$_POST['poll_answer_five']:$poll_answer_five)?>" />
        <input type="radio" name="poll_default_answer" id="poll_default_answer5" class="checkbox" value="5"<?= ($poll_default_answer==5 ? ' checked="checked"' : '') ?> />
        <span class="poll-percent"><?=($polldata['answers'][5]['percentage']+0)?>%</span>
        <br />

    </fieldset>

    <fieldset class="buttons">
        <input type="submit" class="submit" id="f_submit" value="<?=ucfirst($page->drawLabel("tl_generic_save", "Save"))?>" />
    </fieldset>
	<!-- </form> -->
<?php
$page_html .= ob_get_contents();
ob_end_clean();
?>