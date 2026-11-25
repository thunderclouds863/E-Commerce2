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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];

        $update_query = "UPDATE orders SET status='$new_status' WHERE id=$order_id";
        if (mysqli_query($conn, $update_query)) {
            header("Location: courier_dashboard.php");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }
}

$query = "SELECT * FROM orders WHERE status IN ('Siap Dikirim', 'Dikirim')";
$result = mysqli_query($conn, $query);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$usernameDisplay = htmlspecialchars($_SESSION['username'] ?? 'Courier', ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'] ?? 'C', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="courier_dashboard.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
                <span class="brand-badge">Courier</span>
            </a>
            <nav class="site-nav" aria-label="Courier navigation">
                <a href="courier_dashboard.php" class="active">Deliveries</a>
                <div class="user-menu">
                    <button type="button" class="user-trigger" aria-haspopup="true" aria-expanded="false">
                        <span class="user-avatar"><?= $avatarInitial ?></span>
                        <span><?= $usernameDisplay ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="?logout=true">Logout</a>
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

    <main class="page-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h1>Orders to be Delivered</h1>
                    <p>Update shipment status as packages leave the warehouse and reach customers.</p>
                </div>
            </div>

            <div class="surface-card" style="padding: 0; overflow: hidden;">
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="data-table" style="min-width: 440px;">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Shipping Address</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$row['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($row['address'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge <?= ($row['status'] == 'Dikirim') ? 'badge-info' : 'badge-warning' ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'Siap Dikirim'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                                    <input type="hidden" name="new_status" value="Dikirim">
                                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">Mark as Dikirim</button>
                                                </form>
                                            <?php elseif ($row['status'] == 'Dikirim'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                                    <input type="hidden" name="new_status" value="Sampai Tujuan">
                                                    <button type="submit" name="update_status" class="btn btn-success btn-sm">Mark as Sampai Tujuan</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        No orders currently ready for delivery.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="portal-footer">
        &copy; 2024 Guspart Autoshop. All Rights Reserved.
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>
<?php
$conn->close();
?>
