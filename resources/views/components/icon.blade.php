<svg
    class="w-{{ $width }} h-{{ $height }} {{ $color }} dark:{{ $dark }}"
    fill="currentColor"
    aria-label="{{ $label }}"
    aria-hidden="true"
    viewBox="{{ $viewBox }}"
    xmlns="http://www.w3.org/2000/svg">
    <path
        fill-rule="evenodd"
        clip-rule="evenodd"
        d="{{ $path }}"
    >
    </path>

    @if ($secondPath)
        <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="{{ $secondPath }}"
        >
        </path>
    @endif

</svg>
