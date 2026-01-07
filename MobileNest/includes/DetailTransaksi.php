<?php
/**
 * DetailTransaksi Class
 * Handles all detail_transaksi (order items) operations
 * 
 * Database Table: detail_transaksi
 * - id_detail (PK, AUTO_INCREMENT)
 * - id_transaksi (FK)
 * - id_produk (FK)
 * - nama_produk, harga_satuan, jumlah, subtotal
 */

class DetailTransaksi {
    private $conn;
    private $table = 'detail_transaksi';
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Add item to transaksi
     * @param int $id_transaksi
     * @param int $id_produk
     * @param string $nama_produk
     * @param int $harga_satuan
     * @param int $jumlah
     * @return array {success: bool, detail_id: int, subtotal: int, message: string}
     */
    public function addItem($id_transaksi, $id_produk, $nama_produk, $harga_satuan, $jumlah) {
        try {
            if ($harga_satuan < 0 || $jumlah < 0) {
                return [
                    'success' => false,
                    'message' => 'Harga dan jumlah tidak boleh negatif'
                ];
            }
            
            $subtotal = $harga_satuan * $jumlah;
            
            $query = "INSERT INTO " . $this->table . " 
                      (id_transaksi, id_produk, nama_produk, harga_satuan, jumlah, subtotal)
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param(
                'iisiii',
                $id_transaksi,
                $id_produk,
                $nama_produk,
                $harga_satuan,
                $jumlah,
                $subtotal
            );
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal menambah item: ' . $stmt->error
                ];
            }
            
            $detail_id = $stmt->insert_id;
            $stmt->close();
            
            return [
                'success' => true,
                'detail_id' => $detail_id,
                'subtotal' => $subtotal,
                'message' => 'Item berhasil ditambahkan ke pesanan'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all items in order
     * @param int $id_transaksi
     * @return array items array atau empty array
     */
    public function getOrderItems($id_transaksi) {
        try {
            $query = "SELECT dt.*, p.gambar, p.kategori
                      FROM " . $this->table . " dt
                      LEFT JOIN produk p ON dt.id_produk = p.id_produk
                      WHERE dt.id_transaksi = ?
                      ORDER BY dt.id_detail ASC";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param('i', $id_transaksi);
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            $items = [];
            
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            $stmt->close();
            return $items;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get single item
     * @param int $id_detail
     * @return array item data atau null
     */
    public function getItem($id_detail) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_detail = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return null;
            }
            
            $stmt->bind_param('i', $id_detail);
            if (!$stmt->execute()) {
                return null;
            }
            
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            $stmt->close();
            
            return $item;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Remove item from transaksi
     * @param int $id_detail
     * @return array {success: bool, message: string}
     */
    public function removeItem($id_detail) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id_detail = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param('i', $id_detail);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus item: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Item berhasil dihapus'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update item quantity
     * @param int $id_detail
     * @param int $jumlah
     * @return array {success: bool, new_subtotal: int, message: string}
     */
    public function updateQuantity($id_detail, $jumlah) {
        try {
            if ($jumlah <= 0) {
                return [
                    'success' => false,
                    'message' => 'Jumlah harus lebih dari 0'
                ];
            }
            
            // Get current harga_satuan
            $get_query = "SELECT harga_satuan FROM " . $this->table . " WHERE id_detail = ?";
            $get_stmt = $this->conn->prepare($get_query);
            $get_stmt->bind_param('i', $id_detail);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Item tidak ditemukan'
                ];
            }
            
            $harga_satuan = $result->fetch_assoc()['harga_satuan'];
            $get_stmt->close();
            
            $new_subtotal = $harga_satuan * $jumlah;
            
            // Update quantity dan subtotal
            $query = "UPDATE " . $this->table . " 
                      SET jumlah = ?, subtotal = ?
                      WHERE id_detail = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param('iii', $jumlah, $new_subtotal, $id_detail);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal update jumlah: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'new_subtotal' => $new_subtotal,
                'message' => 'Jumlah item berhasil diupdate'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get order subtotal (sum of all items)
     * @param int $id_transaksi
     * @return int subtotal
     */
    public function getOrderSubtotal($id_transaksi) {
        try {
            $query = "SELECT SUM(subtotal) as total FROM " . $this->table . " WHERE id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param('i', $id_transaksi);
            if (!$stmt->execute()) {
                return 0;
            }
            
            $result = $stmt->get_result();
            $total = $result->fetch_assoc()['total'];
            $stmt->close();
            
            return (int)($total ?? 0);
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get item count in order
     * @param int $id_transaksi
     * @return int count
     */
    public function getItemCount($id_transaksi) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param('i', $id_transaksi);
            if (!$stmt->execute()) {
                return 0;
            }
            
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $stmt->close();
            
            return (int)$count;
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get total quantity in order
     * @param int $id_transaksi
     * @return int total quantity
     */
    public function getTotalQuantity($id_transaksi) {
        try {
            $query = "SELECT SUM(jumlah) as total_qty FROM " . $this->table . " WHERE id_transaksi = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param('i', $id_transaksi);
            if (!$stmt->execute()) {
                return 0;
            }
            
            $result = $stmt->get_result();
            $total_qty = $result->fetch_assoc()['total_qty'];
            $stmt->close();
            
            return (int)($total_qty ?? 0);
            
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>
