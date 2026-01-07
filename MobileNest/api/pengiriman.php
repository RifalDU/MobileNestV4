<?php
/**
 * Pengiriman API
 * Endpoints untuk mengelola pengiriman (shipping)
 * 
 * Methods:
 * - POST /api/pengiriman.php?action=create - Buat data pengiriman baru
 * - GET /api/pengiriman.php?action=get&id=X - Ambil detail pengiriman
 * - GET /api/pengiriman.php?action=transaksi&id=X - Ambil pengiriman by transaksi ID
 * - PUT /api/pengiriman.php?action=address&id=X - Update alamat pengiriman
 * - PUT /api/pengiriman.php?action=method&id=X - Update metode pengiriman
 * - PUT /api/pengiriman.php?action=status&id=X - Update status pengiriman
 * - GET /api/pengiriman.php?action=timeline&id=X - Ambil timeline pengiriman
 */

header('Content-Type: application/json');

require_once '../config/Database.php';
require_once '../includes/Pengiriman.php';
require_once '../includes/Transaksi.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $pengiriman = new Pengiriman($conn);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET requests
    if ($method === 'GET') {
        switch ($action) {
            case 'get':
                // Get single shipping info
                $id_pengiriman = $_GET['id'] ?? null;
                if (!$id_pengiriman) {
                    throw new Exception('ID pengiriman diperlukan');
                }
                
                $shipping = $pengiriman->getShippingInfo($id_pengiriman);
                if (!$shipping) {
                    throw new Exception('Data pengiriman tidak ditemukan');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $shipping
                ]);
                break;
                
            case 'transaksi':
                // Get shipping by transaction ID
                $id_transaksi = $_GET['id'] ?? null;
                if (!$id_transaksi) {
                    throw new Exception('ID transaksi diperlukan');
                }
                
                $shipping = $pengiriman->getShippingByTransaksi($id_transaksi);
                if (!$shipping) {
                    throw new Exception('Data pengiriman tidak ditemukan');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $shipping
                ]);
                break;
                
            case 'timeline':
                // Get shipping timeline
                $id_pengiriman = $_GET['id'] ?? null;
                if (!$id_pengiriman) {
                    throw new Exception('ID pengiriman diperlukan');
                }
                
                $timeline = $pengiriman->getTimeline($id_pengiriman);
                echo json_encode([
                    'success' => true,
                    'data' => $timeline
                ]);
                break;
                
            default:
                throw new Exception('Action tidak valid: ' . $action);
        }
    }
    // POST requests
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'create') {
            // Create shipping record
            $required_fields = ['id_transaksi', 'id_user', 'nama_penerima', 'no_telepon', 'email', 
                              'provinsi', 'kota', 'kecamatan', 'kode_pos', 'alamat_lengkap'];
            
            foreach ($required_fields as $field) {
                if (!isset($input[$field])) {
                    throw new Exception('Field ' . $field . ' diperlukan');
                }
            }
            
            $result = $pengiriman->createShipping(
                $input['id_transaksi'],
                $input['id_user'],
                $input
            );
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'shipping_id' => $result['shipping_id'],
                    'no_pengiriman' => $result['no_pengiriman'],
                    'ongkir' => $result['ongkir']
                ]
            ]);
        } else {
            throw new Exception('Action tidak valid untuk POST: ' . $action);
        }
    }
    // PUT requests
    elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_pengiriman = $_GET['id'] ?? null;
        
        if (!$id_pengiriman) {
            throw new Exception('ID pengiriman diperlukan');
        }
        
        switch ($action) {
            case 'address':
                // Update shipping address
                $required_fields = ['nama_penerima', 'no_telepon', 'email', 
                                  'provinsi', 'kota', 'kecamatan', 'kode_pos', 'alamat_lengkap'];
                
                foreach ($required_fields as $field) {
                    if (!isset($input[$field])) {
                        throw new Exception('Field ' . $field . ' diperlukan');
                    }
                }
                
                $result = $pengiriman->updateAddress($id_pengiriman, $input);
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => $result['message']
                ]);
                break;
                
            case 'method':
                // Update shipping method
                $metode = $input['metode_pengiriman'] ?? null;
                $kota = $input['kota'] ?? null;
                
                if (!$metode || !$kota) {
                    throw new Exception('metode_pengiriman dan kota diperlukan');
                }
                
                $result = $pengiriman->updateMethod($id_pengiriman, $metode, $kota);
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'new_ongkir' => $result['new_ongkir']
                    ]
                ]);
                break;
                
            case 'status':
                // Update shipping status
                $status = $input['status'] ?? null;
                if (!$status) {
                    throw new Exception('Status diperlukan');
                }
                
                $result = $pengiriman->updateStatus($id_pengiriman, $status);
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => $result['message']
                ]);
                break;
                
            default:
                throw new Exception('Action tidak valid untuk PUT: ' . $action);
        }
    }
    else {
        throw new Exception('Method tidak didukung: ' . $method);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
