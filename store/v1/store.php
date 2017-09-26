<?php

	if (!$site->getConfig('setup_store')) redirect("/?msg=the store is not enabled");

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));

	$tags = new Tags();
	
	$filter = read($_GET,'ftype','date_created');			// filter results by... from GET
	$filtervalue = read($_GET,'fvalue','desc');				// value of the filter (can be asc/desc or a specific value)
	$currentPage = read($_GET,'page',1);					// page as passed by the pagination code
	$thisPage = $currentPage-1;								// current page	
	$perPage = ($currentPage==1 && $store->config["store_use_featured"]) ? 10 : 9;											// results to show per page - could be set by user


	// Page specific options
	//$catID = read($_REQUEST,'cat',false);
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('2colR','../store/'.$storeVersion.'/style/store','../store/'.$storeVersion.'/style/store_panels', 'lytebox'); 
	/*if($page->style != NULL){
		$css[] = $page->style;
	}*/
	$extraCSS = '';
	
	$js = array('jquery','preloadCssImages','page_functions','lytebox','../store/'.$storeVersion.'/behaviour/store_equalheightblocks'); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	//echo $categoryName.'<br />';
	if( $productName ) $product = $store->loadByName($productName);
	else $products = $store->loadByCat($categoryName,($thisPage*$perPage), $perPage);
	$itemcount = $store->total;

	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php'); 

	if ($product->title) {
		$pagetitle = $product->title;
		$pagetitleex .= '<a href="'.$storeURL.'" class="shop">back to shop home</a>';
	}
	else $pagetitle = "Most recent publications";
?>

<div id="midholder">
    
    <div id="contentholder">
        <h1 class="pagetitle flirneue"><span>Our supporters' store</h1>
    
        <div id="primarycontent">

	<?=drawFeedback($feedback, $message)?>
	<!--<pre><?//= print_r($products) ?></pre>-->
    

<? 
// **************************************************
// We have a specific product to show
if( $productName ) { 
	
	// Did it load correctly?
	if( $product ) { 

		// variants specifics (I'm not using these here but could make nice
		// pretty drop downs out of them?
		$variants = $store->getProductVariantsSelect($product->product_id);
		$totalVars = count($variants);
		$varTxt = '';
		//print "var(".print_r($variants, true).") c($totalVars)<br>\n";
		if( is_array($variants) && $totalVars>1 ){
			foreach( $variants as $var ){
				//print_r($var);
				$class=''; $this_id=$var->item_id;
				//print "title(".$var->title.") stock(".$var->stock_level.") level(".$store->config['sold_out'].")<br>\n";
                if( $var->stock_level<=$store->config['sold_out'] ) {
					$class="sold-out"; 
					$this_id=0;
				}
				$varTxt.='<option class="'.$class.'" value="'.$var->item_id.'" '.($this_id==0?'disabled="disabled"':'').'>'.$var->title.' �'.$var->price.'</option>';
			}
		}
		if ($varTxt) $varSelect = '<label for="f_option">Select item</label>
<select id="f_option" name="id">'.$varTxt.'</select>';
		else $varSelect = '<input type="hidden" name="id" value="'.$product->item_id.'" />';

		//print "got select ($varSelect)<br>\n";
		/*
		// variants overview
		$variants = $store->showProductVariants($product->product_id);
		$totalVars = count($variants);
		//print "var(".print_r($variants, true).") c($totalVars)<br>\n";
		if( is_array($variants) && $totalVars>0 ){
			$varTxt = 'Available in various ';
			$j=1;
			foreach( $variants as $var ){
				$varTxt .= $var . ($j==($totalVars-1) ? ($j<$totalVars ? ' and ' : ', ') : '');
				$j++;
			}
			$varTxt .= '.';
		}
		else $varTxt = '';
		*/
		// breadcrumb
		if( $product->breadcrumb ){
			$breadcrumb = '';
			if( $product->breadcrumb->parent_title ){
				$breadcrumb .= '<a href="'. $storeURL .'/'. $product->breadcrumb->parent_name .'">'. $product->breadcrumb->parent_title .'</a> &raquo; ';
				$breadcrumb .= '<a href="'. $storeURL .'/'. $product->breadcrumb->parent_name .'/'. $product->breadcrumb->name .'">'. $product->breadcrumb->title .'</a>';
			}
			else{
				$breadcrumb .= '<a href="'. $storeURL .'/'. $product->breadcrumb->name .'">'. $product->breadcrumb->title .'</a>';
			}
		}
		
		$priceRange = explode(',',$product->price_range);
		if( $priceRange[0]!=$priceRange[1] ){
			$price = 'from &pound;'. $priceRange[0] .' to &pound;'. $priceRange[1];
		}
		else{
			$price = '&pound;'. $product->price;
		}
			
		// need to find out how many we actually have...should be easy once the db is hooked up to images...
		//print "images (".$product->images.")<br>\n";
		$images = explode(',',$product->images);
		$tmp = array();
		$i=1;
		foreach($images as $key => $value){
			$line = explode('::',$value);
			$img_id = $line[0];
			//print "Got $line[0] = $line[1] = $line[2]<br>\n";
			$tmp[$img_id]['id']=$img_id;
			$tmp[$img_id]['extension']=$line[1];
			$tmp[$img_id]['caption']=$line[2];
			if( $i==1 ) {
				$mainImg = $img_id;
				$mainImgExt = $line[1];
			}
			$i++;
		}
		$images = $tmp;
		//print "images(".print_r($images, true).") main image($mainImg)<br>\n";
		$maxImages = 5;
		?>
		
			
		<?php if ($product->author) { ?>
        <h3 class="author">by <?= $product->author?></h3>
        <?php } ?>
            
        <div id="product" class="hlisting">
        
			<?php 
			if ($mainImg) { 
				?>
                <div class="productImage" id="mainImage" style="background-image: url(/store/<?=$storeVersion?>/images/<?= $product->product_id ?>/<?= $mainImg ?>_large.<?=$mainImgExt?>)">
                    Image for <?=$product->title?>
                    <a class="magnify">Click to view large image</a>
                </div>
                <?php
			}
			?>
            
            <div id="productDetail">
            	<?php if ($price) { ?>
                <p class="price"><strong><?=$price?></strong><?=($product->physical==1?" plus post and packaging":"")?></p>
                <?php } ?>
                
                <?php if( $product->page_guid>'' ) { ?>
					<p class="pagee"><blockquote>Find out more: <a href="<?=$page->drawLinkByGUID($product->page_guid)?>"><?=$page->drawTitleByGUID($product->page_guid)?></a></blockquote></p>
                <?php } ?>

                <?php if ($varTxt) { ?>
                	<p class="variants"><?=$varTxt?></p>
                <?php } ?>
                
                <div id="productImageHolder">
                <? 
                // product images... 
                //for($i=1;$i<$maxImages;$i++){
                if( count($images)>1 ){
                    foreach($images as $img_id => $values){
						//print "Got image k($key) v(".print_r($values, true).")<br>\n";
                        // $loc = 'c:\\Webserver\\xampp\\htdocs\\magdev\\silo\\store\\'. $product->product_id .'\\'. $i .'.jpg';
						if ($img_id != $mainImg) {
							$loc = $_SERVER['DOCUMENT_ROOT'] .'/store/'.$storeVersion.'/images/'. $product->product_id .'/'. $img_id .'.'.$values['extension'];
							if( file_exists($loc) ){
								//print "found for file($loc)<br>\n";
								?>	
                                <div class="smallImageHolder">						
                                    <div id="image<?=$img_id?>" class="productImage productImageSmall" style="background:url(/store/<?=$storeVersion?>/images/<?= $product->product_id ?>/<?= $img_id ?>_thumb.<?=$values['extension']?>) no-repeat 50% 50%">
                                        <a class="magnify" href="/store/<?=$storeVersion?>/images/<?= $product->product_id ?>/<?= $img_id ?>.<?=$values['extension']?>" rel="lytebox[product-<?= $product->product_id ?>]" target="_blank" title="<?= $product->title?>: <?= $values['caption'] ?>">Click to view large image</a>
                                    </div>
                                    <!-- <p class="caption"><?=$values['caption']?></p> -->
                                </div>
								<?
							}
						}
                    }
                }
                ?>
                </div>
                
                <p class="howtoorder">
                <strong>How to order books</strong><br />
				To order this book, us the form below to choose how many copies you want to order. Then click the �Add shopping basket� button. When you have finished shopping, click the �Go to checkout� button.
               	</p>
                
                <h3 id="basketTitle" class="basket-title">Add to shopping basket</h3>
                <form id="addtobasket" action="<?=$storeURL?>/shopping-basket/">
                <fieldset>
                	<?=$varSelect?>
                    <label for="f_qty">Number of copies</label>
                    <select name="quantity" id="f_qty">
                    	<option value="1">Please select</option>
                    	<option>1</option>
                    	<option>2</option>
                    	<option>3</option>
                    	<option>4</option>
                    	<option>5</option>
                    	<option>6</option>
                    	<option>7</option>
                    	<option>8</option>
                    	<option>9</option>
                    	<option>10</option>
                    </select>
                    <input type="submit" class="submit" value="Add to shopping basket" />
                </fieldset>
				</form>
                
            </div>

			<?php if ($product->short_desc || $product->long_desc) { ?>
            <div id="productDescription">
                <h3>About this book</h3>
                <p class="short_desc description"><?=($product->long_desc?$product->long_desc:$product->short_desc)?></p>
            </div>
            <?php } ?>

		</div>			
		<?php
	}
	else { 
		?>
			<p>ERROR!<br />There's no product with that name!</p>
		<? 
	} 
	
}
// **************************************************



// **************************************************
// if we don't have a product name, assume we're looking at the category... 	
else if (is_array($products) && count($products)) { 
	?>		
	<ul id="store_products" start="<?= ($thisPage*$perPage)+1 ?>">
		<?php  
        $i=1;
        $productCount=count($products);
		//print "got $productCount products<br>\n";

        foreach($products as $item) {   
    
            // variant text...
            $variants = $store->showProductVariants($item->product_id);
            $totalVars = count($variants);
			//print "var(".print_r($variants, true).") c($totalVars)<br>\n";
            if( is_array($variants) && $totalVars>0 ){
                $varTxt = 'Available in various ';
                $j=1;
                foreach( $variants as $var ){
                    $varTxt .= $var . ($j==($totalVars-1) ? ($j<$totalVars ? ' and ' : ', ') : '');
                    $j++;
                }
                $varTxt .= '.';
            }
			else $varTxt = '';
            
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
    
            // Setup price/price range text
			$priceRange = explode(',',$item->price_range);
            if( $priceRange[0]!=$priceRange[1] ){
                $price = 'from '.$basket->currency . $priceRange[0] .' to '. $basket->currency . $priceRange[1];
            }
			else $price = $basket->currency . $item->price;
    
            $productLink = $storeURL . $basket->getProductURL($item->product_id);
            
            
            $images = explode(',',$item->images);
			$tmp = array();
            $j=1;
            foreach($images as $key => $value){
				//print "got k($key) = $v($value)<br>\n";
				$line = explode('::',$value);
				//print "Got $line[0] = $line[1] = $line[2]<br>\n";
				$tmp_img_id=$line[0];
				$tmp[$tmp_img_id]['id']=$tmp_img_id;
				$tmp[$tmp_img_id]['extension']=$line[1];
				$tmp[$tmp_img_id]['caption']=$line[2];
				if( $j==1 ) {
					$mainImg = $tmp_img_id;
					$mainImgExt = $line[1];
				}
				$j++;
			}
			$images = $tmp;
			unset($tmp);
           
			// Set up default line ends array of positions to put closing <divs>
			$lineEnds=array();
			$startLoop=0;
			$numRows = 3; $numCols = 3;
			if ($store->config["store_use_featured"] && $currentPage==1) {
				$lineEnds[]=1;
				$startLoop++;
			}
			for($j=1; $j<=$numRows; $j++) {
				$lineEnds[]=$startLoop + ($j*$numCols);
			}
			//print "Lines end at ".print_r($lineEnds, true)."<br>\n";
		        
    
            if(in_array(($i-1), $lineEnds) || ($i-1)==0) {
                echo '<div class="listHolder" id="store_list_'.++$holderCounter.'">'."\n\t";
            }
            
            ?>
            <!-- SHOW THIS PRODUCT INFO -->
            <li id="<?=( ($i==1 && $currentPage==1 && $store->config["store_use_featured"])?'featured':'prod'.$i)?>" class="productInfo<?=(in_array($i, $lineEnds)?' last':'')?>" >
            
                <?php
                // ----------------------------------------------------------
                // Is this a first (bigger image)? 
                if($i==1 && !$resetPriority && $store->config["store_use_featured"]){ 
                    ?>
    
    				<?php
					if ($mainImg) { 
						?>
                        <div class="productImage" style="background:url(/store/<?=$storeVersion?>images/<?= $item->product_id ?>/<?= $mainImg ?>_small.<?=$mainImgExt?>) no-repeat 50% 50%">
                            <a href="<?= $productLink ?>" class="magnify">View '<?= $item->title?>' details</a>
                        </div>
                        <?php
					}
					?>
                    
                    <a href="<?= $productLink ?>" title="View '<?= $item->title?>' details"><?= $item->title?></a>
                    
                    <div class="productDetail">
                        <p class="price">Category: <?= $breadcrumb ?><br /><strong><?= $price ?></strong><? if( $item->physical==1 ){ ?> + P&amp;P<? } ?></p>
                        <p class="short_desc"><?= $item->short_desc ?></p>
                        <p class="variants"><?= $varTxt ?></p>
                    <? 
                    if( $item->stock_level<=$store->config['sold_out'] ){ 
                        ?>
                        <p class="stock_level">Sold out.  Awaiting more stock</p>
                        <? 
                    } 
                    else { 
                        if($totalVars==0){ 
                            ?>
                            <a href="/shopping-basket?id=<?= $item->item_id ?>&amp;quantity=1" class="add-to-basket">Add to basket</a>
                            <? 
                        }
                        else { 
                            ?>
                            <a href="<?= $storeURL . $basket->getProductURL($item->product_id) ?>" class="add-to-basket">Choose item</a>
                            <? 
                        } 
                    } 
                    ?>
                    </div>
                    <?php
                }
                // ----------------------------------------------------------
                // Or just a standard product layout?
                else { 
                    ?>
                    
                    <?php 
					if ($mainImg) { 
						?>
                        <div class="productImage productImageSmall" style="background:#CCC url(/store/<?=$storeVersion?>/images/<?= $item->product_id ?>/<?= $mainImg ?>_small.<?=$mainImgExt?>) no-repeat 50% 50%">
                            <a href="<?= $productLink ?>" class="magnify">View '<?= $item->title?>' details</a>
                        </div>
                      	<?php
					}
					?>
                    
                    <div class="productDetail">
                        <p class="title"><a href="<?= $productLink ?>" title="View '<?= $item->title?>' details"><?= $item->title?></a></p>
                        
                        <?php if ($varTxt) { ?>
                        <p class="variants"><?= $varTxt ?></p>
                        <?php } ?>
                        
                        <?php if ($item->author) { ?>
                        <p class="author"><?=$item->author?></p>
                        <?php } ?>
                    </div>
                    
                    <div class="productPurchase">
                    <p class="price"><strong><?= str_replace('from ','',$price) ?></strong><? if( $item->physical==1 ){ ?> + P&amp;P<? } ?></p>
                    <!-- <p class="short_desc">Category: <?= $breadcrumb ?><br /><?= $item->short_desc ?></p> -->
    
                    <? if( $item->stock_level<=$store->config['sold_out'] ){ ?>
                        <p class="stock_level">Sold out.  Awaiting more stock</p>
                    <? } else { 
                        if($totalVars==0){ 
                            ?><p class="buy"><a href="<?=$storeURL?>/shopping-basket?id=<?= $item->item_id ?>&amp;quantity=1" class="add-to-basket">Add to basket</a></p><? 
                        }
                        else { 
                            ?><p class="more"><a href="<?=$storeURL . $basket->getProductURL($item->product_id) ?>" class="add-to-basket">Choose item</a></p><? 
                        } 
                    } 
                    ?>
                    </div>
                    
                    <?php
                } 
                // ----------------------------------------------------------
                ?>
            </li>
            <?php
            
			
			//print "i($i) tmp(".print_r($lineEnds, true).") count($productCount)<br>\n";           
		    if( in_array($i,$lineEnds) || $i==$productCount ){
                echo '</div>'."\n\t";
            }
            
            if( $i==1 && !$resetPriority && $store->config["store_use_featured"]) $resetPriority=true; 
            else $resetPriority=false; 
            $i++;
        } 
        ?>
	</ul>
	
	<?php
	if( $itemcount > $perPage ){
		echo drawPagination($store->total, $perPage, $currentPage, $storeURL.'?ftype='. $filter .'&amp;fvalue='. $filtervalue .'&amp;filetype='. $filetype.'&amp;view='.$view);
	}
}
// Failed to load the relevant catalogue
else {
	//print "object(".is_array($products).")<br>\n";
	//print "count(".count($products).")<br>\n";
	//print "prods(".print_r($products, true).")<br>\n";
	?>
	<p>No products are set up in this store</p>
	<?php
}		
// **************************************************
?>
    
    
        </div>
    
        <div id="secondarycontent">
        
        
        <? include($_SERVER['DOCUMENT_ROOT'] .'/store/'.$storeVersion.'/snippets/panel.basket.php') ?>
        <? include($_SERVER['DOCUMENT_ROOT'] .'/store/'.$storeVersion.'/snippets/panel.storesearch.php') ?>
        <? include($_SERVER['DOCUMENT_ROOT'] .'/store/'.$storeVersion.'/snippets/panel.delivery.php') ?>
        
        
        </div>
        
    </div>

					
	<script type="text/javascript">
        getHeights();
    </script>


<? 

include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 

?>