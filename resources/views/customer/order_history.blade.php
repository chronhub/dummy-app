<x-layout>

    <x-breadcrumb
        :steps="[
             route('customer.list') => 'customers',
             route('customer.info.show', $customer_id) => $customer->name,
             route('customer.order.show', [$customer_id, $order_id]) => 'order #'. substr($order_id, 0, 8),
        ]"
        lastStep="history"
    />

    <div class="ml-6">

        <ol class="relative border-s border-gray-200 dark:border-gray-700">

            @foreach($orderHistory as $event)

                <li class="mb-10 ms-8">

                <span class="absolute flex items-center justify-center w-8 h-8 text-xs font-extrabold bg-blue-100 rounded-full -start-4 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">

                    <svg
                        class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor"
                        viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                    </svg>

                </span>

                    <h3 class="flex items-center mb-1 text-lg font-bold text-gray-900 dark:text-white">

                        {{ class_basename(\Illuminate\Support\Str::snake($event->header('__event_type'), ' ')) }}

                        <span class="bg-pink-100 text-pink-800 text-xs font-extrabold me-2 px-2.5 py-0.5 rounded dark:bg-pink-900 dark:text-pink-300 ms-3">

                        v{{ $event->header('__aggregate_version') }}

                    </span>

                    </h3>

                    <time class="block mb-2 text-xs font-normal leading-none text-gray-400 dark:text-gray-500">

                        {{ date('m-d-m-Y H:i:s', strtotime($event->header('__event_time'))) }}

                    </time>

                    @foreach($event->content as $key => $value)

                        <div class="flex mt-3">

                            <div class="flex-none w-48 ms-4">

                                <p class="text-sm text-gray-900 dark:text-white underline decoration-dashed underline-offset-8 decoration-white">

                                    {{ $key }}

                                </p>

                            </div>

                            <span class="flew-shrink w-auto bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-800 dark:text-gray-300">

                            @if(is_array($value))

                                    @json($value, JSON_PRETTY_PRINT)

                                @else

                                    {{ $value }}

                                @endif

                        </span>

                        </div>

                    @endforeach

                    <a href="#" class="inline-flex items-center mt-6 px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:outline-none focus:ring-gray-100 focus:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"><svg class="w-3.5 h-3.5 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M14.707 7.793a1 1 0 0 0-1.414 0L11 10.086V1.5a1 1 0 0 0-2 0v8.586L6.707 7.793a1 1 0 1 0-1.414 1.414l4 4a1 1 0 0 0 1.416 0l4-4a1 1 0 0 0-.002-1.414Z"/>
                            <path d="M18 12h-2.55l-2.975 2.975a3.5 3.5 0 0 1-4.95 0L4.55 12H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2Zm-3 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/>
                        </svg>

                        View as JSON

                    </a>

                </li>

            @endforeach

        </ol>

    </div>

</x-layout>
