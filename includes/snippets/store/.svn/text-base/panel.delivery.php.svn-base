		<div id="deliveryPanel" class="panel">
			<h4>Delivery fees explained</h4>
			<? if( $regions = $store->getShippingZones() ){ ?>
			<table id="deliveryFees">
				<caption>Postage and packing costs per item ordered:</caption>
				<thead>
					<th scope="col">Region</th>
					<th scope="col">Cost<!--Packaging--></th>
					<!--<th scope="col">Per kilo</th>-->
				</thead>
				<tbody>
				<? /*foreach( $regions as $r ){ ?>
					<tr>
						<td><?= $r->title ?></td>
						<td class="money">&pound;<?= $r->packaging_value ?></td>
						<td class="money">&pound;<?= $r->postage_per_kilo ?></td>
					</tr>
				<? } */?>
					<tr>
						<td>UK</td>
						<td>&pound;2.85</td>
					</tr>
					<tr>
						<td>Outside UK</td>
						<td>&pound;4.85</td>
					</tr>
				</tbody>
			</table>
			<? } ?>
		</div>