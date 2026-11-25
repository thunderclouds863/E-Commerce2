<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
  header("Location: index.php");
  exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['confirm_order'])) {
    $order_id = $_GET['confirm_order'];

    $update_status = "UPDATE orders SET status = 'Dikonfirmasi' WHERE id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $update_status = "UPDATE orders SET status = 'Dikemas' WHERE id = ? And Status = 'Dikonfirmasi'";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $update_status = "UPDATE orders SET status = 'Siap Dikirim' WHERE id = ? And Status = 'Dikemas'";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $order_items = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($order_items);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $update_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stmt = $conn->prepare($update_stock);
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();
    }

    header("Location: order-management.php");
    exit();
}

$sql = "SELECT orders.id, orders.created_at, orders.total_amount, orders.status, users.username
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.created_at DESC";
$result = $conn->query($sql);

$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));

function getAdminOrderBadge($status) {
    $s = strtolower(trim($status));
    if ($s === 'selesai') return 'badge-success';
    if ($s === 'pending') return 'badge-warning';
    return 'badge-info';
}
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

    <main class="page-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h1>Order Management</h1>
                    <p>Review customer orders, inspect details, and confirm orders for fulfillment.</p>
                </div>
            </div>

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
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$row['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($row['username']) ?></td>
                                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                                        <td style="white-space: nowrap;"><strong>Rp <?= number_format($row['total_amount'], 0, ',', '.') ?></strong></td>
                                        <td>
                                            <span class="badge <?= getAdminOrderBadge($row['status']) ?>">
                                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="order-details-admin.php?order_id=<?= (int)$row['id'] ?>" class="btn btn-outline btn-sm">View Details</a>
                                                <?php if (strtolower($row['status']) == 'pending'): ?>
                                                    <a href="order-management.php?confirm_order=<?= (int)$row['id'] ?>" class="btn btn-success btn-sm">Confirm</a>
                                                <?php endif; ?>
                                                <?php if ($row['status'] == 'Dikonfirmasi'): ?>
                                                    <a href="order-management.php?confirm_order=<?= (int)$row['id'] ?>" class="btn btn-primary btn-sm">Dikemas</a>
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
<?php
$conn->close();
?>
