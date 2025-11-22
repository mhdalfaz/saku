<x-layouts.app title="Pengaturan">

    {{-- PROFILE --}}
    <x-bladewind.card>
        <div class="flex flex-col items-center gap-3">

            <div id="profileAvatar" class="w-14 h-14 rounded-full bg-gray-200 animate-pulse"></div>

            <div id="profileInfo" class="text-center">
                <div class="h-4 bg-gray-200 rounded w-32 mb-2 mx-auto animate-pulse"></div>
                <div class="h-3 bg-gray-200 rounded w-24 mx-auto animate-pulse"></div>
            </div>

        </div>
    </x-bladewind.card>


    {{-- MENU LIST --}}
    <x-bladewind.card>
        <x-bladewind.listview>
            <x-bladewind.listview.item>
                <div class="text-gray-500 text-sm text-center w-full">
                    Belum ada pengaturan.
                </div>
            </x-bladewind.listview.item>
        </x-bladewind.listview>
    </x-bladewind.card>

    {{-- LOGOUT --}}
    <x-bladewind.button name="btnLogout" color="red" class="w-full" onclick="logout()">
        Logout
    </x-bladewind.button>

    <script>
        document.addEventListener("DOMContentLoaded", loadProfile);

        async function loadProfile() {
            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            try {
                const res = await fetch("/api/me", {
                    headers: {
                        "Authorization": "Bearer " + token,
                        "Accept": "application/json"
                    }
                });

                if (!res.ok) return window.location.href = "/login";

                const json = await res.json();

                // Foto atau avatar default
                const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(json.data.name)}&background=0D0D0D&color=fff`;

                document.getElementById("profileAvatar").outerHTML =
                    `<img src="${avatarUrl}" class="w-14 h-14 rounded-full" />`;

                document.getElementById("profileInfo").innerHTML = `
                    <h2 class="text-lg font-semibold">${json.data.name}</h2>
                    <p class="text-sm text-gray-600">${json.data.email}</p>
                `;
            } catch (err) {
                console.error(err);
            }
        }

        function logout() {
            localStorage.removeItem("token");
            window.location.href = "/login";
        }
    </script>

</x-layouts.app>