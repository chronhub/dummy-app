@if($type === 'add' && !$isInCart($product->id))

    <a
        href="{{ route('customer.cart.add', [$cart->customer_id, $cart->id, $product->id, rand(1,5)]) }}">

        <button
            class="absolute bottom-0 align-middle select-none font-sans font-bold text-center uppercase transition-all text-xs py-3 px-6 rounded-lg shadow-gray-900/10 hover:shadow-gray-900/20 focus:opacity-[0.85] active:opacity-[0.85] active:shadow-none block w-full bg-blue-gray-900/10 text-blue-gray-900 shadow-none hover:scale-105 hover:shadow-none focus:scale-105 focus:shadow-none active:scale-100"
            type="button">
            Add to Cart
        </button>

    </a>

@endif

@if($type === 'remove' && $isInCart($product->id))

    <a
        href="{{ route('customer.cart.remove', [$cart->customer_id, $cart->id, $cartItemIdOfSku($product->id), $product->id]) }}">

            <button
                class="align-middle select-none font-bold text-center uppercase transition-all text-xs py-3 px-6 rounded-lg shadow-gray-900/10 hover:shadow-gray-900/20 focus:opacity-[0.85] active:opacity-[0.85] active:shadow-none block w-full bg-blue-gray-900/10 text-blue-gray-900 shadow-none hover:scale-105 hover:shadow-none focus:scale-105 focus:shadow-none active:scale-100"
                type="button">
                Remove from Cart ( +{{ $cartItemQuantity($product->id) }} )
            </button>
    </a>

@endif


@if($type === 'update' && $isInCart($product->id))
    <div class="flex items-center justify-between mb-2">

        <div class="">

            <a
                href="{{ route('customer.cart.update', [$cart->customer_id, $cart->id, $cartItemIdOfSku($product->id), $product->id, $cartItemQuantity($product->id) + 1]) }}"

                class="inline-block px-2 text-2xl font-extrabold dark:text-gray-200 border border-gray-600 rounded active:text-zinc-500 hover:bg-white hover:text-zinc-600 focus:outline-none focus:ring">

                +
            </a>

        </div>

        @if($cartItemQuantity($product->id) > 1)

            <div class="">

                <a
                    href="{{ route('customer.cart.update', [$cart->customer_id, $cart->id, $cartItemIdOfSku($product->id), $product->id, $cartItemQuantity($product->id) - 1]) }}"

                    class="inline-block px-2 text-2xl font-extrabold dark:text-gray-200 border border-gray-600 rounded active:text-zinc-500 hover:bg-white hover:text-zinc-600 focus:outline-none focus:ring">

                    -
                </a>

            </div>

        @endif

    </div>

@endif

