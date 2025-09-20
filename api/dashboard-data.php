<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    // Use the existing connection from db.php
    // The connection is already established in db.php
    // Get date from query parameter or use current date as default
    $requestedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $timePeriod = isset($_GET['period']) ? $_GET['period'] : 'day';
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }
    
    // Validate time period
    if (!in_array($timePeriod, ['day', 'week', 'month'])) {
        throw new Exception('Invalid time period. Use day, week, or month');
    }
    
    $currentDate = $requestedDate;
    
    // Set timezone to match your database timezone (UTC+3)
    date_default_timezone_set('UTC');
    
    // Create proper date boundaries based on time period
    $baseDate = new DateTime($currentDate);
    
    switch ($timePeriod) {
        case 'day':
    $startDateTime = $currentDate . ' 00:00:00';
    $endDateTime = $currentDate . ' 23:59:59';
            $dateFormat = 'H'; // Hour for daily view
            $dateGroupBy = 'HOUR(CONVERT_TZ(o.created_at, \'+00:00\', \'+03:00\'))';
            $dateFilter = 'DATE(CONVERT_TZ(o.created_at, \'+00:00\', \'+03:00\')) = ?';
            $paymentDateGroupBy = 'HOUR(CONVERT_TZ(p.created_at, \'+00:00\', \'+03:00\'))';
            $paymentDateFilter = 'DATE(CONVERT_TZ(p.created_at, \'+00:00\', \'+03:00\')) = ?';
            $orderDateFilter = 'DATE(CONVERT_TZ(created_at, \'+00:00\', \'+03:00\')) = ?';
            $dateParam = $currentDate;
            break;
            
        case 'week':
            // Get start of week (Monday) and end of week (Sunday)
            $startOfWeek = clone $baseDate;
            $startOfWeek->modify('monday this week');
            $endOfWeek = clone $startOfWeek;
            $endOfWeek->modify('+6 days');
            
            $startDateTime = $startOfWeek->format('Y-m-d') . ' 00:00:00';
            $endDateTime = $endOfWeek->format('Y-m-d') . ' 23:59:59';
            $dateFormat = 'Y-m-d'; // Date for weekly view
            $dateGroupBy = 'DATE(CONVERT_TZ(o.created_at, \'+00:00\', \'+03:00\'))';
            $dateFilter = 'DATE(CONVERT_TZ(o.created_at, \'+00:00\', \'+03:00\')) BETWEEN ? AND ?';
            $paymentDateGroupBy = 'DATE(CONVERT_TZ(p.created_at, \'+00:00\', \'+03:00\'))';
            $paymentDateFilter = 'DATE(CONVERT_TZ(p.created_at, \'+00:00\', \'+03:00\')) BETWEEN ? AND ?';
            $orderDateFilter = 'DATE(CONVERT_TZ(created_at, \'+00:00\', \'+03:00\')) BETWEEN ? AND ?';
            $dateParam = [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')];
            break;
            
        case 'month':
            // Get start and end of month
            $startOfMonth = clone $baseDate;
            $startOfMonth->modify('first day of this month');
            $endOfMonth = clone $baseDate;
            $endOfMonth->modify('last day of this month');
            
            $startDateTime = $startOfMonth->format('Y-m-d') . ' 00:00:00';
            $endDateTime = $endOfMonth->format('Y-m-d') . ' 23:59:59';
            $dateFormat = 'Y-m-d'; // Date for monthly view
            $dateGroupBy = 'DATE(CONVERT_TZ(o.created_at, \'+00:00\', \'+03:00\'))';
            $dateFilter = 'DATE(CONVERT_TZ(o.created_at, \'+00:00\', \'+03:00\')) BETWEEN ? AND ?';
            $paymentDateGroupBy = 'DATE(CONVERT_TZ(p.created_at, \'+00:00\', \'+03:00\'))';
            $paymentDateFilter = 'DATE(CONVERT_TZ(p.created_at, \'+00:00\', \'+03:00\')) BETWEEN ? AND ?';
            $orderDateFilter = 'DATE(CONVERT_TZ(created_at, \'+00:00\', \'+03:00\')) BETWEEN ? AND ?';
            $dateParam = [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')];
            break;
    }
    
    error_log("=== DEBUG START ===");
    error_log("Requested date: " . $currentDate);
    error_log("Start datetime: " . $startDateTime);
    error_log("End datetime: " . $endDateTime);
    error_log("Current timezone: " . date_default_timezone_get());
    error_log("Database timezone: UTC+3");
    
    // Initialize arrays for time period data
    if ($timePeriod === 'day') {
    $hourlyOrders = array_fill(0, 24, 0);
    $hourlySales = array_fill(0, 24, 0);
    $hourlyPayments = array_fill(0, 24, 0);
    } else {
        $hourlyOrders = [];
        $hourlySales = [];
        $hourlyPayments = [];
    }
    
    // Fetch orders data for the specified time period
    $ordersQuery = "SELECT 
                        " . $dateGroupBy . " as time_period,
                        COUNT(*) as count,
                        o.created_at as full_timestamp,
                        DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) as order_date
                    FROM order_details o
                    WHERE " . $dateFilter . "
                    GROUP BY " . $dateGroupBy . "
                    ORDER BY time_period";
    
    $stmt = $conn->prepare($ordersQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $ordersResult = $stmt->get_result();
    
    while ($row = $ordersResult->fetch_assoc()) {
        $timePeriodValue = $row['time_period'];
        $count = (int)$row['count'];
        $timestamp = $row['full_timestamp'];
        $orderDate = $row['order_date'];
        
        if ($timePeriod === 'day') {
            $hour = (int)$timePeriodValue;
        if ($hour >= 0 && $hour <= 23) {
            $hourlyOrders[$hour] = $count;
            }
        } else {
            $hourlyOrders[$timePeriodValue] = $count;
        }
        
        // Debug logging
        error_log("Orders: Time period: {$timePeriodValue}, Count: {$count}, Timestamp: {$timestamp}, Date: {$orderDate}");
    }
    
    // Fetch sales data for the specified time period (using actual payment amounts)
    $salesQuery = "SELECT 
                        " . $dateGroupBy . " as time_period,
                        SUM(COALESCE(p.amount, 0)) as total,
                        o.created_at as full_timestamp,
                        DATE(CONVERT_TZ(o.created_at, '+00:00', '+03:00')) as order_date
                    FROM order_details o
                    LEFT JOIN payment_details p ON p.order_id = o.id AND p.status = 'completed'
                    WHERE " . $dateFilter . " AND o.status IN ('completed','shipped','delivered')
                    GROUP BY " . $dateGroupBy . "
                    ORDER BY time_period";
    
    $stmt = $conn->prepare($salesQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $salesResult = $stmt->get_result();
    
    while ($row = $salesResult->fetch_assoc()) {
        $timePeriodValue = $row['time_period'];
        $total = (float)$row['total'];
        $timestamp = $row['full_timestamp'];
        $orderDate = $row['order_date'];
        
        if ($timePeriod === 'day') {
            $hour = (int)$timePeriodValue;
        if ($hour >= 0 && $hour <= 23) {
            $hourlySales[$hour] = $total;
            }
        } else {
            $hourlySales[$timePeriodValue] = $total;
        }
        
        // Debug logging
        error_log("Sales: Time period: {$timePeriodValue}, Total: {$total}, Timestamp: {$timestamp}, Date: {$orderDate}");
    }
    
    // Fetch payments data for the specified time period
    $paymentsQuery = "SELECT 
                        " . $paymentDateGroupBy . " as time_period,
                        SUM(p.amount) as total,
                        p.created_at as full_timestamp,
                        DATE(CONVERT_TZ(p.created_at, '+00:00', '+03:00')) as payment_date,
                        p.status as payment_status,
                        p.order_id,
                        p.amount as individual_amount
                    FROM payment_details p
                    INNER JOIN order_details o ON p.order_id = o.id
                    WHERE " . $paymentDateFilter . " AND p.status = 'completed'
                    GROUP BY " . $paymentDateGroupBy . "
                    ORDER BY time_period";
    
    $stmt = $conn->prepare($paymentsQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $paymentsResult = $stmt->get_result();
    
    // Debug: Log the payments query and results
    error_log("=== PAYMENTS DEBUG ===");
    error_log("Payments query executed for time period: " . $timePeriod);
    error_log("Number of payment rows returned: " . $paymentsResult->num_rows);
    
    while ($row = $paymentsResult->fetch_assoc()) {
        $timePeriodValue = $row['time_period'];
        $total = (float)$row['total'];
        $timestamp = $row['full_timestamp'];
        $paymentDate = $row['payment_date'];
        $paymentStatus = $row['payment_status'];
        $orderId = $row['order_id'];
        $individualAmount = $row['individual_amount'];
        
        if ($timePeriod === 'day') {
            $hour = (int)$timePeriodValue;
        if ($hour >= 0 && $hour <= 23) {
            $hourlyPayments[$hour] = $total;
            }
        } else {
            $hourlyPayments[$timePeriodValue] = $total;
        }
        
        // Debug logging
        error_log("Payments: Time period: {$timePeriodValue}, Total: {$total}, Timestamp: {$timestamp}, Date: {$paymentDate}, Status: {$paymentStatus}, Order ID: {$orderId}, Individual Amount: {$individualAmount}");
    }
    
    // Debug: Check if there are any payments at all for this date
    $debugPaymentsQuery = "SELECT 
                            p.id as payment_id,
                            p.amount,
                            p.status,
                            p.created_at,
                            p.order_id,
                            o.id as order_id_check,
                            o.status as order_status
                          FROM payment_details p
                          LEFT JOIN order_details o ON p.order_id = o.id
                          WHERE DATE(CONVERT_TZ(p.created_at, '+00:00', '+03:00')) = ?
                          ORDER BY p.created_at";
    
    $stmt = $conn->prepare($debugPaymentsQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $debugPaymentsResult = $stmt->get_result();
    
    error_log("=== ALL PAYMENTS FOR DATE DEBUG ===");
    error_log("Total payments found for date: " . $debugPaymentsResult->num_rows);
    
    while ($debugRow = $debugPaymentsResult->fetch_assoc()) {
        error_log("Payment ID: {$debugRow['payment_id']}, Amount: {$debugRow['amount']}, Status: {$debugRow['status']}, Created: {$debugRow['created_at']}, Order ID: {$debugRow['order_id']}, Order Status: {$debugRow['order_status']}");
    }
    
    // Get total counts and amounts using proper datetime boundaries
    $totalOrdersQuery = "SELECT COUNT(*) as total FROM order_details o WHERE " . $dateFilter;
    $stmt = $conn->prepare($totalOrdersQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $totalOrders = $stmt->get_result()->fetch_assoc()['total'];
    
    $totalSalesQuery = "SELECT 
                            SUM(COALESCE(p.amount, 0)) as total
                        FROM order_details o
                        LEFT JOIN payment_details p ON p.order_id = o.id AND p.status = 'completed'
                        WHERE " . $dateFilter . " AND o.status IN ('completed','shipped','delivered')";
    $stmt = $conn->prepare($totalSalesQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $totalSales = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    
    // Debug: Check total payments calculation
    error_log("=== TOTAL PAYMENTS DEBUG ===");
    
    $totalPaymentsQuery = "SELECT SUM(p.amount) as total FROM payment_details p 
                           INNER JOIN order_details o ON p.order_id = o.id 
                           WHERE " . $paymentDateFilter . " AND p.status = 'completed'";
    $stmt = $conn->prepare($totalPaymentsQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $totalPaymentsResult = $stmt->get_result();
    $totalPayments = $totalPaymentsResult->fetch_assoc()['total'] ?? 0;

    // Compute actual discounts (promocode discounts + payment difference) for completed/shipped/delivered orders
    $totalDiscountsQuery = "SELECT 
                                SUM(
                                    COALESCE(promocode_discounts.total_promocode_discounts, 0) +
                                    CASE 
                                        WHEN o.total > COALESCE(p.amount, 0) THEN o.total - COALESCE(p.amount, 0)
                                        ELSE 0
                                    END
                                ) as total_discounts
                            FROM order_details o
                            LEFT JOIN payment_details p ON p.order_id = o.id
                            LEFT JOIN (
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
                                    SELECT order_id, SUM(ps.price * oi.quantity) AS cart_subtotal
                                    FROM order_item oi
                                    LEFT JOIN product_skus ps ON oi.product_sku_id = ps.id
                                    GROUP BY order_id
                                ) cs ON cs.order_id = o3.id
                                GROUP BY o3.id
                            ) promocode_discounts ON promocode_discounts.order_id = o.id
                            WHERE " . $dateFilter . " AND o.status IN ('completed','shipped','delivered')";
    $stmt = $conn->prepare($totalDiscountsQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $totalDiscountsRow = $stmt->get_result()->fetch_assoc();
    $totalDiscounts = $totalDiscountsRow['total_discounts'] ?? 0;
    
    
    error_log("Total payments query executed for date: " . $currentDate);
    error_log("Total payments result: " . json_encode($totalPaymentsResult->fetch_assoc()));
    error_log("Final total payments value: " . $totalPayments);
    
    // Debug: Check if there are any completed payments at all
    $debugCompletedPaymentsQuery = "SELECT 
                                     COUNT(*) as total_count,
                                     SUM(p.amount) as total_amount,
                                     GROUP_CONCAT(p.status) as all_statuses
                                   FROM payment_details p
                                   WHERE DATE(CONVERT_TZ(p.created_at, '+00:00', '+03:00')) = ?";
    
    $stmt = $conn->prepare($debugCompletedPaymentsQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $debugCompletedResult = $stmt->get_result();
    $debugCompletedData = $debugCompletedResult->fetch_assoc();
    
    error_log("All payments for date (any status): Count: {$debugCompletedData['total_count']}, Total Amount: {$debugCompletedData['total_amount']}, Statuses: {$debugCompletedData['all_statuses']}");
    
    // Get additional order statistics using proper datetime boundaries
    $orderStatsQuery = "SELECT 
                            status,
                            COUNT(*) as count,
                            SUM(total) as total_amount
                        FROM order_details 
                        WHERE " . $orderDateFilter . "
                        GROUP BY status";
    
    $stmt = $conn->prepare($orderStatsQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $orderStatsResult = $stmt->get_result();
    
    $orderStats = [];
    while ($row = $orderStatsResult->fetch_assoc()) {
        $orderStats[$row['status']] = [
            'count' => (int)$row['count'],
            'total' => (float)$row['total_amount']
        ];
    }

    // Top performers: products (by quantity), categories (by quantity), customers (by total spend)
    // Products
    $topProductsQuery = "SELECT p.name as label, SUM(oi.quantity) as value
                         FROM order_item oi
                         INNER JOIN order_details o ON o.id = oi.order_id
                         INNER JOIN products p ON p.id = oi.product_id
                         WHERE " . $dateFilter . " AND o.status IN ('completed','shipped','delivered')
                         GROUP BY p.name
                         ORDER BY value DESC
                         LIMIT 5";
    $stmt = $conn->prepare($topProductsQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $tpRes = $stmt->get_result();
    $topProducts = [];
    while ($r = $tpRes->fetch_assoc()) { $topProducts[] = $r; }

    // Categories (use sub_categories as product category relation)
    $topCategoriesQuery = "SELECT sc.name as label, SUM(oi.quantity) as value
                           FROM order_item oi
                           INNER JOIN order_details o ON o.id = oi.order_id
                           INNER JOIN products p ON p.id = oi.product_id
                           INNER JOIN sub_categories sc ON sc.id = p.category_id
                           WHERE " . $dateFilter . " AND o.status IN ('completed','shipped','delivered')
                           GROUP BY sc.name
                           ORDER BY value DESC
                           LIMIT 5";
    $stmt = $conn->prepare($topCategoriesQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $tcRes = $stmt->get_result();
    $topCategories = [];
    while ($r = $tcRes->fetch_assoc()) { $topCategories[] = $r; }

    // Customers (by actual payment amounts)
    $topCustomersQuery = "SELECT CONCAT(u.f_name, ' ', u.l_name) as label,
                                 SUM(COALESCE(p.amount, 0)) as value
                          FROM order_details o
                          INNER JOIN users u ON u.id = o.user_id
                          LEFT JOIN payment_details p ON p.order_id = o.id AND p.status = 'completed'
                          WHERE " . $dateFilter . " AND o.status IN ('completed','shipped','delivered')
                          GROUP BY u.f_name, u.l_name
                          ORDER BY value DESC
                          LIMIT 5";
    $stmt = $conn->prepare($topCustomersQuery);
    if (is_array($dateParam)) {
        $stmt->bind_param("ss", ...$dateParam);
    } else {
        $stmt->bind_param("s", $dateParam);
    }
    $stmt->execute();
    $tcuRes = $stmt->get_result();
    $topCustomers = [];
    while ($r = $tcuRes->fetch_assoc()) { $topCustomers[] = $r; }
    
    // Get all-time data for discounts and top performers (not date-specific)
    
    // All-time actual discounts (promocode discounts + payment difference)
    $allTimeDiscountsQuery = "SELECT 
                                SUM(
                                    COALESCE(promocode_discounts.total_promocode_discounts, 0) +
                                    CASE 
                                        WHEN o.total > COALESCE(p.amount, 0) THEN o.total - COALESCE(p.amount, 0)
                                        ELSE 0
                                    END
                                ) as total_discounts
                            FROM order_details o
                            LEFT JOIN payment_details p ON p.order_id = o.id
                            LEFT JOIN (
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
                                    SELECT order_id, SUM(ps.price * oi.quantity) AS cart_subtotal
                                    FROM order_item oi
                                    LEFT JOIN product_skus ps ON oi.product_sku_id = ps.id
                                    GROUP BY order_id
                                ) cs ON cs.order_id = o3.id
                                GROUP BY o3.id
                            ) promocode_discounts ON promocode_discounts.order_id = o.id
                            WHERE o.status IN ('completed','shipped','delivered')";
    $stmt = $conn->prepare($allTimeDiscountsQuery);
    $stmt->execute();
    $allTimeDiscountsRow = $stmt->get_result()->fetch_assoc();
    $allTimeDiscounts = $allTimeDiscountsRow['total_discounts'] ?? 0;
    
    // All-time top products
    $allTimeTopProductsQuery = "SELECT p.name as label, SUM(oi.quantity) as value
                               FROM order_item oi
                               INNER JOIN order_details o ON o.id = oi.order_id
                               INNER JOIN products p ON p.id = oi.product_id
                               WHERE o.status IN ('completed','shipped','delivered')
                               GROUP BY p.name
                               ORDER BY value DESC
                               LIMIT 5";
    $stmt = $conn->prepare($allTimeTopProductsQuery);
    $stmt->execute();
    $allTimeTpRes = $stmt->get_result();
    $allTimeTopProducts = [];
    while ($r = $allTimeTpRes->fetch_assoc()) { $allTimeTopProducts[] = $r; }
    
    // All-time top categories
    $allTimeTopCategoriesQuery = "SELECT sc.name as label, SUM(oi.quantity) as value
                                 FROM order_item oi
                                 INNER JOIN order_details o ON o.id = oi.order_id
                                 INNER JOIN products p ON p.id = oi.product_id
                                 INNER JOIN sub_categories sc ON sc.id = p.category_id
                                 WHERE o.status IN ('completed','shipped','delivered')
                                 GROUP BY sc.name
                                 ORDER BY value DESC
                                 LIMIT 5";
    $stmt = $conn->prepare($allTimeTopCategoriesQuery);
    $stmt->execute();
    $allTimeTcRes = $stmt->get_result();
    $allTimeTopCategories = [];
    while ($r = $allTimeTcRes->fetch_assoc()) { $allTimeTopCategories[] = $r; }
    
    // All-time top customers (by actual payment amounts)
    $allTimeTopCustomersQuery = "SELECT CONCAT(u.f_name, ' ', u.l_name) as label,
                                        SUM(COALESCE(p.amount, 0)) as value
                                FROM order_details o
                                INNER JOIN users u ON u.id = o.user_id
                                LEFT JOIN payment_details p ON p.order_id = o.id AND p.status = 'completed'
                                WHERE o.status IN ('completed','shipped','delivered')
                                GROUP BY u.f_name, u.l_name
                                ORDER BY value DESC
                                LIMIT 5";
    $stmt = $conn->prepare($allTimeTopCustomersQuery);
    $stmt->execute();
    $allTimeTcuRes = $stmt->get_result();
    $allTimeTopCustomers = [];
    while ($r = $allTimeTcuRes->fetch_assoc()) { $allTimeTopCustomers[] = $r; }
    
    // Generate time labels based on time period
    $timeLabels = [];
    
    if ($timePeriod === 'day') {
        // 24-hour format for daily view
    for ($i = 0; $i < 24; $i++) {
        if ($i == 0) {
            $timeLabels[] = "12AM";
        } elseif ($i == 12) {
            $timeLabels[] = "12PM";
        } elseif ($i < 12) {
            $timeLabels[] = $i . "AM";
        } else {
            $timeLabels[] = ($i - 12) . "PM";
            }
        }
    } else {
        // Generate labels based on actual data for weekly/monthly view
        $allKeys = array_unique(array_merge(
            array_keys($hourlyOrders),
            array_keys($hourlySales),
            array_keys($hourlyPayments)
        ));
        sort($allKeys);
        
        foreach ($allKeys as $key) {
            if ($timePeriod === 'week') {
                // Format as day names
                $date = new DateTime($key);
                $timeLabels[] = $date->format('D'); // Mon, Tue, Wed, etc.
            } else if ($timePeriod === 'month') {
                // Format as day of month
                $date = new DateTime($key);
                $timeLabels[] = $date->format('M j'); // Jan 1, Jan 2, etc.
            }
        }
    }
    
    // Debug: Log the final arrays and verify hour mapping
    error_log("=== DEBUG INFO ===");
    error_log("Final hourly orders: " . json_encode($hourlyOrders));
    error_log("Final hourly sales: " . json_encode($hourlySales));
    error_log("Final hourly payments: " . json_encode($hourlyPayments));
    error_log("Time labels: " . json_encode($timeLabels));
    
    // Verify specific hour mapping (only for daily view)
    if ($timePeriod === 'day') {
        for ($i = 0; $i < 24; $i++) {
            if ($hourlyOrders[$i] > 0) {
                error_log("Hour {$i} ({$timeLabels[$i]}) has {$hourlyOrders[$i]} orders");
            }
        }
    }
    
    // Prepare response data
    $response = [
        'status' => 'success',
        'data' => [
            'date' => $currentDate,
            'timePeriod' => $timePeriod,
            'timeLabels' => $timeLabels,
            'orders' => [
                'hourly' => array_values($hourlyOrders),
                'total' => $totalOrders,
                'stats' => $orderStats
            ],
            'sales' => [
                'hourly' => array_values($hourlySales),
                'total' => $totalSales
            ],
            'payments' => [
                'hourly' => array_values($hourlyPayments),
                'total' => $totalPayments
            ],
            'discounts' => [
                'total' => $totalDiscounts,
                'allTime' => $allTimeDiscounts
            ],
            'topPerformers' => [
                'products' => $topProducts,
                'categories' => $topCategories,
                'customers' => $topCustomers
            ],
            'allTimeTopPerformers' => [
                'products' => $allTimeTopProducts,
                'categories' => $allTimeTopCategories,
                'customers' => $allTimeTopCustomers
            ],
            'summary' => [
                'totalOrders' => $totalOrders,
                'totalSales' => $totalSales,
                'totalPayments' => $totalPayments,
                'totalDiscounts' => $totalDiscounts,
                'completedOrders' => $orderStats['completed']['count'] ?? 0,
                'pendingOrders' => $orderStats['pending']['count'] ?? 0,
                'processingOrders' => $orderStats['processing']['count'] ?? 0
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
