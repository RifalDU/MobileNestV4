# MobileNest Database Schema Documentation

**Database Name:** `mobilenest_db`  
**Last Updated:** January 8, 2026  
**Version:** 2.1

---

## 📊 Database Overview

Database `mobilenest_db` adalah database untuk platform e-commerce MobileNest yang mengelola data produk, pengguna, transaksi/pesanan, promosi, pengiriman, dan review.

### 📈 Tabel Utama (9 Tables)
1. **admin** - Data administrator sistem
2. **users** - Data pengguna/pelanggan
3. **produk** - Katalog produk
4. **promo** - Program promosi dan diskon
5. **transaksi** - Riwayat transaksi/pesanan (order + payment tracking)
6. **detail_transaksi** - Detail item dalam transaksi
7. **keranjang** - Shopping cart pelanggan
8. **ulasan** - Review dan rating produk
9. **pengiriman** - Data pengiriman/shipping ⭐

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

### 5. **transaksi** - Tabel Transaksi/Pesanan
Menyimpan informasi order dan payment tracking dari pengguna.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_transaksi` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik transaksi/pesanan |
| `id_user` | INT | FOREIGN KEY (users.id_user) | ID pengguna |
| `no_transaksi` | VARCHAR(50) | UNIQUE, NOT NULL | Nomor transaksi unik |
| `subtotal` | INT | NOT NULL | Total harga produk sebelum diskon |
| `diskon` | INT | NOT NULL DEFAULT 0 | Total diskon (Rp) |
| `ongkir` | INT | NOT NULL DEFAULT 0 | Biaya ongkos kirim (Rp) |
| `total_harga` | DECIMAL(12,2) | NOT NULL | Total yang harus dibayar (subtotal - diskon + ongkir) |
| `status_pesanan` | VARCHAR(50) | DEFAULT 'Menunggu Verifikasi' | Status pesanan |
| `metode_pembayaran` | VARCHAR(50) | NOT NULL | Metode pembayaran (transfer, cod, ewallet, dll) |
| `bukti_pembayaran` | VARCHAR(255) | | Path/URL bukti pembayaran |
| `tanggal_transaksi` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu transaksi dibuat |
| `tanggal_pembayaran` | DATETIME | | Tanggal transaksi dibayar |
| `tanggal_konfirmasi` | DATETIME | | Tanggal pembayaran dikonfirmasi/diverifikasi |
| `catatan_user` | TEXT | | Catatan/note dari pembeli |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu record dibuat |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Waktu record terakhir diupdate |

**Index:**
- `idx_id_user` (id_user)
- `idx_no_transaksi` (no_transaksi)
- `idx_status` (status_pesanan)
- `idx_tanggal` (tanggal_transaksi)

**Sample Data:** Kosong (0 rows)

**Status Pesanan Values:**
- `Menunggu Verifikasi` - Menunggu verifikasi pembayaran dari admin
- `Verified` - Pembayaran sudah diverifikasi, siap dikirim
- `Dalam Pengiriman` - Sedang dikirim ke user
- `Diterima` - Sudah diterima oleh user
- `Selesai` - Pesanan selesai dan transaksi lengkap
- `Dibatalkan` - Pesanan dibatalkan

---

### 6. **detail_transaksi** - Tabel Detail Transaksi
Menyimpan breakdown item/produk dalam setiap transaksi/pesanan.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_detail` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik detail item |
| `id_transaksi` | INT | FOREIGN KEY (transaksi.id_transaksi) | ID transaksi referensi |
| `id_produk` | INT | FOREIGN KEY (produk.id_produk) | ID produk yang dibeli |
| `nama_produk` | VARCHAR(255) | NOT NULL | Nama produk (snapshot saat pembelian) |
| `harga_satuan` | INT | NOT NULL | Harga per unit saat pembelian (Rp) |
| `jumlah` | INT | NOT NULL | Jumlah/quantity yang dibeli |
| `subtotal` | DECIMAL(12,2) | NOT NULL | Total untuk item ini (harga_satuan × jumlah) |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu record dibuat |

**Index:**
- `idx_id_transaksi` (id_transaksi)
- `idx_id_produk` (id_produk)

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

### 9. **pengiriman** - Tabel Pengiriman/Shipping ⭐
Menyimpan informasi detail pengiriman untuk setiap transaksi/pesanan.

| Kolom | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| `id_pengiriman` | INT | PRIMARY KEY, AUTO_INCREMENT | ID unik pengiriman |
| `id_transaksi` | INT | FOREIGN KEY (transaksi.id_transaksi) | ID transaksi referensi |
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
| `catatan` | TEXT | | Catatan tambahan untuk kurir |
| `status_pengiriman` | VARCHAR(50) | DEFAULT 'Menunggu Pickup' | Status pengiriman |
| `tanggal_pengiriman` | DATETIME | NOT NULL | Tanggal estimasi pengiriman |
| `tanggal_konfirmasi` | DATETIME | | Tanggal barang dikonfirmasi diterima |
| `tanggal_diterima` | DATETIME | | Tanggal barang benar-benar diterima |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Waktu record dibuat |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Waktu record terakhir diupdate |

**Index:**
- `idx_id_transaksi` (id_transaksi)
- `idx_id_user` (id_user)
- `idx_no_pengiriman` (no_pengiriman)
- `idx_status` (status_pengiriman)

**Sample Data:** Kosong (0 rows)

**Status Pengiriman Values:**
- `Menunggu Pickup` - Menunggu pickup dari kurir
- `Dalam Pengiriman` - Sedang dalam perjalanan
- `Tiba di Tujuan` - Sudah tiba di lokasi tujuan
- `Diterima` - Sudah diterima oleh penerima
- `Batal` - Pengiriman dibatalkan

---

## 🔄 Relationships (Foreign Keys)

```
users ──┬──→ transaksi (order + payment tracking)
        ├──→ keranjang
        ├──→ ulasan
        └──→ pengiriman (untuk tracking)

admin ─→ (standalone table)

produk ──┬──→ detail_transaksi (items breakdown)
         ├──→ keranjang
         └──→ ulasan

transaksi ──┬──→ detail_transaksi (items in order)
            └──→ pengiriman (shipping info)

pengiriman → transaksi (linked via id_transaksi)
```

**Relationship Diagram:**
```
┌─────────┐
│  users  │
└────┬────┘
     │
     ├─→ transaksi (id_user)
     │       │
     │       ├─→ detail_transaksi (id_transaksi)
     │       │       └─→ produk (id_produk)
     │       │
     │       └─→ pengiriman (id_transaksi)
     │
     ├─→ pengiriman (id_user)
     │
     ├─→ keranjang (id_user)
     │       └─→ produk (id_produk)
     │
     └─→ ulasan (id_user)
             └─→ produk (id_produk)

┌─────────┐
│  produk │
└────┬────┘
     │
     ├─→ keranjang (id_produk)
     │
     ├─→ ulasan (id_produk)
     │
     └─→ detail_transaksi (id_produk)
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

-- Order/Transaction queries
CREATE INDEX idx_transaksi_id_user ON transaksi(id_user);
CREATE INDEX idx_transaksi_status ON transaksi(status_pesanan);
CREATE INDEX idx_transaksi_tanggal ON transaksi(tanggal_transaksi);
CREATE INDEX idx_detail_transaksi_id_transaksi ON detail_transaksi(id_transaksi);
CREATE INDEX idx_detail_transaksi_id_produk ON detail_transaksi(id_produk);

-- Cart operations
CREATE INDEX idx_keranjang_id_user ON keranjang(id_user);

-- Review queries
CREATE INDEX idx_ulasan_id_produk ON ulasan(id_produk);

-- Shipping queries
CREATE INDEX idx_pengiriman_id_transaksi ON pengiriman(id_transaksi);
CREATE INDEX idx_pengiriman_id_user ON pengiriman(id_user);
CREATE INDEX idx_pengiriman_status ON pengiriman(status_pengiriman);
CREATE INDEX idx_pengiriman_tanggal ON pengiriman(tanggal_pengiriman);
```

### Query Optimization Tips

1. **Always use prepared statements** untuk prevent SQL injection
2. **Avoid N+1 queries** - use JOINs instead of loops
3. **Select only needed columns** - jangan SELECT *
4. **Use LIMIT** untuk pagination
5. **Index frequently queried columns** - seperti status, user_id, dates
6. **Denormalize jika diperlukan** - contoh: simpan nama_produk di detail_transaksi

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
-- Keep transaction tables empty initially for testing
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
CHECK TABLE users, produk, transaksi, detail_transaksi, pengiriman;

-- Optimize tables
OPTIMIZE TABLE users, produk, transaksi, detail_transaksi, pengiriman;

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
|-------|------|----------|
| users | 6 | User accounts |
| admin | 1 | Admin accounts |
| produk | 13 | Product catalog |
| promo | 2 | Active promotions |
| transaksi | 0 | Order + Payment tracking |
| detail_transaksi | 0 | Order items |
| keranjang | 0 | Shopping carts |
| ulasan | 0 | Product reviews |
| pengiriman | 0 | Shipping info |

**Total Tables:** 9 active tables

---

## 🔄 Common Workflow: Keranjang → Pengiriman → Pembayaran

### Step 1: User Add Items to Cart
```sql
INSERT INTO keranjang (id_user, id_produk, jumlah)
VALUES (?, ?, ?);
```

### Step 2: User Checkout (Create Transaction)
```sql
-- Calculate totals from cart
SET @subtotal = (SELECT SUM(k.jumlah * p.harga) 
                 FROM keranjang k 
                 JOIN produk p ON k.id_produk = p.id_produk 
                 WHERE k.id_user = ?);
SET @diskon = 0; -- Apply promo if any
SET @ongkir = 0; -- Will be set after shipping method chosen
SET @total = @subtotal - @diskon + @ongkir;

-- Create transaction
INSERT INTO transaksi (id_user, no_transaksi, subtotal, diskon, ongkir, total_harga, status_pesanan, metode_pembayaran)
VALUES (?, CONCAT('TRX-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s')), @subtotal, @diskon, @ongkir, @total, 'Menunggu Verifikasi', '');

SET @id_transaksi = LAST_INSERT_ID();

-- Copy items from cart to detail_transaksi
INSERT INTO detail_transaksi (id_transaksi, id_produk, nama_produk, harga_satuan, jumlah, subtotal)
SELECT @id_transaksi, p.id_produk, p.nama_produk, p.harga, k.jumlah, (p.harga * k.jumlah)
FROM keranjang k
JOIN produk p ON k.id_produk = p.id_produk
WHERE k.id_user = ?;

-- Clear cart
DELETE FROM keranjang WHERE id_user = ?;
```

### Step 3: User Input Shipping Address & Method
```sql
-- Create shipping record
INSERT INTO pengiriman (id_transaksi, id_user, no_pengiriman, nama_penerima, no_telepon, email, 
                       provinsi, kota, kecamatan, kode_pos, alamat_lengkap, metode_pengiriman)
VALUES (?, ?, CONCAT('SHIP-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s')), ?, ?, ?, ?, ?, ?, ?, ?, ?);

-- Update ongkir based on shipping method
UPDATE transaksi 
SET ongkir = ?, total_harga = (subtotal - diskon + ?)
WHERE id_transaksi = ?;
```

### Step 4: User Select Payment Method & Upload Proof
```sql
UPDATE transaksi
SET metode_pembayaran = ?, bukti_pembayaran = ?, status_pesanan = 'Menunggu Verifikasi'
WHERE id_transaksi = ?;
```

### Step 5: Admin Verify Payment
```sql
UPDATE transaksi
SET status_pesanan = 'Verified', tanggal_konfirmasi = NOW()
WHERE id_transaksi = ?;

UPDATE pengiriman
SET status_pengiriman = 'Menunggu Pickup'
WHERE id_transaksi = ?;
```

### Step 6: Kurir Pickup & Kirim
```sql
UPDATE pengiriman
SET status_pengiriman = 'Dalam Pengiriman', tanggal_pengiriman = NOW()
WHERE id_transaksi = ?;

UPDATE transaksi
SET status_pesanan = 'Dalam Pengiriman'
WHERE id_transaksi = ?;
```

### Step 7: Customer Receive
```sql
UPDATE pengiriman
SET status_pengiriman = 'Diterima', tanggal_diterima = NOW()
WHERE id_transaksi = ?;

UPDATE transaksi
SET status_pesanan = 'Diterima'
WHERE id_transaksi = ?;
```

### Step 8: Mark Transaction Complete
```sql
UPDATE transaksi
SET status_pesanan = 'Selesai'
WHERE id_transaksi = ?;
```

---

## 🔐 Query Examples

### Get All Orders from User
```sql
SELECT t.*, p.status_pengiriman
FROM transaksi t
LEFT JOIN pengiriman p ON t.id_transaksi = p.id_transaksi
WHERE t.id_user = ?
ORDER BY t.tanggal_transaksi DESC;
```

### Get Order Details with Items
```sql
SELECT 
    t.id_transaksi, t.no_transaksi, t.total_harga, t.status_pesanan,
    dt.id_produk, dt.nama_produk, dt.harga_satuan, dt.jumlah, dt.subtotal
FROM transaksi t
JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
WHERE t.id_transaksi = ?
ORDER BY dt.id_detail;
```

### Get Pending Shipments
```sql
SELECT p.*, t.no_transaksi, u.nama_lengkap
FROM pengiriman p
JOIN transaksi t ON p.id_transaksi = t.id_transaksi
JOIN users u ON p.id_user = u.id_user
WHERE p.status_pengiriman IN ('Menunggu Pickup', 'Dalam Pengiriman')
ORDER BY p.tanggal_pengiriman ASC;
```

### Get Revenue Report
```sql
SELECT 
    DATE(tanggal_transaksi) as tanggal,
    COUNT(id_transaksi) as jumlah_order,
    SUM(total_harga) as total_revenue,
    SUM(subtotal) as total_produk,
    SUM(diskon) as total_diskon,
    SUM(ongkir) as total_ongkir
FROM transaksi
WHERE status_pesanan IN ('Verified', 'Dalam Pengiriman', 'Diterima', 'Selesai')
GROUP BY DATE(tanggal_transaksi)
ORDER BY tanggal DESC;
```

---

## 📝 Version History

| Version | Date | Changes |
|---------|------|----------|
| 1.0 | Dec 30, 2025 | Initial schema with 8 tables |
| 2.0 | Jan 8, 2026 | Added pengiriman table for shipping info |
| 2.1 | Jan 8, 2026 | Removed duplicate pesanan & detail_pesanan; use transaksi family; added workflow examples & query examples |

---

**Created by:** AI Assistant  
**For Project:** MobileNest E-Commerce Platform  
**Last Updated:** January 8, 2026, 3:58 AM +07  
**Status:** ✅ Updated - Optimized for Production Use
