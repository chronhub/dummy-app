<x-layout>

    {{-- Breadcrumb --}}

    <x-breadcrumb
        :steps="[route('customer.list') => 'customers' , route('customer.info.show', $order->customer_id) => $customer->name]"
        lastStep="order #{{ substr($order->id, 0, 8) }}"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-b dark:border-gray-700 pb-12">

        <div>

            <div class="flex items-center gap-4 mb-6">

                <div class="relative w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">

                    <svg class="absolute w-12 h-12 text-gray-400 -left-1"
                         fill="currentColor"
                         viewBox="0 0 20 20"
                         xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>

                </div>

                <div class="font-medium dark:text-white">

                    <div>Order of {{ $customer->name }}</div>

                    <div class="text-sm text-gray-500 dark:text-gray-400">registered at {{ date('F d Y', strtotime($order->created_at))  }}</div>

                </div>

            </div>

            <x-two_col_info label="status" :value="$order->status"/>

            <x-two_col_info label="balance" :value="$order->balance"/>

            <x-two_col_info label="quantity" :value="$order->quantity"/>

            <x-two_col_info label="id" :value="$order->id"/>

            <div class="flex items-center gap-4 mt-8 text-gray-700 dark:text-gray-200">

                @if($order->status === 'created' || $order->status === 'modified')

                    <x-button
                        :route="route('seed.order.cancel',[$order->customer_id, $order->id])"
                        label="Cancel"
                        color="violet"
                    />

                    <x-button
                        :route="route('seed.order.add',[$order->customer_id, $order->id])"
                        label="Add random item"
                        color="violet"
                    />

               @endif

                <x-button
                    :route="route('customer.order.history.show',[$order->customer_id, $order->id])"
                    label="History"
                    color="violet"
                />

            </div>

        </div>

        {{-- Orders summary timeline --}}

        <div class="h-64 overflow-y-auto">

            @include('section.customer.partials.order_item', ['orderItems' => $order->items])

        </div>

    </div>

</x-layout>

