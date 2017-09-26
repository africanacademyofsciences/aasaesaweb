<?php

	ini_set("display_errors", "yes");
	//error_reporting(E_ALL);

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "/treeline/newsletters/includes/mailchimp.class.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/member.class.php");

	include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/pledge.class.php');

	if (!$site->getConfig('setup_members_area')) redirect("/treeline/");
	$member = new Member();

	$guid = read($_REQUEST,'guid','');
		
	$message = array();
	$feedback = read($_REQUEST,'feedback','notice');
	
	$member_id = $memberId = read($_REQUEST,'id',NULL);
	$action = read($_REQUEST,'action',NULL);
	$search = read($_REQUEST,'q',NULL);
	$status = read($_REQUEST,'status','');
	
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = read($_REQUEST,'show', 10);
	
	$nextsteps='';

	//print "got memberID($memberId) action($action) member_id($member_id)<br>\n";
	
	if ($_SERVER['REQUEST_METHOD']=="POST") {
		
		// Add new member
		if($action == 'create' || $action == 'add'){
			if ($member->add('admin')) {
				$message[]=$page->drawLabel("tl_mem_err_created", "New member has been created");
				$feedback="success";
				$action="edit";
				$notify_msg="Your membership has been created";	// For the notify email?
				$member_id = $member->new_member_id;
			}
			else {
				$message = $member->errmsg;
			}
		}
		
		// Edit member
		else if($action == 'edit'){
			if ($member->edit($member_id, 'admin')) {
				$message[]=$page->drawLabel("tl_mem_err_updated", "Member details updated");
				$feedback="success";
				$action="";
				$notify_msg="Your membership data has been updated";
			}
			else $message = $member->errmsg;
		}

		else if ($action == "delete") {
			$member->delete($memberId);
			//$notify_msg="Your membership has been suspended";
		}
		
	
	}
	else {
	
		//print "Get action($action) for member($member_id)<br>\n";
		if ($action=="activate" && $member_id > 0) {
			$query = "UPDATE member_access SET `status`='A' WHERE member_id = $member_id AND msv = ".($site->id+0)." AND `status`='N'";
			//print "$query<br>\n";
			if ($db->query($query)) {
				$message[] = "Member activated";
				$notify_msg = "Your funder account has been activated";
			}
			else $message[] = "Failed to activate member";
			$action = "";
		}
			
	}

	// Send a message to the (new) member with their details.
	if ($notify_msg) {
		//print "Got a notify message ($member_id)<br>\n";
		include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/includes/subscriber.class.php");
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
		include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
		include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');
		$result=$member->getById($member_id);
		if ($result->email) {
			$sendParams = array("EMAIL"=>$result->email,
				"NOTIFY_MSG"=>$notify_msg, 
				"MEMBER_DATA"=>"Name: ".$result->firstname." ".$result->surname."<br />
Password: ".$result->password."<br />
".($result->bloggable?"Blogging ok: yes":"")."<br />
"
			);
			//print_r($sendParams);
			$newsletter=new Newsletter();
			$newsletter->sendText($result->email, "MEMBER-DATA", $sendParams);
		}
		$member_id=0;
	}
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array('showHideAddress'); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	//$pageTitleH2 = ($action) ? 'Members: '.ucwords($action) : 'Members';
	//$pageTitle = ($action) ? 'Members: '.ucwords($action) : 'Members';
	$pageTitleH2 = $pageTitle = $page->drawPageTitle("members", $action);
	$pageClass = 'members';
	
	// Get list of subscribed events
	if ($member_id>0) {
		$query = "select e.guid, p.title, 
			ee.member_id, ee.sponsorship 
			from events e 
			left join event_entry ee on e.guid=ee.event_guid 
			left join pages p on e.guid=p.guid
			where p.date_published is not null and ee.member_id=".$member_id;
		//print "$query<br>";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				$event_html.='<li>'.$result->title.'</li>';
				//$event_html.='<li>'.$result->title.' sponsorship £'.number_format($result->sponsorship,2).' <a href="/treeline/events/?m_id='.$result->member_id.'&action=sponsor&fund-guid='.$result->guid.'">add sponsorship</a></li>';
			}
		}
	}
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
<div id="primary_inner">
<?php 
	///print "got message($message) r(".print_r($message, true).")<br>\n";
	echo drawFeedback($feedback, $message);
	if ($nextsteps) echo treelineList($nextsteps, $page->drawGeneric("next_steps", 1), "blue");

	// *************************************************************
	//print "got member id($member_id)<br>\n";
	if($member_id) { 

		if ($result = $member->loadByID($member_id)) {

			// --------------------------------------------------------------------
			// Default = Show member details.
			if(!$action){ 
				$_SESSION['member_id'] = $member_id;
				$_SESSION['member_site_id'] = $site->id;
				
				if ($result->country>0) $countryname = $db->get_var("SELECT title FROM store_countries WHERE country_id = ".$result->country);
				else $countryname = $result->country;
				
				//print "mem data(".print_r($result, 1).") <br>\n";
				?>
				<!-- MEMBER DETAILS -->
				<div class="vcard">
                    <h3 class="fn"><?=$result->firstname?> <?=$result->surname?></h3>
                    <p class="tel">Type: <?=$result->type_name?></p>
                    <!--
                    <p class="addr">
                        <span class="street-address"><?=$result->address1?></span><br />
                        <?php if($result->address2) { ?>
                        <span class="locality"><?=$result->address2?></span><br />
                        <?php } ?>
                        <?php if($result->address3) { ?>
                        <span class="region"><?=$result->address3?></span><br />
                        <?php } ?>
                        <?php if($result->postal_code) { ?>
                        <span class="postal-code"><?=$result->postal_code?></span>
                        <?php } ?>
                    </p>
                    -->
                    
                    <!--
                    <p class="tel"><?=$page->drawGeneric("password", 1)?>: <span class="value"><?=$result->password?></span></p>
                    -->
                    <?php
					if ($result->telephone) {
						?>
	                    <p class="tel"><?=$page->drawGeneric("telephone", 1)?>: <span class="value"><?=$result->telephone?></span></p>
                        <?php
					}
					?>
                    
                    <p><?=$page->drawGeneric("email", 1)?>: <a href="mailto:<?=$result->email?>" class="email"><?=$result->email?></a></p>
                    
                    <?php
					if ($result->jobtitle) {
						?>
                    	<p class="work">Jobtile: <span class="value"><?=$result->jobtitle?></span></p>
                    	<?php
					}
					if ($result->organisation) {
						?>
	                    <p class="work">Organisation: <span class="value"><?=$result->organisation?></span></p>
                        <?php
					}
					if ($countryname) {
						?>
						<p class="country">Country: <span class="value"><?=$countryname?></span></p>
						<?php
					}
					if ($result->profile) {
						?>
                        <p class="tel">Profile: <?=$result->profile?></p>
                        <?php
					}
					if ($result->further_info) {
						?>
    	                <p><?=($result->further_info?nl2br($result->further_info):'<em>'.$page->drawLabel("tl_mem_view_noinfo", "This member has no additional information about themselves").'</em>')?></p>
                        <?php
					}
					?>					

				</div>
			  
				<hr />

				<!-- THINGS TO DO WITH THIS MEMBER -->
                <h3 class="fn"><?=$page->drawLabel("tl_mem_view_options", "Options for this member")?></h3>
                <ul class="menu">
                    <!-- <li><a href="/treeline/events/?m_id=<?=$result->member_id?>&amp;action=book"><?=$page->drawLabel("tl_mem_view_obook", "Book this member on an event")?></a></li> -->
                    <li><a href="/treeline/members/?id=<?=$result->member_id?>&amp;action=edit"><?=$page->drawLabel("tl_mem_view_oedit", "Edit member data")?></a></li>
                    
                    <?php
					if ($result->type_name == "Researcher") {
						?>
		                <li><a href="<?=$site->link?>member-login/?action=profile">Edit member profile</a></li>
        	            <li><a href="/treeline/pages?action=profile">Create a new project</a></li>
                       	<?php
					}
					else if ($result->type_name=="Funder") {
						if ($result->status == 'N') {
							?>
			                <li><a href="/treeline/members/?id=<?=$result->member_id?>&amp;action=activate">Activate this account</a></li>
                            <?php
						}
						?>
                        
                        <?php
					}
					?>
                    <li><a href="/treeline/newsletters/subsedit/?id=<?=$result->member_id?>&amp;action=edit"><?=$page->drawLabel("tl_mem_view_osubs", "Manage subscriptions")?></a></li>
                    <!-- <li>Option 2</li> -->
                </ul>
				<hr />

				<?php
				if ($result->type_name=="Researcher") {
                	?>
                    <h3 class="fn">Projects</h3>
                    <p>List all project and pledges for this member</p>
                    <?php
					$query = "SELECT guid, DATE_FORMAT(date_created, '%D %M %Y') AS `date`, title 
						FROM pages WHERE member_id = ".$member_id." ORDER BY date_created DESC";
					//print "$query<br>\n";
					if ($results = $db->get_results($query)) {
						foreach ($results as $result) {
							?>
                            <h5><a href="<?=$page->drawLinkByGUID($result->guid)?>?mode=preview"><?=$result->title?></a></h5>
                            <?php
							$pledge = new Pledge($result->guid);
							$query = "SELECT pl.guid, DATE_FORMAT(pl.added, '%D %M %Y') AS `date`, pl.amount, 
								p.title AS project, CONCAT(m.firstname, ' ', m.surname) AS funder, m.organisation,
								pt.title AS pledge_type
								FROM pledge pl 
								INNER JOIN pages p ON p.guid = pl.guid 
								INNER JOIN members m on m.member_id = pl.funder_id
								INNER JOIN pledge_type pt ON pt.id=pl.type_id
								WHERE pl.guid = '".$result->guid."'
								ORDER BY pl.added DESC";
							//print "$query<br>\n";
							$total = 0;
							if ($results2 = $db->get_results($query)) {
								?>
								<table class="tl_list">
								<tr><th>Date</th><th>Organisation</th><th>Amount</th></tr>
								<?php
								foreach ($results2 as $result2) {
									//print "Got a pledge(".print_r($result, 1).")<br>\n";
									$total += $result2->amount;
									?>
                                    <tr>
                                    <td><?=$result2->date?></td>
                                    <td><?=($result2->organisation?$result2->organisation:$result2->funder)?></td>
                                    <?php
                                    if ($result2->amount>0) {
										?>
	                                    <td align="right"><?=$pledge->currency?><?=number_format($result2->amount, 2, ".", "")?></td>
										<?php
                                    }
                                    else {
                                        ?>
                                        <td><?=$result2->pledge_type?></td>
                                        <?php
                                    }
                                    ?>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                <td colspan="2"><strong>Total pledges</strong></td>
                                <td align="right"><strong><?=$pledge->currency?><?=number_format($total, 2, ".", "")?></strong></td>
                                </tr>
                                </table>
                                <?php
							}
							else {
								?>
								<p>No pledges made to this project yet</p>
								<?php
							}
						}
					}
					?>
                    <hr />
            	    <?php
				}
				
				else if ($result->type_name=="Funder") {
					$pledge = new Pledge();
    	            ?>
                    <h3 class="fn">Pledges made</h3>
                    <?php
				 	$query = "SELECT pl.guid, DATE_FORMAT(pl.added, '%D %M %Y') AS `date`, pl.amount, 
						p.title AS project,
						pt.title as pledge_type
						FROM pledge pl 
						INNER JOIN pages p ON p.guid = pl.guid 
						INNER JOIN pledge_type pt ON pt.id = pl.type_id
						WHERE pl.funder_id = ".$member_id." 
						ORDER BY added DESC";
					//print "$query<br>\n";
					if ($results = $db->get_results($query)) {
						?>
                        <table class="tl_list">
                        <tr><th>Date</th><th>Project</th><th>Amount</th></tr>
                        <?php
						foreach ($results as $result) {
							//print "Got a pledge(".print_r($result, 1).")<br>\n";
							$total += $result->amount;
							?>
                            <tr>
                            <td><?=$result->date?></td>
							<td><a href="<?=$page->drawLinkByGUID($result->guid)?>?mode=preview" target="_blank"><?=$result->project?></a></td>
                            <?php
							if ($result->amount>0) {
								?>
                                <td align="right"><?=$pledge->currency?><?=number_format($result->amount, 2, ".", "")?></td>
                                <?php
							}
							else {
								?>
                                <td><?=$result->pledge_type?></td>
                                <?php
							}
							?>
                            </tr>
                            <?php
						}
						?>
                        <tr>
                        <td colspan="2"><strong>Total pledges</strong></td>
                        <td align="right"><strong><?=$pledge->currency?><?=number_format($total, 2, ".", "")?></strong></td>
                        </tr>
                        </table>
                        <?php
					}
					else {
						?>
                        <p>No pledges made by this funder yet</p>
                        <?php
					}
					?>
                    <hr />
        	        <?php
				}

				if ($event_html) {
					?>
						<!-- EVENTS HISTORY -->
						<h3 class="fn"><?=$page->drawLabel("tl_mem_view_events", "Events taken part in")?></h3>
						<ul class="menu">
							<?=$event_html?>
						</ul>
						<hr />
					<?php
				}

                if ($photo = $member->getMemberImage($result->access_id)) {
                    ?>
                    <p><img src="<?=$photo?>" alt="" /></p>
                    <?php
                }
                ?>
                

				<p><a href="/treeline/members/"><?=$page->drawLabel("tl_mem_view_all", "View all members")?></a></p>
				
				<?php
			}
			// ----------------------------------------------------------------------
			// Edit a member info
			else if($action == 'edit') {
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/ajax/addEditMember.php');
			}
			else if($action =='delete') {
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/ajax/deleteMember.php');
			}
			// I don't think we ever do this
			else if($action =='approve') {
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/ajax/approveMember.php');
			}
			// I don't think we ever do this either.
			else if($action =='image') {
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/ajax/uploadImage.php');
			}
		}
		// Member not found
		else { 
			?>
			<p>That member does not exist.</p>
			<p><a href="/treeline/members/">View all members</a></p>
			<?php
		}
            
	}

	// *************************************************************
	// Create a new member
	else if($action == 'create'){
		include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/ajax/addEditMember.php');
	}
	// Download members listed
	else if($action == 'download'){
		$results = $member->getAllForCSV($orderBy, $search, $status);
		include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/ajax/membersCSV.inc.php');
	}
	// Not a clue what this is about
	else if($action == 'labels'){
		$results = $member->getAll($orderBy, $search, $status, $currentPage);
		include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/membersLabels.inc.php');
	}

	// ----------------------------------------------------------------------
	// Default option, no id and no action
	// No ID and nothing to do so default action is to show all members
	else{ 
		$page_html='
		<p><a href="?action=create">'.$page->drawLabel("tl_mem_add_legend", "Add a new member").'</a></p>
		<form id="filterForm" action="/treeline/members/" method="post">
		<fieldset>
			<label for="q">'.$page->drawGeneric("search_for", 1).':</label>
			<input type="text" name="q" id="q" value="'.$search.'" /><br />
			
			<label for="sort">'.$page->drawGeneric("sort_by", 1).':</label>
			<select name="sort" id="sort">
			'.drawOrderByDropDownOptions($orderBy).'
			</select><br />
			
			<label for = "f_showall">Show all members</label>
			<input type="checkbox" name="showall" id="f_showall" value="1" '.($_POST['showall']==1?'checked="checked"':"").' /><br />
			
			<fieldset class="buttons">
				<input type="submit" class="submit" name="submitFilter" value="'.$page->drawGeneric("filter", 1).'" />
			</fieldset>
		</fieldset>
		</form>
		';
		echo treelineBox($page_html, $page->drawLabel("tl_mem_find_title", "Find members"), "blue");
		
		
		if ($_POST['showall']) $member->showallmembers = true;
		
		$total = $member->getTotal($search, $status);
		$results = $member->getAll($orderBy, $search, $status, $currentPage, $perPage);
		if($results){ // results exists
			?>
			<p><a href="?action=download&amp;q=<?=$search?>&amp;sort=<?=$orderBy?>&amp;refresh=1"><?=$page->drawLabel("tl_mem_find_download", "Download these members")?></a></p>
			<table class="tl_list">
			<caption><?php echo getShowingXofX($perPage, $currentPage, sizeof($results), $total); ?> <?=$page->drawGeneric("members", 1)?></caption>
			<thead>
				<tr>
					<th scope="col"><?=$page->drawGeneric("fullname", 1)?></th>
					<th scope="col"><?=$page->drawGeneric("email", 1)?></th>
                    <th scope="col">Type</th>
                    <th scope="col">Status</th>
					<th scope="col"><?=$page->drawLabel("tl_mem_find_manage", "Manage member")?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach($results as $result){ // loop through and show results
				$mem_name = $result->firstname." ".$result->surname;
				$mstatus = $result->status == 'A'?"Active":($result->status=='N'?"New":"Unknown");
				//print "Got member(".print_r($result, 1).")\n";
				if (!$mem_name) $mem_name=$page->drawLabel("tl_mem_find_noname", "No name"); 
				
				?>
				<tr>
				<td><?=$mem_name?></td>
				<td><?=$result->email?></td>
                <td><?=$result->member_type?></td>
                <td><?=$mstatus?></td>
				<td class="action">
					<a class="preview" <?=$help->drawInfoPopup($page->drawLabel("tl_mem_help_show", "Show member data"))?> href="?id=<?=$result->member_id; ?>">Preview this member</a>
					<a class="edit" <?=$help->drawInfoPopup($page->drawLabel("tl_mem_help_edit", "Edit member details"))?> href="?id=<?=$result->member_id?>&amp;action=edit">Edit this member</a>
					<a class="participants" <?=$help->drawInfoPopup($page->drawLabel("tl_mem_help_subs", "Manage subscriptions"))?> href="/treeline/newsletters/subsedit/?id=<?=$result->member_id?>&amp;action=edit">Manage subscriptions</a>
					<a class="delete" <?=$help->drawInfoPopup($page->drawLabel("tl_mem_help_delete", "Delete this member"))?> href="?id=<?=$result->member_id?>&amp;action=delete">Delete this member</a>
				</td>
				</tr>
			<?php
			} 
			?>
			</tbody>
			</table>
			<?php
			//echo drawNewPagination($total, $perPage, $currentPage, "/treeline/members/?q=$search&amp;sort=$orderBy");
			echo drawPagination($total, $perPage, $currentPage, "/treeline/members/?q=$search&amp;sort=$orderBy");
		}
		else{ // results
			?><p><?=$page->drawLabel("tl_mem_err_nomem", "No members were found")?></p><?php
		}
	}
	?>
    </div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 


function drawOrderByDropDownOptions($current = 'surname_az', $type = 'members'){
	global $page;
	if($type == 'members'){
		$options = array(
		'surname_az' => $page->drawLabel("tl_mem_opt_suraz", 'Surname (A-Z)'),
		'surname_za' => $page->drawLabel("tl_mem_opt_surza", 'Surname (Z-A)'),
		'firstname_az' => $page->drawLabel("tl_mem_opt_firstaz", 'First name (A-Z)'),
		'firstname_za' => $page->drawLabel("tl_mem_opt_firstza", 'First name (Z-A)'),
		'email_az' => $page->drawLabel("tl_mem_opt_emailaz", 'Email (A-Z)'),
		'email_za' => $page->drawLabel("tl_mem_opt_emailza", 'Email (Z-A)'),
		);
	}
	else{
		$options = array(
		'title_az' => 'Name (A-Z)',
		'title_za' => 'Name (Z-A)',
		'newest' => 'Date added (newest)',
		'oldest' => 'Date added (oldest)'
		);
	}
	
	$html = '';
	
	foreach($options as $value => $text){
		unset($preSelected);
		
		if($current == $value){
			$preSelected = ' selected="selected"';
		}
		$html .= '<option value="'.$value.'"'.$preSelected.'>'.$text.'</option>'."\n";
	}
	
	return $html;
}

function drawStatusDropDownOptions($current = 'all'){
	//
	return "";
	
	$options = array(
	'all' => 'All',
	'approved' => 'Approved members',
	'unapproved' => 'Unapproved members'
	);
	
	$html = '';
	
	foreach($options as $value => $text){
		unset($preSelected);
		
		if($current == $value){
			$preSelected = ' selected="selected"';
		}
		$html .= '<option value="'.$value.'"'.$preSelected.'>'.$text.'</option>'."\n";
	}
	
	return $html;
}

function drawPreferencesCheckboxes($options, $current = NULL){
	//
	global $db;
	$html = '';
	
	if($current){
		foreach($current as $pref => $value){
			$currentArray[] = $value;
		}
	}
	
	
	foreach($options as $option){
		unset($preSelected);
		
		if(is_array($currentArray) && in_array($option->preference_id, $currentArray)){
			$preSelected = ' checked="checked"';
		}
		$html .= '<input type="checkbox" class="checkbox" name="preference[]" id="preference_'.$option->preference_id.'" value="'.$option->preference_id.'"'.$preSelected.' />'."\n".'
		<label for="preference_'.$option->preference_id.'" class="checklabel" >'.$option->preference_title.'</label><br />'."\n";
	}
	
	return $html;
}

?>
	
    