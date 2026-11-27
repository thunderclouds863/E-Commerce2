<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['admin']);

$conn = db();

$pendingOrders = (int)$conn->query("SELECT COUNT(*) AS c FROM orders WHERE LOWER(status) = 'pending'")->fetch_assoc()['c'];
$activeOrders  = (int)$conn->query("SELECT COUNT(*) AS c FROM orders WHERE status IN ('Dikonfirmasi', 'Dikemas', 'Siap Dikirim', 'Dikirim')")->fetch_assoc()['c'];
$totalProducts = (int)$conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$lowStockCount = (int)$conn->query("SELECT COUNT(*) AS c FROM products WHERE stock <= 10")->fetch_assoc()['c'];

$recentOrders = $conn->query("SELECT o.id, o.created_at, o.total_amount, o.status, u.username
                              FROM orders o
                              JOIN users u ON o.user_id = u.id
                              ORDER BY o.created_at DESC
                              LIMIT 5");

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Guspart Autoshop</title>
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
                <a href="admin_dashboard.php" class="active">Dashboard</a>
                <a href="order-management.php">Order Management</a>
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
                    <h1>Operations Overview</h1>
                    <p>Signed in as <strong><?= $usernameDisplay ?></strong>. Monitor incoming orders and spare parts inventory.</p>
                </div>
                <div class="table-actions">
                    <a href="order-management.php" class="btn btn-primary btn-sm">Manage Orders</a>
                    <a href="inventory_management.php" class="btn btn-outline btn-sm">Manage Inventory</a>
                </div>
            </div>

            <div class="kpi-grid-4">
                <div class="kpi-card">
                    <div class="kpi-label">Pending Orders</div>
                    <div class="kpi-value <?= $pendingOrders > 0 ? 'warning' : '' ?>"><?= $pendingOrders ?></div>
                    <div class="kpi-sub">Awaiting confirmation</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Active Fulfillment</div>
                    <div class="kpi-value"><?= $activeOrders ?></div>
                    <div class="kpi-sub">Confirmed to in-transit</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Catalog Products</div>
                    <div class="kpi-value"><?= $totalProducts ?></div>
                    <div class="kpi-sub">Active spare part SKUs</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Low Stock (&le; 10)</div>
                    <div class="kpi-value <?= $lowStockCount > 0 ? 'accent' : 'success' ?>"><?= $lowStockCount ?></div>
                    <div class="kpi-sub">Items needing restock</div>
                </div>
            </div>

            <div class="surface-card" style="padding: 0; overflow: hidden; margin-bottom: 28px;">
                <div class="section-card-header">
                    <h2>Recent Orders</h2>
                    <a href="order-management.php" class="btn btn-outline btn-sm">View All Orders &rarr;</a>
                </div>
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                                <?php while ($row = $recentOrders->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$row['id'] ?></strong></td>
                                        <td><?= e($row['username']) ?></td>
                                        <td><?= e($row['created_at']) ?></td>
                                        <td style="white-space: nowrap;"><strong><?= format_rp($row['total_amount']) ?></strong></td>
                                        <td><span class="badge <?= status_badge_class($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
                                        <td><a href="order-details-admin.php?order_id=<?= (int)$row['id'] ?>" class="btn btn-outline btn-sm">Details</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 28px; color: var(--text-muted);">No orders recorded yet.</td>
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
