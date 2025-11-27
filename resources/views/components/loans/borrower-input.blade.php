@push('styles')
    <style>
        /* Full width modal untuk layar < 640px (mobile) */
        @media (max-width: 639px) {
            .bw-new-borrower {
                width: 95% !important;
                /* atau 100% */
                max-width: 95% !important;
            }
        }
        .sticky.top-0.min-w-full.bg-gray-100.dark\:bg-transparent.py-1.pr-0.-pl-1.search-bar {
            z-index: 50;
        }
    </style>
@endpush

{{-- Card untuk pilih/tambah peminjam --}}
<x-bladewind::card>
    <label class="block text-sm font-medium mb-1">Peminjam</label>

    <div class="flex items-start gap-2">
        <div class="flex-1">
            <x-bladewind::select html_id="borrowerSelect" name="borrower_id" placeholder="Pilih peminjam"
                label_key="name" value_key="id" searchable="true" image_key="avatar" :data="$borrowers" />
            <p id="error-borrower_id" class="text-red-600 text-sm alert-error" style="margin-top: 16px"></p>
        </div>

        <x-bladewind::button onclick="showModal('new-borrower')" class="h-[45px] flex px-3">
            <x-bladewind::icon name="plus" type="solid" />
        </x-bladewind::button>
    </div>

</x-bladewind::card>

@section('modals')
    <x-bladewind::modal name="new-borrower" title="Tambah Peminjam" show_action_buttons="false">

        <x-bladewind::input label="Nama Peminjam" placeholder="Masukkan nama" id="newBorrowerName" />
        <x-bladewind::input label="Email" placeholder="Masukkan email" id="newBorrowerEmail" />
        <x-bladewind::input label="Telepon" placeholder="Masukkan nomor telepon" id="newBorrowerPhone" />
        <x-bladewind::input label="Alamat" placeholder="Masukkan alamat" id="newBorrowerAddress" />

        <x-bladewind::button class="mt-4" onclick="saveBorrower()">
            Simpan
        </x-bladewind::button>
    </x-bladewind::modal>
@endsection

@push('scripts')
    <script>
        async function saveBorrower() {
            const nameInput = document.getElementById("newBorrowerName");
            const name = nameInput.value.trim();

            if (name === "") {
                alert("Nama peminjam tidak boleh kosong.");
                return;
            }

            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            try {
                const res = await fetch("/api/borrowers", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + token
                    },
                    body: JSON.stringify({ name })
                });

                const json = await res.json();

                if (!res.ok) {
                    alert(json.message || "Gagal menambahkan peminjam.");
                    return;
                }

                // reload page
                window.location.reload();
            } catch (error) {
                console.error("Error:", error);
                alert("Terjadi kesalahan saat menambahkan peminjam.");
            }
        }

    </script>
@endpush