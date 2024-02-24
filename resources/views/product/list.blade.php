<x-layout>

    <x-breadcrumb lastStep="products"/>

    <x-product_header_table></x-product_header_table>

    <x-product_table :products="$products"></x-product_table>

    <div class="m-2 p-4">

        {{ $products->links() }}

    </div>

</x-layout>
