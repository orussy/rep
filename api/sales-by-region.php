<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    $whereConditions = [];
    $params = [];
    $paramTypes = '';

    if (!empty($startDate)) {
        $whereConditions[] = 'DATE(o.created_at) >= ?';
        $params[] = $startDate;
        $paramTypes .= 's';
    }
    if (!empty($endDate)) {
        $whereConditions[] = 'DATE(o.created_at) <= ?';
        $params[] = $endDate;
        $paramTypes .= 's';
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    // Group sales by city/country using latest address per user (or any non-deleted), aggregate coordinates
    $sql = "SELECT 
                COALESCE(a.city, 'Unknown') AS city,
                COALESCE(a.country, 'Unknown') AS country,
                COUNT(DISTINCT o.id) AS orders_count,
                AVG(NULLIF(a.latitude, 0)) AS lat,
                AVG(NULLIF(a.longitude, 0)) AS lng,
                GROUP_CONCAT(DISTINCT CONCAT('#', o.id, ' - ', u.f_name, ' ', u.l_name) SEPARATOR ' | ') AS orders_customers
            FROM order_details o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN addresses a ON a.user_id = o.user_id AND a.deleted_at IS NULL
            $whereClause
            GROUP BY city, country";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        // Use numeric lat/lng if exist; skip rows without coordinates
        $lat = isset($row['lat']) ? (float)$row['lat'] : null;
        $lng = isset($row['lng']) ? (float)$row['lng'] : null;
        if ($lat === 0.0 && $lng === 0.0) {
            $lat = null; $lng = null;
        }
        $rows[] = [
            'city' => $row['city'],
            'country' => $row['country'],
            'orders_count' => (int)$row['orders_count'],
            'lat' => $lat,
            'lng' => $lng,
            'orders_customers' => $row['orders_customers']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $rows,
        'count' => count($rows)
    ]);

} catch (Exception $e) {
    error_log('Sales by Region API Error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch sales by region: ' . $e->getMessage()
    ]);
}

$conn->close();
?>


