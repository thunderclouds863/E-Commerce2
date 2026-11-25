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

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_completed'])) {
    $order_id = $_POST['order_id'];
    $update_query = "UPDATE orders SET status = 'Selesai' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();
    header("Location: order-history.php");
    exit;
}

$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));

function getStatusBadgeClass($status) {
    $s = strtolower(trim($status));
    if ($s === 'selesai') return 'badge-success';
    if ($s === 'pending') return 'badge-warning';
    if ($s === 'dikirim' || $s === 'siap dikirim' || $s === 'dikonfirmasi' || $s === 'sampai tujuan') return 'badge-info';
    return 'badge-neutral';
}
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
                <a href="#footer">Contact Us</a>
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
                                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                                        <td><strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></td>
                                        <td>
                                            <span class="badge <?= getStatusBadgeClass($order['status']) ?>">
                                                <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <?php if ($order['status'] == 'Sampai Tujuan'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                                        <button type="submit" name="mark_completed" class="btn btn-success btn-sm">Mark Completed</button>
                                                    </form>
                                                <?php else: ?>
                                                    <a href="order-details.php?order_id=<?= (int)$order['id'] ?>" class="btn btn-outline btn-sm">View Details</a>
                                                    <?php if ($order['status'] == 'Selesai' && !empty($order['product_id'])): ?>
                                                        <a href="product-details.php?id=<?= (int)$order['product_id'] ?>" class="btn btn-primary btn-sm">Review</a>
                                                    <?php endif; ?>
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
<?php
$conn->close();
?>
