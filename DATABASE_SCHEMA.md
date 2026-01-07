# MobileNest Database Schema Documentation

**Database Name:** `mobilenest_db`  
**Last Updated:** January 8, 2026  
**Version:** 2.0

---

## 📊 Database Overview

Database `mobilenest_db` adalah database untuk platform e-commerce MobileNest yang mengelola data produk, pengguna, transaksi, promosi, pengiriman, dan review.

### 📈 Tabel Utama (12 Tables)
1. **admin** - Data administrator sistem
2. **users** - Data pengguna/pelanggan
3. **produk** - Katalog produk
4. **promo** - Program promosi dan diskon
5. **transaksi** - Riwayat transaksi/pesanan (deprecated, gunakan pesanan)
6. **detail_transaksi** - Detail item dalam transaksi (deprecated)
7. **keranjang** - Shopping cart pelanggan
8. **ulasan** - Review dan rating produk
9. **pengiriman** - Data pengiriman/shipping ⭐ NEW
10. **pesanan** - Riwayat pesanan pengguna ⭐ NEW
11. **detail_pesanan** - Detail item dalam pesanan ⭐ NEW

---

## 📋 Struktur Tabel Lengkap

### 1. **users** - Tabel User/Pelanggan
Menyimpan data semua pengguna yang terdaftar di platform.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_user` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik user |
| `username` | VARCHAR(50) | UNIQUE, NOT NULL | Username login |
| `password` | VARCHAR(255) | NOT NULL | Password terenkripsi |
| `nama_lengkap` | VARCHAR(100) | NOT NULL | Nama lengkap user |
| `email` | VARCHAR(100) | NOT NULL | Email user |
| `no_telepon` | VARCHAR(15) | | Nomor telepon |
| `alamat` | TEXT | | Alamat lengkap |
| `tanggal_daftar` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu pendaftaran |
| `status_akun` | ENUM('Aktif', 'Nonaktif') | DEFAULT 'Aktif' | Status akun user |

**Sample Data:** 6 users (user1, user2, testing, testing2, salambim, salambim2)

---

### 2. **admin** - Tabel Administrator
Menyimpan data admin yang memiliki akses ke dashboard admin.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_admin` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik admin |
| `username` | VARCHAR(50) | UNIQUE, NOT NULL | Username admin |
| `password` | VARCHAR(255) | NOT NULL | Password terenkripsi |
| `nama_lengkap` | VARCHAR(100) | NOT NULL | Nama lengkap admin |
| `email` | VARCHAR(100) | NOT NULL | Email admin |
| `no_telepon` | VARCHAR(15) | | Nomor telepon |
| `tanggal_dibuat` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu akun dibuat |

**Sample Data:** 1 admin (admin)

---

### 3. **produk** - Tabel Produk/Katalog
Menyimpan informasi semua produk yang dijual.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_produk` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik produk |
| `nama_produk` | VARCHAR(100) | NOT NULL | Nama produk |
| `merek` | VARCHAR(50) | | Merek/brand produk |
| `deskripsi` | TEXT | | Deskripsi detail produk |
| `spesifikasi` | TEXT | | Spesifikasi teknis produk |
| `harga` | DECIMAL(10,2) | NOT NULL | Harga jual produk |
| `stok` | INT | NOT NULL DEFAULT 0 | Jumlah stok tersedia |
| `gambar` | VARCHAR(255) | | Path/URL gambar produk |
| `kategori` | VARCHAR(50) | | Kategori produk |
| `status_produk` | ENUM('Tersedia', 'Habis') | DEFAULT 'Tersedia' | Status ketersediaan |
| `tanggal_ditambahkan` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu produk ditambah |

**Sample Data:** 13 produk (smartphone: Samsung Galaxy S23, iPhone 14 Pro, Xiaomi Redmi Note, dll)

---

### 4. **promo** - Tabel Program Promosi
Menyimpan data promosi dan program diskon.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_promo` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik promo |
| `nama_promo` | VARCHAR(100) | NOT NULL | Nama program promosi |
| `jenis_promo` | VARCHAR(50) | | Jenis diskon (persentase/nominal) |
| `nilai_diskon` | DECIMAL(10,2) | | Nilai diskon yang diberikan |
| `persentase_diskon` | DECIMAL(5,2) | | Persentase diskon (jika %), max 100% |
| `tanggal_mulai` | DATE | | Tanggal mulai promosi |
| `tanggal_selesai` | DATE | | Tanggal akhir promosi |
| `status_promo` | ENUM('Aktif', 'Nonaktif') | DEFAULT 'Aktif' | Status promosi aktif |
| `deskripsi` | TEXT | | Deskripsi lengkap promosi |

**Sample Data:** 2 promo (Diskon Akhir Tahun, Flash Sale Midnight)

---

### 5. **transaksi** - Tabel Transaksi/Pesanan (DEPRECATED)
⚠️ **Deprecated** - Gunakan tabel `pesanan` untuk pesanan baru.

Menyimpan riwayat transaksi/pesanan dari pengguna.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_transaksi` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik transaksi |
| `id_user` | INT | FOREIGN KEY (users.id_user) | ID pengguna |
| `total_harga` | DECIMAL(12,2) | NOT NULL | Total harga transaksi |
| `status_pesanan` | VARCHAR(50) | | Status: Pending, Konfirmasi, Dikirim, Selesai |
| `metode_pembayaran` | VARCHAR(50) | | Metode: Transfer, COD, E-wallet, dll |
| `alamat_pengiriman` | TEXT | | Alamat tujuan pengiriman |
| `no_resi` | VARCHAR(50) | | Nomor resi pengiriman |
| `tanggal_transaksi` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu transaksi |
| `tanggal_dikirim` | DATETIME | | Waktu barang dikirim |
| `kode_transaksi` | VARCHAR(50) | | Kode unik transaksi |
| `catatan_user` | TEXT | | Catatan/note dari pembeli |
| `bukti_pembayaran` | VARCHAR(255) | | Path bukti pembayaran |

**Sample Data:** Kosong (0 rows)

---

### 6. **detail_transaksi** - Tabel Detail Transaksi (DEPRECATED)
⚠️ **Deprecated** - Gunakan tabel `detail_pesanan` untuk detail pesanan baru.

Menyimpan breakdown item/produk dalam setiap transaksi.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_detail` | INT | PRIMARY KEY, AUTO_INCREMENT | ID detail item |
| `id_transaksi` | INT | FOREIGN KEY (transaksi.id_transaksi) | ID transaksi referensi |
| `id_produk` | INT | FOREIGN KEY (produk.id_produk) | ID produk yang dibeli |
| `jumlah` | INT | NOT NULL | Jumlah item yang dibeli |
| `harga_satuan` | DECIMAL(10,2) | NOT NULL | Harga per unit saat pembelian |
| `subtotal` | DECIMAL(12,2) | NOT NULL | Total untuk item ini (jumlah × harga_satuan) |

**Sample Data:** Kosong (0 rows)

---

### 7. **keranjang** - Tabel Shopping Cart
Menyimpan item-item yang ada di keranjang belanja pengguna.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_keranjang` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik keranjang |
| `id_user` | INT | FOREIGN KEY (users.id_user) | ID pemilik keranjang |
| `id_produk` | INT | FOREIGN KEY (produk.id_produk) | ID produk di keranjang |
| `jumlah` | INT | NOT NULL DEFAULT 1 | Jumlah item |
| `tanggal_ditambahkan` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu item ditambah |

**Sample Data:** Kosong (0 rows)

---

### 8. **ulasan** - Tabel Review/Rating
Menyimpan review dan rating dari pengguna terhadap produk.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_ulasan` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik review |
| `id_user` | INT | FOREIGN KEY (users.id_user) | ID user yang memberi review |
| `id_produk` | INT | FOREIGN KEY (produk.id_produk) | ID produk yang direview |
| `rating` | INT | CHECK (1-5) | Rating 1-5 bintang |
| `komentar` | TEXT | | Komentar/review teks |
| `tanggal_ulasan` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu review dibuat |

**Sample Data:** Kosong (0 rows)

---

### 9. **pengiriman** - Tabel Pengiriman/Shipping ⭐ NEW
Menyimpan informasi detail pengiriman untuk setiap pesanan.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_pengiriman` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik pengiriman |
| `id_user` | INT | FOREIGN KEY (users.id_user) | ID pengguna |
| `no_pengiriman` | VARCHAR(50) | UNIQUE, NOT NULL | Nomor tracking pengiriman |
| `nama_penerima` | VARCHAR(100) | NOT NULL | Nama orang yang menerima |
| `no_telepon` | VARCHAR(15) | NOT NULL | Nomor telepon penerima |
| `email` | VARCHAR(100) | NOT NULL | Email penerima |
| `provinsi` | VARCHAR(50) | NOT NULL | Provinsi tujuan |
| `kota` | VARCHAR(50) | NOT NULL | Kota tujuan |
| `kecamatan` | VARCHAR(50) | NOT NULL | Kecamatan tujuan |
| `kode_pos` | VARCHAR(10) | NOT NULL | Kode pos |
| `alamat_lengkap` | TEXT | NOT NULL | Alamat lengkap pengiriman |
| `metode_pengiriman` | ENUM('regular', 'express', 'same_day') | DEFAULT 'regular' | Metode/kelas pengiriman |
| `ongkir` | INT | NOT NULL DEFAULT 0 | Biaya ongkos kirim (Rp) |
| `catatan` | TEXT | | Catatan tambahan untuk kurir |
| `status_pengiriman` | VARCHAR(50) | DEFAULT 'Menunggu Verifikasi Pembayaran' | Status pengiriman |
| `tanggal_pengiriman` | DATETIME | NOT NULL | Tanggal estimasi pengiriman |
| `tanggal_konfirmasi` | DATETIME | | Tanggal barang dikonfirmasi diterima |
| `tanggal_diterima` | DATETIME | | Tanggal barang benar-benar diterima |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu record dibuat |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Waktu record terakhir diupdate |

**Index:**
- `idx_id_user` (id_user)
- `idx_no_pengiriman` (no_pengiriman)
- `idx_status` (status_pengiriman)

**Sample Data:** Kosong (0 rows)

**Status Pengiriman Values:**
- `Menunggu Verifikasi Pembayaran` - Menunggu verifikasi pembayaran dari user
- `Menunggu Pickup` - Menunggu pickup dari kurir
- `Dalam Pengiriman` - Sedang dalam perjalanan
- `Tiba di Tujuan` - Sudah tiba di lokasi tujuan
- `Diterima` - Sudah diterima oleh penerima
- `Batal` - Pengiriman dibatalkan

---

### 10. **pesanan** - Tabel Pesanan/Order ⭐ NEW
Menyimpan informasi detail pesanan dari user.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_pesanan` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik pesanan |
| `id_user` | INT | FOREIGN KEY (users.id_user) | ID pengguna yang pesan |
| `id_pengiriman` | INT | FOREIGN KEY (pengiriman.id_pengiriman) | ID data pengiriman |
| `no_pesanan` | VARCHAR(50) | UNIQUE, NOT NULL | Nomor pesanan unik |
| `subtotal` | INT | NOT NULL | Total harga produk sebelum diskon |
| `diskon` | INT | NOT NULL DEFAULT 0 | Total diskon (Rp) |
| `ongkir` | INT | NOT NULL | Biaya ongkos kirim (Rp) |
| `total_bayar` | INT | NOT NULL | Total yang harus dibayar (subtotal - diskon + ongkir) |
| `status_pesanan` | VARCHAR(50) | DEFAULT 'Menunggu Verifikasi' | Status pesanan |
| `metode_pembayaran` | VARCHAR(50) | NOT NULL | Metode pembayaran (transfer, cod, ewallet, dll) |
| `bukti_pembayaran` | VARCHAR(255) | | Path/URL bukti pembayaran |
| `tanggal_pesanan` | DATETIME | NOT NULL | Tanggal pesanan dibuat |
| `tanggal_pembayaran` | DATETIME | | Tanggal pesanan dibayar |
| `tanggal_pengiriman` | DATETIME | | Tanggal pesanan dikirim |
| `tanggal_diterima` | DATETIME | | Tanggal pesanan diterima |
| `catatan` | TEXT | | Catatan tambahan dari user |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu record dibuat |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Waktu record terakhir diupdate |

**Index:**
- `idx_id_user` (id_user)
- `idx_id_pengiriman` (id_pengiriman)
- `idx_no_pesanan` (no_pesanan)
- `idx_status` (status_pesanan)

**Sample Data:** Kosong (0 rows)

**Status Pesanan Values:**
- `Menunggu Verifikasi` - Menunggu verifikasi dari admin
- `Menunggu Pembayaran` - Menunggu pembayaran dari user
- `Menunggu Pengiriman` - Pembayaran sudah diterima, menunggu pickup
- `Dalam Pengiriman` - Sedang dikirim ke user
- `Diterima` - Sudah diterima oleh user
- `Selesai` - Pesanan selesai dan transaksi lengkap
- `Dibatalkan` - Pesanan dibatalkan

---

### 11. **detail_pesanan** - Tabel Detail Pesanan ⭐ NEW
Menyimpan breakdown item/produk dalam setiap pesanan.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_detail_pesanan` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik detail pesanan |
| `id_pesanan` | INT | FOREIGN KEY (pesanan.id_pesanan) | ID pesanan referensi |
| `id_produk` | INT | FOREIGN KEY (produk.id_produk) | ID produk yang dibeli |
| `nama_produk` | VARCHAR(255) | NOT NULL | Nama produk (snapshot saat pembelian) |
| `harga` | INT | NOT NULL | Harga per unit saat pembelian (Rp) |
| `qty` | INT | NOT NULL | Jumlah/quantity yang dibeli |
| `subtotal` | INT | NOT NULL | Total untuk item ini (harga × qty) |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu record dibuat |

**Index:**
- `idx_id_pesanan` (id_pesanan)
- `idx_id_produk` (id_produk)

**Sample Data:** Kosong (0 rows)

---

## 🔄 Relationships (Foreign Keys)

```
users ──┬──→ transaksi (DEPRECATED)
        ├──→ keranjang
        ├──→ ulasan
        ├──→ pengiriman ⭐ NEW
        └──→ pesanan ⭐ NEW

admin ─→ (standalone table)

produk ──┬──→ detail_transaksi (DEPRECATED)
         ├──→ keranjang
         ├──→ ulasan
         └──→ detail_pesanan ⭐ NEW

transaksi ──→ detail_transaksi (DEPRECATED)

pengiriman ⭐ NEW (standalone, referenced by pesanan)

pesanan ⭐ NEW ──┬──→ pengiriman
                └──→ detail_pesanan

detail_pesanan ⭐ NEW ──→ pesanan
```

**Relationship Diagram:**
```
┌─────────┐
│  users  │
└────┬────┘
     │
     ├─→ pesanan (id_user) ⭐ NEW
     │       │
     │       ├─→ pengiriman (id_pengiriman) ⭐ NEW
     │       └─→ detail_pesanan (id_pesanan) ⭐ NEW
     │
     ├─→ pengiriman (id_user) ⭐ NEW
     │
     ├─→ keranjang (id_user)
     │
     └─→ ulasan (id_user)

┌─────────┐
│  produk │
└────┬────┘
     │
     ├─→ keranjang (id_produk)
     │
     ├─→ ulasan (id_produk)
     │
     └─→ detail_pesanan (id_produk) ⭐ NEW
```

---

## 📌 Index & Performance Tips

### Recommended Indexes

```sql
-- User lookups
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);

-- Product search
CREATE INDEX idx_produk_kategori ON produk(kategori);
CREATE INDEX idx_produk_status ON produk(status_produk);

-- Transaction queries (OLD)
CREATE INDEX idx_transaksi_id_user ON transaksi(id_user);
CREATE INDEX idx_transaksi_status ON transaksi(status_pesanan);
CREATE INDEX idx_transaksi_tanggal ON transaksi(tanggal_transaksi);

-- Cart operations
CREATE INDEX idx_keranjang_id_user ON keranjang(id_user);

-- Review queries
CREATE INDEX idx_ulasan_id_produk ON ulasan(id_produk);

-- NEW: Order/Pengiriman queries ⭐
CREATE INDEX idx_pesanan_id_user ON pesanan(id_user);
CREATE INDEX idx_pesanan_status ON pesanan(status_pesanan);
CREATE INDEX idx_pesanan_tanggal ON pesanan(tanggal_pesanan);
CREATE INDEX idx_pengiriman_id_user ON pengiriman(id_user);
CREATE INDEX idx_pengiriman_status ON pengiriman(status_pengiriman);
CREATE INDEX idx_pengiriman_tanggal ON pengiriman(tanggal_pengiriman);
CREATE INDEX idx_detail_pesanan_id_pesanan ON detail_pesanan(id_pesanan);
CREATE INDEX idx_detail_pesanan_id_produk ON detail_pesanan(id_produk);
```

### Query Optimization Tips

1. **Always use prepared statements** untuk prevent SQL injection
2. **Avoid N+1 queries** - use JOINs instead of loops
3. **Select only needed columns** - jangan SELECT *
4. **Use LIMIT** untuk pagination
5. **Index frequently queried columns** - seperti status, user_id, dates
6. **Denormalize jika diperlukan** - contoh: simpan nama_produk di detail_pesanan

---

## 🛡️ Security Considerations

1. **Password:** Gunakan bcrypt hashing (password_hash/password_verify)
2. **Sensitive Data:** Email dan telepon sebaiknya terenkripsi di production
3. **SQL Injection:** Gunakan prepared statements di semua query
4. **Foreign Keys:** Enforce referential integrity dengan ON DELETE CASCADE/RESTRICT
5. **Data Validation:** Validasi di aplikasi sebelum insert/update ke database
6. **Audit Trail:** Gunakan created_at dan updated_at untuk tracking perubahan
7. **Access Control:** Implement role-based access control (admin vs user)
8. **Payment Security:** Jangan simpan credit card details, gunakan payment gateway

---

## 🔧 Setup Instructions

### 1. Create Database
```sql
CREATE DATABASE IF NOT EXISTS mobilenest_db;
USE mobilenest_db;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
```

### 2. Create All Tables
```sql
-- Run all CREATE TABLE statements
-- File: mobilenest_schema.sql
```

### 3. Create Indexes
```sql
-- Run all INDEX creation statements
-- See section: Index & Performance Tips
```

### 4. Insert Sample Data
```sql
-- Insert users, products, promotions
-- Keep new tables (pengiriman, pesanan, detail_pesanan) empty initially
```

### 5. Verify Connection
```php
// Di config.php
$host = "localhost";
$user = "root";
$password = "";
$database = "mobilenest_db";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
```

---

## 📝 Maintenance & Backup

### Backup Database
```bash
# Backup to SQL file with timestamp
mysqldump -u root -p mobilenest_db > mobilenest_db_backup_$(date +%Y%m%d_%H%M%S).sql

# Restore from backup
mysql -u root -p mobilenest_db < mobilenest_db_backup_20260108_033900.sql

# Backup all databases
mysqldump -u root -p --all-databases > full_backup_$(date +%Y%m%d).sql
```

### Regular Maintenance
```sql
-- Check table integrity
CHECK TABLE users, produk, pesanan, pengiriman, detail_pesanan;

-- Optimize tables
OPTIMIZE TABLE users, produk, pesanan, pengiriman, detail_pesanan;

-- Show table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'mobilenest_db'
ORDER BY size_mb DESC;
```

### Monitoring
- Monitor disk space dan growth rate
- Regular backups (daily/weekly)
- Monitor query performance dengan slow query log
- Regular check foreign key integrity

---

## 📊 Data Statistics

| Tabel | Rows | Purpose |
|-------|------|---------|
| users | 6 | User accounts |
| admin | 1 | Admin accounts |
| produk | 13 | Product catalog |
| promo | 2 | Active promotions |
| transaksi | 0 | OLD: Order history |
| detail_transaksi | 0 | OLD: Order items |
| keranjang | 0 | Shopping carts |
| ulasan | 0 | Product reviews |
| pengiriman | 0 | Shipping info ⭐ NEW |
| pesanan | 0 | Order history ⭐ NEW |
| detail_pesanan | 0 | Order items ⭐ NEW |

**Total Tables:** 11 active + 2 deprecated = 13 tables

---

## 🔄 Migration Guide (Old to New)

Jika migrasi dari `transaksi` ke `pesanan`:

```sql
-- Step 1: Backup data lama
CREATE TABLE transaksi_backup AS SELECT * FROM transaksi;

-- Step 2: Migrate data (contoh)
INSERT INTO pesanan (id_user, no_pesanan, subtotal, ongkir, total_bayar, 
                     status_pesanan, metode_pembayaran, bukti_pembayaran,
                     tanggal_pesanan, created_at)
SELECT id_user, kode_transaksi, total_harga, 0, total_harga,
       status_pesanan, metode_pembayaran, bukti_pembayaran,
       tanggal_transaksi, tanggal_transaksi
FROM transaksi;

-- Step 3: Migrate detail items
INSERT INTO detail_pesanan (id_pesanan, id_produk, nama_produk, harga, qty, subtotal, created_at)
SELECT dt.id_transaksi, dt.id_produk, p.nama_produk, 
       dt.harga_satuan, dt.jumlah, dt.subtotal, dt.tanggal_dibuat
FROM detail_transaksi dt
JOIN produk p ON dt.id_produk = p.id_produk;

-- Step 4: Verify data
SELECT COUNT(*) FROM pesanan;
SELECT COUNT(*) FROM detail_pesanan;

-- Step 5: Keep old tables for reference (or drop if sure)
-- DROP TABLE detail_transaksi, transaksi;
```

---

## 🔐 Access Control Example

```php
// Restrict access to sensitive queries
function get_pesanan($pesanan_id, $user_id) {
    global $conn;
    
    // Verify user owns this order
    $stmt = $conn->prepare("
        SELECT p.* FROM pesanan p
        WHERE p.id_pesanan = ? AND p.id_user = ?
    ");
    $stmt->bind_param('ii', $pesanan_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
```

---

## 📝 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Dec 30, 2025 | Initial schema with 8 tables |
| 2.0 | Jan 8, 2026 | Added pengiriman, pesanan, detail_pesanan tables; improved structure |

---

**Created by:** AI Assistant  
**For Project:** MobileNest E-Commerce Platform  
**Last Updated:** January 8, 2026, 3:39 AM +07  
**Status:** ✅ Documentation Updated & Ready for Integration
