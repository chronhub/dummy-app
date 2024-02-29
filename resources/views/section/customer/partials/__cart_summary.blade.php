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

            <div>

                Cart of {{ $customer->name }}

            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">

                created at {{ date('F d Y', strtotime($cart->created_at)) }}

            </div>

        </div>

    </div>

    <div class="mb-6">

        <x-two_col_info label="status" :value="$cart->status"/>

        <x-two_col_info label="balance" :value="$cart->balance"/>

        <x-two_col_info label="quantity" :value="$cart->quantity"/>

        <x-two_col_info label="id" :value="$cart->id"/>

    </div>

    @if($cart->items->count() > 0)

        <div class="mb-6">

            <a href="javascript:alert('checkout')"
               class="w-full inline-block text-center px-4 py-2 text-xs font-medium text-white bg-violet-600 rounded active:bg-violet-500 hover:bg-violet-500 focus:outline-none focus:ring">
                Checkout
            </a>

        </div>

    @endif

    <div class="mb-6">

        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">

            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-700 dark:text-gray-400">

            <tr>
                <th scope="col" class="px-6 py-3">

                    Sku

                </th>

                <th scope="col" class="px-6 py-3">

                    Price

                </th>

                <th scope="col" class="px-6 py-3">

                    Quantity

                </th>

            </tr>

            </thead>

            <tbody>

                @if($cart->items->IsEmpty())

                    <tr>

                        <td colspan="3">

                            <x-empty_content text="No item in cart"/>

                        </td>

                    </tr>

                @else

                    @each('section.customer.partials.cart_items', $cart->items, 'item')

                @endif

            </tbody>

        </table>

    </div>

</div>
