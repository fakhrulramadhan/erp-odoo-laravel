# Panduan Penggunaan Lengkap — AppVerse ERP

**Versi:** 1.0  
**Tanggal:** Juli 2026  
**Platform:** Laravel 11 + React 19 + SQLite  

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Login & Autentikasi](#2-login--autentikasi)
3. [Dashboard Utama](#3-dashboard-utama)
4. [Modul Master Data](#4-modul-master-data)
5. [Modul Pembelian (Purchasing)](#5-modul-pembelian-purchasing)
6. [Modul Inventaris (Inventory)](#6-modul-inventaris-inventory)
7. [Modul Keuangan (Finance)](#7-modul-keuangan-finance)
8. [Modul Manufaktur (Manufacturing)](#8-modul-manufaktur-manufacturing)
9. [Modul HRM (Human Resource Management)](#9-modul-hrm-human-resource-management)
10. [Modul CRM (Customer Relationship Management)](#10-modul-crm-customer-relationship-management)
11. [Audit & Notifikasi](#11-audit--notifikasi)
12. [Pengaturan (Settings)](#12-pengaturan-settings)
13. [Laporan (Reports)](#13-laporan-reports)
14. [Tips & Trik](#14-tips--trik)

---

## 1. Pendahuluan

AppVerse ERP adalah sistem Enterprise Resource Planning terintegrasi yang dirancang untuk mengelola seluruh aspek bisnis dalam satu platform. Sistem ini mencakup:

- **Master Data** — Data dasar produk, pelanggan, vendor, gudang, dan akun
- **Pembelian** — Purchase orders, quotation, dan requisition
- **Inventaris** — Stok, picking, dan penyesuaian inventaris
- **Keuangan** — Jurnal, invoice, pembayaran, dan chart of accounts
- **Manufaktur** — Work orders, BOM, quality control, dan aset
- **HRM** — Karyawan, absensi, cuti, payroll, dan rekrutmen
- **CRM** — Leads, opportunities, dan aktivitas penjualan
- **Pengaturan** — Users, perusahaan, mata uang, dan pajak

### Persyaratan Sistem
- Browser modern (Chrome, Firefox, Edge, Safari)
- Resolusi layar minimal 1280×720
- Koneksi internet stabil

### Akses Default
| Field | Nilai |
|-------|-------|
| Email | `admin@demo-erp.com` |
| Password | `password` |

---

## 2. Login & Autentikasi

### Cara Login
1. Buka aplikasi di browser
2. Masukkan **email** dan **password** pada form login
3. Klik tombol **"Masuk"** (Login)
4. Sistem akan mengarahkan ke Dashboard setelah login berhasil

### Keamanan
- Sistem menggunakan **Sanctum Token** untuk autentikasi API
- Token akan otomatis kadaluarsa setelah sesi berakhir
- Klik **"Keluar"** (Logout) di menu profil untuk keluar dari sistem

---

## 3. Dashboard Utama

Dashboard menampilkan ringkasan seluruh aktivitas bisnis.

### Fitur Dashboard
- **Kartu Statistik** — Ringkasan jumlah produk, pelanggan, vendor, pesanan
- **Grafik Penjualan** — Visualisasi tren penjualan dan pembelian
- **Aktivitas Terbaru** — Daftar aktivitas audit terbaru
- **Notifikasi** — Pemberitahuan penting yang perlu perhatian
- **Aksi Cepat** — Tombol shortcut untuk membuat dokumen baru

### Navigasi
- **Sidebar Kiri** — Menu navigasi utama dikelompokkan per modul
- **Header Atas** — Profil pengguna, notifikasi, dan pencarian
- **Breadcrumb** — Menunjukkan posisi halaman saat ini

---

## 4. Modul Master Data

Master Data adalah fondasi sistem yang berisi data referensi yang digunakan oleh semua modul.

### 4.1 Kategori Produk (`/master-data/product-categories`)
**Tujuan:** Mengelompokkan produk berdasarkan kategori.

| Aksi | Deskripsi |
|------|-----------|
| **Tambah** | Klik tombol "Tambah Kategori" → isi nama, kode, deskripsi → Simpan |
| **Edit** | Klik ikon edit pada baris → ubah data → Simpan |
| **Hapus** | Klik ikon hapus → konfirmasi penghapusan |
| **Cari** | Gunakan kolom pencarian untuk filter berdasarkan nama |

### 4.2 Satuan Ukur (`/master-data/unit-of-measures`)
**Tujuan:** Mendefinisikan satuan pengukuran (kg, liter, pcs, box, dll).

- **Kode** — Kode unik satuan (contoh: KG, LTR, PCS)
- **Nama** — Nama lengkap satuan
- **Tipe** — Unit of Measure (satuan dasar) atau Unit of Measure Conversion (konversi)

### 4.3 Produk (`/master-data/products`)
**Tujuan:** Mengelola data produk/barang yang diperjualbelikan.

**Form Produk:**
- **Nama Produk** — Nama lengkap produk
- **Kode Internal** — Kode SKU produk
- **Kategori** — Pilih dari dropdown kategori
- **Tipe** — Storable (bisa disimpan), Consumable (habis pakai), Service (jasa)
- **Satuan** — Satuan dasar produk
- **Harga Jual** — Harga jual default
- **Harga Beli** — Harga beli dari vendor
- **Stok Minimum** — Batas minimum stok untuk peringatan

**Fitur Tambahan:**
- Filter berdasarkan kategori, tipe, dan status
- Toggle status aktif/nonaktif
- Ekspor data ke CSV

### 4.4 Pelanggan (`/master-data/customers`)
**Tujuan:** Mengelola data pelanggan.

**Form Pelanggan:**
- Nama, Email, Telepon, Alamat
- NPWP (opsional)
- Batas Kredit
- Syarat Pembayaran

### 4.5 Vendor (`/master-data/vendors`)
**Tujuan:** Mengelola data supplier/vendor.

**Form Vendor:**
- Nama Perusahaan, Kontak Person
- Email, Telepon, Alamat
- NPWP, Syarat Pembayaran
- Rating (1-5 bintang)

### 4.6 Gudang (`/master-data/warehouses`)
**Tujuan:** Mendefinisikan lokasi penyimpanan barang.

- Nama Gudang, Kode, Alamat
- Penanggung Jawab
- Status Aktif/Nonaktif

### 4.7 Lokasi Stok (`/master-data/stock-locations`)
**Tujuan:** Mendefinisikan lokasi spesifik di dalam gudang.

- Nama Lokasi, Kode
- Gudang Induk
- Tipe Lokasi (Internal, Supplier, Customer, Transit, Loss)

### 4.8 Rekening Bank (`/master-data/bank-accounts`)
**Tujuan:** Mengelola data rekening bank perusahaan.

- Nama Bank, Nomor Rekening, Atas Nama
- Mata Uang, Saldo Awal
- Status Aktif

### 4.9 Metode Pembayaran (`/master-data/payment-methods`)
**Tujuan:** Mendefinisikan cara pembayaran yang diterima.

- Nama Metode, Tipe (Cash, Bank Transfer, Credit Card, E-Wallet)
- Rekening Bank Terkait
- Biaya Administrasi

### 4.10 Chart of Accounts (`/master-data/chart-of-accounts`)
**Tujuan:** Struktur akun akuntansi perusahaan.

- **Kode Akun** — Nomor unik akun (contoh: 1000, 1100, 4000)
- **Nama Akun** — Deskripsi akun
- **Tipe** — Asset, Liability, Equity, Revenue, Expense
- **Akun Induk** — Untuk membuat hierarki akun
- **Saldo Normal** — Debit atau Kredit

---

## 5. Modul Pembelian (Purchasing)

Modul ini mengelola seluruh proses pengadaan barang dari vendor.

### 5.1 Purchase Requisition (`/purchasing/requisitions`)
**Tujuan:** Permintaan pembelian barang dari departemen.

**Alur Kerja:**
1. Klik **"Buat Requisition"**
2. Pilih departemen, tanggal yang dibutuhkan
3. Tambahkan baris item: produk, quantity, estimasi harga
4. Simpan → Status: **Draft**
5. Kirim untuk approval → Status: **Pending Approval**
6. Manager menyetujui → Status: **Approved**
7. Diteruskan ke Purchasing untuk dibuat PO

### 5.2 Supplier Quotation (`/purchasing/quotations`)
**Tujuan:** Penawaran harga dari vendor.

**Alur Kerja:**
1. Buat Quotation → pilih vendor
2. Tambahkan item yang di-quiry
3. Isi harga penawaran vendor
4. Simpan → **Draft**
5. Konfirmasi → **Confirmed**
6. Pilih quotation terbaik → buat PO dari quotation

### 5.3 Purchase Orders (`/purchasing/purchase-orders`)
**Tujuan:** Pesanan pembelian resmi ke vendor.

**Alur Kerja:**
1. Klik **"Buat Purchase Order"**
2. Pilih vendor, tanggal pengiriman
3. Tambahkan item: produk, quantity, harga satuan
4. Sistem hitung subtotal, pajak, dan total otomatis
5. Simpan → **Draft**
6. Kirim ke vendor → **Sent**
7. Vendor konfirmasi → **Confirmed**
8. Terima barang (buat Stock Picking) → **Received**
9. Terima invoice vendor → proses pembayaran

**Status PO:** Draft → Sent → Confirmed → Partially Received → Received → Done / Cancelled

### 5.4 Vendor Pricelists (`/purchasing/pricelists`)
**Tujuan:** Daftar harga dari vendor per produk.

- Pilih vendor dan produk
- Isi harga, minimum order quantity (MOQ)
- Tanggal berlaku mulai & akhir

---

## 6. Modul Inventaris (Inventory)

Modul ini mengelola pergerakan dan ketersediaan stok barang.

### 6.1 Stok Quants (`/inventory/quants`)
**Tujuan:** Melihat ketersediaan stok saat ini.

**Informasi yang ditampilkan:**
- Produk, Gudang, Lokasi
- Quantity Tersedia, Quantity Reserved, Quantity On Hand
- Nilai inventaris

### 6.2 Stock Pickings (`/inventory/pickings`)
**Tujuan:** Mengelola pergerakan barang masuk/keluar.

**Tipe Picking:**
- **Receipt (Penerimaan)** — Barang masuk dari vendor
- **Internal Transfer** — Pindah antar lokasi/gudang
- **Delivery (Pengiriman)** — Barang keluar ke pelanggan
- **Return** — Pengembalian barang

**Alur Kerja Picking:**
1. Picking otomatis dibuat dari PO (Receipt) atau SO (Delivery)
2. Staf gudang memeriksa barang
3. Konfirmasi picking → stok terupdate
4. Barang berpindah lokasi

**Status Picking:** Draft → Waiting → Ready → Done / Cancelled

### 6.3 Inventory Adjustments (`/inventory/adjustments`)
**Tujuan:** Koreksi stok fisik (stock opname).

**Alur Kerja:**
1. Klik **"Buat Adjustment"**
2. Pilih produk dan lokasi
3. Masukkan quantity aktual fisik
4. Sistem hitung selisih otomatis
5. Simpan → stok terkoreksi

---

## 7. Modul Keuangan (Finance)

Modul ini mengelola seluruh transaksi keuangan perusahaan.

### 7.1 Finance Dashboard (`/finance`)
**Tujuan:** Ringkasan kondisi keuangan.

**Fitur:**
- Total Pendapatan, Pengeluaran, Laba
- Grafik arus kas bulanan
- Piutang & Hutang jatuh tempo
- Top 5 pengeluaran terbesar

### 7.2 Jurnal Entries (`/finance/journal-entries`)
**Tujuan:** Mencatat transaksi akuntansi.

**Alur Kerja:**
1. Klik **"Buat Jurnal Entry"**
2. Pilih tanggal dan tipe jurnal
3. Tambahkan baris: akun, debit, kredit
4. Pastikan total debit = total kredit (balanced)
5. Simpan → **Draft**
6. Post → **Posted** (tidak bisa diubah)

**Status:** Draft → Posted → Cancelled

### 7.3 Invoices (`/finance/invoices`)
**Tujuan:** Mengelola invoice pelanggan dan vendor.

**Tipe Invoice:**
- **Customer Invoice** — Tagihan ke pelanggan
- **Vendor Bill** — Tagihan dari vendor
- **Credit Note** — Nota kredit
- **Debit Note** — Nota debet

**Alur Kerja Invoice:**
1. Buat invoice → pilih partner (pelanggan/vendor)
2. Tambahkan baris: produk/jasa, quantity, harga
3. Hitung pajak otomatis
4. Simpan → **Draft**
5. Validate → **Open**
6. Record Payment → **Paid**

### 7.4 Payments (`/finance/payments`)
**Tujuan:** Mencatat penerimaan dan pengeluaran kas/bank.

**Tipe Payment:**
- **Receive** — Penerimaan pembayaran dari pelanggan
- **Send** — Pembayaran ke vendor

**Alur Kerja:**
1. Buat payment → pilih partner dan tipe
2. Pilih metode pembayaran dan rekening bank
3. Masukkan jumlah
4. Link ke invoice (opsional)
5. Konfirmasi → kas/bank terupdate

---

## 8. Modul Manufaktur (Manufacturing)

Modul ini mengelola proses produksi barang.

### 8.1 Manufacturing Dashboard (`/manufacturing`)
**Tujuan:** Ringkasan operasional manufaktur.

**Fitur:**
- Jumlah MO berdasarkan status
- Utilisasi work center
- Status quality checks
- Grafik produksi bulanan

### 8.2 Bill of Materials (BOM) (`/manufacturing/boms`)
**Tujuan:** Resep/formula untuk membuat produk.

**Komponen BOM:**
- **Produk Jadi** — Produk yang akan dibuat
- **Komponen** — Daftar bahan baku yang dibutuhkan
- **Quantity** — Jumlah setiap komponen per unit produk
- **Work Center** — Lokasi produksi
- **Routing** — Urutan operasi

**Alur BOM:**
1. Buat BOM → pilih produk jadi
2. Tambahkan komponen beserta quantity
3. Simpan → **Draft**
4. Approve → **Approved**

### 8.3 Work Centers (`/manufacturing/work-centers`)
**Tujuan:** Lokasi/mesin tempat produksi dilakukan.

- Nama, Kode, Lokasi
- Kapasitas (jam kerja per hari)
- Biaya per jam
- Operator yang ditugaskan

### 8.4 Routings (`/manufacturing/routings`)
**Tujuan:** Urutan langkah-langkah produksi.

- Nama Routing
- Daftar operasi berurutan (sequence)
- Setiap operasi: nama, work center, estimasi waktu

### 8.5 Manufacturing Orders (`/manufacturing/orders`)
**Tujuan:** Perintah produksi untuk membuat produk.

**Alur Kerja MO:**
1. Klik **"Buat Manufacturing Order"**
2. Pilih produk dan BOM
3. Masukkan quantity yang akan diproduksi
4. Sistem otomatis menghitung kebutuhan bahan baku
5. Simpan → **Draft**
6. Confirm → **Confirmed** (bahan baku di-reserve)
7. Approve → **Approved**
8. Start Production → **In Progress**
9. Staf produksi mengerjakan work orders
10. Finish Production → **Done**

**Status MO:** Draft → Confirmed → Approved → In Progress → Done / Cancelled

### 8.6 Quality Checks (`/manufacturing/quality`)
**Tujuan:** Pengendalian kualitas produk.

**Alur Quality Check:**
1. QC Point mendefinisikan kapan pengecekan dilakukan
2. Saat MO berjalan, quality check otomatis dibuat
3. Inspektur mengisi hasil pengecekan
4. Setiap item: Pass / Fail / On Hold
5. Jika semua pass → MO bisa lanjut
6. Jika ada fail → perlu perbaikan

### 8.7 Scrap Orders (`/manufacturing/scraps`)
**Tujuan:** Mencatat barang yang rusak/tidak lolos QC.

- Pilih sumber (MO/Stock Picking)
- Pilih produk dan quantity yang di-scrap
- Pilih tipe scrap: Defective, Expired, Damaged
- Konfirmasi → stok dikurangi

### 8.8 Equipment (`/manufacturing/equipment`)
**Tujuan:** Mengelola data peralatan/mesin produksi.

- Nama, Kode, Serial Number
- Work Center penempatan
- Tanggal pembelian, garansi
- Status: Available, In Use, Under Maintenance, Broken

### 8.9 Maintenance Orders (`/manufacturing/maintenance`)
**Tujuan:** Jadwal dan riwayat perawatan peralatan.

**Tipe Maintenance:**
- **Preventive** — Perawatan terjadwal
- **Corrective** — Perbaikan kerusakan

**Alur:**
1. Buat maintenance order → pilih equipment
2. Pilih tipe maintenance
3. Jadwalkan tanggal
4. Assign teknisi
5. Eksekusi → catat hasil
6. Selesai → status: Done

### 8.10 Aset Tetap (`/manufacturing/assets`)
**Tujuan:** Mengelola aset tetap perusahaan.

**Alur Aset:**
1. Daftarkan aset → kategori, nilai perolehan, tanggal
2. Metode depresiasi: Straight Line, Declining Balance
3. Sistem hitung depresiasi otomatis setiap periode
4. Transfer aset antar departemen/lokasi
5. Dispose aset ketika sudah tidak digunakan

**Status Aset:** Draft → Active → Transferred → Disposed

---

## 9. Modul HRM (Human Resource Management)

Modul ini mengelola seluruh aspek sumber daya manusia.

### 9.1 HR Dashboard (`/hrm`)
**Tujuan:** Ringkasan data kepegawaian.

**Fitur:**
- Total karyawan aktif, cuti, baru
- Statistik absensi hari ini
- Pengajuan cuti pending
- Payroll yang perlu diproses
- Lowongan kerja aktif

### 9.2 Karyawan (`/hrm/employees`)
**Tujuan:** Mengelola data karyawan.

**Form Karyawan:**
- **Data Pribadi** — Nama, NIK, tanggal lahir, jenis kelamin, alamat
- **Data Pekerjaan** — NIK karyawan, departemen, posisi, atasan langsung
- **Tipe Kerja** — Full Time, Part Time, Contract, Freelance, Internship
- **Status** — Active, Probation, On Leave, Resigned, Terminated
- **Tanggal Masuk** — Tanggal mulai bekerja
- **Gaji** — Informasi gaji dasar

**Fitur:**
- Filter status, departemen, tipe kerja
- Terminasi karyawan (soft delete)
- Lihat detail lengkap karyawan

### 9.3 Kontrak Kerja (`/hrm/contracts`)
**Tujuan:** Mengelola kontrak kerja karyawan.

- Nomor kontrak, tipe kontrak
- Tanggal mulai & berakhir
- Gaji kontrak
- Status: Draft, Active, Expired, Renewed, Terminated

### 9.4 Dokumen Karyawan (`/hrm/documents`)
**Tujuan:** Mengelola dokumen karyawan (KTP, Ijazah, Sertifikat, dll).

- Tipe dokumen, nomor dokumen
- Tanggal terbit & kadaluarsa
- File upload (opsional)
- Peringatan dokumen akan kadaluarsa

### 9.5 Jadwal Kerja (`/hrm/work-schedules`)
**Tujuan:** Mendefinisikan jadwal kerja karyawan.

- Nama jadwal (contoh: "Jam Kerja Normal", "Shift Pagi")
- Hari kerja (Senin-Jumat atau custom)
- Jam masuk & jam keluar
- Toleransi keterlambatan

### 9.6 Shift (`/hrm/shifts`)
**Tujuan:** Mendefinisikan shift kerja.

- Nama Shift, Kode
- Jam mulai & jam selesai
- Break time
- Hari berlaku

### 9.7 Absensi (`/hrm/attendance`)
**Tujuan:** Mencatat kehadiran karyawan.

**Fitur:**
- **Check In** — Catat jam masuk
- **Check Out** — Catat jam keluar
- Sistem otomatis hitung keterlambatan
- Status: Checked In, Checked Out, Absent, Late, On Leave, Holiday

**Filter:** Tanggal, status, karyawan

### 9.8 Koreksi Absensi (`/hrm/attendance-corrections`)
**Tujuan:** Mengajukan koreksi jika terjadi kesalahan absensi.

**Alur:**
1. Karyawan ajukan koreksi → alasan & jam yang benar
2. Status: **Pending**
3. Manager review → **Approve** atau **Reject**

### 9.9 Tipe Cuti (`/hrm/leave-types`)
**Tujuan:** Mendefinisikan jenis-jenis cuti.

- Nama cuti (Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, dll)
- Jumlah hari per tahun
- Dibayar/Tidak Dibayar
- Bisa diakumulasi ke tahun berikutnya

### 9.10 Saldo Cuti (`/hrm/leave-balances`)
**Tujuan:** Melihat sisa cuti karyawan.

- Karyawan, Tipe Cuti, Tahun
- Total Alokasi, Terpakai, Sisa

### 9.11 Pengajuan Cuti (`/hrm/leave-requests`)
**Tujuan:** Mengelola permintaan cuti karyawan.

**Alur:**
1. Karyawan ajukan cuti → pilih tipe, tanggal mulai & akhir, alasan
2. Status: **Pending**
3. Manager review:
   - **Approve** → status: Approved, saldo cuti berkurang
   - **Reject** → status: Rejected, alasan penolakan
4. Karyawan bisa **Cancel** sebelum di-approve

### 9.12 Komponen Gaji (`/hrm/payroll-components`)
**Tujuan:** Mendefinisikan komponen gaji.

**Tipe Komponen:**
- **Earning** — Tunjangan, bonus, insentif
- **Deduction** — Potongan (BPJS, pinjaman, dll)
- **Employer Contribution** — Iuran perusahaan

Setiap komponen: Nama, Kode, Tipe, Formula/Nilai Tetap

### 9.13 Struktur Gaji (`/hrm/salary-structures`)
**Tujuan:** Template komponen gaji yang bisa diterapkan ke karyawan.

- Nama struktur
- Daftar komponen gaji dan urutan perhitungan
- Bisa di-clone untuk efisiensi

### 9.14 Gaji Karyawan (`/hrm/employee-salaries`)
**Tujuan:** Menetapkan struktur gaji ke setiap karyawan.

- Pilih karyawan dan struktur gaji
- Override nilai komponen jika perlu
- Berlaku efektif dari tanggal tertentu

### 9.15 Periode Payroll (`/hrm/payroll-periods`)
**Tujuan:** Mendefinisikan periode penggajian.

- Nama periode (contoh: "Januari 2026")
- Tanggal mulai & akhir
- Status: Open, Closed

### 9.16 Payroll Runs (`/hrm/payroll-runs`)
**Tujuan:** Proses penghitungan gaji massal.

**Alur Payroll:**
1. Buat Payroll Run → pilih periode
2. Status: **Draft**
3. **Process** → sistem hitung gaji semua karyawan
4. Status: **Computed** → review hasil
5. **Confirm** → lock perhitungan
6. **Approve** → siap dibayar
7. Buat pembayaran → status: **Paid**

### 9.17 Slip Gaji (`/hrm/payslips`)
**Tujuan:** Detail penghitungan gaji per karyawan.

**Informasi Slip Gaji:**
- Karyawan, Periode
- Rincian earning (gaji pokok, tunjangan)
- Rincian deduction (potongan, pajak)
- Total Take Home Pay
- Status: Draft, Confirmed, Paid

### 9.18 Lowongan Kerja (`/hrm/job-vacancies`)
**Tujuan:** Mengelola rekrutmen.

**Alur:**
1. Buat lowongan → judul, posisi, departemen, deskripsi, kualifikasi
2. Publish → status: **Published** (terlihat publik)
3. Lamaran masuk → jadi Applicant
4. Tutup lowongan → status: **Closed** atau **Expired**

### 9.19 Pelamar (`/hrm/applicants`)
**Tujuan:** Mengelola pelamar kerja.

**Tahapan (Pipeline):**
1. **New** — Lamaran baru masuk
2. **Screening** — Seleksi CV
3. **Interview** — Wawancara
4. **Technical Test** — Tes teknis
5. **Offer** — Penawaran kerja
6. **Hired** — Diterima
7. **Rejected** — Ditolak (bisa di tahap mana pun)

### 9.20 Wawancara (`/hrm/interviews`)
**Tujuan:** Mengelola jadwal wawancara.

- Pelamar, interviewer, tanggal, lokasi
- Tipe: Phone, Video, In Person, Technical
- Submit feedback setelah wawancara

### 9.21 Klaim Pengeluaran (`/hrm/expense-claims`)
**Tujuan:** Mengelola pengajuan reimbursement karyawan.

**Alur:**
1. Karyawan ajukan klaim → judul, tanggal, detail baris pengeluaran
2. Status: **Draft** → **Submitted**
3. Manager review → **Approved** atau **Rejected**
4. Finance proses → **Mark as Paid**

---

## 10. Modul CRM (Customer Relationship Management)

Modul ini mengelola hubungan dengan calon pelanggan dan prospek.

### 10.1 Leads (Prospek)
- Sumber lead: Website, Referral, Cold Call, Exhibition, dll
- Status: New, Contacted, Qualified, Lost, Won
- Konversi lead ke opportunity

### 10.2 Opportunities
- Pipeline penjualan dengan tahapan
- Tahapan: Prospecting, Qualification, Proposal, Negotiation, Closed Won, Closed Lost
- Estimasi revenue dan probability

### 10.3 CRM Activities
- Jadwalkan follow-up, meeting, call
- Link ke lead atau opportunity
- Tracking semua interaksi

---

## 11. Audit & Notifikasi

### 11.1 Audit Trail (`/audit-log`)
**Tujuan:** Mencatat semua aktivitas di sistem.

**Informasi yang dicatat:**
- Siapa (user) melakukan apa (aksi) kapan (timestamp)
- Tabel mana yang terpengaruh
- Data sebelum & sesudah perubahan (old/new values)
- IP address dan user agent

**Filter:** Tanggal, user, tabel, tipe event (create, update, delete)

### 11.2 Notifikasi (`/notifications`)
**Tujuan:** Pemberitahuan real-time.

- Notifikasi sistem (stok rendah, dokumen jatuh tempo)
- Notifikasi approval (butuh persetujuan)
- Tandai sudah dibaca / tandai semua

---

## 12. Pengaturan (Settings)

### 12.1 Users (`/settings/users`)
**Tujuan:** Mengelola akun pengguna sistem.

- Tambah user baru → nama, email, password, role
- Edit profil user
- Assign role: super-admin, admin, manager, staff
- Nonaktifkan user

### 12.2 Companies (`/settings/companies`)
**Tujuan:** Data perusahaan.

- Nama, Nama Legal, NPWP
- Alamat, Kota, Negara
- Email, Telepon, Website
- Logo perusahaan

### 12.3 Pengaturan Lain
Akses melalui API:
- **Currencies** — Mata uang dan kurs
- **Tax Settings** — Pajak (PPN, PPh)
- **Numbering Sequences** — Nomor otomatis untuk dokumen
- **Branches** — Cabang perusahaan
- **Departments** — Departemen/divisi
- **Positions** — Jabatan

---

## 13. Laporan (Reports)

Halaman laporan tersedia di `/reports` dengan beberapa kategori:

| Kategori | Isi |
|----------|-----|
| **Purchase Reports** | Laporan pembelian per vendor, produk, periode |
| **Inventory Reports** | Laporan stok, pergerakan barang, valuasi |
| **Finance Reports** | Laba/Rugi, Neraca, Arus Kas |
| **Manufacturing Reports** | Efisiensi produksi, biaya produksi |
| **Sales Reports** | Laporan penjualan per pelanggan, produk |
| **HR Reports** | Absensi, payroll, turnover |

---

## 14. Tips & Trik

### Navigasi Efisien
- Gunakan **sidebar** untuk berpindah modul dengan cepat
- Manfaatkan **filter dan pencarian** di setiap halaman
- Gunakan **breadcrumb** untuk mengetahui posisi Anda

### Manajemen Data
- Selalu **validasi data** sebelum menyimpan
- Gunakan **export** untuk backup data secara berkala
- Perhatikan **status dokumen** — ikuti alur kerja yang benar

### Keamanan
- **Jangan bagikan** akun dengan orang lain
- **Logout** setelah selesai menggunakan sistem
- Gunakan password yang kuat
- Periksa **audit trail** untuk memantau aktivitas mencurigakan

### Troubleshooting Umum

| Masalah | Solusi |
|---------|--------|
| Halaman tidak bisa diakses | Periksa role & permission Anda |
| Data tidak muncul | Pastikan filter sudah di-reset |
| Error saat menyimpan | Periksa field wajib yang belum diisi |
| Stok negatif | Lakukan inventory adjustment |
| Payroll error | Pastikan semua komponen gaji sudah dikonfigurasi |

---

## Hak Cipta

**AppVerse ERP** © 2026. Dokumen ini dibuat sebagai panduan penggunaan lengkap sistem ERP.

---

*Dokumen terakhir diperbarui: 9 Juli 2026*
