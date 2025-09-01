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
        // Get customer activity log based on orders and other actions
        $stmt = $pdo->prepare("
            SELECT 
                id,
                'order' as activity_type,
                CONCAT('Order #', id, ' - ', status) as description,
                created_at,
                'N/A' as metadata
            FROM order_details
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$customerId]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no activities found, return empty array
        if (empty($activities)) {
            echo json_encode([
                'status' => 'success',
                'data' => []
            ]);
            exit;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $activities
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
