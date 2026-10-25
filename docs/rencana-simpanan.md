# Rencana Fitur: Simpanan (Uang yang Disimpan/Dititipkan di Orang Lain)

- **Branch**: `feat/simpanan`
- **Status**: 📋 Planning (belum ada implementasi)
- **Tanggal**: 25 Okt 2026
- **Dependensi**: di-branch dari `origin/main` (sudah memuat fitur Loans)

---

## 1. Konteks & Ruang Lingkup

Loans (**Piutang**) mencatat uang yang **dipinjam orang lain ke kita**, lalu dibayar balik berkala.
Simpanan kebalikannya: uang milik kita yang **disimpan/dititipkan ke pihak lain**, dan kelak
dikembalikan — mungkin dengan tambahan keuntungan.

**Case nyata yang dicover:**

| Case | Pola |
|---|---|
| Investasi / bagi hasil | Setor modal → pihak lain mengelola → profit berkala → tarik/ambil kembali |
| Arisan | Setor iuran berkala ke ketua → saat giliran, terima dana |
| Titipan biasa | Setor uang ke orang kepercayaan → tarik kapan saja |

**Keputusan desain (sudah disepakati):**

1. **Nama fitur**: **Simpanan** (branch `feat/simpanan`, tabel `savings`)
2. **Profit opsional**: tiap transaksi bertipe `setoran | penarikan | profit`. Case arisan cukup
   abaikan `profit`; case investasi bisa dipakai
3. **Setoran fleksibel**: setoran awal + setoran tambahan kapan saja (nominal bebas) +
   penarikan/pengembalian — tanpa target nominal, tanpa jadwal tetap
4. **Counterparty tabel baru**: `custodians` (mirror `borrowers`, tidak menyentuh tabel loans)

**Bukan scope v1** (lihat §10): jadwal arisan per-periode, integrasi cash-flow, laporan keuangan,
SPA/mobile.

---

## 2. Model Domain & Aturan Bisnis

### 2.1 Konsep

- **Saldo tertahan** = uang kita yang sedang dipegang penampung:

  ```
  saldo = Σ setoran + Σ profit(ditahan) − Σ penarikan
  ```

- **Profit** punya dua mode (kolom `retained`):
  - `retained = false` (default) → profit **dibayar ke kita** (tunai/transfer ke kita): saldo tidak berubah
  - `retained = true` → profit **ditahan/direinvestasi** di penampung: saldo bertambah
- **Total profit** dihitung dari **semua** transaksi `profit` (mau diterima atau ditahan),
  supaya UI bisa menampilkan "Total untung".

### 2.2 Status simpanan

`enum('ongoing', 'closed')` — **tutup manual** (tombol "Tutup Simpanan"), bukan auto-flip seperti
loans. Alasan: saldo bisa 0 lalu user setor lagi di penampung yang sama (mis. arisan siklus baru).

- `ongoing` → semua transaksi (setor/tarik/profit) dibolehkan
- `closed` → transaksi baru **ditolak 422**; hanya bisa lihat & hapus
- Reopen (`POST /reopen`) → **tidak di v1**, dicatat sebagai future (mudah ditambah)

### 2.3 Aturan validasi inti (mirror pola `LoanController@pay`)

| Aksi | Aturan |
|---|---|
| Setoran awal | `amount` wajib `integer min:1` — dicatat sebagai transaksi pertama saat create |
| Setoran tambahan | `amount >= 1`, saving harus `ongoing` |
| Penarikan | `amount >= 1` dan **`amount <= saldo saat ini`** → 422 `"Penarikan melebihi saldo tertahan"` |
| Profit | `amount >= 1`, saving `ongoing`; `retained` boolean (default false) |
| Tutup | saving harus `ongoing` |
| Hapus transaksi | ditolak jika membuat **saldo menjadi negatif** |
| Semua | transaksi hanya pada simpanan milik user yang login |

---

## 3. Skema Database (Migration Baru)

Ikuti konvensi loans: `softDeletes()` + `timestamps()` di semua tabel,
`foreignId()->constrained()->cascadeOnDelete()`, uang `bigInteger` (IDR integer).

### 3.1 `custodians` — (mirror `borrowers` + perbaikan)

```php
Schema::create('custodians', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // PERBAIKAN: borrowers tidak punya ini
    $table->string('name');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->string('address')->nullable();
    $table->softDeletes();
    $table->timestamps();
});
```

> Label UI: **"Penampung"** (perorangan yang memegang dana kita).

### 3.2 `savings` — (mirror `loans`, tanpa `total_amount` karena nominal fleksibel)

```php
Schema::create('savings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('custodian_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['ongoing', 'closed'])->default('ongoing');
    $table->date('date');       // tanggal simpanan dibuka
    $table->text('description')->nullable();
    $table->softDeletes();
    $table->timestamps();
});
```

> Tidak ada `due_date` (bukan kontrak berjatuh tempo) dan tidak ada `total_amount`
> (sumber kebenaran nominal = transaksi).

### 3.3 `saving_transactions`

```php
Schema::create('saving_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('saving_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['deposit', 'withdrawal', 'profit']); // UI: Setoran / Penarikan / Keuntungan
    $table->boolean('retained')->default(false);               // hanya relevan utk profit
    $table->bigInteger('amount')->default(0);
    $table->string('note')->nullable();
    $table->date('date');
    $table->softDeletes();
    $table->timestamps();
});
```

---

## 4. Model Eloquent

### `App\Models\Custodian`
- `fillable`: `user_id, name, email, phone, address`
- Relasi: `user()`, `hasMany(Saving::class)`
- `$appends = ['avatar']` (ikuti pola Borrower: ui-avatars.com)

### `App\Models\Saving`
- `fillable`: `user_id, custodian_id, status, date, description`
- Gunakan trait **`SoftDeletes`** (kolom sudah ada — lihat §8)
- Relasi: `user()`, `custodian()`, `hasMany(SavingTransaction::class)`,
  `morphMany(Image/File)` untuk lampiran
- `$appends` (pola computed accessor `Loan`):

```php
// total_deposit, total_withdrawal, total_profit, profit_retained, balance
getBalanceAttribute()
    = total_deposit + profit_retained − total_withdrawal
```

> ⚠️ Catatan sama seperti `Loan::paid`: accessor memanggil query per-row (N+1 saat list).
> Untuk v1 ikuti pola loans; optimasi `withSum()` bisa jadi perbaikan terpisah.

### `App\Models\SavingTransaction`
- `fillable`: `saving_id, type, retained, amount, note, date`
- ⚠️ **`note` wajib ada di fillable** — bug di `LoanTransaction` (note hilang dari fillable,
  input `pay()` silently dibuang) jangan ditiru.

---

## 5. API Endpoints (`routes/api.php`, group `jwt`)

### 5.1 Custodians (pola `BorrowerController`)

```php
Route::prefix('custodians')->group(function () {
    Route::get('/',      [CustodianController::class, 'getCustodians']); // ?search=
    Route::post('/',     [CustodianController::class, 'create']);
});
```

### 5.2 Savings

```php
Route::prefix('savings')->group(function () {
    Route::get('/',      [SavingController::class, 'getSavings']);       // ?status=ongoing|closed
    Route::post('/',     [SavingController::class, 'create']);           // multipart: simpanan + setoran awal + attachments[]
    Route::get('/{saving}', [SavingController::class, 'detail']);        // + total_deposit/withdrawal/profit/balance
    Route::post('/{saving}/deposit',  [SavingController::class, 'deposit']);  // setoran tambahan
    Route::post('/{saving}/withdraw', [SavingController::class, 'withdraw']); // tarik (validasi <= saldo)
    Route::post('/{saving}/profit',   [SavingController::class, 'profit']);   // untung (retained true/false)
    Route::post('/{saving}/close',    [SavingController::class, 'close']);    // tutup manual
    Route::delete('/{saving}',        [SavingController::class, 'delete']);   // hapus transaksi dulu, lalu saving
    Route::get('/custodian/{custodian}', [SavingController::class, 'getSavingsByCustodian']); // ?status=
});

Route::prefix('saving-transactions')->group(function () {
    Route::delete('/{savingTransaction}', [SavingTransactionController::class, 'delete']);
});
```

**Pola implementasi controller** — ikuti kode loans yang ada (bukan Form Request/Resource,
meski `agents.md` mengarah ke sana; konsistensi kode > konvensi dokumen yang belum dipakai):

- Validasi inline `Validator::make()` → 422 `{success:false, message, errors}`
- `DB::beginTransaction()` / `commit()` / `rollBack()` manual
- `JWTAuth::parseToken()->authenticate()` untuk user scoping
- Respon `ApiResponse::success($data, $messageID, $code)`
- `create()`: dalam satu transaksi DB → buat `Saving` + transaksi `deposit` pertama
  (+ `AttachmentService::handleAttachments` jika pakai lampiran)

---

## 6. Halaman Web (`routes/web.php` + Blade)

Mirror struktur loans, data tetap lewat `fetch` ke `/api/*` dengan Bearer token localStorage.

```php
Route::prefix('savings')->name('savings.')->group(function () {
    Route::get('/create', [SavingController::class, 'createPage'])->name('create.page'); // passes Custodian::all()
    Route::get('/', fn () => view('pages.savings.list'))->name('list.page');
    Route::get('/{saving}', fn () => view('pages.savings.detail'))->name('detail.page');
    // ⚠️ jangan duplikat ->name() seperti bug `loans.payment.page`
});
```

| Halaman | Isi |
|---|---|
| `pages/savings/list.blade.php` | Kartu ringkasan (Saldo Tertahan, Total Profit, Jml Aktif), filter status (client-side), aksi Detail/Tutup/Delete — pola `loans/list` |
| `pages/savings/create.blade.php` | Komponen `x-savings.custodian-input` (klon `borrower-input` + modal tambah penampung), nominal setoran awal (`formatIDR` live), tanggal, deskripsi, lampiran |
| `pages/savings/detail.blade.php` | Info penampung + saldo + total setoran/penarikan/profit, riwayat transaksi, aksi **Setor/Tarik/Profit via Bladewind modal** (tanpa halaman `/pay` terpisah — tidak ada kebutuhan bulk), Tutup, Delete |

**Navigasi**: tambah kartu `SIMPANAN` di `pages/home.blade.php` (grid 2→3) dan entry di
`pages/choose.blade.php`.

---

## 7. Lampiran (Opsional, disarankan)

Tabel `files`/`images` sudah polymorphic — tinggal `morphMany` di `Saving`. Tapi
`AttachmentService::handleAttachments()` **hardcode path `uploads/loans`** → refactor kecil:

```php
public function handleAttachments($model, $files, string $directory = 'uploads/loans')
```

Lampiran cukup **level simpanan** (bukti transfer setoran awal, dsb). Lampiran per-transaksi → future.

---

## 8. Perbaikan Jangan Ditiru dari Loans (Known Issues)

| # | Masalah di loans | Rencana di simpanan |
|---|---|---|
| 1 | `borrowers` tanpa `user_id` → user bisa melihat peminjam user lain | `custodians` **wajib** `user_id`, semua query di-scope |
| 2 | Route binding tanpa cek ownership (`/api/loans/{id}` lintas user) | Scope `where('user_id', auth()->id())` / cek di controller |
| 3 | `LoanTransaction.note` hilang dari `fillable` | `SavingTransaction` lengkap termasuk `note` |
| 4 | Kolom `softDeletes` ada tapi model gak pakai trait (delete = hard delete) | Aktifkan trait `SoftDeletes` di model baru |
| 5 | Duplikat `->name('payment.page')` | Cek unique route names |
| 6 | `getLoans` status via `havingRaw` bisa beda dengan kolom `status` | Filter status dari kolom enum saja |

---

## 9. Checklist Implementasi (urutan kerja)

- [x] Branch `feat/simpanan` + dokumen rencana ini
- [x] **Migration**: `custodians`, `savings`, `saving_transactions` → diuji via `migrate` (sqlite)
- [x] **Model**: `Custodian`, `Saving`, `SavingTransaction` (+ relations, `$appends`, SoftDeletes)
      ⚠️ relasi di `SavingTransaction` dinamai `parentSaving()` — `saving()` bentrok dengan
      static event `Model::saving()`
- [x] **API**: `CustodianController`, `SavingController` (CRUD + deposit/withdraw/profit/close),
      `SavingTransactionController@delete` + register di `routes/api.php`
- [x] **Opsional**: refactor `AttachmentService` (parameter directory, default `uploads/loans`)
      + lampiran di `Saving` (`uploads/savings`)
- [x] **Web route**: group `savings` (closure — create page tidak perlu data server-side,
      custodian di-fetch via `/api/custodians` client-side)
- [x] **Blade**: `pages/savings/{list,create,detail}.blade.php` + komponen
      `components/savings/custodian-input.blade.php`
- [x] **Navigasi**: kartu `SIMPANAN` di `home.blade.php` & entry di `choose.blade.php`
- [x] **Verifikasi**: `pint --test` passed (file baru); E2E API via curl (create → setor →
      over-withdraw 422 → tarik → profit ret/not → tutup → blokir transaksi → hapus transaksi
      guard saldo negatif → ownership lintas user 404 → soft delete); render halaman Blade
      HTTP 200 (perlu `npm run build` dulu bila manifest Vite belum ada)
- [ ] **QA manual di browser**: alur penuh via UI (populate select penampung, modal transaksi,
      formatIDR) — JS runtime belum diuji otomatis
- [ ] **PR** ke `main` (conventional commit: `feat: savings`)

---

## 10. Future / Di Luar Scope v1

- **Integrasi `cash_flows`**: setoran = pengeluaran, penarikan/profit = pemasukan. Tabel & model
  `CashFlow` sudah ada tapi feature-nya belum dibangun — saat cash-flow siap, hubungkan lewat
  event/hook agar tidak dobel catat.
- **Arisan per-periode** (jadwal setoran wajib, tracking giliran/putaran).
- **Target nominal + progress bar** (mirip `percent` di Loan) — desain fleksibel sudah mengantisipasi
  (tinggal tambah kolom `target_amount`).
- **Reopen simpanan** yang tertutup.
- **Lampiran per-transaksi**, aksi bulk, pagination (apabila data mulai besar).
- **Testing**: project belum punya test sama sekali — tambah factory
  (`CustodianFactory`, `SavingFactory`) + feature test untuk aturan saldo/ownership jadi nilai plus.
