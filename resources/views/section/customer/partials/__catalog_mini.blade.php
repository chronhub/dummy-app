<div class="text-sm">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 justify-between gap-4">

        @if(!isset($catalog) || $catalog->isEmpty())

            <x-empty_content text="No product available"/>

        @else

            @foreach($catalog as $item)

                @include('section.customer.partials.__catalog_item_mini', [$customer, $cart, $item])

            @endforeach

        @endif

    </div>

</div>
