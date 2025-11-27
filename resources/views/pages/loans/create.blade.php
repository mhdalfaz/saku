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
            <x-bladewind::filepicker name="attachments" max_file_size="5mb" max_files="10" />
            <p id="error-attachments" class="text-red-600 text-sm alert-error"></p>
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

            const fd = new FormData();

            fd.append("borrower_id", form.borrower_id.value);
            fd.append("amount", parseInt(unformatIDR(form.amount.value)));
            fd.append("date", form.date.value);
            fd.append("note", form.note.value);

            const allInputs = form.querySelectorAll('input[name="attachments"]');

            allInputs.forEach(input => {
                for (let i = 0; i < input.files.length; i++) {
                    fd.append("attachments[]", input.files[i]);
                }
            });

            try {
                const res = await fetch("/api/loans", {
                    method: "POST",
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    body: fd
                });

                const json = await res.json();

                if (!res.ok) {
                    Object.keys(json.errors).forEach(field => {
                        const cleanField = field.replace(/\.\d+$/, "");
                        setBladewindInputError(cleanField, json.errors[field][0]);
                    });
                    return;
                }

                alert("Pinjaman berhasil ditambahkan!");
                window.location.reload();

            } catch (err) {
                console.error(err);
                alert("Terjadi kesalahan, coba lagi.");
            }
        });
    </script>
</x-layouts.menu>