<tr class="odd:bg-white odd:dark:bg-zinc-900 even:bg-zinc-50 even:dark:bg-zinc-800 border-b dark:border-zinc-700">

    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">

        #{{ substr($item->sku_id,0,8) }}

    </th>

    <td class="px-6 py-4">

        ${{ $item->price }}

    </td>

    <td class="px-6 py-4">

        {{ $item->quantity }}

    </td>

</tr>
