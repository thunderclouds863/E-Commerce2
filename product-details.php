<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['customer']);

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header('Location: product.php');
    exit;
}

$product_id = (int)$_GET['id'];
$conn = db();

$stmt = $conn->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product_result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product_result) {
    http_response_code(404);
    die('Product not found.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!verify_csrf()) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $review_text = trim($_POST['review_text'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $user_id = (int)$_SESSION['user_id'];

        if ($rating >= 1 && $rating <= 5 && $review_text !== '') {
            $insert_stmt = $conn->prepare('INSERT INTO reviews (product_id, user_id, review_text, rating) VALUES (?, ?, ?, ?)');
            $insert_stmt->bind_param('iisi', $product_id, $user_id, $review_text, $rating);
            if ($insert_stmt->execute()) {
                $insert_stmt->close();
                header("Location: product-details.php?id={$product_id}");
                exit;
            }
            $insert_stmt->close();
            $error = 'Failed to submit your review. Please try again.';
        } else {
            $error = 'Please provide a valid rating (1-5) and review text.';
        }
    }
}

$review_sql = "SELECT reviews.*, users.username
               FROM reviews
               JOIN users ON reviews.user_id = users.id
               WHERE reviews.product_id = ?
               ORDER BY reviews.created_at DESC";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param('i', $product_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();

$image_src = resolve_product_image((int)$product_result['id'], $product_result['image']);
$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
$cartCount = cart_item_count();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($product_result['name']) ?> - Guspart Autoshop</title>
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

    <main id="main-content" class="page-section">
        <div class="container">
            <div style="margin-bottom: 20px;">
                <a href="product.php" class="btn btn-outline btn-sm">&larr; Back to Products</a>
            </div>

            <div class="surface-card product-detail-grid">
                <div class="product-detail-media">
                    <img src="<?= e($image_src) ?>" alt="<?= e($product_result['name']) ?>">
                </div>
                <div class="product-detail-info">
                    <span class="stock-pill <?= ((int)$product_result['stock'] <= 10) ? 'low' : '' ?>" style="margin-bottom: 12px; display: inline-block;">
                        <?= ((int)$product_result['stock'] > 0) ? 'In Stock: ' . (int)$product_result['stock'] : 'Out of Stock' ?>
                    </span>
                    <h1><?= e($product_result['name']) ?></h1>
                    <p style="color: var(--text-secondary);"><?= e($product_result['description']) ?></p>
                    <p class="product-detail-price"><?= format_rp($product_result['price']) ?></p>
                    <div class="hero-actions">
                        <?php if ((int)$product_result['stock'] > 0): ?>
                            <a href="product.php?add_to_cart=<?= (int)$product_result['id'] ?>" class="btn btn-primary btn-lg">Add to Cart</a>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline btn-lg" disabled>Out of Stock</button>
                        <?php endif; ?>
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
                                    <span class="review-author"><?= e($review['username']) ?></span>
                                    <span class="stars" aria-label="<?= (int)$review['rating'] ?> out of 5 stars"><?= str_repeat('&#9733;', (int)$review['rating']) ?></span>
                                </div>
                                <p style="margin-bottom: 8px; color: var(--text);"><?= e($review['review_text']) ?></p>
                                <span class="review-date"><?= e($review['created_at']) ?></span>
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
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-error" role="alert" aria-live="polite"><?= e($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <?= csrf_field() ?>
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
