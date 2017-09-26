
<div id="deliveryPanel" class="store_panel">
    <h3>Postal charges explained</h3>
    <? if( $regions = $store->getShippingZones() ){ ?>
    <table id="deliveryFees" border="0" cellpadding="0" cellspacing="0">
        <caption>We charge postage and packing based on the total weight of the order(s), and the delivery region.<br /><br />Examples to a UK address</caption>
        <thead>
            <th scope="col">Up to</th>
            <th scope="col" class="price">P&amp;P</th>
        </thead>
        <tbody>
            <tr>
                <td>1Kg</td>
                <td class="price">&pound;1.50</td>
            </tr>
            <tr>
                <td>2Kg</td>
                <td class="price">&pound;3.00</td>
            </tr>
            <tr>
                <td>3Kg</td>
                <td class="price">&pound;7.00</td>
            </tr>
        </tbody>
    </table>
    <? } ?>
</div>