<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection using shared config
require_once '../config/db.php';
try {
    $dsn = 'mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $customerId = $_GET['id'] ?? null;
    
    if (!$customerId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Customer ID is required'
        ]);
        exit;
    }
    
    try {
        // Get customer orders with item count and latest payment provider
        $stmt = $pdo->prepare("
            SELECT 
                o.id,
                o.created_at,
                o.status,
                o.total as total_amount,
                (
                    SELECT p.provider FROM payment_details p
                    WHERE p.order_id = o.id
                    ORDER BY p.created_at DESC
                    LIMIT 1
                ) as payment_method,
                COUNT(oi.id) as items_count
            FROM order_details o
            LEFT JOIN order_item oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$customerId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $orders
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}
?>
