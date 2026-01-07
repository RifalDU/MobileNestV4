# 📋 Dokumentasi 20 File Core MobileNestV4

## Overview
Dokumentasi lengkap untuk 20 file inti yang saling terhubung membentuk sistem e-commerce MobileNest.

---

## 📁 Struktur File

```
MobileNest/
├── config/
│   ├── Database.php                 [FILE 1]
│   └── Constants.php                [FILE 2]
│
├── includes/
│   ├── User.php                     [FILE 3]
│   ├── Produk.php                   [FILE 4]
│   ├── Kategori.php                 [FILE 5]
│   ├── Transaksi.php                [FILE 6]
│   ├── DetailTransaksi.php          [FILE 7]
│   ├── Pengiriman.php               [FILE 8]
│   └── Keranjang.php                [FILE 9]
│
└── api/
    ├── user.php                     [FILE 10]
    ├── produk.php                   [FILE 11]
    ├── kategori.php                 [FILE 12]
    ├── transaksi.php                [FILE 13]
    ├── detail_transaksi.php         [FILE 14]
    ├── pengiriman.php               [FILE 15]
    ├── keranjang.php                [FILE 16]
    ├── auth.php                     [FILE 17]
    ├── order.php                    [FILE 18]
    ├── search.php                   [FILE 19]
    └── analytics.php                [FILE 20]
```

---

## 🔧 FILE-BY-FILE BREAKDOWN

### TIER 1: CONFIGURATION FILES

#### FILE 1: `config/Database.php`
**Fungsi:** Koneksi dan manajemen database
**Tanggung Jawab:**
- Connection pooling ke MySQL
- Error handling untuk koneksi
- Validasi database credentials

**Methods Utama:**
- `__construct($host, $user, $pass, $db)`
- `connect()` → mysqli connection
- `close()` → close connection
- `getConnection()` → return current connection

**Digunakan Oleh:** Semua file (includes dan api)

**Contoh Penggunaan:**
```php
$db = new Database();
$conn = $db->connect();
if (!$conn) throw new Exception('Connection failed');
```

---

#### FILE 2: `config/Constants.php`
**Fungsi:** Konstanta global aplikasi
**Isi:**
- `BASE_URL` → URL aplikasi
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
- `JWT_SECRET` → untuk token authentication
- `API_TIMEOUT` → timeout API requests
- `MAX_UPLOAD_SIZE` → ukuran file maksimal
- Status mapping (Pending, Completed, Cancelled, etc)
- Error messages

**Digunakan Oleh:** Config/Database.php, semua API files

---

### TIER 2: CORE BUSINESS LOGIC (Includes)

#### FILE 3: `includes/User.php`
**Fungsi:** Manajemen user dan authentication
**Database Table:** `user`
**Relasi:** 1 user → banyak transaksi, keranjang, dll

**Methods:**
- `createUser($email, $password, $nama)` → register user
- `getUserById($id)` → ambil user by ID
- `getUserByEmail($email)` → ambil user by email
- `updateProfile($id, $data)` → update profile
- `verifyPassword($password, $hash)` → validasi password
- `updatePassword($id, $old_pass, $new_pass)` → ganti password
- `deactivateUser($id)` → nonaktifkan user
- `getAllUsers()` → ambil semua user (admin)

**Return Format:** Array dengan success boolean dan data

**Digunakan Oleh:** 
- `api/user.php` (CRUD)
- `api/auth.php` (login/register)
- `includes/Transaksi.php` (relasi)
- `includes/Keranjang.php` (relasi)

---

#### FILE 4: `includes/Produk.php`
**Fungsi:** Manajemen produk katalog
**Database Table:** `produk`
**Relasi:** 1 produk → banyak detail_transaksi, keranjang items

**Methods:**
- `createProduk($data)` → buat produk baru
- `getProduk($id)` → ambil produk by ID
- `getAllProduk()` → ambil semua produk
- `getProdukByKategori($kategori_id)` → filter by kategori
- `searchProduk($query)` → search produk
- `updateProduk($id, $data)` → update info produk
- `updateStok($id, $jumlah)` → kurangi stok
- `deleteProduk($id)` → hapus produk
- `getFeaturedProduk()` → ambil produk featured

**Validasi:**
- Harga > 0
- Stok >= 0
- Nama produk tidak kosong

**Digunakan Oleh:**
- `api/produk.php` (CRUD)
- `includes/Keranjang.php` (ambil harga)
- `includes/DetailTransaksi.php` (ambil info)
- `api/search.php` (search)
- `api/order.php` (order processing)

---

#### FILE 5: `includes/Kategori.php`
**Fungsi:** Manajemen kategori produk
**Database Table:** `kategori`
**Relasi:** 1 kategori → banyak produk

**Methods:**
- `createKategori($nama, $deskripsi)` → buat kategori
- `getKategori($id)` → ambil kategori by ID
- `getAllKategori()` → ambil semua kategori
- `getProdukInKategori($id)` → ambil produk di kategori
- `updateKategori($id, $data)` → update kategori
- `deleteKategori($id)` → hapus kategori
- `getKategoriCount()` → jumlah kategori

**Digunakan Oleh:**
- `api/kategori.php` (CRUD)
- `includes/Produk.php` (relasi)
- `api/produk.php` (filter by kategori)

---

#### FILE 6: `includes/Transaksi.php`
**Fungsi:** Manajemen order/pesanan utama
**Database Table:** `transaksi`
**Relasi:** 1 transaksi → 1 user, 1 pengiriman, banyak detail_transaksi

**Methods:**
- `createTransaksi($id_user)` → buat order baru
- `getTransaksi($id)` → ambil order detail
- `getUserTransaksi($id_user)` → ambil orders user
- `getAllTransaksi()` → ambil semua orders (admin)
- `updateStatus($id, $status)` → ubah status order
- `updateOngkir($id, $ongkir)` → update shipping cost
- `getTotalAmount($id)` → hitung total dengan ongkir
- `deleteTransaksi($id)` → hapus transaksi

**Status Flow:**
- Pending → Confirmed → Processing → Shipped → Delivered

**Digunakan Oleh:**
- `api/transaksi.php` (CRUD)
- `includes/DetailTransaksi.php` (relasi)
- `includes/Pengiriman.php` (relasi)
- `api/order.php` (checkout logic)
- `api/auth.php` (order history)

---

#### FILE 7: `includes/DetailTransaksi.php`
**Fungsi:** Item-item dalam satu transaksi
**Database Table:** `detail_transaksi`
**Relasi:** Many-to-One dengan Transaksi, banyak items per order

**Methods:**
- `addItem($id_transaksi, $id_produk, ...)` → tambah item ke order
- `getOrderItems($id_transaksi)` → ambil semua items dalam order
- `getItem($id_detail)` → ambil single item detail
- `removeItem($id_detail)` → hapus item dari order
- `updateQuantity($id_detail, $jumlah)` → ubah qty item
- `getOrderSubtotal($id_transaksi)` → hitung subtotal order
- `getItemCount($id_transaksi)` → hitung jumlah item
- `getTotalQuantity($id_transaksi)` → total qty semua item

**Kalkulasi:**
- Subtotal per item = harga_satuan × jumlah
- Total order = sum(subtotal) + ongkir

**Digunakan Oleh:**
- `api/detail_transaksi.php` (CRUD items)
- `api/order.php` (checkout, add items)
- `includes/Transaksi.php` (ambil total)

---

#### FILE 8: `includes/Pengiriman.php`
**Fungsi:** Manajemen pengiriman dan tracking
**Database Table:** `pengiriman`
**Relasi:** 1-to-1 dengan Transaksi

**Methods:**
- `createShipping($id_transaksi, $id_user, $data)` → buat pengiriman
- `getShippingInfo($id_pengiriman)` → ambil detail pengiriman
- `getShippingByTransaksi($id_transaksi)` → ambil pengiriman by order
- `updateAddress($id_pengiriman, $data)` → update alamat
- `updateMethod($id_pengiriman, $metode, $kota)` → ganti courier/method
- `updateStatus($id_pengiriman, $status)` → update tracking status
- `calculateOngkir($metode, $kota)` → hitung shipping cost
- `getTimeline($id_pengiriman)` → ambil timeline pengiriman

**Shipping Methods:**
- Regular (50,000) - 3-5 hari
- Express (100,000) - 1-2 hari
- Same Day (200,000) - same day

**Status Timeline:**
- Menunggu Pickup → Dalam Pengiriman → Tiba di Tujuan → Diterima

**Digunakan Oleh:**
- `api/pengiriman.php` (CRUD shipping)
- `api/order.php` (checkout shipping)
- `includes/Transaksi.php` (relasi)

---

#### FILE 9: `includes/Keranjang.php`
**Fungsi:** Manajemen shopping cart
**Database Table:** `keranjang`
**Relasi:** Many-to-Many: user-produk melalui keranjang

**Methods:**
- `addItem($id_user, $id_produk, $jumlah)` → tambah item
- `getCart($id_user)` → ambil semua items keranjang user
- `getCartItem($id_keranjang)` → ambil single item detail
- `removeItem($id_keranjang)` → hapus item dari keranjang
- `clearCart($id_user)` → kosongkan seluruh keranjang
- `updateQuantity($id_keranjang, $jumlah)` → ubah qty
- `getCartTotal($id_user)` → hitung total harga
- `getCartItemCount($id_user)` → jumlah item type
- `getCartTotalQuantity($id_user)` → total qty semua item
- `itemExists($id_user, $id_produk)` → check item sudah ada

**Logic:**
- Jika item sudah ada → update qty (tidak insert duplikat)
- Jika qty ≤ 0 → hapus item otomatis

**Digunakan Oleh:**
- `api/keranjang.php` (CRUD cart)
- `api/order.php` (convert cart to order)
- Frontend (cart display)

---

### TIER 3: API ENDPOINTS

#### FILE 10: `api/user.php`
**Fungsi:** REST API untuk user management
**Menggunakan:** `includes/User.php`

**Endpoints:**
```
GET  /api/user.php?action=get&id=X     → ambil user
GET  /api/user.php?action=list         → ambil semua users (admin)
POST /api/user.php?action=create       → buat user
PUT  /api/user.php?action=update&id=X → update user
DEL  /api/user.php?action=delete&id=X → hapus user
```

**Request Body (POST/PUT):**
```json
{
  "email": "user@email.com",
  "nama": "Nama User",
  "no_telepon": "081234567890",
  "alamat": "Jl. Test No 1"
}
```

**Response:**
```json
{
  "success": true,
  "message": "...",
  "data": { /* user data */ }
}
```

---

#### FILE 11: `api/produk.php`
**Fungsi:** REST API untuk produk management
**Menggunakan:** `includes/Produk.php`, `includes/Kategori.php`

**Endpoints:**
```
GET  /api/produk.php?action=get&id=X            → ambil produk
GET  /api/produk.php?action=list                → ambil semua
GET  /api/produk.php?action=kategori&id=X      → filter by kategori
GET  /api/produk.php?action=search&q=keyword   → search produk
POST /api/produk.php?action=create              → buat produk
PUT  /api/produk.php?action=update&id=X        → update produk
DEL  /api/produk.php?action=delete&id=X        → hapus produk
```

---

#### FILE 12: `api/kategori.php`
**Fungsi:** REST API untuk kategori management
**Menggunakan:** `includes/Kategori.php`

**Endpoints:**
```
GET  /api/kategori.php?action=list     → ambil semua
GET  /api/kategori.php?action=get&id=X → ambil kategori
POST /api/kategori.php?action=create   → buat kategori
PUT  /api/kategori.php?action=update&id=X → update
DEL  /api/kategori.php?action=delete&id=X → hapus
```

---

#### FILE 13: `api/transaksi.php`
**Fungsi:** REST API untuk transaksi/order management
**Menggunakan:** `includes/Transaksi.php`

**Endpoints:**
```
GET  /api/transaksi.php?action=get&id=X     → detail order
GET  /api/transaksi.php?action=user&id=X    → orders by user
GET  /api/transaksi.php?action=list         → semua orders (admin)
POST /api/transaksi.php?action=create       → buat order baru
PUT  /api/transaksi.php?action=update&id=X  → update status
DEL  /api/transaksi.php?action=delete&id=X  → hapus order
```

---

#### FILE 14: `api/detail_transaksi.php`
**Fungsi:** REST API untuk items dalam order
**Menggunakan:** `includes/DetailTransaksi.php`

**Endpoints:**
```
GET  /api/detail_transaksi.php?action=order&id=X   → items in order
GET  /api/detail_transaksi.php?action=get&id=X     → single item
POST /api/detail_transaksi.php?action=add          → add item
PUT  /api/detail_transaksi.php?action=update&id=X  → update qty
DEL  /api/detail_transaksi.php?action=remove&id=X  → remove item
```

---

#### FILE 15: `api/pengiriman.php`
**Fungsi:** REST API untuk shipping management
**Menggunakan:** `includes/Pengiriman.php`, `includes/Transaksi.php`

**Endpoints:**
```
GET  /api/pengiriman.php?action=get&id=X           → detail pengiriman
GET  /api/pengiriman.php?action=transaksi&id=X     → by order ID
GET  /api/pengiriman.php?action=timeline&id=X      → timeline tracking
POST /api/pengiriman.php?action=create             → buat pengiriman
PUT  /api/pengiriman.php?action=address&id=X       → update alamat
PUT  /api/pengiriman.php?action=method&id=X        → ganti method
PUT  /api/pengiriman.php?action=status&id=X        → update status
```

---

#### FILE 16: `api/keranjang.php`
**Fungsi:** REST API untuk shopping cart
**Menggunakan:** `includes/Keranjang.php`

**Endpoints:**
```
GET  /api/keranjang.php?action=get&id=X      → ambil cart user
GET  /api/keranjang.php?action=total&id=X    → total harga
GET  /api/keranjang.php?action=count&id=X    → jumlah items
POST /api/keranjang.php?action=add           → tambah item
PUT  /api/keranjang.php?action=update&id=X   → update qty
DEL  /api/keranjang.php?action=remove&id=X   → hapus item
DEL  /api/keranjang.php?action=clear&id=X    → kosongkan
```

---

#### FILE 17: `api/auth.php`
**Fungsi:** Authentication & authorization
**Menggunakan:** `includes/User.php`, `config/Constants.php`

**Endpoints:**
```
POST /api/auth.php?action=login      → login user
POST /api/auth.php?action=register   → register user
POST /api/auth.php?action=logout     → logout
POST /api/auth.php?action=refresh    → refresh token
POST /api/auth.php?action=verify     → verify token
```

**Features:**
- JWT token generation
- Password hashing (bcrypt)
- Session management
- Role-based access (user, admin)

---

#### FILE 18: `api/order.php`
**Fungsi:** Complex order processing (checkout flow)
**Menggunakan:** Semua includes files

**Flow:**
1. Get cart items (`Keranjang`)
2. Create transaksi (`Transaksi`)
3. Add items to order (`DetailTransaksi`)
4. Create shipping (`Pengiriman`)
5. Clear cart (`Keranjang`)

**Endpoints:**
```
POST /api/order.php?action=checkout     → complete checkout
GET  /api/order.php?action=summary&id=X → order summary
GET  /api/order.php?action=history&id=X → order history user
```

---

#### FILE 19: `api/search.php`
**Fungsi:** Advanced search across products
**Menggunakan:** `includes/Produk.php`

**Features:**
- Full-text search
- Filter by kategori
- Filter by price range
- Filter by rating
- Sorting (newest, popular, price)

**Endpoints:**
```
GET /api/search.php?q=keyword&kategori=X&min_price=X&max_price=X
```

---

#### FILE 20: `api/analytics.php`
**Fungsi:** Admin analytics & reporting
**Menggunakan:** Semua includes files

**Metrics:**
- Total sales (today, month, year)
- Top products
- Top categories
- User activity
- Shipping status distribution

**Endpoints:**
```
GET /api/analytics.php?action=summary       → dashboard summary
GET /api/analytics.php?action=sales&period=X → sales by period
GET /api/analytics.php?action=products      → product analytics
GET /api/analytics.php?action=users         → user analytics
```

---

## 🔗 DEPENDENCY GRAPH

```
config/Database.php
        ↓
     (used by)
        ↓
┌─────────────────────────────────────────┐
│  Semua includes & api files             │
│  (bergantung pada Database connection)  │
└─────────────────────────────────────────┘
        ↓
    includes/
   ┌──────────────────────────────────────┐
   │ User.php       ──→ api/user.php      │
   │ Produk.php     ──→ api/produk.php    │
   │ Kategori.php   ──→ api/kategori.php  │
   │ Transaksi.php  ──→ api/transaksi.php │
   │ DetailTransaksi.php ──→ api/detail_transaksi.php
   │ Pengiriman.php ──→ api/pengiriman.php│
   │ Keranjang.php  ──→ api/keranjang.php │
   └──────────────────────────────────────┘
        ↓
    Complex APIs:
   ┌──────────────────────────────────────┐
   │ api/auth.php    (User + JWT)         │
   │ api/order.php   (semua includes)     │
   │ api/search.php  (Produk + Kategori)  │
   │ api/analytics.php (semua includes)   │
   └──────────────────────────────────────┘
```

---

## 🗄️ DATABASE RELATIONSHIPS

```
user (1) ──→ (∞) transaksi
user (1) ──→ (∞) keranjang

transaksi (1) ──→ (∞) detail_transaksi
transaksi (1) ──→ (1) pengiriman

produk (1) ──→ (∞) detail_transaksi
produk (1) ──→ (∞) keranjang

kategori (1) ──→ (∞) produk
```

---

## 🚀 IMPLEMENTATION CHECKLIST

✅ FILE 1-2:   Config files
✅ FILE 3-9:   Core business logic
✅ FILE 10-16: Basic CRUD APIs
✅ FILE 17-20: Complex APIs

Semua file sudah saling terhubung dengan proper error handling dan validation.

---

## 📞 CONTACT & SUPPORT

**Developer:** Your Name
**Last Updated:** January 2026
**Version:** 1.0.0

---

**Happy Coding! 🎉**
