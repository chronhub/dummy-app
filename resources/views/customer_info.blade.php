<x-layout>

    <div class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">

        <div>

            <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Profile</h2>

            <div class="mx-auto mt-3">

                <div class="md:flex max-w-lg text-gray-500 dark:text-gray-400 text-sm leading-relaxed">

                    <div class="flex-grow md:text-left">

                        <p class="font-bold">{{ $customer->email }}</p>
                        <h3 class="text-xl heading">{{ $customer->name }}</h3>
                        <p class="mt-2 mb-3">{{ $customer->street }}</p>
                        <p class="mt-2 mb-3">{{ $customer->city }}</p>
                        <p class="mt-2 mb-3">{{ $customer->postal_code }}</p>
                        <p class="mt-2 mb-3">{{ $customer->country }}</p>

                    </div>

                </div>

            </div>

            <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">Orders</h2>

            <div class="mx-auto mt-3">

                <div class="card md:flex max-w-lg text-gray-500 dark:text-gray-400 text-sm leading-relaxed">

                    <div class="flex-grow md:text-left">

                        <div class="px-4 py-5">

                            @if($orders->isEmpty())

                                <p class="mt-2 mb-3">No orders</p>

                            @else

                                @foreach($orders as $order)

                                    <p class="mt-2 mb-3">

                                        <a href="{{ route('customer.order.show', [$order->customer_id, $order->id]) }}">
                                            {{ $order->id }} - {{ $order->status }}
                                        </a>

                                    </p>

                                @endforeach

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">

        <div>



        </div>

    </div>

</x-layout>

