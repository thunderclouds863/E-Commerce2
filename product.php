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

// Check if the connection is successful
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// For storing cart data
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_GET['add_to_cart'])) {
  $product_id = $_GET['add_to_cart'];
  if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity']++;
  } else {
    $_SESSION['cart'][$product_id] = ['quantity' => 1];
  }
}

// Query to fetch products from database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Check if the query was successful and returned results
if (!$result) {
  die("Error: " . $conn->error); // Display query error if the query fails
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guspart Autoshop</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      overflow-x: hidden;
    }

    header {
      padding: 20px 10%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: white;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      width: 40px;
    }

    nav {
      display: flex;
      gap: 20px;
      align-items: baseline;
    }

    nav a {
      text-decoration: none;
      color: #333;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    nav a:hover {
      color: #f4a460;
    }

    .hamburger {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 5px;
    }

    .hamburger span {
      width: 25px;
      height: 3px;
      background: #333;
    }

    .main-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      padding: 50px 10%;
      min-height: calc(100vh - 120px);
    }

    .welcome-text {
      flex: 1;
      margin-bottom: 30px;
    }

    .welcome-text h1 {
      color: 36454F;
      font-size: 48px;
      margin-bottom: 20px;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
    }

    .welcome-text p {
      color: #314e52;
      font-size: 18px;
    }

    .welcome-text a {
      color: whitesmoke;
      font-weight: bold;
      text-decoration: none;
      padding: 8px 16px;
      background: #28545a;
      border-radius: 25px;
      transition: all 0.3s ease;
    }

    .welcome-text a:hover {
      background: white;
      color: #333;
    }

    /* Hero Section */
    .hero {
      height: 65vh;
      background: #f5f5f5;
      padding: 80px 10%;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .hero-text {
      flex: 1;
    }

    .hero-text h1 {
      font-size: 48px;
      color: #2F4F4F;
      margin-bottom: 30px;
    }

    .shop-now-btn {
      padding: 15px 30px;
      background: #f4a460;
      color: white;
      border: none;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }

    .cart-image {
      flex: 1;
      text-align: right;
    }

    .cart-image img {
      width: 600px;
    }

    /* Features */
    .features {
      background-color: #2F4F4F;
      color: #E0E0E0;
      padding: 20px;
      text-align: center;
    }

    .features .features {
      display: flex;
      flex-wrap: wrap;
      justify-content: left;
      gap: 5%;
    }

    .feature {
      display: flex;
      align-items: left;
      gap: 10px;
      text-align: left;
    }

    .feature h3 {
      font-size: 1.2em;
      margin-bottom: 5px;
    }

    .feature p {
      font-size: 0.9em;
      color: #A7D5D5;
    }

    /* Products */
    .products {
      padding: 50px 10%;
      background: #fff;
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
    }

    .product-card {
      padding: 20px;
      text-align: center;
      background: #f9f9f9;
      border-radius: 10px;
    }

    .product-card img {
      width: 200px;
      margin-bottom: 15px;
    }

    .product-card .price {
      color: #2F4F4F;
      font-weight: bold;
      margin: 10px 0;
    }

    .stars {
      color: #f4a460;
    }

    /* Footer */
    footer {
      background: #2F4F4F;
      color: white;
      padding: 50px 10%;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 30px;
    }

    footer h3 {
      margin-bottom: 20px;
    }

    footer p,
    footer a {
      color: #ddd;
      text-decoration: none;
      display: block;
      margin-bottom: 10px;
    }

    .newsletter input {
      padding: 10px;
      width: 200px;
    }

    .newsletter button {
      padding: 10px 20px;
      background: #f4a460;
      border: none;
      color: white;
      cursor: pointer;
    }

    /* Promo Banner */
    .promo-banner {
      background: #2F4F4F;
      color: white;
      text-align: center;
      padding: 50px;
    }

    @media (max-width: 768px) {
      nav {
        display: none;
        flex-direction: column;
        background: white;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        padding: 10px 0;
      }

      nav.show {
        display: flex;
      }

      .hamburger {
        display: flex;
      }

      footer .features {
        flex-direction: column;
        gap: 20px;
      }
    }

    .search-bar {
      width: 100%;
      padding: 15px;
      margin-bottom: 40px;
      border: none;
      background: #f0f0f0;
      border-radius: 5px;
    }

    /* User Menu */
    .user-menu {
      position: relative;
      display: flex;
      align-items: center;
      cursor: pointer;
      gap: 5px;
    }

    .user-menu:hover .dropdown-menu {
      display: block;
    }

    .user-icon {
      font-size: 20px;
      color: #333;
    }

    #username {
      font-weight: 500;
      color: #333;
    }

    .dropdown-menu {
      display: none;
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 10px;
      border-radius: 5px;
      z-index: 1000;
      text-align: left;
    }

    .dropdown-menu a {
      display: block;
      padding: 5px 10px;
      color: #333;
      text-decoration: none;
      transition: background 0.2s ease;
    }

    .dropdown-menu a:hover {
      background: #f4a460;
      color: white;
    }

    .action-button {
      display: inline-block;
      padding: 12px 20px;
      background: linear-gradient(45deg, #333, #81C784);
      /* Gradien warna hijau */
      color: white;
      font-size: 16px;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: none;
      border-radius: 8px;
      /* Membuat sudut membulat */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      /* Efek bayangan */
      transition: all 0.3s ease;
      /* Animasi transisi */
    }

    .action-button:hover {
      background: linear-gradient(45deg, #81C784, #4CAF50);
      /* Warna gradien berubah */
      box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
      /* Efek bayangan lebih jelas */
      transform: translateY(-3px);
      /* Tombol naik sedikit */
    }

    .action-button:active {
      background: linear-gradient(45deg, #388E3C, #66BB6A);
      /* Warna lebih gelap saat diklik */
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      /* Bayangan lebih tipis */
      transform: translateY(1px);
      /* Tombol turun sedikit */
    }
  </style>
</head>

<body>
<header>
        <div class="logo">
            <img src=gambar1.png alt="Logo">
            <h2>Guspart Autoshop</h2>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <a href="product.php">Product</a>
            <a href="order.php">Order</a>
            <a href="order-history.php">Order History</a>
            <a href="#footer">Contact Us</a>
            <div class="user-menu">
                <?php if ($isLoggedIn): ?>
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="user-icon">&#128100; <?= $_SESSION['username']; ?></span>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Optionally add login link or other content for not logged in users -->
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>

        </nav>

        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>


  <section class="products" id="products">
    <input type="text" id="searchBar" class="search-bar" placeholder="Find what you need">
    <div class="product-grid" id="productGrid">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="product-card" data-name="<?= htmlspecialchars($row['name']) ?>">
            <?php
            $product_image_name = 'produk' . $row['id'];
            $image_extension = pathinfo($row['image'], PATHINFO_EXTENSION); // Ekstensi file dari kolom image
            $image_path = 'uploads/' . $product_image_name . '.' . $image_extension;

            // Check apakah file gambar ada di path
            if (file_exists($image_path)) {
              $image_src = $image_path; // Gunakan gambar yang diunggah
            } else {
              // Jika gambar tidak ditemukan, gunakan gambar default
              $image_src = 'https://via.placeholder.com/200'; // Gambar placeholder
            }
            ?>
            <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <p class="price">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
            <p class="stock">Stock: <?= $row['stock'] ?></p>
            <div class="stars">★★★★★</div>
            <a href="?add_to_cart=<?php echo $row['id']; ?>" class="action-button">Add to Cart</a>
            <a href="product-details.php?id=<?= $row['id']; ?>" class="action-button"
              style="background-color: #007BFF;">Review</a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No products found.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="features">
    <div class="features">
      <div class="feature">
        <span><img src=gambar3.png></span>
        <div>
          <h3>Free Shipping</h3>
          <p>Order now and you'll know</p>
        </div>
      </div>
      <div class="feature">
        <span><img src=gambar4.png></span>
        <div>
          <h3>24/7 Support</h3>
          <p>Order now and you'll know</p>
        </div>
      </div>
      <div class="feature">
        <span><img src=gambar5.png></span>
        <div>
          <h3>Free return</h3>
          <p>Order now and you'll know</p>
        </div>
      </div>
    </div>
  </section>

  <section class="promo-banner">
    <h2>Upgrade Your Ride Today! Special Discounts End on Sunday!</h2>
    <br>
    <a href="product.php" class="shop-now-btn">Shop now →</a>
  </section>

  <footer id="footer">
    <div>
      <h3>Guspart Autoshop</h3>
      <p>We've worked tirelessly to find the best suppliers to bring high-quality and reliable spare parts to your
        garage.</p>
    </div>
    <div>
      <h3>Get in touch</h3>
      <p>123-456-7890</p>
      <p>+123-456-7890</p>
      <p>guspart@autoshop.com</p>
    </div>
    <div>
      <h3>Useful link</h3>
      <a href="#">Services</a>
      <a href="#">Our team</a>
      <a href="#">Portfolio</a>
      <a href="#">Blog</a>
    </div>
    <div class="newsletter">
      <h3>Join our newsletter</h3>
      <p>We searched extensively for a provider that could bring,</p>
      <input type="email" placeholder="Enter your email">
      <button>Subscribe</button>
    </div>
  </footer>

  <script>
    function toggleMenu() {
      const nav = document.querySelector('nav');
      nav.classList.toggle('show');
    }


    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });

    // Sticky header
    window.onscroll = function () {
      if (window.pageYOffset > 50) {
        document.querySelector('header').style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
      } else {
        document.querySelector('header').style.boxShadow = 'none';
      }
    };
    // Get elements
    const searchBar = document.getElementById('searchBar');
    const productGrid = document.getElementById('productGrid');
    const productCards = productGrid.getElementsByClassName('product-card');

    // Add event listener for input on search bar
    searchBar.addEventListener('input', function () {
      const query = searchBar.value.toLowerCase();

      // Loop through all product cards
      for (let i = 0; i < productCards.length; i++) {
        const productName = productCards[i].getAttribute('data-name').toLowerCase();

        // Check if the product name includes the search query
        if (productName.includes(query)) {
          productCards[i].style.display = 'block'; // Show matching products
        } else {
          productCards[i].style.display = 'none'; // Hide non-matching products
        }
      }
    });

  </script>
</body>

</html>