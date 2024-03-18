<x-layout>

    <x-breadcrumb lastStep="products"/>

    <x-layout.main>

        @include('section.catalog.partials.catalog_header_table')

        @include('section.catalog.partials.catalog_table', ['catalog' => $catalog])

    </x-layout.main>

</x-layout>
