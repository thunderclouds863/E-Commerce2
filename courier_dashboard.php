<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'courier') {
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

// Proses perubahan status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    // Pastikan 'order_id' dan 'new_status' ada
    if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];

        // Update status pesanan
        $update_query = "UPDATE orders SET status='$new_status' WHERE id=$order_id";
        if (mysqli_query($conn, $update_query)) {
            // Redirect setelah update
            header("Location: kurir_dashboard.php");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }
}

// Mengambil pesanan dengan status 'Siap Dikirim' atau 'Dikirim'
$query = "SELECT * FROM orders WHERE status IN ('Siap Dikirim', 'Dikirim')";
$result = mysqli_query($conn, $query);


// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            background-color: #6200ea;
            color: white;
            padding: 20px 0;
            text-align: center;
            position: relative;
            font-size: 24px;
            font-weight: 500;
        }

        .logout-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            background-color: #f44336;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #d32f2f;
        }

        .container {
            width: 85%;
            max-width: 1200px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        h1 {
            color: #6200ea;
            text-align: center;
            font-size: 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        th {
            background-color: #6200ea;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        button {
            padding: 10px 20px;
            background-color: #03a9f4;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0288d1;
        }

        .status-buttons {
            display: flex;
            justify-content: space-evenly;
            gap: 10px;
        }

        footer {
            background-color: #6200ea;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 30px;
        }

        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .header {
                font-size: 20px;
            }

            .container {
                width: 90%;
            }

            table {
                font-size: 14px;
            }

            button {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Courier Dashboard</h2>
        <a href="?logout=true" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <h1>Orders to be Delivered</h1>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <?php if ($row['status'] == 'Siap Dikirim'): ?>
                                <form method="POST" class="status-buttons">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="new_status" value="Dikirim">
                                    <button type="submit" name="update_status">Dikirim</button>
                                </form>
                            <?php elseif ($row['status'] == 'Dikirim'): ?>
                                <form method="POST" class="status-buttons">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="new_status" value="Sampai Tujuan">
                                    <button type="submit" name="update_status">Sampai Tujuan</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; 2024 Courier Service. All rights reserved.</p>
    </footer>

</body>

</html>

<?php
$conn->close();
?>
