<x-layout>

    <x-breadcrumb lastStep="Overview"/>

    <x-layout.main>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 justify-between">

            @include('section.overview.partials.stats', [$order, $inventory])

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 justify-between mt-8">

            @include('section.overview.partials.last_ten_customers', [$lastTenCustomers])

        </div>

    </x-layout.main>

</x-layout>
