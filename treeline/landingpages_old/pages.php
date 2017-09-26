<?php

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/landingpages/includes/landingpage.class.php");
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	$id = read($_REQUEST,'id','');
	$action = read($_REQUEST,'action',NULL);
	$orderBy = read($_REQUEST,'sort','date'); // sort query/results
	$search = read($_REQUEST,'search',''); // search through results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = read($_REQUEST,'show',20);
	
	$pluginFolder = '/landingpages/pages/';
	$pluginName = 'landing pages';
	$pluginNamePlural = 'landing pages';
	
	
	$plugin = new LandingPage($currentPage, $perPage, $orderBy, $id, $search);
	$results = $plugin->properties;
	$pages = $plugin->pages;
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
	
		div.page{
			background: #FFF;
			border: 1px solid #EDF1F1;
			float: left;
			margin: 0 20px 20px 0;
			padding: 0 10px 10px;
			width: 200px;
		}
		
		div.page img{
			float: left;
			margin: 0 10px 10px 0;
		}
		
		p.options{
			background: #DAE3E3;
			clear: both;
			margin: 0;
			padding: 2px 5px;
		}
		
		hr{
			clear: both;
		}
	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? ucwords($pluginName).' : '.ucwords($action) : ucwords($pluginName);
	$pageTitle = ($action) ? ucwords($pluginName).' : '.ucwords($action) : ucwords($pluginName);
	
	$pageClass = $pluginName;
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

      <div id="primarycontent">
        <div id="primary_inner">
          <?=drawFeedback($feedback,$message)?>
          <?php
            if($id){ // Id is present so show individual item
				//$result = $landingpage->getById($id);
                if(!$action){ // No action so draw out item
					echo '<h2>'.$results['title'].'</h2>'."\n";
					echo '<p><a href="#">Preview</a></p>'."\n";
					echo '<p>Added: '.getUFDateTime($results['date_added']).'</p>'."\n";
					echo '<h3>Pages</h3>';
					if($pages){
						echo '<div id="pages">'."\n";
						foreach($pages as $item){
							$img = ($item['img_guid']) ? '<img src="http://amref/images/content/panel.gif" alt="" />': '';
							echo '<div class="page">'."\n";
							echo '<h3>'.$item['title'].'</h3>'."\n"; 
								echo ($item['content']) ? '<p>'.$img.$item['content'].'</p>'."\n": '<p>Content goes here</p>'."\n";
								echo '<p class="options"><a href="?action=reorder">reorder</a> or <a href="?action=edit">edit</a> or <a href="?action=delete">remove</a></p>'."\n"; 
							echo '</div>'."\n";
						}
						echo '</div>'."\n";
					}
					echo '<hr />'."\n";
					// Give user (navigation) options
					echo '<p><a href="./">View all '.$pluginNamePlural.'</a> or <a href="?id='.$results['guid'].'&amp;action=edit">Edit</a> or <a href="?id='.$results['guid'].'&amp;action=delete">Delete</a></p>'."\n";
                }
                else{
                    if($action == 'edit'){ // Edit item
                        // FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/'.$pluginFolder.'/includes/ajax/addEdit.php');
                    }
                    else if($action =='delete'){ // Delete item
                    	// FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/'.$pluginFolder.'/includes/ajax/delete.php');
                    }
                }
                
            } 
			else if(!$id && $action == 'create'){ // Create item
			  // FORM
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/'.$pluginFolder.'/includes/ajax/addEdit.php');
            }
            else{ // No id so show all results i.e. BROWSE LISTINGS
			
				// SEARCH FORM
			?>
            <form id="filterForm" method="get" action="">
            	<fieldset>
                	<legend>Filter <?=$pluginNamePlural?></legend>
                	<label for="search">Search:</label>
                    <input type="text" name="search" id="search" value="<?=$search?>" /><br />
                    <label for="section">Section:</label>
                    <select name="section" id="section">
                      <option value="xx">Select:</option>
                      <?=$treeline->drawSelectPagesByParent(1,$parent)?>
                    </select><br />
                    <fieldset class="buttons">
                		<button type="submit" class="button submit">Filter</button>
                    </fieldset>
                </fieldset>
            </form>
            <p id="options"><a href="?action=create">Add a new <?=$pluginName?></a></p>
            <?php				
				if($results){ // results exists
				// TABULAR LISTINGS
			?>
      		<table class="treeline">
				<caption><?=getShowingXofX($perPage, $currentPage, sizeof($results), $plugin->total)?> <?=$pluginNamePlural?></caption>
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
                        <td class="action preview"><a href="?id=<?=$result['guid']?>" title="Preview">Preview this <?=$pluginName?></a></td>
                    	<td><?=$result['title']?></td>
                        <td class="action edit"><a href="?id=<?=$result['guid']?>&amp;action=edit" title="Edit">Edit this <?=$pluginName?></a></td>
                        <td class="action delete"><a href="?id=<?=$result['guid']?>&amp;action=delete" title="Delete">Delete this <?=$pluginName?></a></td>

                        
                        </tr>
                <?php
					} // end loop
				?>
                </tbody>
                </table>
                <?php
					// PAGINATE
					$currentURL = '/treeline/'.$pluginFolder.'/';
					echo drawPagination($total, $perPage, $currentPage, $currentURL);
				}
				else{ // results
				?>
                <p>There are no <?=$pluginNamePlural?></p>
            <?php
				}
            }
            ?>
        </div>
      </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>