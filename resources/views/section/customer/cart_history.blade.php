<x-layout>

    <x-breadcrumb
        :steps="[
             route('customer.list') => 'customers',
             route('customer.info.show', $customer->id) => $customer->name,
             route('customer.cart.view', [$customer->id, $cart_id]) => 'cart #'. substr($cart_id, 0, 8),
        ]"
        lastStep="history"
    />

    <x-layout.main>

        <x-event_history :history="$cart_history"/>

    </x-layout.main>

</x-layout>

