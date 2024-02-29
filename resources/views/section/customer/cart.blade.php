<x-layout>

    {{-- Breadcrumb --}}

    <x-breadcrumb
        :steps="[route('customer.list') => 'customers' , route('customer.info.show', $cart->customer_id) => $customer->name]"
        lastStep="cart #{{ substr($cart->id, 0, 8) }}"
    />

    <x-layout.main>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 border-b dark:border-gray-700 pb-12">

            {{-- Mini catalog --}}

            @include('section.customer.partials.__catalog_mini', [$customer, $cart, $catalog])

            {{-- Cart --}}

            @include('section.customer.partials.__cart_summary', [$cart, $customer])

        </div>

    </x-layout.main>

</x-layout>


