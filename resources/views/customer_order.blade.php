<x-layout>

    <div class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">

        <div>

            <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Order {{ $order->id }}</h2>

            <div class="text-gray-500 dark:text-gray-400">

                <p class="mt-2 mb-3">Customer {{ $order->customer_id }}</p>
                <p class="mt-2 mb-3">Status {{ $order->status }}</p>
                <p class="mt-2 mb-3">Balance {{ $order->balance }}</p>
                <p class="mt-2 mb-3">Quantity {{ $order->quantity }}</p>

            </div>

        </div>

    </div>

    <div class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">

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
