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

    <script>
        (function () {
            try {
                var token = localStorage.getItem('token');
                var path = window.location.pathname;
                if (token && (path === '/login' || path === '/register')) {
                    window.location.href = '/home';
                    return;
                }

                if (!token && (path !== '/login' && path !== '/register')) {
                    window.location.href = "/login";
                }
            } catch (e) {
                // ignore
            }
        })();
    </script>
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
    <div id="mobileNav" class="hidden md:hidden">
        <x-mobile-nav />
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const token = localStorage.getItem("token");
            console.log("Token:", token);

            const mobileNav = document.getElementById("mobileNav");

            if (token && mobileNav) {
                mobileNav.classList.remove("hidden");
            }
        });
    </script>
</body>

</html>