<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once '../config/db.php';

try {
    // Check if connection exists
    if (!$conn) {
        throw new Exception("Database connection not established");
    }
    
    // Get parameters from query string
    $categoryId = $_GET['category_id'] ?? '';
    $warehouse = $_GET['warehouse'] ?? '';
    $stockStatus = $_GET['stock_status'] ?? '';
    
    // First, let's check if tables exist and have data
    $checkQuery = "SELECT COUNT(*) as count FROM products WHERE deleted_at IS NULL";
    $checkResult = $conn->query($checkQuery);
    $productCount = $checkResult->fetch_assoc()['count'];
    
    $checkQuery2 = "SELECT COUNT(*) as count FROM product_skus WHERE deleted_at IS NULL";
    $checkResult2 = $conn->query($checkQuery2);
    $skuCount = $checkResult2->fetch_assoc()['count'];
    
    $checkQuery3 = "SELECT COUNT(*) as count FROM categories WHERE deleted_at IS NULL";
    $checkResult3 = $conn->query($checkQuery3);
    $categoryCount = $checkResult3->fetch_assoc()['count'];
    
    // Query to get stock on hand data with real category names and reserved stock
    $query = "
        SELECT 
            p.id as product_id,
            p.name as product_name,
            ps.sku,
            COALESCE(sc.name, CONCAT('Category ', p.category_id)) as category_name,
            ps.quantity as current_stock,
            COALESCE(ci_reserved.reserved_quantity, 0) as reserved_stock,
            (ps.quantity - COALESCE(ci_reserved.reserved_quantity, 0)) as available_stock,
            'Main Warehouse' as warehouse,
            ps.created_at as last_updated,
            p.category_id as category_id
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        LEFT JOIN sub_categories sc ON p.category_id = sc.id
        LEFT JOIN (
            SELECT 
                product_sku_id,
                SUM(quantity) as reserved_quantity
            FROM cart_item
            GROUP BY product_sku_id
        ) ci_reserved ON ps.id = ci_reserved.product_sku_id
        WHERE p.deleted_at IS NULL 
        AND ps.deleted_at IS NULL
    ";
    
    // Add category filter if specified
    if (!empty($categoryId)) {
        $categoryId = (int)$categoryId;
        $query .= " AND p.category_id = $categoryId";
    }
    
    // Add warehouse filter if specified (for now just check warehouse name)
    if (!empty($warehouse)) {
        $warehouse = $conn->real_escape_string($warehouse);
        $query .= " AND 'Main Warehouse' = '$warehouse'";
    }
    
    // Add stock status filter
    if (!empty($stockStatus)) {
        switch ($stockStatus) {
            case 'low':
                $query .= " AND ps.quantity <= 10";
                break;
            case 'medium':
                $query .= " AND ps.quantity > 10 AND ps.quantity <= 50";
                break;
            case 'high':
                $query .= " AND ps.quantity > 50";
                break;
            case 'out':
                $query .= " AND ps.quantity = 0";
                break;
        }
    }
    
    $query .= " ORDER BY p.name, ps.sku";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $stockData = [];
    while ($row = $result->fetch_assoc()) {
        // Reserved stock is now calculated in the SQL query
        $stockData[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $stockData,
        'debug_info' => [
            'products_count' => $productCount,
            'skus_count' => $skuCount,
            'categories_count' => $categoryCount,
            'result_count' => count($stockData),
            'timestamp' => date('Y-m-d H:i:s'),
            'reserved_test' => 'API updated with reserved stock calculation'
        ],
        'message' => 'Stock on hand data retrieved successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
