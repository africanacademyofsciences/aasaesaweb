<?php

ini_set("display_errors", "yes");
error_reporting(E_ALL ^ E_NOTICE);

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
				else {
					$message[]="Your new catgeory has been saved";
					$feedback = "success";
					$mode="edit";
				}
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
		
		// Delete a category
		else if ($deleteCategory) {
			$catid = $_POST['cat_id'];
			//print "delete cat($catid)<br>\n";
			$query = "DELETE FROM store_categories WHERE msv=".$site->id." AND parent_id=".$catid;
			//print "$query<br>\n";
			$db->query($query);
			$query = "DELETE FROM store_categories WHERE msv=".$site->id." AND cat_id=".$catid;
			//print "$query<br>\n";
			$db->query($query);
			$message[]="Your category has been deleted";
			$feedback = "success";
		}


		// Delete a variation
		else if ($deleteVariant) {
			$varid = $_POST['variant_id'];
			//print "delete cat($catid)<br>\n";
			$query = "DELETE FROM store_variants WHERE msv=".$site->id." AND variant_id=".$varid;
			//print "$query<br>\n";
			$db->query($query);
			//$query = "DELETE FROM store_categories WHERE msv=".$site->id." AND cat_id=".$catid;
			//print "$query<br>\n";
			//$db->query($query);
			$message[]="This variation has been deleted";
			$feedback = "success";
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
			//print "Mode($mode)<br>\n";
			if ($mode == "pack") {
				foreach ($_POST as $k=>$v) {
					if (substr($k,0,4)=="zone") {
						$tmp_zone = substr($k,4)+0;
						if ($v!=$_POST['cost'.$tmp_zone]) {
							$query = "REPLACE INTO store_shipping_zones 
								(zone_id, msv, packaging_value)
								VALUES 
								(
									".(substr($k,4)+0).", ".$site->id.", 
									'".($v+0)."'
								)
								";
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
					$new_over_kg = number_format($_POST['gram_new']/1000, 3, ".", "");
					//print "got new over($new_over_kg) from(".$_POST['gram_new'].")<br>\n";
					$new_price = number_format($_POST['cost_new']+0, 2, ".", "");
					//print "got ".$_POST['gram_new']."($new_over_kg) ($new_price)<br>\n";
					$query = "INSERT INTO store_shipping_weight (zone_id, over_kg, price, msv) 
						VALUES 
						(
							".($_POST['new_zone']+0).", 
							$new_over_kg, $new_price,
							".$site->id."
						)
						";
					//print "$query<br>\n";
					if (!$db->query($query)) $message[]="Failed to add new postage price band";
					else $message[]="New postage band added OK";
				}
				
			}

		}
	}
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables','../store/'.$storeVersion.'/style/store'); // all CSS needed by this page
	$extraCSS = '
	
a.category-delete {
	display: block;
	text-indent: -9999px;
	background: url("/treeline/img/icons/blue_delete.gif") no-repeat;
	width: 14px;
	float: left;
	height: 14px;
	margin: 7px 0 5px 5px;
}	

p.used-category {
	font-size: 120%;
	font-weight: bold;
}

'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Store Configuration: '.ucwords($action) : 'Store Configuration';
	$pageTitle = ($action) ? 'Store Configuration: '.ucwords($action) : 'Store Configuration';
	
	$pageClass = 'store';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
    <div id="primary_inner">


	<?=drawFeedback($feedback,$message)?>

	<?php
	//print "Running config($action)<br>\n";
	if( (!$action || $action=='global') && $site->id==1){ 

        if($config = $store->getConfig(true) ){ 
			$page_html = '
            <form action="" method="post">
                <fieldset>
                    <p class="instructions"><strong>Please note</strong>: These settings will effect the live store</p>
					';
			$i=0; 
			foreach($config as $key=>$item ){ 
				$title = ucwords(str_replace('_',' ',$key));
				$page_html.='
				<div>
					<label for="f_'.$key.'">'.$title.'</label>
					<input type="text" name="config_'.$key.'" id="f_'.$title.'" value="'.$item.'" />
				</div>
				';
				$i++; 
			} 
			$page_html .= '
                    <div>
                    	<label for="f_submit" style="visibility:hidden;">Submit</label>
	                    <input type="submit" name="saveGlobal" class="submit" value="Save changes" />
                   	</div>
                </fieldset>
            </form>			
			';
			echo treelineBox($page_html, "Store configuration settings", "blue");
		} 
	}
	
	else if( $action=='categories' ){ 
		
		if( !$mode || $mode=='add' ){ 
			$page_html = '
            <form action="" method="post">
                <fieldset>
                    <p class="instructions">First, you need to select a category that will be the parent.<br />This means 
                    you could create a category called \'t-shirts\', with one called \'clothing\' as the parent.</p>
                    <fieldset class="field">
                        <label for="cat_parent">Parent category</label>
                        <select name="cat_parent">
                            '.$store->getSelectCategories(0, $cat_parent, false, true, 'Top Level').'
                        </select>
                    </fieldset>
                    <fieldset class="field">
                        <label for="cat_title">New category title</label>
                        <input type="text" name="cat_title" id="cat_title" value="'.$cat_title.'" />
                    </fieldset>
                    <fieldset class="field">
                    	<label for="f_submit" style="visibility:hidden;">Submit</label>
	                    <input type="submit" name="newCategory" value="Save" class="submit" />
                   	</fieldset>
                </fieldset>
            </form>
			';
			echo treelineBox($page_html, "Add a new category", "blue");
		}
		else if( $mode=='edit' ){ 
			
			
			// Do we have a category to delete
			if ($_SERVER['REQUEST_METHOD']=="GET" && $_GET['del']>0) {
				$categorytitle = $db->get_var("SELECT title FROM store_categories WHERE cat_id=".$_GET['del']);
				$page_html='
<form action="" method="post" name="deleteCategory">
	<input type="hidden" name="cat_id" value="'.$_GET['del'].'" />
	<fieldset>
		<legend>Confirm deletion of : '.$categorytitle.'</legend>
				';
				
				$query = "SELECT DISTINCT sp.title 
					FROM store_categories_products scp 
					INNER JOIN store_categories sc ON sc.cat_id = scp.cat_id 
					INNER JOIN store_products sp ON sp.product_id = scp.product_id
					WHERE sc.msv=".$site->id."
					AND sp.msv=".$site->id."
					AND sc.cat_id = ".$_GET['del'];
				//print "$query<br>\n";
				if ($results = $db->get_results($query)) {
					$page_html .= '<p>This category is in use by the following products:</p>';
					foreach ($results as $result) {
						$page_html.= '<p class="used-category">'.$result->title.'</p>';
					}
				}
				$page_html.='
		<p class="instructions">Are you sure you want to <strong>permanently</strong> remove this category from the store?</p>
		<label for="f_submit" style="visibility: hidden;">Submit</label>
		<input type="submit" name="deleteCategory" class="submit" id="f_submit" value="Delete" />
	</fieldset>
</form>
				';
				echo treelineBox($page_html, "Delete category", "blue");
			}
			

			
			//echo '<pre>'. print_r($store->getCategories(),true) .'</pre>';
			$categories = $store->getCategories();
			$page_html = '
            <form action="" method="post">
                <fieldset>
                    <p class="instructions">Here you can change the names of the categories in your store. Be aware that no two categories can have the same name.</p>
                    <p class="instructions"><strong>Please note</strong>: If a category is deleted that has products associated with it, the products will not be 
                    viewable on the site until they are allocated to a new category.</p>
					';
			if (is_array($categories) && count($categories)>0) {
				foreach($categories as $cat) { 
					$catid = $cat['id'];
					$page_html .= '
					<fieldset class="field" style="padding-top: 10px;">
						<label for="cat_id'.$catid.'">Category title</label>
						<input type="text" name="cat_id'.$catid.'" id="cat_id'.$catid.'" value="'.$cat['title'].'" />
						'.(!$cat['children']?'<a class="category-delete" href="?action=categories&mode=edit&del='.$catid.'">Delete</a>':'').'
					</fieldset>
					';
					if( $cat['children'] ){ 
						foreach( $cat['children'] as $ccat ){ 
							$page_html .= '
							<fieldset class="field" style="padding: 0 0 0.5em 30px;">
                                <label for="cat_id'.$ccat['id'].'">Category title</label>
                                <input type="text" name="cat_id'.$ccat['id'].'" id="cat_id'.$ccat['id'].'" class="childcat" value="'.$ccat['title'].'" />
                                <a class="category-delete" href="?action=categories&mode=edit&del='.$ccat['id'].'">Delete</a>                            
							</fieldset>
							';
						}
					}
				} 
				$page_html .= '
				<fieldset class="field">
					<label for="f_submit" style="visibility:hidden;">Submit</label>
					<input type="submit" name="editCatSave" id="f_submit" value="Save changes" class="submit" />
				</fieldset>
				';
			}
			else {
				$page_html .= '
				<p>No categories have been set up yet.</p>
				';
			}
			$page_html.='
                </fieldset>
            </form>	
			';
			echo treelineBox($page_html, "Manage categories", "blue");
		} 
	}

	else if( $action=='delivery' ){ 
		if ($mode == "post") {
		
			// Show packaging costs.
			$query = "SELECT id AS zone_id, title 
				FROM store_shipping_zones_types sszt
				WHERE sszt.in_use = 1
				ORDER BY id";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				$zones = array();
				$page_html = '
                <form method="post" id="postage-form">
                <fieldset>
					<input type="hidden" name="action" value="'.$action.'" />
					<input type="hidden" name="mode" value="'.$mode.'" />
                    <p class="instructions">Please enter delivery weight bands for each delivery zone. Each band should start at the minimum weight for the band and will be automatically capped at the start of the next band with the final band having no upper limit.</p>
					';
			foreach ($results as $result) {
			
				// Save this zone for the new band feature later
				$zones[$result->zone_id]='<option value="'.$result->zone_id.'">'.$result->title.'</option>';
				
				// Loop through all postage bands
				$query = "SELECT * FROM store_shipping_weight sw
					WHERE sw.zone_id = ".$result->zone_id."
					AND sw.msv=".$site->id."
					ORDER BY sw.over_kg
					";
				//print "$query<br>\n";
				if ($results2 = $db->get_results($query)) {
					foreach ($results2 as $result2) {
						//print "got a row(".$i++.")<br>\n";
						$page_html.='
						<div class="fields postage">
							<label for="f_gram'.$result2->id.'">'.$result->title.' from (g)</label>
							<input type="hidden" name="post'.$result2->id.'" value="'.$result->zone_id.'" />
							<input type="text" class="text" name="gram'.$result2->id.'" id="f_gram'.$result2->id.'" value="'.floor($result2->over_kg*1000).'" />
							<label class="noclear" for="f_cost'.$result2->id.'">Cost £</label>
							<input type="text" class="text" name="cost'.$result2->id.'" id="f_cost'.$result2->id.'" value="'.(number_format($result2->price, 2, ".", "")).'" />
							<label class="noclear" for="f_dele'.$result2->id.'">Delete?</label>
							<input type="checkbox" class="checkbox" name="dele'.$result2->id.'" id="f_dele'.$result2->id.'" value="1" />
						</div>
						';
					}
				}
			}
			$page_html.='
                    <p class="instructions">Use the form below if you need to add a new zone.</p>
                    <div class="fields" class="postage">
                        <select name="new_zone" id="new-zone">
							';
            foreach($zones as $tmp) $page_html.=$tmp;
			$page_html.='
                        </select>
                        <label class="noclear" for="f_gram_new">From(g)</label>
                        <input type="text" class="text" name="gram_new" id="f_gram_new" value="0" />
                        <label class="noclear" for="f_cost_new">Cost £</label>
                        <input type="text" class="text" name="cost_new" id="f_cost_new" value="'.number_format(0, 2, ".", "").'" />
                        <label for="f_submit" style="visibility:hidden;">Submit</label>
                        <input type="submit" class="submit" value="Save" id="f_submit" />
                    </div>
               </fieldset>
               </form>
			   ';
			}                    
			else {
				$page_html .= '
                <p>Failed to get postage weight bands</p>
				';
			}
			echo treelineBox($page_html, "Set up postage costs", "blue");
		}
		
		if ($mode == "pack") {
		
			// Show packaging costs.
			$query = "SELECT id, title FROM store_shipping_zones_types sszt
				WHERE sszt.in_use = 1
				ORDER BY id";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				$page_html = '
				<form method="post">
				<fieldset>
                    <p class="instructions">Packaging costs are fixed for all deliveries and do not vary by the number of items purchased or the total weight of the order.</p>
					<input type="hidden" name="action" value="'.$action.'" />
					<input type="hidden" name="mode" value="'.$mode.'" />
					';
				foreach($results as $result) {
					$query = "SELECT packaging_value FROM store_shipping_zones WHERE msv=".$site->id." AND zone_id=".$result->id;
					$packaging_value = number_format($db->get_var($query), 2, ".", "");
					$page_html .= '
					<label for="f_'.$result->id.'">'.$result->title.'</label>
					<input type="text" name="zone'.$result->id.'" id="f_'.$result->id.'" value="'.$packaging_value.'" />
					<input type="hidden" name="cost'.$result->id.'" value="'.$packaging_value.'" />
					';
				}
				$page_html.='
                    <label for="f_submit" style="visibility:hidden;">Submit</label>
                    <input type="submit" class="submit" value="Save" id="f_submit" />
				</fieldset>
				</form>
				';
			}
			else {
				$page_html .= '
                <p>Failed to get shipping zones</p>
				';
			}
			echo treelineBox($page_html, "Set up packaging costs", "blue");
		}
	}
	
	
		
	else if( $action=='variants' ){ 
		// Add a new variant type
		if( !$mode || $mode=='add' ){ 
		
		
			// Do we have a variant to delete
			if ($_SERVER['REQUEST_METHOD']=="GET") {
				if ($_GET['del']>0) {
					$varianttitle = $db->get_var("SELECT title FROM store_variants WHERE variant_id=".$_GET['del']);
					$page_html='
<form action="" method="post" name="deleteVariant">
	<input type="hidden" name="variant_id" value="'.$_GET['del'].'" />
	<fieldset>
		<legend>Confirm deletion of : '.$varianttitle.'</legend>
					';
					$query = "SELECT DISTINCT sp.title 
						FROM store_products_variants spv 
						INNER JOIN store_variants sv ON sv.variant_id = spv.variant_id 
						INNER JOIN store_products sp ON sp.product_id = spv.product_id
						WHERE sv.msv=".$site->id."
						AND sp.msv=".$site->id."
						AND sv.variant_id = ".$_GET['del'];
					//print "$query<br>\n";
					if ($results = $db->get_results($query)) {
						$page_html .= '<p>This variant is in use by the following products:</p>';
						foreach ($results as $result) {
							$page_html.= '<p class="used-category">'.$result->title.'</p>';
						}
						$page_html .= '<p>You cannot delete this variation as it is currently used by the store</p>';
					}
					else {
						$page_html.='
		<p class="instructions">Are you sure you want to <strong>permanently</strong> remove this variant from the store?</p>
		<label for="f_submit" style="visibility: hidden;">Submit</label>
		<input type="submit" name="deleteVariant" class="submit" id="f_submit" value="Delete" />
					';
					}
					$page_html.='
	</fieldset>
</form>
					';
					echo treelineBox($page_html, "Delete variant", "blue");
				}
				// Delete a whole variant type
				// We can only do this with empty variants so no need to much about confirming
				// Hopefully nobody will try to hack this on the URL bar.
				else if ($_GET['deltype']>0) {
					$query = "DELETE FROM store_types WHERE type_id=".$_GET['deltype']." AND msv=".$site->id;
					$db->query($query);
					//print "$query<br>\n";				
				}
			}
			
			
			$page_html = '
            <form action="" method="post">
            <fieldset>
                <p class="instructions">Variations are the different types of a product you might have in stock such as 
                a t-shirt in several colours and/or sizes.  These should try to be fairly generic so that they can apply to many 
                types of product.  When editing a product you can choose any number of these to describe the properties that product has.</p>
                <label for="var_title">New variation title</label>
                <input type="text" name="var_title" id="var_title" value="'.$var_title.'" />
                <button type="submit" name="newVariantType" value="1" class="submit">Save</button>
				';
			if( $types = $store->getVariantTypes() ){ 
				$page_html.='
				<p class="instructions">Existing types of product variations</p>
				<ul>
				';
				foreach( $types[0] as $t ){ 
					//print "got type(".print_r($t, true).")<br>\n";
					$page_html.='
					<li>'.$t->title.'</li>
					';
				} 
				$page_html.='
				</ul>
				';
			} 
			$page_html .= '
            </fieldset>
            </form>
			';
			echo treelineBox($page_html, "Add a new product variation type", "blue");
			
			$page_html = '
            <form action="" method="post">
            <fieldset>
                <p class="instructions">Variations are the different types of a product you might have in stock such as 
                a t-shirt in several colours and/or sizes.  These should try to be fairly generic so that they can apply to many 
                types of product.  When editing a product you can choose any number of these to describe the properties that product has.</p>
				';
			$types = $store->getVariantTypes();
			$page_html.='
				<fieldset class="field">
					<label for="var_type">Variation types</label>
					<select name="var_type">
				';
			foreach($types[0] as $t ){ 
				$page_html.='
					<option value="'.$t->type_id.'">'.$t->title.'</option>
				';
			} 
			$page_html.='
					</select>
				</fieldset>
				<fieldset>
					<label for="var_title">New variation title</label>
					<input type="text" name="var_title" id="var_title" value="'.$var_title.'" />
				</fieldset>
				<fieldset>
					<label for="f_submit2" style="visibility: hidden;">Submit</label>
					<input type="submit" name="newVariant" value="Save" id="f_submit2" class="submit" />
				</fieldset>
				
                <p class="instructions">These variations currently exist:</p>
				<ul class="var-type">
				';
			foreach( $types[0] as $t ){ 
				$page_html.='
				<li style="margin-top: 10px;">'.$t->title.'
				';
				if( $list = $store->getVariantList($t->type_id) ){ 
					$page_html.='
					<ul class="var-item" style="margin-top:0;">
					';
					foreach( $list as $item ){ 
						//print "got item(".print_r($item, true).")<br>\n";
						$page_html.='
						<li>'.$item->title.' [<a href="?action=variants&mode=add&del='.$item->variant_id.'">delete</a>]</li>
						';
					} 
					$page_html.='
					</ul>
					';
				} 
				else {
					if (strtolower($t->title)!="single item") $page_html.=' [<a href="?action=variants&mode=add&deltype='.$t->type_id.'">delete</a>]';
				}
				$page_html.='
				</li>
				';
			} 
			$page_html.='
                </ul>
            </fieldset>
            </form>	
			';
			echo treelineBox($page_html, "Add a new product variation", "blue");
		}
		else if( $mode=='edit' ){ 
			$page_html .= '
            <p>This screen needs a table of variantions and their types and a list of the title sof variation types</p>
			';
		} 
	} 

	//print "Ran config($action)<br>\n";
	?>
    </div>
</div>
		
<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>