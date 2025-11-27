<x-layouts.menu title="Bayar Pinjaman">

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
            oninput="updateAfterRemaining()" />

        <p id="error-amount" class="text-red-600 text-sm alert-error"></p>
        <p id="error-pay" class="text-red-500 text-sm"></p>

        <div class="mt-4 text-sm">
            <div>Sisa setelah bayar:</div>
            <div id="afterRemaining" class="font-bold text-lg">Rp 0</div>
        </div>
    </x-bladewind::card>

    {{-- BUTTON FIXED --}}
    <div class="fixed bottom-0 left-0 w-full bg-white py-4 px-6 border-t shadow-md">
        <div class="max-w-xl mx-auto">
            <x-bladewind.button can_submit="true" id="btnPay" class="w-full">
                Bayar Sekarang
            </x-bladewind.button>
        </div>
    </div>

</x-layouts.menu>

<script>
    const loanId = window.location.pathname.split("/")[2];
    let loan = null;

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

        } catch (err) {
            console.error(err);
            alert("Gagal memuat data pinjaman");
        }
    }

    function renderLoanHeader() {
        const paid = loan.paid_amount ?? 0;
        const total = loan.total_amount;
        const remaining = total - paid;
        const percent = Math.floor((paid / total) * 100);

        document.getElementById("borrowerName").textContent = loan.borrower.name;

        document.getElementById("loanDate").textContent = new Date(loan.date)
            .toLocaleDateString("id-ID", {
                weekday: "long", day: "numeric", month: "long", year: "numeric"
            });

        document.getElementById("loanTotal").textContent = "Rp " + total.toLocaleString("id-ID");
        document.getElementById("loanPaid").textContent = "Rp " + paid.toLocaleString("id-ID");
        document.getElementById("loanRemaining").textContent = "Rp " + remaining.toLocaleString("id-ID");

        document.getElementById("loanProgress").style.width = percent + "%";

        updateAfterRemaining();
    }

    function updateAfterRemaining() {
        const el = document.getElementById("payAmount");
        const errorEl = document.getElementById("error-pay");
        const afterEl = document.getElementById("afterRemaining");

        let raw = unformatIDR(el.value);   // Ambil angka mentah
        if (isNaN(raw)) raw = 0;

        const remaining = loan.remaining_amount;

        // Reset error
        errorEl.textContent = "";

        // Validasi MAX
        if (raw > remaining) {
            errorEl.textContent = `Pembayaran tidak boleh melebihi ${formatIDR(remaining)}`;
        }

        // Hitung sisa setelah membayar — jangan kurang dari 0
        const after = Math.max(remaining - raw, 0);
        afterEl.textContent = formatIDR(after);

        // Format ulang input ke IDR (supaya tetap otomatis)
        el.value = raw === 0 ? "" : formatIDR(raw);
    }

    document.getElementById("payAmount").addEventListener("input", updateAfterRemaining);

    // SUBMIT
    document.getElementById("btnPay").addEventListener("click", async () => {

        resetBladewindInputError();

        const amount = unformatIDR(document.getElementById("payAmount").value);
        const fd = new FormData();
        fd.append("amount", amount);

        try {
            const token = localStorage.getItem("token");

            const res = await fetch(`/api/loans/${loanId}/pay`, {
                method: "POST",
                headers: { "Authorization": "Bearer " + token },
                body: fd
            });

            const json = await res.json();

            if (!res.ok) {
                if (json.errors) {
                    Object.keys(json.errors).forEach(field => {
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

    loadLoan();
</script>