<?php
session_start();
$isLoggedIn = isset($_SESSION['username']); // Check if user is logged in
if (!$isLoggedIn) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = $_GET['order_id']; // Mendapatkan ID pesanan dari URL

// Mengambil detail pesanan berdasarkan order_id
$order_sql = "SELECT o.id, o.total_amount, o.status, o.created_at, o.address, o.payment_method
              FROM orders o
              WHERE o.id = ?";
$stmt_order = $conn->prepare($order_sql);
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();

// Mengambil data order_items dengan informasi produk
$order_items_sql = "SELECT oi.*, p.name AS product_name, p.price AS product_price
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($order_items_sql);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items_result = $stmt_items->get_result();

if ($order_result->num_rows > 0) {
    $order = $order_result->fetch_assoc();
} else {
    echo "Order not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - AS Berkah E-Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f5f2;
            /* Light pastel background */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            /* Full viewport height */
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

footer {
    background: #2F4F4F;
    color: white;
    padding: 40px 0;
    margin-top: 60px;
    text-align: center;
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


        .container {
            width: 80%;
            max-width: 1200px;
            margin: 20px auto;
            flex: 1;
            /* Ensure container takes up available space */
        }

        .content {
            margin-top: 30px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th,
        .cart-table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .cart-table th {
            background-color: #2F4F4F;
            color: white;
        }

        .cart-table td {
            text-align: right;
        }

        .cart-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff4e6;
            border-radius: 8px;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .navbar a {
                padding: 8px 15px;
                /* Smaller padding for mobile screens */
            }

            .container {
                width: 95%;
                /* Reduced container width on smaller screens */
            }
        }
    </style>
</head>

<body>

<header>
    <div class="logo">
        <img src="gambar1.png" alt="Logo">
        <h2>Guspart Autoshop</h2>
    </div>
    <nav>
        <a href="index.php">Home</a>
        <a href="product.php">Product</a>
        <a href="Order.php">Order</a>
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


    <div class="container">
        <div class="content">
            <h2>Order ID: <?php echo $order['id']; ?></h2>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            <p><strong>Total Amount:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
            <p><strong>Shipping Address:</strong> <?php echo $order['address']; ?></p>
            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
            <p><strong>Order Date:</strong> <?php echo $order['created_at']; ?></p>

            <h3>Order Items</h3>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_order_amount = 0;
                    while ($item = $order_items_result->fetch_assoc()) {
                        $total_product_price = $item['quantity'] * $item['product_price'];
                        $total_order_amount += $total_product_price;
                        ?>
                        <tr>
                            <td><?php echo $item['product_name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>Rp <?php echo number_format($item['product_price'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($total_product_price, 0, ',', '.'); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <p class="total-price">Total Order Amount: Rp
                    <?php echo number_format($total_order_amount, 0, ',', '.'); ?>
                </p>
            </div>
        </div>
    </div>

    <footer>
    <p>&copy; 2024 Guspart Autoshop. All Rights Reserved.</p>
</footer>


</body>
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

</script>
</html>

<?php
$conn->close();
?>