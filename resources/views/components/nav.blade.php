<div class="sm:fixed sm:top-0 sm:left-0 p-6 text-left z-10">

    <a href="{{ route('home') }}"
       class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
        Home
    </a>

    <a href="{{ route('customer.list') }}"
       class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
        Customers
    </a>

    @if(request()->routeIs('customer.order.show') && isset($customer_id))

        <a href="{{ route('customer.info.show', $customer_id) }}"
           class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
            Return to customer info
        </a>

    @endif

</div>
