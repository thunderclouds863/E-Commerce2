<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    header("Location: login.php");
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_completed'])) {
    $order_id = $_POST['order_id'];
    // Mark the order as 'Selesai'
    $update_query = "UPDATE orders SET status = 'Selesai' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();
    header("Location: order-history.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - AS Berkah E-Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f5f2;
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

        s .navbar {
            background-color: #ff7043;
            padding: 10px;
            text-align: center;
        }

        .navbar a {
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            margin: 0 15px;
            border-radius: 8px;
        }

        .navbar a:hover {
            background-color: #ff5722;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            flex: 1;
            /* This ensures the container expands to take up available space */
        }

        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th,
        .order-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .order-table th {
            background-color: #f2f2f2;
        }

        .order-table td .status {
            font-weight: bold;
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


        .view-details-btn {
            background-color: #ff9f1c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
        }

        .view-details-btn:hover {
            background-color: #ff7043;
        }

        .view-details-btn {
            background-color: #ff9f1c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
        }

        .view-details-btn:hover {
            background-color: #ff7043;
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

    <div class="container">
        <div class="content">
            <h2>Your Order History</h2>

            <?php
            if ($orders_result->num_rows > 0) {
                echo "<table class='order-table'>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";
                while ($order = $orders_result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$order['id']}</td>
                            <td>{$order['created_at']}</td>
                            <td>Rp " . number_format($order['total_amount'], 0, ',', '.') . "</td>
                            <td><span class='status'>{$order['status']}</span></td>
                            <td>";

                    if ($order['status'] == 'Sampai Tujuan') {
                        echo "
                                <form method='POST'>
                                    <input type='hidden' name='order_id' value='{$order['id']}'>
                                    <button type='submit' name='mark_completed'>Selesai</button>
                                </form>
                            ";
                    } else {
                        echo "<a href='order-details.php?order_id={$order['id']}' class='view-details-btn'>View Details</a>";
                        // Tambahkan tombol Review untuk pesanan dengan status "Selesai"
                        if ($order['status'] == 'Selesai') {
                            // Ambil product_id dari pesanan untuk link ulasan
                            echo " <a href='product-details.php?id={$order['product_id']}' class='view-details-btn'>Review</a>";
                        }
                    }

                    echo "</td>
                        </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No orders found.</p>";
            }
            ?>
        </div>
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

</body>


</html>

<?php
$conn->close();
?>