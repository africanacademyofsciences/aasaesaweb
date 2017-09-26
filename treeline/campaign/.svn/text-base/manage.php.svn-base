<?php
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	$action = read($_REQUEST,'action','create');
	$campaign_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "id", 0);
	$newsletter_id = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "newsletter", 0);

	$feedback ='error'; 
	$message = array();

	// Not keen but if we have been sent a campaign id then we aint in create mode really.
	if ($campaign_id && !$action) $action="edit";


	/*****LOGIC**********/ 
	//CREATE A CAMPAIGN 
	if ($_SERVER['REQUEST_METHOD']=="POST") {
		
		//required feilds 
		if ($action == "create" || $action=="edit") {
			if(empty($_POST['title'])) $message[] = 'You must enter a title for your campaign';
			else if(!$newsletter_id) $message[]= 'You must select a newsletter to monitor';	
			else if ($action=="create" || ($action=="edit" && $_POST['title']!=$_POST['old_title'])) {
				if ($db->get_var("SELECT id FROM campaigns WHERE title='".$db->escape($_POST['title'])."'")) {	
					$message[] = 'The campaign <strong>'.$_POST['title'].'</strong> already exists. Please try again.';
				}
			}
			
			if (!count($message)) {
	
				// CHeck if this newsletter has already been sent and get the date if it has
				$query = "SELECT send_date, count(nob.newsletter_id) as total
					FROM newsletter n
					LEFT JOIN newsletter_outbox nob ON n.id=nob.newsletter_id 
					WHERE n.id=$newsletter_id
					GROUP by n.id
					";
				//print "$query<Br>\n";
				$send_data = $db->get_row($query);
				
				//Insert query 
				if (!$campaign_id && $action=="create") {
					$query ="INSERT INTO campaigns 
					( newsletter_id, title, date_created, 
					 ".($send_data->send_date?"date_sent, ":"")."mail_count
					) 
					VALUES 
					( '$newsletter_id', '".$db->escape($_POST['title']). "', NOW(),
					 ".($send_data->send_date?"'".$send_data->send_date."',":"").($send_data->total+0)."
					)
					"; 
				}
				else if ($campaign_id && $action=="edit") {
					$query = "UPDATE campaigns SET 
						title='".$db->escape($_POST['title']). "', 
						newsletter_id='$newsletter_id', 
						".($send_data->send_date?"date_sent='".$send_data->send_date."',":"")."
						mail_count= ".($send_data->total+0)."
						WHERE id=$campaign_id";
				}
				//print "$query<br>\n";
				$db->query($query);
				if($db->last_error) $message[] = 'Campaign was not saved. Please try again.';	
			}


		}
					
		else if ($action == "delete") {
			$query = "DELETE FROM campaigns WHERE id=$campaign_id";
			//$message[] = $query;
			if ($db->query($query)) {
				$query = "DELETE FROM campaign_link_stats WHERE campaign_id=$campaign_id";
				$db->query($query);
			}
			else $message[] =  "Failed to delete campaign data";
		}
		
				
		// Assume we did something and it worked.
		if (!count($message)) redirect ("/treeline/campaign/read");
		
	}	
	else {
	
	}


	/*********************/ 
	

	/*Select all newsletters used to create drop down for the form*/  
	$query = "SELECT n.id, n.subject, c.id as campaign_id
		FROM newsletter n
		LEFT JOIN campaigns c ON c.newsletter_id = n.id
		WHERE n.`status`='N'
		AND n.msv = ".$site->id."
		AND n.text3<>'DIGEST'
		";
	if(!$newsletters = $db->get_results($query)){
		$message = "You must create a newsletter before you can start a campaign"; 
		$feedback ="warning"; 
	}		
	/**********************************************************/


	// PAGE specific HTML settings
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript

	
	$curPage = "campaignstats_home";
	$pageClass = 'campaignstats';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
	<?=drawFeedback($feedback,$message);?>
   <? if($newsletters){ ?> 
    <div class="tl-box"  style="width:735px;" >
	<div class="tl-head tl-head-blue">
		<span class="tl-head-left"></span>

		<h2 class="tl-head-right" style="width:711px;">
			<span style="width:711px;">Use the form below to <?=$action?> your campaign</span>
		</h2>
	</div>
	<div class="tl-content" style="width:689px;" >		
	
		<?php
		if ($action=="delete") {
			$campaign=$db->get_row("SELECT * FROM campaigns WHERE id = ".$campaign_id);
			?>
            <p class="instructions">Do you really want to delete the campaign <strong><?=$campaign->title?></strong>. All status associated with this campaign will be permenantly deleted also.</p>
			<form method="post">
            <fieldset>
                <input type="hidden" name="id" value="<?=$campaign_id?>" />
                <input type="hidden" name="action" value="<?=$action?>" />
                <input type="submit"  name="save" class="submit" value="Delete" />        
            </fieldset>
            </form>            
            <?php
		}
		else {
			require_once($_SERVER['DOCUMENT_ROOT'].'/treeline/campaign/forms/createEdit.php'); 
		}
		?> 
        
		
	</div>
	<div class="tl-footer" style="width:735px;" >
		<span class="tl-footer-left"></span>
		<span class="tl-footer-right"></span>
	</div>
</div>
<? }  ?> 
    
</div>
</div>
<?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>