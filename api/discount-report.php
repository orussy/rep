<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        throw new Exception('Invalid start_date. Use YYYY-MM-DD');
    }
    if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception('Invalid end_date. Use YYYY-MM-DD');
    }

    // Build date filters
    $dateWhere = [];
    $params = [];
    $types = '';
    if ($startDate) { $dateWhere[] = "DATE(od.created_at) >= ?"; $params[] = $startDate; $types .= 's'; }
    if ($endDate)   { $dateWhere[] = "DATE(od.created_at) <= ?"; $params[] = $endDate;   $types .= 's'; }
    $dateFilterSql = '';
    if (!empty($dateWhere)) {
        $dateFilterSql = 'AND ' . implode(' AND ', $dateWhere);
    }

    // Query for discount report
    $sql = "
WITH
orders_in_range AS (
  SELECT od.id AS order_id, od.cart_id, od.user_id, od.created_at, od.status, od.tax_amount, od.tax_rate, od.total
  FROM order_details od
  WHERE od.status IN ('completed','delivered') $dateFilterSql
),
order_lines AS (
  SELECT oi.order_id, oi.product_id, oi.product_sku_id, oi.quantity AS qty,
         ps.price AS unit_price,
         (ps.price * oi.quantity) AS line_subtotal_before_discount,
         p.name AS product_name,
         sc.name AS category_name
  FROM order_item oi
  JOIN orders_in_range oir ON oir.order_id = oi.order_id
  JOIN product_skus ps     ON ps.id = oi.product_sku_id
  JOIN products p          ON p.id = oi.product_id
  LEFT JOIN sub_categories sc ON p.category_id = sc.id
),
-- Product discounts applied
product_discounts AS (
  SELECT 
    ol.order_id,
    ol.product_id,
    ol.product_name,
    ol.category_name,
    ol.line_subtotal_before_discount,
    d.discount_type,
    d.discount_value,
    CASE 
      WHEN d.discount_type = 'percentage' THEN (d.discount_value / 100.0) * ol.line_subtotal_before_discount
      WHEN d.discount_type = 'fixed' THEN d.discount_value * ol.qty
      ELSE 0
    END AS product_discount_amount
  FROM order_lines ol
  JOIN orders_in_range oir ON oir.order_id = ol.order_id
  LEFT JOIN discounts d ON d.product_id = ol.product_id 
    AND d.is_active = 1
    AND (d.start_date IS NULL OR d.start_date <= DATE(oir.created_at))
    AND (d.end_date IS NULL OR d.end_date >= DATE(oir.created_at))
),
-- Promocode discounts applied
promocode_discounts AS (
  SELECT 
    oir.order_id,
    oir.cart_id,
    p.id AS promocode_id,
    p.code AS promocode_code,
    p.description AS promocode_description,
    p.discount_type,
    p.discount_value,
    opbd.products_before_discount AS cart_subtotal,
    CASE 
      WHEN p.discount_type = 'percentage' AND opbd.products_before_discount >= COALESCE(p.min_cart_total, 0)
        THEN (p.discount_value / 100.0) * opbd.products_before_discount
      WHEN p.discount_type = 'fixed' AND opbd.products_before_discount >= COALESCE(p.min_cart_total, 0)
        THEN p.discount_value
      ELSE 0
    END AS promocode_discount_amount
  FROM orders_in_range oir
  LEFT JOIN cart_promocodes cp ON cp.cart_id = oir.cart_id
  LEFT JOIN promocodes p ON p.id = cp.promocode_id
    AND p.is_active = 1
    AND (p.start_date IS NULL OR p.start_date <= DATE(oir.created_at))
    AND (p.end_date IS NULL OR p.end_date >= DATE(oir.created_at))
  LEFT JOIN (
    SELECT order_id, SUM(line_subtotal_before_discount) AS products_before_discount
    FROM order_lines
    GROUP BY order_id
  ) opbd ON opbd.order_id = oir.order_id
),
-- Actual discounts from payment difference
actual_discounts AS (
  SELECT 
    oir.order_id,
    oir.total AS order_total,
    pd.amount AS payment_amount,
    CASE 
      WHEN pd.amount IS NOT NULL AND oir.total > pd.amount
        THEN oir.total - pd.amount
      ELSE 0
    END AS actual_discount_amount
  FROM orders_in_range oir
  LEFT JOIN payment_details pd ON pd.order_id = oir.order_id
),
-- Combined discount summary
discount_summary AS (
  SELECT 
    oir.order_id,
    oir.created_at,
    oir.total AS order_total,
    COALESCE(SUM(pd.product_discount_amount), 0) AS total_product_discounts,
    COALESCE(SUM(pcd.promocode_discount_amount), 0) AS total_promocode_discounts,
    COALESCE(ad.actual_discount_amount, 0) AS actual_discount_amount,
    -- Total discount = promocode discounts + actual payment difference (matching sales summary)
    COALESCE(SUM(pcd.promocode_discount_amount), 0) + COALESCE(ad.actual_discount_amount, 0) AS total_discount_amount,
    COUNT(DISTINCT pd.product_id) AS products_with_discounts,
    COUNT(DISTINCT pcd.promocode_id) AS promocodes_used
  FROM orders_in_range oir
  LEFT JOIN product_discounts pd ON pd.order_id = oir.order_id
  LEFT JOIN promocode_discounts pcd ON pcd.order_id = oir.order_id
  LEFT JOIN actual_discounts ad ON ad.order_id = oir.order_id
  GROUP BY oir.order_id, oir.created_at, oir.total, ad.actual_discount_amount
)
SELECT 
  order_id,
  DATE(created_at) AS order_date,
  order_total,
  total_product_discounts,
  total_promocode_discounts,
  actual_discount_amount,
  total_discount_amount,
  products_with_discounts,
  promocodes_used,
  CASE 
    WHEN order_total > 0 THEN ROUND((total_discount_amount / order_total) * 100, 2)
    ELSE 0
  END AS discount_percentage
FROM discount_summary
ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $conn->error);
    }
    $result = $stmt->get_result();

    $rows = [];
    $summary = [
        'total_orders' => 0,
        'total_product_discounts' => 0,
        'total_promocode_discounts' => 0,
        'total_actual_discounts' => 0,
        'total_discount_amount' => 0,
        'avg_discount_percentage' => 0,
        'orders_with_discounts' => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            'order_id' => (int)$row['order_id'],
            'order_date' => $row['order_date'],
            'order_total' => number_format((float)$row['order_total'], 2, '.', ''),
            'total_product_discounts' => number_format((float)$row['total_product_discounts'], 2, '.', ''),
            'total_promocode_discounts' => number_format((float)$row['total_promocode_discounts'], 2, '.', ''),
            'actual_discount_amount' => number_format((float)$row['actual_discount_amount'], 2, '.', ''),
            'total_discount_amount' => number_format((float)$row['total_discount_amount'], 2, '.', ''),
            'products_with_discounts' => (int)$row['products_with_discounts'],
            'promocodes_used' => (int)$row['promocodes_used'],
            'discount_percentage' => number_format((float)$row['discount_percentage'], 2, '.', '')
        ];

        // Update summary
        $summary['total_orders']++;
        $summary['total_product_discounts'] += (float)$row['total_product_discounts'];
        $summary['total_promocode_discounts'] += (float)$row['total_promocode_discounts'];
        $summary['total_actual_discounts'] += (float)$row['actual_discount_amount'];
        $summary['total_discount_amount'] += (float)$row['total_discount_amount'];
        $summary['orders_with_discounts']++;
    }

    // Calculate average discount percentage
    if ($summary['orders_with_discounts'] > 0) {
        $summary['avg_discount_percentage'] = number_format($summary['total_discount_amount'] / $summary['total_orders'], 2, '.', '');
    }

    // Format summary amounts
    $summary['total_product_discounts'] = number_format($summary['total_product_discounts'], 2, '.', '');
    $summary['total_promocode_discounts'] = number_format($summary['total_promocode_discounts'], 2, '.', '');
    $summary['total_actual_discounts'] = number_format($summary['total_actual_discounts'], 2, '.', '');
    $summary['total_discount_amount'] = number_format($summary['total_discount_amount'], 2, '.', '');

    echo json_encode([
        'status' => 'success',
        'data' => $rows,
        'summary' => $summary,
        'count' => count($rows)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to generate discount report: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
