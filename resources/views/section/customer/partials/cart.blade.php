{{-- Customer cart --}}

<div class="pl-3">

    <div class="flex items-center gap-4 mb-6">

        <div class="relative w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">

            <svg class="absolute w-12 h-12 text-gray-400 -left-2.5"
                 fill="currentColor"
                 viewBox="0 0 20 20"
                 xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.3L19 7H7.3"
                      clip-rule="evenodd">

                </path>
            </svg>

        </div>

        <div class="font-medium dark:text-white">

            <span>Cart</span>

        </div>

    </div>

    <div>

        @if(isset($cart))

            <x-two_col_info label="Id" :value="$cart->id"/>
            <x-two_col_info label="balance" :value="$cart->balance"/>
            <x-two_col_info label="quantity" :value="$cart->quantity"/>
            <x-two_col_info label="items" :value="$cart->items->count()"/>
            <x-two_col_info label="status" :value="$cart->status"/>

            <a href="{{ route('customer.cart.view',[$cart->customer_id, $cart->id]) }}" class="inline-block sm:mr-8">

                <button type="button"
                        class="inline-block text-gray-700 mt-6 hover:text-white border border-gray-700 hover:bg-zinc-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800">

                    View cart

                </button>

            </a>

            <a href="{{ route('customer.cart.history', [$cart->customer_id, $cart->id]) }}" class="inline-block">

                <button type="button"
                        class="text-gray-700 mt-6 hover:text-white border border-gray-700 hover:bg-zinc-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800">

                    Cart history

                </button>

            </a>

        @else

            <x-empty_content text="No cart"/>

        @endif

    </div>

</div>


