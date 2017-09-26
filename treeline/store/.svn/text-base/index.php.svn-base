<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	// Make sure access is allowed to the store configuration
	if (!$site->getConfig('setup_store')) {
		redirect("/treeline/?msg=store is not configured for this website");
	}

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/events/includes/event.class.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/events/includes/event.functions.php");
	
	$event = new Event();

	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	$eventId = read($_REQUEST,'id',NULL);
	$action = read($_REQUEST,'action',NULL);
	$search = read($_REQUEST,'q',NULL);
	$status = read($_REQUEST,'status','all');
	$dateType = read($_REQUEST,'date','all');
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = 20;
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Events : '.ucwords($action) : 'Events';
	$pageTitle = ($action) ? 'Events : '.ucwords($action) : 'Events';
	
	$pageClass = 'events';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

      <div id="primarycontent">
        <div id="primary_inner">
          <?=drawFeedback($feedback,$message)?>
          <?php
            
            if($eventId){ // Id is present so show individual item
				$result = $event->getById($eventId); // get individual item to view
                if(!$action){ // No action
                    // draw out item
				?>
                <div class="vevent">
                    <h3 class="summary"><?php echo $result->title; ?></h3>
                    <p class="description"><?php echo $result->description; ?></p>
                    <p class="location">Where: <?php echo $result->venue; ?></p>
                    <p>When: <abbr class="dtstart"><?php echo getUFDateTime($result->start_date); ?></abbr> until <abbr class="dtend"><?php echo getUFDateTime($result->end_date); ?></abbr></p>
                </div>
                <hr />
                <p><a href="/treeline/events/">View all events</a></p>
                <?php
                }
                else{
					
                    if($action == 'edit'){ // Edit item
                        // FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/events/includes/ajax/addEditEvent.php');
                    }
                    else if($action =='delete'){ // Delete item
                    	// FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/events/includes/ajax/deleteEvent.php');
                    }
					
					else if($action =='approve'){ // Delete item
                    	// FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/events/includes/ajax/approveEvent.php');
                    }
                }
                
            } 
			else if(!$eventId && $action == 'create'){ // Create item
			  // FORM
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/events/includes/ajax/addEditEvent.php');
            }
            else{ // No id so show all results i.e. BROWSE LISTINGS
			
				// SEARCH FORM
				
				// TABULAR LISTINGS
				$total = $event->getTotal($status, $search, $dateType);
                $results = $event->getAll($orderBy, $status, $search, $dateType, $currentPage, $perPage);
            ?>
            <p><a href="?action=create">Add a new event</a></p>
            <form id="filterForm" action="/treeline/events/" method="post">
            	<fieldset>
                	<legend>Find events</legend>
                	<label for="q">Search for:</label>
                    <input type="text" name="q" id="q" value="<?php echo $search; ?>" /><br />
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                    	<?php echo drawStatusDropDownOptions($status); ?>
                    </select><br />
                    <label for="date">Date:</label>
                    <select name="date" id="date">
						<option value="future"<?php echo ($dateType == 'future') ? ' selected="selected"': ''; ?>>Upcoming</option>
                        <option value="past"<?php echo ($dateType == 'past') ? ' selected="selected"': ''; ?>>Previous</option>
                        <option value="all"<?php echo ($dateType == 'all') ? ' selected="selected"': ''; ?>>All</option>
                    </select><br />
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort">
						<?php echo drawOrderByDropDownOptions($orderBy); ?>
                    </select><br />
                    <fieldset class="buttons">
                    	<button type="submit" class="submit" name="submitFilter">Filter</button>
                    </fieldset>
                </fieldset>
            </form>
            <?php				
				if($results){ // results exists
			?>
      		<table class="treeline">
				<caption><?php echo getShowingXofX($perPage, $currentPage, sizeof($results), $total); ?> Events</caption>
				<thead>
                    <tr>
                        <th scope="col">Preview</th>
                        <th scope="col">Title</th>
                        <th scope="col">Location</th>
                        <th scope="col">Date</th>
                        <th scope="col">Approve</th>
                        <th scope="col">Edit</th>
                        <th scope="col">Delete</th>
                    </tr>
				</thead>
				<tbody>
                <?php
					foreach($results as $result){ // loop through and show results
				?>
                    	<tr>
                        <td class="action preview"><a href="?id=<?php echo $result->event_id; ?>" title="Preview">Preview this event</a></td>
                    	<td><?php echo $result->title; ?></td>
                        <td><?php echo $result->venue; ?></td>
                        <td><?php echo getUFDate($result->start_date); ?></td>
                        <?php if($result->status == 0) { ?>
                        <td class="action approve"><a href="?id=<?php echo $result->event_id; ?>&amp;action=approve" title="Approve">Approve this event</a></td>
                        <?php } else { ?>
                        <td>N/A</td>
                        <?php } ?>
                        <td class="action edit"><a href="?id=<?php echo $result->event_id; ?>&amp;action=edit" title="Edit">Edit this event</a></td>
                        <td class="action delete"><a href="?id=<?php echo $result->event_id; ?>&amp;action=delete" title="Delete">Delete this event</a></td>

                        
                        </tr>
                <?php
					} // end loop
				?>
                </tbody>
                </table>
                <?php
					echo drawPagination($total, $perPage, $currentPage);
				}
				else{ // results
				?>
                <p>There are no events</p>
            <?php
				}
            }
			
            ?>
        </div>
      </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>