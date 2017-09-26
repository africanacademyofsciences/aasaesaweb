<?

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/plugin_template/includes/plugin.class.php");
	
	$plugin = new Plugin();

	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	$pluginId = read($_GET,'id',NULL);
	$action = read($_GET,'action',NULL);
	$orderBy = read($_GET,'sort',NULL); // sort query/results
	$currentPage = read($_GET,'page',1); // pagination value
	$perPage = read($_GET,'show',20);
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Section : '.ucwords($action) : 'Section';
	$pageTitle = ($action) ? 'Section : '.ucwords($action) : 'Section';
	
	$pageClass = 'section';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

      <div id="primarycontent">
        <div id="primary_inner">
          <?=drawFeedback($feedback,$message)?>
          <?php
            
            if($pluginId){ // Id is present so show individual item
				$result = $plugin->getById($pluginId); // get individual item to view
                if(!$action){ // No action
                    // draw out item
                }
                else{
                    $pluginDetails = $plugin->getById($pluginId); // get details of
					
                    if($action == 'edit'){ // Edit item
                        // FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/venues/includes/ajax/addEditPlugin.php');
                    }
                    else if($action =='delete'){ // Delete item
                    	// FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/venues/includes/ajax/deletePlugin.php');
                    }
                }
                
            } 
			else if(!$pluginId && $action == 'create'){ // Create item
			  // FORM
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/venues/includes/ajax/addEditPlugin.php');
            }
            else{ // No id so show all results i.e. BROWSE LISTINGS
			
				// SEARCH FORM
				
				// TABULAR LISTINGS
                $results = $plugin->getAll($orderBy, $currentPage, $perPage);
            ?>
            <h2>Plugins</h2>
            <p><a href="?action=create">Add a new plugin</a></p>
            
            <?php				
				if($results){ // results exists
			?>
      		<table class="treeline">
				<caption>Showing x - x of <?php echo $total; ?> Plugin</caption>
				<thead>
                    <tr>
                        <th scope="col">Preview</th>
                        <th scope="col">Title</th>
                        <th scope="col">Edit</th>
                        <th scope="col">Delete</th>
                    </tr>
				</thead>
				<tbody>
                <?php
					foreach($results as $result){ // loop through and show results
				?>
                    	<tr>
                        <td class="action preview"><a href="?id=<?php echo $result->plugin_id; ?>" title="Preview">Preview this plugin</a></td>
                    	<td>title</td>
                        <td class="action edit"><a href="?id=<?php echo $result->plugin_id; ?>&amp;action=edit" title="Edit">Edit this plugin</a></td>
                        <td class="action delete"><a href="?id=<?php echo $result->plugin_id; ?>&amp;action=delete" title="Delete">Delete this plugin</a></td>

                        
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
                <p>There are no plugins</p>
            <?php
				}
            }
			
            ?>
        </div>
      </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>