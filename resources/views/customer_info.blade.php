<x-layout>

    <div class="flex gap-4 border-b dark:border-gray-700 mb-3 pb-3">

        <div class="relative w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">

            <svg
                class="absolute w-12 h-12 text-gray-400 -left-1"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>

        </div>


        <div class="dark:text-white">

            <div>{{ $customer->name }}</div>
            <div>{{ $customer->email }}</div>

            <div class="text-sm text-gray-500 dark:text-white-400">

                <ul class="mt-1.5 list-disc list-inside list-none">
                    <li>{{ $customer->street }}</li>
                    <li>{{ $customer->city }}</li>
                    <li>{{ $customer->postal_code }}</li>
                    <li>{{ $customer->country }}</li>
                </ul>

            </div>

        </div>

    </div>

    <div class="flex gap-4 border-b dark:border-gray-700 pb-3">

        <h1 class="dark:text-white">Orders</h1>


        <div class="text-md text-gray-500 dark:text-white-400">

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

</x-layout>

