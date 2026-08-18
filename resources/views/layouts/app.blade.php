<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="@yield('meta_description', 'SI-MELAYUR adalah Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur milik Dinas Kelautan dan Perikanan Provinsi Jawa Timur.')"
    >

    <title>@yield('title', 'SI-MELAYUR')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body>
    @yield('content')

    @hasSection('hide_dev_nav')
    @else
        @include('components.dev-nav')
    @endif

    @stack('scripts')
</body>
</html>
