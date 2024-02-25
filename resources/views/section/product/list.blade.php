<x-layout>

    <x-breadcrumb lastStep="products"/>

    @include('section.product.partials.product_header_table')

    @include('section.product.partials.product_table', ['products' => $products])

</x-layout>
