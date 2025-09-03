<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        // Fallback to form-encoded
        $input = $_POST;
    }

    $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
    $targetStatus = isset($input['target_status']) ? strtolower(trim($input['target_status'])) : null;
    if (!$orderId) {
        throw new Exception('order_id is required');
    }

    // Fetch current order status
    $stmt = $conn->prepare("SELECT status FROM order_details WHERE id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        throw new Exception('Order not found');
    }
    $row = $res->fetch_assoc();
    $currentStatus = strtolower($row['status'] ?? 'pending');

    // Blocked terminal states
    if (in_array($currentStatus, ['completed','canceled','cancelled','delivered'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Order in terminal state cannot be changed',
            'current' => $currentStatus
        ]);
        exit;
    }

    // Determine next status with explicit target support
    $nextStatus = null;
    $allowedTargets = ['shipped','delivered'];

    if ($targetStatus && !in_array($targetStatus, $allowedTargets, true)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid target status'
        ]);
        exit;
    }

    if ($currentStatus === 'pending') {
        // From pending: allow shipped or delivered
        $nextStatus = $targetStatus ?: 'shipped';
    } elseif ($currentStatus === 'shipped') {
        // From shipped: only delivered
        $nextStatus = 'delivered';
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Status cannot be changed from current state',
            'current' => $currentStatus
        ]);
        exit;
    }

    // Update order status
    $update = $conn->prepare("UPDATE order_details SET status = ?, updated_at = NOW() WHERE id = ?");
    $update->bind_param('si', $nextStatus, $orderId);
    if (!$update->execute()) {
        throw new Exception('Failed to update order status');
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'order_id' => $orderId,
            'previous_status' => $currentStatus,
            'new_status' => $nextStatus
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>


