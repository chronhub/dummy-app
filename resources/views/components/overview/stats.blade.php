<x-overview-stats
    label="Order"
    :value="$order->total_orders"
    path="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
/>

<x-overview-stats
    label="Balance"
    :value="$order->total_balance"
    path="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
/>

<x-overview-stats
    label="Stock"
    :value="$inventory->total_stock"
    path="M13 10V3L4 14h7v7l9-11h-7z"
/>

<x-overview-stats
    label="Reserved"
    :value="$inventory->total_reserved"
    path="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"
/>


{{--<p class="mt-2 mb-3">total: {{ $order->total_orders }}</p>--}}
{{--<p class="mt-2 mb-3">balance: {{ $order->total_balance }}</p>--}}
{{--<p class="mt-2 mb-3">quantity: {{ $order->total_quantity }}</p>--}}
{{--<p class="mt-2 mb-3">total: {{ $inventory->total_items }}</p>--}}
{{--<p class="mt-2 mb-3">stock: {{ $inventory->total_stock }}</p>--}}
{{--<p class="mt-2 mb-3">reserved: {{ $inventory->total_reserved }}</p>--}}
