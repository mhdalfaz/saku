<x-layouts.menu title="Detail Pinjaman">

    <h1 class="text-2xl font-bold mb-4">Detail Pinjaman</h1>

    {{-- Header Info --}}
    <div id="loanHeader"></div>

    {{-- Attachment Section --}}
    <div id="attachmentsContainer" class="mt-6"></div>

    <h2 class="text-xl font-semibold mt-6 mb-2">Riwayat Pembayaran</h2>

    {{-- List Transaksi --}}
    <div id="transactionsContainer" class="space-y-3"></div>

    {{-- Modal Delete Transaksi --}}
    @section('modals')
        <x-bladewind::modal name="deleteTransactionModal" show_action_buttons="false">
            <h3 class="text-lg font-bold mb-2">Hapus Pembayaran</h3>
            <p class="mb-4">Yakin ingin menghapus pembayaran ini? Data akan <span class="font-bold text-red-600">hilang
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
    const loanId = window.location.pathname.split('/').pop();
    let loan = null;
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

    // -----------------------------
    // Attachments Render
    // -----------------------------
    function renderAttachments() {
        const wrap = document.getElementById("attachmentsContainer");

        const images = Array.isArray(loan.images) ? loan.images : [];
        const files = Array.isArray(loan.files) ? loan.files : [];

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
    // Buka & Tutup Modal
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
        const token = localStorage.getItem("token");
        try {
            const res = await fetch(`/api/loan-transactions/${currentDeleteTransactionId}`, {
                method: "DELETE",
                headers: { "Authorization": "Bearer " + token }
            });
            if (res.ok) {
                closeDeleteTransactionModal();
                await loadLoan();
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
            renderAttachments();
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
                    <div class="flex flex-col">
                        <div class="text-lg font-semibold">${formatIDR(t.amount)}</div>
                        ${t.note ? `<div class="text-gray-700 text-sm">Catatan: ${t.note}</div>` : ''}
                    </div>
                    <div class="flex flex-col gap-2 items-end">
                        <div class="text-sm text-gray-500">${formatFullDate(t.date)}</div>
                        <x-bladewind::button size="tiny" color="red" onclick="openDeleteTransactionModal('${t.id}')">Delete</x-bladewind::button>
                    </div>
                </div>

            </x-bladewind::card>
        `).join('');
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('confirmDeleteTransactionBtn').addEventListener('click', handleDeleteTransaction);
    });

    // Optional: tutup modal gambar kalau klik background/modal-close
    // BladeWind modal default: tutup klik X atas (bisa edit modal jika ingin)
    window.closeImagePreviewModal = closeImagePreviewModal;

    loadLoan();
</script>