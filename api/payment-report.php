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
        $whereConditions[] = 'DATE(p.created_at) >= ?';
        $params[] = $startDate;
        $paramTypes .= 's';
    }
    if (!empty($endDate)) {
        $whereConditions[] = 'DATE(p.created_at) <= ?';
        $params[] = $endDate;
        $paramTypes .= 's';
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $sql = "SELECT 
                p.id AS payment_id,
                p.order_id,
                p.amount,
                p.provider,
                p.status AS payment_status,
                p.created_at AS payment_date,
                o.user_id,
                u.f_name,
                u.l_name
            FROM payment_details p
            LEFT JOIN order_details o ON p.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            $whereClause
            ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    $summary = [
        'total_amount' => 0.0,
        'count' => 0,
        'by_provider' => [],
        'by_provider_counts' => [],
        'by_status' => [],
        'cash_total' => 0.0,
        'credit_total' => 0.0
    ];

    while ($row = $result->fetch_assoc()) {
        $row['payment_date'] = date('Y-m-d H:i:s', strtotime($row['payment_date']));
        $row['amount'] = number_format((float)$row['amount'], 2);

        $rows[] = $row;

        $amountRaw = (float)str_replace(',', '', $row['amount']);
        $summary['total_amount'] += $amountRaw;
        $summary['count'] += 1;
        $prov = $row['provider'] ?? 'Unknown';
        $status = $row['payment_status'] ?? 'Unknown';
        if (!isset($summary['by_provider'][$prov])) $summary['by_provider'][$prov] = 0.0;
        if (!isset($summary['by_status'][$status])) $summary['by_status'][$status] = 0.0;
        $summary['by_provider'][$prov] += $amountRaw;
        $summary['by_status'][$status] += $amountRaw;
        $summary['by_provider_counts'][$prov] = ($summary['by_provider_counts'][$prov] ?? 0) + 1;

        // Classify provider into cash vs credit-like
        $provLower = strtolower($prov);
        if ($provLower === 'cash' || $provLower === 'cod' || $provLower === 'cash_on_delivery' || $provLower === 'cash on delivery') {
            $summary['cash_total'] += $amountRaw;
        } else {
            $summary['credit_total'] += $amountRaw;
        }
    }

    // Format summary amounts
    $summary['total_amount'] = number_format($summary['total_amount'], 2);
    foreach ($summary['by_provider'] as $k => $v) {
        $summary['by_provider'][$k] = number_format($v, 2);
    }
    foreach ($summary['by_status'] as $k => $v) {
        $summary['by_status'][$k] = number_format($v, 2);
    }
    $summary['cash_total'] = number_format($summary['cash_total'], 2);
    $summary['credit_total'] = number_format($summary['credit_total'], 2);

    echo json_encode([
        'status' => 'success',
        'data' => $rows,
        'summary' => $summary,
        'count' => count($rows)
    ]);

} catch (Exception $e) {
    error_log('Payment Report API Error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch payment report: ' . $e->getMessage()
    ]);
}

$conn->close();
?>


