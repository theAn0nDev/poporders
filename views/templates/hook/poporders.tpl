<!-- Block poporders -->

<div id="poporders_block_header" class="poporders_block_header">
    <div class="img">
        <img class="linkImg" src="{$order['linkImg']}" alt="Product buy">
    </div>

    <div class="data">
        <div class="customer">
            <p class="bold">{$order['customer']}</p>
        </div>

        <div class="product">
            <p>{$order['product']}</p>
        </div>

        <div class="daysAgo">
            <p class="bold">{$order['daysAgo']}</p>
        </div>
    </div>
</div>

<!-- /Block poporders -->

<script type="text/javascript" src="/modules/poporders/js/poporders.js"></script>