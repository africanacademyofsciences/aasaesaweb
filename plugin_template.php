<?php

	// EMAIL NEWSLETTER SUBSCRIPTION
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/nplugin_template/includes/plugin.class.php');
	$plugin = new Plugin();
	
	$pluginId = read($_GET,'id',NULL);
	$action = read($_GET,'action',NULL);
	$pluginType = read($_GET,'type',NULL);
	$orderBy = read($_GET,'sort',NULL); // sort query/results
	$currentPage = read($_GET,'page',1); // pagination value
	$perPage = read($_GET,'show',20);

	// Page specific options
	
	$action = read($_REQUEST,'action','subscribe');
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array(); // all attached stylesheets
	if($page->style != NULL){
		$css[] = $page->style;
	}
	if($action){
		$css = 'forms';
	}
	$extraCSS = ''; // extra page specific CSS
	
	$js = array(); // all atatched JS behaviours
	if($action){
		$js[] = 'jquery';
		$js[] = 'usableForms';
		
	}
	$extraJS = ''; // etxra page specific  JS behaviours
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');	
	
?>
		<div id="primarycontent">
			<?php
            
            if($pluginId){ // Id is present so show individual item
				$result = $plugin->getById($pluginId); // draw individual item to view
                if(!$action){ // No action
                ?>
                <h3><?php echo $result->title; ?></h3>
                <?php
                }
                else{
                    else if($action == 'edit'){ // edit member
                        // FORM
                    }
                    else if($action =='delete'){ // delete item
                    	// FORM
                    }
                }
                
            } 
            else{ // No id so show all results BROWSE LISTINGS
				$total = $plugin->getTotal();
                $results = $plugin->getAll($orderBy, $currentPage, $perPage);
				
				if($results){
				?>
				<ul>
				<?php
					foreach($results as $result){
				?>
                <li><?php echo $rsult->title; ?></li>
                <?php
					}
				?>
                </ul>
                <?php
				}
				else{ // No results
				?>
                <p>No results</p>
                <?php
            }
			
            ?>
		</div>
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>