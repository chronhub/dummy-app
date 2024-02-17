<x-layout>

    <div class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">

        <div>

            @if($customers->isEmpty())

                <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">no customer registered</h2>

            @else
                <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Customers {{ $customers->count() }}</h2>

            <div class="mt-6 text-gray-900 dark:text-white">

                @foreach($customers as $customer)

                    <a href="{{ route('customer.info.show',[$customer->id]) }}">
                        <p class="mt-6">{{ $customer->name }}</p>
                    </a>

                @endforeach

            </div>

            @endif

        </div>

    </div>

</x-layout>
