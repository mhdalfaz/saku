{{-- SUMMARY ALL LOANS --}}
<div id="allLoansSummary" class="mb-4">
    <x-bladewind::card>
        <div class="flex justify-between mb-1">
            <div class="font-bold text-lg" id="allBorrowerName">...</div>
            <div class="text-gray-500 text-sm" id="loanCount"></div>
        </div>

        <div class="text-xl font-bold mb-1">
            Total Pinjaman: <span id="allLoanTotal">Rp 0</span>
        </div>

        <div class="text-green-600 text-sm">
            Sudah dibayar: <span id="allLoanPaid">Rp 0</span>
        </div>

        <div class="text-red-600 text-sm mb-2">
            Total Sisa: <span id="allLoanRemaining">Rp 0</span>
        </div>

        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
            <div id="allLoanProgress" class="h-full bg-green-500" style="width:0%"></div>
        </div>
    </x-bladewind::card>
</div>

{{-- LIST LOANS (VIEW ONLY) --}}
<div id="allLoansList" class="space-y-3 mb-4">
    <div class="text-center py-4 text-gray-500">
        <p>Memuat data...</p>
    </div>
</div>

{{-- FORM PEMBAYARAN --}}
<x-bladewind::card>
    <label class="block text-sm font-semibold mb-1">Nominal Pembayaran</label>
    <x-bladewind.input type="text" name="amountAll" id="payAmountAll" placeholder="Masukkan nominal" value="0"
        oninput="updateAfterRemainingBulk()" />

    <p id="error-pay-all" class="text-red-500 text-sm"></p>

    <div class="mt-4 text-sm">
        <div>Sisa setelah bayar:</div>
        <div id="afterRemainingAll" class="font-bold text-lg">Rp 0</div>
    </div>
</x-bladewind::card>

{{-- MARGIN BOTTOM --}}
<div class="h-10"></div>

{{-- BUTTON FIXED --}}
<div class="fixed bottom-0 left-0 w-full bg-white py-4 px-6 border-t shadow-md" id="btnPayAllContainer">
    <div class="max-w-xl mx-auto">
        <x-bladewind.button can_submit="true" id="btnPayAll" class="w-full">
            Bayar Sekarang
        </x-bladewind.button>
    </div>
</div>

<script>
    // ============ BULK PAY FUNCTIONS ============
    // Requires: allLoans, loan (from parent), formatRupiah, formatDate

    function renderAllLoansSummary() {
        const totalAmount = allLoans.reduce((sum, l) => sum + l.total_amount, 0);
        const totalPaid = allLoans.reduce((sum, l) => sum + l.paid_amount, 0);
        const totalRemaining = totalAmount - totalPaid;
        const percent = totalAmount > 0 ? Math.floor((totalPaid / totalAmount) * 100) : 0;

        const borrowerNameEl = document.getElementById("allBorrowerName");
        if (borrowerNameEl) borrowerNameEl.textContent = loan.borrower.name;

        const loanCountEl = document.getElementById("loanCount");
        if (loanCountEl) loanCountEl.textContent = allLoans.length + " pinjaman";

        const totalEl = document.getElementById("allLoanTotal");
        if (totalEl) totalEl.textContent = formatRupiah(totalAmount);

        const paidEl = document.getElementById("allLoanPaid");
        if (paidEl) paidEl.textContent = formatRupiah(totalPaid);

        const remainingEl = document.getElementById("allLoanRemaining");
        if (remainingEl) remainingEl.textContent = formatRupiah(totalRemaining);

        const progressEl = document.getElementById("allLoanProgress");
        if (progressEl) progressEl.style.width = percent + "%";
    }

    function renderAllLoansList() {
        const container = document.getElementById("allLoansList");
        if (!container) return;

        if (!allLoans || allLoans.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-500"><p>Tidak ada data pinjaman</p></div>';
            return;
        }

        let html = '';
        allLoans.forEach(function (l) {
            html += '<div class="bg-white rounded-lg border border-gray-200 p-3 flex justify-between items-center">';
            html += '<div>';
            html += '<div class="font-medium text-sm">' + formatDate(l.date) + '</div>';
            html += '<div class="text-xs text-gray-500">Sisa: ' + formatRupiah(l.remaining_amount) + '</div>';
            html += '</div>';
            html += '<div class="text-right">';
            html += '<div class="font-bold">' + formatRupiah(l.total_amount) + '</div>';
            html += '<div class="text-xs text-green-600">' + l.percent + '% lunas</div>';
            html += '</div>';
            html += '</div>';
        });

        container.innerHTML = html;
    }

    function updateAfterRemainingBulk() {
        const el = document.getElementById("payAmountAll");
        if (!el) return;

        const errorEl = document.getElementById("error-pay-all");
        const afterEl = document.getElementById("afterRemainingAll");

        let raw = unformatIDR(el.value);
        if (isNaN(raw)) raw = 0;

        const totalRemaining = allLoans.reduce((sum, l) => sum + l.remaining_amount, 0);

        if (errorEl) errorEl.textContent = "";

        if (raw > totalRemaining) {
            if (errorEl) errorEl.textContent = "Pembayaran tidak boleh melebihi " + formatRupiah(totalRemaining);
        }

        const after = Math.max(totalRemaining - raw, 0);
        if (afterEl) afterEl.textContent = formatRupiah(after);

        el.value = raw === 0 ? "" : formatIDR(raw);
    }

    // Bulk pay button event
    document.addEventListener("DOMContentLoaded", function () {
        const btnPayAll = document.getElementById("btnPayAll");
        if (btnPayAll) {
            btnPayAll.addEventListener("click", async function () {
                resetBladewindInputError();

                const amount = unformatIDR(document.getElementById("payAmountAll").value);
                if (!amount || amount <= 0) {
                    alert("Masukkan nominal pembayaran");
                    return;
                }

                const totalRemaining = allLoans.reduce((sum, l) => sum + l.remaining_amount, 0);
                if (amount > totalRemaining) {
                    document.getElementById("error-pay-all").textContent = "Pembayaran tidak boleh melebihi " + formatRupiah(totalRemaining);
                    return;
                }

                let remainingAmount = amount;
                let successCount = 0;

                for (let i = 0; i < allLoans.length && remainingAmount > 0; i++) {
                    const l = allLoans[i];
                    if (l.remaining_amount <= 0) continue;

                    const payAmountVal = Math.min(remainingAmount, l.remaining_amount);

                    const fd = new FormData();
                    fd.append("amount", payAmountVal);

                    try {
                        const token = localStorage.getItem("token");
                        const res = await fetch("/api/loans/" + l.id + "/pay", {
                            method: "POST",
                            headers: { "Authorization": "Bearer " + token },
                            body: fd
                        });

                        const json = await res.json();
                        if (res.ok) {
                            successCount++;
                        }
                    } catch (err) {
                        console.error(err);
                    }

                    remainingAmount -= payAmountVal;
                }

                if (successCount > 0) {
                    alert("Pembayaran berhasil! (" + successCount + " pinjaman)");
                    document.getElementById("payAmountAll").value = "";
                    updateAfterRemainingBulk();
                    loadAllLoans();
                    loadLoan();
                    window.location.href = "/loans";
                } else {
                    alert("Gagal melakukan pembayaran");
                }
            });
        }

        const payAmountAll = document.getElementById("payAmountAll");
        if (payAmountAll) {
            payAmountAll.addEventListener("input", updateAfterRemainingBulk);
        }
    });
</script>