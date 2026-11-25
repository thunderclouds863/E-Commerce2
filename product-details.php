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

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("<h3 style='color: red;'>Error: Invalid or missing Product ID in the URL.</h3>");
}

$product_id = intval($_GET['id']);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<h3 style='color: red;'>Error: Database connection failed. Please try again later.</h3>");
}

$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result()->fetch_assoc();

if (!$product_result) {
    die("<h3 style='color: red;'>Error: Product not found. Please check the URL.</h3>");
}

$review_sql = "SELECT reviews.*, users.username
               FROM reviews
               JOIN users ON reviews.user_id = users.id
               WHERE reviews.product_id = ?
               ORDER BY reviews.created_at DESC";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $review_text = htmlspecialchars($_POST['review_text'], ENT_QUOTES, 'UTF-8');
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];

    if ($rating >= 1 && $rating <= 5 && !empty($review_text)) {
        $insert_review_sql = "INSERT INTO reviews (product_id, user_id, review_text, rating) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_review_sql);
        $insert_stmt->bind_param("iisi", $product_id, $user_id, $review_text, $rating);
        if ($insert_stmt->execute()) {
            header("Location: product-details.php?id=$product_id");
            exit;
        } else {
            $error = "Failed to submit your review. Please try again.";
        }
    } else {
        $error = "Please provide a valid rating (1-5) and review text.";
    }
}

$product_image_name = 'produk' . $product_result['id'];
$image_extension = pathinfo($product_result['image'], PATHINFO_EXTENSION);
$image_path = 'uploads/' . $product_image_name . '.' . $image_extension;
if (file_exists($image_path)) {
    $image_src = $image_path;
} elseif (!empty($product_result['image']) && file_exists($product_result['image'])) {
    $image_src = $product_result['image'];
} else {
    $image_src = 'gambar2.png';
}

$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product_result['name'], ENT_QUOTES, 'UTF-8') ?> - Guspart Autoshop</title>
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
                <a href="order.php">Order</a>
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
            <div style="margin-bottom: 20px;">
                <a href="product.php" class="btn btn-outline btn-sm">&larr; Back to Products</a>
            </div>

            <div class="surface-card product-detail-grid">
                <div class="product-detail-media">
                    <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($product_result['name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="product-detail-info">
                    <span class="stock-pill" style="margin-bottom: 12px; display: inline-block;">In Stock: <?= (int)$product_result['stock'] ?></span>
                    <h1><?= htmlspecialchars($product_result['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <p style="color: var(--text-secondary);"><?= htmlspecialchars($product_result['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="product-detail-price">Rp <?= number_format($product_result['price'], 0, ',', '.') ?></p>
                    <div class="hero-actions">
                        <a href="product.php?add_to_cart=<?= (int)$product_result['id'] ?>" class="btn btn-primary btn-lg">Add to Cart</a>
                        <a href="order.php" class="btn btn-outline btn-lg">Go to Cart</a>
                    </div>
                </div>
            </div>

            <div class="reviews-layout">
                <div class="surface-card">
                    <h2 style="font-size: 1.35rem; color: var(--primary); margin-bottom: 18px;">Customer Reviews</h2>
                    <?php if ($reviews_result->num_rows > 0): ?>
                        <?php while ($review = $reviews_result->fetch_assoc()): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="review-author"><?= htmlspecialchars($review['username'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="stars" aria-label="<?= (int)$review['rating'] ?> out of 5 stars"><?= str_repeat('&#9733;', (int)$review['rating']) ?></span>
                                </div>
                                <p style="margin-bottom: 8px; color: var(--text);"><?= htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8') ?></p>
                                <span class="review-date"><?= htmlspecialchars($review['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state" style="padding: 28px 12px;">
                            <p style="margin: 0;">No reviews yet. Be the first to review this spare part!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="surface-card">
                    <h2 style="font-size: 1.35rem; color: var(--primary); margin-bottom: 18px;">Write a Review</h2>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <select name="rating" id="rating" required>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Poor</option>
                                <option value="1">1 - Terrible</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="review_text">Your Review</label>
                            <textarea id="review_text" name="review_text" placeholder="Share your experience with this spare part..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary btn-block">Submit Review</button>
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
