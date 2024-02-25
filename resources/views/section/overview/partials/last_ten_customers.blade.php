<div>

    <div class="block py-2 px-4 text-base font-extrabold text-center text-zinc-700 bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-300">

        Last ten customers

    </div>

    <div>

        @foreach($lastTenCustomers as $customer)

            <a href="{{ route('customer.info.show',[$customer->id]) }}"
               class="flex py-3 px-4 border-b hover:bg-gray-100 dark:hover:bg-zinc-800 dark:border-zinc-600">

                <div class="flex-shrink-0">

                    <div class="relative w-8 h-8 bg-gray-100 rounded-full dark:bg-gray-600">

                        <svg class="absolute w-10 h-10 text-gray-400 -left-1"
                             fill="currentColor"
                             viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                      clip-rule="evenodd">
                                </path>
                        </svg>

                        <span class="top-0 left-6 absolute w-3.5 h-3.5 bg-green-600 border-2 border-white dark:border-gray-800 rounded-full"></span>

                    </div>

                </div>

                <div class="pl-3 w-full">

                    <div class="text-gray-500 font-normal text-sm mb-1.5 dark:text-gray-400">

                        <span class="font-semibold text-gray-900 dark:text-white">

                            {{ $customer->name }}

                        </span>

                    </div>

                    <div class="text-gray-500 font-normal text-xs mb-1.5 dark:text-gray-400">

                        <span class="text-gray-900 dark:text-gray-400">

                            {{ $customer->country }}

                        </span>

                    </div>

                    <div class="text-xs font-medium text-primary-600 dark:text-primary-500">

                        {{ date('F j, Y', strtotime($customer->created_at)) }}

                    </div>

                </div>

            </a>

        @endforeach

    </div>

    <a
        href="{{ route('customer.list') }}"
        class="block py-2 text-md font-medium text-center text-gray-900 bg-gray-50 hover:bg-zinc-900 dark:bg-zinc-800 dark:text-white dark:hover:underline"
    >
        <div class="inline-flex items-center">

            <svg
                aria-hidden="true"
                class="mr-2 w-4 h-4 text-gray-500 dark:text-gray-400"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>

                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                />
            </svg>

            View all

        </div>

    </a>

</div>


