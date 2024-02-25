<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Shop</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css"/>

</head>

<body class="antialiased bg-gray-50 dark:bg-zinc-900">

<div class="antialiased">

    <x-layout.navbar/>

    <x-layout.sidebar/>

    <main class="px-8 mt-20 md:ml-64 dark:text-gray-300 relative">

        {{ $slot }}

    </main>

</div>

</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>

</html>
