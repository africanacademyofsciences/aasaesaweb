<?php

	if (!$site->getConfig('setup_store')) redirect("/?msg=the store is not enabled");

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));

	$tags = new Tags();
	
	$filter = read($_GET,'ftype','date_created');			// filter results by... from GET
	$filtervalue = read($_GET,'fvalue','desc');				// value of the filter (can be asc/desc or a specific value)
	$currentPage = read($_GET,'page',1);					// page as passed by the pagination code
	$thisPage = $currentPage-1;								// current page	
	$perPage = ($currentPage==1 && $store->config["store_use_featured"]) ? 10 : 9;											// results to show per page - could be set by user
	$perPage = 6;


	// Page specific options
	//$catID = read($_REQUEST,'cat',false);
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('2col','../store/style/store','../store/style/store_panels', 'lytebox'); 
	/*if($page->style != NULL){
		$css[] = $page->style;
	}*/
	$extraCSS = '';
	
	$js = array('jquery','preloadCssImages','page_functions','lytebox','../store/behaviour/store_equalheightblocks', '../store/behaviour/jquery.fader'); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	$css[]="../store/style/storev2";		
	
	if ($featured = $store->getFeatured($categoryName)) {
		$extraJS .= '
// Variable to store the images we need to set as background
// which also includes some text and url\'s.
var photos = new Array();
		';
		$i=0;
		$numerics = array("first", "second", "third", "fourth", "fifth");
		foreach ($featured as $listing) {
			//print_r($listing);
			$prices = explode(",", $listing->price_range);
			$items =  explode(",", $listing->variants);
			$extraJS .= '
var texts = new Array();
texts["findmore"]="'.$page->drawLabel("sp_sf_findoutmore", "Find out more").'";
texts["additems"] = "Add to cart";
texts["selitem"] = "'.$page->drawLabel("sp_sf_selitem", "Select item").'";
texts["readmore"] = "'.$page->drawLabel("sp_sf_readmore", "Read more...").'";

photos['.$i.'] = new Object();
photos['.$i.'].title = "'.$listing->title.'";
//photos['.$i.'].image = "/store/images/'.$listing->product_id.'/img-'.$listing->product_id.'.'.$listing->img_extension.'";
photos['.$i.'].image = "/store/images/'.$listing->product_id.$listing->image.'";
photos['.$i.'].detail = "'.$site->link.'shop/cat/?product='.$listing->name.'";
photos['.$i.'].basketurl = "'.(count($items)==1?$site->link.'shop/shopping-basket?id='.$items[0].'&quantity=1':"").'";
photos['.$i.'].firstline = "Small Arms and Light Weapons";
photos['.$i.'].price = "'.($prices[1]>$prices[0]?"$".$prices[0]."+":"$".$prices[0]).'";
photos['.$i.'].physical = "'.$listing->physical.'";
photos['.$i.'].item = "'.(count($items)>1?"0":$items[0]).'";
photos['.$i.'].secondline = "'.substr($listing->short_desc, 0, 200).'";
			'; 
			$extraCSS .= '
div#headertxt div#header-'.($numerics[$i]).' {
	display: block;
}				
			';
			$i++;
		}
		$i=0;	// Just in case :o)
	}
	
	
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

<div id="fixed-content" style="visibility: hidden;"> 
</div>
    
<div id="content-container">
    
<div id="contentholder-top"></div>
<div id="contentholder">

	<?php
	if (!$productName) {
		?>
	    <h1 class="pagetitle flirneue"><span>Our supporters' store</h1>
        <?php
	}
	?>

    <div id="primarycontent">

	<?=drawFeedback($feedback, $message)?>
	<!--<pre><?//= print_r($products) ?></pre>-->
    

	<? 
    // **************************************************
    // We have a specific product to show
    if( $productName ) { 
        
        // Did it load correctly?
        if( $product ) { 
    
			//print "product(".print_r($product, true).")<br>\n";
			
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
                    $varTxt.='<option class="'.$class.'" value="'.$var->item_id.'" '.($this_id==0?'disabled="disabled"':'').'>'.$var->tagline.' £'.$var->price.'</option>';
                }
            }
            if ($varTxt) $varSelect = '
				<label for="f_option">Variation</label>
				<select id="f_option" class="store-grey" name="id">'.$varTxt.'</select>
				';
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
            
                
            <?php 
			if ($product->author) { 
				?>
				<h3 class="author">by <?= $product->author?></h3>
				<?php 
			} 
			
			if (!$quantity) $quantity = 1;
			$basketForm = '

                <form class="addtobasket" action="'.$storeURL.'/shopping-basket/">
                <fieldset>
                    <label for="f_qty">Quantity: </label>
					<input type="text" name="quantity" class="store-grey" id="f_qty" value="'.$quantity.'" />
                    '.$varSelect.'
                    <input type="submit" class="submit" value="Add to cart" />
                </fieldset>
                </form>

			';
			?>
                
            <div id="product" class="hlisting">
            
            	<h1 class="pagetitle product-title"><?=$product->title?></h1>
                <?php 
				
				echo $basketForm;
				
				if ($product->image) $mainImg = $product->image;
                if ($mainImg) { 
                    ?>
                    <div class="productImage" id="mainImage" style="background-image: url(/store/images/<?= $product->product_id ?><?= $mainImg ?>)"></div>
                    <?php
                }
                ?>
                
                <div id="productDetail">
                    
                    <?php 
					if( $product->page_guid>'' ) { 
						?>
                        <p class="pagee"><blockquote>Find out more: <a href="<?=$page->drawLinkByGUID($product->page_guid)?>"><?=$page->drawTitleByGUID($product->page_guid)?></a></blockquote></p>
                    	<?php 
					} 
					?>

                </div>
    
                <div id="productDescription">
                	<?php
                    if ($price) { 
                        ?>
                        <p class="price"><strong><?=$price?></strong><?=($product->physical==1?" plus post and packaging":"")?></p>
                        <?php 
                    } 
                    echo $product->long_desc?$product->long_desc:$product->short_desc;
					?>
                </div>
				
                <div id="bottomAdd2Basket"><?=$basketForm?></div>
    
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

		echo $store->drawFeatured($categoryName);
	
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
				
				// Breadcrumb
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
		
				$productLink = $storeURL."/?product=".$item->name;
				//print "Got prod link ($productLink) id(".$item->product_id.")<br>\n";
				/*
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
			   	*/
				$images[0] = $item->image;
				
				// Set up default line ends array of positions to put closing <divs>
				$lineEnds=array();
				$startLoop=0;
				$numRows = 3; $numCols = 2;
				if ($store->config["store_use_featured"] && $currentPage==1) {
					$lineEnds[]=1;
					$startLoop++;
				}
				for($j=1; $j<=$numRows; $j++) {
					$lineEnds[]=$startLoop + ($j*$numCols);
				}
				//print "Lines end at ".print_r($lineEnds, true)."<br>\n";
					
		
				if(in_array(($i-1), $lineEnds) || ($i-1)==0) {
					//echo '<div class="listHolder" id="store_list_'.++$holderCounter.'">'."\n\t";
				}
				
				?>
				<!-- SHOW THIS PRODUCT INFO -->
				<li id="<?=( ($i==1 && $currentPage==1 && $store->config["store_use_featured"])?'featured':'prod'.$i)?>" class="prod-<?=($i%$numCols)?> productInfo<?=(in_array($i, $lineEnds)?' last':'')?>" >
				
					<?php
					if ($item->image) $image = $item->product_id.$item->image;
					else $image = "layout/prod-default.png";
					?>
					<div class="productImage productImageSmall">
						<a href="<?= $productLink ?>" class="magnify"><img src="/store/images/<?=$image?>" /></a>
					</div>
					
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
                    <p class="short_desc"><?= $item->short_desc ?></p>
    
                    <? 
					if( $item->stock_level<=$store->config['sold_out'] ){ 
						?>
                        <p class="stock_level">Sold out.  Awaiting more stock</p>
	                    <? 
					} 
                    ?>
                    <p class="more"><a href="<?=$productLink?>" class="add-to-basket">More information</a></p>
                    </div>

				</li>
				<?php
				
				
				//print "i($i) tmp(".print_r($lineEnds, true).") count($productCount)<br>\n";           
				if( in_array($i,$lineEnds) || $i==$productCount ){
					//echo '</div>'."\n\t";
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

</div>
<div id="contentholder-bottom"></div>
</div>


<div id="secondarycontent">
	<?php
	include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.shopping-basket.php'); 
	include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.legal.php');
	//include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.delivery.php'); 
    ?>
</div>
    
                

<? 

include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 

?>