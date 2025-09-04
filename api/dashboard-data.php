<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    // Get date from query parameter or use current date as default
    $requestedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }
    
    $currentDate = $requestedDate;
    
    // Set timezone to match your database timezone (UTC+3)
    date_default_timezone_set('UTC');
    
    // Create proper date boundaries for the requested date in UTC+3
    // Since your database is UTC+3, we need to adjust the boundaries
    $startDateTime = $currentDate . ' 00:00:00';
    $endDateTime = $currentDate . ' 23:59:59';
    
    error_log("=== DEBUG START ===");
    error_log("Requested date: " . $currentDate);
    error_log("Start datetime: " . $startDateTime);
    error_log("End datetime: " . $endDateTime);
    error_log("Current timezone: " . date_default_timezone_get());
    error_log("Database timezone: UTC+3");
    
    // Initialize arrays for 24-hour data
    $hourlyOrders = array_fill(0, 24, 0);
    $hourlySales = array_fill(0, 24, 0);
    $hourlyPayments = array_fill(0, 24, 0);
    
    // Fetch orders data for the specified date using proper datetime boundaries
    // Use CONVERT_TZ to ensure we're working in the correct timezone
    $ordersQuery = "SELECT 
                        HOUR(CONVERT_TZ(created_at, '+00:00', '+03:00')) as hour,
                        COUNT(*) as count,
                        created_at as full_timestamp,
                        DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) as order_date
                    FROM order_details 
                    WHERE DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) = ?
                    GROUP BY HOUR(CONVERT_TZ(created_at, '+00:00', '+03:00'))
                    ORDER BY hour";
    
    $stmt = $conn->prepare($ordersQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $ordersResult = $stmt->get_result();
    
    while ($row = $ordersResult->fetch_assoc()) {
        $hour = (int)$row['hour'];
        $count = (int)$row['count'];
        $timestamp = $row['full_timestamp'];
        $orderDate = $row['order_date'];
        
        // Ensure hour is within valid range
        if ($hour >= 0 && $hour <= 23) {
            $hourlyOrders[$hour] = $count;
        }
        
        // Debug logging
        error_log("Orders: Raw hour: {$row['hour']}, Casted hour: {$hour}, Count: {$count}, Timestamp: {$timestamp}, Date: {$orderDate}");
    }
    
    // Fetch sales data for the specified date using proper datetime boundaries
    $salesQuery = "SELECT 
                        HOUR(CONVERT_TZ(created_at, '+00:00', '+03:00')) as hour,
                        SUM(total) as total,
                        created_at as full_timestamp,
                        DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) as order_date
                    FROM order_details 
                    WHERE DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) = ? AND status IN ('completed','shipped','delivered')
                    GROUP BY HOUR(CONVERT_TZ(created_at, '+00:00', '+03:00'))
                    ORDER BY hour";
    
    $stmt = $conn->prepare($salesQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $salesResult = $stmt->get_result();
    
    while ($row = $salesResult->fetch_assoc()) {
        $hour = (int)$row['hour'];
        $total = (float)$row['total'];
        $timestamp = $row['full_timestamp'];
        $orderDate = $row['order_date'];
        
        // Ensure hour is within valid range
        if ($hour >= 0 && $hour <= 23) {
            $hourlySales[$hour] = $total;
        }
        
        // Debug logging
        error_log("Sales: Raw hour: {$row['hour']}, Casted hour: {$hour}, Total: {$total}, Timestamp: {$timestamp}, Date: {$orderDate}");
    }
    
    // Fetch payments data for the specified date using proper datetime boundaries
    $paymentsQuery = "SELECT 
                        HOUR(CONVERT_TZ(p.created_at, '+00:00', '+03:00')) as hour,
                        SUM(p.amount) as total,
                        p.created_at as full_timestamp,
                        DATE(CONVERT_TZ(p.created_at, '+00:00', '+03:00')) as payment_date,
                        p.status as payment_status,
                        p.order_id,
                        p.amount as individual_amount
                    FROM payment_details p
                    INNER JOIN order_details o ON p.order_id = o.id
                    WHERE DATE(CONVERT_TZ(p.created_at, '+00:00', '+03:00')) = ? AND p.status = 'completed'
                    GROUP BY HOUR(CONVERT_TZ(p.created_at, '+00:00', '+03:00'))
                    ORDER BY hour";
    
    $stmt = $conn->prepare($paymentsQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $paymentsResult = $stmt->get_result();
    
    // Debug: Log the payments query and results
    error_log("=== PAYMENTS DEBUG ===");
    error_log("Payments query executed for date: " . $currentDate);
    error_log("Number of payment rows returned: " . $paymentsResult->num_rows);
    
    while ($row = $paymentsResult->fetch_assoc()) {
        $hour = (int)$row['hour'];
        $total = (float)$row['total'];
        $timestamp = $row['full_timestamp'];
        $paymentDate = $row['payment_date'];
        $paymentStatus = $row['payment_status'];
        $orderId = $row['order_id'];
        $individualAmount = $row['individual_amount'];
        
        // Ensure hour is within valid range
        if ($hour >= 0 && $hour <= 23) {
            $hourlyPayments[$hour] = $total;
        }
        
        // Debug logging
        error_log("Payments: Raw hour: {$row['hour']}, Casted hour: {$hour}, Total: {$total}, Timestamp: {$timestamp}, Date: {$paymentDate}, Status: {$paymentStatus}, Order ID: {$orderId}, Individual Amount: {$individualAmount}");
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
    $totalOrdersQuery = "SELECT COUNT(*) as total FROM order_details WHERE DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) = ?";
    $stmt = $conn->prepare($totalOrdersQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $totalOrders = $stmt->get_result()->fetch_assoc()['total'];
    
    $totalSalesQuery = "SELECT SUM(total) as total FROM order_details WHERE DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) = ? AND status IN ('completed','shipped','delivered')";
    $stmt = $conn->prepare($totalSalesQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $totalSales = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    
    // Debug: Check total payments calculation
    error_log("=== TOTAL PAYMENTS DEBUG ===");
    
    $totalPaymentsQuery = "SELECT SUM(p.amount) as total FROM payment_details p 
                           INNER JOIN order_details o ON p.order_id = o.id 
                           WHERE DATE(CONVERT_TZ(p.created_at, '+00:00', '+03:00')) = ? AND p.status = 'completed'";
    $stmt = $conn->prepare($totalPaymentsQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $totalPaymentsResult = $stmt->get_result();
    $totalPayments = $totalPaymentsResult->fetch_assoc()['total'] ?? 0;
    
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
                        WHERE DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) = ?
                        GROUP BY status";
    
    $stmt = $conn->prepare($orderStatsQuery);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $orderStatsResult = $stmt->get_result();
    
    $orderStats = [];
    while ($row = $orderStatsResult->fetch_assoc()) {
        $orderStats[$row['status']] = [
            'count' => (int)$row['count'],
            'total' => (float)$row['total_amount']
        ];
    }
    
    // Format time labels (12-hour format)
    $timeLabels = [];
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
    
    // Debug: Log the final arrays and verify hour mapping
    error_log("=== DEBUG INFO ===");
    error_log("Final hourly orders: " . json_encode($hourlyOrders));
    error_log("Final hourly sales: " . json_encode($hourlySales));
    error_log("Final hourly payments: " . json_encode($hourlyPayments));
    error_log("Time labels: " . json_encode($timeLabels));
    
    // Verify specific hour mapping
    for ($i = 0; $i < 24; $i++) {
        if ($hourlyOrders[$i] > 0) {
            error_log("Hour {$i} ({$timeLabels[$i]}) has {$hourlyOrders[$i]} orders");
        }
    }
    
    // Prepare response data
    $response = [
        'status' => 'success',
        'data' => [
            'date' => $currentDate,
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
            'summary' => [
                'totalOrders' => $totalOrders,
                'totalSales' => $totalSales,
                'totalPayments' => $totalPayments,
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
