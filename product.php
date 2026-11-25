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

$addedNotice = false;
if (isset($_GET['add_to_cart'])) {
  $product_id = $_GET['add_to_cart'];
  if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity']++;
  } else {
    $_SESSION['cart'][$product_id] = ['quantity' => 1];
  }
  $addedNotice = true;
}

$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if (!$result) {
  die("Error: " . $conn->error);
}

$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
$cartCount = 0;
foreach ($_SESSION['cart'] as $cItem) {
  if (is_array($cItem) && isset($cItem['quantity'])) {
    $cartCount += (int)$cItem['quantity'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products - Guspart Autoshop</title>
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
        <a href="product.php" class="active">Products</a>
        <a href="order.php">Order<?= $cartCount > 0 ? " ($cartCount)" : "" ?></a>
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

  <main>
    <section class="page-section" id="products">
      <div class="container">
        <div class="section-header">
          <div>
            <h1>Spare Parts Catalog</h1>
            <p>Browse quality-tested components ready to ship to your workshop.</p>
          </div>
          <a href="order.php" class="btn btn-outline">View Cart<?= $cartCount > 0 ? " ($cartCount)" : "" ?></a>
        </div>

        <?php if ($addedNotice): ?>
          <div class="alert alert-success">
            Item added to your cart. <a href="order.php" style="text-decoration: underline; font-weight: 600;">Go to Cart &rarr;</a>
          </div>
        <?php endif; ?>

        <div class="search-bar-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" id="searchBar" class="search-input" placeholder="Search spare parts by name...">
        </div>

        <div class="product-grid" id="productGrid">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <?php
              $product_image_name = 'produk' . $row['id'];
              $image_extension = pathinfo($row['image'], PATHINFO_EXTENSION);
              $image_path = 'uploads/' . $product_image_name . '.' . $image_extension;

              if (file_exists($image_path)) {
                $image_src = $image_path;
              } elseif (!empty($row['image']) && file_exists($row['image'])) {
                $image_src = $row['image'];
              } else {
                $image_src = 'gambar2.png';
              }
              $stockClass = ((int)$row['stock'] <= 10) ? 'stock-pill low' : 'stock-pill';
              ?>
              <article class="product-card" data-name="<?= htmlspecialchars($row['name']) ?>">
                <div class="product-thumb">
                  <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                </div>
                <div class="product-body">
                  <div class="product-meta-row">
                    <span class="stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                    <span class="<?= $stockClass ?>">Stock: <?= (int)$row['stock'] ?></span>
                  </div>
                  <h3><?= htmlspecialchars($row['name']) ?></h3>
                  <p class="product-desc"><?= htmlspecialchars($row['description']) ?></p>
                  <p class="product-price">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                  <div class="product-actions">
                    <a href="?add_to_cart=<?= (int)$row['id']; ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                    <a href="product-details.php?id=<?= (int)$row['id']; ?>" class="btn btn-outline btn-sm">Reviews</a>
                  </div>
                </div>
              </article>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="surface-card empty-state" style="grid-column: 1 / -1;">
              <h3>No products found</h3>
              <p>Check back soon for newly stocked spare parts.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="features-band">
      <div class="container">
        <div class="features-grid">
          <div class="feature-item">
            <div class="feature-icon"><img src="gambar3.png" alt="Free Shipping"></div>
            <div>
              <h3>Free Shipping</h3>
              <p>Fast, dependable delivery directly to your doorstep.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon"><img src="gambar4.png" alt="24/7 Support"></div>
            <div>
              <h3>24/7 Support</h3>
              <p>Dedicated assistance for part fitment and order help.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon"><img src="gambar5.png" alt="Free Return"></div>
            <div>
              <h3>Free Return</h3>
              <p>Easy return guarantee if a spare part doesn't match.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="promo-section">
      <div class="container">
        <div class="promo-card">
          <h2>Upgrade Your Ride Today! Special Discounts End on Sunday!</h2>
          <p>Stock up on essential maintenance parts while weekend pricing lasts.</p>
          <a href="#products" class="btn btn-primary btn-lg">Back to Top &uarr;</a>
        </div>
      </div>
    </section>
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
    const searchBar = document.getElementById('searchBar');
    const productGrid = document.getElementById('productGrid');
    const productCards = productGrid.getElementsByClassName('product-card');

    searchBar.addEventListener('input', function () {
      const query = searchBar.value.toLowerCase();
      for (let i = 0; i < productCards.length; i++) {
        const productName = productCards[i].getAttribute('data-name').toLowerCase();
        productCards[i].style.display = productName.includes(query) ? 'flex' : 'none';
      }
    });
  </script>
</body>

</html>
