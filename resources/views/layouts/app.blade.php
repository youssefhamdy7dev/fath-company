<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>شركة الفتح | لتجارة الفواكه</title>

    {{-- Website Icon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    {{-- Vite compiled assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Google Fonts (Arabic-friendly) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&family=Reem+Kufi+Fun:wght@400..700&display=swap"
        rel="stylesheet">
</head>

<body>
    {{-- Navbar --}}
    @include('partials.header')

    {{-- Main content --}}
    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>
    @stack('modals')
    @stack('scripts')
</body>

</html>
