<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    header("Location: login.php");
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

$order_id = $_GET['order_id'];

$order_sql = "SELECT o.id, o.total_amount, o.status, o.created_at, o.address, o.payment_method
              FROM orders o
              WHERE o.id = ?";
$stmt_order = $conn->prepare($order_sql);
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();

$order_items_sql = "SELECT oi.*, p.name AS product_name, p.price AS product_price
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($order_items_sql);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items_result = $stmt_items->get_result();

if ($order_result->num_rows > 0) {
    $order = $order_result->fetch_assoc();
} else {
    echo "Order not found.";
    exit;
}

$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= (int)$order['id'] ?> - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="index.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
            </a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="index.php">Home</a>
                <a href="product.php">Products</a>
                <a href="order.php">Order</a>
                <a href="order-history.php" class="active">Order History</a>
                <a href="index.php#footer">Contact Us</a>
                <div class="user-menu">
                    <?php if ($isLoggedIn): ?>
                        <button type="button" class="user-trigger" aria-haspopup="true" aria-expanded="false">
                            <span class="user-avatar"><?= $avatarInitial ?></span>
                            <span><?= $usernameDisplay ?></span>
                        </button>
                        <div class="dropdown-menu">
                            <a href="logout.php">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="nav-cta">Login</a>
                    <?php endif; ?>
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
                    <h1>Order #<?= (int)$order['id'] ?></h1>
                    <p>Placed on <?= htmlspecialchars($order['created_at']) ?></p>
                </div>
                <a href="order-history.php" class="btn btn-outline btn-sm">&larr; Back to Order History</a>
            </div>

            <div class="surface-card">
                <div class="meta-grid">
                    <div class="meta-box">
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            <span class="badge badge-info"><?= htmlspecialchars(ucfirst($order['status'])) ?></span>
                        </div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-label">Payment Method</div>
                        <div class="meta-value"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))) ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-label">Shipping Address</div>
                        <div class="meta-value"><?= htmlspecialchars($order['address']) ?></div>
                    </div>
                    <div class="meta-box">
                        <div class="meta-label">Total Amount</div>
                        <div class="meta-value" style="color: var(--primary);">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></div>
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
                                    <td><strong><?= htmlspecialchars($item['product_name']) ?></strong></td>
                                    <td><?= (int)$item['quantity'] ?></td>
                                    <td>Rp <?= number_format($item['product_price'], 0, ',', '.') ?></td>
                                    <td><strong>Rp <?= number_format($total_product_price, 0, ',', '.') ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="checkout-summary-box" style="margin-top: 24px;">
                    <span style="font-weight: 600; color: var(--primary);">Total Order Amount</span>
                    <span class="total-price">Rp <?= number_format($total_order_amount, 0, ',', '.') ?></span>
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
