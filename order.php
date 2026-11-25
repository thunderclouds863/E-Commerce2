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

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if (isset($_GET['add_to_cart'])) {
  $product_id = $_GET['add_to_cart'];
  if ($product_id > 0) {
    if (!isset($_SESSION['cart'][$product_id])) {
      $_SESSION['cart'][$product_id] = ['quantity' => 0];
    }
    $_SESSION['cart'][$product_id]['quantity']++;
  }
}

if (isset($_GET['remove_from_cart'])) {
  $product_id = $_GET['remove_from_cart'];
  if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
  }
}

if (isset($_POST['update_quantity'])) {
  if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
      if (isset($_SESSION['cart'][$product_id])) {
        $quantity = intval($quantity);
        if ($quantity > 0) {
          $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
      }
    }
  }
}

$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
$grand_total = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart - Guspart Autoshop</title>
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
          <h1>Your Shopping Cart</h1>
          <p>Review selected spare parts and adjust quantities before checkout.</p>
        </div>
        <a href="product.php" class="btn btn-outline btn-sm">&larr; Continue Shopping</a>
      </div>

      <div class="surface-card">
        <?php if (empty($_SESSION['cart'])): ?>
          <div class="empty-state">
            <h3>Your cart is currently empty</h3>
            <p>Browse our spare parts catalog to add items to your order.</p>
            <a href="product.php" class="btn btn-primary">Explore Products</a>
          </div>
        <?php else: ?>
          <div class="cart-list">
            <?php
            foreach ($_SESSION['cart'] as $product_id => $item) {
              if ($product_id <= 0) {
                unset($_SESSION['cart'][$product_id]);
                continue;
              }

              if (is_array($item) && isset($item['quantity'])) {
                $pid = intval($product_id);
                $sql = "SELECT * FROM products WHERE id = $pid";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                  $product = $result->fetch_assoc();
                  $total_price = $product['price'] * $item['quantity'];
                  $grand_total += $total_price;

                  $product_image_name = 'produk' . $product['id'];
                  $image_extension = pathinfo($product['image'], PATHINFO_EXTENSION);
                  $image_path = 'uploads/' . $product_image_name . '.' . $image_extension;
                  $image_src = file_exists($image_path) ? $image_path : 'gambar2.png';
                  ?>
                  <div class="cart-item">
                    <img class="cart-item-img" src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($product['name']) ?>">

                    <div class="cart-item-info">
                      <h3><?= htmlspecialchars($product['name']) ?></h3>
                      <p class="price">
                        Rp <?= number_format($product['price'], 0, ',', '.') ?> &times; <?= (int)$item['quantity'] ?>
                        &nbsp;&middot;&nbsp;
                        <strong style="color: var(--primary);">Rp <?= number_format($total_price, 0, ',', '.') ?></strong>
                      </p>
                    </div>

                    <div class="cart-item-actions">
                      <form action="order.php" method="POST" class="update-quantity-form" style="display: flex; align-items: center; gap: 10px;">
                        <input type="hidden" name="update_quantity" value="1">
                        <input type="number" class="qty-input" name="quantity[<?= $pid ?>]" value="<?= (int)$item['quantity'] ?>" min="1" max="10" aria-label="Quantity for <?= htmlspecialchars($product['name']) ?>">
                        <a href="order.php?remove_from_cart=<?= $pid ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this item?');">Remove</a>
                      </form>
                    </div>
                  </div>
                  <?php
                } else {
                  echo "<p>Product not found for ID: " . htmlspecialchars($product_id) . "</p>";
                }
              }
            }
            ?>
          </div>

          <div class="cart-footer">
            <div>
              <div class="cart-total-label">Estimated Total</div>
              <div class="cart-total-value">Rp <?= number_format($grand_total, 0, ',', '.') ?></div>
            </div>
            <a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout &rarr;</a>
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
  <script>
    document.querySelectorAll('.qty-input').forEach(input => {
      input.addEventListener('change', function () {
        this.closest('form').submit();
      });
    });
  </script>
</body>

</html>
<?php
$conn->close();
?>
