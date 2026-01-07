<?php
/**
 * Transaksi API
 * Endpoints untuk mengelola transaksi (pesanan)
 * 
 * Methods:
 * - POST /api/transaksi.php?action=create - Buat transaksi baru
 * - GET /api/transaksi.php?action=get&id=X - Ambil detail transaksi
 * - GET /api/transaksi.php?action=user&id=X - Ambil transaksi user
 * - PUT /api/transaksi.php?action=update&id=X - Update status transaksi
 * - DELETE /api/transaksi.php?action=delete&id=X - Hapus transaksi
 * - GET /api/transaksi.php?action=list - Ambil semua transaksi
 */

header('Content-Type: application/json');

require_once '../config/Database.php';
require_once '../includes/Transaksi.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $transaksi = new Transaksi($conn);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET requests
    if ($method === 'GET') {
        switch ($action) {
            case 'get':
                // Get single transaction
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    throw new Exception('ID transaksi diperlukan');
                }
                
                $result = $transaksi->getTransaksi($id);
                if (!$result) {
                    throw new Exception('Transaksi tidak ditemukan');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
                break;
                
            case 'user':
                // Get user transactions
                $id_user = $_GET['id'] ?? null;
                if (!$id_user) {
                    throw new Exception('ID user diperlukan');
                }
                
                $results = $transaksi->getUserTransaksi($id_user);
                echo json_encode([
                    'success' => true,
                    'data' => $results
                ]);
                break;
                
            case 'list':
                // Get all transactions
                $results = $transaksi->getAllTransaksi();
                echo json_encode([
                    'success' => true,
                    'data' => $results
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
            // Create new transaction
            $required_fields = ['id_user'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field])) {
                    throw new Exception('Field ' . $field . ' diperlukan');
                }
            }
            
            $result = $transaksi->createTransaksi($input['id_user']);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'id_transaksi' => $result['transaksi_id'],
                    'no_pesanan' => $result['no_pesanan']
                ]
            ]);
        } else {
            throw new Exception('Action tidak valid untuk POST: ' . $action);
        }
    }
    // PUT requests
    elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('ID transaksi diperlukan');
        }
        
        switch ($action) {
            case 'update':
                // Update transaction status
                $status = $input['status'] ?? null;
                if (!$status) {
                    throw new Exception('Status diperlukan');
                }
                
                $result = $transaksi->updateStatus($id, $status);
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
    // DELETE requests
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('ID transaksi diperlukan');
        }
        
        if ($action === 'delete') {
            $result = $transaksi->deleteTransaksi($id);
            
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
