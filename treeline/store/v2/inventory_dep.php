<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

	$storeURL = '/shop'; // this is set in rewrite for the front-end and should probably be done dynamically...
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	// Make sure access is allowed to the store configuration
	if (!$site->getConfig('setup_store')) {
		redirect("/treeline/?msg=store is not configured for this website");
	}

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/store/includes/basket.class.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/store/includes/store.class.php");

	$basket = new Basket();
	$store = new Store();

	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','notice');
	
	$eventId = read($_REQUEST,'id',NULL);
	$action = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET,'action',NULL);
	$search = read($_REQUEST,'q',NULL);
	$status = read($_REQUEST,'status','all');
	$dateType = read($_REQUEST,'date','all');
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results

	$currentPage = read($_REQUEST,'page',1); // pagination value
	$perPage = 20;
	
	$productName = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET,'product',false);
	$productID = read($_REQUEST,'productID',false);

	$status = array('suspended','live');
	
	$images = $store->getProductImages($productName);
	
	// Can't organise if no images exist so redirect to uploader
	if($action=='organise' && !$images){
		redirect('/treeline/store/inventory.php?action=images&product='. $productName .'&feedback=notice&message='. urlencode('Please upload some images for this product'));
	}
	

	if( isset($_POST) && $_POST ) {

		extract($_POST);
		//echo '<pre>'. print_r($_POST,true) .'</pre>';
		
		if( $uploader>'' ){
			$action = 'organise';
		}
		
		// SORT ORDER
		if($saveSortOrder) {
			$tmp = array();
			foreach($_POST as $key=>$val){
				if( strstr($key,'sort_') ){
					$tmpKey = explode('_',$key);
					$tmpKey = $tmpKey[1];
					$t = explode('::',$tmpKey);
					$tmp[$t[0]] = $val;
				}
			}
			//echo '<pre>'. print_r($tmp,true) .'</pre>';
			if( $store->saveSortOrder($tmp) ){
				$feedback = 'success';
				$message = 'Your changes have been saved';
			}else{
				$feedback = 'error';
				$message = 'Your changes could not be saved';
			}
		}
		
		// DETAILS
		if( $saveDetails ){
			$properties = array();
			$complete = false;
			foreach( $_POST as $key=>$val ){
				if( strstr($key,'product_') && $key!='product_name' ){
					$field = substr($key,strpos($key,'_')+1);
					if( ($field=='title') && ($name = $store->generateName($productID,$product_title)) ){
						$properties['name'] = $name;
						$properties[$field] = $val;
					}else{
						$properties[$field] = $val;
					}
				}
			}
			if( !isset($properties['variants']) ){
				$properties['variants'] = array(0=>4);
			}
			//echo '<pre>'. print_r($properties,true) .'</pre>';
			
			if( $store->saveProduct($productID,$properties) ){ // if we have an ID it's an edit
				$feedback = 'success';
				$message = 'Your product details have been saved.';
			}else{
				$feedback = 'error';
				$message[] = 'Your product details could not be saved. '.print_r($store->msg, true);
			}
			
		}
		
		
		// ADD
		if( $addNewInventory ){
			//echo 'Add<br><pre>'. print_r($_POST,true) .'</pre>';
			$properties = array();
			foreach( $_POST as $key=>$val ){
				if( substr($key,0,2)=='id' && $val ){
					$id = substr($key,2,strpos($key,'_')-2);
					$field = substr($key,strpos($key,'_')+1);
					if( substr($key,strpos($key,'_')+1,3)=='var' ){
						$var = substr($key,strpos($key,'_var')+4);
						$properties[$id]['variants'][$var] = $val;
					}else{
						$properties[$id][$field] = $val;
					}
				}
			}
			//echo '<pre>'. print_r($properties,true) .'</pre>';
			if( $store->addInventory($productID,$properties) ){
				$feedback = 'success';
				$message = 'Your changes have been saved';
				$numberOfNew=false;
			}else{
				$feedback = 'error';
				$message = 'Your changes could not be saved';
			}
		}
		
		// UPDATE
		if( $saveInventory ){
			//echo 'Save<br><pre>'. print_r($_POST,true) .'</pre>';
			$properties = array();
			foreach( $_POST as $key=>$val ){
				if( substr($key,0,2)=='id' && $val ){
					$id = substr($key,2,strpos($key,'_')-2);
					$field = substr($key,strpos($key,'_')+1);
					if( substr($key,strpos($key,'_')+1,3)=='var' ){
						$var = substr($key,strpos($key,'_var')+4);
						$properties[$id]['variants'][$var] = $val;
					}else{
						$properties[$id][$field] = ($val>'' ? $val : '0');
					}
				}
			}
			//echo '<pre>'. print_r($properties,true) .'</pre>';
			
			if( $store->updateInventory($productID,$properties) ){
				$feedback = 'success';
				$message = 'Your changes have been saved';
			}else{
				$feedback = 'error';
				$message = 'Your changes could not be saved';
			}
			
		}
		
		// DELETE 
		if( $deleteProduct && $productID ){
			if( $store->deleteProduct($productID) ){
				$feedback = 'success';
				$message = 'Your product has been deleted';
				$action=false;
				$productName=false;
			}else{
				$feedback = 'error';
				$message = 'Your product could not be deleted';
			}
		}
	
	
		// ORGANISE IMAGES
		if($saveImageDetails){	
			$images = $_POST['gi'];
			$product = $store->loadByName($productName);
			
			if( $store->updateProductImages($product->product_id,$images) ){
				$feedback = 'success';
				$message = 'Your images have been updated';			
			}
			else $message[] = 'Your images could not be updated';
			
			// If we just deleted the last image we need to go back to the upload page.
			$tmp = $store->getProductImages($productName);
			if (!$tmp) redirect('/treeline/store/inventory.php?action=images&product='. $productName .'&feedback=notice&message='. urlencode('Please upload some images for this product'));
		}
		
	}
	
	
	
	
	
	
	// PAGE specific HTML settings
	$css = array('forms','tables','galleries','../store/style/store'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	if ($productName>'' && ($action=="uploader" || 1)) {
		$js[]="swfupload";
		$js[]="swfsetup";
		$extraOnloadJS.=' 
swfu = new SWFUpload(swfsetup(\''.$productName.'\', \'../store/upload_store_process\')); 
';
	}
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Store Inventory: '.ucwords($action) : 'Store Inventory';
	$pageTitle = ($action) ? 'Store Inventory: '.ucwords($action) : 'Store Inventory';
	
	$pageClass = 'store_inventory';

	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	

?>

<div id="primarycontent">
	
    <div id="primary_inner">

	<?php
	echo drawFeedback($feedback,$message);

	// ****************************************************
	// Add, Edit or Search
	if( !$action || in_array($action,array('add','edit')) ){ 

		// Display product list.
		if( !$productName  ) { 
			
			// Show the search product form.
			$page_html = '
				<a href="?action=add&product=new">Add a new product</a>
				<form action="" method="post" class="filter">
				<fieldset>
					<legend>Find a product</legend>
					<p class="instructions">This space should have filters for the list of orders.</p>
					<div class="fields">
						<label for="prodName">Product Name</label>
						<input type="text" name="prodName" id="prodName" value="'.$prodName.'" />
					</div>
					<div class="fields">
						<label for="filterStatus">Product Status</label>
						<select name="filterStatus" id="filterStatus">
							<option value="">-- all --</option>
				';
			foreach($status as $key=>$value){ 
				$page_html.='<option value="'.$key.'"'.($filterStatus>'' && $filterStatus==$key ? ' selected="selected"' : '').'>'.ucfirst($value).'</option>'."\n";
			}
			$page_html.='
						</select>
					</div>
					<div class="fields">
						<label for="f_submit" style="visibiltiy: hidden;">Submit</label>
						<input type="submit" class="submit" id="f_submit" name="useFilter" value="Search" />
					</div>
				</fieldset>
				</form>		
				';
			echo treelineBox($page_html, "Find products", "blue");
			
			// Show the list of matching products.
			$products = $store->getProductList($prodName, $perPage, $currentPage, $filterStatus);
			$productCount = sizeof($products);
			print "Search returned $productCount products<br>\n";
				
			if( $productCount>0 ) { 
				?>
				<form action="<?= str_replace("?".$_SERVER['QUERY_STRING'],'',$_SERVER['REQUEST_URI']) ?>" method="post">
                	<input type="hidden" name="page" value="<?=$currentPage?>" />
					<table class="treeline">
						<caption>List products from <em><?=(($currentPage-1)*$perPage)?></em> to <em><?= ($productCount<$perPage?$store->totalProducts:($currentPage*$perPage)) ?></em> of <em><?=$store->totalProducts?></em><?= ($prodName ? ' (filtered by <em>'. $prodName .'</em>)</caption>' : '') ?>
						<thead>
							<tr>
								<th scope="col">Title</th>
								<th scope="col">Summary</th>
								<th scope="col">Status</th>
								<th scope="col">Display order</th>
								<!--//<th scope="col">Stock level</th>
								<th scope="col">Country</th>//-->
								<th scope="col" width="20em">Actions</th>
							</tr>
							<tbody>
							<?php
							foreach($products as $row){ 
								?>
								<tr>
									<td><a href="?product=<?= $row->name ?>"><?= $row->title ?></a></td>
									<td><?= $row->short_desc ?></td>
									<td><?= ucfirst($status[$row->status]) ?></td>
									<td>
										<select name="sort_<?= $row->name ?>">
											<option value="">--</option>
											<?php 
											for($i=1;$i<=$store->totalProducts;$i++){ 
												$sortOrder = (${'sort_'.$row->name} ? ${'sort_'.$row->name} : $row->priority);
												?>
												<option value="<?= $i ?>"<?= ($sortOrder==$i ? ' selected="selected"' :'') ?>><?= $i ?></option>
												<? 
											} 
											?>
										</select>
									</td>
									<!--//<td><?//= $row->stock_level ?></td>
									<td><?//= $row->price ?></td>//-->
									<td>
										<a href="?action=edit&amp;product=<?= $row->name ?>">edit</a> | 
										<a href="?action=delete&amp;product=<?= $row->name ?>">delete</a>
									</td>
								</tr>
								<? 
							} 
							?>
							</tbody>
						</thead>
					</table>
					<input type="submit" class="submit" name="saveSortOrder" value="Save changes" />
				</form>
				<?php
				
				echo drawPagination($store->totalProducts, $perPage, $currentPage);
			}
			else {
				echo '<p>There were no products found matching your search</p>';
			}
	    }
    
		// Edit a product
		else { 

			$product = $store->loadByName($productName,false);
			?>
            <form action="" method="post" id="products" >
                <input type="hidden" name="product" value="<?= $product->name ?>" />
                <input type="hidden" name="productID" value="<?= $product->product_id ?>" />
                <fieldset>
                    <legend>Edit Product</legend>
                    <p class="instructions">
                        Adding or editing a product is a simple 3 stage process. First add the details of the product, 
                        then manage it's images, then add any variations of it such as colour and size.
                    </p>
                    
                    <fieldset>
                        <legend>Product Details</legend>
                        <div class="fields">
                            <label for="product_title">Title</label>
                            <input type="text" class="text" name="product_title" id="product_title" value="<?= $product->title ?>" />
                        </div>
                        <div class="fields">
                            <label for="product_category">Category</label>
                            <select name="product_category" id="product_category">
                            <?= $store->getSelectCategories(0,$product->category) ?>
                            </select>
                        </div>

                        <div class="fields">
                            <label for="f_author">Author</label>
                            <input class="text" type="text" name="product_author" id="f_author" value="<?= $product->author ?>" />
                        </div>
                        
                        <div class="fields">
                            <label for="product_short_desc">Summary</label>
                            <textarea name="product_short_desc" id="product_short_desc"><?= $product->short_desc ?></textarea>
                        </div>
                        <div class="fields">
                            <label for="product_long_desc">Full Description</label>
                            <textarea name="product_long_desc" id="product_long_desc"><?= $product->long_desc ?></textarea>
                        </div>
                        
                        <?=$store->drawCategorySelects()?>

                        <div class="fields">
                            <label for="product_tag">Tag</label>
                            <input type="text" class="text" name="product_tag" id="product_tag" value="<?= $product->tag?>" />
                        </div>
                        
                        <div class="fields">
                            <label for="product_status">Status</label>
                            <select name="product_status" id="product_status">
                            <? foreach($status as $key=>$value){ ?>
                                <option value="<?= $key ?>"<?= ($product->status==$key ? ' selected="selected"' : '') ?>><?= ucfirst($value) ?></option>
                            <? } ?>
                            </select>
                        </div>

                        <div class="fields">
                            <label for="product_homepage">On homepage</label>
                            <input type="checkbox" name="product_featured" id="product_homepage"  <?=($product->featured?' checked="checked"':'')?> style="margin: 0.5em 0;" />
                        </div>

                    </fieldset>
                    
                    <fieldset style="display: <?=($store->config['extra-info']?"block":"none")?>">
                        <legend>Product Information Panels</legend>
                        <p class="instructions">These feature in a panel underneath the main product display</p>
                        <div class="fields">
                            <label for="product_info">Product Info</label>
                            <textarea name="product_info" id="product_info"><?= strip_tags($product->about_tab1) ?></textarea>
                        </div>
                        <div class="fields">
                            <label for="product_care">Care Information</label>
                            <textarea name="product_care" id="product_care"><?= strip_tags($product->about_tab2) ?></textarea>
                        </div>
                    </fieldset>
                    
                    <fieldset>
                        <legend>What variations are there for this product?</legend>
                        <p class="instructions">Does this product have different colours and sizes, for example?<br />
                        If this product has no variations, please select 'Single Item'</p>
                        <label for="product_variants">Variants</label>
                        <?
                        if( $pv = $store->getVariantTypes($product->product_id) ){ 
                            //echo '<pre>'. print_r($pv,true) .'</pre>';
                            $types = (!is_object($pv[1])) ? $pv[0] : $pv ;
                            $selected = (is_array($pv)) ? $pv[1] : '' ;
                        ?>
                        <select name="product_variants[]" id="product_variants" multiple="multiple">
                        <? foreach( $types as $type ){ ?>
                            <option value="<?= $type->type_id ?>"<?= ((is_array($selected) ? in_array($type->type_id,$selected) : $type->type_id==$selected) ? ' selected="selected"' :'') ?>><?= $type->title ?></option>
                        <? } ?>
                        </select>
                        <? } ?>
                    </fieldset>
                    
                    <fieldset id="controls">
                        <button type="submit" class="submit" name="saveDetails" value="1">Save changes</button>
                        <a href="/treeline/store/inventory.php?action=organise&amp;product=<?= $product->name ?>" class="fauxButtons">Manage product images</a>
                        <a href="/treeline/store/inventory.php?action=variants&amp;product=<?= $product->name ?>" class="fauxButtons">Manage product variations</a>
                    </fieldset>
                </fieldset>
            </form>
			<?php
    	}

	}

	// ****************************************************
	// UPLOAD IMAGES
	else if( $action=='images' ) { 
	
		$product = $store->loadByName($productName,false);
	
		$gallery_dir = $_SERVER['DOCUMENT_ROOT'].'/store/images/'.$product->product_id;
		//print "check if dir($gallery_dir) exists<br>\n";
		if (!file_exists($gallery_dir))
		{
			@mkdir($gallery_dir);
			@chmod($gallery_dir, 0755);
		}
		if (!file_exists($gallery_dir)) {
			?>
			<p>Could not create the store gallery directory</p>
			<?php
		}
		else {
	
			$page_html = '
			<!-- Multiple uploader form -->
			<div id="uploader">
				<div id="upload-info"></div>
				<div id="upload-buttons">
					<div id="upload-button" class="uploader-button"></div>
					<button onclick="javascript:swfCancelUpload();" id="upload-cancel" class="uploader-button cancel" style="margin-left: 20px;">Cancel</button>
					<div id="progress-bar"><span id="progress-span"></span></div>
				</div>
			</div>
			<!-- // End of multiple uploader html -->
			';
			
			$page_title = "Select images to upload";
			echo $page_html;
			
			?>
			<button class="submit" name="finished" id="b_finished" onclick="location='/treeline/store/inventory.php?action=edit&product=<?=$productName?>';" style="clear: left; float: right;">Finished</button>
			<?php
		}
	}
	// ****************************************************
	// ORGANISE IMAGES
	elseif( $action=='organise' ){
	
		$product = $store->loadByName($productName,false);
		$images = $store->getProductImages($productName);
		if ($images){
		?>
			<h3>Manage images for '<?= $product->title ?>'</h3>
			<a id="upload-more" href="/treeline/store/inventory.php?action=images&amp;product=<?=$productName?>" class="fauxButtons">Upload more product images?</a>
			<form action="" class="form-organise" method="post" style="">
			<fieldset>
				<input type="hidden" name="action" value="organise" />
				<input type="hidden" name="product" value="<?= $productName ?>" />
				<?php
				//$main_gallery_image	= ($p) ? $_POST['main_gallery_image'] : $gallery->get('main_image_id');
				// No main gallery image set, use the first one in the gallery
				//$main_gallery_image = $images[0]['image_id'];
				
				//echo '<pre>'. print_r($_POST,true) .'</pre>';
				$i=0;
				foreach ($images as $f){
					$i++;
					$u = $f['image_id'];
					$img_id	= $u;
					$img_caption= ($p) ? $_POST['gi'][$u]['caption']	: htmlentities($f['caption']);
					$img_sort_order	= ($p) ? $_POST['gi'][$u]['sort_order']	: htmlentities($f['sort_order']);
					$img_sort_order	= (!$img_sort_order) ? $i : $img_sort_order;
					$img_name = $u.'_sm.'.$f['img_extension'];
					?>
		
					<div class="gimg">
						<div class="imgHolder">
							<img src="/store/images/<?=$product->product_id?>/<?=$img_name?>" alt="<?=$img_caption?>" />
						</div>
						
						<div class="fields">
							<input type="hidden" name="gi[<?=$u?>][id]" value="<?=$u?>" />
							<label for="s<?=$u?>">Sort order</label>
							<input type="text" name="gi[<?=$u?>][sort_order]" id="s<?=$u?>" size="4" maxlength="10" value="<?=$img_sort_order?>" />
							<label for="t<?=$u?>">Caption</label>
							<input type="text" name="gi[<?=$u?>][caption]" id="t<?=$u?>" size="20" maxlength="15" value="<?=$img_caption?>" />
							<label for="m<?=$u?>" class="">Delete</label>
							<input type="checkbox" name="gi[<?=$u?>][marked_for_deletion]" id="m<?=$u?>" value="1" class="checkbox" />
						</div>
					</div>
			
					<? 
				} 
				?>
					
				<fieldset id="controls">
					<input type="submit" class="submit" name="saveImageDetails" value="Save changes" />
					<a href="/treeline/store/inventory.php?action=edit&amp;product=<?= $product->name ?>" class="fauxButtons">Edit product details</a>
					<a href="/treeline/store/inventory.php?action=variants&amp;product=<?= $product->name ?>" class="fauxButtons">Manage product variations</a>
				</fieldset>
				
			</fieldset>
			</form>
			<?php
		}
	}

	// ****************************************************
	else if( $action=='variants' ) { 

		// table of all variants...
		// add, edit & delete
		if( !$_POST ){
			$numberOfNew=false;
		}

		if( $product = $store->loadByName($productName) ){
		
			$vars = $store->getVariantTypes($product->product_id, true);
			$types = (is_array($vars[1])) ? $vars[0] : $vars ;
			$showVariants = ( count($vars[1])==1 && $types[0]->type_id==4 ) ? false : true;
			
			if( $items = $store->getProductItems($product->product_id) ){
				?>
				<form action="" method="post" id="inventory">
					<input type="hidden" name="productID" value="<?= $product->product_id ?>" />
					<fieldset>
						<table id="variants" class="treeline">
							<caption>Edit <?= ($showVariants ? 'Variations' : 'Item Details') ?> of '<a href="<?= $storeURL . $basket->getProductURL($product->product_id)?>&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width=920" class="thickbox"><?= $product->title ?></a>'
								<?= ( file_exists($_SERVER['DOCUMENT_ROOT'] .'/silo/store/'. $product->product_id .'/1.jpg') ? '<div style="background:url(/silo/store/'. $product->product_id .'/1_vsm.jpg) no-repeat 50% 50%;width:40px;height:40px;float:right;margin-top:-1.2em;margin-bottom:-10px;"></div>' : '') ?>
							</caption>
							<thead>
								<tr>
									<th scope="col">ID</th>
									<th scope="col">Tagline</th>
									<? if( $showVariants ){ ?>
									<th scope="col">Variations</th>
									<? } ?>
									<th scope="col">Price (&pound;)</th>
									<th scope="col">Weight (kg)</th>
									<th scope="col">Stock level</th>
									<th scope="col">Delete</th>
								</tr>
							</thead>
							<tbody>
							<? foreach( $items as $item ){ 
									$id = $item->item_id;
									$class = ($item->stock_level==0 ? ' class="soldOut"' :'');
							?>
								<tr<?= $class ?>>
									<td><?= $id ?></td>
									<td><input type="text" name="id<?= $id ?>_tagline" value="<?= $item->tagline ?>" /></td>
									<?
									if( $showVariants ){
									$itemSelected = $store->getItemVariants($product->product_id, $id);
									$itemSelected = strstr($itemSelected,',') ? explode(',',$itemSelected) : array($itemSelected);								
									?>
									<td>
									<? 	
									foreach($types as $i){ 
										$label = $i->title;
										$i = $i->type_id;
										$list = $store->getVariantList($i);
									?>
										<label for="id<?= $id ?>_var<?=$i?>"><?= $label ?></label>
										<select name="id<?= $id ?>_var<?=$i?>" id="id<?= $id ?>_var<?=$i?>">
											<option value="">--</option>
											<? foreach( $list as $l ){ ?>
											<option value="<?= $l->variant_id ?>"<?= (in_array($l->variant_id,$itemSelected) ? ' selected="selected"' : '') ?>><?= $l->title ?></option>
											<? } ?>
										</select>
									<? 	} ?>
									</td>
									<? } ?>
									<td><input type="text" class="int" name="id<?= $id ?>_price" value="<?= $item->price ?>" maxlength="6" /></td>
									<td><input type="text" class="int" name="id<?= $id ?>_weight" value="<?= $item->weight ?>" maxlength="6" /></td>
									<td><input type="text" class="int" name="id<?= $id ?>_stock_level" value="<?= $item->stock_level ?>" maxlength="6" /></td>
									<td><input type="checkbox" name="id<?= $id ?>_delete" value="1" /></td>
								</tr>
							<? } ?>
							</tbody>
						</table>
						
						<button type="submit" name="saveInventory" class="submit" value="1">Save changes</button>
					</fieldset>
				</form>
				<? 
			}
			else { 
				$numberOfNew=1; 
			} 
			
		} 


		if( ((!$numberOfNew && $showVariants) || (!$numberOfNew && !$showVariants) || (!$numberOfNew && !$items)) && $types[0]->type_id!=4 ){ 
			/*
			echo 'numberofNew: '. $numberOfNew .'<br />';
			echo 'showVariants: '. $showVariants .'<br />';
			echo 'items: '. $items .'<br />';
			echo '$types[0]->type_id: '. $types[0]->type_id .'<br />';
			echo '<pre>'. print_r($_POST,true) .'</pre>';
			
			numberofNew: 1
			showVariants: 1
			items:
			$types[0]->type_id: 8
			*/
			?>
			<form action="" method="post" id="addNumberOfNewVariants">
				<fieldset>
                	<input type="hidden" name="product" value="<?=$productName?>" />
                	<input type="hidden" name="action" value="<?=$action?>" />
					<legend>Add new variations?</legend>
					<label for="numberOfNew">Number of new variations to add:</label>
					<select name="numberOfNew" id="numberOfNew">
					<? for($i=1;$i<=10;$i++){ ?>
						<option value="<?= $i ?>"><?= $i ?></option>
					<? } ?>
					</select>
					 <button type="submit" name="addNumberOfNewVariants" value="1" class="submit">Submit</button>
				</fieldset>
			</form>
		
			<? 
		}
		else if( ($numberOfNew && $showVariants) || $numberOfNew || !$items ){ 
			$numberOfNew = (!$items) ? 1 : $numberOfNew;
			?>
		
			<form action="" method="post" id="addNewVariants">
				<input type="hidden" name="productID" value="<?= $product->product_id ?>" />
				<input type="hidden" name="numberOfNew" value="<?= $numberOfNew ?>" />
				<fieldset>
					<p class="instructions">The 'tagline' is shown in the shopping basket to help differentiate different variantions of a product.</p>
					<table id="newVariants" class="treeline">
						<caption><?= ($types[0]->type_id==4 && !$items ? 'Add details to ' : 'Add new variations of '). '\''. $product->title .'\'' ?></caption>
						<thead>
							<tr>
								<th scope="col">Number</th>
								<th scope="col">Tagline</th>
								<? if( $showVariants ){ ?>
								<th scope="col">Variations</th>
								<? } ?>
								<th scope="col">Price (&pound;)</th>
								<th scope="col">Weight (Kg)</th>
								<th scope="col">Stock level</th>
							</tr>
						</thead>
						<tbody>
						<? for( $id=1; $id<=$numberOfNew; $id++ ){ ?>
							<tr>
								<td><?= $id ?></td>
								<td><input type="text" name="id<?= $id ?>_tagline" value="" /></td>
								<? if( $showVariants ){ ?>
								<td>
								<? 	
									$vars = $store->getVariantTypes($product->product_id, true);
									$types = (is_array($vars)) ? $vars[0] : $vars ;
									
									foreach($types as $i){ 
										$label = $i->title;
										$i = $i->type_id;
										$list = $store->getVariantList($i);
									?>
									<label for="id<?= $id ?>_var<?=$i?>"><?= $label ?></label>
									<select name="id<?= $id ?>_var<?=$i?>" id="id<?= $id ?>_var<?=$i?>">
										<option value="">--</option>
										<? foreach( $list as $l ){ ?>
										<option value="<?= $l->variant_id ?>"><?= $l->title ?></option>
										<? } ?>
									</select>
								<? 	} ?>
								</td>
								<? } ?>
								<td><input type="text" class="int" name="id<?= $id ?>_price" value="" maxlength="6" /></td>
								<td><input type="text" class="int" name="id<?= $id ?>_weight" value="" maxlength="6" /></td>
								<td><input type="text" class="int" name="id<?= $id ?>_stock_level" value="" maxlength="6" /></td>
							</tr>
						<? } ?>
						</tbody>
					</table>
					<button type="submit" class="submit" name="addNewInventory" value="1">Add new items</button>
				</fieldset>
			</form>
		
			<? 
		}
		?>
		<form>
		<fieldset id="controls">
			<!--<button type="submit" class="submit" name="saveDetails" value="1">Save changes</button>-->
			<a href="/treeline/store/inventory.php?action=edit&amp;product=<?= $product->name ?>" class="fauxButtons">Edit product details</a>
			<a href="/treeline/store/inventory.php?action=organise&amp;product=<?= $product->name ?>" class="fauxButtons">Manage product images</a>
		</fieldset>
		</form>
		
		<? 
	}
			
	// ****************************************************
	else if( $action=='delete' ){ 
		$product = $store->loadByName($productName);
		?>
	
		<form action="" method="post" name="deleteProduct">
			<input type="hidden" name="productID" value="<?= $product->product_id ?>" />
			<fieldset>
				<legend>Confirm deletion of '<?= $product->title ?>'</legend>
				<p class="instructions">Are you sure you want to <strong>permanently</strong> remove this product from the store?</p>
				<button type="submit" name="deleteProduct" value="1" class="submit">Delete</button>
			</fieldset>
		</form>
	
		<? 
	} 
	?>
				
			
	</div>

</div>

<?php 
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>