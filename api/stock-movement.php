<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once '../config/db.php';

try {
    // Get parameters from query string
    $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    $movementType = $_GET['movement_type'] ?? '';
    $productId = $_GET['product_id'] ?? '';
    
    // Build the query with filters
    $query = "
        SELECT 
            sm.id,
            sm.created_at,
            p.name as product_name,
            ps.sku,
            sm.movement_type,
            sm.quantity,
            sm.reference,
            sm.reference_id,
            (SELECT COALESCE(SUM(
                CASE 
                    WHEN sm2.movement_type = 'IN' THEN sm2.quantity 
                    ELSE -sm2.quantity 
                END
            ), 0) 
            FROM stock_movements sm2 
            WHERE sm2.product_sku_id = sm.product_sku_id 
            AND sm2.created_at <= sm.created_at) as balance_after
        FROM stock_movements sm
        INNER JOIN product_skus ps ON sm.product_sku_id = ps.id
        INNER JOIN products p ON ps.product_id = p.id
        WHERE DATE(sm.created_at) BETWEEN '$dateFrom' AND '$dateTo'
    ";
    
    // Add movement type filter if specified
    if (!empty($movementType)) {
        $movementType = $conn->real_escape_string($movementType);
        $query .= " AND sm.movement_type = '$movementType'";
    }
    
    // Add product filter if specified
    if (!empty($productId)) {
        $productId = (int)$productId;
        $query .= " AND p.id = $productId";
    }
    
    $query .= " ORDER BY sm.created_at DESC, p.name";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $movementData = [];
    while ($row = $result->fetch_assoc()) {
        $movementData[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $movementData,
        'message' => 'Stock movement data retrieved successfully',
        'filters' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'movement_type' => $movementType,
            'product_id' => $productId
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
