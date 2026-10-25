<x-layouts.menu title="Daftar Simpanan">

    <h1 class="text-2xl font-bold mb-4">Daftar Simpanan</h1>

    {{-- Filter --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4 mb-4">
        <input type="text" id="filterName" placeholder="Cari penampung..."
            class="border rounded px-3 py-2 w-full sm:flex-1" />
        <div class="flex gap-2 w-full">
            <select id="sortDate" class="custom-select border rounded px-3 py-2 flex-1 w-full">
                <option value="desc">Terbaru</option>
                <option value="asc">Terlama</option>
            </select>
            <select id="filterStatus" class="custom-select border rounded px-3 py-2 flex-1 w-full">
                <option value="">Semua</option>
                <option value="ongoing">Aktif</option>
                <option value="closed">Ditutup</option>
            </select>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-2 mb-4">
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm font-medium min-h-10">Saldo Tertahan</div>
            <div class="text-2xl font-bold text-blue-600"><span id="totalBalance">-</span></div>
            <div class="text-sm text-gray-500"><span id="ongoingCount">0</span> simpanan aktif</div>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm font-medium min-h-10">Total Keuntungan</div>
            <div class="text-2xl font-bold text-green-600"><span id="totalProfit">-</span></div>
            <div class="text-sm text-gray-500">dari semua simpanan</div>
        </div>
    </div>

    {{-- Container daftar simpanan --}}
    <div id="savingsContainer" class="space-y-3"></div>

    {{-- BladeUI Modal untuk konfirmasi hapus --}}
    @section('modals')
        <x-bladewind::modal name="deleteSavingModal" show_action_buttons="false">
            <h3 class="text-lg font-bold mb-2">Konfirmasi Hapus Simpanan</h3>
            <p class="mb-4">Anda yakin ingin menghapus simpanan ini? Beserta seluruh riwayat transaksinya akan
                <span class="font-bold text-red-600">hilang permanen</span>!</p>
            <div class="flex gap-2 justify-end">
                <x-bladewind::button color="secondary" outline="true"
                    onclick="closeDeleteModal()">Batal</x-bladewind::button>
                <x-bladewind::button color="red" id="confirmDeleteBtn">Hapus</x-bladewind::button>
            </div>
        </x-bladewind::modal>
    @endsection
</x-layouts.menu>

<script>
    const savingsContainer = document.getElementById('savingsContainer');
    const filterInput = document.getElementById('filterName');
    const sortSelect = document.getElementById('sortDate');
    const filterStatus = document.getElementById('filterStatus');

    let savingsData = [];
    let currentDeleteId = null;

    async function loadSavings() {
        try {
            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            const statusFilter = filterStatus.value;
            let url = "/api/savings";
            if (statusFilter) {
                url += "?status=" + statusFilter;
            }

            const res = await fetch(url, {
                headers: { "Authorization": "Bearer " + token }
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || "Gagal memuat simpanan");

            savingsData = json.data;

            await renderSavings();

        } catch (err) {
            console.error(err);
            savingsContainer.innerHTML = `<p class="text-red-600">Gagal memuat data simpanan.</p>`;
        }
    }

    function formatFullDate(dateString) {
        const options = {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric"
        };

        return new Date(dateString).toLocaleDateString("id-ID", options);
    }

    function handleOpenDeleteModal(savingId) {
        currentDeleteId = savingId;
        showModal('deleteSavingModal');
    }
    function closeDeleteModal() {
        currentDeleteId = null;
        hideModal('deleteSavingModal');
    }

    async function handleDeleteSaving() {
        if (!currentDeleteId) return;
        try {
            const token = localStorage.getItem("token");
            const res = await fetch(`/api/savings/${currentDeleteId}`, {
                method: "DELETE",
                headers: { "Authorization": "Bearer " + token }
            });
            if (res.ok) {
                await loadSavings();
                closeDeleteModal();
            } else {
                const json = await res.json();
                alert(json.message || "Gagal menghapus simpanan.");
            }
        } catch (err) {
            console.error(err);
            alert("Gagal menghapus simpanan.");
        }
    }

    async function renderSavings() {
        const filterText = filterInput.value.toLowerCase();
        const sortOrder = sortSelect.value;
        const statusFilter = filterStatus.value;

        // filter
        let filtered = savingsData.filter(s => {
            const nameMatch = s.custodian.name.toLowerCase().includes(filterText);
            const statusMatch = !statusFilter || s.status === statusFilter;
            return nameMatch && statusMatch;
        });

        // sort
        filtered.sort((a, b) =>
            sortOrder === 'asc'
                ? new Date(a.date) - new Date(b.date)
                : new Date(b.date) - new Date(a.date)
        );

        // update summary cards
        document.getElementById('totalBalance').textContent =
            formatIDR(filtered.reduce((sum, s) => sum + s.balance, 0));
        document.getElementById('totalProfit').textContent =
            formatIDR(filtered.reduce((sum, s) => sum + s.total_profit, 0));
        document.getElementById('ongoingCount').textContent =
            filtered.filter(s => s.status === 'ongoing').length;

        savingsContainer.innerHTML = filtered.map(s => {
            const statusBadge = s.status === 'ongoing'
                ? `<span class="text-xs font-bold px-2 py-1 rounded bg-green-100 text-green-700">Aktif</span>`
                : `<span class="text-xs font-bold px-2 py-1 rounded bg-gray-200 text-gray-600">Ditutup</span>`;

            return `
                <x-bladewind::card class="border rounded-md p-4 shadow-sm bg-white">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col gap-1">
                            <div class="font-semibold text-lg">${s.custodian.name}</div>
                            <div class="text-sm text-gray-500 mb-2">
                                ${formatFullDate(s.date)}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 items-end">
                            ${statusBadge}
                            <x-bladewind::button size="tiny" color="secondary" outline="true" onclick="window.location.href='/savings/${s.id}'">Detail</x-bladewind::button>
                            <x-bladewind::button size="tiny" color="red" onclick="handleOpenDeleteModal('${s.id}')">Delete</x-bladewind::button>
                        </div>
                    </div>

                    <div class="text-3xl font-bold mb-2">${formatIDR(s.balance)}</div>
                    <div class="text-sm text-green-600">Total setoran: <span class="font-bold">${formatIDR(s.total_deposit)}</span></div>
                    <div class="text-sm text-red-600">Total penarikan: <span class="font-bold">${formatIDR(s.total_withdrawal)}</span></div>
                    <div class="text-sm text-blue-600 mb-2">Total keuntungan: <span class="font-bold">${formatIDR(s.total_profit)}</span></div>
                </x-bladewind::card>
            `;
        }).join('');

        if (filtered.length === 0) {
            savingsContainer.innerHTML = `<p class="text-gray-500">Tidak ada simpanan.</p>`;
        }
    }

    // Event filter & sort
    filterInput.addEventListener('input', async () => await renderSavings());
    sortSelect.addEventListener('change', async () => await renderSavings());
    filterStatus.addEventListener('change', async () => {
        await loadSavings();
    });

    // Event modal tombol hapus
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('confirmDeleteBtn').addEventListener('click', handleDeleteSaving);
    });

    // load data saat halaman dibuka
    loadSavings();
</script>
