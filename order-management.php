<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
  header("Location: index.php");
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

// Handle Order Confirmation
if (isset($_GET['confirm_order'])) {
    $order_id = $_GET['confirm_order'];

    // Update order status to 'Dikonfirmasi'
    $update_status = "UPDATE orders SET status = 'Dikonfirmasi' WHERE id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();


    $update_status = "UPDATE orders SET status = 'Dikemas' WHERE id = ? And Status = 'Dikonfirmasi'";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    $update_status = "UPDATE orders SET status = 'Siap Dikirim' WHERE id = ? And Status = 'Dikemas'";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Decrease stock for each item in the order
    $order_items = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($order_items);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        // Update product stock
        $update_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stmt = $conn->prepare($update_stock);
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();
    }

    header("Location: order-management.php");
    exit();
}

$sql = "SELECT orders.id, orders.created_at, orders.total_amount, orders.status, users.username
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin</title>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #2f3b52;
        color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    header {
        padding: 20px;
        text-align: center;
        background-color: #1c2d41;
        color: white;
        font-size: 24px;
        font-weight: bold;
    }

    .container {
        width: 80%;
        margin: 20px auto;
        background-color: #3a4e6d;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .order-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .order-table th, .order-table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #444;
        color: #ffffff;
    }

    .order-table th {
        background-color: #ff8a65;
        font-weight: bold;
    }

    .order-table td {
        background-color: #2d3c4e;
    }

    .order-table td .status {
        font-weight: bold;
        color: #4caf50;
    }

    .confirm-btn {
        background-color: #4caf50;
        color: white;
        padding: 8px 14px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .confirm-btn:hover {
        background-color: #388e3c;
        transform: translateY(-2px);
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

    footer {
        background-color: #1c2d41;
        padding: 15px;
        color: white;
        text-align: center;
        position: fixed;
        bottom: 0;
        width: 100%;
    }

    nav {
        background-color: #1c2d41;
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    nav ul {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    nav ul li {
        margin: 0 15px;
    }

    nav ul li a {
        color: white;
        text-decoration: none;
        font-weight: bold;
        padding: 8px 14px;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    nav ul li a:hover {
        background-color: #ff8a65;
        transform: scale(1.05);
    }

    nav div a {
        color: white;
        text-decoration: none;
        font-weight: bold;
        padding: 8px 14px;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    nav div a:hover {
        background-color: #ff8a65;
        transform: scale(1.05);
    }
    .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 40px;
        }

</style>

</head>
<body>
<nav>
<div class="logo">
            <img src=logo.png alt="Logo">
            <a href="admin_dashboard.php">Admin Dashboard</a>
        </div>
    <ul>
      <li><a href="admin_dashboard.php">Home</a></li>
      <li><a href="inventory_management.php">Inventory Management</a></li>
      <li><a href="order-management.php">Order Management</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
    <header>
            <h1>Order Management</h1>
    </header>

    <div class="container">
        <h2>Order List</h2>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Detail</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['username']}</td>
                                <td>{$row['created_at']}</td>
                                <td>Rp " . number_format($row['total_amount'], 0, ',', '.') . "</td>
                                <td> <a href='order-details-admin.php?order_id={$row['id']}' class='view-details-btn'>View Details</a></td>
                                <td><span class='status'>{$row['status']}</span></td>
                                <td>";
                        if ($row['status'] == 'Pending') {
                            echo "<a href='order-management.php?confirm_order={$row['id']}' class='confirm-btn'>Confirm</a>";
                        }if ($row['status'] == 'Dikonfirmasi') {
                            echo "<a href='order-management.php?confirm_order={$row['id']}' class='confirm-btn'>Dikemas</a>";
                        }
                        echo "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; © 2024 Gushpart Autoshop. All Rights Reserved.</p>
    </footer>

</body>
</html>

<?php
$conn->close();
?>
