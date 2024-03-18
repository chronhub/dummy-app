{{-- Customer orders list --}}
<div class="pl-3">

    <div class="flex items-center gap-4 mb-6">

        <div class="relative w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">

            <svg class="absolute w-12 h-12 text-gray-400 -left-2.5"
                 fill="currentColor"
                 viewBox="0 0 20 20"
                 xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      d="M14 7h-4v3a1 1 0 1 1-2 0V7H6a1 1 0 0 0-1 1L4 19.7A2 2 0 0 0 6 22h12c1 0 2-1 2-2.2L19 8c0-.5-.5-.9-1-.9h-2v3a1 1 0 1 1-2 0V7Zm-2-3a2 2 0 0 0-2 2v1H8V6a4 4 0 1 1 8 0v1h-2V6a2 2 0 0 0-2-2Z"
                      clip-rule="evenodd">

                </path>
            </svg>

        </div>

        <div class="font-medium dark:text-white">

            <div>Orders</div>

        </div>

    </div>

    <ol class="mt-4 border-l border-neutral-300 dark:border-neutral-500">

        @if($orders->isEmpty())

            <li>

                <div class="flex-start flex items-center pt-2">

                    <div class="-ml-[5px] mr-3 h-[9px] w-[9px] rounded-full bg-neutral-300 dark:bg-neutral-500"></div>

                    <p class="text-sm text-neutral-500 dark:text-neutral-300">

                        NO ORDER

                    </p>

                </div>

            </li>

        @else

            @foreach($orders as $order)

                <li class="hover:dark:bg-gray-800 cursor:pointer">

                    <a href="{{ route('customer.order.show',[$order->customer_id, $order->id]) }}">

                        <div class="flex-start flex items-center pt-2">

                            <div class="-ml-[5px] mr-3 h-[9px] w-[9px] rounded-full bg-neutral-300 dark:bg-neutral-500"></div>

                            <p class="text-sm text-neutral-500 dark:text-neutral-300">

                                {{ date('m/d/Y', strtotime($order->created_at)) }}

                            </p>

                        </div>

                        <div class="mb-6 ml-4 mt-2">

                        <span class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-400 border border-gray-500">

                            Order {{ $order->status }}

                        </span>

                            <p class="mb-3 mt-3 text-neutral-500 dark:text-neutral-300 select-all">

                                {{ $order->id }}

                            </p>

                        </div>

                    </a>

                </li>

            @endforeach

        @endif

    </ol>

</div>

