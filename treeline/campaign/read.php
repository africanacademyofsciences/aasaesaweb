<? 
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	
	//instatiate campaignstats object 
	$action = read($_REQUEST,'action',false);
	//get campaign id 
	$id = read($_REQUEST,'id',false);
	
	
	//if (!$action) header("Location: /treeline/"); // only for action pages
	$guid = read($_REQUEST,'guid','');
		
	$message = array();
	$feedback = "error";
	
	/*PAGINATION VARIABLES****/
	//totalresults
	$perPage = read($_REQUEST,"rowsPerPage",10);
	$currentPage = read($_GET,'page',1); 
	$url = ($action)? $action : ''; 
	
	/*************************/
	/*SEARCH VARIABLES********/
	//check form has been posted
	$hidden = read($_REQUEST,"hidden",false);
	$searchTitle = read($_REQUEST,"searchTitle",false);//search term 
	$orderBy = read($_REQUEST,"orderBy","date_sent"); // Column to orderby
	$orderDir = read($_REQUEST,"orderDir","DESC"); // Direction to orderby - DESC or ASC
	/***********************/
	
	/***********LOGIC*************/
	
	/******GET DATA***************/
	//get selected campaign 
	if($id){
		$query = "SELECT c.id, c.title, c.newsletter_id,  c.date_sent AS dateSent, 
		c.date_created AS dateCreated,  
		n.subject AS newsletter	
		FROM campaigns c 
		INNER JOIN newsletter n ON n.id=c.newsletter_id
		WHERE c.id=".(int)$id;
		
		$campaigns = $db->get_results($query);
		if (!$campaigns) {
			 $feedback = 'warning'; 
			 $message[] ='Could not find the selected campaign. Please try again.';			
		}
	}
	else {
		/***Build SQL *******/
		//get all campaigns
		$query = "SELECT 
			c.id, c.title, c.newsletter_id, 
			c.date_sent, c.date_created,
			c.mail_count,
			date_format(c.date_sent, '%d %b %Y %H:%i') AS dateSent, 
			date_format(c.date_created, '%d %b %Y %H:%i') AS dateCreated, 
			n.subject AS newsletter	
			FROM campaigns c 
			INNER JOIN newsletter n ON n.id=c.newsletter_id 
			"; 
	
		/******ADD FILTER OPTIONS*********/
		//check if search has been posted
		if($hidden){
			if($searchTitle) $query .= "LIKE '%".$db->escape($searchTitle)."%'";
		}	
		/*********************************/			

		$campaigns = $db->get_results($query);
		if($campaigns) {
			$totalResults = sizeof($campaigns);

			// Add ordering	
			if($orderBy && $orderDir) $query .= " ORDER BY ".$db->escape($orderBy)." " . $db->escape($orderDir) . " ";
			else $query .= " ORDER BY c.title ASC, c.date_created DESC ";
			//limit
			$query .= " LIMIT ".getQueryLimits($perPage,$currentPage);
			
			//print "$query<br>\n";
			$campaigns = $db->get_results($query);
		}	
		else {
			$feedback = 'warning'; 
			$message ='There are no campaigns available. <a href="/treeline/campaign/manage/">Please start a campaign</a>';			
		}

			
	}	
	/****************************/	
	
	
	// PAGE specific HTML settings
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Campaigns : '.ucwords($action) : 'Campaigns';
	$pageTitle = ($action) ? 'Campaigns : '.ucwords($action) : 'Campaigns';
	
	$action="campaignstats";
	$curPage = "campaignstats_home";
	$pageClass = 'campaignstats';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>
<div id="primarycontent">
<div id="primary_inner">
	<?=drawFeedback($feedback,$message);?>
    
	<? 
	if( $totalResults > 0 ){ 
		?> 
        <h2 class="pagetitle rounded">Locate a campaign to manage</h2>
        <div class="tl-box"  style="width:735px;" >

            <div class="tl-head tl-head-blue">
        
                <span class="tl-head-left"></span>
                <h2 class="tl-head-right" style="width:711px;">
                    <span style="width:711px;">Find campaigns</span>
                    
                </h2>
            </div>
        
	        <div class="tl-content" style="width:689px;" >

                <p>Search through the list of existing campaigns to edit, delete, send or retrieve campaign stats.</p>
                <? 
                //include search from 
                require_once('forms/search.php'); 
                ?>
    
            </div>

            <div class="tl-footer" style="width:735px;" >
                <span class="tl-footer-left"></span>
                <span class="tl-footer-right"></span>
            </div>
		</div>

        <table class="tl_list">
            <caption><?=$totalResults?> results found</caption>
            <thead>
                <tr>
                <th scope="col">Title</th>
                <th scope="col">Newsletter</th>
                <th scope="col">Date Created</th>
                <th scope="col">Date Sent</th>
                <th scope="col">Total</th>
                <th scope="col">Manage Campaign</th>
                </tr>
            </thead>
			<tbody>
            	<?php 
				foreach($campaigns as $view) {
					?> 
    	            <tr> 
                    <td><?=$view->title?></td>
                    <td><?=$view->newsletter?></td>
                    <td><?=$view->dateCreated?></td>
                    <td><?=$view->dateSent?></td> 
                    <td align="right"><?=$view->mail_count?></td> 
                    <td class="action">
                        <a <?=$help->drawInfoPopup("Campaign stats")?> href="/treeline/campaign/stats/?id=<?=$view->id?>" class="reuse">Stats</a>
                        <!-- <a <?=$help->drawInfoPopup("Sending options")?> href="/treeline/campaign/send/?id=<?=$view->id?>" class="send">Send</a> -->
                        <a <?=$help->drawInfoPopup("Edit campaign")?> href="/treeline/campaign/manage/?action=edit&id=<?=$view->id?>" class="edit">Edit</a>
                        <a <?=$help->drawInfoPopup("Delete campaign")?> href="/treeline/campaign/manage/?action=delete&id=<?=$view->id?>" class="delete">Delete</a>              
                    </td>
	                </tr>
              		<?php
				}
				?> 
            </tbody>
		</table>

		<? 
		echo drawPagination($totalResults,$perPage,$currentPage,$url);
	} 
    
	
?>

</div>
</div>
<?php 
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>