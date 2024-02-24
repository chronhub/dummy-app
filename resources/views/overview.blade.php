<x-layout>

    <x-breadcrumb lastStep="Overview"/>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 justify-between">

        <x-overview.stats :order="$order" :inventory="$inventory"></x-overview.stats>

    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 justify-between mt-8">

        <x-overview.last_ten_customers :lastTenCustomers="$lastTenCustomers"></x-overview.last_ten_customers>

    </div>

</x-layout>
