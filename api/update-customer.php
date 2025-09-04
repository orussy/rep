<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once '../config/db.php';

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        throw new Exception('Invalid customer id');
    }

    $fields = [
        'f_name' => null,
        'l_name' => null,
        'email' => null,
        'phone_no' => null,
        'gender' => null,
        'birthdate' => null,
        'status' => null
    ];

    $updates = [];
    $params = [];
    $types = '';

    foreach ($fields as $key => $_) {
        if (array_key_exists($key, $input)) {
            $updates[] = "$key = ?";
            $params[] = $input[$key];
            $types .= 's';
        }
    }

    if (empty($updates)) {
        echo json_encode(['status' => 'success', 'message' => 'Nothing to update']);
        exit;
    }

    $updates[] = "updated_at = NOW()";
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);

    $types .= 'i';
    $params[] = $id;
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) throw new Exception('Update failed: ' . $stmt->error);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>


