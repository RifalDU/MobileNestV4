# 👨‍💻 DEVELOPER GUIDE - MobileNestV4

## Quick Start untuk Developer

Panduan cepat untuk memahami dan menggunakan 20 file core MobileNestV4.

---

## 📋 Table of Contents

1. [Folder Structure](#folder-structure)
2. [Setup Instructions](#setup-instructions)
3. [API Usage Examples](#api-usage-examples)
4. [Common Workflows](#common-workflows)
5. [Error Handling](#error-handling)
6. [Best Practices](#best-practices)
7. [Testing](#testing)

---

## 📁 Folder Structure

```
MobileNest/
├── config/              (2 files)
│   ├── Database.php     - Database connection & pooling
│   └── Constants.php    - Global constants & config
│
├── includes/            (7 files - Business Logic)
│   ├── User.php         - User management
│   ├── Produk.php       - Product catalog
│   ├── Kategori.php     - Product categories
│   ├── Transaksi.php    - Orders/Transactions
│   ├── DetailTransaksi.php - Order items
│   ├── Pengiriman.php   - Shipping management
│   └── Keranjang.php    - Shopping cart
│
├── api/                 (11 files - REST Endpoints)
│   ├── user.php         - User CRUD endpoints
│   ├── produk.php       - Product CRUD endpoints
│   ├── kategori.php     - Category CRUD endpoints
│   ├── transaksi.php    - Order CRUD endpoints
│   ├── detail_transaksi.php - Order items endpoints
│   ├── pengiriman.php   - Shipping endpoints
│   ├── keranjang.php    - Cart endpoints
│   ├── auth.php         - Authentication
│   ├── order.php        - Complex checkout logic
│   ├── search.php       - Product search
│   └── analytics.php    - Admin analytics
│
└── docs/                (Documentation)
    ├── DOKUMENTASI_20_FILES.md - Complete documentation
    └── DEVELOPER_GUIDE.md       - This file
```

---

## 🔧 Setup Instructions

### 1. Database Setup

```php
// config/Database.php
require_once 'config/Database.php';
require_once 'config/Constants.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die('Database connection failed');
}
```

### 2. Create Required Tables

Run SQL scripts untuk membuat tables:
- user
- produk
- kategori
- transaksi
- detail_transaksi
- pengiriman
- keranjang

### 3. Configuration

Edit `config/Constants.php` dengan:
- Database credentials
- JWT secret
- Base URL
- Upload paths

---

## 🔌 API Usage Examples

### 1. USER API

**Register User:**
```bash
curl -X POST http://localhost/MobileNest/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "nama": "John Doe"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "User berhasil dibuat",
  "data": {
    "user_id": 1,
    "email": "user@example.com",
    "nama": "John Doe"
  }
}
```

**Get User:**
```bash
curl -X GET "http://localhost/MobileNest/api/user.php?action=get&id=1"
```

**Update User:**
```bash
curl -X PUT http://localhost/MobileNest/api/user.php?action=update&id=1 \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "John Updated",
    "no_telepon": "081234567890"
  }'
```

---

### 2. PRODUCT API

**Get All Products:**
```bash
curl -X GET "http://localhost/MobileNest/api/produk.php?action=list"
```

**Get Products by Category:**
```bash
curl -X GET "http://localhost/MobileNest/api/produk.php?action=kategori&id=1"
```

**Search Products:**
```bash
curl -X GET "http://localhost/MobileNest/api/search.php?q=smartphone&kategori=1&min_price=1000000&max_price=5000000"
```

**Create Product:**
```bash
curl -X POST http://localhost/MobileNest/api/produk.php?action=create \
  -H "Content-Type: application/json" \
  -d '{
    "nama_produk": "iPhone 15",
    "harga": 15999000,
    "stok": 50,
    "id_kategori": 1,
    "deskripsi": "Smartphone flagship terbaru"
  }'
```

---

### 3. SHOPPING CART API

**Add Item to Cart:**
```bash
curl -X POST http://localhost/MobileNest/api/keranjang.php?action=add \
  -H "Content-Type: application/json" \
  -d '{
    "id_user": 1,
    "id_produk": 5,
    "jumlah": 2
  }'
```

**Get Cart:**
```bash
curl -X GET "http://localhost/MobileNest/api/keranjang.php?action=get&id=1"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id_keranjang": 1,
      "id_produk": 5,
      "nama_produk": "iPhone 15",
      "harga": 15999000,
      "jumlah": 2,
      "gambar": "iphone15.jpg"
    }
  ]
}
```

**Get Cart Total:**
```bash
curl -X GET "http://localhost/MobileNest/api/keranjang.php?action=total&id=1"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total": 31998000
  }
}
```

**Update Quantity:**
```bash
curl -X PUT http://localhost/MobileNest/api/keranjang.php?action=update&id=1 \
  -H "Content-Type: application/json" \
  -d '{
    "jumlah": 3
  }'
```

**Remove Item:**
```bash
curl -X DELETE "http://localhost/MobileNest/api/keranjang.php?action=remove&id=1"
```

---

### 4. ORDER/CHECKOUT API

**Create Order (Checkout):**
```bash
curl -X POST http://localhost/MobileNest/api/order.php?action=checkout \
  -H "Content-Type: application/json" \
  -d '{
    "id_user": 1,
    "nama_penerima": "John Doe",
    "no_telepon": "081234567890",
    "email": "john@example.com",
    "provinsi": "Jawa Barat",
    "kota": "Bandung",
    "kecamatan": "Cibeunying",
    "kode_pos": "40121",
    "alamat_lengkap": "Jl. Test No 1",
    "metode_pengiriman": "regular"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Order berhasil dibuat",
  "data": {
    "transaksi_id": 10,
    "no_pesanan": "ORD-20260107-001",
    "total_amount": 32050000,
    "ongkir": 50000,
    "status": "Pending"
  }
}
```

**Get Order Details:**
```bash
curl -X GET "http://localhost/MobileNest/api/transaksi.php?action=get&id=10"
```

**Get Order Items:**
```bash
curl -X GET "http://localhost/MobileNest/api/detail_transaksi.php?action=order&id=10"
```

---

### 5. SHIPPING API

**Get Shipping Info:**
```bash
curl -X GET "http://localhost/MobileNest/api/pengiriman.php?action=transaksi&id=10"
```

**Update Shipping Status:**
```bash
curl -X PUT http://localhost/MobileNest/api/pengiriman.php?action=status&id=1 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Dalam Pengiriman"
  }'
```

**Get Shipping Timeline:**
```bash
curl -X GET "http://localhost/MobileNest/api/pengiriman.php?action=timeline&id=1"
```

---

## 🔄 Common Workflows

### Workflow 1: User Registration & Login

```
1. POST /api/auth.php?action=register
   └─> User.php::createUser()
   
2. POST /api/auth.php?action=login
   └─> User.php::getUserByEmail()
   └─> User.php::verifyPassword()
   └─> Generate JWT Token
```

### Workflow 2: Browse & Add to Cart

```
1. GET /api/produk.php?action=list
   └─> Produk.php::getAllProduk()
   
2. GET /api/produk.php?action=get&id=X
   └─> Produk.php::getProduk()
   
3. POST /api/keranjang.php?action=add
   └─> Keranjang.php::addItem()
   └─> Check if product exists
   └─> Update quantity if already in cart
   
4. GET /api/keranjang.php?action=get&id=USER_ID
   └─> Keranjang.php::getCart()
```

### Workflow 3: Checkout Complete Order

```
1. POST /api/order.php?action=checkout
   ├─> Get user cart (Keranjang.php::getCart())
   ├─> Create transaction (Transaksi.php::createTransaksi())
   ├─> Add items to order (DetailTransaksi.php::addItem())
   ├─> Create shipping (Pengiriman.php::createShipping())
   │  └─> Calculate ongkir
   │  └─> Update transaksi ongkir
   └─> Clear cart (Keranjang.php::clearCart())
   
2. Return order confirmation with:
   - Order number
   - Total amount
   - Shipping info
   - Tracking number
```

### Workflow 4: Track Order

```
1. GET /api/pengiriman.php?action=transaksi&id=TRANSAKSI_ID
   └─> Pengiriman.php::getShippingByTransaksi()
   
2. GET /api/pengiriman.php?action=timeline&id=PENGIRIMAN_ID
   └─> Pengiriman.php::getTimeline()
   └─> Return timeline with dates
```

---

## ⚠️ Error Handling

### API Error Response Format

```json
{
  "success": false,
  "message": "Deskripsi error yang jelas"
}
```

### Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "Database connection failed" | Database down | Check DB credentials in Constants.php |
| "ID diperlukan" | Missing required parameter | Pass id in query string ?id=X |
| "Field X diperlukan" | Missing required field | Include field in request body |
| "Jumlah harus lebih dari 0" | Invalid quantity | Send positive integer |
| "Item tidak ditemukan" | Record doesn't exist | Check ID is correct |
| "Stok tidak cukup" | Product out of stock | Check Produk::updateStok() |

---

## ✅ Best Practices

### 1. Input Validation

```php
// ALWAYS validate input
if (empty($input['email'])) {
    throw new Exception('Email diperlukan');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Format email tidak valid');
}

if ($harga < 0) {
    throw new Exception('Harga tidak boleh negatif');
}
```

### 2. Error Handling

```php
try {
    $result = $produk->getProduk($id);
    if (!$result) {
        throw new Exception('Produk tidak ditemukan');
    }
    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

### 3. Database Queries

```php
// ALWAYS use prepared statements
$query = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = $this->conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

// NEVER concatenate user input
// BAD: "SELECT * FROM produk WHERE id = " . $_GET['id'];
// GOOD: Use prepared statements with bind_param
```

### 4. API Response Format

```php
// Always return consistent JSON format
echo json_encode([
    'success' => true/false,
    'message' => 'Optional message',
    'data' => [] // Optional data
]);

// Set correct HTTP status
if ($success) {
    http_response_code(200);
} else {
    http_response_code(400);
}
```

### 5. File Inclusions

```php
// Always use absolute paths
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../includes/User.php';

// AVOID relative paths
// BAD: require_once '../config/Database.php';
```

---

## 🧪 Testing

### Unit Testing Classes

```php
// Test User creation
$user = new User($conn);
$result = $user->createUser('test@example.com', 'password', 'Test User');
assert($result['success'] === true);
assert($result['user_id'] > 0);

// Test Product retrieval
$produk = new Produk($conn);
$result = $produk->getProduk(1);
assert($result !== null);
assert($result['id_produk'] === 1);
```

### API Testing with cURL

```bash
#!/bin/bash

# Test user registration
curl -X POST http://localhost/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"test","nama":"Test"}'

# Test product listing
curl -X GET http://localhost/api/produk.php?action=list

# Test add to cart
curl -X POST http://localhost/api/keranjang.php?action=add \
  -H "Content-Type: application/json" \
  -d '{"id_user":1,"id_produk":1,"jumlah":1}'
```

### Postman Collection

Import into Postman untuk testing:
```json
{
  "info": {
    "name": "MobileNestV4 API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "url": "{{baseUrl}}/api/auth.php?action=register"
          }
        }
      ]
    }
  ]
}
```

---

## 📊 Database Schema Quick Reference

```sql
-- Tables at a glance
user (id_user, email, password_hash, nama, created_at)
produk (id_produk, id_kategori, nama_produk, harga, stok, deskripsi)
kategori (id_kategori, nama, deskripsi)
transaksi (id_transaksi, id_user, total, ongkir, status, created_at)
detail_transaksi (id_detail, id_transaksi, id_produk, harga_satuan, jumlah, subtotal)
pengiriman (id_pengiriman, id_transaksi, no_pengiriman, alamat_lengkap, ongkir, status)
keranjang (id_keranjang, id_user, id_produk, jumlah)
```

---

## 🚀 Performance Tips

1. **Use Indexes** pada frequently queried columns
   ```sql
   CREATE INDEX idx_user_email ON user(email);
   CREATE INDEX idx_produk_kategori ON produk(id_kategori);
   CREATE INDEX idx_transaksi_user ON transaksi(id_user);
   ```

2. **Pagination** untuk large result sets
   ```php
   LIMIT 10 OFFSET (page - 1) * 10
   ```

3. **Caching** untuk frequently accessed data
   ```php
   $cache_key = 'produk_' . $id;
   if (redis_exists($cache_key)) {
       return redis_get($cache_key);
   }
   ```

4. **Connection Pooling** dalam Database.php

---

## 📞 Troubleshooting

**Q: Database connection error?**
A: Check Database.php constants, ensure MySQL is running

**Q: API returns 400 error?**
A: Check JSON format, required fields, valid parameters

**Q: Shopping cart not updating?**
A: Verify user ID is correct, product exists, quantity > 0

**Q: Order checkout failing?**
A: Ensure cart has items, shipping address complete, shipping method valid

---

**Version:** 1.0.0
**Last Updated:** January 2026
**Author:** Development Team
