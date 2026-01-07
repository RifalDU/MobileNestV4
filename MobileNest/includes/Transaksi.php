<?php
/**
 * Transaksi Class
 * Handles all transaction/order and payment related operations
 * 
 * Database Table: transaksi
 * - id_transaksi (PK, AUTO_INCREMENT)
 * - id_user (FK)
 * - no_transaksi (UNIQUE)
 * - subtotal, diskon, ongkir, total_harga
 * - status_pesanan, metode_pembayaran, bukti_pembayaran
 * - tanggal_transaksi, tanggal_pembayaran, tanggal_konfirmasi
 */

class Transaksi {
    private $conn;
    private $table = 'transaksi';
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create new order from cart
     * @param int $id_user
     * @param string $metode_pembayaran (optional)
     * @param string $catatan (optional)
     * @return array {success: bool, order_id: int, no_transaksi: string, message: string}
     */
    public function createOrder($id_user, $metode_pembayaran = '', $catatan = '') {
        try {
            // Generate unique transaction number
            $no_transaksi = $this->generateTransactionNumber();
            
            // Get cart total from Keranjang class
            $keranjang = new Keranjang($this->conn);
            $cartTotal = $keranjang->getCartTotal($id_user);
            
            if ($cartTotal <= 0) {
                return [
                    'success' => false,
                    'message' => 'Keranjang kosong. Tambahkan produk terlebih dahulu.'
                ];
            }
            
            // Insert into transaksi table
            $query = "INSERT INTO " . $this->table . " 
                      (id_user, no_transaksi, subtotal, diskon, ongkir, total_harga, 
                       status_pesanan, metode_pembayaran, catatan_user, tanggal_transaksi)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $subtotal = (int)$cartTotal;
            $diskon = 0;
            $ongkir = 0;
            $total_harga = $subtotal - $diskon + $ongkir;
            $status_pesanan = 'Menunggu Verifikasi';
            
            $stmt->bind_param(
                'isiiiiiss',
                $id_user,
                $no_transaksi,
                $subtotal,
                $diskon,
                $ongkir,
                $total_harga,
                $status_pesanan,
                $metode_pembayaran,
                $catatan
            );
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuat transaksi: ' . $stmt->error
                ];
            }
            
            $id_transaksi = $stmt->insert_id;
            $stmt->close();
            
            // Copy cart items to detail_transaksi
            $cart_items = $keranjang->getCart($id_user);
            $detail = new DetailTransaksi($this->conn);
            
            foreach ($cart_items as $item) {
                $detail->addItem(
                    $id_transaksi,
                    $item['id_produk'],
                    $item['nama_produk'],
                    (int)$item['harga'],
                    $item['jumlah']
                );
            }
            
            // Clear cart after order created
            $keranjang->clearCart($id_user);
            
            return [
                'success' => true,
                'order_id' => $id_transaksi,
                'no_transaksi' => $no_transaksi,
                'total_harga' => $total_harga,
                'message' => 'Pesanan berhasil dibuat. Lanjut ke pengiriman.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get order by ID with items and shipping info
     * @param int $id_transaksi
     * @return array {success: bool, data: array, message: string}
     */
    public function getOrderById($id_transaksi) {
        try {
            $query = "SELECT t.*, u.nama_lengkap, u.email as user_email, u.no_telepon as user_telepon,
                      p.status_pengiriman, p.no_pengiriman, p.nama_penerima, p.alamat_lengkap, 
                      p.metode_pengiriman, p.tanggal_diterima
                      FROM " . $this->table . " t
                      LEFT JOIN users u ON t.id_user = u.id_user
                      LEFT JOIN pengiriman p ON t.id_transaksi = p.id_transaksi
                      WHERE t.id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Prepare statement gagal: ' . $this->conn->error];
            }
            
            $stmt->bind_param('i', $id_transaksi);
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Query gagal: ' . $stmt->error];
            }
            
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan'
                ];
            }
            
            $order = $result->fetch_assoc();
            $stmt->close();
            
            // Get items
            $detail = new DetailTransaksi($this->conn);
            $items = $detail->getOrderItems($id_transaksi);
            
            $order['items'] = $items;
            
            return [
                'success' => true,
                'data' => $order,
                'message' => 'Data pesanan berhasil diambil'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all orders for a user
     * @param int $id_user
     * @param string $status (optional - filter by status)
     * @param int $limit (default 20)
     * @param int $offset (default 0)
     * @return array {success: bool, data: array, total: int, message: string}
     */
    public function getUserOrders($id_user, $status = null, $limit = 20, $offset = 0) {
        try {
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE id_user = ?";
            if ($status) {
                $count_query .= " AND status_pesanan = ?";
            }
            
            $count_stmt = $this->conn->prepare($count_query);
            if ($status) {
                $count_stmt->bind_param('is', $id_user, $status);
            } else {
                $count_stmt->bind_param('i', $id_user);
            }
            $count_stmt->execute();
            $total = $count_stmt->get_result()->fetch_assoc()['total'];
            $count_stmt->close();
            
            // Get data
            $query = "SELECT t.*, COUNT(dt.id_detail) as jumlah_item,
                      p.status_pengiriman, p.no_pengiriman
                      FROM " . $this->table . " t
                      LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                      LEFT JOIN pengiriman p ON t.id_transaksi = p.id_transaksi
                      WHERE t.id_user = ?";
            
            if ($status) {
                $query .= " AND t.status_pesanan = ?";
            }
            
            $query .= " GROUP BY t.id_transaksi
                       ORDER BY t.tanggal_transaksi DESC
                       LIMIT ? OFFSET ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Prepare statement gagal: ' . $this->conn->error];
            }
            
            if ($status) {
                $stmt->bind_param('isii', $id_user, $status, $limit, $offset);
            } else {
                $stmt->bind_param('iii', $id_user, $limit, $offset);
            }
            
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Query gagal: ' . $stmt->error];
            }
            
            $result = $stmt->get_result();
            $orders = [];
            
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            $stmt->close();
            
            return [
                'success' => true,
                'data' => $orders,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'message' => 'Data pesanan berhasil diambil'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update order status
     * @param int $id_transaksi
     * @param string $status (Menunggu Verifikasi, Verified, Dalam Pengiriman, Diterima, Selesai, Dibatalkan)
     * @return array {success: bool, message: string}
     */
    public function updateStatus($id_transaksi, $status) {
        try {
            // Validate status
            $valid_status = ['Menunggu Verifikasi', 'Verified', 'Dalam Pengiriman', 'Diterima', 'Selesai', 'Dibatalkan'];
            if (!in_array($status, $valid_status)) {
                return [
                    'success' => false,
                    'message' => 'Status tidak valid. Status yang tersedia: ' . implode(', ', $valid_status)
                ];
            }
            
            $query = "UPDATE " . $this->table . " 
                      SET status_pesanan = ?, updated_at = NOW()
                      WHERE id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Prepare statement gagal: ' . $this->conn->error];
            }
            
            $stmt->bind_param('si', $status, $id_transaksi);
            
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Gagal update status: ' . $stmt->error];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Status pesanan berhasil diupdate ke: ' . $status
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update payment info
     * @param int $id_transaksi
     * @param string $metode_pembayaran
     * @param string $bukti_pembayaran (path/filename)
     * @return array {success: bool, message: string}
     */
    public function updatePaymentInfo($id_transaksi, $metode_pembayaran, $bukti_pembayaran = '') {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET metode_pembayaran = ?, bukti_pembayaran = ?, updated_at = NOW()
                      WHERE id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Prepare statement gagal: ' . $this->conn->error];
            }
            
            $stmt->bind_param('ssi', $metode_pembayaran, $bukti_pembayaran, $id_transaksi);
            
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Gagal update info pembayaran: ' . $stmt->error];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Info pembayaran berhasil diupdate'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verify payment (Admin function)
     * @param int $id_transaksi
     * @return array {success: bool, message: string}
     */
    public function verifyPayment($id_transaksi) {
        try {
            // Update transaksi status
            $query = "UPDATE " . $this->table . " 
                      SET status_pesanan = 'Verified', tanggal_pembayaran = NOW(), 
                          tanggal_konfirmasi = NOW(), updated_at = NOW()
                      WHERE id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Prepare statement gagal: ' . $this->conn->error];
            }
            
            $stmt->bind_param('i', $id_transaksi);
            
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Gagal verifikasi pembayaran: ' . $stmt->error];
            }
            
            $stmt->close();
            
            // Update related pengiriman status
            $pengiriman = new Pengiriman($this->conn);
            $pengiriman->updateStatusByTransaksi($id_transaksi, 'Menunggu Pickup');
            
            return [
                'success' => true,
                'message' => 'Pembayaran berhasil diverifikasi'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update total with ongkir
     * @param int $id_transaksi
     * @param int $ongkir
     * @return array {success: bool, new_total: int, message: string}
     */
    public function updateOngkir($id_transaksi, $ongkir) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET ongkir = ?, total_harga = (subtotal - diskon + ?), updated_at = NOW()
                      WHERE id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Prepare statement gagal: ' . $this->conn->error];
            }
            
            $stmt->bind_param('iii', $ongkir, $ongkir, $id_transaksi);
            
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Gagal update ongkir: ' . $stmt->error];
            }
            
            // Get new total
            $select_query = "SELECT total_harga FROM " . $this->table . " WHERE id_transaksi = ?";
            $select_stmt = $this->conn->prepare($select_query);
            $select_stmt->bind_param('i', $id_transaksi);
            $select_stmt->execute();
            $new_total = $select_stmt->get_result()->fetch_assoc()['total_harga'];
            $select_stmt->close();
            
            $stmt->close();
            
            return [
                'success' => true,
                'new_total' => $new_total,
                'message' => 'Ongkir berhasil diupdate'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Generate unique transaction number
     * Format: TRX-YYYYMMDDHHMMSS
     * @return string
     */
    private function generateTransactionNumber() {
        return 'TRX-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    }
    
    /**
     * Calculate transaction total
     * @param int $subtotal
     * @param int $diskon
     * @param int $ongkir
     * @return int
     */
    public static function calculateTotal($subtotal, $diskon, $ongkir) {
        return $subtotal - $diskon + $ongkir;
    }
}
?>
