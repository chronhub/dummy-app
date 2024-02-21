<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Shop</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="antialiased bg-gray-50 dark:bg-gray-900">

<div class="antialiased h-lvh overflow-hidden">

    <x-navbar/>

    <x-sidebar/>

    <main class="p-12 md:ml-64 pt-20 dark:text-gray-300">

        {{ $slot }}

    </main>

</div>

</body>

</html>
