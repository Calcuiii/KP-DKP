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
        content="@yield('meta_description', 'Si-Molek adalah layanan informasi Kerja Praktik dan Magang Dinas Kelautan dan Perikanan Provinsi Jawa Timur.')"
    >

    <title>@yield('title', 'Si-Molek')</title>

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
