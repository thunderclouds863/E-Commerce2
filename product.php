<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$conn = db();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$addedNotice = false;
if (isset($_GET['add_to_cart']) && ctype_digit((string)$_GET['add_to_cart'])) {
    $product_id = (int)$_GET['add_to_cart'];
    $chk = $conn->prepare('SELECT id, stock FROM products WHERE id = ? LIMIT 1');
    $chk->bind_param('i', $product_id);
    $chk->execute();
    $prod = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($prod && (int)$prod['stock'] > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = min(10, (int)$_SESSION['cart'][$product_id]['quantity'] + 1);
        } else {
            $_SESSION['cart'][$product_id] = ['quantity' => 1];
        }
        $addedNotice = true;
    }
}

$result = $conn->query('SELECT * FROM products ORDER BY id ASC');
$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
$cartCount = cart_item_count();
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
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <header class="site-header">
    <div class="header-inner">
      <a href="index.php" class="brand-logo">
        <img src="gambar1.png" alt="Guspart Autoshop Logo">
        <span class="brand-name">Guspart Autoshop</span>
      </a>
      <nav class="site-nav" aria-label="Primary navigation">
        <a href="index.php">Home</a>
        <a href="product.php" class="active">Products</a>
        <a href="order.php">Order<?= $cartCount > 0 ? " ({$cartCount})" : '' ?></a>
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

  <main id="main-content">
    <section class="page-section" id="products">
      <div class="container">
        <div class="section-header">
          <div>
            <h1>Spare Parts Catalog</h1>
            <p>Browse quality-tested components ready to ship to your workshop.</p>
          </div>
          <a href="order.php" class="btn btn-outline">View Cart<?= $cartCount > 0 ? " ({$cartCount})" : '' ?></a>
        </div>

        <?php if ($addedNotice): ?>
          <div class="alert alert-success" role="status" aria-live="polite">
            Item added to your cart. <a href="order.php" style="text-decoration: underline; font-weight: 600;">Go to Cart &rarr;</a>
          </div>
        <?php endif; ?>

        <div class="search-bar-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" id="searchBar" class="search-input" placeholder="Search spare parts by name..." aria-label="Search spare parts by name">
        </div>

        <div class="product-grid" id="productGrid">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <?php
              $image_src = resolve_product_image((int)$row['id'], $row['image']);
              $stockNum = (int)$row['stock'];
              $stockClass = ($stockNum <= 10) ? 'stock-pill low' : 'stock-pill';
              ?>
              <article class="product-card" data-name="<?= e($row['name']) ?>">
                <div class="product-thumb">
                  <img src="<?= e($image_src) ?>" alt="<?= e($row['name']) ?>">
                </div>
                <div class="product-body">
                  <div class="product-meta-row">
                    <span class="stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                    <span class="<?= $stockClass ?>"><?= $stockNum > 0 ? "Stock: {$stockNum}" : 'Out of Stock' ?></span>
                  </div>
                  <h3><?= e($row['name']) ?></h3>
                  <p class="product-desc"><?= e($row['description']) ?></p>
                  <p class="product-price"><?= format_rp($row['price']) ?></p>
                  <div class="product-actions">
                    <?php if ($stockNum > 0): ?>
                      <a href="?add_to_cart=<?= (int)$row['id'] ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                    <?php else: ?>
                      <button type="button" class="btn btn-outline btn-sm" disabled>Out of Stock</button>
                    <?php endif; ?>
                    <a href="product-details.php?id=<?= (int)$row['id'] ?>" class="btn btn-outline btn-sm">Reviews</a>
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
