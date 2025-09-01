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
    $allowedSortFields = ['id', 'f_name', 'l_name', 'email', 'phone_no', 'status', 'created_at', 'total_orders', 'total_spent', 'last_order_date'];
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
        $whereConditions[] = "(u.id LIKE ? OR u.f_name LIKE ? OR u.l_name LIKE ? OR u.email LIKE ? OR u.phone_no LIKE ? OR CONCAT(u.f_name, ' ', u.l_name) LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $paramTypes .= 'ssssss';
    }
    
    if (!empty($startDate)) {
        $whereConditions[] = "DATE(u.created_at) >= ?";
        $params[] = $startDate;
        $paramTypes .= 's';
    }
    
    if (!empty($endDate)) {
        $whereConditions[] = "DATE(u.created_at) <= ?";
        $params[] = $endDate;
        $paramTypes .= 's';
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Add condition to exclude admin users
    if (!empty($whereConditions)) {
        $whereConditions[] = "(u.role != 'admin' OR u.role IS NULL)";
    } else {
        $whereConditions[] = "(u.role != 'admin' OR u.role IS NULL)";
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Fetch all customers with their order statistics (excluding admin users)
    $query = "SELECT 
                u.id,
                u.f_name,
                u.l_name,
                u.email,
                u.phone_no,
                u.status,
                u.created_at,
                COUNT(o.id) as total_orders,
                COALESCE(SUM(o.total), 0) as total_spent,
                MAX(o.created_at) as last_order_date
              FROM users u
              LEFT JOIN order_details o ON u.id = o.user_id
              $whereClause
              GROUP BY u.id, u.f_name, u.l_name, u.email, u.phone_no, u.status, u.created_at
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
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        // Format the dates for better display
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        
        // Format last order date
        if ($row['last_order_date']) {
            $row['last_order_date'] = date('Y-m-d H:i:s', strtotime($row['last_order_date']));
        } else {
            $row['last_order_date'] = 'No orders';
        }
        
        // Format total spent as currency
        $row['total_spent'] = number_format($row['total_spent'], 2);
        
        // Set default status if null
        if (empty($row['status'])) {
            $row['status'] = 'active';
        }
        
        $customers[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $customers,
        'count' => count($customers)
    ]);
    
} catch (Exception $e) {
    error_log("Customers API Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch customers: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
