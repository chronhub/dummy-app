{{-- Product Table --}}

<div class="relative shadow-md">

    <table class="w-full table-fixed text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">

        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-700 dark:text-gray-400">

        <tr>

            <th scope="col" class="px-6 py-3">
                Sku
            </th>

            <th scope="col" class="px-6 py-3">
                Name
            </th>

            <th scope="col" class="px-6 py-3">
                Price
            </th>

            <th scope="col" class="px-6 py-3">
                Quantity
            </th>

            <th scope="col" class="px-6 py-3">
                Reserved
            </th>

            <th scope="col" class="px-6 py-3">
                Status
            </th>

            <th scope="col" class="px-6 py-3">
                Action
            </th>
        </tr>

        </thead>

        <tbody>

            @foreach($catalog->items() as $product)

                <tr class="odd:bg-white odd:dark:bg-zinc-900 even:bg-zinc-50 even:dark:bg-zinc-800 border-b dark:border-zinc-600">

                    <td class="px-6 py-4 select-all">
                        {{ $product->sku_code }}
                    </td>

                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $product->name }}
                    </th>

                    <td class="px-6 py-4">
                        {{ $product->current_price }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $product->quantity }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $product->reserved }}
                    </td>

                    <td class="px-6 py-4">

                        <span class="bg-green-100 text-green-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">

                            {{ $product->status }}

                        </span>

                    </td>

                    <td class="px-6 py-4">

                        <a href="#" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">

                            View

                        </a>

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

    {{-- Pagination --}}

    <nav class="m-2 p-4">

        {{ $catalog->links() }}

    </nav>

</div>
