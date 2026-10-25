<x-layouts.menu title="Detail Simpanan">

    <h1 class="text-2xl font-bold mb-4">Detail Simpanan</h1>

    {{-- Header Info --}}
    <div id="savingHeader"></div>

    {{-- Attachment Section --}}
    <div id="attachmentsContainer" class="mt-6"></div>

    <h2 class="text-xl font-semibold mt-6 mb-2">Riwayat Transaksi</h2>

    {{-- List Transaksi --}}
    <div id="transactionsContainer" class="space-y-3"></div>

    {{-- Modal Transaksi --}}
    @section('modals')
        <x-bladewind::modal name="depositModal" title="Tambah Setoran" show_action_buttons="false">
            <label class="block text-sm font-medium mb-1">Nominal Setoran (IDR)</label>
            <x-bladewind.input type="text" id="depositAmount" placeholder="500000"
                oninput="this.value = formatIDR(this.value)" />
            <label class="block text-sm font-medium mb-1 mt-3">Catatan (opsional)</label>
            <x-bladewind.input type="text" id="depositNote" placeholder="Misal: setoran bulan ini" />
            <x-bladewind::button class="mt-4 w-full" onclick="submitTransaction('deposit')">Simpan Setoran
            </x-bladewind::button>
        </x-bladewind::modal>

        <x-bladewind::modal name="withdrawModal" title="Tarik Simpanan" show_action_buttons="false">
            <p id="withdrawBalanceInfo" class="text-sm text-gray-600 mb-3"></p>
            <label class="block text-sm font-medium mb-1">Nominal Penarikan (IDR)</label>
            <x-bladewind.input type="text" id="withdrawAmount" placeholder="500000"
                oninput="this.value = formatIDR(this.value)" />
            <label class="block text-sm font-medium mb-1 mt-3">Catatan (opsional)</label>
            <x-bladewind.input type="text" id="withdrawNote" placeholder="Misal: ditarik via transfer" />
            <x-bladewind::button class="mt-4 w-full" onclick="submitTransaction('withdraw')">Simpan Penarikan
            </x-bladewind::button>
        </x-bladewind::modal>

        <x-bladewind::modal name="profitModal" title="Catat Keuntungan" show_action_buttons="false">
            <label class="block text-sm font-medium mb-1">Nominal Keuntungan (IDR)</label>
            <x-bladewind.input type="text" id="profitAmount" placeholder="50000"
                oninput="this.value = formatIDR(this.value)" />
            <label class="block text-sm font-medium mb-1 mt-3">Catatan (opsional)</label>
            <x-bladewind.input type="text" id="profitNote" placeholder="Misal: bagi hasil kuartal ini" />
            <label class="flex items-start gap-2 mt-3 text-sm">
                <input type="checkbox" id="profitRetained" class="mt-1" />
                <span>Profit ditahan/direinvestasi di penampung (menambah saldo tertahan)</span>
            </label>
            <x-bladewind::button class="mt-4 w-full" onclick="submitTransaction('profit')">Simpan Keuntungan
            </x-bladewind::button>
        </x-bladewind::modal>

        <x-bladewind::modal name="closeSavingModal" show_action_buttons="false">
            <h3 class="text-lg font-bold mb-2">Tutup Simpanan</h3>
            <p id="closeSavingMessage" class="mb-4"></p>
            <div class="flex gap-2 justify-end">
                <x-bladewind::button color="secondary" outline="true"
                    onclick="hideModal('closeSavingModal')">Batal</x-bladewind::button>
                <x-bladewind::button color="red" onclick="confirmCloseSaving()">Tutup Simpanan</x-bladewind::button>
            </div>
        </x-bladewind::modal>

        <x-bladewind::modal name="deleteTransactionModal" show_action_buttons="false">
            <h3 class="text-lg font-bold mb-2">Hapus Transaksi</h3>
            <p class="mb-4">Yakin ingin menghapus transaksi ini? Data akan <span class="font-bold text-red-600">hilang
                    permanen</span>!</p>
            <div class="flex gap-2 justify-end">
                <x-bladewind::button color="secondary" outline="true"
                    onclick="closeDeleteTransactionModal()">Batal</x-bladewind::button>
                <x-bladewind::button color="red" id="confirmDeleteTransactionBtn">Hapus</x-bladewind::button>
            </div>
        </x-bladewind::modal>

        <x-bladewind::modal name="previewImageModal" show_action_buttons="false" size="large">
            <div id="previewImageContent" class="flex flex-col items-center justify-center p-6">
                <!-- Image injected by JS -->
            </div>
        </x-bladewind::modal>
    @endsection

    <style>
        .attachment-fixed-height {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-img-modal {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 3px 16px 0 rgba(0, 0, 0, 0.1);
        }
    </style>
</x-layouts.menu>

<script>
    const savingId = window.location.pathname.split('/').pop();
    let saving = null;
    let currentDeleteTransactionId = null;

    // For image preview
    function openImagePreviewModal(imgUrl, altText = "") {
        document.getElementById("previewImageContent").innerHTML = `
            <img src="${imgUrl}" alt="${altText}" class="preview-img-modal" />
        `;
        showModal('previewImageModal');
    }

    function closeImagePreviewModal() {
        hideModal('previewImageModal');
        document.getElementById("previewImageContent").innerHTML = '';
    }

    function formatFullDate(dateString) {
        return new Date(dateString).toLocaleDateString("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric"
        });
    }

    function getAuthToken() {
        const token = localStorage.getItem("token");
        if (!token) window.location.href = "/login";
        return token;
    }

    // -----------------------------
    // Attachments Render
    // -----------------------------
    function renderAttachments() {
        const wrap = document.getElementById("attachmentsContainer");

        const images = Array.isArray(saving.images) ? saving.images : [];
        const files = Array.isArray(saving.files) ? saving.files : [];

        if (images.length === 0 && files.length === 0) {
            wrap.innerHTML = "";
            return;
        }

        let html = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">`;

        // Render images: object-fit & preview modal
        images.forEach(img => {
            const url = img.public_url;
            html += `
            <div class="border rounded p-3 flex flex-col items-center bg-white shadow-sm attachment-fixed-height">
                <img src="${url}" alt="${img.original_name}"
                     class="rounded cursor-pointer"
                     style="object-fit: cover; width:100%; height:100%; max-width:210px; max-height:210px;"
                     onclick="openImagePreviewModal('${url.replace(/'/g, "\\'")}', '${img.original_name.replace(/'/g, "\\'") || ''}')"/>
                <div class="text-xs text-gray-700 mt-2 break-all w-full text-center">${img.original_name}</div>
            </div>
        `;
        });

        // Render files (PDF, docs) with fixed height as well
        files.forEach(file => {
            const url = file.public_url;
            if ((file.mime_type && file.mime_type === "application/pdf") || file.original_name.toLowerCase().endsWith(".pdf")) {
                html += `
                <div class="border rounded p-3 flex flex-col items-center bg-white shadow-sm attachment-fixed-height">
                    <iframe src="${url}" class="border rounded w-full" style="height:150px; min-height:150px;"></iframe>
                    <a href="${url}" target="_blank" class="text-blue-600 underline text-xs font-bold mt-2">Preview / Download PDF</a>
                    <div class="text-xs text-gray-700 break-all mt-1 w-full text-center">${file.original_name}</div>
                </div>
            `;
            } else {
                html += `
                <div class="border rounded p-3 flex flex-col items-center bg-white shadow-sm attachment-fixed-height">
                    <span class="mb-2 text-gray-400">
                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m0 0v3m0-3h3m-3 0H9m3-11V3.75A1.75 1.75 0 0010.25 2H7.75A1.75 1.75 0 006 3.75V20.25A1.75 1.75 0 007.75 22h8.5A1.75 1.75 0 0018 20.25V9.75A1.75 1.75 0 0016.25 8H15V3.75z"/>
                        </svg>
                    </span>
                    <a href="${url}" target="_blank" class="text-blue-600 underline text-xs font-bold mb-1">Download / Preview</a>
                    <div class="text-xs text-gray-700 break-all w-full text-center">${file.original_name}</div>
                </div>
            `;
            }
        });

        html += `</div>`;
        wrap.innerHTML = html;
    }

    // -----------------------------
    // Transaksi: Setor / Tarik / Profit
    // -----------------------------
    async function submitTransaction(type) {
        const token = getAuthToken();

        const amountInput = document.getElementById(`${type}Amount`);
        const amount = unformatIDR(amountInput.value);

        if (!amount || amount < 1) {
            alert("Nominal harus lebih dari 0.");
            return;
        }

        const payload = {
            amount: amount,
            note: document.getElementById(`${type}Note`).value.trim() || null,
        };

        if (type === 'profit') {
            payload.retained = document.getElementById('profitRetained').checked;
        }

        try {
            const res = await fetch(`/api/savings/${savingId}/${type}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer " + token
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json();

            if (!res.ok) {
                alert(json.message || "Gagal menyimpan transaksi.");
                return;
            }

            alert(json.message);
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan, coba lagi.");
        }
    }

    // -----------------------------
    // Tutup Simpanan
    // -----------------------------
    function openCloseSavingModal() {
        const message = saving.balance > 0
            ? `Saldo tertahan masih <span class="font-bold">${formatIDR(saving.balance)}</span>. Pastikan dana sudah dikembalikan oleh penampung sebelum menutup simpanan.`
            : "Anda yakin ingin menutup simpanan ini? Simpanan yang ditutup tidak bisa menerima transaksi baru.";

        document.getElementById('closeSavingMessage').innerHTML = message;
        showModal('closeSavingModal');
    }

    async function confirmCloseSaving() {
        const token = getAuthToken();
        try {
            const res = await fetch(`/api/savings/${savingId}/close`, {
                method: "POST",
                headers: { "Authorization": "Bearer " + token }
            });

            const json = await res.json();

            if (!res.ok) {
                alert(json.message || "Gagal menutup simpanan.");
                return;
            }

            hideModal('closeSavingModal');
            alert(json.message);
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan, coba lagi.");
        }
    }

    // -----------------------------
    // Hapus Transaksi
    // -----------------------------
    function openDeleteTransactionModal(transactionId) {
        currentDeleteTransactionId = transactionId;
        showModal('deleteTransactionModal');
    }
    function closeDeleteTransactionModal() {
        currentDeleteTransactionId = null;
        hideModal('deleteTransactionModal');
    }

    async function handleDeleteTransaction() {
        if (!currentDeleteTransactionId) return;
        const token = getAuthToken();
        try {
            const res = await fetch(`/api/saving-transactions/${currentDeleteTransactionId}`, {
                method: "DELETE",
                headers: { "Authorization": "Bearer " + token }
            });
            if (res.ok) {
                closeDeleteTransactionModal();
                await loadSaving();
            } else {
                const json = await res.json();
                alert(json.message || "Gagal menghapus transaksi.");
            }
        } catch (err) {
            alert("Gagal menghapus transaksi.");
            console.error(err);
        }
    }

    // -----------------------------
    // Load Detail Saving
    // -----------------------------
    async function loadSaving() {
        try {
            const token = getAuthToken();

            const res = await fetch(`/api/savings/${savingId}`, {
                headers: {
                    "Authorization": "Bearer " + token
                }
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message);

            saving = json.data;

            renderSavingHeader();
            renderAttachments();
            renderTransactions();

        } catch (err) {
            console.error(err);
            document.getElementById("savingHeader").innerHTML =
                `<p class="text-red-600">Gagal memuat data simpanan.</p>`;
        }
    }

    // -----------------------------
    // Render Header Simpanan
    // -----------------------------
    function renderSavingHeader() {
        const statusBadge = saving.status === 'ongoing'
            ? `<span class="text-xs font-bold px-2 py-1 rounded bg-green-100 text-green-700">Aktif</span>`
            : `<span class="text-xs font-bold px-2 py-1 rounded bg-gray-200 text-gray-600">Ditutup</span>`;

        const actionButtons = saving.status === 'ongoing'
            ? `
                <div class="flex flex-wrap gap-2 mt-4">
                    <x-bladewind::button color="green" onclick="showModal('depositModal')">Setor</x-bladewind::button>
                    <x-bladewind::button color="red" onclick="showModal('withdrawModal')">Tarik</x-bladewind::button>
                    <x-bladewind::button color="blue" onclick="showModal('profitModal')">Keuntungan</x-bladewind::button>
                    <x-bladewind::button color="secondary" outline="true" onclick="openCloseSavingModal()">Tutup</x-bladewind::button>
                </div>
            `
            : '';

        document.getElementById("savingHeader").innerHTML = `
            <x-bladewind::card class="border rounded-md p-4 shadow-sm bg-white">

                <div class="flex justify-between items-start mb-2">
                    <div class="font-semibold text-xl">
                        ${saving.custodian.name}
                    </div>
                    ${statusBadge}
                </div>

                <div class="text-sm text-gray-500">
                    Dibuka: ${formatFullDate(saving.date)}
                </div>
                ${saving.description ? `<div class="text-sm text-gray-600 mt-1">${saving.description}</div>` : ''}

                <div class="text-3xl font-bold mt-3">
                    ${formatIDR(saving.balance)}
                </div>
                <div class="text-sm text-gray-500 mb-3">Saldo tertahan</div>

                <div class="text-sm text-green-600">Total setoran: <span class="font-bold">${formatIDR(saving.total_deposit)}</span></div>
                <div class="text-sm text-red-600">Total penarikan: <span class="font-bold">${formatIDR(saving.total_withdrawal)}</span></div>
                <div class="text-sm text-blue-600">Total keuntungan: <span class="font-bold">${formatIDR(saving.total_profit)}</span> (${formatIDR(saving.profit_retained)} ditahan)</div>

                ${actionButtons}
            </x-bladewind::card>
        `;

        document.getElementById('withdrawBalanceInfo').textContent =
            `Saldo saat ini: ${formatIDR(saving.balance)}`;
    }

    // -----------------------------
    // Render Transaksi
    // -----------------------------
    function renderTransactions() {
        const wrap = document.getElementById("transactionsContainer");

        if (saving.transactions.length === 0) {
            wrap.innerHTML = `
                <x-bladewind::empty-state
                    message="Belum ada transaksi"
                    icon="cash"
                />
            `;
            return;
        }

        const typeConfig = {
            deposit: { label: 'Setoran', cls: 'bg-green-100 text-green-700' },
            withdrawal: { label: 'Penarikan', cls: 'bg-red-100 text-red-700' },
            profit: { label: 'Keuntungan', cls: 'bg-blue-100 text-blue-700' },
        };

        wrap.innerHTML = saving.transactions.map(t => {
            const config = typeConfig[t.type] || { label: t.type, cls: 'bg-gray-100 text-gray-700' };
            const retainedLabel = (t.type === 'profit')
                ? (t.retained ? ' (ditahan)' : ' (diterima)')
                : '';

            return `
                <x-bladewind::card class="border rounded-md p-4 shadow-sm bg-white">

                    <div class="flex justify-between mb-2">
                        <div class="flex flex-col">
                            <div class="mb-1">
                                <span class="text-xs font-bold px-2 py-1 rounded ${config.cls}">${config.label}${retainedLabel}</span>
                            </div>
                            <div class="text-lg font-semibold">${formatIDR(t.amount)}</div>
                            ${t.note ? `<div class="text-gray-700 text-sm">Catatan: ${t.note}</div>` : ''}
                        </div>
                        <div class="flex flex-col gap-2 items-end">
                            <div class="text-sm text-gray-500">${formatFullDate(t.date)}</div>
                            <x-bladewind::button size="tiny" color="red" onclick="openDeleteTransactionModal('${t.id}')">Delete</x-bladewind::button>
                        </div>
                    </div>

                </x-bladewind::card>
            `;
        }).join('');
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('confirmDeleteTransactionBtn').addEventListener('click', handleDeleteTransaction);
    });

    window.closeImagePreviewModal = closeImagePreviewModal;

    loadSaving();
</script>
