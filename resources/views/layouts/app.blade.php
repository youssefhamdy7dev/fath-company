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

    <style>
        @media print {

            :root {
                /* --print-scale: 0.75; */
            }

            body * {
                visibility: hidden !important;
            }

            #printArea,
            #printArea * {
                visibility: visible !important;
            }

            #printArea {
                position: absolute;
                top: 0;
                left: 0;
                width: calc(100% / var(--print-scale));
                transform: scale(var(--print-scale));
                transform-origin: top left;
            }

            /* 🔥 FORCE 2 columns layout */
            .row {
                display: flex !important;
                flex-wrap: wrap !important;
            }

            .print-card {
                width: 50% !important;
                /* 2 columns */
                padding: 5px;
            }

            /* 🔥 Keep each block intact */
            .print-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Prevent row breaking */
            tr {
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }

            td,
            th {
                padding: 3px !important;
            }

            h3 {
                margin: 4px 0;
            }
        }
    </style>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // disable mouse wheel on number inputs
            document.querySelectorAll('input[type=number]').forEach(input => {
                input.addEventListener('wheel', function(e) {
                    e.preventDefault();
                });

                // optional: disable arrow keys
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                        e.preventDefault();
                    }
                });
            });

        });
    </script>
</body>

</html>
