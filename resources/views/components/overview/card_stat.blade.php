<div class="bg-white dark:bg-gray-800 shadow-lg rounded-md flex items-center justify-between p-3 border-b-4 border-blue-600 dark:border-gray-600 text-white font-medium group">

    <div class="flex justify-center items-center w-14 h-14 dark:bg-gray-400 rounded-full transition-all duration-300 transform group-hover:rotate-12">

        <svg
            width="30"
            height="30"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            class="stroke-current text-gray-800 dark:text-gray-800 transform transition-transform duration-500 ease-in-out">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                      d="{{ $path }}">

                </path>
        </svg>


    </div>

    <div class="text-right">

        <p class="text-2xl">{{ $label }}</p>

        <p>{{ $value }}</p>

    </div>

</div>
