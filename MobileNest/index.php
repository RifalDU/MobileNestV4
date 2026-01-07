<?php
/**
 * MobileNestV4 - API Status Check & Quick Test
 * 
 * This file provides:
 * - Server status check
 * - Database connection test
 * - Quick API endpoint testing
 * - System information
 */

header('Content-Type: application/json');

require_once 'config/Database.php';
require_once 'config/Constants.php';

// Get action from query string
$action = $_GET['action'] ?? 'status';

try {
    switch ($action) {
        case 'status':
            // Server status check
            $db = new Database();
            $conn = $db->connect();
            
            $db_status = $conn ? 'Connected' : 'Failed';
            $php_version = phpversion();
            $current_time = date('Y-m-d H:i:s');
            
            echo json_encode([
                'success' => true,
                'status' => 'MobileNestV4 Backend - OK',
                'version' => '1.0.0',
                'php_version' => $php_version,
                'current_time' => $current_time,
                'database_status' => $db_status,
                'api_base_url' => BASE_URL . '/api',
                'endpoints' => [
                    'auth' => BASE_URL . '/api/auth.php',
                    'user' => BASE_URL . '/api/user.php',
                    'produk' => BASE_URL . '/api/produk.php',
                    'kategori' => BASE_URL . '/api/kategori.php',
                    'transaksi' => BASE_URL . '/api/transaksi.php',
                    'detail_transaksi' => BASE_URL . '/api/detail_transaksi.php',
                    'pengiriman' => BASE_URL . '/api/pengiriman.php',
                    'keranjang' => BASE_URL . '/api/keranjang.php',
                    'order' => BASE_URL . '/api/order.php',
                    'search' => BASE_URL . '/api/search.php',
                    'analytics' => BASE_URL . '/api/analytics.php'
                ],
                'documentation' => [
                    'overview' => 'SUMMARY_20_FILES.txt',
                    'full_docs' => 'DOKUMENTASI_20_FILES.md',
                    'developer_guide' => 'DEVELOPER_GUIDE.md',
                    'checklist' => 'IMPLEMENTATION_CHECKLIST.md'
                ]
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'test':
            // Quick test endpoint
            $test_results = [
                'timestamp' => date('Y-m-d H:i:s'),
                'tests' => []
            ];
            
            // Test database connection
            $db = new Database();
            $conn = $db->connect();
            $test_results['tests']['database_connection'] = $conn ? 'PASS' : 'FAIL';
            
            // Test constants
            $test_results['tests']['constants_loaded'] = defined('BASE_URL') ? 'PASS' : 'FAIL';
            
            // Test file includes
            $test_results['tests']['config_files_exist'] = (
                file_exists('config/Database.php') &&
                file_exists('config/Constants.php')
            ) ? 'PASS' : 'FAIL';
            
            $test_results['tests']['includes_files_exist'] = (
                file_exists('includes/User.php') &&
                file_exists('includes/Produk.php') &&
                file_exists('includes/Kategori.php') &&
                file_exists('includes/Transaksi.php') &&
                file_exists('includes/DetailTransaksi.php') &&
                file_exists('includes/Pengiriman.php') &&
                file_exists('includes/Keranjang.php')
            ) ? 'PASS' : 'FAIL';
            
            $test_results['tests']['api_files_exist'] = (
                file_exists('api/user.php') &&
                file_exists('api/produk.php') &&
                file_exists('api/kategori.php') &&
                file_exists('api/transaksi.php') &&
                file_exists('api/detail_transaksi.php') &&
                file_exists('api/pengiriman.php') &&
                file_exists('api/keranjang.php') &&
                file_exists('api/auth.php') &&
                file_exists('api/order.php') &&
                file_exists('api/search.php') &&
                file_exists('api/analytics.php')
            ) ? 'PASS' : 'FAIL';
            
            // Count passed tests
            $passed = array_reduce($test_results['tests'], function($carry, $item) {
                return $carry + ($item === 'PASS' ? 1 : 0);
            }, 0);
            
            $total = count($test_results['tests']);
            $test_results['summary'] = [
                'total_tests' => $total,
                'passed' => $passed,
                'failed' => $total - $passed,
                'status' => $passed === $total ? 'ALL TESTS PASSED' : 'SOME TESTS FAILED'
            ];
            
            echo json_encode($test_results, JSON_PRETTY_PRINT);
            break;
            
        case 'endpoints':
            // List all endpoints
            $endpoints = [
                'Authentication' => [
                    'POST /api/auth.php?action=register' => 'Register user',
                    'POST /api/auth.php?action=login' => 'Login user',
                    'POST /api/auth.php?action=logout' => 'Logout',
                    'POST /api/auth.php?action=refresh' => 'Refresh token'
                ],
                'User Management' => [
                    'GET /api/user.php?action=list' => 'Get all users',
                    'GET /api/user.php?action=get&id=X' => 'Get user by ID',
                    'POST /api/user.php?action=create' => 'Create user',
                    'PUT /api/user.php?action=update&id=X' => 'Update user',
                    'DELETE /api/user.php?action=delete&id=X' => 'Delete user'
                ],
                'Products' => [
                    'GET /api/produk.php?action=list' => 'Get all products',
                    'GET /api/produk.php?action=get&id=X' => 'Get product by ID',
                    'GET /api/produk.php?action=kategori&id=X' => 'Get products by category',
                    'GET /api/search.php?q=keyword' => 'Search products',
                    'POST /api/produk.php?action=create' => 'Create product',
                    'PUT /api/produk.php?action=update&id=X' => 'Update product',
                    'DELETE /api/produk.php?action=delete&id=X' => 'Delete product'
                ],
                'Categories' => [
                    'GET /api/kategori.php?action=list' => 'Get all categories',
                    'GET /api/kategori.php?action=get&id=X' => 'Get category by ID',
                    'POST /api/kategori.php?action=create' => 'Create category',
                    'PUT /api/kategori.php?action=update&id=X' => 'Update category',
                    'DELETE /api/kategori.php?action=delete&id=X' => 'Delete category'
                ],
                'Shopping Cart' => [
                    'GET /api/keranjang.php?action=get&id=X' => 'Get user cart',
                    'GET /api/keranjang.php?action=total&id=X' => 'Get cart total',
                    'GET /api/keranjang.php?action=count&id=X' => 'Get cart item count',
                    'POST /api/keranjang.php?action=add' => 'Add item to cart',
                    'PUT /api/keranjang.php?action=update&id=X' => 'Update item quantity',
                    'DELETE /api/keranjang.php?action=remove&id=X' => 'Remove item from cart',
                    'DELETE /api/keranjang.php?action=clear&id=X' => 'Clear entire cart'
                ],
                'Orders' => [
                    'POST /api/order.php?action=checkout' => 'Complete checkout',
                    'GET /api/transaksi.php?action=get&id=X' => 'Get order details',
                    'GET /api/transaksi.php?action=user&id=X' => 'Get user orders',
                    'GET /api/transaksi.php?action=list' => 'Get all orders (admin)',
                    'GET /api/detail_transaksi.php?action=order&id=X' => 'Get order items'
                ],
                'Shipping' => [
                    'GET /api/pengiriman.php?action=transaksi&id=X' => 'Get shipping by order',
                    'GET /api/pengiriman.php?action=timeline&id=X' => 'Get shipping timeline',
                    'PUT /api/pengiriman.php?action=status&id=X' => 'Update shipping status'
                ],
                'Admin' => [
                    'GET /api/analytics.php?action=summary' => 'Dashboard summary',
                    'GET /api/analytics.php?action=sales' => 'Sales report',
                    'GET /api/analytics.php?action=products' => 'Product analytics',
                    'GET /api/analytics.php?action=users' => 'User analytics'
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'total_endpoints' => array_sum(array_map('count', $endpoints)),
                'endpoints' => $endpoints
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'docs':
            // Documentation links
            echo json_encode([
                'success' => true,
                'documentation' => [
                    'README' => [
                        'file' => 'README_BACKEND.md',
                        'description' => 'Backend overview and quick start'
                    ],
                    'Quick Summary' => [
                        'file' => 'SUMMARY_20_FILES.txt',
                        'description' => 'Quick reference of all 20 files'
                    ],
                    'Full Documentation' => [
                        'file' => 'DOKUMENTASI_20_FILES.md',
                        'description' => 'Complete API documentation with all methods'
                    ],
                    'Developer Guide' => [
                        'file' => 'DEVELOPER_GUIDE.md',
                        'description' => 'Implementation guide with examples and best practices'
                    ],
                    'Implementation Checklist' => [
                        'file' => 'IMPLEMENTATION_CHECKLIST.md',
                        'description' => 'Phase-by-phase development plan and checklist'
                    ]
                ],
                'recommended_reading_order' => [
                    '1. README_BACKEND.md - Overview',
                    '2. SUMMARY_20_FILES.txt - Quick reference',
                    '3. DEVELOPER_GUIDE.md - Examples',
                    '4. DOKUMENTASI_20_FILES.md - Full details',
                    '5. IMPLEMENTATION_CHECKLIST.md - Development plan'
                ]
            ], JSON_PRETTY_PRINT);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Unknown action',
                'available_actions' => [
                    'status' => 'System status check',
                    'test' => 'Run quick tests',
                    'endpoints' => 'List all endpoints',
                    'docs' => 'Documentation links'
                ],
                'usage' => [
                    'GET /index.php?action=status' => 'Check system status',
                    'GET /index.php?action=test' => 'Run tests',
                    'GET /index.php?action=endpoints' => 'List endpoints',
                    'GET /index.php?action=docs' => 'Show documentation'
                ]
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
