<x-layouts.menu title="Daftar Pinjaman">

    <h1 class="text-2xl font-bold mb-4">Daftar Pinjaman</h1>

    {{-- Filter --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4 mb-4">
        <input type="text" id="filterName" placeholder="Cari peminjam..."
            class="border rounded px-3 py-2 w-full sm:flex-1" />
        <div class="flex gap-2 w-full">
            <select id="sortDate" class="custom-select border rounded px-3 py-2 flex-1 w-full">
                <option value="desc">Terbaru</option>
                <option value="asc">Terlama</option>
            </select>
            <select id="filterStatus" class="custom-select border rounded px-3 py-2 flex-1 w-full">
                <option value="">Semua</option>
                <option value="paid">Lunas</option>
                <option value="unpaid">Belum Lunas</option>
            </select>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-2 mb-4">
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm font-medium min-h-10">Total Lunas</div>
            <div class="flex items-center gap-2">
                <span id="paidCount" class="text-2xl font-bold ">-</span>
                <span class="text-sm">pinjaman</span>
            </div>
            <div class="text-green-600 text-sm"><span id="paidTotal" class="font-semibold">-</span></div>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm font-medium min-h-10">Total Belum Lunas</div>
            <div class="flex items-center gap-2">
                <span id="unpaidCount" class="text-2xl font-bold ">-</span>
                <span class="text-sm">pinjaman</span>
            </div>
            <div class="text-red-600 text-sm"><span id="unpaidTotal" class="font-semibold">-</span></div>
        </div>
    </div>

    {{-- Container daftar pinjaman --}}
    <div id="loansContainer" class="space-y-3"></div>


    {{-- BladeUI Modal untuk konfirmasi hapus --}}
    @section('modals')
        <x-bladewind::modal name="deleteLoanModal" show_action_buttons="false">
            <h3 class="text-lg font-bold mb-2">Konfirmasi Hapus Pinjaman</h3>
            <p class="mb-4">Anda yakin ingin menghapus pinjaman ini? Data akan <span class="font-bold text-red-600">hilang
                    permanen</span>!</p>
            <div class="flex gap-2 justify-end">
                <x-bladewind::button color="secondary" outline="true"
                    onclick="closeDeleteModal()">Batal</x-bladewind::button>
                <x-bladewind::button color="red" id="confirmDeleteBtn">Hapus</x-bladewind::button>
            </div>
        </x-bladewind::modal>
    @endsection
</x-layouts.menu>

<script>
    const loansContainer = document.getElementById('loansContainer');
    const filterInput = document.getElementById('filterName');
    const sortSelect = document.getElementById('sortDate');
    const filterStatus = document.getElementById('filterStatus');

    let loansData = [];
    let currentDeleteId = null;

    async function loadLoans() {
        try {
            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            const statusFilter = filterStatus.value;
            let url = "/api/loans";
            if (statusFilter) {
                url += "?status=" + statusFilter;
            }

            const res = await fetch(url, {
                headers: { "Authorization": "Bearer " + token }
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || "Gagal memuat pinjaman");

            loansData = json.data;

            await renderLoans();

        } catch (err) {
            console.error(err);
            loansContainer.innerHTML = `<p class="text-red-600">Gagal memuat data pinjaman.</p>`;
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

    function handleOpenDeleteModal(loanId) {
        currentDeleteId = loanId;
        // Buka modal dari BladeUI
        showModal('deleteLoanModal');
    }
    function closeDeleteModal() {
        currentDeleteId = null;
        hideModal('deleteLoanModal');
    }

    async function handleDeleteLoan() {
        if (!currentDeleteId) return;
        try {
            const token = localStorage.getItem("token");
            const res = await fetch(`/api/loans/${currentDeleteId}`, {
                method: "DELETE",
                headers: { "Authorization": "Bearer " + token }
            });
            if (res.ok) {
                await loadLoans();
                closeDeleteModal();
            } else {
                const json = await res.json();
                alert(json.message || "Gagal menghapus pinjaman.");
            }
        } catch (err) {
            console.error(err);
            alert("Gagal menghapus pinjaman.");
        }
    }

    async function renderLoans() {
        const filterText = filterInput.value.toLowerCase();
        const sortOrder = sortSelect.value;
        const statusFilter = filterStatus.value;

        // filter
        let filtered = loansData.filter(l => {
            const nameMatch = l.borrower.name.toLowerCase().includes(filterText);
            const statusMatch = !statusFilter ||
                (statusFilter === 'paid' && l.remaining === 0) ||
                (statusFilter === 'unpaid' && l.remaining > 0);
            return nameMatch && statusMatch;
        });

        // sort
        filtered.sort((a, b) =>
            sortOrder === 'asc'
                ? new Date(a.date) - new Date(b.date)
                : new Date(b.date) - new Date(a.date)
        );

        // update summary cards
        const paidLoans = filtered.filter(l => l.remaining === 0);
        const unpaidLoans = filtered.filter(l => l.remaining > 0);
        document.getElementById('paidCount').textContent = paidLoans.length;
        document.getElementById('paidTotal').textContent = formatIDR(paidLoans.reduce((sum, l) => sum + l.paid, 0));
        document.getElementById('unpaidCount').textContent = unpaidLoans.length;
        document.getElementById('unpaidTotal').textContent = formatIDR(unpaidLoans.reduce((sum, l) => sum + l.remaining, 0));

        loansContainer.innerHTML = filtered.map(l => {
            const isPaid = l.status === 'paid' || l.remaining === 0;
            const payButton = isPaid
                ? ''
                : `<x-bladewind::button size="tiny" color="secondary" outline="true" onclick="window.location.href='/loans/${l.id}/pay'">Bayar</x-bladewind::button>`;

            return `
                <x-bladewind::card class="border rounded-md p-4 shadow-sm bg-white">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col gap-1">
                            <div class="font-semibold text-lg">${l.borrower.name}</div>
                            <div class="text-sm text-gray-500 mb-2">
                                ${formatFullDate(l.date)}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            ${payButton}
                            <x-bladewind::button size="tiny" color="secondary" outline="true" onclick="window.location.href='/loans/${l.id}'">Detail</x-bladewind::button>
                            <x-bladewind::button size="tiny" color="red" onclick="handleOpenDeleteModal('${l.id}')">Delete</x-bladewind::button>
                        </div>
                    </div>

                    <div class="text-3xl font-bold mb-2">${formatIDR(l.total_amount)}</div>
                    <div class="text-sm text-green-600">Sudah dibayar: <span class="font-bold">${formatIDR(l.paid)}</span></div>
                    <div class="text-sm text-red-600 mb-2">Sisa: <span class="font-bold">${formatIDR(l.remaining)}</span></div>
                    <x-bladewind::progress-bar percentage="${l.percent}" color="green" />
                </x-bladewind::card>
            `;
        }).join('');

        if (filtered.length === 0) {
            loansContainer.innerHTML = `<p class="text-gray-500">Tidak ada pinjaman.</p>`;
        }
    }

    // Event filter & sort
    filterInput.addEventListener('input', async () => await renderLoans());
    sortSelect.addEventListener('change', async () => await renderLoans());
    filterStatus.addEventListener('change', async () => {
        await loadLoans();
    });

    // Event modal tombol hapus
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('confirmDeleteBtn').addEventListener('click', handleDeleteLoan);
    });

    // load data saat halaman dibuka
    loadLoans();
</script>