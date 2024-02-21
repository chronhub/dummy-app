<x-layout>

    <div class="">

        <div>

            <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Order {{ $order->id }}</h2>

            <div class="text-gray-500 dark:text-gray-400">

                <p class="mt-2 mb-3">Customer {{ $order->customer_id }}</p>
                <p class="mt-2 mb-3">Status {{ $order->status }}</p>
                <p class="mt-2 mb-3">Balance {{ $order->balance }}</p>
                <p class="mt-2 mb-3">Quantity {{ $order->quantity }}</p>

            </div>

            @if($order->status === 'created' || $order->status === 'modified')

                <div class="flex mt-6 gap-6">

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

                </div>

            @endif

        </div>

    </div>

    <div class="overflow-y-auto h-lvh pb-40">

        <div>

            <h3 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Items {{ $order->items->count() }}</h3>

            <div class="mx-auto mt-3">

                <div class="md:flex max-w-lg text-gray-500 dark:text-gray-400 text-sm leading-relaxed">

                    <div class="flex-grow md:text-left">

                        @if($order->items->isEmpty())

                            <p class="mt-2 mb-3">No order item</p>

                        @else

                            @foreach($order->items as $item)

                                <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Sku {{ $item->sku_id }}</h4>

                                <p class="mt-2 mb-3">quantity {{ $item->quantity }}</p>
                                <p class="mt-2 mb-3">unit price {{ $item->unit_price }}</p>
                                <p class="mt-2 mb-3">created_at {{ $item->created_at }}</p>

                            @endforeach

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layout>
