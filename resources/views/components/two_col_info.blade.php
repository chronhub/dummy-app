<div class="flex mt-3">

    <div class="flex-none w-36 ms-4 py-1">

        <p class="text-xs text-gray-900 dark:text-gray-400 font-semibold uppercase">

            {{ $label }}

        </p>

    </div>

    <span class="flex-shrink w-auto bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded dark:bg-gray-800 dark:text-gray-300">

        @if(is_array($value))

            @json($value, JSON_PRETTY_PRINT)

        @else

            {{ $value }}

        @endif

    </span>

</div>
