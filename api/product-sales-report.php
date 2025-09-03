<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

    if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        throw new Exception('Invalid start_date. Use YYYY-MM-DD');
    }
    if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception('Invalid end_date. Use YYYY-MM-DD');
    }

    // Defaults: include all data if no dates specified
    $where = [];
    $params = [];
    $types = '';

    if ($startDate) {
        $where[] = "DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) >= ?";
        $params[] = $startDate;
        $types .= 's';
    }
    if ($endDate) {
        $where[] = "DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) <= ?";
        $params[] = $endDate;
        $types .= 's';
    }
    if ($categoryId) {
        $where[] = "p.category_id = ?";
        $params[] = $categoryId;
        $types .= 'i';
    }

    $whereSql = '';
    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    // Build query to aggregate product sales
    // Columns: Product ID, Product Name, Category, Total Orders, Units Sold, Total Revenue, Avg. Selling Price, Returns (canceled orders), Last Sold Date
    $query = "
        SELECT
            p.id AS product_id,
            p.name AS product_name,
            COALESCE(c.name, sc.name) AS category_name,
            COUNT(DISTINCT CASE WHEN o.status <> 'canceled' THEN o.id END) AS total_orders,
            COALESCE(SUM(oi.quantity), 0) AS units_sold,
            COALESCE(SUM(oi.quantity * COALESCE(ps.price, 0)), 0) AS total_revenue,
            CASE WHEN COALESCE(SUM(oi.quantity), 0) > 0
                 THEN ROUND(COALESCE(SUM(oi.quantity * COALESCE(ps.price, 0)), 0) / SUM(oi.quantity), 2)
                 ELSE 0 END AS avg_selling_price,
            0 AS returns_count,
            MAX(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) AS last_sold_date
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN sub_categories sc ON p.category_id = sc.id
        LEFT JOIN order_item oi ON oi.product_id = p.id
        LEFT JOIN order_details o ON oi.order_id = o.id AND o.status <> 'canceled'
        LEFT JOIN product_skus ps ON oi.product_sku_id = ps.id
        $whereSql
        GROUP BY p.id, p.name, category_name
        ORDER BY total_revenue DESC
    ";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    $productIds = [];
    while ($row = $result->fetch_assoc()) {
        $productIds[] = (int)$row['product_id'];
        $rows[] = [
            'product_id' => (int)$row['product_id'],
            'product_name' => $row['product_name'],
            'category' => $row['category_name'],
            'total_orders' => (int)$row['total_orders'],
            'units_sold' => (int)$row['units_sold'],
            'total_revenue' => round((float)$row['total_revenue'], 2),
            'avg_selling_price' => round((float)$row['avg_selling_price'], 2),
            'returns' => 0,
            'last_sold_date' => $row['last_sold_date'] ? date('Y-m-d H:i:s', strtotime($row['last_sold_date'])) : null,
        ];
    }

    // Compute returns (canceled orders) per product within the same filters
    if (!empty($productIds)) {
        $inPlaceholders = implode(',', array_fill(0, count($productIds), '?'));
        $retTypes = str_repeat('i', count($productIds));
        $retParams = $productIds;
        $retWhere = [];
        $retWhere[] = "o.status = 'canceled'";
        if ($startDate) { $retWhere[] = "DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) >= ?"; $retParams[] = $startDate; $retTypes .= 's'; }
        if ($endDate) { $retWhere[] = "DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) <= ?"; $retParams[] = $endDate; $retTypes .= 's'; }
        if ($categoryId) { $retWhere[] = "p.category_id = ?"; $retParams[] = $categoryId; $retTypes .= 'i'; }
        $retWhereSql = 'WHERE ' . implode(' AND ', $retWhere) . " AND oi.product_id IN ($inPlaceholders)";

        $retQuery = "SELECT oi.product_id, COUNT(DISTINCT o.id) AS canceled_orders
                     FROM order_item oi
                     JOIN order_details o ON oi.order_id = o.id
                     JOIN products p ON oi.product_id = p.id
                     $retWhereSql
                     GROUP BY oi.product_id";
        $retStmt = $conn->prepare($retQuery);
        $retStmt->bind_param($retTypes, ...$retParams);
        $retStmt->execute();
        $retRes = $retStmt->get_result();
        $productIdToReturns = [];
        while ($r = $retRes->fetch_assoc()) {
            $productIdToReturns[(int)$r['product_id']] = (int)$r['canceled_orders'];
        }
        // Merge into rows
        foreach ($rows as &$rrow) {
            $pid = $rrow['product_id'];
            if (isset($productIdToReturns[$pid])) {
                $rrow['returns'] = $productIdToReturns[$pid];
            }
        }
        unset($rrow);
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($rows),
        'data' => $rows
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to generate product sales report: ' . $e->getMessage()
    ]);
}

$conn->close();
?>


