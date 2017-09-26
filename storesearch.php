<?php

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
	$search_term = read($_REQUEST,'ssk',false); 		// ssk = store search keywords	
	$search_category = read($_REQUEST,'ssc',false); 	// ssc = store search category - uses category name	
	
	$filter = read($_GET,'ftype',false);			// filter results by... from GET
	$filtervalue = read($_GET,'fvalue',false);		// value of the filter (can be asc/desc or a specific value)
	$currentPage = read($_GET,'page',1);			// page as passed by the pagination code
	$thisPage = $currentPage-1;						// current page	
	$perPage = 5;			// results to show per page - could be set by user

	// Page specific options
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('2col_right','forms','store','store_panels'); // all attached stylesheets
	$extraCSS = '
	
		ol#product_search {
			margin:0;
			padding:0;
		}
		
			ol#product_search li {
				border-bottom:1px solid #dfdfdf;
				font-family:Georgia;
				font-size:140%;
				font-weight:bold;
				list-style:decimal none outside;
				margin-bottom:10px;
				padding-bottom:15px;
				padding-right:100px;
				position:relative;
			}
			
				ol#product_search li h4 {
					font-size:100%;
					padding-bottom:2px;
				}
				
				ol#product_search li p{
					font-size:70%;
					font-weight:normal;
					padding:0 0 0 20px;
				}

					ol#product_search li p span {
						font-size:80%;
					}

				ol#product_search li div.productImage{
					position:absolute;
					right:0;
					top:0;
				}


	';
	
	$js = array('jquery','store','page_functions'); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');

	if( $search_term ){ 
		$products = $store->search($search_term, $search_category, $filter, $filtervalue, ($thisPage*$perPage), $perPage);
	}
	$itemcount = $store->total;

?>	

	<h1>Store</h1>
    <div id="primarycontent">
		<!--<pre><?//= print_r($products,true) ?></pre>-->
		<h2>Search Results</h2>
		<? if( $itemcount>0 ){ ?>
		<p>Showing <?= ($thisPage*$perPage)+1 ?> to <?= ( $itemcount<($currentPage*$perPage) ? $itemcount : ($currentPage*$perPage)) ?> of <?= ( $itemcount>$perPage ? $itemcount : $perPage) ?></p>
		<? }else{ ?>
		<p>No results could be found for you search, please try again</p>
		<? } ?>
		<? if( $products ){ ?>
		
		<ol start="<?= ($thisPage*$perPage)+1 ?>" id="product_search">
		<?  $i=1;
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

		?>
			<li>
				<h4><a href="<?= $productLink ?>"><?= $item->title ?></a></h4>							
					<? if( file_exists($_SERVER['DOCUMENT_ROOT'] .'/silo/store/'. $item->product_id .'/'. $mainImg .'_sm.jpg') ){ ?>							
						<div id="image" class="productImage productImageSmall" style="background:url(/silo/store/<?= $item->product_id ?>/<?= $mainImg ?>_sm.jpg) no-repeat 50% 50%">Alt text!<a class="magnify" href="<?= $productLink ?>">Click to view large image</a></div>
					<? } ?>
				<p class="categories"><?= $breadcrumb ?></p>
				<p class="tagline"><?= $item->short_desc ?><br /><span class="variants"><?= $varTxt ?></span></p>
			</li>
			<? } ?>
		</ol>
		<?
			if( $itemcount > $perPage ){
				echo drawPagination($itemcount,$perPage,$currentPage, '/shop/search/?ssk='. $search_term .'&amp;ssc='. $search_category .'&amp;ftype='. $filter .'&amp;fvalue='. $filtervalue);
			}
		}		
		?>
    </div>
    <div id="secondarycontent">
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.storesearch.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.categories.php') ?>
	
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.security.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.delivery.php') ?>
		
		<? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.tools.php') ?>
	
	</div>
  
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>

 <?php /* TINY MCE */ 
 if($mode == 'edit'){	?>
	 <script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_content.js"></script>
 <?php 
 }
 ?>