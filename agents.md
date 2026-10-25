# Project Overview: Saku (Pencatatan Keuangan)

Dokumen ini berisi konteks, panduan, dan arsitektur proyek untuk membantu AI Agents (seperti OpenCode) atau developer baru dalam memahami dan mengembangkan aplikasi ini.

## 📌 Deskripsi Proyek
Aplikasi pencatatan keuangan pribadi/bisnis. Proyek ini dibangun dengan konsep **API-First**, memisahkan antara Backend (BE) dan Frontend (FE). Tujuannya agar Backend dapat dengan mudah digunakan kembali (cross-platform) untuk aplikasi Mobile di masa mendatang.

## 🛠️ Tech Stack & Arsitektur
- **Backend Framework**: Laravel
- **Arsitektur**: RESTful API (Backend dan Frontend terpisah)
- **Response Format**: JSON (Menggunakan API Resources)
- **Database**: MySQL / PostgreSQL (dikelola melalui Migration Laravel)

## 🚀 Status Fitur
### 1. Loans (Piutang) - ✅ *Sudah Berjalan*
Modul ini digunakan untuk mencatat **Piutang** (uang kita yang dipinjam oleh orang lain / hutang orang lain ke kita).
- Fitur terkait: Pencatatan peminjam, nominal, tanggal pinjaman, status pelunasan, dan riwayat pembayaran cicilan (jika ada).

### 2. Rencana Pengembangan Berikutnya (Roadmap)
- Integrasi ke Frontend terpisah (Web SPA).
- Dukungan untuk platform Mobile (Flutter/React Native) menggunakan API yang sama.
- Penambahan modul keuangan lainnya (Pemasukan, Pengeluaran, Hutang, Laporan Keuangan, dll).

## 📜 Panduan Pengembangan (Guidelines for AI Agents)

Saat menulis atau memodifikasi kode di proyek ini, harap patuhi aturan berikut:

### 1. Pendekatan API-First
- Karena FE dan BE terpisah, semua output dari Backend **wajib** berupa JSON API.
- Jangan gunakan Blade templates untuk merender UI fitur utama (kecuali untuk halaman statis, email, atau admin dashboard sederhana jika diminta).
- Gunakan `routes/api.php` untuk routing aplikasi.

### 2. Standar Struktur & Kode
- **Controller**: Jaga Controller agar tetap ramping (Thin Controller). Controller hanya bertugas menangani HTTP Request dan Response.
- **Validation**: Gunakan **Form Request Validation** (contoh: `StoreLoanRequest`, `UpdateLoanRequest`) untuk memvalidasi input, jangan taruh validasi di dalam Controller.
- **API Resources**: Gunakan `JsonResource` (`php artisan make:resource`) untuk memformat respons API agar seragam dan menyembunyikan kolom database yang sensitif.
- **Business Logic**: Jika logika mulai kompleks, pisahkan ke dalam kelas `Service` atau `Action` di folder `app/Services` atau `app/Actions`.
- **Response Format**: Buat format JSON yang konsisten, contoh:
  ```json
  {
    "success": true,
    "message": "Data berhasil diambil",
    "data": { ... }
  }
  ```

### 3. Database & Eloquent
- Gunakan **Migration** untuk semua perubahan struktur tabel. Jangan mengubah database secara manual.
- Terapkan relasi Eloquent yang tepat (One-to-Many, Many-to-Many) dan gunakan *Eager Loading* (`with()`) untuk mencegah N+1 Query problem.

### 4. Authentication (Jika Nanti Ditambahkan)
- Gunakan **Laravel Sanctum** atau **Laravel Passport** karena aplikasi menggunakan sistem token berbasis API (bukan Session berbasis web tradisional).

---
*Catatan untuk Agent: Selalu baca dokumen ini untuk memastikan bahwa penambahan kode tidak keluar dari jalur arsitektur API-First yang disiapkan untuk lintas platform (Web & Mobile).*