<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$dbname = 'store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
        // Get customer basic information
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.f_name,
                c.l_name,
                c.email,
                c.phone_no,
                c.status,
                c.created_at,
                c.birthdate as dob,
                c.gender
            FROM users c
            WHERE c.id = ?
        ");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Customer not found'
            ]);
            exit;
        }
        
        // Get customer order statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_spent,
                COALESCE(AVG(total), 0) as avg_order_value,
                MAX(created_at) as last_order_date
            FROM order_details 
            WHERE user_id = ? AND status != 'canceled'
        ");
        $stmt->execute([$customerId]);
        $orderStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate days since last order
        $daysSinceLastOrder = null;
        if ($orderStats['last_order_date']) {
            $lastOrderDate = new DateTime($orderStats['last_order_date']);
            $now = new DateTime();
            $daysSinceLastOrder = $now->diff($lastOrderDate)->days;
        }
        
        // Combine customer data with order statistics
        $customerData = array_merge($customer, [
            'total_orders' => (int)$orderStats['total_orders'],
            'total_spent' => number_format($orderStats['total_spent'], 2),
            'avg_order_value' => number_format($orderStats['avg_order_value'], 2),
            'last_order_date' => $orderStats['last_order_date'] ?: 'No orders',
            'days_since_last_order' => $daysSinceLastOrder
        ]);
        
        echo json_encode([
            'status' => 'success',
            'data' => $customerData
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
