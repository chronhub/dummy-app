<div>

    <div class="flex items-center gap-4 mb-6">

        <div class="relative w-10 h-10 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-600">

            <svg class="absolute w-12 h-12 text-gray-400 -left-1"
                 fill="currentColor"
                 viewBox="0 0 20 20"
                 xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>

        </div>

        <div class="font-medium dark:text-white">

            <div>

                {{ $customer->name }}

            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">

                Joined in {{ date('F Y', strtotime($customer->created_at)) }}

            </div>

        </div>

    </div>

    <x-two_col_info label="Gender" :value="$customer->gender"/>

    <x-two_col_info label="full name" :value="$customer->name"/>

    <x-two_col_info label="birthday" :value="$customer->birthday"/>

    <x-two_col_info label="email" :value="$customer->email"/>

    <x-two_col_info label="phone" :value="$customer->phone_number"/>

    <x-two_col_info label="id" :value="$customer->id"/>


    <div class="flex mt-6 ml-3 underline underline-offset-8">

        Main address

    </div>

    <x-two_col_info label="street" :value="$customer->street"/>

    <x-two_col_info label="postal code" :value="$customer->postal_code"/>

    <x-two_col_info label="city" :value="$customer->city"/>

    <x-two_col_info label="country" :value="$customer->country"/>

</div>
