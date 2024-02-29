<x-layout>

    <x-breadcrumb
        :steps="[
             route('customer.list') => 'customers',
             route('customer.info.show', $customer_id) => $customer->name,
             route('customer.order.show', [$customer_id, $order_id]) => 'order #'. substr($order_id, 0, 8),
        ]"
        lastStep="history"
    />

    <x-layout.main>

        <x-event_history :history="$order_history"/>

    </x-layout.main>

</x-layout>
