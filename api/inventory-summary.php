<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once '../config/db.php';

try {
    // Get total products count
    $totalProductsQuery = "
        SELECT COUNT(DISTINCT p.id) as total_products
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        WHERE p.deleted_at IS NULL AND ps.deleted_at IS NULL
    ";
    $result = $conn->query($totalProductsQuery);
    $totalProducts = $result->fetch_assoc()['total_products'];
    
    // Get low stock items count (threshold = 10)
    $lowStockQuery = "
        SELECT COUNT(*) as low_stock_items
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        WHERE p.deleted_at IS NULL 
        AND ps.deleted_at IS NULL
        AND ps.quantity <= 10
    ";
    $result = $conn->query($lowStockQuery);
    $lowStockItems = $result->fetch_assoc()['low_stock_items'];
    
    // Get out of stock items count
    $outOfStockQuery = "
        SELECT COUNT(*) as out_of_stock_items
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        WHERE p.deleted_at IS NULL 
        AND ps.deleted_at IS NULL
        AND ps.quantity = 0
    ";
    $result = $conn->query($outOfStockQuery);
    $outOfStockItems = $result->fetch_assoc()['out_of_stock_items'];
    
    // Get total inventory value
    $totalValueQuery = "
        SELECT SUM(ps.quantity * ps.price) as total_inventory_value
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        WHERE p.deleted_at IS NULL AND ps.deleted_at IS NULL
    ";
    $result = $conn->query($totalValueQuery);
    $totalInventoryValue = $result->fetch_assoc()['total_inventory_value'] ?? 0;
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'totalProducts' => (int)$totalProducts,
            'lowStockItems' => (int)$lowStockItems,
            'outOfStockItems' => (int)$outOfStockItems,
            'totalInventoryValue' => (float)$totalInventoryValue
        ],
        'message' => 'Inventory summary data retrieved successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
