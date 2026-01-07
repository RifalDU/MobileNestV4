<?php
/**
 * Keranjang API
 * Endpoints untuk mengelola keranjang belanja
 * 
 * Methods:
 * - POST /api/keranjang.php?action=add - Tambah item ke keranjang
 * - GET /api/keranjang.php?action=get&id=X - Ambil keranjang user
 * - GET /api/keranjang.php?action=total&id=X - Ambil total harga keranjang
 * - GET /api/keranjang.php?action=count&id=X - Ambil jumlah item di keranjang
 * - PUT /api/keranjang.php?action=update&id=X - Update jumlah item
 * - DELETE /api/keranjang.php?action=remove&id=X - Hapus item dari keranjang
 * - DELETE /api/keranjang.php?action=clear&id=X - Kosongkan seluruh keranjang
 */

header('Content-Type: application/json');

require_once '../config/Database.php';
require_once '../includes/Keranjang.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $keranjang = new Keranjang($conn);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET requests
    if ($method === 'GET') {
        $id_user = $_GET['id'] ?? null;
        
        switch ($action) {
            case 'get':
                // Get user cart
                if (!$id_user) {
                    throw new Exception('ID user diperlukan');
                }
                
                $cart = $keranjang->getCart($id_user);
                echo json_encode([
                    'success' => true,
                    'data' => $cart
                ]);
                break;
                
            case 'total':
                // Get cart total
                if (!$id_user) {
                    throw new Exception('ID user diperlukan');
                }
                
                $total = $keranjang->getCartTotal($id_user);
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'total' => $total
                    ]
                ]);
                break;
                
            case 'count':
                // Get item count
                if (!$id_user) {
                    throw new Exception('ID user diperlukan');
                }
                
                $count = $keranjang->getCartItemCount($id_user);
                $total_qty = $keranjang->getCartTotalQuantity($id_user);
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'item_count' => $count,
                        'total_quantity' => $total_qty
                    ]
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
            // Add item to cart
            $required_fields = ['id_user', 'id_produk'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field])) {
                    throw new Exception('Field ' . $field . ' diperlukan');
                }
            }
            
            $jumlah = $input['jumlah'] ?? 1;
            
            $result = $keranjang->addItem(
                $input['id_user'],
                $input['id_produk'],
                $jumlah
            );
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'cart_id' => $result['cart_id']
                ]
            ]);
        } else {
            throw new Exception('Action tidak valid untuk POST: ' . $action);
        }
    }
    // PUT requests
    elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_keranjang = $_GET['id'] ?? null;
        
        if (!$id_keranjang) {
            throw new Exception('ID keranjang diperlukan');
        }
        
        if ($action === 'update') {
            // Update quantity
            $jumlah = $input['jumlah'] ?? null;
            if ($jumlah === null) {
                throw new Exception('Jumlah diperlukan');
            }
            
            $result = $keranjang->updateQuantity($id_keranjang, $jumlah);
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            throw new Exception('Action tidak valid untuk PUT: ' . $action);
        }
    }
    // DELETE requests
    elseif ($method === 'DELETE') {
        switch ($action) {
            case 'remove':
                // Remove item from cart
                $id_keranjang = $_GET['id'] ?? null;
                if (!$id_keranjang) {
                    throw new Exception('ID keranjang diperlukan');
                }
                
                $result = $keranjang->removeItem($id_keranjang);
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => $result['message']
                ]);
                break;
                
            case 'clear':
                // Clear entire cart
                $id_user = $_GET['id'] ?? null;
                if (!$id_user) {
                    throw new Exception('ID user diperlukan');
                }
                
                $result = $keranjang->clearCart($id_user);
                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => $result['message']
                ]);
                break;
                
            default:
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
