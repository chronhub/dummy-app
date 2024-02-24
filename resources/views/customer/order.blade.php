<x-layout>

    {{-- Breadcrumb --}}

    <x-breadcrumb
        :steps="[route('customer.list') => 'customers' , route('customer.info.show', $order->customer_id) => $customer->name]"
        lastStep="order #{{ substr($order->id, 0, 8) }}"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-b dark:border-gray-700 pb-12">

        <div class="">

            <span class="flex items-center text-xl font-semibold text-gray-900 dark:text-white me-3">

                <span class="flex w-2.5 h-2.5 bg-teal-500 rounded-full me-1.5 flex-shrink-0"></span>

                Order of {{ $customer->name }}

            </span>

            <div class="flex mt-3">

                <div class="flex-none w-24 ms-4">

                    <p class="text-sm text-gray-900 dark:text-white underline decoration-dashed underline-offset-8 decoration-white">

                        Status

                    </p>

                </div>

                <span class="flew-shrink w-auto bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">

                    {{ $order->status }}

                </span>

            </div>


            <div class="flex mt-3">

                <div class="flex-none w-24 ms-4">

                    <p class="text-sm text-gray-900 dark:text-white underline decoration-dashed underline-offset-8 decoration-white">

                        Balance

                    </p>

                </div>

                <span class="flew-shrink w-auto bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">

                    {{ $order->balance }}

                </span>

            </div>

            <div class="flex mt-3">

                <div class="flex-none w-24 ms-4">

                    <p class="text-sm text-gray-900 dark:text-white underline decoration-dashed underline-offset-8 decoration-white">

                        Quantity

                    </p>

                </div>

                <span class="flew-shrink w-auto bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">

                    {{ $order->quantity }}

                </span>

            </div>

            <div class="flex mt-3">

                <div class="flex-none w-24 ms-4">

                    <p class="text-sm text-gray-900 dark:text-white underline decoration-dashed underline-offset-8 decoration-white">

                        Id

                    </p>

                </div>

                <span class="flew-shrink w-auto bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300 select-all">

                    {{ $order->id }}

                </span>

            </div>

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

        <div class="overflow-hidden h-64 overflow-y-auto">

            <x-timeline.order_item :orderItems="$order->items"/>

        </div>

    </div>

</x-layout>

