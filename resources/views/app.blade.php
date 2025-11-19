<!DOCTYPE html>
<html lang="en">

<head>
    @vite('resources/css/app.css')
    <!-----------------------------------------------------------
    -- animate.min.css by Daniel Eden (https://animate.style)
    -- is required for the animation of notifications and slide out panels
    -- you can ignore this step if you already have this file in your project
    --------------------------------------------------------------------------->

    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="p-8 bg-gray-100">

    <h1 class="text-2xl mb-4 font-bold">BladewindUI Test</h1>

    <x-bladewind::button>Save User</x-bladewind::button>
    <x-bladewind.button>Save User</x-bladewind.button>

    <x-bladewind::card class="mt-4">
        Contoh card dari BladewindUI.
    </x-bladewind::card>

    <x-bladewind::table divider="thin">
        <x-slot name="header">
            <th>Name</th>
            <th>Department</th>
            <th>Email</th>
        </x-slot>
        <tr>
            <td>Alfred Rowe</td>
            <td>Outsourcing</td>
            <td>alfred@therowe.com</td>
        </tr>
        <tr>
            <td>Michael K. Ocansey</td>
            <td>Tech</td>
            <td>kabutey@gmail.com</td>
        </tr>
    </x-bladewind::table>
</body>

</html>