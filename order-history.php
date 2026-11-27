<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$conn = db();
$user_id = (int)$_SESSION['user_id'];
$flashSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_completed']) && verify_csrf()) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $update_stmt = $conn->prepare("UPDATE orders SET status = 'Selesai' WHERE id = ? AND user_id = ? AND status = 'Sampai Tujuan'");
    $update_stmt->bind_param('ii', $order_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
    header('Location: order-history.php');
    exit;
}

$sql = "SELECT o.*,
               (SELECT oi.product_id FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS first_product_id
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
$cartCount = cart_item_count();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="header-inner">
            <a href="index.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
            </a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="index.php">Home</a>
                <a href="product.php">Products</a>
                <a href="order.php">Order<?= $cartCount > 0 ? " ({$cartCount})" : '' ?></a>
                <a href="order-history.php" class="active">Order History</a>
                <a href="#footer">Contact Us</a>
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
                    <h1>Your Order History</h1>
                    <p>Track the status of your recent spare part purchases and confirm deliveries.</p>
                </div>
                <a href="product.php" class="btn btn-primary btn-sm">Shop More Parts</a>
            </div>

            <div class="surface-card" style="padding: 0; overflow: hidden;">
                <?php if ($orders_result->num_rows > 0): ?>
                    <div class="table-responsive" style="border: none; border-radius: 0;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$order['id'] ?></strong></td>
                                        <td><?= e($order['created_at']) ?></td>
                                        <td><strong><?= format_rp($order['total_amount']) ?></strong></td>
                                        <td>
                                            <span class="badge <?= status_badge_class($order['status']) ?>">
                                                <?= e(ucfirst($order['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <?php if ($order['status'] === 'Sampai Tujuan'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                                        <button type="submit" name="mark_completed" class="btn btn-success btn-sm">Confirm Received</button>
                                                    </form>
                                                <?php endif; ?>
                                                <a href="order-details.php?order_id=<?= (int)$order['id'] ?>" class="btn btn-outline btn-sm">View Details</a>
                                                <?php if ($order['status'] === 'Selesai' && !empty($order['first_product_id'])): ?>
                                                    <a href="product-details.php?id=<?= (int)$order['first_product_id'] ?>" class="btn btn-primary btn-sm">Review</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No orders yet</h3>
                        <p>Once you complete a checkout, your order history will appear here.</p>
                        <a href="product.php" class="btn btn-primary">Browse Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer" id="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3>Guspart Autoshop</h3>
                    <p>We've worked tirelessly to find the best suppliers to bring high-quality and reliable spare parts to your garage.</p>
                </div>
                <div>
                    <h3>Get in Touch</h3>
                    <p>123-456-7890</p>
                    <p>+123-456-7890</p>
                    <p>guspart@autoshop.com</p>
                </div>
                <div>
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="product.php">Products</a>
                    <a href="order.php">Shopping Cart</a>
                    <a href="order-history.php">Order History</a>
                </div>
                <div>
                    <h3>Join Our Newsletter</h3>
                    <p>Get notified about new spare part arrivals and weekend promos.</p>
                    <form class="newsletter-form" onsubmit="event.preventDefault();">
                        <input type="email" placeholder="Enter your email" aria-label="Email address">
                        <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2024 Guspart Autoshop. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>
