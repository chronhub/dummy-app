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
