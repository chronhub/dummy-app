<x-layout>

    {{-- Breadcrumb --}}

    <x-breadcrumb
        :steps="[route('customer.list') => 'customers' , route('customer.info.show', $cart->customer_id) => $customer->name]"
        lastStep="cart #{{ substr($cart->id, 0, 8) }}"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 border-b dark:border-gray-700 pb-12">

        <div class="text-sm">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 justify-between gap-4">

                @if(!isset($catalog) || $catalog->isEmpty())

                    <p>No catalog item</p>

                @else

                    @foreach($catalog as $item)

                        <div class="relative flex flex-col text-gray-700 dark:text-gray-300 shadow-md bg-clip-border h-auto border-b border-gray-600 min-h-32">

                            <div class="px-2">

                                <div class="flex items-center justify-between mb-2 px-2 bg-gray-400 dark:bg-zinc-800">

                                    <p class="block antialiased leading-relaxed">
                                        Product #{{ substr($item->id, 0, 8) }}
                                    </p>

                                    <p class="block leading-relaxed">
                                        ${{ $item->unit_price }}
                                    </p>

                                </div>

                                <p class="block leading-normal">
                                    ipsum dolor sit amet, consectetur adipiscing elit.
                                </p>

                            </div>

                            <div class="mt-3">

                                <x-product_card type="update" :product="$item" :cart="$cart"></x-product_card>

                                <x-product_card type="add" :product="$item" :cart="$cart"></x-product_card>

                                <x-product_card type="remove" :product="$item" :cart="$cart"></x-product_card>

                            </div>

                        </div>

                    @endforeach

                @endif

            </div>

        </div>

        {{-- Cart --}}

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

                    <div>Cart of {{ $customer->name }}</div>

                    <div class="text-sm text-gray-500 dark:text-gray-400">

                        created at {{ date('F d Y', strtotime($cart->created_at)) }}

                    </div>

                </div>

            </div>

            <x-two_col_info label="status" :value="$cart->status"/>

            <x-two_col_info label="balance" :value="$cart->balance"/>

            <x-two_col_info label="quantity" :value="$cart->quantity"/>

            <x-two_col_info label="id" :value="$cart->id"/>

            <div class="mt-6">

                @if($cart->items->count() > 0)

                    <a href="javascript:alert('checkout')"
                       class="w-full inline-block text-center px-4 py-2 text-xs font-medium text-white bg-violet-600 rounded active:bg-violet-500 hover:bg-violet-500 focus:outline-none focus:ring">
                        Checkout
                    </a>

                @endif

                <div>

                    @if($cart->items->IsEmpty())

                        <p>No items in the cart</p>

                    @else

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

                            @each('section.customer.partials.cart_items', $cart->items, 'item')

                            </tbody>

                        </table>

                    @endif

                </div>

            </div>

        </div>

    </div>

</x-layout>


