<x-layouts.menu title="Daftar Pinjaman">

    <h1 class="text-2xl font-bold mb-4">Daftar Pinjaman</h1>

    {{-- Filter --}}
    <div class="flex items-center gap-4 mb-4">
        <input type="text" id="filterName" placeholder="Cari peminjam..." class="border rounded px-3 py-2 flex-1" />
        <select id="sortDate" class="border rounded px-3 py-2">
            <option value="desc">Terbaru</option>
            <option value="asc">Terlama</option>
        </select>
    </div>

    {{-- Container daftar pinjaman --}}
    <div id="loansContainer" class="space-y-3"></div>

</x-layouts.menu>

<script>
    const loansContainer = document.getElementById('loansContainer');
    const filterInput = document.getElementById('filterName');
    const sortSelect = document.getElementById('sortDate');

    let loansData = [];

    async function loadLoans() {
        try {
            const token = localStorage.getItem("token");
            if (!token) return window.location.href = "/login";

            const res = await fetch("/api/loans", {
                headers: { "Authorization": "Bearer " + token }
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || "Gagal memuat pinjaman");

            loansData = json.data;
            renderLoans();

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

    function formatRupiah(number) {
        return number.toLocaleString("id-ID");
    }

    function renderLoans() {
        const filterText = filterInput.value.toLowerCase();
        const sortOrder = sortSelect.value;

        // filter
        let filtered = loansData.filter(l =>
            l.borrower.name.toLowerCase().includes(filterText)
        );

        // sort
        filtered.sort((a, b) =>
            sortOrder === 'asc'
                ? new Date(a.date) - new Date(b.date)
                : new Date(b.date) - new Date(a.date)
        );

        // render
        loansContainer.innerHTML = filtered.map(l => {
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
                            <x-bladewind::button size="tiny" onclick="window.location.href='/loans/${l.id}/pay'">Bayar</x-bladewind::button>
                            <x-bladewind::button size="tiny" color="secondary" onclick="window.location.href='/loans/${l.id}'">Detail</x-bladewind::button>
                        </div>
                    </div>


                    <div class="text-3xl font-bold mb-2">Rp ${formatRupiah(l.total_amount)}</div>

                    <div class="text-sm text-green-600">Sudah dibayar: Rp ${formatRupiah(l.paid)}</div>
                    <div class="text-sm text-red-600 mb-2">Sisa: Rp ${formatRupiah(l.remaining)}</div>

                    <x-bladewind::progress-bar percentage="${l.percent}" color="green" />
                </x-bladewind::card>
            `;
        }).join('');

        if (filtered.length === 0) {
            loansContainer.innerHTML = `<p class="text-gray-500">Tidak ada pinjaman.</p>`;
        }
    }

    // Event filter & sort
    filterInput.addEventListener('input', renderLoans);
    sortSelect.addEventListener('change', renderLoans);

    // load data saat halaman dibuka
    loadLoans();
</script>