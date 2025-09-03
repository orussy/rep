<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    // Get search parameters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
    $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
    
    // Validate sort parameters
    $allowedSortFields = ['id', 'user_id', 'total', 'status', 'created_at', 'f_name', 'l_name', 'discount_amount', 'provider'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'created_at';
    }
    if (!in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
        $sortOrder = 'DESC';
    }
    
    // Build WHERE clause
    $whereConditions = [];
    $params = [];
    $paramTypes = '';
    
    if (!empty($search)) {
        $whereConditions[] = "(o.id LIKE ? OR u.f_name LIKE ? OR u.l_name LIKE ? OR CONCAT(u.f_name, ' ', u.l_name) LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $paramTypes .= 'ssss';
    }
    
    if (!empty($startDate)) {
        $whereConditions[] = "DATE(o.created_at) >= ?";
        $params[] = $startDate;
        $paramTypes .= 's';
    }
    
    if (!empty($endDate)) {
        $whereConditions[] = "DATE(o.created_at) <= ?";
        $params[] = $endDate;
        $paramTypes .= 's';
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Fetch all orders with their details and user information
    $query = "SELECT 
                o.id,
                o.user_id,
                o.total,
                o.status,
                o.tax_amount,
                o.tax_rate,
                o.created_at,
                o.updated_at,
                u.f_name,
                u.l_name,
                (
                  SELECT p.provider FROM payment_details p 
                  WHERE p.order_id = o.id 
                  ORDER BY p.created_at DESC LIMIT 1
                ) as provider,
                (
                  SELECT 
                    COALESCE(SUM(
                      CASE 
                        WHEN d.discount_type = 'percentage' THEN (ps.price * oi.quantity) * (d.discount_value/100)
                        WHEN d.discount_type = 'fixed' THEN (d.discount_value * oi.quantity)
                        ELSE 0
                      END
                    ), 0)
                  FROM order_item oi
                  LEFT JOIN product_skus ps ON oi.product_sku_id = ps.id
                  LEFT JOIN discounts d ON d.product_id = oi.product_id 
                    AND d.is_active = 1
                    AND (d.start_date IS NULL OR d.start_date <= DATE(o.created_at))
                    AND (d.end_date IS NULL OR d.end_date >= DATE(o.created_at))
                  WHERE oi.order_id = o.id
                ) AS discount_amount,
                GROUP_CONCAT(
                    CONCAT(
                        p.name, ' (Qty: ', oi.quantity, ')'
                    ) SEPARATOR '; '
                ) as order_items,
                COUNT(oi.id) as total_items
              FROM order_details o
              LEFT JOIN users u ON o.user_id = u.id
              LEFT JOIN order_item oi ON o.id = oi.order_id
              LEFT JOIN products p ON oi.product_id = p.id
              $whereClause
              GROUP BY o.id, o.user_id, o.total, o.status, o.tax_amount, o.tax_rate, o.created_at, o.updated_at, u.f_name, u.l_name
              ORDER BY $sortBy $sortOrder";
    
    // Prepare and execute the query with parameters
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        // Format the dates for better display
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $row['updated_at'] = $row['updated_at'] ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : null;
        
        // Format total as currency
        $row['total'] = number_format($row['total'], 2);
        // Format discount amount as currency
        if (isset($row['discount_amount'])) {
            $row['discount_amount'] = number_format((float)$row['discount_amount'], 2);
        } else {
            $row['discount_amount'] = number_format(0, 2);
        }
        
        $orders[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $orders,
        'count' => count($orders)
    ]);
    
} catch (Exception $e) {
    error_log("Orders API Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch orders: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
