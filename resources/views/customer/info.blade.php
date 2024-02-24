<x-layout>

    <x-breadcrumb
        :steps="[route('customer.list') => 'customers']"
        lastStep="{{ $customer->name }}"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 justify-evenly border-b dark:border-gray-700 pb-12">

        <div class="">

            <span class="flex items-center text-xl font-semibold text-gray-900 dark:text-white me-3">

                <span class="flex w-2.5 h-2.5 bg-teal-500 rounded-full me-1.5 flex-shrink-0"></span>

                {{ $customer->name }}

            </span>

            <div class="flex items-center mt-4 text-gray-700 dark:text-gray-200">

                <x-icon
                    label="customer id"
                    path="M3.6 6.4 12 13l8.7-6.6L13 2.3a2 2 0 0 0-2 0l-7.4 4Z"
                    second-path="m22 8-8.8 6.7a2 2 0 0 1-2.4 0L2 7.7v11.2A3 3 0 0 0 5 22h14a3 3 0 0 0 3-3V8Z"
                />

                <span class="px-2 text-sm truncate">{{ $customer->email }}</span>

            </div>

            <div class="flex items-center mt-4 text-gray-700 dark:text-gray-200">

                <x-icon
                    label="customer id"
                    path="M7 2a2 2 0 0 0-2 2v1a1 1 0 0 0 0 2v1a1 1 0 0 0 0 2v1a1 1 0 1 0 0 2v1a1 1 0 1 0 0 2v1a1 1 0 1 0 0 2v1c0 1.1.9 2 2 2h11a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H7Zm3 8a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm-1 7a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3c0 .6-.4 1-1 1h-6a1 1 0 0 1-1-1Z"/>

                <span class="px-2 text-sm select-all">{{ $customer->id }}</span>

            </div>

            <div class="flex items-center mt-4 text-gray-700 dark:text-gray-200 overflow-hidden">

                <x-icon
                    label="customer address"
                    path="M12 2a8 8 0 0 1 6.6 12.6l-.1.1-.6.7-5.1 6.2a1 1 0 0 1-1.6 0L6 15.3l-.3-.4-.2-.2v-.2A8 8 0 0 1 11.8 2Zm3 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>

                <span class="px-2 text-sm truncate">

                    {{ $customer->street }}
                    {{ $customer->postal_code }}
                    {{ $customer->city }}
                    {{ $customer->country }}

                </span>

            </div>

        </div>

        {{-- Orders summary timeline --}}

        <div class="overflow-hidden h-64 overflow-y-auto">

            <x-timeline.order :orders="$orders"/>

        </div>

    </div>

</x-layout>

