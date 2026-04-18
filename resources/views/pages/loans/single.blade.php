{{-- HEADER LOAN --}}
<div id="loanHeader" class="mb-4">
    <x-bladewind::card>
        <div class="flex justify-between mb-1">
            <div class="font-bold text-lg" id="borrowerName">...</div>
            <div class="text-gray-500 text-sm" id="loanDate">...</div>
        </div>

        <div class="text-xl font-bold mb-1">
            Total: <span id="loanTotal">Rp 0</span>
        </div>

        <div class="text-green-600 text-sm">
            Sudah dibayar: <span id="loanPaid">Rp 0</span>
        </div>

        <div class="text-red-600 text-sm mb-2">
            Sisa: <span id="loanRemaining">Rp 0</span>
        </div>

        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
            <div id="loanProgress" class="h-full bg-green-500" style="width:0%"></div>
        </div>
    </x-bladewind::card>
</div>

{{-- FORM PEMBAYARAN --}}
<x-bladewind::card>
    <label class="block text-sm font-semibold mb-1">Nominal Pembayaran</label>
    <x-bladewind.input type="text" name="amount" id="payAmount" placeholder="Masukkan nominal" value="0"
        oninput="updateAfterRemainingSingle()" />

    <p id="error-amount" class="text-red-600 text-sm alert-error"></p>
    <p id="error-pay" class="text-red-500 text-sm"></p>

    <div class="mt-4 text-sm">
        <div>Sisa setelah bayar:</div>
        <div id="afterRemaining" class="font-bold text-lg">Rp 0</div>
    </div>
</x-bladewind::card>

{{-- BUTTON FIXED --}}
<div class="fixed bottom-0 left-0 w-full bg-white py-4 px-6 border-t shadow-md" id="btnPayContainer">
    <div class="max-w-xl mx-auto">
        <x-bladewind.button can_submit="true" id="btnPay" class="w-full">
            Bayar Sekarang
        </x-bladewind.button>
    </div>
</div>

<script>
    // ============ SINGLE PAY FUNCTIONS ============
    // Requires: loan, loanId (from parent)

    function renderLoanHeader() {
        const paid = loan.paid_amount ?? 0;
        const total = loan.total_amount;
        const remaining = total - paid;
        const percent = total > 0 ? Math.floor((paid / total) * 100) : 0;

        document.getElementById("borrowerName").textContent = loan.borrower.name;

        document.getElementById("loanDate").textContent = new Date(loan.date)
            .toLocaleDateString("id-ID", {
                weekday: "long", day: "numeric", month: "long", year: "numeric"
            });

        document.getElementById("loanTotal").textContent = formatRupiah(total);
        document.getElementById("loanPaid").textContent = formatRupiah(paid);
        document.getElementById("loanRemaining").textContent = formatRupiah(remaining);

        document.getElementById("loanProgress").style.width = percent + "%";

        if (document.getElementById("payAmount")) {
            updateAfterRemainingSingle();
        }
    }

    function updateAfterRemainingSingle() {
        const el = document.getElementById("payAmount");
        if (!el) return;

        const errorEl = document.getElementById("error-pay");
        const afterEl = document.getElementById("afterRemaining");

        let raw = unformatIDR(el.value);
        if (isNaN(raw)) raw = 0;

        const remaining = loan.remaining_amount;

        if (errorEl) errorEl.textContent = "";

        if (raw > remaining) {
            if (errorEl) errorEl.textContent = "Pembayaran tidak boleh melebihi " + formatRupiah(remaining);
        }

        const after = Math.max(remaining - raw, 0);
        if (afterEl) afterEl.textContent = formatRupiah(after);

        el.value = raw === 0 ? "" : formatIDR(raw);
    }

    // Single pay button event
    document.addEventListener("DOMContentLoaded", function () {
        const btnPay = document.getElementById("btnPay");
        if (btnPay) {
            btnPay.addEventListener("click", async function () {
                resetBladewindInputError();

                const amount = unformatIDR(document.getElementById("payAmount").value);
                const fd = new FormData();
                fd.append("amount", amount);

                try {
                    const token = localStorage.getItem("token");
                    const res = await fetch("/api/loans/" + loanId + "/pay", {
                        method: "POST",
                        headers: { "Authorization": "Bearer " + token },
                        body: fd
                    });

                    const json = await res.json();

                    if (!res.ok) {
                        if (json.errors) {
                            Object.keys(json.errors).forEach(function (field) {
                                setBladewindInputError(field, json.errors[field][0]);
                            });
                        }
                        return;
                    }

                    alert("Pembayaran berhasil!");
                    window.location.href = "/loans";

                } catch (err) {
                    console.error(err);
                    alert("Terjadi kesalahan");
                }
            });
        }

        const payAmount = document.getElementById("payAmount");
        if (payAmount) {
            payAmount.addEventListener("input", updateAfterRemainingSingle);
        }
    });
</script>