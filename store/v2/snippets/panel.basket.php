<?php 
if ($basket->getGrandTotal()>0) { 
	?>
	<div id="store_basket" class="panel panel_2">
    	<div class="panel-top"></div>
    	<div class="panel-content">
		<h3><span>&pound;<?=number_format($basket->getGrandTotal() + $basket->pandp,2)?></span>Shopping basket total</h3>
		<div>
			<form method="post" id="go_checkout" action="<?=$storeURL?>/shopping-basket">
			<input type="submit" class="orange" value="Checkout"/>
			</form>
		</div>
        </div>
    	<div class="panel-bottom"></div>
	</div>
	<?php 
} 
?>
