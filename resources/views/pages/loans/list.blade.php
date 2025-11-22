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
                headers: {
                    "Authorization": "Bearer " + token
                }
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

    function renderLoans() {
        const filterText = filterInput.value.toLowerCase();
        const sortOrder = sortSelect.value;

        // filter
        let filtered = loansData.filter(l =>
            l.borrower.name.toLowerCase().includes(filterText)
        );

        // sort
        filtered.sort((a, b) => {
            const dateA = new Date(a.date);
            const dateB = new Date(b.date);
            return sortOrder === 'asc' ? dateA - dateB : dateB - dateA;
        });

        // render
        loansContainer.innerHTML = filtered.map(l => `
            <x-bladewind::card>
                <div class="flex justify-between items-center">
                    <div class="font-semibold">${l.borrower.name}</div>
                    <div class="text-sm text-gray-500">${new Date(l.date).toLocaleDateString()}</div>
                </div>
                <div class="text-2xl font-semibold">Rp ${l.total_amount.toLocaleString()}</div>
            </x-bladewind::card>
        `).join('');

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