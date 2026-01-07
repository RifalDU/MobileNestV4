<?php
/**
 * Keranjang Class
 * Handles all keranjang (shopping cart) related operations
 * 
 * Database Table: keranjang
 * - id_keranjang (PK, AUTO_INCREMENT)
 * - id_user (FK), id_produk (FK)
 * - jumlah, tanggal_ditambahkan
 */

class Keranjang {
    private $conn;
    private $table = 'keranjang';
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Add item to cart (or update if already exists)
     * @param int $id_user
     * @param int $id_produk
     * @param int $jumlah (default 1)
     * @return array {success: bool, cart_id: int, message: string}
     */
    public function addItem($id_user, $id_produk, $jumlah = 1) {
        try {
            if ($jumlah <= 0) {
                return [
                    'success' => false,
                    'message' => 'Jumlah produk harus lebih dari 0'
                ];
            }
            
            // Check if item already in cart
            $check_query = "SELECT id_keranjang, jumlah FROM " . $this->table . " 
                           WHERE id_user = ? AND id_produk = ?";
            
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $check_stmt->bind_param('ii', $id_user, $id_produk);
            if (!$check_stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Query gagal: ' . $check_stmt->error
                ];
            }
            
            $result = $check_stmt->get_result();
            $check_stmt->close();
            
            if ($result->num_rows > 0) {
                // Item already exists - update quantity
                $existing = $result->fetch_assoc();
                $new_jumlah = $existing['jumlah'] + $jumlah;
                
                return $this->updateQuantity($existing['id_keranjang'], $new_jumlah);
            }
            
            // Item not in cart - insert new
            $insert_query = "INSERT INTO " . $this->table . " 
                            (id_user, id_produk, jumlah, tanggal_ditambahkan)
                            VALUES (?, ?, ?, NOW())";
            
            $insert_stmt = $this->conn->prepare($insert_query);
            if (!$insert_stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $insert_stmt->bind_param('iii', $id_user, $id_produk, $jumlah);
            
            if (!$insert_stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal menambah item ke keranjang: ' . $insert_stmt->error
                ];
            }
            
            $cart_id = $insert_stmt->insert_id;
            $insert_stmt->close();
            
            return [
                'success' => true,
                'cart_id' => $cart_id,
                'message' => 'Produk berhasil ditambahkan ke keranjang'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user cart with product details
     * @param int $id_user
     * @return array cart items
     */
    public function getCart($id_user) {
        try {
            $query = "SELECT k.id_keranjang, k.id_produk, k.jumlah, k.tanggal_ditambahkan,
                      p.id_produk, p.nama_produk, p.harga, p.gambar, p.stok, p.kategori
                      FROM " . $this->table . " k
                      JOIN produk p ON k.id_produk = p.id_produk
                      WHERE k.id_user = ?
                      ORDER BY k.tanggal_ditambahkan DESC";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param('i', $id_user);
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            $cart = [];
            
            while ($row = $result->fetch_assoc()) {
                $cart[] = $row;
            }
            
            $stmt->close();
            return $cart;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get single cart item
     * @param int $id_keranjang
     * @return array cart item atau null
     */
    public function getCartItem($id_keranjang) {
        try {
            $query = "SELECT k.*, p.nama_produk, p.harga, p.stok
                      FROM " . $this->table . " k
                      JOIN produk p ON k.id_produk = p.id_produk
                      WHERE k.id_keranjang = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return null;
            }
            
            $stmt->bind_param('i', $id_keranjang);
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
     * Remove item from cart
     * @param int $id_keranjang
     * @return array {success: bool, message: string}
     */
    public function removeItem($id_keranjang) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id_keranjang = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param('i', $id_keranjang);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus item: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Item berhasil dihapus dari keranjang'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Clear entire cart for user
     * @param int $id_user
     * @return array {success: bool, message: string}
     */
    public function clearCart($id_user) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id_user = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param('i', $id_user);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengosongkan keranjang: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Keranjang berhasil dikosongkan'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update cart item quantity
     * @param int $id_keranjang
     * @param int $jumlah
     * @return array {success: bool, message: string}
     */
    public function updateQuantity($id_keranjang, $jumlah) {
        try {
            if ($jumlah <= 0) {
                return $this->removeItem($id_keranjang);
            }
            
            $query = "UPDATE " . $this->table . " SET jumlah = ? WHERE id_keranjang = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param('ii', $jumlah, $id_keranjang);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal update jumlah: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
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
     * Get cart total (sum of all items price)
     * @param int $id_user
     * @return int total amount
     */
    public function getCartTotal($id_user) {
        try {
            $query = "SELECT SUM(k.jumlah * p.harga) as total
                      FROM " . $this->table . " k
                      JOIN produk p ON k.id_produk = p.id_produk
                      WHERE k.id_user = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param('i', $id_user);
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
     * Get cart item count
     * @param int $id_user
     * @return int item count
     */
    public function getCartItemCount($id_user) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE id_user = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param('i', $id_user);
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
     * Get cart total quantity
     * @param int $id_user
     * @return int total quantity
     */
    public function getCartTotalQuantity($id_user) {
        try {
            $query = "SELECT SUM(jumlah) as total_qty FROM " . $this->table . " WHERE id_user = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return 0;
            }
            
            $stmt->bind_param('i', $id_user);
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
    
    /**
     * Check if item exists in cart
     * @param int $id_user
     * @param int $id_produk
     * @return bool
     */
    public function itemExists($id_user, $id_produk) {
        try {
            $query = "SELECT id_keranjang FROM " . $this->table . " 
                     WHERE id_user = ? AND id_produk = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param('ii', $id_user, $id_produk);
            if (!$stmt->execute()) {
                return false;
            }
            
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            
            return $exists;
            
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
