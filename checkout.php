<?php
session_start();

$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
  header("Location: login.php");
  exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_amount = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];

    $user_id = $_SESSION['user_id'];
    $sql = "INSERT INTO orders (user_id, total_amount, address, payment_method, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $user_id, $total_amount, $address, $payment_method);
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        foreach ($cart_items as $product_id => $item) {
            $sql = "SELECT price FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $price = $product['price'];
            $quantity = $item['quantity'];

            $total_amount += $price * $quantity;

            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt->execute();
        }

        $sql = "UPDATE orders SET total_amount = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $total_amount, $order_id);
        $stmt->execute();

        $_SESSION['cart'] = [];
        header("Location: order-history.php");
        exit;
    }
}

$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Guspart Autoshop</title>
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
                <a href="order.php" class="active">Order</a>
                <a href="order-history.php">Order History</a>
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
                    <h1>Checkout</h1>
                    <p>Confirm your items, enter your shipping address, and choose a payment method.</p>
                </div>
                <a href="order.php" class="btn btn-outline btn-sm">&larr; Back to Cart</a>
            </div>

            <div class="checkout-grid">
                <div class="surface-card">
                    <h2 style="font-size: 1.3rem; color: var(--primary); margin-bottom: 16px;">Order Summary</h2>
                    <?php
                    if (!empty($cart_items)) {
                        foreach ($cart_items as $product_id => $item) {
                            $pid = intval($product_id);
                            $sql = "SELECT * FROM products WHERE id = $pid";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                $product = $result->fetch_assoc();
                                $line_total = $product['price'] * $item['quantity'];
                                $total_amount += $line_total;
                                ?>
                                <div class="checkout-item">
                                    <div>
                                        <h3 style="font-size: 1rem; margin-bottom: 2px;"><?= htmlspecialchars($product['name']) ?></h3>
                                        <span style="font-size: 0.86rem; color: var(--text-muted);">
                                            Rp <?= number_format($product['price'], 0, ',', '.') ?> &times; <?= (int)$item['quantity'] ?>
                                        </span>
                                    </div>
                                    <strong style="color: var(--primary); white-space: nowrap;">
                                        Rp <?= number_format($line_total, 0, ',', '.') ?>
                                    </strong>
                                </div>
                                <?php
                            }
                        }
                    } else {
                        echo '<p style="color: var(--text-secondary);">No items in your cart.</p>';
                    }
                    ?>

                    <div class="checkout-summary-box">
                        <span style="font-weight: 600; color: var(--primary);">Total Price</span>
                        <span class="total-price">Rp <?= number_format($total_amount, 0, ',', '.') ?></span>
                    </div>
                </div>

                <div class="surface-card">
                    <h2 style="font-size: 1.3rem; color: var(--primary); margin-bottom: 18px;">Shipping &amp; Payment</h2>
                    <form action="checkout.php" method="POST">
                        <div class="form-group">
                            <label for="address">Shipping Address</label>
                            <textarea id="address" name="address" placeholder="Enter your full delivery address..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="cash_on_delivery">Cash on Delivery</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">Place Order &amp; Pay</button>
                    </form>
                </div>
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
