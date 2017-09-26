<?php

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/landingpages/includes/landingpage.class.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/landingpages/includes/functions.php");
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	$id = read($_REQUEST,'id','');
	$page_id = read($_REQUEST,'page_id','');
	$action = read($_REQUEST,'action',NULL);
	$orderBy = read($_REQUEST,'sort','date'); // sort query/results
	$search = read($_REQUEST,'search',''); // search through results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = read($_REQUEST,'show',20);
	
	$pluginFolder = '/landingpages/';
	$pluginName = 'landing page';
	$pluginNamePlural = 'landing pages';
	
	
	$plugin = new LandingPage($currentPage, $perPage, $orderBy, $id, $page_id, $search);
	$results = $plugin->properties;	
	$pages = $plugin->pages;

	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
		
		div#preview img{
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
		
		form#sortorderform, form#sortorderform fieldset{
			background: transparent;
			border: none;
			margin: 0;
			padding: 0;
		}
		
		form#sortorderform input{
			background: #FFF;
			border: 1px solid #999;
			padding: 0 2px;
			width: 1em;
		}
		
		form#sortorderform button.update{
			clear:none;
			display: inline;
			float: none;
			font-weight: normal;
			margin: 0;
			width: auto;
		}
		
		tr.show td{
			background: #F7FBFB;
		}
		
		.mceEditor select{
			float: none;
			width: 100px;
		}

	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = '
	/*$(document).ready(function() { 
		$("#editForm").submit(function(){
		
	
		var content = $("#content").val();
		var style = $("#style").val();
		var sort_order = $("#sort_order").val();
		var page_id = $("#page_id").val();
		var action = $("#action").val();
		
		var data = "content="+content;
		data += "&style="+style;
		data += "&sort_order="+sort_order;
		data += "&action="+action;
		data += "&page_id="+page_id;
		//alert(data);
		
			var html = $.ajax({
				  url: "includes/ajax/addEdit_page.php",
				  data: data,
				  async: false
				 }).responseText;
				 alert(html);
			
			
			$("div#primary_inner").load(html);
			//return false;
		});
	});*/
	'; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? ucwords($pluginNamePlural).' : '.ucwords($action) : ucwords($pluginNamePlural);
	$pageTitle = ($action) ? ucwords($pluginNamePlural).' : '.ucwords($action) : ucwords($pluginNamePlural);
	
	$pageClass = $pluginName;
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	

?>

      <div id="primarycontent">
        <div id="primary_inner">
          <?=drawFeedback($feedback,$message)?>
          <?php
            if($id && !$page_id){ // Id is present so show individual item
				//$result = $landingpage->getById($id);
                if(!$action){ // No action so draw out item
					echo '<div id="preview">'."\n";
					echo '<h2>'.$results['title'].' landing page</h2>'."\n";
					echo '<p><strong>Content:</strong></p>'."\n";
					echo $results['content']."\n";
					echo '</div>'."\n";
					echo '<hr />'."\n";
					echo '<h3>Details</h3>'."\n";
					echo '<p><strong>Style:</strong> Landing page style '.$results['style'].'<br /><img src="/treeline/img/landingpanels/page'.$results['style'].'.gif" alt="Style '.$results['style'].'" title="Style '.$results['style'].'" /></p>'."\n";
					echo ($results['donate'] == 1) ? '<p><strong>Has a donate button</strong></p>'."\n": '';
					echo '<p><strong>Added:</strong> '.getUFDateTime($results['date_added']).'</p>'."\n";
					//echo '<p><a href="/'.$results['name'].'/?mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width=920" class="thickbox">Preview this landing page</a></p>'."\n";
					if($pages){ 
					?>
                    <hr />
                    <h3>Step 3: Choose which panels should be on the landing page</h3>
                    <p><a href="includes/ajax/help.php?helpsection=create&amp;mode=preview&amp;KeepThis=true&amp;TB_iframe=true&amp;height=400&amp;width=720" class="thickbox">Step 3? What were steps 1 &amp; 2?</a></p>
                    <?php
						if($_POST['sort_order']){							
							$plugin->updateSortOrders();
						}
					?>
                    <form method="post" action="<?=$_SERVER['REQUEST_URI']?>" id="sortorderform">
         			<fieldset>
          			<table class="treeline">
						<thead>
                        	<tr>
                            	<th scope="col">Review</th>
                            	<th scope="col">Title</th>
                                <th scope="col">Sort order</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Add/Remove</th>
                            </tr>
                        </thead>
                    	<tbody>
                    <?php foreach($pages as $item){ ?>
                    		 <?php if(!$item['sort_order']) { ?>
                        	<tr>
                           
                            	<td>N/A</td>
                            	<td><?=$item['title']?></td>
                                <td>N/A</td>
                                <td>N/A</td>
								
                                <td><a href="?page_id=<?=$item['original_guid']?>&amp;id=<?=$id?>&amp;action=create" title="Create">Add to landing page</a></td>
                            </tr>
        						<?php } else { ?>
                            <tr class="show">
                                <td class="action preview"><a href="?page_id=<?=$item['page_guid']?>" title="Preview">Preview</a></td>
        						<td><?=$item['title']?></td>
                                <td><input type="text" name="sort_order['<?=$item['page_guid']?>']" id="sort_order_<?=$item['page_guid']?>" value="<?=$item['sort_order']?>" /> <button type="submit" class="update">Update</button></td>
                                <td class="action edit"><a href="?page_id=<?=$item['page_guid']?>&amp;action=edit" title="Edit">Edit</a></td>
                                <td><a href="?page_id=<?=$item['page_guid']?>&amp;action=delete" title="Delete">Remove from landing page</a></td>
                                </tr>
        <?php } ?>
                            
                    <?php } ?>
                        </tbody>
                    </table>
                    </fieldset>
                    </form>
                    <?php
					}
					else{ // no pages
						echo '<p>This landing page has no panels.</p>'."\n";
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
					else if($action == 'create'){ // Create item
					  // QUICKLY ADD NEW LANDING PAGE
					  $_POST['guid'] = $id;
					  $_POST['style'] = 1;
					  $plugin->add();
					}
                }
                
            }
			else if($page_id){
				if(!$action){ // No action so draw out item
					$content = ($results['content']) ? $results['content'] : $results['meta_description'];
					$content = ($content) ? $content : 'This panel has no custom content and no meta description. I suggest you add some custom content by clicking edit below.';
					echo '<div id="preview">'."\n";
					echo '<h2>'.$results['title'].' panel</h2>'."\n";
					echo '<p><strong>Content:</strong> <br />'.nl2br($content).'</p>'."\n";
					echo '</div>'."\n";
					echo '<hr />'."\n";
					echo '<h3>Details</h3>'."\n";
					echo '<p><strong>Style:</strong> Landing panel style '.$results['style'].'<br /><img src="/treeline/img/landingpanels/panel'.$results['style'].'.gif" alt="Style '.$results['style'].'" title="Style '.$results['style'].'" /></p>'."\n";
					echo '<p><strong>Order on landing page:</strong> '.$results['sort_order'].'</p>'."\n";
					echo '<hr />'."\n";
					// Give user (navigation) options
					echo '<p><a href="./?id='.$results['guid'].'">Return to '.$pluginName.'</a> or <a href="?page_id='.$results['page_guid'].'&amp;action=edit">Edit</a> or <a href="?page_id='.$results['page_guid'].'&amp;action=delete">Delete</a></p>'."\n";
				}
				else{
                    if($action == 'edit'){ // Edit item
                        // FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/'.$pluginFolder.'/includes/ajax/addEdit_page.php');
                    }
                    else if($action =='delete'){ // Delete item
                    	// FORM
						include($_SERVER['DOCUMENT_ROOT'].'/treeline/'.$pluginFolder.'/includes/ajax/delete_page.php');
                    }
					else if($action == 'create'){ // Create item
					  // QUICKLY ADD NEW LANDING PAGE
					  $_POST['guid'] = $id;
					  $_POST['page_guid'] = $page_id;
					  $_POST['style'] = 1;
					  $plugin->addPage();
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
            <!--<form id="filterForm" method="get" action="">
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
                        <?php if($search) { ?>
                        <a href="./" class="cancel button">Show all</a>
                        <?php } ?>
                </fieldset>
                </fieldset>
            </form>-->
            <!--<p id="options"><a href="?action=create">Add a new <?=$pluginName?></a></p>-->
          	<?php				
				if($results){ // results exists
				// TABULAR LISTINGS
				$_GET['helpsection'] = 'create';
				include($_SERVER['DOCUMENT_ROOT'].'/treeline/landingpages/includes/ajax/help.php');
			?>
            <hr />
            <h3>Step 1: Choose a section</h3>
            <p>The following sections either are or could be landing pages.</p>
			<table class="treeline">
				<caption><?//=getShowingXofX($perPage, $currentPage, sizeof($results), $plugin->total)?></caption>
				<thead>
                    <tr>
                        <th scope="col">Review</th>
                        <th scope="col">Title</th>
                        <th scope="col">Edit</th>
                        <th scope="col">Create/Delete</th>
                    </tr>
				</thead>
				<tbody>
                <?php
					foreach($results as $result){ // loop through and show results
						if($result['children']){ // only show sections that have children
				?>
                    	<tr>
                        
                        <?php if(!$result['landingpage']) { ?>
                        <td>N/A</td>
                    	<td><?=$result['title']?></td>
                        <td>N/A</td>
                        <td><a href="?id=<?=$result['guid']?>&amp;action=create" title="Create">Make this a landing page</a></td>
						<?php } else { ?>
                        <td class="action preview"><a href="?id=<?=$result['guid']?>" title="Preview">Preview this <?=$pluginName?></a></td>
                    	<td><?=$result['title']?></td>
                        <td class="action edit"><a href="?id=<?=$result['guid']?>&amp;action=edit" title="Edit">Edit</a></td>
						<td><a href="?id=<?=$result['guid']?>&amp;action=delete" title="Delete">Remove this landing page</a></td>
						<?php } ?>
                        
                        </tr>
                <?php
						}
					} // end loop
				?>
                </tbody>
                </table>
                <?php
					// PAGINATE
					$currentURL = '/treeline/'.$pluginFolder.'/';
					echo drawPagination($plugin->total, $perPage, $currentPage, $currentURL);
				}
				else{ // NO RESULTS
				?>
                <p>There are no <?=$pluginNamePlural?></p>
            <?php
				}
            }
            ?>
        </div>
      </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>