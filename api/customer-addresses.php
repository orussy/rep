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
        // Get customer addresses
        $stmt = $pdo->prepare("
            SELECT 
                id,
                title as type,
                address1 as street_address,
                city,
                'N/A' as state,
                postal_code,
                country,
                'N/A' as is_default
            FROM addresses
            WHERE user_id = ? AND deleted_at IS NULL
            ORDER BY id ASC
        ");
        $stmt->execute([$customerId]);
        $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no addresses found, return empty array
        if (empty($addresses)) {
            echo json_encode([
                'status' => 'success',
                'data' => []
            ]);
            exit;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $addresses
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
