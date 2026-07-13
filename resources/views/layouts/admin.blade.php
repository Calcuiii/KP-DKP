<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - DKP Assistant')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/charts.js'])
    @stack('styles')
</head>
<body class="bg-background">
    <div class="flex h-screen overflow-hidden">
        <x-admin.sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-admin.topbar :title="$title ?? 'Dashboard'" />

            <main class="flex-1 overflow-y-auto p-5">
                @yield('content')
            </main>
        </div>
    </div>

    @include('components.dev-nav')

    @stack('scripts')
</body>
</html>