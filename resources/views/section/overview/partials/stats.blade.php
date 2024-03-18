<x-overview-stats
    label="Order"
    :value="$order->total_orders"
    path="M4 4c0-.6.4-1 1-1h1.5c.5 0 .9.3 1 .8L7.9 6H19a1 1 0 0 1 1 1.2l-1.3 6a1 1 0 0 1-1 .8h-8l.2 1H17a3 3 0 1 1-2.8 2h-2.4a3 3 0 1 1-4-1.8L5.7 5H5a1 1 0 0 1-1-1Z"
/>

<x-overview-stats
    label="Balance"
    :value="$order->total_balance"
    path="M8 17.3a5 5 0 0 0 2.6 1.7c2.2.6 4.5-.5 5-2.3.4-2-1.3-4-3.6-4.5-2.3-.6-4-2.7-3.5-4.5.5-1.9 2.7-3 5-2.3 1 .2 1.8.8 2.5 1.6m-3.9 12v2m0-18v2.2"
/>

<x-overview-stats
    label="Stock"
    :value="$inventory->total_stock"
    path="M13 10V3L4 14h7v7l9-11h-7z"
/>

<x-overview-stats
    label="Reserved"
    :value="$inventory->total_reserved"
    path="M14 7h-4v3a1 1 0 1 1-2 0V7H6a1 1 0 0 0-1 1L4 19.7A2 2 0 0 0 6 22h12c1 0 2-1 2-2.2L19 8c0-.5-.5-.9-1-.9h-2v3a1 1 0 1 1-2 0V7Zm-2-3a2 2 0 0 0-2 2v1H8V6a4 4 0 1 1 8 0v1h-2V6a2 2 0 0 0-2-2Z"
/>


{{--<p class="mt-2 mb-3">total: {{ $order->total_orders }}</p>--}}
{{--<p class="mt-2 mb-3">balance: {{ $order->total_balance }}</p>--}}
{{--<p class="mt-2 mb-3">quantity: {{ $order->total_quantity }}</p>--}}
{{--<p class="mt-2 mb-3">total: {{ $inventory->total_items }}</p>--}}
{{--<p class="mt-2 mb-3">stock: {{ $inventory->total_stock }}</p>--}}
{{--<p class="mt-2 mb-3">reserved: {{ $inventory->total_reserved }}</p>--}}

