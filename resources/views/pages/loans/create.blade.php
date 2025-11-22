<x-layouts.menu title="Tambah Pinjaman">

    <h1 class="text-2xl font-bold mb-4">Tambah Pinjaman</h1>

    <form id="loanForm" class="space-y-5">

        <x-loans.borrower-input :borrowers="$borrowers" />

        <x-bladewind::card>
            <label class="block text-sm font-medium mb-1">Jumlah (IDR)</label>
            <x-bladewind.input type="text" name="amount" placeholder="100000" required
                oninput="this.value = formatIDR(this.value)" value="0" />
            <p id="error-amount" class="text-red-600 text-sm alert-error"></p>

            <label class="block text-sm font-medium mb-1">Tanggal Pinjaman</label>
            <x-bladewind.input type="date" name="date" required />
            <p id="error-date" class="text-red-600 text-sm alert-error"></p>

            <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
            <x-bladewind.textarea name="note" placeholder="Misal: Pinjaman untuk..." />
            <p id="error-note" class="text-red-600 text-sm alert-error"></p>
        </x-bladewind::card>

        <x-bladewind::card class="mb-[70px]">
            // upload files atau images
        </x-bladewind::card>

        <div class="fixed bottom-0 left-0 w-full bg-white py-4 px-6 border-t shadow-md">
            <div class="max-w-xl mx-auto">
                <x-bladewind.button can_submit="true" id="submitBtn" class="w-full">Tambah Pinjaman</x-bladewind.button>
            </div>
        </div>

    </form>

    <script>
        const form = document.getElementById("loanForm");

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            resetBladewindInputError();

            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            const data = {
                borrower_id: form.borrower_id.value,
                amount: parseInt(unformatIDR(form.amount.value)),
                date: form.date.value,
                note: form.note.value
            };

            try {
                const res = await fetch("/api/loans", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + token
                    },
                    body: JSON.stringify(data)
                });

                const json = await res.json();

                if (!res.ok) {
                    const errors = json.errors;

                    // set new errors
                    Object.keys(errors).forEach(field => {
                        setBladewindInputError(field, errors[field][0]);
                    });

                    return;
                }

                alert("Pinjaman berhasil ditambahkan!");
                form.reset();

            } catch (err) {
                console.error(err);
                alert("Terjadi kesalahan, coba lagi.");
            }
        });
    </script>

    {{--
    <script>
        console.log("SCRIPT LOADED");
        document.addEventListener('DOMContentLoaded', () => {
            waitForSelect();
        });

        function waitForSelect() {
            // kalau select belum siap, tunggu 100ms
            if (!window.bladeWindSelects || !window.bladeWindSelects['borrowerSelect']) {
                return setTimeout(waitForSelect, 100);
            }

            loadBorrowers();
        }

        function loadBorrowers() {
            fetch('/api/borrowers', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem("token")
                }
            })
                .then(response => response.json())
                .then((d) => {
                    console.log('logging borrowers data:');
                    console.log(d.data);

                    const bwSelect = window.bladeWindSelects['borrowerSelect'];

                    bwSelect.populate(d.data);  // FIXED
                })
                .catch(error => console.error(error));
        }
    </script> --}}
</x-layouts.menu>