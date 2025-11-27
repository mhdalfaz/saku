<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? 'Saku' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    
    {{-- Styles --}}
    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-900">

    {{-- Desktop Navbar --}}
    <div id="desktopNav">
        <x-navbar />
    </div>

    <div class="flex w-full">
        {{-- Content --}}
        <main class="flex-1 p-6">
            <div class="max-w-xl mx-auto space-y-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Modal --}}
    @yield('modals')

    {{-- Scripts --}}
    @stack('scripts')
</body>

</html>