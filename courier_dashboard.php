<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['courier']);

if (isset($_GET['logout'])) {
    header('Location: logout.php');
    exit();
}

$conn = db();
$flashSuccess = '';
$flashError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verify_csrf()) {
        $flashError = 'Invalid security token. Please try again.';
    } else {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $new_status = trim($_POST['new_status'] ?? '');

        $allowedMap = [
            'Siap Dikirim' => 'Dikirim',
            'Dikirim'      => 'Sampai Tujuan',
        ];

        $stmtCheck = $conn->prepare('SELECT status FROM orders WHERE id = ? LIMIT 1');
        $stmtCheck->bind_param('i', $order_id);
        $stmtCheck->execute();
        $rowCheck = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if ($rowCheck && isset($allowedMap[$rowCheck['status']]) && $allowedMap[$rowCheck['status']] === $new_status) {
            $stmtUpd = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmtUpd->bind_param('si', $new_status, $order_id);
            if ($stmtUpd->execute()) {
                $flashSuccess = "Order #{$order_id} marked as {$new_status}.";
            } else {
                $flashError = 'Failed to update delivery status.';
            }
            $stmtUpd->close();
        } else {
            $flashError = 'Invalid delivery status transition.';
        }
    }
}

$result = $conn->query("SELECT * FROM orders WHERE status IN ('Siap Dikirim', 'Dikirim') ORDER BY created_at ASC");

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="header-inner">
            <a href="courier_dashboard.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
                <span class="brand-badge">Courier</span>
            </a>
            <nav class="site-nav" aria-label="Courier navigation">
                <a href="courier_dashboard.php" class="active">Deliveries</a>
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
                    <h1>Orders to be Delivered</h1>
                    <p>Update shipment status as packages leave the warehouse and reach customers.</p>
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
                    <table class="data-table" style="min-width: 440px;">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Shipping Address</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$row['id'] ?></strong></td>
                                        <td><?= e($row['address'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge <?= status_badge_class($row['status']) ?>">
                                                <?= e($row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] === 'Siap Dikirim'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                                    <input type="hidden" name="new_status" value="Dikirim">
                                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">Mark as Dikirim</button>
                                                </form>
                                            <?php elseif ($row['status'] === 'Dikirim'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                                    <input type="hidden" name="new_status" value="Sampai Tujuan">
                                                    <button type="submit" name="update_status" class="btn btn-success btn-sm">Mark as Sampai Tujuan</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        No orders currently ready for delivery.
                                    </td>
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
