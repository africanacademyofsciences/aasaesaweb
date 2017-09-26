<?php

	if (!$site->getConfig('setup_store')) redirect("/?msg=the store is not enabled");
	
	// Header image
	$header_img = new HTMLPlaceholder();
	$header_img->load($siteID, 'header_img');
	if (!$header_img->draw()) {
			$header_img->load($siteData->primary_msv, 'header_img');
			if (!$header_img->draw()) {
					$header_img->load(1, 'header_img');
			}
	}
	$header_img->setMode("view");

	
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','');

	
	// Panels
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');
	$panels->setMode($mode);

	$tags = new Tags();
	
	
	if( isset($_POST) && $_POST ) {

	}
	
	$filter = read($_GET,'ftype','date_created');			// filter results by... from GET
	$filtervalue = read($_GET,'fvalue','desc');				// value of the filter (can be asc/desc or a specific value)
	$currentPage = read($_GET,'page',1);					// page as passed by the pagination code
	$thisPage = $currentPage-1;								// current page	
	$perPage = ($currentPage==1) ? 10 : 12;											// results to show per page - could be set by user

	// Page specific options
	//$catID = read($_REQUEST,'cat',false);
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('2col_right','forms','store','store_panels','lytebox'); // all attached stylesheets
	/*if($page->style != NULL){
		$css[] = $page->style;
	}*/
	$extraCSS = '';
	
	$js = array('jquery','preloadCssImages','store','page_functions','lytebox'); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
	//echo $categoryName.'<br />';
	if( $productName ){ 
		$product = $store->loadByName($productName);
	}else{
		$products = $store->loadByCat($categoryName,($thisPage*$perPage),$perPage);
	}
	$itemcount = $store->total;
?>	

	<h1>Store</h1>
    <div id="primarycontent">
		<!--<pre><?//= print_r($products) ?></pre>-->
		<? if( $productName ){ ?>
		
			<? if( $product ){ 
				// variants
				$variants = $store->getProductVariantsSelect($product->product_id);
				$totalVars = count($variants);
				// breadcrumb
				if( $product->breadcrumb ){
					$breadcrumb = '';
					if( $product->breadcrumb->parent_title ){
						$breadcrumb .= '<a href="'. $storeURL .'/'. $product->breadcrumb->parent_name .'">'. $product->breadcrumb->parent_title .'</a> &raquo; ';
						$breadcrumb .= '<a href="'. $storeURL .'/'. $product->breadcrumb->parent_name .'/'. $product->breadcrumb->name .'">'. $product->breadcrumb->title .'</a>';
					}else{
						$breadcrumb .= '<a href="'. $storeURL .'/'. $product->breadcrumb->name .'">'. $product->breadcrumb->title .'</a>';
					}
				}
				$priceRange = explode(',',$product->price_range);
				if( $priceRange[0]!=$priceRange[1] ){
					$price = 'from &pound;'. $priceRange[0] .' to &pound;'. $priceRange[1];
				}else{
					$price = '&pound;'. $product->price;
				}
				
				// need to find out how many we actually have...should be easy once the db is hooked up to images...
				$images = explode(',',$product->images);
				$tmp = array();
				$i=1;
				foreach($images as $key => $value){
					$line = explode('::',$value);
					$tmp[$line[0]] = $line[1];
					if( $i==1 ){
						$mainImg = $line[0];
					}
					$i++;
				}
				$images = $tmp;
				//print "<!-- image($images) --> \n";
				$maxImages = 5;
			?>
				<div id="product" class="hlisting">
					
					<h3 class="item"><span class="fn"><?= $product->title?></span></h3>
					<div class="productImage" id="mainImage" style="background:url(/silo/store/<?= $product->product_id ?>/<?= $mainImg ?>_m.jpg) no-repeat 50% 50%">
						Alt text!
						<a class="magnify">Click to view large image</a>
					</div>
					<p class="price"><strong><?= $price ?></strong><? if( $product->physical==1 ){ ?> + P&amp;P<? } ?></p>
					<p class="short_desc description"><?= $product->long_desc ?>
					<? if( $product->page_guid>'' ){ echo '<blockquote>Find out more: <a href="'. $page->drawLinkByGUID($product->page_guid) .'">'. $page->drawTitleByGUID($product->page_guid) .'</a></blockquote>'; } ?>
					</p>
					<div id="productImageHolder">
					<? // product images... 
						//for($i=1;$i<$maxImages;$i++){
					if( count($images)>1 ){
						foreach($images as $key => $value){
							// $loc = 'c:\\Webserver\\xampp\\htdocs\\magdev\\silo\\store\\'. $product->product_id .'\\'. $i .'.jpg';
							$loc = $_SERVER['DOCUMENT_ROOT'] .'/silo/store/'. $product->product_id .'/'. $key .'.jpg';
							if( file_exists($loc) ){
					?>	
						<div class="smallImageHolder">						
							<div id="image<?= $key ?>" class="productImage productImageSmall" style="background:url(/silo/store/<?= $product->product_id ?>/<?= $key ?>_sm.jpg) no-repeat 50% 50%">Alt text!
								<a class="magnify" href="/silo/store/<?= $product->product_id ?>/<?= $key ?>.jpg" rel="lytebox[product-<?= $product->product_id ?>]" target="_blank" title="<?= $product->title?>: <?= $value ?>">Click to view large image</a>
							</div>
							<p class="caption"><?= $value ?></p>
						</div>
					<?
							}
						}
					}
					?>
					</div>

				</div>			
				
			<? }else{ ?>
				<p>ERROR!<br />There's no product with that name!</p>
			<? } ?>
		
		<? }else{ // if we don't have a product name, assume we're looking at the category... 	?>
		
		<ol start="<?= ($thisPage*$perPage)+1 ?>" id="product_list">
		<?  $i=1;
			$resetPriority = false;
			foreach( $products as $item ){ 
				// variant text...
				$variants = $store->showProductVariants($item->product_id);
				$totalVars = count($variants);
				if( is_array($variants) && $totalVars>0 ){
					$varTxt = 'Available in various ';
					$j=1;
					foreach( $variants as $var ){
						$varTxt .= $var . ($j==($totalVars-1) ? ($j<$totalVars ? ' and ' : ', ') : '');
						$j++;
					}
					$varTxt .= '.';
				}else{
					$varTxt = '';
				}
				// breadcrumb
				if( $thisBC = $store->getProductBreadcrumb($item->product_id) ){
					$breadcrumb = '';
					if( $thisBC->parent_title ){
						$breadcrumb .= '<a href="'. $storeURL .'/'. $thisBC->parent_name .'">'. $thisBC->parent_title .'</a> &raquo; ';
						$breadcrumb .= '<a href="'. $storeURL .'/'. $thisBC->parent_name .'/'. $thisBC->name .'">'. $thisBC->title .'</a>';
					}else{
						$breadcrumb .= '<a href="'. $storeURL .'/'. $thisBC->name .'">'. $thisBC->title .'</a>';
					}
				}

				$priceRange = explode(',',$item->price_range);
				if( $priceRange[0]!=$priceRange[1] ){
					$price = 'from '.$basket->currency . $priceRange[0] .' to '. $basket->currency . $priceRange[1];
				}else{
					$price = $basket->currency . $item->price;
				}

				$productLink = $storeURL . $basket->getProductURL($item->product_id);
				
				
				$images = explode(',',$item->images);
				$j=1;
				foreach($images as $key => $value){
					$line = explode('::',$value);
					if( $j==1 ){
						$mainImg = $line[0];
					}
					$j++;
				}
				

			if( $resetPriority ){
				$i=1;
			}
			//echo 'i=='.$i .', p=='. $item->priority .', reset=='. $resetPriority .'<br />';
			
			if(  in_array($i,array(1,4,7)) ){
				echo '<div class="listHolder">'."\n\t";
			}
		?>
			<li<?= (($i==1 && !$resetPriority) ? ' id="featured"' :'') . ($i==3 ? ' class="last"' : '')?>>
				<? if( $i==1 && !$resetPriority ){ ?>
					<h3><a href="<?= $productLink ?>" title="View '<?= $item->title?>' details"><?= $item->title?></a></h3>
					<div class="productImage" style="background:url(/silo/store/<?= $item->product_id ?>/<?= $mainImg ?>_m.jpg) no-repeat 50% 50%">
						Alt text!
						<a href="<?= $productLink ?>" class="magnify">View '<?= $item->title?>' details</a>
					</div>
					
					<div class="productInfo">
						<p class="price">Category: <?= $breadcrumb ?><br /><strong><?= $price ?></strong><? if( $item->physical==1 ){ ?> + P&amp;P<? } ?></p>
						<p class="short_desc"><?= $item->short_desc ?></p>
						<p class="variants"><?= $varTxt ?></p>
					<? if( $item->stock_level<=$store->config['sold_out'] ){ ?>
						<p class="stock_level">Sold out.  Awaiting more stock</p>
					<? }else{ ?>
						<? if($totalVars==0){ ?>
							<a href="/shopping-basket?id=<?= $item->item_id ?>&amp;quantity=1" class="add-to-basket">Add to basket</a>
						<? }else{ ?>
							<a href="<?= $storeURL . $basket->getProductURL($item->product_id) ?>" class="add-to-basket">Choose item</a>
						<? } ?>
					<? } ?>
					</div>
					
				<? }else{ ?>
					<div class="productImage productImageSmall" style="background:url(/silo/store/<?= $item->product_id ?>/<?= $mainImg ?>_sm.jpg) no-repeat 50% 50%">
						Alt text!
						<a href="<?= $productLink ?>" class="magnify">View '<?= $item->title?>' details</a>
					</div>
					
					<div class="productInfo">
						<h3><a href="<?= $productLink ?>" title="View '<?= $item->title?>' details"><?= $item->title?></a></h3>
						<p class="variants"><?= $varTxt ?></p>
						<p class="price"><strong><?= str_replace('from ','',$price) ?></strong><? if( $item->physical==1 ){ ?> + P&amp;P<? } ?></p>
					</div>
					<p class="short_desc">Category: <?= $breadcrumb ?><br /><?= $item->short_desc ?></p>

					<? if( $item->stock_level<=$store->config['sold_out'] ){ ?>
						<p class="stock_level">Sold out.  Awaiting more stock</p>
					<? }else{ ?>
						<? if($totalVars==0){ ?>
							<a href="/shopping-basket?id=<?= $item->item_id ?>&amp;quantity=1" class="add-to-basket">Add to basket</a>
						<? }else{ ?>
							<a href="<?= $storeURL . $basket->getProductURL($item->product_id) ?>" class="add-to-basket">Choose item</a>
						<? } ?>
					<? } ?>
					
				<? } ?>
			</li>
			<?
			// ends wrapping div...
			$productCount = count($products)-1;
			//echo 'i='. $i .' and pCount: '. $productCount.'<br />';
			if( $productCount<3 ){
				$tmp = array($productCount);
			}else if( $productCount<6 ){
				$tmp = array(3,$productCount);
			}else if( $productCount<9 ){
				$tmp = array(3,6,$productCount);
			}else{
				$tmp = array(3,6,$productCount);
			}
			if( ( $i==1 && !$resetPriority ) || (in_array($i,$tmp)) ){
				echo '</div>'."\n\t";
			}
			?>
			<? 	if( $i==1 && !$resetPriority ){ $resetPriority=true; }else{ $resetPriority=false; }
			$i++;
			} ?>
		</ol>
		<?
			if( $itemcount > $perPage ){
				echo drawPagination('/shop/'.$url.'?ftype='. $filter .'&amp;fvalue='. $filtervalue .'&amp;filetype='. $filetype.'&amp;view='.$view,$filecount,$perPage,$currentPage);
			}
		}		
		?>
    </div>
    <div id="secondarycontent">
		<? if( $product ){ 
		
			$priceRange = explode(',',$product->price_range);
			if( $priceRange[0]!=$priceRange[1] ){
				$price = ' from &pound;'. $priceRange[0] .' to &pound;'. $priceRange[1];
			}else{
				$price = ': &pound;'. $product->price;
			}
		?>
		<div id="placeOrder">
			<h4>Place an order</h4>
			<p id="itemDetails"><?= $product->title ?><br />Price<?= $price ?></p>
			<? 	if( $itemCC = $basket->drawCurrencyConversion($product->price,'min') ){
					$totalCurrencies = count($itemCC);
					$i=1;
				echo '<ul id="itemCC">'."\n";
					foreach( $itemCC as $c ){
						echo "\t".'<li'. ($i==3 ? ' class="last"' :'') .' title="'. $c['title'] .'">'. $c['symbol'] . $c['value'] .'</li>'."\n";
						$i++;
					}
				echo '</ul>'."\n\n";	
				} 
			?>
			<? if( $product->stock_level<=$store->config['sold_out'] ){ ?>
				<p class="stock_level">Sold out.  Awaiting more stock</p>
			<? }else{ ?>
				<? if( is_array($variants) && $totalVars<=1){ ?>
					<a href="/shopping-basket?id=<?= $product->item_id ?>&amp;quantity=1" id="addToBasket">Add to basket</a>
				<? }else{ ?>
					<!-- <?= $store->config['sold_out'] ?> -->
					<form action="/shopping-basket" method="get" id="product-options">
						<fieldset>
						<label for="id" id="labelID">Choose item</label>
						<label for="quantity" id="labelQuantity">Quantity</label>
						<select name="id" id="id">
						<? foreach( $variants as $v ){
							echo '<option value="'. $v->item_id .'"'. ($v->stock_level<=$store->config['sold_out'] ? ' disabled="disabled"' : '') .'>'.$v->title;
							if( $priceRange[0]!=$priceRange[1] ){ echo '(&pound;'.$v->price.')'; }
							if( $v->stock_level<=$store->config['sold_out'] ){ echo ' <strong style="color:red">SOLD OUT!</strong>'; }
							echo '</option>'.$v->stock_level;
						}
						$quantities = array(1,2,3,4,5,6,7,8,9,10);
						?>
						</select>
						<select name="quantity" id="quantity">
						<? foreach( $quantities as $q ){ ?>
							<option><?= $q ?></option>
						<? } ?>
						</select>
						<button type="submit" id="addToBasket">Add to basket</button>
						</fieldset>
					</form>
				<? } ?>
			<? } ?>	

		</div>
	</div>
		<? } ?>
		

	
	<? if( $product ){ ?>
	<div id="productPanels">

		<div id="aboutPanel" class="panel">
			<h4>About this product</h4>
			<ul id="aboutTabs">
				<li id="tab1"><a>Product Info</a></li>
				<li id="tab2"><a href="">Care Info</a></li>
			</ul>
			<div id="abouttab1">
				<p><?= nl2br($product->about_tab1) ?></p>
			</div>
			<div id="abouttab2">
				<p><?= nl2br($product->about_tab2) ?></p>
			</div>
		</div>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.tools.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.delivery.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.security.php') ?>
	<? }else{ ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.storesearch.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.categories.php') ?>
	
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.security.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.delivery.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.tools.php') ?>
	
	<? } ?>		

		
	<? if( $product ){ ?></div><? } ?>
	

		<?
		if( $product ){
		// show related products...
		$i=0;
		if( $related = $store->getSimilarProducts($product->product_id) ){ ?>
		<div id="relatedProductHolder">
			<h3>Similar products</h3>
			<ul id="relatedProducts">
			<?	foreach( $related as $ritem ){ 	
					$rProductLink = $storeURL . $basket->getProductURL($ritem->product_id);
					$images = explode(',',$ritem->images);
					$j=1;
					foreach($images as $key => $value){
						$line = explode('::',$value);
						if( $j==1 ){
							$mainImg = $line[0];
						}
						$j++;
					}
			?>
				<li<?= ($i==$totalRelated ? ' class="last"' :'') ?>>
					<? // product images... 
						//$imgSrc = 'c:\\Webserver\\xampp\\htdocs\\magdev\\silo\\store\\';
						$imgSrc = $_SERVER['DOCUMENT_ROOT'] .'/silo/store/';
						if( file_exists($imgSrc. $ritem->product_id .'/'. $mainImg .'.jpg') ){
							$showImage = ' style="background:url(/silo/store/'. $ritem->product_id .'/'. $mainImg .'_vsm.jpg) no-repeat 50% 50%"';
						} else{
							$showImage = '';
						} 
					?>
					<h4>
						<a href="<?= $rProductLink ?>" title="View '<?= $ritem->title?>' details"><?= $ritem->title?></a>
					</h4>
					<div id="image<?= $i ?>" class="productImage productImageVerySmall"<?= $showImage ?>>Alt text!</div>
					<p class="price">&pound;<?= $ritem->price ?></p>
				</li>	
			<?	$i++; } ?>
			</ul>
			<?= substr($productLink,0,strpos($productLink,'?')-1) ?>
			<a href="<?= $target ?>">See more similar products</a>
		</div>
		<? } } ?>

	<? if( !$product ){ ?></div><? } ?>
  
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>

 <?php /* TINY MCE */ 
 if($mode == 'edit'){	?>
	 <script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_content.js"></script>
 <?php 
 }
 ?>
