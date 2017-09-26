<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

	// Make sure access is allowed to the store configuration
	if (!$site->getConfig('setup_store')) {
		redirect("/treeline/?msg=store is not configured for this website");
	}

	include($_SERVER['DOCUMENT_ROOT'] . "/treeline/store/includes/store.class.php");
	$store = new Store();

	$guid = read($_REQUEST,'guid','');
		
	$message = read($_REQUEST,'message','');
	$feedback = read($_REQUEST,'feedback','');
	
	$eventId = read($_REQUEST,'id',NULL);
	$action = read($_REQUEST,'action',NULL);
	$search = read($_REQUEST,'q',NULL);
	$status = read($_REQUEST,'status','all');
	$dateType = read($_REQUEST,'date','all');
	$orderBy = read($_REQUEST,'sort',NULL); // sort query/results
	$currentPage = read($_REQUEST,'page',1); // pagination value
	$thisPage = $currentPage-1;
	$perPage = 20;
	
	$orderID = read($_REQUEST,'orderID',false);
	$status = array('cart', 'pending', 'despatched', 'completed');
	$totalOrders = 0;
	
	
	if( isset($_POST) && $_POST ){
		//echo '<pre>'. print_r($_POST,true) .'</pre>';
		extract($_POST);
		
		if( !$orderID ){
			// prepare search filter
			
			if( $ordID>0 ){ // search for an order
				$filterBy = 'order';
				$filterValue = $ordID;
				$filterOrder = 'asc';
			}else if( $custName>'' ){
				$filterBy = 'name';
				$filterValue = $custName;
				$filterOrder = 'asc';
			}else if( $filterStatus>'' ){
				$filterBy = 'status';
				$filterValue = $filterStatus;
				$filterOrder = 'asc';
			}
		}
		
		
	// Change order status
		if( $saveStatus ){
			if( isset($orderStatus) && is_numeric($orderStatus) ){
				if( in_array($orderStatus,array(2,3)) ){
					$dateUpdate = 'date_order_completed';
				}
				$query = "UPDATE store_orders SET `status`=$orderStatus, $dateUpdate=NOW() WHERE order_id='$orderID'";
				//echo $query .'<br />';
				$db->query($query);
				if( $db->affected_rows>=0 ){
					$feedback = 'success';
					$message = 'The order status has changed';
				}
			}
		}
	}
	
	
	
	
	
	
	// PAGE specific HTML settings
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = '
	
	div#orderHeader {
		float:left;
		margin-bottom:20px;
		padding-bottom:10px;
		width:100%;
	}
		div#orderHeader h3 {
			font-size:150%;
			padding:0;
		}
		
		div#orderHeader div#orderDetails {
			float:left;
			width:48%;
		}
		
			div#orderDetails form {
				background:0;
				border:none;
				margin:0;
				padding:0;
			}
			
				div#orderDetails form select {
					width:80px;
				}
				
				div#orderDetails form button {
					clear:none;
					float:none;
					font-size:90%;
					padding:2px 3px;
					width:auto;
				}

			div#orderDetails p#giftaid {
				background:#fff;
				border:1px solid #aad;
				margin-top:10px;
				padding:3px;
			}
			
				div#orderDetails p#giftaid strong {
					/*background:url(/images/Gift_Aid_40mm_black.jpg) no-repeat -15px -12px;
					display:block;
					float:left;
					height:100px;
					text-indent:-2000px;
					width:200px;*/
				}
				
		div#orderHeader div#memberDetails {
			float:right;
			width:48%;
		}
	
	table.treeline {
		margin-bottom:30px;
	}

	table#grand_total,
	table#grand_total td {
		color:#666;
		border-color:#333;
		font-size:120%;
	}
	table#grand_total td.totals {
		text-align:right;
	}
	
	div#orderNote {
		background:#efefef;
		border:1px dashed #ccc;
		padding:5px;
	}
		div#orderNote h3 {
			padding:0;
		}
	
	'; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	// Page title	
	$pageTitleH2 = ($action) ? 'Store Orders: '.ucwords($action) : 'Store Orders';
	$pageTitle = ($action) ? 'Store Orders: '.ucwords($action) : 'Store Orders';
	
	$pageClass = 'store_orders';
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
?>

<div id="primarycontent">
    <div id="primary_inner">
	<?=drawFeedback($feedback,$message)?>
				
	<? 
	// ------------------------------------------------------------------
	// No order show all / search
    if( !$orderID ){ 
		// getOrders($from=0, $to=20, $filterBy=false, $filterOrder='asc', $filterValue=false)
		$orders = $store->getOrders(($thisPage*$perPage),$perPage, $filterBy, $filterOrder, $filterValue);
    	$page_html = '
        <form action="" method="post">
            <fieldset>
                <legend>Find an order</legend>
				<p class="instructions">To filter orders, use the search form below'.($totalOrders>0?' or <a href="/treeline/store/orders.php?action=download">download all orders as a CSV list</a>':"").'</p>
                <label for="ordID">Order reference</label>
                <input type="text" name="ordID" id="ordID" value="'.$ordID.'" />
                <label for="custName">Customer Name</label>
                <input type="text" name="custName" id="custName" value="'.$custName.'" />
                <label for="filterStatus">Order Status</label>
                <select name="filterStatus" id="filterStatus">
                    <option value="">-- all --</option>
		';
		$tmp = array_shift($status);
		foreach($status as $key=>$value){ 
			$page_html.='
			<option value="'.($key+1).'"'.($filterStatus==($key+1)?' selected="selected"':'').'>'.ucfirst($value).'</option>
			';
		} 
		$page_html.='
                </select>
				<label for="f_submit" style="visibility:hidden;">Sumbit</label>
                <input type="submit" name="useFilter" class="submit" value="Search" />
            </fieldset>
        </form>			
		';
		echo treelineBox($page_html, "Find an order", "blue");
		
		if( $totalOrders>0 ){ 

			// Show downloadable CSV listing of orders.
			if ($_GET['action']=="download" && $_SERVER['REQUEST_METHOD']=="GET") {
				//ini_set("display_errors", 1);
				//error_reporting(E_ALL);
				include_once ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/csv.class.php");
				$query = "SELECT * FROM store_orders WHERE `status`=1";
				$query = "SELECT 
					so.order_id AS order_id,
					so.member_id AS member_id,
					so.date_cart_started AS date,
					so.`status` AS `status`, 
					CONCAT(m.firstname, ' ', m.surname) AS customer_name, 
					(
						SELECT sum(sod.quantity*si.price) 
						FROM store_orders_details sod
						LEFT JOIN store_inventory si ON sod.item_id=si.item_id
						WHERE sod.order_id=so.order_id
					) AS products_total,
					IF (sodon.donation_amount IS NULL,0,sodon.donation_amount) AS donation_total,
					sodon.use_gift_aid AS gift_aid,
					(
						SELECT IF(sosp.amount IS NULL,0,sum(sosp.amount)) 
						FROM store_orders_sponsorships sosp 
						WHERE so.order_id=sosp.order_id
					) AS sponsorship_total,
					(
						SELECT SUM(e.price)
						FROM events e 
						LEFT JOIN event_entry ee ON e.guid=ee.event_guid
						LEFT JOIN store_orders_events soe ON soe.entry_id=ee.id 
						WHERE soe.order_id=so.order_id
						AND e.price>0
					) AS events_total,
					(
						SELECT count(*) FROM newsletter_user_preferences nup 
						WHERE nup.member_id = so.member_id
					) AS subscriptions
					FROM store_orders so
					LEFT JOIN members m ON so.member_id=m.member_id
					LEFT OUTER JOIN store_orders_donations sodon ON so.order_id=sodon.order_id
					LEFT OUTER JOIN store_address_book sab ON so.shipping_addr_id=sab.addr_id
					LEFT OUTER JOIN store_address_book sab2 ON so.billing_addr_id=sab2.addr_id
					LEFT OUTER JOIN store_countries sc ON sab.country_id=sc.country_id
					LEFT OUTER JOIN store_countries sc2 ON sab2.country_id=sc2.country_id
					WHERE so.`status` > 0
					AND so.msv = ".($site->id+0)."
					GROUP BY so.order_id
					ORDER BY so.date_order_started DESC
					";
				$csv = new CSV($query);
				if ($csv->generateCSV()) {
					$count=$csv->getRecordCount();
					print '<p>'.$count.' record'.($count>1?"s":"").' saved to <a href="/silo/tmp/'.$csv->getFilename().'" target="_blank">'.$csv->getFilename().'</a></p>';
				}
				else echo drawFeedback("error", $csv->errmsg);
			}
			else {
				?>
				<table class="treeline">
					<? if( $totalOrders==1 ){ ?>
					<caption>There is one order to display
					<? }else{ ?>
					<caption>List of orders from <em><?= ($thisPage*$perPage)+1?></em> to <em><?= ($totalOrders<$perPage ? $totalOrders : $currentPage*$perPage) ?></em><? if( $totalOrders>$perPage ){ echo ' of <em>'. $totalOrders; }?></em> 
					<? } ?>
					<?= ($filterBy ? '(filtered by <em>'. $filterBy .'</em>)' : '' ) ?></caption>
					<thead>
						<tr>
							<th scope="col">Order ID</th>
							<th scope="col">Date</th>
							<th scope="col">Status</th>
							<th scope="col">Customer's Name</th>
							<th scope="col">Country</th>
							<th scope="col">Total (less P&amp;P)</th>
						</tr>
						<tbody>
						<?
						foreach($orders as $row){
							$total = ($row->products_total + $row->donation_total + $row->sponsorship_total + $row->events_total);
						?>
							<tr>
								<td><a href="?orderID=<?= $row->order_id ?>"><?= $row->order_id ?></a></td>
								<td><?= ($row->date_order_started ? $row->date_order_started : $row->date_cart_started) ?></td>
								<td><?= ucfirst($status[$row->status-1]) ?></td>
								<td><?= ($row->mem_title?$row->mem_title." ":"").$row->customer_name ?></td>
								<td><?= $row->country_title ?></td>
								<td>&pound;<?= number_format($total, 2, ".", "") ?></td>
							</tr>
						<? } ?>
						</tbody>
					</thead>
				</table>
				<?
				if( $totalOrders>$perPage ){
					echo drawPagination($totalOrders,$perPage,$currentPage,'/treeline/store/orders.php');
				}
			}
            
		}
		else { // if we have no results
			echo '<p>There are no orders to display'. ($filterBy ? ' for your search':'') .'</p>';
		}


	}
	// ------------------------------------------------------------------
	// we have an orderID to view/modify 
	else { 

		//echo 'orderID: '. $orderID .'<br />';
		if( $order = $store->getOrderDetails($orderID) ){
			//print_r($order);
			include($_SERVER['DOCUMENT_ROOT'] . "/treeline/store/includes/basket.class.php");
			$basket = new Basket($orderID);

			$page_html='
            <div id="orderHeader">
                <div id="memberDetails">
					';
			if( is_array($basket->basket) && count($basket->basket)>0 ){ 
				$page_html.='
					<h3>Delivery Details</h3>
					'.$order->customer_name.'<br />
					'.($order->telephone>''?'Telephone: '.$order->telephone:'').'
				';
				if( $addr = $basket->getAddress('delivery',$orderID) ){ 
					$page_html.='
					<p>
					'.($addr->house && $addr->street ? $addr->house .' '. $addr->street : $addr->street).'<br />
					'.($addr->address_2 ? $addr->address_2.'<br />' : '').'
					'.($addr->locality ? $addr->locality.'<br />' : '').'
					'.($addr->town_city ? $addr->town_city.'<br />' : '').'
					'.($addr->county ? $addr->county.'<br />' : '').'
					'.($addr->post_code ? $addr->post_code.'<br />' : '').'
					'.($addr->country ? $addr->country.'<br />' : '').'
					'.($order->email ? '<a href="mailto:'.$order->email.'">'.$order->email.'</a>' : '').'
					</p>
					';
					if($addr->country_id>0) { 
						$basket->country=$addr->country_id; 
						$basket->getPostageAndPacking(); 
					} 
				}
			}
			else { 
				$page_html.='
				<h3>Billing Details</h3>
				'.$order->customer_name.'<br />
				'.($order->telephone>'' ? 'Telephone: '.$order->telephone : '').'
				';
				if( $addr = $basket->getAddress('billing',$orderID) ){ 
					$page_html.='
					<p>
					'.($addr->house && $addr->street ? $addr->house .' '. $addr->street : $addr->street).'<br />
					'.($addr->address_2 ? $addr->address_2.'<br />' : '').'
					'.($addr->locality ? $addr->locality.'<br />' : '').'
					'.($addr->town_city ? $addr->town_city.'<br />' : '').'
					'.($addr->county ? $addr->county.'<br />' : '').'
					'.($addr->post_code ? $addr->post_code.'<br />' : '').'
					'.($addr->country ? $addr->country.'<br />' : '').'
					'.($order->email ? '<a href="mailto:'.$order->email.'">'.$order->email.'</a>' : '').'
					</p>
					'; 
				} 
			}
                
			$page_html.='
				</div>
				<div id="orderDetails">
					<form action="" method="post">
					<h3>Order Details for <strong>'.$orderID.'</strong></h3>
					<label for="orderStatus">Current status:</label> 
					<select name="orderStatus" id="orderStatus">
					';
			$tmp = array_shift($status);
			foreach($status as $key=>$value){ 
				$page_html.='
				<option value="'.($key+1).'"'.($order->status==($key+1)?' selected="selected"':'').'>'.ucfirst($value).'</option>
				';
			} 
			$page_html.='
					</select>
					<button type="submit" name="saveStatus" value="1">Save status</button>
					<br />
					Basket started: '.date('H:i \o\n jS F Y',$order->f_date_cart_started).'<br />
					Order placed: '.date('H:i \o\n jS F Y',$order->f_date_order_started).'<br />
					';
					
				if($order->date_order_completed){ 
					?>Order completed: <?= date('H:i \o\n jS F Y',$order->f_date_order_completed) ?><br /><? 
					} 
				
				if( $basket->totals['donation']>0 || $basket->totals['sponsorships']>0 ){
					$page_html.='
					<p id="giftaid"><strong>Gift Aid</strong>: '.( $order->gift_aid==1 ? 'YES' : 'NO' ).'</p>
					';
				} 
				if( $basket->totals['donation']>0 && $basket->donation['donation_message']>'' ) {
					$page_html.='
					<p><strong>Message</strong>: '.$basket->donation['donation_message'].'</p>
					';
				} 
				/*
				if( $basket->totals['donation']>0 && $order->donation_written>'' ){ 
					$page_html.='
					<p><strong>Postal Confirmation</strong>: '.( $order->donation_written==1 ? 'YES' : 'NO' ).'</p>
					';
				} 
				*/
				if( $order->where_seen>'' ){ 
					$page_html.='
					<p><strong>Heard about '.$site->name.'</strong>: '.$order->where_seen.'</p>
					';
				}
			$page_html.=' 
					</form>
                </div>
            </div>
            ';
			echo treelineBox($page_html, "Order details", "blue");
				
			// SHOW PRODUCTS				
			if( is_array($basket->basket) && count($basket->basket)>0 ){ 
				?>
				<table id="shopping-basket" class="treeline">
				<caption>Order Items</caption>
				<thead>
					<th scope="col" class="col_title">Item</th>
					<th scope="col" class="col_desc">Description</th>
					<th scope="col" class="col_var">Details</th>
					<th scope="col" class="col_price">Price per item</th>
					<th scope="col" class="col_quantity">Quantity</th>
					<th scope="col" class="col_quantity">Totals</th>
				</thead>
				<tbody>
				<? foreach( $basket->basket as $row ){ 
					//echo '<pre>'. print_r($row,true) .'</pre>';
					?>
					<tr>
						<td class="col_title"><a href="<?=$site->link?>shop/<?= $basket->getProductURL($row->product_id) ?>&amp;KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width=920" class="thickbox"><?= $row->title ?></a></td>
						<td class="col_desc"><?= $row->tagline //$row->variants ?></td>
						<td class="col_var"><?= $row->variants //$row->variants ?></td>
						<td class="col_price"><?= $basket->currency . number_format($row->price,2) ?></td>
						<td class="col_quantity"><?= $row->quantity ?></td>
						<td><?= $basket->currency .number_format(($row->quantity*$row->price),2) ?></td>
					</tr>
					<? 
				} 
				?>
				<tr>
					<td colspan="5">
						Postage and Packaging
					</td>
					<td><?= $basket->currency . number_format($basket->pandp,2) ?></td>
				</tr>
				</tbody>
				</table>
				<? 
			} 

			// Show donations			
			if( $basket->totals['donation'] ){ 
				?>
                <table class="treeline">
                    <caption>Donation</caption>
                    <thead>
                        <th scope="col" class="col_title">Item</th>
                        <th scope="col" class="col_desc">Description</th>
                        <th scope="col" class="col_price">Price per item</th>
                        <th scope="col" class="col_quantity">Quantity</th>
                        <th scope="col" class="col_quantity">Totals</th>
                    </thead>
                    <tbody>
                    <tr class="goods_total">
                        <td colspan="4">Donation</td>
                        <td class="total_big"><?= $basket->currency ?><?= ($basket->totals['donation']>0 ? $basket->totals['donation'] : 0) ?><?= ($basket->donation['frequency']==1 ? ' <span id="dFreq">(monthly)</span>' :'') ?></td>
                    </tr>
                    </tbody>
                </table>
                <? 
			} 

			// Show events
            if( is_array($basket->events) && count($basket->events) ){ 
				?>
				<table class="treeline">
					<caption>Events Places</caption>
					<thead>
						<th scope="col" class="col_title">Item</th>
						<th scope="col" class="col_desc">Description</th>
						<th scope="col" class="col_price">Price per item</th>
						<th scope="col" class="col_quantity">Quantity</th>
						<th scope="col" class="col_quantity">Totals</th>
					</thead>
					<tbody>
					<? foreach( $basket->events as $event ){ ?>
					<tr>
						<td><a href="<?= $page->drawLinkByGUID($event->guid) ?>?KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width=920" class="thickbox"><?= $event->title ?></a></td>
						<td class="col_desc"><?= $event->start_date ?> - <?= $event->end_date ?></td>
						<td class="col_price"><?= $basket->currency . $event->price ?></td>
						<td class="col_quantity">1</td>
						<td><?= $basket->currency . number_format($event->price, 2, ".", "") ?></td>
					</tr>
					<? } ?>
					<tr class="goods_total">
						<td colspan="4">Sub Total</td>
						<td class="total_big"><?= $basket->currency ?><?= number_format($basket->totals['events'], 2, ".", "") ?></td>
					</tr>
					</tbody>
				</table>
				<? 
            } 
            ?>
					
			<? 
			if( is_array($basket->sponsorships) && count($basket->sponsorships) ){ 
				?>
                <table class="treeline">
                    <caption>Sponsorships</caption>
                    <thead>
                        <th scope="col" class="col_title">Item</th>
                        <th scope="col" class="col_desc">Description</th>
                        <th scope="col" class="col_price">Price per item</th>
                        <th scope="col" class="col_quantity">Quantity</th>
                        <th scope="col" class="col_quantity">Totals</th>
                    </thead>
                    <tbody>
                    <? foreach( $basket->sponsorships as $item ){ ?>
                    <tr>
                        <td colspan="5" class="col_title<?= (($item->event_id==$eventID && $item->member_id==$memberID) ? ' highlight' :'') ?>">
                        <?= ($item->event_id==$eventID && $item->member_id==$memberID) ? '<strong>Just added!</strong><br />' : '' ?>
                            Sponsoring <a href="<?= $page->drawLinkByGUID($item->pp_guid) ?>?KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width=920" class="thickbox"><?= $item->member_name ?></a> on the 
                            <a href="<?= $page->drawLinkByGUID($item->event_id) ?>?KeepThis=true&amp;TB_iframe=true&amp;height=520&amp;width=920" class="thickbox" ><?= $item->event_title ?></a>
                        </td>
                        <td class="col_quantity"><?= $item->amount ?></td>
                    </tr>
                    <? } ?>
                    <tr class="goods_total">
                        <td colspan="5">Sub Total</td>
                        <td class="total_big"><?= $basket->currency ?><?= $basket->totals['sponsorships'] ?></td>
                    </tr>
                    </tbody>
                </table>
				<? 
			} 
			?>
				
            <table class="treeline" id="grand_total">
                <tbody>
                    <tr>
                        <td class="textlabel" colspan="4">Grand Total</td>
                        <td class="col_quantity totals total_big"><?= $basket->currency . number_format($basket->getGrandTotal() + $basket->pandp,2) ?></td>
                    </tr>
                </tbody>
            </table>					
		
			<? 
			if( $order->order_note ){ 
				?>
				<div id="orderNote">
					<h3>Customer's Note</h3>
					<p><?= nl2br($order->order_note) ?></p>
				</div>
				<? 
			} 

		}
		// End of getOrders success 
			
	} 
    ?>

    </div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>