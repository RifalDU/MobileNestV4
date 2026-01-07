<?php
/**
 * Pengiriman Class
 * Handles all pengiriman (shipping) related operations
 * 
 * Database Table: pengiriman
 * - id_pengiriman (PK, AUTO_INCREMENT)
 * - id_transaksi (FK), id_user (FK)
 * - no_pengiriman (UNIQUE)
 * - nama_penerima, no_telepon, email, alamat_lengkap
 * - provinsi, kota, kecamatan, kode_pos
 * - metode_pengiriman, ongkir, status_pengiriman
 * - tanggal_pengiriman, tanggal_konfirmasi, tanggal_diterima
 */

class Pengiriman {
    private $conn;
    private $table = 'pengiriman';
    
    // Ongkir rates (can be moved to config or database)
    private $ongkir_rates = [
        'regular' => 50000,
        'express' => 100000,
        'same_day' => 200000
    ];
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create shipping record
     * @param int $id_transaksi
     * @param int $id_user
     * @param array $data (nama_penerima, no_telepon, email, provinsi, kota, kecamatan, kode_pos, alamat_lengkap, metode_pengiriman)
     * @return array {success: bool, shipping_id: int, no_pengiriman: string, ongkir: int, message: string}
     */
    public function createShipping($id_transaksi, $id_user, $data) {
        try {
            // Validate required fields
            $required_fields = ['nama_penerima', 'no_telepon', 'email', 'provinsi', 'kota', 'kecamatan', 'kode_pos', 'alamat_lengkap'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => 'Field ' . $field . ' harus diisi'
                    ];
                }
            }
            
            // Set default metode_pengiriman if not provided
            $metode_pengiriman = $data['metode_pengiriman'] ?? 'regular';
            $ongkir = $this->calculateOngkir($metode_pengiriman, $data['kota']);
            
            // Generate unique shipping number
            $no_pengiriman = $this->generateShippingNumber();
            
            $query = "INSERT INTO " . $this->table . " 
                      (id_transaksi, id_user, no_pengiriman, nama_penerima, no_telepon, email,
                       provinsi, kota, kecamatan, kode_pos, alamat_lengkap, metode_pengiriman, ongkir, status_pengiriman)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $status = 'Menunggu Pickup';
            
            $stmt->bind_param(
                'iisssssssssii',
                $id_transaksi,
                $id_user,
                $no_pengiriman,
                $data['nama_penerima'],
                $data['no_telepon'],
                $data['email'],
                $data['provinsi'],
                $data['kota'],
                $data['kecamatan'],
                $data['kode_pos'],
                $data['alamat_lengkap'],
                $metode_pengiriman,
                $ongkir,
                $status
            );
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuat pengiriman: ' . $stmt->error
                ];
            }
            
            $shipping_id = $stmt->insert_id;
            $stmt->close();
            
            // Update transaksi ongkir
            $transaksi = new Transaksi($this->conn);
            $transaksi->updateOngkir($id_transaksi, $ongkir);
            
            return [
                'success' => true,
                'shipping_id' => $shipping_id,
                'no_pengiriman' => $no_pengiriman,
                'ongkir' => $ongkir,
                'message' => 'Data pengiriman berhasil dibuat'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get shipping info
     * @param int $id_pengiriman
     * @return array shipping data atau null
     */
    public function getShippingInfo($id_pengiriman) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_pengiriman = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return null;
            }
            
            $stmt->bind_param('i', $id_pengiriman);
            if (!$stmt->execute()) {
                return null;
            }
            
            $result = $stmt->get_result();
            $shipping = $result->fetch_assoc();
            $stmt->close();
            
            return $shipping;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get shipping by transaction ID
     * @param int $id_transaksi
     * @return array shipping data atau null
     */
    public function getShippingByTransaksi($id_transaksi) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_transaksi = ? LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return null;
            }
            
            $stmt->bind_param('i', $id_transaksi);
            if (!$stmt->execute()) {
                return null;
            }
            
            $result = $stmt->get_result();
            $shipping = $result->fetch_assoc();
            $stmt->close();
            
            return $shipping;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update shipping address
     * @param int $id_pengiriman
     * @param array $data (nama_penerima, no_telepon, email, provinsi, kota, kecamatan, kode_pos, alamat_lengkap)
     * @return array {success: bool, message: string}
     */
    public function updateAddress($id_pengiriman, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET nama_penerima = ?, no_telepon = ?, email = ?,
                          provinsi = ?, kota = ?, kecamatan = ?, kode_pos = ?, alamat_lengkap = ?,
                          updated_at = NOW()
                      WHERE id_pengiriman = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param(
                'ssssssssi',
                $data['nama_penerima'],
                $data['no_telepon'],
                $data['email'],
                $data['provinsi'],
                $data['kota'],
                $data['kecamatan'],
                $data['kode_pos'],
                $data['alamat_lengkap'],
                $id_pengiriman
            );
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal update alamat: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Alamat pengiriman berhasil diupdate'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update shipping method
     * @param int $id_pengiriman
     * @param string $metode_pengiriman (regular, express, same_day)
     * @param string $kota
     * @return array {success: bool, new_ongkir: int, message: string}
     */
    public function updateMethod($id_pengiriman, $metode_pengiriman, $kota) {
        try {
            $valid_methods = ['regular', 'express', 'same_day'];
            if (!in_array($metode_pengiriman, $valid_methods)) {
                return [
                    'success' => false,
                    'message' => 'Metode pengiriman tidak valid. Pilih: ' . implode(', ', $valid_methods)
                ];
            }
            
            $new_ongkir = $this->calculateOngkir($metode_pengiriman, $kota);
            
            // Get id_transaksi
            $get_query = "SELECT id_transaksi FROM " . $this->table . " WHERE id_pengiriman = ?";
            $get_stmt = $this->conn->prepare($get_query);
            $get_stmt->bind_param('i', $id_pengiriman);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Pengiriman tidak ditemukan'
                ];
            }
            
            $id_transaksi = $result->fetch_assoc()['id_transaksi'];
            $get_stmt->close();
            
            // Update pengiriman
            $query = "UPDATE " . $this->table . " 
                      SET metode_pengiriman = ?, ongkir = ?, updated_at = NOW()
                      WHERE id_pengiriman = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param('sii', $metode_pengiriman, $new_ongkir, $id_pengiriman);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal update metode pengiriman: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            // Update transaksi ongkir
            $transaksi = new Transaksi($this->conn);
            $transaksi->updateOngkir($id_transaksi, $new_ongkir);
            
            return [
                'success' => true,
                'new_ongkir' => $new_ongkir,
                'message' => 'Metode pengiriman berhasil diupdate'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate shipping cost
     * @param string $metode (regular, express, same_day)
     * @param string $kota
     * @return int ongkir amount
     */
    public function calculateOngkir($metode, $kota = null) {
        // Basic calculation - can be extended with city-based rates
        if (isset($this->ongkir_rates[$metode])) {
            return $this->ongkir_rates[$metode];
        }
        return $this->ongkir_rates['regular']; // Default
    }
    
    /**
     * Update shipping status
     * @param int $id_pengiriman
     * @param string $status (Menunggu Pickup, Dalam Pengiriman, Tiba di Tujuan, Diterima, Batal)
     * @return array {success: bool, message: string}
     */
    public function updateStatus($id_pengiriman, $status) {
        try {
            $valid_status = ['Menunggu Pickup', 'Dalam Pengiriman', 'Tiba di Tujuan', 'Diterima', 'Batal'];
            if (!in_array($status, $valid_status)) {
                return [
                    'success' => false,
                    'message' => 'Status tidak valid. Status yang tersedia: ' . implode(', ', $valid_status)
                ];
            }
            
            $update_fields = 'status_pengiriman = ?, updated_at = NOW()';
            $params = [$status, $id_pengiriman];
            $types = 'si';
            
            // Set additional timestamps based on status
            if ($status === 'Dalam Pengiriman') {
                $update_fields = 'status_pengiriman = ?, tanggal_pengiriman = NOW(), updated_at = NOW()';
            } elseif ($status === 'Diterima') {
                $update_fields = 'status_pengiriman = ?, tanggal_diterima = NOW(), updated_at = NOW()';
            }
            
            $query = "UPDATE " . $this->table . " SET " . $update_fields . " WHERE id_pengiriman = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [
                    'success' => false,
                    'message' => 'Prepare statement gagal: ' . $this->conn->error
                ];
            }
            
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => 'Gagal update status pengiriman: ' . $stmt->error
                ];
            }
            
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Status pengiriman berhasil diupdate ke: ' . $status
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update shipping status by transaction ID
     * @param int $id_transaksi
     * @param string $status
     * @return array {success: bool, message: string}
     */
    public function updateStatusByTransaksi($id_transaksi, $status) {
        try {
            $get_query = "SELECT id_pengiriman FROM " . $this->table . " WHERE id_transaksi = ? LIMIT 1";
            $get_stmt = $this->conn->prepare($get_query);
            $get_stmt->bind_param('i', $id_transaksi);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Pengiriman untuk transaksi ini tidak ditemukan'
                ];
            }
            
            $id_pengiriman = $result->fetch_assoc()['id_pengiriman'];
            $get_stmt->close();
            
            return $this->updateStatus($id_pengiriman, $status);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get shipping timeline
     * @param int $id_pengiriman
     * @return array timeline data
     */
    public function getTimeline($id_pengiriman) {
        try {
            $query = "SELECT tanggal_pengiriman, tanggal_konfirmasi, tanggal_diterima, status_pengiriman 
                      FROM " . $this->table . " WHERE id_pengiriman = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                return [];
            }
            
            $stmt->bind_param('i', $id_pengiriman);
            if (!$stmt->execute()) {
                return [];
            }
            
            $result = $stmt->get_result();
            $timeline = $result->fetch_assoc();
            $stmt->close();
            
            return $timeline ?? [];
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Generate unique shipping number
     * Format: SHIP-YYYYMMDDHHMMSS
     * @return string
     */
    private function generateShippingNumber() {
        return 'SHIP-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    }
}
?>
