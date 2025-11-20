<x-layouts.auth title="Login">

    <div class="flex justify-center items-center min-h-screen">
        <div class="w-full max-w-sm bg-white p-6 rounded-xl shadow">

            <h1 class="text-xl font-bold mb-4 text-center">Masuk ke Saku</h1>

            <div id="error" class="hidden mb-3 p-3 rounded bg-red-200 text-red-700 text-sm"></div>

            <form id="loginForm">
                <div class="mb-4">
                    <label class="block text-sm mb-1">Email</label>
                    <x-bladewind.input type="email" name="email" required/>
                </div>

                <div class="mb-4">
                    <label class="block text-sm mb-1">Password</label>
                    <x-bladewind.input type="password" viewable="true" name="password" required/>
                </div>

                <x-bladewind.button can_submit="true" id="loginBtn" class="w-full" disabled>
                    Login
                </x-bladewind.button>
            </form>

        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const errorBox = document.getElementById('error');

        form.addEventListener('input', () => {
            const isValid = form.checkValidity();
            document.getElementById('loginBtn').disabled = !isValid;
        });
        
        form.addEventListener('submit', async (e) => {
            if (!form.checkValidity()) return;
            e.preventDefault();
            console.log("Submitting login form...");

            const formData = {
                email: form.email.value,
                password: form.password.value,
            };

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (!response.ok) {
                    errorBox.textContent = result.message || "Login gagal";
                    errorBox.classList.remove('hidden');
                    return;
                }

                // Simpan token di localStorage
                localStorage.setItem('token', result.data.token);

                // Redirect
                window.location.href = '/home';

            } catch (err) {
                errorBox.textContent = "Terjadi kesalahan";
                errorBox.classList.remove('hidden');
            }
        });
    </script>

</x-layouts.auth>