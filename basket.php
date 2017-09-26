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
	$mode = read($_REQUEST,'mode','basket');
	$message = '';
	
	// adding to basket?
	$productID = read($_REQUEST,'id',false);
	$productQuantity = read($_REQUEST,'quantity',false);
	$campaign = read($_GET,'campaign','');
	if( isset($productID) && is_numeric($productID) ){
		$quantity = (isset($productQuantity) && is_numeric($productQuantity)) ? $productQuantity : 1;
		if( $basket->add($productID,$quantity) ){
			$feedback = 'success';
			$message = 'Your shopping basket has been updated';
		}
	}
	
	// adding events or sponsorships to basket
	$eventID = read($_REQUEST,'eid',false);
	// if the eventID has a double-colon, the last bit is the memberID
	if( strstr($eventID,'::') ){
		$tmp = explode('::',$eventID);
		$memberID = $tmp[1];
		$eventID = $tmp[0];
	}else{
		$memberID = false;	
	}
	// if we have an eventID and that event exists...
	if( isset($eventID) && $eventID ){
		if( $data = $basket->eventExists($eventID,$memberID) ){
			// if we have an eventID & memberID then this is a sponsorship
			if( $eventID && $memberID ){
				// if the end date of the event is reached we can accept sponsorships
				if( date('U') > date('U',strtotime($data->cutoff_date)) ){
					$feedback = 'Failure';
					$message = 'Sorry, this event can no longer accept sponsorships';
				}else{
					// If the member exists, and we've already confirmed the event, go ahead with sponsorship...
					if( isset($data->member_exists) && $data->member_exists>'' ){
						$feedback = 'success';
						$message = 'Your sponsorship has been added to your shopping basket.<br />';
						$message .= 'Please <a href="#sponsorships">add an amount</a> to help support <strong>'. $data->member_exists .'</strong> and '.$site->title;
						$basket->add(array($eventID,$memberID),'0.00','sponsorship');
					}else{
						$message = 'member does not exist! '.$data->member_exists;
					}
				}
			// if we only have an eventID, we're buying a place on it
			}else if( $eventID && !$memberID ){
				// if the cut-off date for applications has been reached you can't buy a place
				if( date('U') > date('U',strtotime($data->cutoff_date)) ){
					$feedback = 'Failure';
					$message = 'This event can no longer accept places';
				}else{
					// go ahead with buying a place on this event...
					//$message = 'buy place on event: '. $eventID;
					$basket->add($eventID,1,'event');
				}
			}	
		}else{
			// the event doesn't exist!
			$message = 'This event does not exist!';
		}
	}
	
	
	// Panels
	$panels = new PanelsPlaceholder();
	$panels->load($page->getGUID(), 'panels');
	$panels->setMode($mode);

	$tags = new Tags();
	
	
	if( isset($_POST) && $_POST ) {
		// Form Processing
		if( isset($_POST['shipping_zone']) ){
			$basket->country = $_POST['shipping_zone'];
			$_SESSION['shipping_zone']=$basket->country;
		}
		
		//echo '<pre style="color:#fff">'. print_r($_POST,true) .'</pre>';
		//exit();

		$tmp = array(); // hold our items and quantities
		$eTmp = array(); // events
		$sTmp = array(); // sponsorships
		$iTmp = array();
		foreach($_POST as $key => $value){
			if( substr_count($key,'quantity')==1 && $value>0 ){
				$id = substr($key,strrpos($key,'_')+1);
				$itemStock = $store->checkStockLevel($id);
				if( $value > $itemStock ){
					$value = $itemStock;
					$feedback = 'error';
					$message = 'You were attempting to order more of a product than is currently available';
				}
				$tmp[] = array('item_id'=>$id,'quantity'=>$value);
			}else if( substr($key,0,3)=='sp_' ){
				if( $value>0 ){
					$thisKey = substr($key,3);
					$sTmp[$thisKey] = $value;
				}
			}else if( substr($key,0,6)=='event_' ){
				$thisKey = substr($key,6);
				$eTmp[$thisKey] = $value;				
			}else if (substr($key,0,6)=="store_") {
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
		// events...		
		
		// sponsorships...
		
		//echo '<pre style="color:#fff">'. print_r($tmp,true) .'</pre>';
		//echo '<pre style="color:#fff">'. print_r($dTmp,true) .'</pre>';
		//echo '<pre style="color:#fff">'. print_r($sTmp,true) .'</pre>';
		//echo '<div style="color:#fff">';

		if( $basket->update($tmp,$dTmp,$eTmp,$sTmp,$iTmp) ){
			//echo 'UPDATED!';
			if( !$message ){
				$feedback = 'success';
				$message = 'Your shopping basket has been updated';
			}
		}else{
			//echo 'NOT UPDATED!';
		}
		
		if( isset($_POST['checkout']) && $_POST['checkout']>'' ){
			if( $basket->getGrandTotal()>0 ){
				//$feedback = 'error';
				//$message = 'Please note that the store is temporarily offline.';
				
				// Add to subscribers if required...
				
				redirect('/shop/checkout/?cartID='. $cartID.($_POST['news_add_email']?"&subscribe=1":""));
			}else{
				$feedback = 'error';
				$message = 'You can\'t proceed to the checkout with nothing in your shopping basket';
			}
		}
		//echo '</div>';
	}
	
	

	// Page specific options
	
	$pageClass = 'page'; // used for CSS usually
	
	$css = array('page','forms','basket','store_panels'); // all attached stylesheets
	if($page->style != NULL){
		$css[] = '2col_right';
	}
	
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

	
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/header.inc.php');
	include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/breadcrumb.inc.php');
?>	

    <div id="primarycontent">
        <?=drawFeedback($feedback, $message)?>
		<div id="basket">
			<h1 class="pagetitle"><?=($mode=='checkout' ? 'Checkout' : $page->getTitle()) ?></h1>
			
	<? // SHOPPING BASKET ?>			
				<form action="<?= $request // set at the top of rewrite?>" method="post" id="shopping-basket">
					<input type="hidden" name="cartID" value="<?= $cartID ?>" />

				<? if( is_array($basket->basket) && count($basket->basket)>0 ){ ?>
					<table id="shopping-basket">
						<caption>Purchases from the <?=$site->name?> store</caption>
						<thead>
							<th scope="col" class="col_title">Item</th>
							<th scope="col" class="col_desc">Description</th>
							<th scope="col" class="col_price">Price per item</th>
							<th scope="col" class="col_quantity">Quantity</th>
						</thead>
						<tbody>
							<? foreach( $basket->basket as $row ){ 
								//echo '<pre>'. print_r($row,true) .'</pre>';
							?>
							<tr>
								<td class="col_title"><a href="<?= $storeURL . $basket->getProductURL($row->product_id) ?>"><?= $row->title ?></a></td>
								<td class="col_desc"><?= $row->tagline //$row->variants ?></td>
								<td class="col_price"><?= $basket->currency . number_format($row->price,2) ?></td>
								<td class="col_quantity">
									<input type="text" maxlength="3" id="quantity_<?= $row->item_id ?>" name="quantity_<?= $row->item_id ?>" class="quantity" value="<?= $row->quantity ?>" />
								</td>
							</tr>
							<? } ?>
							<tr class="goods_total">
								<td colspan="3" class="textlabel">Total</td>
								<td id="total_big" class="col_quantity" colspan="1"><strong><?= $basket->currency . number_format($basket->total,2) ?></strong></td>
							</tr>
							<? if( !(count($basket->basket)==1 && $row->physical==0)){ ?>
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
								<td class="col_quantity" colspan="1"><strong><?= $basket->currency . number_format($basket->pandp,2) ?></strong></td>
							</tr>
							<? } ?>
							<tr>
								<td colspan="4">
									<!--<button type="submit" class="update">Recalculate</button>-->
									<input type="submit" class="update button" value="Recalculate" />
								</td>
							</tr>
						</tbody>
					</table>					
					<? }
					// it doesn't matter if we have no products in our basket because it can be used for other things...
					?>


					<? if( is_array($basket->events) && count($basket->events) ){ ?>
					<table id="events">
						<caption>Event reservations</caption>
						<thead>
							<tr>
								<th scope="col" class="col_title">Item</th>
								<th scope="col" class="col_desc">Details</th>
								<th scope="col" class="col_price">Price</th>
								<th scope="col" class="col_quantity">Remove</th>
							</tr>
						</thead>
						<tbody>
							<? foreach( $basket->events as $event ){ ?>
							<tr>
								<td><a href="<?= $page->drawLinkByGUID($event->guid) ?>"><?= $event->title ?></a></td>
								<td class="col_desc"><?= $event->start_date ?> - <?= $event->end_date ?></td>
								<td class="col_price"><?= $basket->currency . $event->price ?></td>
								<td class="col_quantity">
									<label for=""></label>
									<input type="checkbox" class="quantity" name="event_<?= $event->guid ?>" id="event_<?= $event->guid ?>" />
								</td>
							</tr>
							<? } ?>
							<tr class="goods_total">
								<td colspan="3">Total</td>
								<td class="total_big"><?= $basket->currency ?><?= $basket->totals['events'] ?></td>
							</tr>
							<tr>
								<td colspan="4">
									<!--<button type="submit" class="update">Recalculate</button>-->
									<input type="submit" class="update button" value="Recalculate" />
								</td>
							</tr>
						</tbody>
					</table>
					<? } ?>



					<? if( is_array($basket->sponsorships) && count($basket->sponsorships) ){ ?>
					<table id="sponsorships">
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
					<? } ?>


					
					<? $donationTypes = $basket->getDonationAmounts();
					if( isset($donationTypes) && ( !( is_array($basket->sponsorships) && count($basket->sponsorships)) || $basket->totals['donation']>0)  ){
					?>
					<table id="donations">
						<caption><?= (count($basket->basket)>0 && $basket->total>0 ? 'Why not add a donation to '.$site->name.'?' : 'A small donation can make a huge difference to our work') ?></caption>
						<thead>
							<tr>
								<th scope="col">Item</th>
								<th scope="col" class="col_quantity">Donation amount</th>
							</tr>
						</thead>
						<tbody>
							<? foreach( $donationTypes as $type ){ ?>
							<tr>
								<td class="donation_desc"><?= preg_replace('/&pound;([0-9])*/i', '<strong>\0</strong>', $type->description); ?></td>
								<td class="col_quantity">
									<label for="donation_amount_id_<?= $type->amount_id ?>" class="donation_amount">Donate <?= $basket->currency . $type->value ?></label>
									<input type="radio" onClick="javascript:recalculate(1);" name="donation_amount_id" id="donation_amount_id_<?= $type->amount_id ?>" value="<?= $type->amount_id ?>"<?= ($type->amount_id==$basket->donation['amount_id'] ? ' checked="checked"' : '') ?> />
								</td>
							</tr>
							<? } ?>
							<tr>
								<td class="textlabel"><label for="donation_value">Or enter another amount</label></td>
								<td class="col_quantity"><?= $basket->currency ?><input type="text" class="quantity" id="donate-amt" onBlur="javascript:recalculate();" maxlength="6" name="donation_value" value="<?= ($basket->donation['amount_id']<1 ? $basket->donation['value'] : '') ?>" /></td>
							</tr>
							<!--//
							<tr>
								<td class="textlabel"><label for="donation_frequency" id="monthly_donation">I would like to make this donation every month</label></td>
								<td class="totals"><input type="checkbox" name="donation_frequency" value="1" <?= ($basket->donation['frequency']==1 ? ' checked="checked"' : '') ?> /></td>
							</tr>
							
							<tr>
								<td class="textlabel"><label for="donation_gift_aid" id="donation_gift_aid">Use Gift Aid</label></td>
								<td class="totals"><input type="checkbox" name="donation_gift_aid" id="donation_gift_aid" value="1" <?= ($basket->donation['gift_aid']==1 ? ' checked="checked"' : '') ?> /></td>
							</tr>
							//-->
							<tr class="goods_total">
								<td>Total</td>
								<td class="total_big"><?= $basket->currency ?><?= ($basket->totals['donation']>0 ? $basket->totals['donation'] : 0) ?><?= ($basket->donation['frequency']==1 ? '</td></tr><tr id="frequency"><td></td><td><span id="dFreq">(monthly)</span>' :'') ?></td>
							</tr>
							<?// if( count($basket->basket)<=0 ){ ?>
							<tr>
								<td colspan="3">
									<!--<button type="submit" class="update">Recalculate</button>-->
									<input type="submit" id="recalc-donation" class="update button" value="Recalculate" />
									<input type="submit" name="removeDonation" class="update button" style="color:#a33" value="Remove" />
								</td>
							</tr>
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
                            </tr
							><?// } ?>
						</tbody>
					</table>
					<? } ?>

					<table id="final_total">
						<tbody>
							<tr id="grand_total">
								<td class="textlabel">Total</td>
								<td class="col_quantity totals total_big"><?= $basket->currency . number_format($basket->getGrandTotal() + $basket->pandp,2) ?></td>
							</tr>
				<? if( $basket->getGrandTotal()>0 ){ ?>
					<? 
					if( $cArray = $basket->drawCurrencyConversion(number_format(($basket->getGrandTotal() + $basket->pandp),2)) ){
						foreach( $cArray as $c ){
							echo "\t".'<tr class="currencyConv"><td>'. $c['title'] .'</td>
								<td class="col_quantity"><span class="symbol">'. $c['symbol'] .'</span>'. 
								$c['value'] .'</td></tr>'."\n";
						}
						echo '<tr>
								<td id="currencyNote" colspan="2">These conversions are supplied via <abbr title="Really Simple Syndication">RSS</abbr> 
													by <a href="http://www.currencysource.com" target="_blank">CurrencySource</a> and should be used as guidance only.
								</td>
							</tr>';
					}
					?>
				<? } ?>
						</tbody>
					</table>
					
					<p id="giftaidNote">
						<strong>Add up to 25% to your donation for free. Just complete the Gift Aid Declaration in the checkout.</strong><br />
						Gift Aid allows <?=$site->name?> to reclaim tax on donations from UK taxpayers, increasing your generous donation by up to 25%.
					</p>
					<table id="other_info" style="padding-bottom:20px;">
					<tbody>
					<tr>
                        <td colspan="1">How did you hear about <?=$site->name?></td>
                        <td>
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
                    </tr><tr>
                        <td colspan="1"><?=$site->name?> would like to keep you informed of our work tick here if you would like to receive regular updates</td>
                        <td class="col_quantity"><input type="checkbox" name="news_add_email" value="1" <?=(($_POST['news_add_email']==1)?'checked="checked"':"")?> /></td>
                    </tr><tr>
						<td colspan="1"><?=$site->name?> occasionally swaps names of supporters with like-minded organizations. If you would prefer not to be contacted in this way, please tick here</td>
    	                <td class="col_quantity"><input type="checkbox" name="store_no_swap" value="1" <?=(($basket->no_swap==1)?'checked="checked"':"")?> /></td>
					</tr>
                    </tbody>
                    </table>                    	
                    
					<fieldset id="buttons">
						<a id="cancel_button" href="<?= $storeURL ?>">Continue shopping</a>
						<!--<button type="submit" id="checkout" name="checkout" value="1">Proceed to checkout</button>-->
						<input type="submit" id="checkout" name="checkout" class="submit button" value="Proceed to checkout" />
					</fieldset>
				</form>
				
		</div>
    </div>
    <div id="secondarycontent">
        <!--PANELS-->
        <? include($_SERVER['DOCUMENT_ROOT'] .'/includes/snippets/store/panel.security.php') ?>
		
		<div class="panel">
			<h4>Having problems with this page?</h4>
			<p>If you�re having problems with your payment, 
			please contact us at (+44) 0161 236 4311 or <a href="mailto:<?=$site->config['contact_recipient_email']?>"><?=$site->config['contact_recipient_email']?></a> for help.</p>
		</div>

		<div class="panel">
			<h4>Give with confidence</h4>
			<p>
			<img src="/silo/images/frsb-logo-190x190_190x190.jpg" width=190" height="190" />
			<?=$site->name?> is a member of the Fundraising Standards Board (FRSB).
			</p>
			<ul>
				<li><a href="/news/the-fundraising-standards-board-frsb/">What is the FRSB?</a></li>
			</ul>
		</div>
		
    </div>
    
<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/footer.inc.php'); ?>

 <?php /* TINY MCE */ 
 if($mode == 'edit'){	?>
	 <script type="text/javascript" src="/treeline/behaviour/tiny_mce/tiny_mce_content.js"></script>
 <?php 
 }
 ?>
