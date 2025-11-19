<x-layouts.app title="Home">

    <script>
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = "/login";
        }
    </script>

    <h1 class="text-2xl font-bold">Home</h1>

</x-layouts.app>
