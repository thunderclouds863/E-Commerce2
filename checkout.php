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

$cart_items = $_SESSION['cart']; // Get the cart items from session
$total_amount = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];

    // Insert order into the database
    $user_id = $_SESSION['user_id']; // Get user ID from session
    $sql = "INSERT INTO orders (user_id, total_amount, address, payment_method, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $user_id, $total_amount, $address, $payment_method);
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id; // Get the ID of the newly created order

        // Insert order items
        foreach ($cart_items as $product_id => $item) {
            $sql = "SELECT price FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $price = $product['price'];
            $quantity = $item['quantity'];

            // Add the price * quantity to total_amount
            $total_amount += $price * $quantity;

            // Insert into order_items table
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt->execute();
        }

        // Update the total_amount in orders table after the loop
        $sql = "UPDATE orders SET total_amount = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $total_amount, $order_id);
        $stmt->execute();

        // Clear cart after successful order
        $_SESSION['cart'] = [];
        header("Location: order-history.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .title a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            border-radius: 5px;
            padding: 8px 12px;
        }

        .title a:hover {
            background-color: #ff5722;
        }

        .checkout-container {
            padding: 30px;
            background-color: #fff;
            max-width: 800px;
            margin: 30px auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .checkout-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .checkout-item h3 {
            margin: 0;
        }

        .total-price {
            font-size: 20px;
            margin-top: 20px;
            text-align: right;
            color: #ff7043;
        }

        .checkout-form {
            margin-top: 30px;
        }

        .checkout-form textarea,
        .checkout-form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .checkout-form button {
            background-color: #ff7043;
            color: white;
            padding: 15px;
            border-radius: 5px;
            border: none;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .checkout-form button:hover {
            background-color: #ff5722;
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

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            /* Ensures the body takes up full height */
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


        .title a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            border-radius: 5px;
            padding: 8px 12px;
        }

        .title a:hover {
            background-color: #ff5722;
        }

        .content {
            flex-grow: 1;
            /* Ensures content takes up remaining space */
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .cart-item-info {
            flex-grow: 1;
            padding-left: 15px;
        }

        .cart-item-info h3 {
            font-size: 18px;
            margin: 0;
        }

        .cart-item-info .price {
            font-size: 16px;
            color: #ff7043;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
        }

        .cart-item-actions input {
            width: 50px;
            margin-right: 10px;
        }

        .cart-item-actions a {
            background-color: #ff7043;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
        }

        .cart-item-actions a:hover {
            background-color: #ff5722;
        }

        .checkout-button {
            display: inline-block;
            background-color: #ff7043;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            margin-top: 20px;
        }

        .checkout-button:hover {
            background-color: #ff5722;
        }
        * {
      margin: 0;
      padding: 0;

      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f5f5f5;
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

    .progress-bar {
      display: flex;
      justify-content: center;
      margin: 30px 0;
      position: relative;
    }

    .progress-step {
      background-color: #2F4F4F;
      color: white;
      padding: 15px 40px;
      position: relative;
      min-width: 200px;
      text-align: center;
    }

    .progress-step.active {
      background-color: #2F4F4F;
    }

    .progress-step.inactive {
      background-color: #e0e0e0;
    }

    .progress-step:after {
      content: '';
      position: absolute;
      right: -20px;
      top: 0;
      border-left: 20px solid #2F4F4F;
      border-top: 25px solid transparent;
      border-bottom: 25px solid transparent;
    }

    .progress-step.inactive:after {
      border-left-color: #e0e0e0;
    }

    /* Enhanced styling for the content section */
    .content {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .content h2 {
      color: #2F4F4F;
      font-size: 28px;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f4f4f4;
    }

    /* Cart item styling */
    .cart-item {
      display: flex;
      align-items: center;
      padding: 20px;
      margin-bottom: 20px;
      background: #f8f9fa;
      border-radius: 8px;
      transition: transform 0.2s ease;
    }

    .cart-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .cart-item img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 25px;
    }

    .cart-item-info {
      flex: 1;
    }

    .cart-item-info h3 {
      color: #2F4F4F;
      font-size: 18px;
      margin-bottom: 10px;
    }

    .price {
      color: #666;
      font-size: 16px;
      font-weight: 500;
    }

    .cart-item-actions {
      display: flex;
      align-items: center;
      gap: 20px;
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


    /* Quantity input styling */
    input[type="number"] {
      width: 70px;
      padding: 8px;
      border: 2px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
      text-align: center;
    }

    /* Remove button styling */
    .remove-button {
      background: #ff4444;
      color: white;
      padding: 8px 15px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s ease;
    }

    .remove-button:hover {
      background: #cc0000;
    }

    /* Checkout button styling */
    .checkout-button {
      display: block;
      width: 100%;
      max-width: 300px;
      margin: 30px auto;
      padding: 15px 30px;
      background: #2F4F4F;
      color: white;
      text-align: center;
      text-decoration: none;
      border-radius: 8px;
      font-size: 18px;
      font-weight: 500;
      transition: background 0.3s ease;
    }

    .checkout-button:hover {
      background: #1a2f2f;
    }

    /* Empty cart message styling */
    .content p {
      text-align: center;
      color: #666;
      font-size: 18px;
      padding: 40px 0;
    }

    /* Footer styling */
    footer {
      background: #2F4F4F;
      color: white;
      padding: 40px 0;
      margin-top: 60px;
      text-align: center;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .cart-item {
        flex-direction: column;
        text-align: center;
      }

      .cart-item img {
        margin-right: 0;
        margin-bottom: 15px;
      }

      .cart-item-actions {
        flex-direction: column;
        margin-top: 15px;
      }

      .remove-button {
        margin-top: 10px;
      }
    }

    /* Adding loading animation for better UX */
    .update-quantity-form {
      position: relative;
    }

    .update-quantity-form.loading::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.8);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Toast notification for actions */
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 15px 25px;
      background: #333;
      color: white;
      border-radius: 6px;
      display: none;
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
      }

      to {
        transform: translateX(0);
      }
    }

    .main-content {
      display: flex;
      justify-content: space-around;
      padding: 50px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .product-card {
      background-color: #fff;
      padding: 20px;
      text-align: center;
      border-radius: 5px;
      width: 300px;
    }

    .product-card img {
      width: 200px;
      height: 200px;
      object-fit: contain;
    }

    .product-card h2 {
      color: #2F4F4F;
      margin: 10px 0;
    }

    .order-details {
      background-color: #2F4F4F;
      color: white;
      padding: 30px;
      width: 400px;
      border-radius: 5px;
    }

    .order-details h2 {
      margin-bottom: 20px;
    }

    .price-row {
      display: flex;
      justify-content: space-between;
      margin: 10px 0;
    }

    .voucher-input {
      background-color: #f5f5f5;
      padding: 15px;
      margin: 20px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .create-order-btn {
      background-color: #f5f5f5;
      color: #2F4F4F;
      padding: 15px;
      width: 100%;
      border: none;
      cursor: pointer;
      font-weight: bold;
      margin-top: 20px;
    }

    .create-order-btn:hover {
      background-color: #e0e0e0;
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

    <div class="checkout-container">
        <h2>Review Your Order</h2>

        <?php
        if (!empty($cart_items)) {
            foreach ($cart_items as $product_id => $item) {
                $sql = "SELECT * FROM products WHERE id = $product_id";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $total_amount += $product['price'] * $item['quantity']; // Accumulate total amount
                    ?>

                    <div class="checkout-item">
                        <h3><?php echo $product['name']; ?></h3>
                        <p>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?> x <?php echo $item['quantity']; ?></p>
                    </div>

                    <?php
                }
            }
        }
        ?>

        <div class="cart-summary">
            <p class="total-price">Total Price: Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></p>
        </div>

        <h2>Shipping Address</h2>
        <form action="checkout.php" method="POST" class="checkout-form">
            <textarea name="address" placeholder="Enter your shipping address" required></textarea>

            <h3>Payment Method</h3>
            <select name="payment_method" required>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="credit_card">Credit Card</option>
                <option value="cash_on_delivery">Cash on Delivery</option>
            </select>

            <button type="submit">Pay</button>
        </form>

    </div>

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
  </script>

  </body>

</html>

<?php
$conn->close();
?>