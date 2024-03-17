<x-layout>

    <x-breadcrumb lastStep="Customers"/>

    <x-layout.main>

        @include('section.catalog.partials.catalog_header_table')

        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">

            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-zinc-700 dark:text-gray-400">

            <tr>
                <th scope="col" class="px-6 py-3">
                    Name
                </th>

                <th scope="col" class="px-6 py-3">
                    Email
                </th>

                <th scope="col" class="px-6 py-3">
                    City
                </th>

                <th scope="col" class="px-6 py-3">
                    Country
                </th>

                <th scope="col" class="px-6 py-3">
                    Action
                </th>

            </tr>

            </thead>

            <tbody>

            @foreach($customers->items() as $customer)

                <tr class="odd:bg-white odd:dark:bg-zinc-900 even:bg-zinc-50 even:dark:bg-zinc-800 border-b dark:border-zinc-700">

                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $customer->name }}
                    </th>

                    <td class="px-6 py-4">
                        {{ $customer->email }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $customer->city }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $customer->country }}
                    </td>

                    <td class="px-6 py-4">
                        <a
                                href="{{ route('customer.info.show', $customer->id) }}"
                                class="font-medium text-blue-600 dark:text-blue-400 hover:underline">View
                        </a>
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

        <div class="m-2 p-4">

            {{ $customers->links() }}

        </div>

    </x-layout.main>

</x-layout>
