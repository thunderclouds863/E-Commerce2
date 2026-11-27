<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['admin', 'owner']);

if (!isset($_GET['order_id']) || !ctype_digit((string)$_GET['order_id'])) {
    header('Location: order-management.php');
    exit();
}

$order_id = (int)$_GET['order_id'];
$conn = db();

$order_sql = "SELECT o.id, o.total_amount, o.status, o.created_at, o.address, o.payment_method, u.username, u.email
              FROM orders o
              JOIN users u ON o.user_id = u.id
              WHERE o.id = ?";
$stmt_order = $conn->prepare($order_sql);
$stmt_order->bind_param('i', $order_id);
$stmt_order->execute();
$order = $stmt_order->get_result()->fetch_assoc();
$stmt_order->close();

if (!$order) {
    http_response_code(404);
    die('Order not found.');
}

$order_items_sql = "SELECT oi.*, p.name AS product_name, p.price AS product_price
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($order_items_sql);
$stmt_items->bind_param('i', $order_id);
$stmt_items->execute();
$order_items_result = $stmt_items->get_result();

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= (int)$order['id'] ?> (Admin) - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="header-inner">
            <a href="admin_dashboard.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
                <span class="brand-badge">Admin</span>
            </a>
            <nav class="site-nav" aria-label="Admin navigation">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="order-management.php" class="active">Order Management</a>
                <a href="inventory_management.php">Inventory Management</a>
                <div class="user-menu">
                    <button type="button" class="user-trigger" aria-haspopup="true" aria-expanded="false">
                        <span class="user-avatar"><?= $avatarInitial ?></span>
                        <span><?= $usernameDisplay ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </nav>
            <button type="button" class="hamburger" onclick="toggleMenu()" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main id="main-content" class="page-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h1>Order #<?= (int)$order['id'] ?></h1>
                    <p>Customer: <strong><?= e($order['username']) ?></strong> (<?= e($order['email']) ?>) &middot; <?= e($order['created_at']) ?></p>
                </div>
                <a href="order-management.php" class="btn btn-outline btn-sm">&larr; Back to Order Management</a>
            </div>

            <div class="surface-card">
                <div class="meta-grid">
                    <div class="meta-box">
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            <span class="badge <?= status_badge_class($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span>
                        </div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-label">Payment Method</div>
                        <div class="meta-value"><?= e(ucwords(str_replace('_', ' ', $order['payment_method']))) ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-label">Shipping Address</div>
                        <div class="meta-value"><?= e($order['address']) ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-label">Total Amount</div>
                        <div class="meta-value" style="color: var(--primary);"><?= format_rp($order['total_amount']) ?></div>
                    </div>
                </div>

                <h2 style="font-size: 1.2rem; color: var(--primary); margin-bottom: 14px;">Order Items</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_order_amount = 0;
                            while ($item = $order_items_result->fetch_assoc()):
                                $total_product_price = $item['quantity'] * $item['product_price'];
                                $total_order_amount += $total_product_price;
                                ?>
                                <tr>
                                    <td><strong><?= e($item['product_name']) ?></strong></td>
                                    <td><?= (int)$item['quantity'] ?></td>
                                    <td><?= format_rp($item['product_price']) ?></td>
                                    <td><strong><?= format_rp($total_product_price) ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="checkout-summary-box" style="margin-top: 24px;">
                    <span style="font-weight: 600; color: var(--primary);">Total Order Amount</span>
                    <span class="total-price"><?= format_rp($total_order_amount) ?></span>
                </div>
            </div>
        </div>
    </main>

    <footer class="portal-footer">
        &copy; 2024 Guspart Autoshop. All Rights Reserved.
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>
