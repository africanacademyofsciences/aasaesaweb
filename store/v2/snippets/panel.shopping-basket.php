<?php 
if ($basket->getGrandTotal()>0) { 
	$nicetotal = $basket->currency.number_format($basket->getGrandTotal() + $basket->pandp,2);
	?>
	<div id="store_basket" class="panel panel">
    	<div class="panel-top"></div>
    	<div class="panel-content">
		<h3>Shopping cart</h3>
		<div>
            <form action="<?=$storeURL?>/shopping-basket/" method="post" id="shopping-basket">
				<input type="hidden" name="fwd" value="<?=$request?>" />

                        <? 
                        // --------------------------------------------------------------
                        // SHOW ALL PURCHASES FROM THE STORE
                        if( is_array($basket->basket) && count($basket->basket)>0 ){ 
                            ?>
                            <table id="shopping-basket" cellpadding="0" cellspacing="0" border="0">
                            <tbody>
                                <? 
                                foreach( $basket->basket as $row ){ 
                                    //echo '<pre>'. print_r($row,true) .'</pre>';
                                    ?>
                                    <tr>
                                        <!-- <td class="col_title"><a href="<?=($storeURL.$row->name)?>"><?= $row->title ?></a></td> -->
										<td class="col_desc"><?= $row->tagline //$row->variants ?></td>
                                        <td class="col_quantity">
                                            <input type="text" maxlength="3" id="quantity_<?= $row->item_id ?>" name="quantity_<?= $row->item_id ?>" class="quantity" value="<?= $row->quantity ?>" />
                                        </td>
                                        <td class="col_price"><?= $basket->currency . number_format($row->price,2) ?></td>
										<!-- <td class="col_remove"><a href="<?=$storeURL?>/shopping-basket/?id=<?=$row->item_id?>&quantity=0">Remove this book from your shopping basket</a></td> -->
                                    </tr>
                                    <? 
                                } 

								if($basket->physicalproducts>0){ 
									?>
									<tr class="textlabel">
										<td colspan="3" class="textlabel">
                                        	<label for="shipping_zone">Plus postage and packing to </label>
                                        </td>
                                    </tr>
                                    
                                    <tr id="shipping">
                                        <td colspan="2" class="country-list">
											<select name="shipping_zone" class="store-grey" id="shipping_zone">
												<?
												$countries = $basket->getCountryZoneList();
												$selected = isset($_POST['shipping_zone']) ? $_POST['shipping_zone'] : ($_SESSION['shipping_zone']?$_SESSION['shipping_zone']:222); // default to UK
												foreach( $countries as $country ){
													?>
													<option value="<?= $country->country_id ?>"<?= ($selected==$country->country_id ? ' selected="selected"' : '') ?>><?= $country->title ?></option>
													<? 
												} 
												?>
											</select>
										</td>
										<td class="col_price">
											<strong><?= $basket->currency . number_format($basket->pandp,2) ?></strong>
										</td>
										
									</tr>
									<? 
								} 
								?>
                                <tr class="goods_recalc">
                                	<td>Edit the cart then</td>
                                    <td class="button" colspan="2"><input type="submit" class="orange-button" value="Update" /></td>
                                </tr>

                                <tr class="goods_total">
                                    <td colspan="2" class="textlabel">Total</td>
                                    <td id="total_big" class="col_price"><?= $basket->currency . number_format($basket->total,2) ?></td>
                                </tr>
                                
                                <?php
								$formattedStoreURL = $storeURL;
								if (substr($formattedStoreURL, 0, 1)=="/") $formattedStoreURL = substr($formattedStoreURL, 1);
								?>
                                <tr class="checkout">
                                	<td colspan="3"><a href="<?=$site->link?><?=$formattedStoreURL?>/checkout/">Checkout</a></td>
                                </tr>
                                
                            </tbody>
                            </table>					
                            <? 
                        }
                        // it doesn't matter if we have no products in our basket because it can be used for other things...
                        ?>

			</form>

		</div>
        </div>
    	<div class="panel-bottom"></div>
	</div>
	<?php 
} 
?>
