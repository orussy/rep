<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON input'
        ]);
        exit;
    }
    
    $customerId = $input['customer_id'] ?? null;
    $newStatus = $input['status'] ?? null;
    
    if (!$customerId || !$newStatus) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Customer ID and status are required'
        ]);
        exit;
    }
    
    // Validate status
    $validStatuses = ['active', 'inactive', 'pending', 'suspended'];
    if (!in_array(strtolower($newStatus), $validStatuses)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)
        ]);
        exit;
    }
    
    try {
        // Update customer status
        $stmt = $pdo->prepare("
            UPDATE users 
            SET status = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([$newStatus, $customerId]);
        
        if ($result && $stmt->rowCount() > 0) {
            // Log the activity (we'll skip this for now since there's no activity log table)
            // The status update succeeded
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Customer status updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Customer not found or no changes made'
            ]);
        }
        
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
