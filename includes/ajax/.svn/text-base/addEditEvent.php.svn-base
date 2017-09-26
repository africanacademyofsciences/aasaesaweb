<?php

// ADD/EDIT PLUGIN

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/formDate.class.php');
$formDate = new formDate();
include($_SERVER['DOCUMENT_ROOT'].'/treeline/events/getCurrentDate.inc.php');


$title = read($_POST,'title', ''); // title
$description = read($_POST,'description', ''); // description
$venue = read($_POST,'venue', ''); // venue
$member_id = ($memberLogin->getId()) ? $memberLogin->getId() : NULL; // member_id


if($action == 'edit'){
	$title = read($_POST,'title', $result->title); // title
	$description = read($_POST,'description', $result->description); // description
	$venue = read($_POST,'venue', $result->venue); // venue
	$currentDay = ($_POST['date']['day']) ? $_POST['date']['day'] : date('d',strtotime($result->start_date));
	$currentMonth = ($_POST['date']['month']) ? $_POST['date']['month'] : date('m',strtotime($result->start_date));
	$currentYear = ($_POST['date']['year']) ? $_POST['date']['year'] : date('Y',strtotime($result->start_date));
	$currentHour = ($_POST['date']['hour']) ? $_POST['date']['hour'] : date('H',strtotime($result->start_date));
	$currentMinute = ($_POST['date']['minute']) ? $_POST['date']['minute'] : date('i',strtotime($result->start_date));
	$currentEndDay = ($_POST['end_date']['day']) ? $_POST['end_date']['day'] : date('d',strtotime($result->end_date));
	$currentEndMonth = ($_POST['end_date']['month']) ? $_POST['end_date']['month'] : date('m',strtotime($result->end_date));
	$currentEndYear = ($_POST['end_date']['year']) ? $_POST['end_date']['year'] : date('Y',strtotime($result->end_date));
	$currentEndHour = ($_POST['end_date']['hour']) ? $_POST['end_date']['hour'] : date('H',strtotime($result->end_date));
	$currentEndMinute = ($_POST['end_date']['minute']) ? $_POST['date']['minute'] : date('i',strtotime($result->end_date));
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

	if($action == 'add' || $action == 'create'){
		$message = $event->add();
	}
	else{
		$message = $event->edit($eventId);
	}

}

if(!$member_id || ($action =='edit' && $member_id  && ($member_id != $result->member_id))){
		$message = ($action == 'add') ? 'Only logged in members can add events' : 'You cannot edit this event';
}


echo drawFeedback('error',$message);
if(($action == 'add' && $member_id) || ($action == 'edit' && $result->member_id && ($member_id == $result->member_id))){
?>
<form id="<?php echo $action; ?>EventForm" action="" method="post">
    <fieldset>
        <legend><?php echo ucwords($action) ?> Event</legend>
        <p class="instructions"><?php echo ucwords($action) ?> an event with this form.</p>
				<label for="title" class="required">Event name:</label>
				<input type="text" value="<?php echo $title; ?>" id="title" name="title" /><br />
				<label for="venue" class="required">Location:</label>
				<input type="text" value="<?php echo $venue; ?>" id="venue" name="venue" /><br />
				<label for="description" class="required">Description:</label>
    			<textarea id="description" name="description" rows="5" cols="10"><?php echo $description; ?></textarea><br />
				<fieldset class="date">
                	<legend>Dates and times</legend>
                    <fieldset class="date">
                    <legend>Start date</legend>
                    <?php echo $formDate->getDay('date[day]',$currentDay); ?>
            		<?php echo $formDate->getMonth('date[month]',$currentMonth); ?>
            		<?php echo $formDate->getYear('date[year]',$currentYear, 2, 'future'); ?>
                    </fieldset>
                    <fieldset class="date">
                    <legend>Start time</legend>
                    <?php echo $formDate->getHour('date[hour]',$currentStartHour); ?>
            		<?php echo $formDate->getMinute('date[minute]',$currentStartMinute); ?>
                    </fieldset>
                    <fieldset class="date">
                    <legend>End date</legend>
                    <?php echo $formDate->getDay('end_date[day]',$currentEndDay); ?>
            		<?php echo $formDate->getMonth('end_date[month]',$currentEndMonth); ?>
            		<?php echo $formDate->getYear('end_date[year]',$currentEndYear, 2, 'future'); ?><br />
					</fieldset>
                    <fieldset class="date">
                    <legend>End time</legend>
                    <?php echo $formDate->getHour('end_date[hour]',$currentEndHour); ?>
            		<?php echo $formDate->getMinute('end_date[minute]',$currentEndMinute); ?>
                    </fieldset>
                </fieldset>
                <input type="hidden" name="action" value="<?php echo $action; ?>" />
                <input type="hidden" name="member_id" value="<?php echo $member_id; ?>" />
				<fieldset class="buttons">
					<button type="submit" class="submit"><?php echo ($action == 'create' || $action == 'add') ? 'Add event' : 'Submit changes'; ?></button>
				</fieldset>	
			</fieldset>
		</form>
 <?php } ?>