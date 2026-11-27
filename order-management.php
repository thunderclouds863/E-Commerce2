<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['admin']);

$conn = db();
$flashSuccess = '';
$flashError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transition_order'])) {
    if (!verify_csrf()) {
        $flashError = 'Invalid security token. Please try again.';
    } else {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $next_status = trim($_POST['next_status'] ?? '');

        $allowedTransitions = [
            'Pending'      => 'Dikonfirmasi',
            'pending'      => 'Dikonfirmasi',
            'Dikonfirmasi' => 'Dikemas',
            'Dikemas'      => 'Siap Dikirim',
        ];

        $stmtCheck = $conn->prepare('SELECT status FROM orders WHERE id = ? LIMIT 1');
        $stmtCheck->bind_param('i', $order_id);
        $stmtCheck->execute();
        $orderRow = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if (!$orderRow) {
            $flashError = 'Order not found.';
        } elseif (!isset($allowedTransitions[$orderRow['status']]) || $allowedTransitions[$orderRow['status']] !== $next_status) {
            $flashError = 'Invalid order status transition.';
        } else {
            $conn->begin_transaction();
            try {
                // Only deduct product stock ONCE when transitioning from Pending -> Dikonfirmasi
                if (strtolower($orderRow['status']) === 'pending' && $next_status === 'Dikonfirmasi') {
                    $stmtItems = $conn->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
                    $stmtItems->bind_param('i', $order_id);
                    $stmtItems->execute();
                    $itemsRes = $stmtItems->get_result();
                    while ($item = $itemsRes->fetch_assoc()) {
                        $updStock = $conn->prepare('UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?');
                        $updStock->bind_param('ii', $item['quantity'], $item['product_id']);
                        $updStock->execute();
                        $updStock->close();
                    }
                    $stmtItems->close();
                }

                $updOrder = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
                $updOrder->bind_param('si', $next_status, $order_id);
                $updOrder->execute();
                $updOrder->close();

                $conn->commit();
                $flashSuccess = "Order #{$order_id} updated to {$next_status}.";
            } catch (Throwable $ex) {
                $conn->rollback();
                $flashError = 'Failed to update order status.';
            }
        }
    }
}

$sql = "SELECT orders.id, orders.created_at, orders.total_amount, orders.status, users.username
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.created_at DESC";
$result = $conn->query($sql);

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Guspart Autoshop</title>
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
                    <h1>Order Management</h1>
                    <p>Confirm pending orders, pack items, and hand off packages to couriers step by step.</p>
                </div>
            </div>

            <?php if ($flashSuccess !== ''): ?>
                <div class="alert alert-success" role="status" aria-live="polite"><?= e($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError !== ''): ?>
                <div class="alert alert-error" role="alert" aria-live="polite"><?= e($flashError) ?></div>
            <?php endif; ?>

            <div class="surface-card" style="padding: 0; overflow: hidden;">
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$row['id'] ?></strong></td>
                                        <td><?= e($row['username']) ?></td>
                                        <td><?= e($row['created_at']) ?></td>
                                        <td style="white-space: nowrap;"><strong><?= format_rp($row['total_amount']) ?></strong></td>
                                        <td>
                                            <span class="badge <?= status_badge_class($row['status']) ?>">
                                                <?= e(ucfirst($row['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="order-details-admin.php?order_id=<?= (int)$row['id'] ?>" class="btn btn-outline btn-sm">View Details</a>
                                                <?php if (strtolower($row['status']) === 'pending'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                                        <input type="hidden" name="next_status" value="Dikonfirmasi">
                                                        <button type="submit" name="transition_order" class="btn btn-success btn-sm">Confirm Order</button>
                                                    </form>
                                                <?php elseif ($row['status'] === 'Dikonfirmasi'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                                        <input type="hidden" name="next_status" value="Dikemas">
                                                        <button type="submit" name="transition_order" class="btn btn-primary btn-sm">Mark Packed</button>
                                                    </form>
                                                <?php elseif ($row['status'] === 'Dikemas'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                                        <input type="hidden" name="next_status" value="Siap Dikirim">
                                                        <button type="submit" name="transition_order" class="btn btn-secondary btn-sm">Ready to Ship</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
