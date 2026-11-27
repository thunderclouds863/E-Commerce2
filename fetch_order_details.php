<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'], $_SESSION['role']) || !in_array($_SESSION['role'], ['owner', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id']) || !ctype_digit((string)$_POST['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

$orderId = (int)$_POST['order_id'];
$conn = db();

$sql = "SELECT
            o.address AS shipping_address,
            o.payment_method,
            o.total_amount,
            o.status,
            u.username AS customer_name,
            u.email AS customer_email,
            u.created_at AS customer_created_at
        FROM orders o
        INNER JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $orderId);
$stmt->execute();
$result = $stmt->get_result();
$response = $result->fetch_assoc();
$stmt->close();

if (!$response) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$sqlItems = "SELECT
                p.name AS product_name,
                oi.quantity,
                oi.price
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param('i', $orderId);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();
$response['order_items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
$stmtItems->close();

echo json_encode($response);
