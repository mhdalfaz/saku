<x-layouts.menu title="Bayar Pinjaman">
    {{-- TAB HEADER --}}
    <x-custom-tab-list name="loan-tabs" />

    {{-- TAB CONTENT: Single Pay --}}
    <div data-tab-content="loan-tabs" data-tab="single-pay" class="tab-content active">
        @include('pages.loans.single')
    </div>

    {{-- TAB CONTENT: All Pays --}}
    <div data-tab-content="loan-tabs" data-tab="all-pays" class="tab-content hidden">
        @include('pages.loans.bulk')
    </div>

    <script>
        // ============ SHARED FUNCTIONS ============

        const loanId = window.location.pathname.split("/")[2];
        let loan = null;
        let allLoans = [];

        function formatRupiah(num) {
            return "Rp " + num.toLocaleString("id-ID");
        }

        function formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString("id-ID", {
                day: "numeric",
                month: "short",
                year: "numeric"
            });
        }

        async function loadLoan() {
            try {
                const token = localStorage.getItem("token");
                const res = await fetch(`/api/loans/${loanId}`, {
                    headers: { "Authorization": "Bearer " + token }
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.message);

                loan = json.data;
                renderLoanHeader();
                loadAllLoans();

            } catch (err) {
                console.error(err);
                alert("Gagal memuat data pinjaman");
            }
        }

        async function loadAllLoans() {
            try {
                const token = localStorage.getItem("token");
                const borrowerId = loan.borrower_id;

                const res = await fetch(`/api/loans/borrower/${borrowerId}?status=ongoing`, {
                    headers: { "Authorization": "Bearer " + token }
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.message);

                allLoans = json.data;
                renderAllLoansSummary();
                renderAllLoansList();

            } catch (err) {
                console.error(err);
                const container = document.getElementById("allLoansList");
                if (container) {
                    container.innerHTML = '<div class="text-center py-8 text-red-500"><p>Gagal memuat data</p></div>';
                }
            }
        }

        // Initialize
        loadLoan();
    </script>

</x-layouts.menu>