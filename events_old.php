<?php

	// EVENTS
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/login.class.php');
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/member.class.php');
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/members/includes/member.functions.php');
	$memberLogin = new memberLogin();
	$member = new Member();
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/events/includes/event.class.php');
	$event = new Event();
	
	$eventId = read($_GET,'id',$location[$offset+1]);

	$result = ($eventId) ? $result = $event->getById($eventId) : NULL; // draw individual item to view
	
	$action = read($_REQUEST,'action',NULL);
	$search = read($_REQUEST,'q',NULL);
	$orderBy = read($_REQUEST,'sort','chronologically'); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = read($_REQUEST,'show',20);

	// Page specific options
	
	$pageClass = 'events'; // used for CSS usually
	
	$css = array('tables'); // all attached stylesheets
	if($page->style != NULL){
		$css[] = $page->style;
	}
	if($action){
		$css[] = 'forms';
	}
	$extraCSS = ''; // extra page specific CSS
	
	$js = array(); // all atatched JS behaviours
	if($action){
		$js[] = 'jquery';
		$js[] = 'usableForms';
		
	}
	$extraJS = ''; // etxra page specific  JS behaviours
	
	// Page title
	
	$pageTitle = 'Events';
	
	$pageTitle .= ($action) ? ' &gt; '.ucwords($action) : '';
	$pageTitle .= ($eventId) ? ' &gt; '. $result->title .' at '.$result->venue : '';
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	
?>
		<div id="primarycontent">
			<?php
            
            if($eventId){ // Id is present so show individual item

                if(!$action){ // No action
				
				$editMeLink = ($memberLogin->getId() == $result->member_id) ? '<p><a href="/events/'.$result->event_id.'/?action=edit">Edit this event</a</p>' : '';
                ?>
                <div class="vevent">
                <h3 class="summary"><?php echo $result->title; ?></h3>
                <?php echo $editMeLink; ?>
                <p class="description"><?php echo $result->description; ?></p>
                <p class="location">Where: <?php echo $result->venue; ?></p>
				<p>When: <abbr class="dtstart"><?php echo getUFDateTime($result->start_date); ?></abbr> until <abbr class="dtend"><?php echo getUFDateTime($result->end_date); ?></abbr></p>
                </div>
                <hr />
                <p><a href="<?= ($siteID==1 ? '' : '/'.$site->properties['site_name']) ?>/events/">View all events</a></p>
                <?php
                }
                else{
                    if($action == 'edit'){ // edit member
                        // FORM
						include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/addEditEvent.php');
                    }
                    else if($action =='delete'){ // delete item
                    	// FORM
                    }
                }
                
            } 
			else if(!$eventId && $action == 'add'){
				// FORM
				include($_SERVER['DOCUMENT_ROOT'].'/includes/ajax/addEditEvent.php');
			}
            else{ // No id so show all results BROWSE LISTINGS
				$total = $event->getTotal('approved', $search, 'future');
                $results = $event->getAll($orderBy, 'approved', $search, 'future', $currentPage, $perPage);
			?>
            <!--<p><a href="?action=add">Add an event</a></p>-->
      <?php		
				if($results){
					$i = 1;
		?>
				<table>
                <thead>
                	<tr>
                    	<th scope="col">Event</th>
                        <th scope="col">Venue</th>
                        <th scope="col">When</th>
                    </tr>
                </thead>
                <tbody>
				<?php
					foreach($results as $result){
				?>
                <tr class="vevent">
                <td><a href="<?= ($siteID==1 ? '' : '/'.$site->properties['site_name']) ?>/events/<?php echo $result->event_id; ?>/" class="summary"><?php echo $result->title; ?></a></td>
                <td class="location"><?php echo $result->venue; ?></td>
                <td class="dtstart"><?php echo getUFDate($result->start_date); ?></td>
                </tr>
                <?php
					}
				?>
                </tbody>
                </table>
                <?php
				}
				else{ // No results
				?>
                <p>There are no events.</p>
                <?php
            }
		}	
            ?>
		</div>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>