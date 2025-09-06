<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once '../config/db.php';

try {
    // Get parameters from query string
    $categoryId = $_GET['category_id'] ?? '';
    $sortBy = $_GET['sort_by'] ?? 'value_desc';
    
    // Build the query
    $query = "
        SELECT 
            p.id as product_id,
            p.name as product_name,
            ps.sku,
            ps.quantity,
            ps.price as cost_price,
            (ps.quantity * ps.price) as inventory_value,
            COALESCE(sc.name, CONCAT('Category ', p.category_id)) as category_name,
            p.category_id as category_id
        FROM products p
        INNER JOIN product_skus ps ON p.id = ps.product_id
        LEFT JOIN sub_categories sc ON p.category_id = sc.id
        WHERE p.deleted_at IS NULL 
        AND ps.deleted_at IS NULL
    ";
    
    // Add category filter if specified
    if (!empty($categoryId)) {
        $categoryId = (int)$categoryId;
        $query .= " AND p.category_id = $categoryId";
    }
    
    // Add sorting
    switch ($sortBy) {
        case 'value_desc':
            $query .= " ORDER BY inventory_value DESC, p.name";
            break;
        case 'value_asc':
            $query .= " ORDER BY inventory_value ASC, p.name";
            break;
        case 'quantity_desc':
            $query .= " ORDER BY ps.quantity DESC, p.name";
            break;
        case 'quantity_asc':
            $query .= " ORDER BY ps.quantity ASC, p.name";
            break;
        case 'product_name':
            $query .= " ORDER BY p.name, ps.sku";
            break;
        default:
            $query .= " ORDER BY inventory_value DESC, p.name";
    }
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $valuationData = [];
    $totalValue = 0;
    while ($row = $result->fetch_assoc()) {
        $valuationData[] = $row;
        $totalValue += $row['inventory_value'];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $valuationData,
        'message' => 'Inventory valuation data retrieved successfully',
        'total_value' => $totalValue,
        'filters' => [
            'category_id' => $categoryId,
            'sort_by' => $sortBy
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
