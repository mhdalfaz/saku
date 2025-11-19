<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? 'Saku' }}</title>

    @vite('resources/css/app.css')

    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100 text-gray-900">

    {{-- Desktop Navbar --}}
    <x-navbar class="hidden md:block" />

    <div class="flex w-full">
        {{-- Desktop Sidebar --}}
        <x-sidebar class="hidden md:block" />

        {{-- Content --}}
        <main class="flex-1 p-6 pb-24 md:pb-6">
            {{ $slot }}
        </main>
    </div>

    {{-- Mobile Bottom Nav --}}
    <x-mobile-nav class="md:hidden" />

</body>

</html>