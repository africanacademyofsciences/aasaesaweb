<?php


	if (!$site->getConfig('setup_store')) redirect("/?msg=the store is not enabled");
	
	$tags = new Tags();

	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));

	$search_term = read($_REQUEST, 'ssk', false); 		// ssk = store search keywords	
	
	$search_category = read($_REQUEST,'ssc',false); 	// ssc = store search category - uses category name	
	
	$filter = read($_GET,'ftype',false);			// filter results by... from GET
	$filtervalue = read($_GET,'fvalue',false);		// value of the filter (can be asc/desc or a specific value)
	$currentPage = read($_GET,'page',1);			// page as passed by the pagination code
	$thisPage = $currentPage-1;						// current page	
	$perPage = 5;			// results to show per page - could be set by user

	// Page specific options
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('2colR','../store/style/store','../store/style/store_panels'); // all attached stylesheets
	$extraCSS = '';
	
	$js = array('jquery','page_functions'); // all atatched JS behaviours
	$extraJS = ''; // etxra page specific  JS behaviours
	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');

	$products = $store->search($search_term, $search_category, $filter, $filtervalue, ($thisPage*$perPage), $perPage);
	$itemcount = $store->total;

	if($itemcount>1) $search_result = 'Showing results '.((($currentPage-1)*$perPage)+1).' to '.( $itemcount<($currentPage*$perPage) ? $itemcount : ($currentPage*$perPage)).' of '.($itemcount);
	else if ($itemcount==1) $search_result = "Showing 1 item found";
	else $search_result = 'No results could be found for you search, please try again';

?>	

<div id="midholder">
    
    <div id="contentholder">
		<h1 class="pagetitle flir">Search the store</h1>
    
        <div id="primarycontent">

			<h2><?=$search_result?></h2>

<?php 
if( $products ) { 
	?>
	<ul id="serps">
	<?php
	$i=1;
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
			<? if( file_exists($_SERVER['DOCUMENT_ROOT'] .'/store/images'. $item->product_id .'/'. $mainImg .'_sm.jpg') ){ ?>							
				<div id="image" class="productImage productImageSmall" style="background:url(/silo/store/<?= $item->product_id ?>/<?= $mainImg ?>_sm.jpg) no-repeat 50% 50%">Alt text!<a class="magnify" href="<?= $productLink ?>">Click to view large image</a></div>
			<? } ?>
			<!-- <p class="categories"><?= $breadcrumb ?></p> -->
			<p class="tagline"><?=($item->short_desc?$item->short_desc:substr($item->long_desc,0, 200))?></p>
            <!-- <p class="variants"><?= $varTxt ?></p> -->
		</li>
		<? 
	} 
	?>
	</ul>
        
	<?php
    if( $itemcount > $perPage ){
		// Get category filter vars 
		$tmp='';
		foreach (array('key-stage', 'subject', 'themes') as $cat) {
			//print "test cat(".$cat.")<br>\n";
			if (is_array($_GET["product_".$cat])) {
				foreach ($_GET["product_".$cat] as $k=>$v) {
					$tmp.="&product_".$cat."[]=".$v;
				}
			}
		}
        echo drawPagination($itemcount,$perPage,$currentPage, $storeURL.'/search/?ssk='. $search_term .'&amp;ssc='. $search_category .'&amp;ftype='. $filter .'&amp;fvalue='. $filtervalue.$tmp);
    }
	?>
    
    
<?php 	
}		
?>

		</div>

        <div id="secondarycontent">
        
		<? include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.checkout.php') ?>
        <? include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.storesearch.php') ?>
        </div>
        
	</div>       
        
<?php
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>
