<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    // Get order ID from query parameter
    $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$orderId) {
        throw new Exception('Order ID is required');
    }
    
    // Fetch order details with user information
    $orderQuery = "SELECT 
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
                    u.email,
                    u.phone_no
                  FROM order_details o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE o.id = ?";
    
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Order not found');
    }
    
    $order = $result->fetch_assoc();
    
    // Fetch order items with product information
    $itemsQuery = "SELECT 
                    oi.id,
                    oi.product_id,
                    oi.product_sku_id,
                    oi.quantity,
                    oi.created_at,
                    p.name as product_name,
                    ps.price,
                    ps.sku as sku_code
                  FROM order_item oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  LEFT JOIN product_skus ps ON oi.product_sku_id = ps.id
                  WHERE oi.order_id = ?
                  ORDER BY oi.created_at ASC";
    
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        // Format the item data
        $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
        $items[] = $item;
    }
    
    // Fetch payment information
    $paymentQuery = "SELECT 
                      p.id as payment_id,
                      p.amount,
                      p.status as payment_status,
                      p.created_at as payment_date
                    FROM payment_details p
                    WHERE p.order_id = ?
                    ORDER BY p.created_at DESC
                    LIMIT 1";
    
    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $paymentResult = $stmt->get_result();
    
    $payment = null;
    if ($paymentResult->num_rows > 0) {
        $payment = $paymentResult->fetch_assoc();
        // Format payment date
        $payment['payment_date'] = date('Y-m-d H:i:s', strtotime($payment['payment_date']));
        // Format payment amount
        $payment['amount'] = number_format($payment['amount'], 2);
    }
    
    // Add items and payment to order data
    $order['items'] = $items;
    $order['payment'] = $payment;
    
    // Format dates
    $order['created_at'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
    $order['updated_at'] = $order['updated_at'] ? date('Y-m-d H:i:s', strtotime($order['updated_at'])) : null;
    
    // Format currency values
    $order['total'] = number_format($order['total'], 2);
    $order['tax_amount'] = number_format($order['tax_amount'], 2);
    
    echo json_encode([
        'status' => 'success',
        'data' => $order
    ]);
    
} catch (Exception $e) {
    error_log("Order Details API Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch order details: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
