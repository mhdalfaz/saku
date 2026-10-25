@push('styles')
    <style>
        /* Full width modal untuk layar < 640px (mobile) */
        @media (max-width: 639px) {
            .bw-new-custodian {
                width: 95% !important;
                max-width: 95% !important;
            }
        }
        .sticky.top-0.min-w-full.bg-gray-100.dark\:bg-transparent.py-1.pr-0.-pl-1.search-bar {
            z-index: 50;
        }
    </style>
@endpush

{{-- Card untuk pilih/tambah penampung --}}
<x-bladewind::card>
    <label class="block text-sm font-medium mb-1">Penampung</label>

    <div class="flex items-start gap-2">
        <div class="flex-1">
            <x-bladewind::select html_id="custodianSelect" name="custodian_id" placeholder="Pilih penampung"
                label_key="name" value_key="id" searchable="true" data="manual" />
            <p id="error-custodian_id" class="text-red-600 text-sm alert-error" style="margin-top: 16px"></p>
        </div>

        <x-bladewind::button onclick="showModal('new-custodian')" class="h-[45px] flex px-3">
            <x-bladewind::icon name="plus" type="solid" />
        </x-bladewind::button>
    </div>

</x-bladewind::card>

@section('modals')
    <x-bladewind::modal name="new-custodian" title="Tambah Penampung" show_action_buttons="false">

        <x-bladewind::input label="Nama Penampung" placeholder="Masukkan nama" id="newCustodianName" />
        <x-bladewind::input label="Email" placeholder="Masukkan email" id="newCustodianEmail" />
        <x-bladewind::input label="Telepon" placeholder="Masukkan nomor telepon" id="newCustodianPhone" />
        <x-bladewind::input label="Alamat" placeholder="Masukkan alamat" id="newCustodianAddress" />

        <x-bladewind::button class="mt-4" onclick="saveCustodian()">
            Simpan
        </x-bladewind::button>
    </x-bladewind::modal>
@endsection

@push('scripts')
    <script>
        async function loadCustodians() {
            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            try {
                const res = await fetch("/api/custodians", {
                    headers: { "Authorization": "Bearer " + token }
                });

                const json = await res.json();
                if (!res.ok) {
                    console.error(json.message || "Gagal memuat penampung");
                    return;
                }

                (json.data || []).forEach(c => bw_custodian_id.addItem(String(c.id), c.name));
            } catch (error) {
                console.error("Error:", error);
            }
        }

        async function saveCustodian() {
            const nameInput = document.getElementById("newCustodianName");
            const name = nameInput.value.trim();

            if (name === "") {
                alert("Nama penampung tidak boleh kosong.");
                return;
            }

            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            const payload = { name };
            const email = document.getElementById("newCustodianEmail").value.trim();
            const phone = document.getElementById("newCustodianPhone").value.trim();
            const address = document.getElementById("newCustodianAddress").value.trim();

            if (email) payload.email = email;
            if (phone) payload.phone = phone;
            if (address) payload.address = address;

            try {
                const res = await fetch("/api/custodians", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + token
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                if (!res.ok) {
                    alert(json.message || "Gagal menambahkan penampung.");
                    return;
                }

                // reload page
                window.location.reload();
            } catch (error) {
                console.error("Error:", error);
                alert("Terjadi kesalahan saat menambahkan penampung.");
            }
        }

        document.addEventListener("DOMContentLoaded", loadCustodians);
    </script>
@endpush
