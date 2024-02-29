<x-layout>

    <x-breadcrumb lastStep="products"/>

    <x-layout.main>

        @include('section.product.partials.product_header_table')

        @include('section.product.partials.product_table', ['products' => $products])

    </x-layout.main>

</x-layout>
