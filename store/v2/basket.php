<?php


	if (!$site->getConfig('setup_store')) redirect("/?msg=the store is not enabled");
	
	$referer = urldecode(read($_REQUEST,'referer','/treeline/'));
	$mode = read($_REQUEST,'mode','basket');

	// adding to basket?
	$productID = read($_POST?$_POST:$_GET,'id',false);
	$productQuantity = read($_POST?$_POST:$_GET,'quantity',false);
	$productType = read($_POST?$_POST:$_GET, 'type', '');

	$campaign = read($_GET,'campaign','');

	// Make sure no comma's in donations
	if ($_POST && $_POST['donation_value']) $_POST['donation_value']=str_replace(",","",$_POST['donation_value']);
	
	// If we have arrived here from an event booking link we need to create find or create a new entry_ID
	if ($productType=="event" && $productQuantity>0) {
		$eguid=read($_POST?$_POST:$_GET,'eguid','');
		$query = "SELECT template FROM pages WHERE guid='$eguid'";
		$pagetemplate = $db->get_var($query);
		if (!$productID && $eguid && $pagetemplate==19) {
			// Check if we already have an entry ID for this event
			$query = "SELECT entry_id FROM event_entry ee 
				LEFT JOIN store_orders_events soe ON soe.entry_id = ee.id
				WHERE soe.order_id = '".$basket->cartID."'
				AND ee.event_guid='$eguid'
				LIMIT 1
				";
			$productID = $db->get_var($query);
			if (!$productID) {
				$query="INSERT INTO event_entry 
					(member_id, event_guid, registered, added)
					VALUES 
					(".($_SESSION['member_id']+0).", '".$eguid."', 0, NOW())
					";
				//$message[] = $query;
				if ($db->query($query)) $productID = $db->insert_id;
			}
		}
		else $message[]="Failed to add this event to your shopping basket, it does not appear to be a valid event";
		//print "got productID($productID)<br>\n";
	}
	
	if( isset($productID) && is_numeric($productID) ){
		//$message[] = "got id($productID) q($productQuantity)";
		$quantity = (isset($productQuantity) && is_numeric($productQuantity)) ? $productQuantity : 1;
		//print "add($productID, $quantity)<br>\n";
		if( $basket->add($productID, $quantity, $productType) ){
			if (!count($message)) {
				$feedback = 'success';
				$message[] = 'Your shopping basket has been updated';
			}
		}
	}

	$tags = new Tags($site->id, 1);
	

	// New style donations system redirects straight to the checkout process
	if ($_GET['f'] && 
		($_GET['a']>0 || $_GET['o']>0) 
		) {	
		//print "Update the basket(".$_GET['f'].") a(".$_GET['a'].") o(".$_GET['o'].")...<br>\n";
		
		// donations
		$dTmp = array();
		$dTmp['donation_message']='';
		$dTmp['donation_written']='';
		if ($_GET['a']>0) {
			$dTmp['type'] = 'amount_id';
			$dTmp['value'] = $_GET['a'];
		}
		else if ($_GET['o']>0) {
			$dTmp['type'] = 'value';
			$dTmp['value'] = $_GET['o'];
		}
		$dTmp['frequency'] = $_GET['f']=="m"?1:0;
		$dTmp['gift_aid'] = 0;

		if( $basket->update('',$dTmp,'','','') ){
			//print "redirect<br>\n";
			redirect($storeURL.'/checkout/');
			exit();
		}
		else print "No update<br>\n";
	}
	
	if( isset($_POST) && $_POST ) {
		
		// Form Processing
		if( isset($_POST['shipping_zone']) ){
			$basket->country = $_POST['shipping_zone'];
			$_SESSION['shipping_zone']=$basket->country;
		}
		
		//echo '<pre style="color:#fff">'. print_r($_POST,true) .'</pre>';
		//exit();

		$tmp = array(); 	// hold our items and quantities
		$eTmp = array(); 	// events
		$sTmp = array(); 	// sponsorships
		$iTmp = array();	// Info (order details)
		
		foreach($_POST as $key => $value){
		
			if( substr_count($key,'quantity')==1 && $value>0 ) {
				$id = substr($key,strrpos($key,'_')+1);
				$itemStock = $store->checkStockLevel($id);
				if( $value > $itemStock ){
					$value = $itemStock;
					$feedback = 'error';
					$message[] = 'You were attempting to order more of a product than is currently available';
				}
				$tmp[] = array('item_id'=>$id,'quantity'=>$value);
			}
			//if( substr_count($key,'eventqty')==1 && $value>0) {
			if( substr_count($key,'eventqty')==1) {
				$entry_id = substr($key,strrpos($key,'_')+1);
				//print "check evt qty($key => $value) entry_id($entry_id)<br>\n";
				$itemStock = $store->checkEventCapacity($entry_id);
				//print "event stock($itemStock) for id($id)<br>\n";
				if( $value > $itemStock ){
					$value = $itemStock;
					$message[] = 'You were attempting to order tickets to an event than are currently available';
				}
				$eTmp[] = array('item_id'=>$entry_id, 'quantity'=>$value);
				//print "set eTmp to(".print_r($eTmp, true).")<br>\n";
			}
			else if( substr($key,0,3)=='sp_' ){
				if( $value>0 ){
					$thisKey = substr($key,3);
					$sTmp[$thisKey] = $value;
				}
			}
			else if( substr($key,0,6)=='event_' ){
				$thisKey = substr($key,6);
				$eTmp[$thisKey] = $value;				
			}
			else if (substr($key,0,6)=="store_") {
				$thisKey = substr($key,6);
				$iTmp[$thisKey] = $value;
			}
			
		}
		
		// donations
		$dTmp = array();
		$dTmp['donation_message']=$_POST['donation_message'];
		$dTmp['donation_written']=$_POST['donation_written'];
		if( $_POST['donation_value']>0 || $_POST['donation_amount_id']>0 ){
			if( $_POST['donation_value']>0 ){
				$dTmp['type'] = 'value';
				$dTmp['value'] = $_POST['donation_value'];
			}else{
				$dTmp['type'] = 'amount_id';
				$dTmp['value'] = $_POST['donation_amount_id'];		
			}
			if( $dTmp['value']<=0 ){
				$dTmp['type'] == 'value';
				$dTmp['value'] = 0;
			}
			$dTmp['frequency'] = (isset($_POST['donation_frequency']) && $dTmp['value']>0) ? 1 : 0 ;
			$dTmp['gift_aid'] = (isset($_POST['donation_gift_aid']) && $dTmp['value']>0) ? 1 : 0 ;
		}
		if( $_POST['removeDonation']>'' ){
			$dTmp = array();
		}
		
		//print "Update(tmp, dTmp, eTmp, sTmp, iTmp)<br>\n";
		if( $basket->update($tmp,$dTmp,$eTmp,$sTmp,$iTmp) ){
			//print "Updated<br>\n";
			if( !$message ){
				$feedback = 'success';
				$message[] = 'Your shopping basket has been updated';
				
				if ($_SERVER['REQUEST_METHOD']=="POST" && $_POST['fwd']) redirect($_POST['fwd']);
			}
		}
		else {
			//echo 'NOT UPDATED!';
		}
		
		if( isset($_POST['checkout']) && $_POST['checkout']>'' ){
			if( $basket->getGrandTotal()>0 ){
				redirect($storeURL.'/checkout/?subscribe='.($_POST['news_add_email']?"1":"0"));
			}
			else{
				$feedback = 'error';
				$message[] = 'You cannot proceed to the checkout with nothing in your shopping basket';
			}
		}
		//echo '</div>';
	}
	
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('1col', 'thanks', '../store/style/store','../store/style/basket','../store/style/store_panels'); // all attached stylesheets
	//if($page->style != NULL) $css[]=$page->style;
	
	$extraCSS = '';
	
	$js = array(); // all atatched JS behaviours
	$extraJS = '
	
function recalculate(empty) {
	
	//alert("recalc");
	recal_but = document.getElementById("recalc-donation");
	//alert(recal_but);
	donat_txt = document.getElementById("donate-amt");
	if (empty) {
		donat_txt.value = "";
	}
	recal_but.click();

}

'; // etxra page specific  JS behaviours

	if ($_GET['donation']) {
		$extraJSbottom .= '

donat_txt = document.getElementById("donate-amt");
donat_txt.value = '.$_GET['donation'].';
recalculate(false);

		
';
	}

	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
?>	


<div id="fixed-content" style="visibility: hidden;"> 
</div>
    
<div id="contentholder-top"></div>
<div id="contentholder">

	<?php
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
	?>

    <h1 class="pagetitle"><?=($mode=='checkout' ? 'Checkout' : $page->getTitle()) ?></h1>
    
    <div id="primarycontent">

        <div id="red-box-top"></div>
        <div id="red-box">
    
            <!--//<pre><?//= print_r($_POST,true) ?></pre>
            <pre><?//= print_r($_COOKIE,true) ?></pre>//-->
		    <h2 class="pagetitle">Confirm or edit your order details before going to checkout</h2>
        
            <div id="white-box-top"></div>
            <div id="white-box">
    
				<?=drawFeedback($feedback, $message)?>
                <div id="basket">
                        
                    <? // SHOPPING BASKET ?>			
                    <form action="<?=$request?>" class="std-form" method="post" id="shopping-basket">
            
                        <? 
                        // --------------------------------------------------------------
                        // SHOW ALL PURCHASES FROM THE STORE
                        if( is_array($basket->basket) && count($basket->basket)>0 ){ 
                            ?>
                            <table id="shopping-basket" class="basket-table">
                            <caption>Products from our shop</caption>
                            <thead>
                                <th scope="col" class="col_title">Title</th>
                                <th scope="col" class="col_price">Price</th>
                                <th scope="col" class="col_quantity">Quantity</th>
                                <th scope="col" class="col_remove">Remove?</th>
                            </thead>
                            <tbody>
								<? 
                                foreach( $basket->basket as $row ){ 
									//echo '<pre>'. print_r($row,true) .'</pre>';
                                    ?>
                                    <tr>
                                        <td class="col_title"><a href="<?= $storeURL . $basket->getProductURL($row->product_id) ?>"><?= $row->tagline ?></a></td>
                                        <td class="col_price"><?= $basket->currency . number_format($row->price,2) ?></td>
                                        <td class="col_quantity">
                                            <input type="text" maxlength="3" id="quantity_<?= $row->item_id ?>" name="quantity_<?= $row->item_id ?>" class="text quantity" value="<?= $row->quantity ?>" />
                                        </td>
                                        <td class="col_remove"><a href="<?=$storeURL?>/shopping-basket/?id=<?=$row->item_id?>&quantity=0">Remove this item from your shopping basket</a></td>
                                    </tr>
                                    <? 
                                } 
                                ?>
                                <tr class="goods_total">
                                    <td colspan="3" class=""></td>
                                    <td id="total_big" class="col_quantity" colspan="1">Total <strong><?= $basket->currency . number_format($basket->total,2) ?></strong></td>
                                </tr>
                                <? 
								if($basket->physicalproducts){ 
									?>
									<tr id="shipping">
										<td colspan="3" class="textlabel"><label for="shipping_zone">Plus postage and packing to </label>
											<select name="shipping_zone" id="shipping_zone">
												<?
												$countries = $basket->getCountryZoneList();
												$selected = isset($_POST['shipping_zone']) ? $_POST['shipping_zone'] : ($_SESSION['shipping_zone']?$_SESSION['shipping_zone']:222); // default to UK
												foreach( $countries as $country ){
												?>
												<option value="<?= $country->country_id ?>"<?= ($selected==$country->country_id ? ' selected="selected"' : '') ?>><?= $country->title ?></option>
												<? } ?>
											</select>
										</td>
										<td class="col_quantity" colspan="1">
											<strong><?= $basket->currency . number_format($basket->pandp,2) ?></strong>
										</td>
										
									</tr>
									<? 
								} 
								?>
                                <tr class="col_recalc">
                                    <td colspan="4">
                                        <input type="submit" class="orange-button" value="Recalculate" />
                                    </td>
                                </tr>
                            </tbody>
                            </table>					
                            <? 
                        }
                        // it doesn't matter if we have no products in our basket because it can be used for other things...
                        ?>
                
                        <?
                        // --------------------------------------------------------------
                        // SHOW ANY PURCHASED EVENTS IN THE BASKET
                        // We do not need to check config here as there will be none if event purchase is not allowed 
                        if( is_array($basket->events) && count($basket->events) ){ 
                            ?>
                            <table class="basket-table" id="events">
                                <caption>Event reservations</caption>
                                <thead>
                                    <tr>
                                        <th scope="col" class="col_title">Item</th>
                                        <th scope="col" class="col_desc">Details</th>
                                        <th scope="col" class="col_price">Price</th>
                                        <th scope="col" class="col_quantity">Quantity</th>
                                        <th scope="col" class="col_last">Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <? foreach( $basket->events as $event ){ ?>
                                    <tr>
                                        <td><a href="<?= $page->drawLinkByGUID($event->guid) ?>"><?= $event->title ?></a></td>
                                        <td class="col_desc"><?= $event->start_date ?> - <?= $event->end_date ?></td>
                                        <td class="col_price"><?= $basket->currency . $event->price ?></td>
                                        <!-- <td class="col_quantity"><?=$event->quantity?></td> -->
					                    <td class="col_quantity"><input type="text" maxlength="3" id="eventqty_<?=$event->entry_id?>" name="eventqty_<?=$event->entry_id?>" class="text quantity" value="<?=$event->quantity?>" /></td>
                                        <td class="col_remove"><a href="<?=$storeURL?>/shopping-basket/?eid=<?=$event->entry_id?>&quantity=0">Remove this item from your shopping basket</a></td>
                                        <!--
                                        <td class="col_remove">
                                            <label for=""></label>
                                            <input type="checkbox" class="quantity" name="event_<?=$event->entry_id?>" id="event_<?=$event->entry_id?>" />
                                        </td>
                                        -->
                                    </tr>
                                    <? } ?>
                                    <tr class="goods_total">
                                        <td colspan="4"></td>
                                        <td class="total_big">Total <?= $basket->currency ?><?= $basket->totals['events'] ?></td>
                                    </tr>
                                    <tr class="button">
                                        <td colspan="5">
                                            <!--<button type="submit" class="update">Recalculate</button>-->
                                            <input type="submit" class="update button" value="Recalculate" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <? 
                        } 
                        ?>
                
                
                
                        <? 
                        // --------------------------------------------------------------
                        // SHOW ANY SPONSORSHIPS YOU WANT TO PAY UP
                        if( is_array($basket->sponsorships) && count($basket->sponsorships) ){ 
                            ?>
                            <table class="basket-table" id="sponsorships">
                                <caption>Sponsorships</caption>
                                <thead>
                                    <tr>
                                        <th scope="col" class="col_title">Item</th>
                                        <th scope="col" class="col_quantity">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <? foreach( $basket->sponsorships as $item ){ ?>
                                    <tr>
                                        <td class="col_title<?= (($item->event_id==$eventID && $item->member_id==$memberID) ? ' highlight' :'') ?>">
                                        <?= ($item->event_id==$eventID && $item->member_id==$memberID) ? '<strong>Just added!</strong><br />' : '' ?>
                                            Sponsoring <a href="<?= $page->drawLinkByGUID($item->pp_guid) ?>"><?= ($item->grp_title>'' ? $item->grp_title : $item->member_name) ?></a> on the 
                                            <a href="<?= $page->drawLinkByGUID($item->event_id) ?>"><?= $item->event_title ?></a>
                                        </td>
                                        <td class="col_quantity"><?= $basket->currency ?><input type="text" class="quantity" name="sp_<?= $item->event_id ?>::<?= $item->member_id ?>" id="sp_<?= $item->event_id ?>::<?= $item->member_id ?>" value="<?= $item->amount ?>" /></td>
                                    </tr>
                                    <? } ?>
                                    <tr class="goods_total">
                                        <td>Total</td>
                                        <td class="total_big"><?= $basket->currency ?><?= $basket->totals['sponsorships'] ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" >
                                            <!--<button type="submit" class="update">Recalculate</button>-->
                                            <input type="submit" class="update button" value="Recalculate" />
                                        </td>
                                    </tr>							
                                </tbody>
                            </table>
                            <? 
                        } 
                        ?>
                
                
                        <? 
                        // --------------------------------------------------------------
                        // SHOW ANY DONATIONS YOU WISH TO MAKE
                        $donationTypes = $basket->getDonationAmounts();
                        if ( 
                            //is_array($donationTypes) && count($donationTypes)>0) && 
                            $store->config['accept-donation'] &&
                            ( 
                                !(is_array($basket->sponsorships) && count($basket->sponsorships)) || 
                                $basket->totals['donation']>0
                            )  
                           ) {
                            ?>
                            <table class="basket-table" id="donations">
                                <caption><?= (count($basket->basket)>0 && $basket->total>0 ? 'Why not add a donation to '.$site->name.'?' : 'A small donation can make a huge difference to our work') ?></caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col" class="col_last">Donation amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($donationTypes) {
                                        foreach( $donationTypes as $type ){ 
                                            ?>
                                            <tr>
                                                <td class="donation_desc"><?= preg_replace('/&pound;([0-9])*/i', '<strong>\0</strong>', $type->description); ?></td>
                                                <td class="col_last">
                                                    <label for="donation_amount_id_<?= $type->amount_id ?>" class="donation_amount">Donate <?= $basket->currency . $type->value ?></label>
                                                    <input type="radio" onClick="javascript:recalculate(1);" name="donation_amount_id" id="donation_amount_id_<?= $type->amount_id ?>" value="<?= $type->amount_id ?>"<?= ($type->amount_id==$basket->donation['amount_id'] ? ' checked="checked"' : '') ?> />
                                                </td>
                                            </tr>
                                            <? 
                                        }
                                    } 
                                    ?>
                                    <tr>
                                        <td class="textlabel"><label for="donation_value">Or enter another amount</label></td>
                                        <td class="col_last"><?= $basket->currency ?><input type="text" class="quantity" id="donate-amt" onBlur="javascript:recalculate();" maxlength="6" name="donation_value" value="<?= ($basket->donation['amount_id']<1 ? $basket->donation['value'] : '') ?>" /></td>
                                    </tr>
                                    <tr class="goods_total">
                                        <td></td>
                                        <td class="total_big">Total <?= $basket->currency ?><?= ($basket->totals['donation']>0 ? $basket->totals['donation'] : 0) ?><?= ($basket->donation['frequency']==1 ? '</td></tr><tr id="frequency"><td></td><td><span id="dFreq">(monthly)</span>' :'') ?></td>
                                    </tr>

                                    <tr class="button">
                                        <td colspan="3">
                                            <!--<button type="submit" class="update">Recalculate</button>-->
                                            <input type="submit" id="recalc-donation" class="update button" value="Recalculate" />
                                            <input type="submit" name="removeDonation" class="update button" style="color:#a33" value="Remove" />
                                        </td>
                                    </tr>
                                    <!--
                                    <tr>
                                        <td colspan="3" style="padding:0 5px;">Would you like to add a message to accompany your donation?</td>
                                    </tr><tr>
                                        <td colspan="3" style="padding:0 5px;">
                                            <textarea style="width:350px;height:40px;" name="donation_message"><?= html_entity_decode(($basket->donation['donation_message'] ? $basket->donation['donation_message'] : $campaign )) ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="1">If you would like to receive a written acknowledgement in the post please tick here</td>
                                        <td class="col_quantity"><input type="checkbox" name="donation_written" value="1" <?=(($basket->donation['donation_written']==1)?'checked="checked"':"")?> /></td>
                                    </tr>
                                    -->
                                </tbody>
                            </table>
                            <? 
                        } 
                
                
                        if( $basket->getGrandTotal()>0 ){ 
                            ?>
                
                            <table class="basket-table" id="final_total">
                            <tbody>
                            <tr id="grand_total">
                                <td class="textlabel">Total cost of your shopping basket</td>
                                <td class="col_quantity col_last"><?= $basket->currency . number_format($basket->getGrandTotal() + $basket->pandp,2) ?></td>
                            </tr>
                            <?php
							/*
                            if( $cArray = $basket->drawCurrencyConversion(number_format(($basket->getGrandTotal() + $basket->pandp),2)) ){
                                foreach( $cArray as $c ){
                                    $conversionDate = $c['date'];
                                    echo '<tr class="currencyConv"><td>'. $c['title'] .'</td>
                                        <td class="col_quantity">
                                            <span class="symbol">'. $c['symbol'] .'</span>
                                            '.$c['value'].'
                                        </td>
                                    </tr>
                                    ';
                                }
                                echo '
                                <tr>
                                    <td id="currencyNote" colspan="2">These conversions are supplied via <abbr title="Really Simple Syndication">RSS</abbr> 
                                            by <a href="http://www.currencysource.com" target="_blank">CurrencySource</a> and should be used as guidance only.
                                    </td>
                                </tr>
                                ';
                            }
                			*/
                            ?>
                            </tbody>
                            </table>
							
                            <table class="basket-table" id="other_info" cellpadding="0" cellspacing="0" border="0" style="padding-bottom:20px;">
                            <tbody>
                            <!--
                            <tr>
                                <td>How did you hear about <?=strtoupper($site->name)?></td>
                                <td class="col_last">
                                    <select name="store_where_seen">
                                    <option value="">Select</option>
                                    <?php
                                        $aopts=array("TV", "Radio", "Newspaper", "Internet", "Direct mail", "Newsletter", "Friend/colleague", "other");
                                        foreach ($aopts as $tmp_item) {
                                            ?><option <?=(($tmp_item==$basket->where_seen)?"selected":"")?>><?=$tmp_item?></option><?php
                                        }
                                    ?>
                                    </select>
                                </td>
                            </tr>
                            -->
                            
                           
                           	<tr>
                            	<td colspan="2" class="col_last">
                                    <fieldset id="buttons">
                                        <input type="submit" id="checkout" name="checkout" class="orange-button" value="Go to checkout" />
                                    </fieldset>
                                </td>
                            </tr> 

                            </tbody>
                            </table>                    	
                                    
                            <?php
                
                        } 
                        else {
                            echo drawFeedback("notice", "Your shopping basket is empty");
                        }
                        ?>
                        
                    </form>
                            
                </div>
                <!-- // End of basket div -->
    
            </div>
            <!-- // End of white-box div -->
            <div id="white-box-bottom"></div>
                
        </div>
        <!-- // End of red-box div -->
        <div id="red-box-bottom"></div>
    
    </div>
    <!-- END OF PRIMARY CONTENT -->
    
</div>

<div id="secondarycontent">
    <!--PANELS-->
    <? include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.checkout.php') ?>
    <? include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.delivery.php') ?>
    <? include($_SERVER['DOCUMENT_ROOT'] .'/store/snippets/panel.security.php') ?>
</div>

		
    
<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); 
?>