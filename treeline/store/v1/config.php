<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	// Make sure access is allowed to the store configuration
	if (!$site->getConfig('setup_store')) {
		redirect("/treeline/?msg=store is not configured for this website");
	}

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/store/".$storeVersion."/includes/store.class.php");
	$store = new Store();

	$guid = read($_REQUEST,'guid','');
	$mode = read($_REQUEST,'mode',false);
		
	//$message = read($_REQUEST,'message','');
	$feedback = "notice";
	
	$eventId = read($_REQUEST,'id',NULL);
	$action = read($_REQUEST,'action',NULL);
	$search = read($_REQUEST,'q',NULL);
	$status = read($_REQUEST,'status','all');
	$dateType = read($_REQUEST,'date','all');
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = 20;
	
	
	if( isset($_POST) && $_POST ){
		extract($_POST);
		
		// GLOBAL
		if( $saveGlobal ){
			foreach( $_POST as $key=>$value ){
				if( strstr($key,'config_') ){
					$title = str_replace('config_','',$key);
					$query = "UPDATE store_config SET value=$value WHERE title='$title'";
					$db->query($query);
					if( $db->affected_rows>=0 ){
						$feedback = 'success';
						$message[] = 'Your changes have been saved';
					}
				}
			}
		}
		
		// EDIT categories	
		//print "newCategory($newCategory)<br>\n";
		else if( $newCategory ){
			if ($catTitle = $store->generateName(false, $_POST['cat_title'], 'categories')) {
				$query = "SELECT cat_id FROM store_categories WHERE name='".$_POST['cat_parent']."' AND msv=".($site->id+0)." ";
				//print "get parent($query)<br>\n";
				$parent = $db->get_var($query);
				$query = "INSERT INTO store_categories (title, name, parent_id, msv) 
					VALUES ('".$db->escape($_POST['cat_title'])."', '$catTitle', ".($parent+0).", ".($site->id+0).")";
				if (!$db->query($query)) $message[]="Failed to add new category";
				//print "$query<br>\n";
			}
			else $message[]="There is a problem with this category name";
		}

		// EDIT categories	
		else if( $editCatSave ){
			$tmp = array();
			$complete=false;
			foreach( $_POST as $key=>$val ){
				if( strstr($key,'cat_id') || strstr($key,'delete_id') ){
					$thisKey = substr($key,strpos($key,'_')+3);
					if( strstr($key,'cat_id') ){
						$tmp[$thisKey]['title'] = $val;
						if( $name = $store->generateName($thisKey,$val,'categories') ){
							$tmp[$thisKey]['name'] = $name;
							$complete=true;
						}else{
							$complete=false;
							break;
						}
					}else if( strstr($key,'delete_id') ){
						$tmp[$thisKey]['delete'] = 1;
					}
				}
			}
			if( $complete ){
				if( $store->saveCategories($tmp) ){
					$feedback = 'success';
					$message[] = 'Your changes have been saved';				
				}else{
					$feedback = 'error';
					$message[] = 'Your changes could not be saved';
				}
			}else{
				$feedback = 'error';
				$message = 'Your changes could not be saved';			
			}
			//echo '<pre>'. print_r($tmp,true) .'</pre>';
		}
		
		// ADD variant type
		else if( $newVariantType ){
			if( $store->addVariantType($var_title) ){
				$feedback = 'success';
				$message[] = 'Your changes have been saved';				
			}else{
				$feedback = 'error';
				$message[] = 'Your changes could not be saved';			
			}	
			unset($var_title);		
		}


		// ADD variant
		else if( $newVariant ){
		//echo '<pre>'. print_r($_POST,true) .'</pre>';
			if( $store->addVariant($var_type,$var_title) ){
				$feedback = 'success';
				$message[] = 'Your changes have been saved';				
			}else{
				$feedback = 'error';
				$message[] = 'Your changes could not be saved';			
			}		
			unset($var_title,$var_type);	
		}
		
		else if ($action=="delivery") {
			
			// Save packaging costs
			if ($mode == "pack") {
				foreach ($_POST as $k=>$v) {
					if (substr($k,0,4)=="zone") {
						$tmp_zone = substr($k,4)+0;
						if ($v!=$_POST['cost'.$tmp_zone]) {
							$query = "UPDATE store_shipping_zones SET packaging_value = '".($v+0)."' WHERE zone_id=".(substr($k,4)+0);
							//print "$query<br>\n";
							if (!$db->query($query)) $message[]="Failed to update shipping zone[".(substr($k,4)+0)."]";
						}
						//else print "price for zone $tmp_zone has not changed<br>\n";
					}
				}
				if (!count($message)) {
					$message[]="Packaging costs have been updated";	
					$feedback="success";
				}
			}
			// Change postage data
			else if ($mode=="post") {
				//print_r($_POST);
				$success_count = $failure_count = 0;
				foreach ($_POST as $k=>$v) {
					if (substr($k,0,4)=="post") {
						$tmp_post = substr($k,4)+0;
						//print "got post($tmp_post) gram(".$_POST['gram'.$tmp_post].") price(".$_POST['cost'.$tmp_post].")<br>\n";
						if ($_POST['dele'.$tmp_post]==1) {
							$query = "DELETE FROM store_shipping_weight WHERE id = ".$tmp_post;
							if ($db->query($query)) $success_count++;
							else $failure_count++;
						}
						else {
							$new_over_kg = number_format($_POST['gram'.$tmp_post]/1000, 2, ".", "");
							$new_price = number_format($_POST['cost'.$tmp_post]+0, 2, ".", "");
							$query = "SELECT id FROM store_shipping_weight WHERE over_kg=$new_over_kg AND price=".$new_price;
							//print "$query<br>\n";
							if (!$db->get_var($query)) {
								$query = "UPDATE store_shipping_weight SET 
									over_kg=$new_over_kg, price=$new_price
									WHERE id=".$tmp_post;
								//print "$query<br>\n";
								if ($db->query($query)) $success_count++;
								else $failure_count++;
							}
						}
					}
				}
				// Summary of looping activity
				if ($failure_count) {
					if ($success_count) $message[]="Some row(s) updated ok but there were errors";
					else "Failed to update postage costs";
				}
				else if ($success_count) {
					$message[]=$success_count." row".(($success_count>1)?"s were": " was")." updated";
					$feedback="success";
				}

				// Check if a new postage band to save
				if ($_POST['new_zone']>0 && $_POST['cost_new']>0) {
					//print "save new zone(".$_POST['new_zone'].") gram(".$_POST['gram_new'].") price(".$_POST['cost_new'].")<br>\n";
					$new_over_kg = number_format($_POST['gram_new']/1000, 2, ".", "");
					$new_price = number_format($_POST['cost_new']+0, 2, ".", "");
					//print "got ".$_POST['gram_new']."($new_over_kg) ($new_price)<br>\n";
					$query = "INSERT INTO store_shipping_weight (zone_id, over_kg, price) 
						VALUES 
						(
							".($_POST['new_zone']+0).", 
							$new_over_kg, $new_price
						)
						";
					//print "$query<br>\n";
					if (!$db->query($query)) $message[]="Failed to add new postage price band";
				}
				
			}

		}
		
	}
	
	
	
	
	
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables','../store/style/store'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Store Configuration: '.ucwords($action) : 'Store Configuration';
	$pageTitle = ($action) ? 'Store Configuration: '.ucwords($action) : 'Store Configuration';
	
	$pageClass = 'store_inventory';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
    <div id="primary_inner">

	<?=drawFeedback($feedback,$message)?>

    <ul>
    	<?php
		if ($site->id==1) {
			?>
	        <li><a href="?action=global">General configuration</a></li>
    	    <li><strong>Manage product variations</strong>: <a href="?action=variants&mode=add">Add</a> | <a href="?action=variants&mode=edit">Edit</a></li>
	        <li><strong>Manage delivery costs</strong>: <a href="?action=delivery&mode=post">Postage</a> | <a href="?action=delivery&mode=pack">Packaging</a></li>
           	<?php
		}
		?>
        <li><strong>Manage categories</strong>: <a href="?action=categories&mode=add">Add a new category</a> | <a href="?action=categories&mode=edit">Manage categories</a></li>
    </ul>

	<?php
	if( (!$action || $action=='global') && $site->id==1){ 

        if($config = $store->getConfig(true) ){ 
        	?>
            <form action="" method="post">
                <fieldset>
                    <legend>Store configuration settings</legend>
                    <p class="instructions"><strong>Please note</strong>: These settings will effect the live store</p>
                    <?php
					$i=0; 
					foreach($config as $key=>$item ){ 
						$title = ucwords(str_replace('_',' ',$key));
						?>
                        <div>
                            <label for="f_<?=$key?>"><?=$title?></label>
                            <input type="text" name="config_<?=$key?>" id="f_<?=$title?>" value="<?=$item?>" />
                        </div>
						<?php
						$i++; 
					} 
					?>
                    <div>
                    	<label for="f_submit" style="visibility:hidden;">Submit</label>
	                    <input type="submit" name="saveGlobal" class="submit" value="Save changes" />
                   	</div>
                </fieldset>
            </form>			
        	<? 
		} 
	}
	
	else if( $action=='categories' ){ 
		
		if( !$mode || $mode=='add' ){ 
			?>
            <form action="" method="post">
                <fieldset>
                    <legend>Add a new category</legend>
                    <p class="instructions">First, you need to select a category that will be it's parent.  <br />This means 
                    you could create a category called 't-shirts', with one called'clothing' as the parent.</p>
                    <fieldset class="field">
                        <label for="cat_parent">Parent category</label>
                        <select name="cat_parent">
                            <?=$store->getSelectCategories(0, $cat_parent, false, true, 'Top Level') ?>
                        </select>
                    </fieldset>
                    <fieldset class="field">
                        <label for="cat_title">New category title</label>
                        <input type="text" name="cat_title" id="cat_title" value="<?=$cat_title?>" />
                    </fieldset>
                    <fieldset class="field">
                    	<label for="f_submit" style="visibility:hidden;">Submit</label>
	                    <input type="submit" name="newCategory" value="Save" class="submit" />
                   	</fieldset>
                </fieldset>
            </form>
			<? 
		}
		else if( $mode=='edit' ){ 
			//echo '<pre>'. print_r($store->getCategories(),true) .'</pre>';
			$categories = $store->getCategories();
			?>
            <form action="" method="post">
                <fieldset>
                    <legend>Edit categories</legend>
                    <p class="instructions">Here you can change the names of the categories in your store. Be aware that no two categories can have the same name.</p>
                    <p class="instructions"><strong>Please note</strong>: If a category is deleted that has products associated with it, the products will not be 
                    viewable on the site until they are allocated to a new category.</p>
                    <?php
					if (is_array($categories) && count($categories)>0) {
						foreach($categories as $cat) { 
							?>
                            <fieldset class="field">
							<label for="cat_id<?= $cat['id'] ?>">Category title</label>
							<input type="text" name="cat_id<?= $cat['id'] ?>" id="cat_id<?= $cat['id'] ?>" value="<?= $cat['title'] ?>" />
							<label for="delete_id<?= $cat['id'] ?>">Delete</label>
							<input type="checkbox" name="delete_id<?= $cat['id'] ?>" id="delete_id<?= $cat['id'] ?>" />
                            </fieldset>
							<?php 
                            if( $cat['children'] ){ 
                                foreach( $cat['children'] as $ccat ){ 
                                    ?>
                                    <fieldset class="field" style="padding-left:30px;">
                                    <label for="cat_id<?= $ccat['id'] ?>">Category title</label>
                                    <input type="text" name="cat_id<?= $ccat['id'] ?>" id="cat_id<?= $ccat['id'] ?>" class="childcat" value="<?= $ccat['title'] ?>" />
                                    <label for="delete_id<?= $ccat['id'] ?>">Delete</label>
                                    <input type="checkbox" name="delete_id<?= $ccat['id'] ?>" id="delete_id<?= $ccat['id'] ?>" />
                                    </fieldset>
                                    <? 
                                }
                            }
						} 
						?>
                        <fieldset class="field">
                            <label for="f_submit" style="visibility:hidden;">Submit</label>
                            <input type="submit" name="editCatSave" id="f_submit" value="Select" class="submit" />
                        </fieldset>
                        <?php
					}
					else {
						?>
						<p>No categories have been set up yet.</p>
                        <?php
					}
					?>
                </fieldset>
            </form>	
			<? 
		} 
	}

	else if( $action=='delivery' ){ 
	
		if ($mode == "post") {
		
			// Show postage costs.
			$query = "SELECT sz.zone_id, sz.title, 
				sw.id, sw.over_kg, sw.price 
				FROM store_shipping_zones sz 
				LEFT JOIN store_shipping_weight sw ON sz.zone_id=sw.zone_id
				ORDER BY sz.zone_id, sw.over_kg
				";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				$zones = array();
				?>
                <form method="post" id="postage-form">
                <fieldset>
					<input type="hidden" name="action" value="<?=$action?>" />
					<input type="hidden" name="mode" value="<?=$mode?>" />
                	<legend>Set up postage costs</legend>
                    <p class="instructions">Please enter delivery weight bands for each delivery zone. Each band should start at the minimum weight for the band and will be automatically capped at the start of the next band with the final band having no upper limit.</p>
					<?php
					foreach ($results as $result) {
						$zones[$result->zone_id]='<option value="'.$result->zone_id.'">'.$result->title.'</option>';
						?>
                        <div class="fields" class="postage">
                            <label for="f_gram<?=$result->id?>"><?=$result->title?> from (g)</label>
                            <input type="hidden" name="post<?=$result->id?>" value="<?=$result->zone_id?>" />
                            <input type="text" class="text" name="gram<?=$result->id?>" id="f_gram<?=$result->id?>" value="<?=floor($result->over_kg*1000)?>" />
                            <label class="noclear" for="f_cost<?=$result->id?>">Cost £</label>
                            <input type="text" class="text" name="cost<?=$result->id?>" id="f_cost<?=$result->id?>" value="<?=number_format($result->price, 2, ".", "")?>" />
                            <label class="noclear" for="f_dele<?=$result->id?>">Delete?</label>
                            <input type="checkbox" class="checkbox" name="dele<?=$result->id?>" id="f_dele<?=$result->id?>" value="1" />
						</div>
                        <?php
					}
					?>
                    <p class="instructions">Use the form below if you need to add a new zone.</p>
                    <div class="fields" class="postage">
                        <select name="new_zone" id="new-zone">
                        	<?php foreach($zones as $tmp) echo $tmp; ?>
                        </select>
                        <input type="text" class="text" name="gram_new" id="f_gram_new" value="0" />
                        <label class="noclear" for="f_cost_new">Cost £</label>
                        <input type="text" class="text" name="cost_new" id="f_cost_new" value="<?=number_format(0, 2, ".", "")?>" />
                        <label for="f_submit" style="visibility:hidden;">Submit</label>
                        <input type="submit" class="submit" value="Save" id="f_submit" />
                    </div>
               </fieldset>
               </form>
               <?php
			}                    
			else {
				?>
                <p>Failed to get postage weight bands</p>
                <?php
			}
		}
		
		if ($mode == "pack") {
		
			// Show packaging costs.
			$query = "SELECT zone_id, title, packaging_value FROM store_shipping_zones ORDER BY zone_id";
			if ($results = $db->get_results($query)) {
				?>
				<form method="post">
				<fieldset>
                	<legend>Set up packaging costs</legend>
                    <p class="instructions">Packaging costs are fixed for all deliveries and do not vary by the number of items purchased or the total weight of the order.</p>
					<input type="hidden" name="action" value="<?=$action?>" />
					<input type="hidden" name="mode" value="<?=$mode?>" />
					<?php
					foreach($results as $result) {
						?>
						<label for="f_<?=$result->zone_id?>"><?=$result->title?></label>
						<input type="text" name="zone<?=$result->zone_id?>" id="f_<?=$result->zone_id?>" value="<?=$result->packaging_value?>" />
						<input type="hidden" name="cost<?=$result->zone_id?>" value="<?=$result->packaging_value?>" />
						<?php
					}
					?>
                    <label for="f_submit" style="visibility:hidden;">Submit</label>
                    <input type="submit" class="submit" value="Save" id="f_submit" />
				</fieldset>
				</form>
				<?php
			}
			else {
				?>
                <p>Failed to get shipping zones</p>
                <?php
			}
		}
		
	}
	
	
		
	else if( $action=='variants' ){ ?>
				<? if( !$mode || $mode=='add' ){ ?>
				<form action="" method="post">
					<fieldset>
						<legend>Add a new product variation type</legend>
						<p class="instructions">Variations are the different types of a product you might have in stock such as 
						a t-shirt in several colours and/or sizes.  These should try to be fairly generic so that they can apply to many 
						types of product.  When editing a product you can choose any number of these to describe the properties that product has.</p>
						<label for="var_title">New variation title</label>
						<input type="text" name="var_title" id="var_title" value="<?= $var_title ?>" />
						<button type="submit" name="newVariantType" value="1" class="submit">Save</button>
						<? if( $types = $store->getVariantTypes() ){ ?>
						<p class="instructions">Existing types of product variations</p>
						<ul>
							<? foreach( $types as $t ){ ?>
							<li><?= $t->title ?></li>
							<? } ?>
						</ul>
						<? } ?>
					</fieldset>
				</form>		
				
				<form action="" method="post">
					<fieldset>
						<legend>Add a new product variation</legend>
						<p class="instructions">Variations are the different types of a product you might have in stock such as 
						a t-shirt in several colours and/or sizes.  These should try to be fairly generic so that they can apply to many 
						types of product.  When editing a product you can choose any number of these to describe the properties that product has.</p>
						<? $types = $store->getVariantTypes() ?>
						<label for="var_type">Variation types</label>
						<select name="var_type">
							<? foreach( $types as $t ){ ?>
							<option value="<?= $t->type_id ?>"><?= $t->title ?></option>
							<? } ?>
						</select>
						<label for="var_title">New variation title</label>
						<input type="text" name="var_title" id="var_title" value="<?= $var_title ?>" />
						<button type="submit" name="newVariant" value="1" class="submit">Save</button>
						<p class="instructions">These variations currently exist:</p>
						<ul>
							<? foreach( $types as $t ){ ?>
							<li><?= $t->title ?>
							<? if( $list = $store->getVariantList($t->type_id) ){ ?>
								<ul>
								<? foreach( $list as $item ){ ?>
									<li><?= $item->title ?></li>
								<? } ?>
								</ul>
							<? } ?>
							</li>
							<? } ?>
						</ul>
					</fieldset>
				</form>	
				<? }else if( $mode=='edit' ){ ?>
				
				<p>This screen needs a table of variantions and their types and a list of the title sof variation types</p>
				
				<? } ?>	
					
			<? } ?>
			</div>
		</div>
		
      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); ?>