<?php
/**
 * Detail Transaksi API
 * Endpoints untuk mengelola item transaksi
 * 
 * Methods:
 * - POST /api/detail_transaksi.php?action=add - Tambah item ke transaksi
 * - GET /api/detail_transaksi.php?action=order&id=X - Ambil semua item transaksi
 * - GET /api/detail_transaksi.php?action=get&id=X - Ambil detail item
 * - PUT /api/detail_transaksi.php?action=update&id=X - Update quantity item
 * - DELETE /api/detail_transaksi.php?action=remove&id=X - Hapus item dari transaksi
 */

header('Content-Type: application/json');

require_once '../config/Database.php';
require_once '../includes/DetailTransaksi.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $detail = new DetailTransaksi($conn);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET requests
    if ($method === 'GET') {
        switch ($action) {
            case 'order':
                // Get all items in order
                $id_transaksi = $_GET['id'] ?? null;
                if (!$id_transaksi) {
                    throw new Exception('ID transaksi diperlukan');
                }
                
                $items = $detail->getOrderItems($id_transaksi);
                echo json_encode([
                    'success' => true,
                    'data' => $items
                ]);
                break;
                
            case 'get':
                // Get single item detail
                $id_detail = $_GET['id'] ?? null;
                if (!$id_detail) {
                    throw new Exception('ID detail diperlukan');
                }
                
                $item = $detail->getItem($id_detail);
                if (!$item) {
                    throw new Exception('Item tidak ditemukan');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $item
                ]);
                break;
                
            default:
                throw new Exception('Action tidak valid: ' . $action);
        }
    }
    // POST requests
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'add') {
            // Add item to transaction
            $required_fields = ['id_transaksi', 'id_produk', 'nama_produk', 'harga_satuan', 'jumlah'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field])) {
                    throw new Exception('Field ' . $field . ' diperlukan');
                }
            }
            
            $result = $detail->addItem(
                $input['id_transaksi'],
                $input['id_produk'],
                $input['nama_produk'],
                $input['harga_satuan'],
                $input['jumlah']
            );
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'detail_id' => $result['detail_id'],
                    'subtotal' => $result['subtotal']
                ]
            ]);
        } else {
            throw new Exception('Action tidak valid untuk POST: ' . $action);
        }
    }
    // PUT requests
    elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_detail = $_GET['id'] ?? null;
        
        if (!$id_detail) {
            throw new Exception('ID detail diperlukan');
        }
        
        if ($action === 'update') {
            // Update item quantity
            $jumlah = $input['jumlah'] ?? null;
            if ($jumlah === null) {
                throw new Exception('Jumlah diperlukan');
            }
            
            $result = $detail->updateQuantity($id_detail, $jumlah);
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'new_subtotal' => $result['new_subtotal']
                ]
            ]);
        } else {
            throw new Exception('Action tidak valid untuk PUT: ' . $action);
        }
    }
    // DELETE requests
    elseif ($method === 'DELETE') {
        $id_detail = $_GET['id'] ?? null;
        
        if (!$id_detail) {
            throw new Exception('ID detail diperlukan');
        }
        
        if ($action === 'remove') {
            $result = $detail->removeItem($id_detail);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            throw new Exception('Action tidak valid untuk DELETE: ' . $action);
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
