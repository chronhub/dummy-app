{{-- Customer order items  --}}

<div class="pl-3">

    <span class="flex items-center text-xl font-semibold text-gray-900 dark:text-white me-3">

        <span class="flex w-2.5 h-2.5 bg-teal-500 rounded-full me-1.5 flex-shrink-0"></span>

        {{ $orderItems->count() }} items

    </span>

    @if($orderItems->isNotEmpty())

        <ol class="mt-4 border-l border-neutral-300 dark:border-neutral-500">

            @foreach($orderItems as $orderItem)

                <li class="hover:dark:bg-gray-800 cursor:pointer text-sm">

                    <a href="#">

                        <div class="flex-start flex items-center pt-2">

                            <div
                                class="-ml-[5px] mr-3 h-[9px] w-[9px] rounded-full bg-neutral-300 dark:bg-neutral-500"></div>

                            <p class="text-sm text-neutral-500 dark:text-neutral-300">

                                {{ date('m/d/Y H:i:s', strtotime($orderItem->created_at)) }}

                            </p>

                        </div>

                        <div class="mb-3 ml-4 pt-2 pb-2">

                            <span
                                class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-400 border border-gray-500">

                                #SKU {{ $orderItem->sku_id }}

                            </span>

                            <p class="mt-3">

                                <span class="mt-3 w-auto bg-pink-100 text-pink-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-pink-900 dark:text-pink-300">

                                    #UP {{ $orderItem->unit_price }}

                                </span>

                                <span class="mt-3 w-auto bg-pink-100 text-pink-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-pink-900 dark:text-pink-300">

                                    #Q {{ $orderItem->quantity }}

                                </span>

                            </p>

                        </div>

                    </a>

                </li>

            @endforeach

        </ol>

    @endif

</div>


