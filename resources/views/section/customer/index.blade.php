<x-layout>

    <x-breadcrumb
        :steps="[route('customer.list') => 'customers']"
        lastStep="{{ $customer->name }}"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 justify-evenly pb-12">

        @include('section.customer.partials.profile', ['customer' => $customer])

        {{-- Orders summary timeline --}}

        <div class="overflow-hidden h-64 overflow-y-auto">

            @include('section.customer.partials.order', ['orders' => $orders])

        </div>

    </div>

    @include('section.customer.partials.segment', ['customer' => $customer])

</x-layout>

