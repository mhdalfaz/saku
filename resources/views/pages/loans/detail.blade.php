<x-layouts.menu title="Detail Pinjaman">

    <h1 class="text-2xl font-bold mb-4">Detail Pinjaman</h1>

    {{-- Header Info --}}
    <div id="loanHeader"></div>

    <h2 class="text-xl font-semibold mt-6 mb-2">Riwayat Pembayaran</h2>

    {{-- List Transaksi --}}
    <div id="transactionsContainer" class="space-y-3"></div>

</x-layouts.menu>


<script>
    const loanId = window.location.pathname.split('/').pop();
    let loan = null;

    // -----------------------------
    // Helpers
    // -----------------------------
    function formatIDR(value) {
        if (!value) return "Rp 0";
        return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function formatFullDate(dateString) {
        return new Date(dateString).toLocaleDateString("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric"
        });
    }

    // -----------------------------
    // Load Detail Loan
    // -----------------------------
    async function loadLoan() {
        try {
            const token = localStorage.getItem("token");

            const res = await fetch(`/api/loans/${loanId}`, {
                headers: {
                    "Authorization": "Bearer " + token
                }
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message);

            loan = json.data;

            renderLoanHeader();
            renderTransactions();

        } catch (err) {
            console.error(err);
            document.getElementById("loanHeader").innerHTML =
                `<p class="text-red-600">Gagal memuat data pinjaman.</p>`;
        }
    }

    // -----------------------------
    // Render Header Pinjaman
    // -----------------------------
    function renderLoanHeader() {
        document.getElementById("loanHeader").innerHTML = `
            <x-bladewind::card class="border rounded-md p-4 shadow-sm bg-white">

                <div class="font-semibold text-xl mb-2">
                    ${loan.borrower.name}
                </div>

                <div class="text-sm text-gray-500">
                    Tanggal Pinjaman: ${formatFullDate(loan.date)}
                </div>

                <div class="text-2xl font-bold mt-3">
                    Total: ${formatIDR(loan.total_amount)}
                </div>

                <div class="text-green-600">
                    Sudah dibayar: ${formatIDR(loan.paid)}
                </div>
                <div class="text-red-600 mb-2">
                    Sisa: ${formatIDR(loan.remaining)}
                </div>

                <x-bladewind::progress-bar percentage="${loan.percent}" color="green" />
            </x-bladewind::card>
        `;
    }

    // -----------------------------
    // Render Transaksi
    // -----------------------------
    function renderTransactions() {
        const wrap = document.getElementById("transactionsContainer");

        if (loan.transactions.length === 0) {
            wrap.innerHTML = `
                <x-bladewind::empty-state
                    message="Belum ada transaksi pembayaran"
                    icon="cash"
                />
            `;
            return;
        }

        wrap.innerHTML = loan.transactions.map(t => `
            <x-bladewind::card class="border rounded-md p-4 shadow-sm bg-white">

                <div class="flex justify-between mb-2">
                    <div class="text-lg font-semibold">${formatIDR(t.amount)}</div>
                    <div class="text-sm text-gray-500">${formatFullDate(t.date)}</div>
                </div>

                ${t.note ? `<div class="text-gray-700 text-sm">Catatan: ${t.note}</div>` : ''}
            </x-bladewind::card>
        `).join('');
    }

    loadLoan();
</script>