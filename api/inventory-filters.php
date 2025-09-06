<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once '../config/db.php';

try {
    // Get categories for filters with real category names
    $categoriesQuery = "
        SELECT DISTINCT p.category_id as id, COALESCE(sc.name, CONCAT('Category ', p.category_id)) as name
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        LEFT JOIN sub_categories sc ON p.category_id = sc.id
        WHERE p.deleted_at IS NULL 
        AND ps.deleted_at IS NULL
        ORDER BY sc.name, p.category_id
    ";
    $result = $conn->query($categoriesQuery);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    // Get products for movement report filter
    $productsQuery = "
        SELECT DISTINCT p.id, p.name
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        WHERE p.deleted_at IS NULL AND ps.deleted_at IS NULL
        ORDER BY p.name
    ";
    $result = $conn->query($productsQuery);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    // Get warehouses (for now, just return a default warehouse)
    $warehouses = [
        ['id' => 'main', 'name' => 'Main Warehouse'],
        ['id' => 'secondary', 'name' => 'Secondary Warehouse']
    ];
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'categories' => $categories,
            'products' => $products,
            'warehouses' => $warehouses
        ],
        'message' => 'Filter data retrieved successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
