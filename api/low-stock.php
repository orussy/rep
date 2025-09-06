<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once '../config/db.php';

try {
    // Get threshold from query parameter, default to 10
    $threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 10;
    
    // Query to get low stock items with supplier information
    $query = "
        SELECT 
            p.id as product_id,
            p.name as product_name,
            ps.sku,
            ps.quantity as current_stock,
            COALESCE(sc.name, CONCAT('Category ', p.category_id)) as category_name,
            GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') as suppliers
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        LEFT JOIN sub_categories sc ON p.category_id = sc.id
        LEFT JOIN product_suppliers ps_rel ON p.id = ps_rel.product_id
        LEFT JOIN suppliers s ON ps_rel.supplier_id = s.id
        WHERE p.deleted_at IS NULL 
        AND ps.deleted_at IS NULL
        AND ps.quantity <= $threshold
        GROUP BY p.id, ps.id, ps.sku, ps.quantity, sc.name, p.category_id
        ORDER BY ps.quantity ASC, p.name
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $lowStockData = [];
    while ($row = $result->fetch_assoc()) {
        $lowStockData[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $lowStockData,
        'message' => 'Low stock data retrieved successfully',
        'threshold' => $threshold
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
