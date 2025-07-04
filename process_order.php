<?php
session_start();

// Pastikan pengguna login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil data dari form
$total_amount = $_POST['total_amount'];
$user_id = $_POST['user_id'];
$address = $_POST['address'];
$payment_method = $_POST['payment_method'];

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "part";
$conn = new mysqli($servername, $username, $password, $dbname);

// Masukkan data pesanan ke tabel orders
$sql = "INSERT INTO orders (user_id, total_amount, address, payment_method, status)
        VALUES ('$user_id', '$total_amount', '$address', '$payment_method', 'Pending')";
if ($conn->query($sql) === TRUE) {
    $order_id = $conn->insert_id;

    // Masukkan detail pesanan ke tabel order_items
    foreach ($_SESSION['cart'] as $product_id => $cart_item) {
        $quantity = $cart_item['quantity'];
        // Ambil harga produk dari database
        $product_sql = "SELECT price FROM products WHERE id = '$product_id'";
        $product_result = $conn->query($product_sql);
        $product = $product_result->fetch_assoc();
        $price = $product['price'];

        // Masukkan data ke tabel order_items
        $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                           VALUES ('$order_id', '$product_id', '$quantity', '$price')";
        $conn->query($order_item_sql);
    }

    // Kosongkan keranjang
    unset($_SESSION['cart']);

    // Redirect atau tampilkan konfirmasi
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>
