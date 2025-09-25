<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    // Get date parameters
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    
    // Validate date format
    if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        throw new Exception('Invalid start_date format. Use YYYY-MM-DD');
    }
    if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception('Invalid end_date format. Use YYYY-MM-DD');
    }
    
    // Build date filter
    $dateFilter = '';
    $params = [];
    $paramTypes = '';
    
    if ($startDate && $endDate) {
        $dateFilter = "DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        $paramTypes = 'ss';
    } elseif ($startDate) {
        $dateFilter = "DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) >= ?";
        $params = [$startDate];
        $paramTypes = 's';
    } elseif ($endDate) {
        $dateFilter = "DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) <= ?";
        $params = [$endDate];
        $paramTypes = 's';
    } else {
        // If no dates provided, use all completed orders
        $dateFilter = "1=1";
    }
    
    // Query to get sales summary with discounts and tax calculations
    $summaryQuery = "SELECT 
                        COUNT(o.id) as orders_count,
                        SUM(order_totals.gross_sales) as gross_sales,
                        SUM(
                            COALESCE(product_discounts.total_product_discounts, 0) +
                            COALESCE(promocode_discounts.total_promocode_discounts, 0)
                        ) as discount_amount,
                        SUM(order_totals.gross_sales) - 
                        SUM(
                            COALESCE(product_discounts.total_product_discounts, 0) +
                            COALESCE(promocode_discounts.total_promocode_discounts, 0)
                        ) as net_sales,
                        (SUM(order_totals.gross_sales) - 
                         SUM(
                             COALESCE(product_discounts.total_product_discounts, 0) +
                             COALESCE(promocode_discounts.total_promocode_discounts, 0)
                         )) * 
                        (1 + AVG(COALESCE(o.tax_rate, 0)) / 100.0) as net_sales_with_tax
                    FROM order_details o
                    INNER JOIN payment_details p ON p.order_id = o.id AND p.status = 'completed'
                    LEFT JOIN (
                        SELECT order_id, SUM(ps.price * oi.quantity) as gross_sales
                        FROM order_item oi
                        LEFT JOIN product_skus ps ON ps.id = oi.product_sku_id
                        GROUP BY order_id
                    ) order_totals ON order_totals.order_id = o.id
                    LEFT JOIN (
                        -- Product discounts
                        SELECT 
                            oi2.order_id,
                            SUM(
                                CASE 
                                    WHEN d.discount_type = 'percentage' THEN (ps2.price * oi2.quantity) * (d.discount_value / 100.0)
                                    WHEN d.discount_type = 'fixed' THEN d.discount_value * oi2.quantity
                                    ELSE 0
                                END
                            ) as total_product_discounts
                        FROM order_item oi2
                        LEFT JOIN product_skus ps2 ON ps2.id = oi2.product_sku_id
                        LEFT JOIN order_details od2 ON od2.id = oi2.order_id
                        LEFT JOIN discounts d ON d.product_id = oi2.product_id 
                            AND d.is_active = 1
                            AND (d.start_date IS NULL OR d.start_date <= DATE(od2.created_at))
                            AND (d.end_date IS NULL OR d.end_date >= DATE(od2.created_at))
                        GROUP BY oi2.order_id
                    ) product_discounts ON product_discounts.order_id = o.id
                    LEFT JOIN (
                        -- Promocode discounts
                        SELECT 
                            o3.id as order_id,
                            SUM(
                                CASE 
                                    WHEN pc.discount_type = 'percentage' AND cart_subtotal >= COALESCE(pc.min_cart_total, 0)
                                        THEN (pc.discount_value / 100.0) * cart_subtotal
                                    WHEN pc.discount_type = 'fixed' AND cart_subtotal >= COALESCE(pc.min_cart_total, 0)
                                        THEN pc.discount_value
                                    ELSE 0
                                END
                            ) as total_promocode_discounts
                        FROM order_details o3
                        LEFT JOIN cart_promocodes cpc ON cpc.cart_id = o3.cart_id
                        LEFT JOIN promocodes pc ON pc.id = cpc.promocode_id
                            AND pc.is_active = 1
                            AND (pc.start_date IS NULL OR pc.start_date <= DATE(o3.created_at))
                            AND (pc.end_date IS NULL OR pc.end_date >= DATE(o3.created_at))
                        LEFT JOIN (
                            SELECT order_id, SUM(ps3.price * oi3.quantity) AS cart_subtotal
                            FROM order_item oi3
                            LEFT JOIN product_skus ps3 ON ps3.id = oi3.product_sku_id
                            GROUP BY order_id
                        ) cs ON cs.order_id = o3.id
                        GROUP BY o3.id
                    ) promocode_discounts ON promocode_discounts.order_id = o.id
                    WHERE o.status IN ('completed','shipped','delivered') AND " . $dateFilter;
    
    $stmt = $conn->prepare($summaryQuery);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc();
    
    // Format the response data
    $response = [
        'status' => 'success',
        'data' => [
            'orders_count' => (int)($summary['orders_count'] ?? 0),
            'gross_sales' => round((float)($summary['gross_sales'] ?? 0), 2),
            'discount_amount' => round((float)($summary['discount_amount'] ?? 0), 2),
            'net_sales' => round((float)($summary['net_sales'] ?? 0), 2),
            'net_sales_with_tax' => round((float)($summary['net_sales_with_tax'] ?? 0), 2),
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch sales summary: ' . $e->getMessage()
    ]);
}

$conn->close();
?>