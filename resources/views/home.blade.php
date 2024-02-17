<x-layout>

    <div class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">

        <div>

            <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Orders</h2>

            <div class="mx-auto mt-3">

                <div class="md:flex max-w-lg text-gray-500 dark:text-gray-400 text-sm leading-relaxed">

                    <div class="flex-grow md:text-left mt-6">

                        <h3 class="text-xl heading">Order</h3>

                        <p class="mt-2 mb-3">total: {{ $order->total_orders }}</p>
                        <p class="mt-2 mb-3">balance: {{ $order->total_balance }}</p>
                        <p class="mt-2 mb-3">quantity: {{ $order->total_quantity }}</p>

                    </div>

                    <div class="flex-grow md:text-left mt-6">

                        <h3 class="text-xl heading">Inventory</h3>

                        <p class="mt-2 mb-3">total: {{ $inventory->total_items }}</p>
                        <p class="mt-2 mb-3">stock: {{ $inventory->total_stock }}</p>
                        <p class="mt-2 mb-3">reserved: {{ $inventory->total_reserved }}</p>

                    </div>

                </div>

            </div>

        </div>

    </div>


</x-layout>
