<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['owner']);

$conn = db();

$salesRow = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status = 'Selesai'")->fetch_assoc();
$completedOrders = (int)$salesRow['cnt'];
$totalRevenue    = (float)$salesRow['total'];
$pendingUsers    = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE status = 'pending'")->fetch_assoc()['c'];
$activeUsers     = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE status = 'approved'")->fetch_assoc()['c'];

$recentPending = $conn->query("SELECT id, username, email, role, created_at FROM users WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5");

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="header-inner">
            <a href="owner_dashboard.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
                <span class="brand-badge">Owner</span>
            </a>
            <nav class="site-nav" aria-label="Owner navigation">
                <a href="owner_dashboard.php" class="active">Dashboard</a>
                <a href="management-user.php">User Management</a>
                <a href="report.php">Sales Report</a>
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
                    <h1>Business Overview</h1>
                    <p>Signed in as <strong><?= $usernameDisplay ?></strong>. Monitor completed revenue and staff approvals.</p>
                </div>
                <div class="table-actions">
                    <a href="report.php" class="btn btn-primary btn-sm">Sales Report</a>
                    <a href="management-user.php" class="btn btn-outline btn-sm">User Management</a>
                </div>
            </div>

            <div class="kpi-grid-4">
                <div class="kpi-card">
                    <div class="kpi-label">Completed Revenue</div>
                    <div class="kpi-value success"><?= format_rp($totalRevenue) ?></div>
                    <div class="kpi-sub">From finished orders</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Completed Orders</div>
                    <div class="kpi-value"><?= $completedOrders ?></div>
                    <div class="kpi-sub">Delivered &amp; confirmed</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Pending Approvals</div>
                    <div class="kpi-value <?= $pendingUsers > 0 ? 'warning' : '' ?>"><?= $pendingUsers ?></div>
                    <div class="kpi-sub">Staff accounts waiting</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Active Accounts</div>
                    <div class="kpi-value"><?= $activeUsers ?></div>
                    <div class="kpi-sub">Customers, admins &amp; couriers</div>
                </div>
            </div>

            <div class="surface-card" style="padding: 0; overflow: hidden;">
                <div class="section-card-header">
                    <h2>Pending Staff Registrations</h2>
                    <a href="management-user.php" class="btn btn-outline btn-sm">Manage All Users &rarr;</a>
                </div>
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Requested</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentPending && $recentPending->num_rows > 0): ?>
                                <?php while ($u = $recentPending->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$u['id'] ?></strong></td>
                                        <td><?= e($u['username']) ?></td>
                                        <td><?= e($u['email']) ?></td>
                                        <td><span class="badge badge-warning"><?= e(ucfirst($u['role'])) ?></span></td>
                                        <td><?= e($u['created_at']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 28px; color: var(--text-muted);">No pending user approvals at this time.</td>
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
