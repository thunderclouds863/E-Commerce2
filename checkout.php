<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['customer']);

$conn = db();
$cart_items = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? $_SESSION['cart'] : [];
$total_amount = 0;
$checkoutError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $checkoutError = 'Invalid security token. Please refresh and try again.';
    } elseif (empty($cart_items)) {
        $checkoutError = 'Your shopping cart is empty. Please add products before checking out.';
    } else {
        $address = trim($_POST['address'] ?? '');
        $payment_method = trim($_POST['payment_method'] ?? '');
        $allowedMethods = ['bank_transfer', 'credit_card', 'cash_on_delivery'];

        if ($address === '' || strlen($address) < 5) {
            $checkoutError = 'Please enter a complete shipping address.';
        } elseif (!in_array($payment_method, $allowedMethods, true)) {
            $checkoutError = 'Please select a valid payment method.';
        } else {
            $user_id = (int)$_SESSION['user_id'];
            $conn->begin_transaction();
            try {
                $verifiedItems = [];
                $computedTotal = 0.0;

                $stmtProd = $conn->prepare('SELECT id, name, price, stock FROM products WHERE id = ? LIMIT 1');
                foreach ($cart_items as $product_id => $item) {
                    $pid = (int)$product_id;
                    $qty = max(1, (int)($item['quantity'] ?? 1));
                    $stmtProd->bind_param('i', $pid);
                    $stmtProd->execute();
                    $prod = $stmtProd->get_result()->fetch_assoc();
                    if (!$prod) {
                        throw new RuntimeException('One of the items in your cart is no longer available.');
                    }
                    if ((int)$prod['stock'] < $qty) {
                        throw new RuntimeException("Insufficient stock for {$prod['name']}.");
                    }
                    $verifiedItems[] = ['id' => $pid, 'qty' => $qty, 'price' => (float)$prod['price']];
                    $computedTotal += (float)$prod['price'] * $qty;
                }
                $stmtProd->close();

                if (empty($verifiedItems)) {
                    throw new RuntimeException('Your cart has no valid items.');
                }

                $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, total_amount, address, payment_method, status) VALUES (?, ?, ?, ?, 'Pending')");
                $stmtOrder->bind_param('idss', $user_id, $computedTotal, $address, $payment_method);
                $stmtOrder->execute();
                $order_id = $stmtOrder->insert_id;
                $stmtOrder->close();

                $stmtItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
                foreach ($verifiedItems as $vi) {
                    $stmtItem->bind_param('iiid', $order_id, $vi['id'], $vi['qty'], $vi['price']);
                    $stmtItem->execute();
                }
                $stmtItem->close();

                $conn->commit();
                $_SESSION['cart'] = [];
                header('Location: order-history.php');
                exit;
            } catch (Throwable $ex) {
                $conn->rollback();
                $checkoutError = $ex->getMessage();
            }
        }
    }
}

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
$cartCount = cart_item_count();
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
                <a href="order.php" class="active">Order<?= $cartCount > 0 ? " ({$cartCount})" : '' ?></a>
                <a href="order-history.php">Order History</a>
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
                    <h1>Checkout</h1>
                    <p>Confirm your items, enter your shipping address, and choose a payment method.</p>
                </div>
                <a href="order.php" class="btn btn-outline btn-sm">&larr; Back to Cart</a>
            </div>

            <?php if ($checkoutError !== ''): ?>
                <div class="alert alert-error" role="alert" aria-live="polite"><?= e($checkoutError) ?></div>
            <?php endif; ?>

            <div class="checkout-grid">
                <div class="surface-card">
                    <h2 style="font-size: 1.3rem; color: var(--primary); margin-bottom: 16px;">Order Summary</h2>
                    <?php
                    if (!empty($cart_items)) {
                        $stmtProd = $conn->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
                        foreach ($cart_items as $product_id => $item) {
                            $pid = (int)$product_id;
                            $stmtProd->bind_param('i', $pid);
                            $stmtProd->execute();
                            $product = $stmtProd->get_result()->fetch_assoc();

                            if ($product) {
                                $qty = max(1, (int)($item['quantity'] ?? 1));
                                $line_total = $product['price'] * $qty;
                                $total_amount += $line_total;
                                ?>
                                <div class="checkout-item">
                                    <div>
                                        <h3 style="font-size: 1rem; margin-bottom: 2px;"><?= e($product['name']) ?></h3>
                                        <span style="font-size: 0.86rem; color: var(--text-muted);">
                                            <?= format_rp($product['price']) ?> &times; <?= $qty ?>
                                        </span>
                                    </div>
                                    <strong style="color: var(--primary); white-space: nowrap;">
                                        <?= format_rp($line_total) ?>
                                    </strong>
                                </div>
                                <?php
                            }
                        }
                        $stmtProd->close();
                    } else {
                        echo '<p style="color: var(--text-secondary);">No items in your cart. <a href="product.php" style="color: var(--primary); font-weight: 600;">Browse products</a>.</p>';
                    }
                    ?>

                    <div class="checkout-summary-box">
                        <span style="font-weight: 600; color: var(--primary);">Total Price</span>
                        <span class="total-price"><?= format_rp($total_amount) ?></span>
                    </div>
                </div>

                <div class="surface-card">
                    <h2 style="font-size: 1.3rem; color: var(--primary); margin-bottom: 18px;">Shipping &amp; Payment</h2>
                    <form action="checkout.php" method="POST" data-loading-text="Placing order...">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="address">Shipping Address</label>
                            <textarea id="address" name="address" placeholder="Enter your full delivery address..." required><?= e($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="cash_on_delivery">Cash on Delivery</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg" <?= empty($cart_items) ? 'disabled' : '' ?>>Place Order &amp; Pay</button>
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
