<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/db.php';

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get date range parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Default to first day of current month
    $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Default to today
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception("Invalid date format. Use YYYY-MM-DD");
    }

    // Query to get promo code performance data
    $query = "
        SELECT 
            pc.id as discount_id,
            pc.code as promo_code,
            pc.description as promo_name,
            pc.discount_type,
            pc.discount_value,
            pc.min_cart_total as minimum_amount,
            NULL as maximum_discount,
            pc.max_uses as usage_limit,
            COALESCE(usage_stats.used_count, 0) as used_count,
            COALESCE(usage_stats.orders_count, 0) as orders_count,
            COALESCE(usage_stats.total_discount_amount, 0) as total_discount_amount,
            COALESCE(usage_stats.total_order_value, 0) as total_order_value,
            COALESCE(usage_stats.avg_discount_per_order, 0) as avg_discount_per_order,
            usage_stats.first_used,
            usage_stats.last_used
        FROM promocodes pc
        LEFT JOIN (
            SELECT 
                cpc.promocode_id,
                COUNT(DISTINCT cpc.id) as orders_count,
                COUNT(DISTINCT cpc.id) as used_count,
                SUM(CASE 
                    WHEN pc.discount_type = 'percentage' THEN (c.total * pc.discount_value / 100)
                    WHEN pc.discount_type = 'fixed' THEN pc.discount_value
                    ELSE 0
                END) as total_discount_amount,
                SUM(c.total) as total_order_value,
                AVG(CASE 
                    WHEN pc.discount_type = 'percentage' THEN (c.total * pc.discount_value / 100)
                    WHEN pc.discount_type = 'fixed' THEN pc.discount_value
                    ELSE 0
                END) as avg_discount_per_order,
                MIN(cpc.applied_at) as first_used,
                MAX(cpc.applied_at) as last_used
            FROM cart_promocodes cpc
            LEFT JOIN promocodes pc ON cpc.promocode_id = pc.id
            LEFT JOIN cart c ON cpc.cart_id = c.id
            WHERE cpc.applied_at >= ? 
                AND cpc.applied_at <= ?
            GROUP BY cpc.promocode_id
        ) usage_stats ON pc.id = usage_stats.promocode_id
        WHERE pc.is_active = 1
        ORDER BY orders_count DESC, total_discount_amount DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $promoData = [];
    $summary = [
        'total_promo_codes' => 0,
        'active_promo_codes' => 0,
        'total_orders' => 0,
        'total_discount_amount' => 0,
        'total_order_value' => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $promoData[] = [
            'promo_code' => $row['promo_code'],
            'promo_name' => $row['promo_name'],
            'discount_type' => $row['discount_type'],
            'discount_value' => $row['discount_value'],
            'minimum_amount' => $row['minimum_amount'],
            'maximum_discount' => $row['maximum_discount'],
            'usage_limit' => $row['usage_limit'],
            'used_count' => (int)$row['used_count'],
            'orders_count' => (int)$row['orders_count'],
            'total_discount_amount' => (float)$row['total_discount_amount'],
            'total_order_value' => (float)$row['total_order_value'],
            'avg_discount_per_order' => (float)$row['avg_discount_per_order'],
            'first_used' => $row['first_used'],
            'last_used' => $row['last_used'],
            'usage_percentage' => $row['usage_limit'] > 0 ? round(($row['used_count'] / $row['usage_limit']) * 100, 2) : 0,
            'discount_percentage' => $row['total_order_value'] > 0 ? round(($row['total_discount_amount'] / $row['total_order_value']) * 100, 2) : 0
        ];

        // Update summary
        $summary['total_promo_codes']++;
        if ($row['orders_count'] > 0) {
            $summary['active_promo_codes']++;
        }
        $summary['total_orders'] += (int)$row['orders_count'];
        $summary['total_discount_amount'] += (float)$row['total_discount_amount'];
        $summary['total_order_value'] += (float)$row['total_order_value'];
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'data' => $promoData,
        'summary' => $summary,
        'date_range' => [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
