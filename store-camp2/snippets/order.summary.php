<table id="orderSummary">
<tbody>
	<tr class="title">
		<td colspan="2">Summary of this purchase</td>
	</tr>
	<tr>
		<td>Total for purchases (including donations)</td>
		<td class="totals"><?= $basket->currency . number_format($basket->getGrandTotal(),2) ?></td>
	</tr>
	<tr>
		<td>Postage and packaging</td>
		<td class="totals"><?= $basket->currency . number_format($basket->pandp,2) ?></td>
	</tr>
	<tr>
		<td class="">Total amount</td>
		<td class="totals grand-total"><strong><?= $basket->currency . number_format($basket->getGrandTotal() + $basket->pandp,2) ?></strong></td>
	</tr>
	<?php if ($store->config['transaction-name']) { ?>           
		<tr class="statementAdvice">
		<td colspan="2">This transaction will appear on your statement as <em><?=$store->config['transaction-name']?></em></td>
		</tr>
	<?php } ?>
</tbody>
</table>
